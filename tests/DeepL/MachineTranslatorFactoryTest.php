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

namespace Tobento\Service\MachineTranslator\Test\DeepL;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Psr18Client;
use Tobento\Service\MachineTranslator\DeepL\MachineTranslator;
use Tobento\Service\MachineTranslator\DeepL\MachineTranslatorFactory;
use Tobento\Service\MachineTranslator\Exception\MachineTranslatorCreationException;
use Tobento\Service\MachineTranslator\MachineTranslatorFactoryInterface;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

class MachineTranslatorFactoryTest extends TestCase
{
    protected function createFactory(array $defaults = []): MachineTranslatorFactoryInterface
    {
        return new MachineTranslatorFactory(
            client: new Psr18Client(),
            requestFactory: new Psr17Factory(),
            streamFactory: new Psr17Factory(),
            defaults: $defaults,
        );
    }

    public function testCreatesTranslatorSuccessfully(): void
    {
        $factory = $this->createFactory();

        $translator = $factory->createTranslator('deepl', [
            'apiKey' => '123',
        ]);

        $this->assertInstanceOf(MachineTranslatorInterface::class, $translator);
        $this->assertInstanceOf(MachineTranslator::class, $translator);
        $this->assertSame('deepl', $translator->name());
    }

    public function testTranslatorExposesEndpointAndApiKey(): void
    {
        $factory = $this->createFactory([
            'endpoint' => 'https://custom-endpoint',
        ]);

        $translator = $factory->createTranslator('deepl', [
            'apiKey' => 'secret-key',
        ]);

        $this->assertInstanceOf(MachineTranslator::class, $translator);

        $this->assertSame('https://custom-endpoint', $translator->endpoint());
        $this->assertSame('secret-key', $translator->apiKey());
    }

    public function testTranslatorUsesFactoryDefaults(): void
    {
        $factory = $this->createFactory();

        $translator = $factory->createTranslator('deepl', [
            'apiKey' => 'abc123',
        ]);

        $this->assertSame('https://api.deepl.com/v2/translate', $translator->endpoint());
        $this->assertSame('abc123', $translator->apiKey());
    }

    public function testTranslatorName(): void
    {
        $factory = $this->createFactory();

        $translator = $factory->createTranslator('deepl-test', [
            'apiKey' => 'xyz',
        ]);

        $this->assertSame('deepl-test', $translator->name());
    }

    public function testMissingApiKeyThrowsException(): void
    {
        $this->expectException(MachineTranslatorCreationException::class);

        $factory = $this->createFactory();

        $factory->createTranslator('deepl', []);
    }

    public function testInvalidConfigThrowsWrappedException(): void
    {
        $this->expectException(MachineTranslatorCreationException::class);
        $this->expectExceptionMessage('Failed creating DeepL translator');

        // Force invalid config to break the translator constructor
        $factory = $this->createFactory([
            'endpoint' => null, // invalid type
        ]);

        $factory->createTranslator('deepl', [
            'apiKey' => '123',
        ]);
    }
}