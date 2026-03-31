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

namespace Tobento\Service\MachineTranslator\Test\Adapter;

use PHPUnit\Framework\TestCase;
use Tobento\Service\MachineTranslator\Event\TranslationSuccess;
use Tobento\Service\MachineTranslator\Test\GammaTranslator;

class TranslationSuccessTest extends TestCase
{
    public function testEvent()
    {
        $translator = new GammaTranslator('t1', 'OK');

        $event = new TranslationSuccess(
            texts: ['a'],
            translated: ['b'],
            locale: 'en',
            translator: $translator,
        );

        $this->assertSame(['a'], $event->texts());
        $this->assertSame(['b'], $event->translated());
        $this->assertSame('en', $event->locale());
        $this->assertSame($translator, $event->translator());
    }
}