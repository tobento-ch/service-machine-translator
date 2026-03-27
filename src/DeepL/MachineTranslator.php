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

namespace Tobento\Service\MachineTranslator\DeepL;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Tobento\Service\MachineTranslator\Exception\QuotaExceededException;
use Tobento\Service\MachineTranslator\Exception\TranslateException;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

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
     */
    public function __construct(
        protected ClientInterface $client,
        protected RequestFactoryInterface $requestFactory,
        protected StreamFactoryInterface $streamFactory,
        protected string $endpoint,
        #[\SensitiveParameter] protected string $apiKey,
        protected string $name = 'deepl',
    ) {
        if ($this->endpoint === '' || $this->apiKey === '') {
            throw new \InvalidArgumentException(
                'DeepL translator requires non-empty "endpoint" and "apiKey" values.'
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

        // DeepL uses form-encoded POST:
        // text=Hello&text=World&target_lang=DE
        $form = [
            'target_lang' => strtoupper($locale),
        ];

        foreach ($texts as $t) {
            $form['text'][] = $t;
        }

        $body = http_build_query($form, '', '&', PHP_QUERY_RFC3986);
        $stream = $this->streamFactory->createStream($body);

        $request = $this->requestFactory
            ->createRequest('POST', $this->endpoint)
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Authorization', 'DeepL-Auth-Key ' . $this->apiKey)
            ->withBody($stream);

        try {
            $response = $this->client->sendRequest($request);
        } catch (\Throwable $e) {
            throw new TranslateException(
                sprintf('DeepL request failed: %s', $e->getMessage()),
                0,
                $e
            );
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();

        // DeepL quota exceeded → HTTP 456
        if ($status === 456) {
            throw new QuotaExceededException('DeepL quota exceeded.');
        }

        if ($status < 200 || $status >= 300) {
            throw new TranslateException(
                sprintf('DeepL returned HTTP %s: %s', $status, $body)
            );
        }

        try {
            $json = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw new TranslateException(
                sprintf('DeepL returned invalid JSON: %s', $e->getMessage()),
                0,
                $e
            );
        }

        if (!isset($json['translations']) || !is_array($json['translations'])) {
            throw new TranslateException('Invalid DeepL response format.');
        }

        $results = [];

        foreach ($json['translations'] as $item) {
            $text = $item['text'] ?? null;

            if (!is_string($text)) {
                throw new TranslateException('DeepL response missing translation text.');
            }

            $results[] = $text;
        }

        return $results;
    }
}