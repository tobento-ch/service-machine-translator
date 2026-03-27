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

namespace Tobento\Service\MachineTranslator\Test;

use PHPUnit\Framework\TestCase;
use Tobento\Service\Container\Container;
use Tobento\Service\MachineTranslator\Exception\MachineTranslatorNotFoundException;
use Tobento\Service\MachineTranslator\LazyMachineTranslators;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;
use Tobento\Service\MachineTranslator\MachineTranslatorsInterface;
use Tobento\Service\MachineTranslator\MachineTranslatorFactoryInterface;

class LazyMachineTranslatorsTest extends TestCase
{
    public function testImplementsInterfaces(): void
    {
        $container = new Container();

        $translators = new LazyMachineTranslators(
            container: $container,
            translators: [
                'gamma' => new GammaTranslator(name: 'gamma'),
            ]
        );

        $this->assertInstanceOf(MachineTranslatorsInterface::class, $translators);

        $this->assertInstanceOf(MachineTranslatorInterface::class, $translators);
    }

    public function testDirectInstance(): void
    {
        $container = new Container();

        $translator = new GammaTranslator(name: 'direct', value: 'direct-ok');

        $translators = new LazyMachineTranslators(
            container: $container,
            translators: [
                'direct' => $translator,
            ]
        );

        $this->assertSame($translator, $translators->get('direct'));
    }

    public function testCallableDefinition(): void
    {
        $container = new Container();

        $container->set(GammaTranslatorFactory::class, new GammaTranslatorFactory());

        $translators = new LazyMachineTranslators(
            container: $container,
            translators: [
                'callable' => function (string $name, MachineTranslatorsInterface $translators, GammaTranslatorFactory $factory) {
                    return $factory->createTranslator($name, ['value' => 'callable-ok']);
                },
            ]
        );

        $translator = $translators->get('callable');

        $this->assertInstanceOf(MachineTranslatorInterface::class, $translator);
        $this->assertSame('callable', $translator->name());
        $this->assertSame('callable-ok', $translator->translate('x', 'en'));
    }

    public function testFactoryArrayDefinition(): void
    {
        $container = new Container();

        $container->set(GammaTranslatorFactory::class, new GammaTranslatorFactory());

        $translators = new LazyMachineTranslators(
            container: $container,
            translators: [
                'gamma' => [
                    'factory' => GammaTranslatorFactory::class,
                    'config' => ['value' => 'factory-ok'],
                ],
            ]
        );

        $translator = $translators->get('gamma');

        $this->assertSame('gamma', $translator->name());
        $this->assertSame('factory-ok', $translator->translate('x', 'en'));
    }

    public function testMissingTranslatorThrowsException(): void
    {
        $this->expectException(MachineTranslatorNotFoundException::class);

        $translators = new LazyMachineTranslators(
            container: new Container(),
            translators: []
        );

        $translators->get('missing');
    }

    public function testMissingFactoryThrowsException(): void
    {
        $this->expectException(MachineTranslatorNotFoundException::class);

        $translators = new LazyMachineTranslators(
            container: new Container(),
            translators: [
                'invalid' => ['config' => []],
            ]
        );

        $translators->get('invalid');
    }

    public function testInvalidFactoryThrowsException(): void
    {
        $this->expectException(\Tobento\Service\Autowire\AutowireException::class);

        $container = new Container();
        $container->set('invalid', new \stdClass());

        $translators = new LazyMachineTranslators(
            container: $container,
            translators: [
                'invalid' => [
                    'factory' => 'invalid',
                ],
            ]
        );

        $translators->get('invalid');
    }

    public function testTranslateUsesFirstTranslator(): void
    {
        $container = new Container();

        $translators = new LazyMachineTranslators(
            container: $container,
            translators: [
                'first' => new GammaTranslator(name: 'first', value: 'first-ok'),
                'second' => new GammaTranslator(name: 'second', value: 'second-ok'),
            ]
        );

        $this->assertSame('first-ok', $translators->translate('x', 'en'));
        $this->assertSame(['first-ok'], $translators->translateMany(['x'], 'en'));
        $this->assertSame('first', $translators->name());
    }

    public function testTranslatorIsCreatedOnlyOnce(): void
    {
        $container = new Container();

        $counter = 0;

        $translators = new LazyMachineTranslators(
            container: $container,
            translators: [
                'once' => function () use (&$counter) {
                    $counter++;
                    return new GammaTranslator(name: 'once', value: 'ok');
                },
            ]
        );

        $translators->get('once');
        $translators->get('once');

        $this->assertSame(1, $counter);
    }
}