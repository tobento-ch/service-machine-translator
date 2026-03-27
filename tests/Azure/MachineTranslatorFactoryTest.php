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

namespace Tobento\Service\MachineTranslator\Test\Azure;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Psr18Client;
use Tobento\Service\MachineTranslator\Azure\MachineTranslator;
use Tobento\Service\MachineTranslator\Azure\MachineTranslatorFactory;
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

        $translator = $factory->createTranslator('azure', [
            'apiKey' => '123',
        ]);

        $this->assertInstanceOf(MachineTranslatorInterface::class, $translator);
        $this->assertInstanceOf(MachineTranslator::class, $translator);
        $this->assertSame('azure', $translator->name());
    }

    public function testTranslatorExposesEndpointRegionAndApiKey(): void
    {
        $factory = $this->createFactory([
            'endpoint' => 'https://custom-endpoint',
            'region'   => 'eastus',
        ]);

        $translator = $factory->createTranslator('azure', [
            'apiKey' => 'secret-key',
        ]);

        $this->assertInstanceOf(MachineTranslator::class, $translator);

        $this->assertSame('https://custom-endpoint', $translator->endpoint());
        $this->assertSame('eastus', $translator->region());
        $this->assertSame('secret-key', $translator->apiKey());
    }

    public function testTranslatorUsesFactoryDefaults(): void
    {
        $factory = $this->createFactory();

        $translator = $factory->createTranslator('azure', [
            'apiKey' => 'abc123',
        ]);

        $this->assertSame('https://api.cognitive.microsofttranslator.com', $translator->endpoint());
        $this->assertSame('westeurope', $translator->region());
        $this->assertSame('abc123', $translator->apiKey());
    }

    public function testTranslatorName(): void
    {
        $factory = $this->createFactory();

        $translator = $factory->createTranslator('azure-test', [
            'apiKey' => 'xyz',
        ]);

        $this->assertSame('azure-test', $translator->name());
    }

    public function testMissingApiKeyThrowsException(): void
    {
        $this->expectException(MachineTranslatorCreationException::class);

        $factory = $this->createFactory();

        $factory->createTranslator('azure', []);
    }
}