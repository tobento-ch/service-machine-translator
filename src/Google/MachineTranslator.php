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

namespace Tobento\Service\MachineTranslator\Google;

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
     * @param string $apiKey
     * @param string $name
     * @param NonTranslatableStrategyInterface $nonTranslatableStrategy
     */
    public function __construct(
        protected ClientInterface $client,
        protected RequestFactoryInterface $requestFactory,
        protected StreamFactoryInterface $streamFactory,
        protected string $endpoint,
        #[\SensitiveParameter] protected string $apiKey,
        protected string $name = 'google',
        protected NonTranslatableStrategyInterface $nonTranslatableStrategy = new NullStrategy(),
    ) {
        if ($this->endpoint === '' || $this->apiKey === '') {
            throw new \InvalidArgumentException(
                'Google translator requires non-empty "endpoint" and "apiKey" values.'
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

        // Google uses: POST https://translation.googleapis.com/language/translate/v2?key=API_KEY
        $url = $this->endpoint . '?key=' . urlencode($this->apiKey);

        $protectedTexts = array_map(
            fn($t) => $this->nonTranslatableStrategy->protect($t),
            array_values($texts)
        );
        
        // Google expects JSON:
        // {
        //   "q": ["Hello", "World"],
        //   "target": "de"
        // }
        $payload = [
            'q' => $protectedTexts,
            'target' => strtolower($locale),
            'format' => 'text',
        ];

        $body = $this->streamFactory->createStream(json_encode($payload, JSON_UNESCAPED_UNICODE));

        $request = $this->requestFactory
            ->createRequest('POST', $url)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        try {
            $response = $this->client->sendRequest($request);
        } catch (\Throwable $e) {
            throw new TranslateException(
                message: sprintf('Google request failed: %s', $e->getMessage()),
                code: 0,
                previous: $e,
            );
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            throw new TranslateException(sprintf('Google returned HTTP %s: %s', $status, $body));
        }
        
        try {
            $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw new TranslateException(
                message: sprintf('Google returned invalid JSON: %s', $e->getMessage()),
                code: 0,
                previous: $e,
            );
        }

        // Detect Google quota exceeded errors
        if (isset($json['error']['errors'][0]['reason'])) {
            $reason = $json['error']['errors'][0]['reason'];

            if (in_array($reason, ['quotaExceeded', 'dailyLimitExceeded', 'userRateLimitExceeded'], true)) {
                throw new QuotaExceededException(sprintf('Google quota exceeded: %s', $reason));
            }
        }
        
        if (!is_array($json) || !isset($json['data']['translations'])) {
            throw new TranslateException('Invalid Google response format.');
        }

        $results = [];

        foreach ($json['data']['translations'] as $item) {
            $text = $item['translatedText'] ?? null;

            if (!is_string($text)) {
                throw new TranslateException('Google response missing translation text.');
            }

            $results[] = $this->nonTranslatableStrategy->unprotect($text);
        }

        return $results;
    }
}