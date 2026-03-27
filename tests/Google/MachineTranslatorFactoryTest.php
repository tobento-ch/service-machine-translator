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
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Psr18Client;
use Tobento\Service\MachineTranslator\Exception\MachineTranslatorCreationException;
use Tobento\Service\MachineTranslator\Google\MachineTranslator;
use Tobento\Service\MachineTranslator\Google\MachineTranslatorFactory;
use Tobento\Service\MachineTranslator\MachineTranslatorFactoryInterface;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;
use Tobento\Service\MachineTranslator\NonTranslatableStrategy;

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

        $translator = $factory->createTranslator('google', [
            'apiKey' => '123',
        ]);

        $this->assertInstanceOf(MachineTranslatorInterface::class, $translator);
        $this->assertInstanceOf(MachineTranslator::class, $translator);
        $this->assertSame('google', $translator->name());
    }

    public function testTranslatorExposesEndpointAndApiKey(): void
    {
        $factory = $this->createFactory([
            'endpoint' => 'https://custom-google-endpoint',
        ]);

        $translator = $factory->createTranslator('google', [
            'apiKey' => 'secret-key',
        ]);

        $this->assertInstanceOf(MachineTranslator::class, $translator);

        $this->assertSame('https://custom-google-endpoint', $translator->endpoint());
        $this->assertSame('secret-key', $translator->apiKey());
    }

    public function testTranslatorUsesFactoryDefaults(): void
    {
        $factory = $this->createFactory();

        $translator = $factory->createTranslator('google', [
            'apiKey' => 'abc123',
        ]);

        $this->assertSame(
            'https://translation.googleapis.com/language/translate/v2',
            $translator->endpoint()
        );

        $this->assertSame('abc123', $translator->apiKey());
    }

    public function testTranslatorName(): void
    {
        $factory = $this->createFactory();

        $translator = $factory->createTranslator('google-test', [
            'apiKey' => 'xyz',
        ]);

        $this->assertSame('google-test', $translator->name());
    }

    public function testMissingApiKeyThrowsException(): void
    {
        $this->expectException(MachineTranslatorCreationException::class);

        $factory = $this->createFactory();

        $factory->createTranslator('google', []);
    }

    public function testInvalidConfigThrowsWrappedException(): void
    {
        $this->expectException(MachineTranslatorCreationException::class);
        $this->expectExceptionMessage('Failed creating Google translator');

        // Force invalid config to break the translator constructor
        $factory = $this->createFactory([
            'endpoint' => null, // invalid type
        ]);

        $factory->createTranslator('google', [
            'apiKey' => '123',
        ]);
    }
    
    public function testTranslatorUsesNullStrategyIfNoneProvided(): void
    {
        $factory = $this->createFactory();

        $translator = $factory->createTranslator('google', [
            'apiKey' => '123',
        ]);

        $this->assertInstanceOf(
            NonTranslatableStrategy\NullStrategy::class,
            $translator->nonTranslatableStrategy()
        );
    }

    public function testTranslatorUsesStrategyFromDefaults(): void
    {
        $strategy = new NonTranslatableStrategy\Placeholder();

        $factory = $this->createFactory([
            'nonTranslatableStrategy' => $strategy,
        ]);

        $translator = $factory->createTranslator('google', [
            'apiKey' => '123',
        ]);

        $this->assertSame($strategy, $translator->nonTranslatableStrategy());
    }

    public function testTranslatorConfigOverridesDefaultStrategy(): void
    {
        $default = new NonTranslatableStrategy\Placeholder();
        $override = new NonTranslatableStrategy\NullStrategy();

        $factory = $this->createFactory([
            'nonTranslatableStrategy' => $default,
        ]);

        $translator = $factory->createTranslator('google', [
            'apiKey' => '123',
            'nonTranslatableStrategy' => $override,
        ]);

        $this->assertSame($override, $translator->nonTranslatableStrategy());
    }
}