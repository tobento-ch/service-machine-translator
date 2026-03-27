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
use Tobento\Service\MachineTranslator\NonTranslatableStrategy\Placeholder;
use Tobento\Service\MachineTranslator\NonTranslatableStrategyInterface;

class PlaceholderTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $strategy = new Placeholder();

        $this->assertInstanceOf(
            NonTranslatableStrategyInterface::class,
            $strategy
        );
    }

    public function testProtectsIndicatorPlaceholders(): void
    {
        $strategy = new Placeholder([':'], []);

        $text = 'Hello :name, your order is ready.';
        $expected = 'Hello <nt>:name</nt>, your order is ready.';

        $this->assertSame($expected, $strategy->protect($text));
    }

    public function testProtectsWrapperPlaceholders(): void
    {
        $strategy = new Placeholder([], [['{', '}']]);

        $text = 'Order ID: {id}';
        $expected = 'Order ID: <nt>{id}</nt>';

        $this->assertSame($expected, $strategy->protect($text));
    }

    public function testProtectsMultiplePatterns(): void
    {
        $strategy = new Placeholder([':'], [['{', '}']]);

        $text = 'Hello :name, your order {id} is ready.';
        $expected = 'Hello <nt>:name</nt>, your order <nt>{id}</nt> is ready.';

        $this->assertSame($expected, $strategy->protect($text));
    }

    public function testCustomMarker(): void
    {
        $strategy = new Placeholder([':'], [], ['[[', ']]']);

        $text = 'Hello :name';
        $expected = 'Hello [[:name]]';

        $this->assertSame($expected, $strategy->protect($text));
    }

    public function testUnprotectRemovesMarkers(): void
    {
        $strategy = new Placeholder([':'], []);

        $text = 'Hallo <nt>:name</nt>';
        $expected = 'Hallo :name';

        $this->assertSame($expected, $strategy->unprotect($text));
    }

    public function testUnprotectWithCustomMarkers(): void
    {
        $strategy = new Placeholder([':'], [], ['[[', ']]']);

        $text = 'Hallo [[:name]]';
        $expected = 'Hallo :name';

        $this->assertSame($expected, $strategy->unprotect($text));
    }

    public function testInvalidMarkerThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Placeholder([':'], [], ['<nt>']); // only one element
    }

    public function testEmptyStringReturnsEmpty(): void
    {
        $strategy = new Placeholder();

        $this->assertSame('', $strategy->protect(''));
        $this->assertSame('', $strategy->unprotect(''));
    }

    public function testNoPatternsReturnsOriginalText(): void
    {
        $strategy = new Placeholder([], []);

        $text = 'Nothing to protect here.';
        $this->assertSame($text, $strategy->protect($text));
    }
}