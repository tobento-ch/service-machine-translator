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

namespace Tobento\Service\MachineTranslator\NonTranslatableStrategy\Test;

use PHPUnit\Framework\TestCase;
use Tobento\Service\MachineTranslator\NonTranslatableStrategy\Composite;
use Tobento\Service\MachineTranslator\NonTranslatableStrategy\Placeholder;
use Tobento\Service\MachineTranslator\NonTranslatableStrategy\NullStrategy;
use Tobento\Service\MachineTranslator\NonTranslatableStrategyInterface;

class CompositeTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $strategy = new Composite();

        $this->assertInstanceOf(
            NonTranslatableStrategyInterface::class,
            $strategy
        );
    }

    public function testProtectAppliesStrategiesInOrder(): void
    {
        $s1 = new Placeholder([':'], [], ['<a>', '</a>']);
        $s2 = new Placeholder([], [['{', '}']], ['<b>', '</b>']);

        $composite = new Composite($s1, $s2);

        $text = 'Hello :name, order {id}';
        $expected = 'Hello <a>:name</a>, order <b>{id}</b>';

        $this->assertSame($expected, $composite->protect($text));
    }

    public function testUnprotectAppliesStrategiesInReverseOrder(): void
    {
        $s1 = new Placeholder([':'], [], ['<a>', '</a>']);
        $s2 = new Placeholder([], [['{', '}']], ['<b>', '</b>']);

        $composite = new Composite($s1, $s2);

        $text = 'Hallo <a>:name</a> und <b>{id}</b>';
        $expected = 'Hallo :name und {id}';

        $this->assertSame($expected, $composite->unprotect($text));
    }

    public function testAddStrategy(): void
    {
        $composite = new Composite();

        $s1 = new Placeholder([':'], []);
        $s2 = new Placeholder([], [['{', '}']]);

        $composite->addStrategy($s1)->addStrategy($s2);

        $text = 'Hello :name, order {id}';
        $expected = 'Hello <nt>:name</nt>, order <nt>{id}</nt>';

        $this->assertSame($expected, $composite->protect($text));
    }

    public function testEmptyCompositeReturnsOriginalText(): void
    {
        $composite = new Composite();

        $text = 'Nothing changes here.';
        $this->assertSame($text, $composite->protect($text));
        $this->assertSame($text, $composite->unprotect($text));
    }

    public function testCompositeWithNullStrategyDoesNothing(): void
    {
        $composite = new Composite(new NullStrategy());

        $text = 'Hello :name';
        $this->assertSame($text, $composite->protect($text));
        $this->assertSame($text, $composite->unprotect($text));
    }

    public function testNestedPlaceholderStrategies(): void
    {
        $s1 = new Placeholder([':'], [], ['<a>', '</a>']);
        $s2 = new Placeholder(['%'], [], ['<b>', '</b>']);

        $composite = new Composite($s1, $s2);

        $text = 'Hello :name, discount %value';
        $expected = 'Hello <a>:name</a>, discount <b>%value</b>';

        $this->assertSame($expected, $composite->protect($text));
    }
}