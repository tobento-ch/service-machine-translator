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
use Tobento\Service\MachineTranslator\MachineTranslators;
use Tobento\Service\MachineTranslator\MachineTranslatorsInterface;
use Tobento\Service\MachineTranslator\Exception\MachineTranslatorNotFoundException;

class MachineTranslatorsTest extends TestCase
{
    public function testImplementsInterfaces(): void
    {
        $translators = new MachineTranslators(
            new GammaTranslator(name: 'gamma')
        );

        $this->assertInstanceOf(MachineTranslatorsInterface::class, $translators);
        $this->assertInstanceOf(MachineTranslatorInterface::class, $translators);
    }

    public function testGetReturnsTranslator(): void
    {
        $translator = new GammaTranslator(name: 'alpha');

        $translators = new MachineTranslators($translator);

        $this->assertSame($translator, $translators->get('alpha'));
    }

    public function testGetThrowsExceptionIfMissing(): void
    {
        $this->expectException(MachineTranslatorNotFoundException::class);

        $translators = new MachineTranslators();

        $translators->get('missing');
    }

    public function testHas(): void
    {
        $translators = new MachineTranslators(
            new GammaTranslator(name: 'alpha')
        );

        $this->assertTrue($translators->has('alpha'));
        $this->assertFalse($translators->has('beta'));
    }

    public function testNames(): void
    {
        $translators = new MachineTranslators(
            new GammaTranslator(name: 'alpha'),
            new GammaTranslator(name: 'beta')
        );

        $this->assertSame(['alpha', 'beta'], $translators->names());
    }

    public function testTranslateUsesFirstTranslator(): void
    {
        $translators = new MachineTranslators(
            new GammaTranslator(name: 'first', value: 'first-ok'),
            new GammaTranslator(name: 'second', value: 'second-ok'),
        );

        $this->assertSame('first-ok', $translators->translate('x', 'en'));
        $this->assertSame(['first-ok'], $translators->translateMany(['x'], 'en'));
        $this->assertSame('first', $translators->name());
    }

    public function testTranslateThrowsIfNoTranslatorsRegistered(): void
    {
        $this->expectException(MachineTranslatorNotFoundException::class);

        $translators = new MachineTranslators();

        $translators->translate('x', 'en');
    }
}