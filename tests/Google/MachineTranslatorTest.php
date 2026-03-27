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

namespace Tobento\Service\MachineTranslator\Test\Google;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpClient\Psr18Client;
use Tobento\Service\MachineTranslator\Exception\QuotaExceededException;
use Tobento\Service\MachineTranslator\Exception\TranslateException;
use Tobento\Service\MachineTranslator\Google\MachineTranslator;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;
use Tobento\Service\MachineTranslator\NonTranslatableStrategy\NullStrategy;
use Tobento\Service\MachineTranslator\NonTranslatableStrategyInterface;

class MachineTranslatorTest extends TestCase
{
    protected function createTranslatorWithResponse(
        Response $response,
        null|NonTranslatableStrategyInterface $strategy = null,
    ): MachineTranslator&MachineTranslatorInterface {
        // Tiny fake PSR-18 client
        $client = new class($response) implements ClientInterface {
            public function __construct(private \Psr\Http\Message\ResponseInterface $response) {}
            public function sendRequest(RequestInterface $request): \Psr\Http\Message\ResponseInterface
            {
                return $this->response;
            }
        };

        $factory = new Psr17Factory();

        return new MachineTranslator(
            client: $client,
            requestFactory: $factory,
            streamFactory: $factory,
            endpoint: 'https://translation.googleapis.com/language/translate/v2',
            apiKey: 'secret-key',
            name: 'google-test',
            nonTranslatableStrategy: $strategy ?: new NullStrategy(),
        );
    }

    public function testConstructorRejectsEmptyValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new MachineTranslator(
            client: new Psr18Client(),
            requestFactory: new Psr17Factory(),
            streamFactory: new Psr17Factory(),
            endpoint: '',
            apiKey: 'x',
        );
    }

    public function testCustomGetterMethods(): void
    {
        $translator = $this->createTranslatorWithResponse(
            new Response(200, [], '{"data":{"translations":[]}}')
        );

        $this->assertSame('google-test', $translator->name());
        $this->assertSame(
            'https://translation.googleapis.com/language/translate/v2',
            $translator->endpoint()
        );
        $this->assertSame('secret-key', $translator->apiKey());
        
        $this->assertInstanceOf(
            NonTranslatableStrategyInterface::class,
            $translator->nonTranslatableStrategy()
        );
    }

    public function testTranslateManyReturnsExpectedTexts(): void
    {
        $json = json_encode([
            'data' => [
                'translations' => [
                    ['translatedText' => 'Hallo'],
                    ['translatedText' => 'Welt'],
                ]
            ]
        ], JSON_UNESCAPED_UNICODE);

        $translator = $this->createTranslatorWithResponse(
            new Response(200, ['Content-Type' => 'application/json'], $json)
        );

        $result = $translator->translateMany(['Hello', 'World'], 'de');

        $this->assertSame(['Hallo', 'Welt'], $result);
    }

    public function testTranslateUsesTranslateMany(): void
    {
        $json = json_encode([
            'data' => [
                'translations' => [
                    ['translatedText' => 'Hallo'],
                ]
            ]
        ]);

        $translator = $this->createTranslatorWithResponse(
            new Response(200, [], $json)
        );

        $this->assertSame('Hallo', $translator->translate('Hello', 'de'));
    }

    public function testEmptyArrayReturnsEmptyArray(): void
    {
        $translator = $this->createTranslatorWithResponse(
            new Response(200, [], '{"data":{"translations":[]}}')
        );

        $this->assertSame([], $translator->translateMany([], 'de'));
    }

    public function testQuotaExceededThrowsException(): void
    {
        $this->expectException(QuotaExceededException::class);

        $json = json_encode([
            'error' => [
                'errors' => [
                    ['reason' => 'quotaExceeded']
                ]
            ]
        ]);

        $translator = $this->createTranslatorWithResponse(
            new Response(200, [], $json)
        );

        $translator->translate('Hello', 'de');
    }

    public function testNon200ResponseThrowsTranslateException(): void
    {
        $this->expectException(TranslateException::class);

        $translator = $this->createTranslatorWithResponse(
            new Response(500, [], 'Server error')
        );

        $translator->translate('Hello', 'de');
    }

    public function testInvalidJsonThrowsTranslateException(): void
    {
        $this->expectException(TranslateException::class);

        $translator = $this->createTranslatorWithResponse(
            new Response(200, [], '{invalid-json')
        );

        $translator->translate('Hello', 'de');
    }

    public function testMissingTranslationsArrayThrowsException(): void
    {
        $this->expectException(TranslateException::class);

        $translator = $this->createTranslatorWithResponse(
            new Response(200, [], '{"foo":"bar"}')
        );

        $translator->translate('Hello', 'de');
    }

    public function testMissingTranslationTextThrowsException(): void
    {
        $this->expectException(TranslateException::class);

        $json = json_encode([
            'data' => [
                'translations' => [
                    ['no-text' => 'oops']
                ]
            ]
        ]);

        $translator = $this->createTranslatorWithResponse(
            new Response(200, [], $json)
        );

        $translator->translate('Hello', 'de');
    }
    
    public function testTranslatorUsesPassedStrategy(): void
    {
        // Google returns: {"data":{"translations":[{"translatedText":"..."}]}}
        $json = json_encode([
            'data' => [
                'translations' => [
                    ['translatedText' => '[[PROTECT]]Hallo[[/PROTECT]]']
                ]
            ]
        ], JSON_UNESCAPED_UNICODE);

        $strategy = new \Tobento\Service\MachineTranslator\Test\FakeStrategy();

        $translator = $this->createTranslatorWithResponse(
            new Response(200, ['Content-Type' => 'application/json'], $json),
            $strategy
        );

        $result = $translator->translate('Hello', 'de');

        // Strategy must have been used
        $this->assertTrue($strategy->protectCalled);
        $this->assertTrue($strategy->unprotectCalled);

        // Final result must be unprotected
        $this->assertSame('Hallo', $result);
    }
}