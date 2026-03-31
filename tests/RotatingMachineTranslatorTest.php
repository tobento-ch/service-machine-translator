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
use Tobento\Service\MachineTranslator\Exception\MachineTranslatorNotFoundException;
use Tobento\Service\MachineTranslator\Exception\TranslateException;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;
use Tobento\Service\MachineTranslator\MachineTranslators;
use Tobento\Service\MachineTranslator\RotatingMachineTranslator;

class RotatingMachineTranslatorTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $translators = new MachineTranslators(
            new GammaTranslator('a'),
            new GammaTranslator('b'),
        );

        $rotator = new RotatingMachineTranslator(
            translators: $translators,
            name: 'rotator'
        );

        $this->assertInstanceOf(MachineTranslatorInterface::class, $rotator);
    }

    public function testName(): void
    {
        $rotator = new RotatingMachineTranslator(
            translators: new MachineTranslators(),
            name: 'rotator'
        );

        $this->assertSame('rotator', $rotator->name());
    }

    public function testFailoverUsesFirstWorkingTranslator(): void
    {
        $translators = new MachineTranslators(
            new FailingTranslator('fail'),
            new GammaTranslator('ok', 'success'),
        );

        $rotator = new RotatingMachineTranslator(
            translators: $translators,
            name: 'rotator',
            strategy: RotatingMachineTranslator::STRATEGY_FAILOVER
        );

        $this->assertSame('success', $rotator->translate('x', 'en'));
    }

    public function testFailoverAllFailThrows(): void
    {
        $this->expectException(TranslateException::class);

        $translators = new MachineTranslators(
            new FailingTranslator('fail1'),
            new FailingTranslator('fail2'),
        );

        $rotator = new RotatingMachineTranslator(
            translators: $translators,
            name: 'rotator'
        );

        $rotator->translate('x', 'en');
    }

    public function testFallbackUsedIfAllFail(): void
    {
        $translators = new MachineTranslators(
            new FailingTranslator('fail1'),
            new FailingTranslator('fail2'),
            new GammaTranslator('fallback', 'fallback-ok'),
        );

        $rotator = new RotatingMachineTranslator(
            translators: $translators,
            name: 'rotator',
            fallback: 'fallback'
        );

        $this->assertSame('fallback-ok', $rotator->translate('x', 'en'));
    }

    public function testFallbackMissingThrows(): void
    {
        $this->expectException(MachineTranslatorNotFoundException::class);

        $translators = new MachineTranslators(
            new FailingTranslator('fail1'),
            new FailingTranslator('fail2'),
        );

        $rotator = new RotatingMachineTranslator(
            translators: $translators,
            name: 'rotator',
            fallback: 'missing'
        );

        $rotator->translate('x', 'en');
    }

    public function testRoundRobinCyclesThroughTranslators(): void
    {
        $translators = new MachineTranslators(
            new GammaTranslator('t1', 'one'),
            new GammaTranslator('t2', 'two'),
        );

        $rotator = new RotatingMachineTranslator(
            translators: $translators,
            name: 'rotator',
            strategy: RotatingMachineTranslator::STRATEGY_ROUND_ROBIN
        );

        $this->assertSame('one', $rotator->translate('x', 'en'));
        $this->assertSame('two', $rotator->translate('x', 'en'));
        $this->assertSame('one', $rotator->translate('x', 'en'));
    }

    public function testOnlyFilter(): void
    {
        $translators = new MachineTranslators(
            new GammaTranslator('a', 'A'),
            new GammaTranslator('b', 'B'),
            new GammaTranslator('c', 'C'),
        );

        $rotator = new RotatingMachineTranslator(
            translators: $translators,
            name: 'rotator',
            only: ['b']
        );

        $this->assertSame('B', $rotator->translate('x', 'en'));
    }

    public function testExceptFilter(): void
    {
        $translators = new MachineTranslators(
            new GammaTranslator('a', 'A'),
            new GammaTranslator('b', 'B'),
            new GammaTranslator('c', 'C'),
        );

        $rotator = new RotatingMachineTranslator(
            translators: $translators,
            name: 'rotator',
            except: ['a', 'c']
        );

        $this->assertSame('B', $rotator->translate('x', 'en'));
    }
}