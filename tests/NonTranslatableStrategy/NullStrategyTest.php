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
use Tobento\Service\MachineTranslator\NonTranslatableStrategy\NullStrategy;
use Tobento\Service\MachineTranslator\NonTranslatableStrategyInterface;

class NullStrategyTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $strategy = new NullStrategy();

        $this->assertInstanceOf(
            NonTranslatableStrategyInterface::class,
            $strategy
        );
    }
    
    public function testProtectReturnsTextUnchanged(): void
    {
        $strategy = new NullStrategy();

        $text = 'Hello :name, your order {id} is ready.';
        $this->assertSame($text, $strategy->protect($text));
    }

    public function testUnprotectReturnsTextUnchanged(): void
    {
        $strategy = new NullStrategy();

        $text = 'Hallo :name, Ihre Bestellung {id} ist bereit.';
        $this->assertSame($text, $strategy->unprotect($text));
    }

    public function testEmptyStringRemainsUnchanged(): void
    {
        $strategy = new NullStrategy();

        $this->assertSame('', $strategy->protect(''));
        $this->assertSame('', $strategy->unprotect(''));
    }

    public function testTextWithSpecialCharactersRemainsUnchanged(): void
    {
        $strategy = new NullStrategy();

        $text = 'Symbols: <nt> { } % $ [[test]]';
        $this->assertSame($text, $strategy->protect($text));
        $this->assertSame($text, $strategy->unprotect($text));
    }
}