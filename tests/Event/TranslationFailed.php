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
use Tobento\Service\MachineTranslator\Event\TranslationFailed;
use Tobento\Service\MachineTranslator\Test\GammaTranslator;
use Exception;

class TranslationFailedTest extends TestCase
{
    public function testEvent()
    {
        $translator = new GammaTranslator('t1', 'OK');
        $exception = new Exception('fail');

        $event = new TranslationFailed(
            texts: ['a'],
            locale: 'en',
            exception: $exception,
            translator: $translator,
        );

        $this->assertSame(['a'], $event->texts());
        $this->assertSame('en', $event->locale());
        $this->assertSame($exception, $event->exception());
        $this->assertSame($translator, $event->translator());
    }
}