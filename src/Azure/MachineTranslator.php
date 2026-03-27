<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\Service\MachineTranslator\Azure;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tobento\Service\MachineTranslator\Exception\QuotaExceededException;
use Tobento\Service\MachineTranslator\Exception\TranslateException;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;
use Tobento\Service\MachineTranslator\NonTranslatableStrategy\NullStrategy;
use Tobento\Service\MachineTranslator\NonTranslatableStrategyInterface;

class MachineTranslator implements MachineTranslatorInterface
{
    /**
     * Create a new instance.
     *
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @param string $endpoint
     * @param string $region
     * @param string $apiKey
     * @param string $name
     * @param NonTranslatableStrategyInterface $nonTranslatableStrategy
     */
    public function __construct(
        protected ClientInterface $client,
        protected RequestFactoryInterface $requestFactory,
        protected StreamFactoryInterface $streamFactory,
        protected string $endpoint,
        protected string $region,
        #[\SensitiveParameter] protected string $apiKey,
        protected string $name = 'azure',
        protected NonTranslatableStrategyInterface $nonTranslatableStrategy = new NullStrategy(),
    ) {
        if ($this->endpoint === '' || $this->region === '' || $this->apiKey === '') {
            throw new \InvalidArgumentException(
                'Azure translator requires non-empty "endpoint", "region", and "apiKey" values.'
            );
        }
    }

    /**
     * Returns the endpoint.
     *
     * @return string
     */
    public function endpoint(): string
    {
        return $this->endpoint;
    }
    
    /**
     * Returns the region.
     *
     * @return string
     */
    public function region(): string
    {
        return $this->region;
    }
    
    /**
     * Returns the apiKey.
     *
     * @return string
     */
    public function apiKey(): string
    {
        return $this->apiKey;
    }
    
    /**
     * Returns the nonTranslatableStrategy.
     *
     * @return NonTranslatableStrategyInterface
     */
    public function nonTranslatableStrategy(): NonTranslatableStrategyInterface
    {
        return $this->nonTranslatableStrategy;
    }
    
    /**
     * Returns the translator name (e.g. "google", "deepl", "azure").
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Translates the given text into the specified locale.
     *
     * @param string $text The text to translate.
     * @param string $locale The target locale (e.g. "de", "fr", "es").
     * @return string The translated text.
     * @throws TranslateException If translation fails.
     */
    public function translate(string $text, string $locale): string
    {
        $results = $this->translateMany([$text], $locale);
        return $results[0] ?? '';
    }

    /**
     * Translates multiple texts into the specified locale.
     *
     * @param array<int, string> $texts
     * @param string $locale
     * @return array<int, string> Translated texts in the same order.
     * @throws TranslateException
     */
    public function translateMany(array $texts, string $locale): array
    {
        if ($texts === []) {
            return [];
        }
        
        $url = sprintf(
            '%s/translate?api-version=3.0&to=%s',
            rtrim($this->endpoint, '/'),
            urlencode($locale)
        );

        // Azure expects: [ { "Text": "Hello" }, ... ]
        $payload = array_map(
            fn($t) => ['Text' => $this->nonTranslatableStrategy->protect($t)],
            $texts
        );

        $body = $this->streamFactory->createStream(
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        $request = $this->requestFactory
            ->createRequest('POST', $url)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Ocp-Apim-Subscription-Key', $this->apiKey)
            ->withHeader('Ocp-Apim-Subscription-Region', $this->region)
            ->withBody($body);

        try {
            $response = $this->client->sendRequest($request);
        } catch (\Throwable $e) {
            throw new TranslateException(
                sprintf('Azure request failed: %s', $e->getMessage()),
                0,
                $e
            );
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        // Azure quota exceeded → HTTP 429
        if ($status === 429) {
            throw new QuotaExceededException('Azure quota exceeded.');
        }

        if ($status < 200 || $status >= 300) {
            throw new TranslateException(
                sprintf('Azure returned HTTP %s: %s', $status, $body)
            );
        }

        try {
            $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw new TranslateException(
                sprintf('Azure returned invalid JSON: %s', $e->getMessage()),
                0,
                $e
            );
        }

        $results = [];

        foreach ($json as $item) {
            $text = $item['translations'][0]['text'] ?? null;

            if (!is_string($text)) {
                throw new TranslateException('Azure response missing translation text.');
            }

            $results[] = $this->nonTranslatableStrategy->unprotect($text);
        }

        return $results;
    }
}