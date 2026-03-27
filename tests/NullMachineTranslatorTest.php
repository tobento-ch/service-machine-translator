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
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;
use Tobento\Service\MachineTranslator\NullMachineTranslator;

class NullMachineTranslatorTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $translator = new NullMachineTranslator();

        $this->assertInstanceOf(MachineTranslatorInterface::class, $translator);
    }

    public function testName(): void
    {
        $translator = new NullMachineTranslator(name: 'nuller');

        $this->assertSame('nuller', $translator->name());
    }

    public function testTranslateReturnsInputUnchanged(): void
    {
        $translator = new NullMachineTranslator();

        $this->assertSame('hello', $translator->translate('hello', 'en'));
        $this->assertSame('Bonjour', $translator->translate('Bonjour', 'fr'));
        $this->assertSame('', $translator->translate('', 'de'));
    }

    public function testTranslateManyReturnsInputUnchanged(): void
    {
        $translator = new NullMachineTranslator();

        $texts = ['a', 'b', 'c'];

        $this->assertSame($texts, $translator->translateMany($texts, 'en'));
    }

    public function testTranslateManyEmptyArray(): void
    {
        $translator = new NullMachineTranslator();

        $this->assertSame([], $translator->translateMany([], 'en'));
    }
}