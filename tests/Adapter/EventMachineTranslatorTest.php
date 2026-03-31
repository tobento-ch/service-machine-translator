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
use Tobento\Service\Event\Events;
use Tobento\Service\MachineTranslator\Adapter\EventMachineTranslator;
use Tobento\Service\MachineTranslator\Event\TranslationSuccess;
use Tobento\Service\MachineTranslator\Event\TranslationFailed;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;
use Tobento\Service\MachineTranslator\Test\GammaTranslator;
use Tobento\Service\MachineTranslator\Test\FailingTranslator;

class EventMachineTranslatorTest extends TestCase
{
    public function testImplementsInterface(): void
    {
        $translator = new EventMachineTranslator(
            translator: new GammaTranslator('t1', 'OK'),
            events: new Events(),
        );

        $this->assertInstanceOf(MachineTranslatorInterface::class, $translator);
    }
    
    public function testName(): void
    {
        $translator = new EventMachineTranslator(
            translator: new GammaTranslator('t1', 'OK'),
            events: new Events(),
        );

        $this->assertSame('t1', $translator->name());
    }

    public function testDispatchesSuccessEvent(): void
    {
        $events = new Events();
        $captured = [];

        // Attach listener directly to Events
        $events->listen(function(TranslationSuccess $event) use (&$captured) {
            $captured[] = $event;
        });

        $translator = new EventMachineTranslator(
            translator: new GammaTranslator('t1', 'OK'),
            events: $events,
        );

        $result = $translator->translate('x', 'en');

        $this->assertSame('OK', $result);
        $this->assertCount(1, $captured);

        $event = $captured[0];

        $this->assertSame(['x'], $event->texts());
        $this->assertSame(['OK'], $event->translated());
        $this->assertSame('en', $event->locale());
        $this->assertSame('t1', $event->translator()->name());
    }

    public function testDispatchesFailedEvent(): void
    {
        $events = new Events();
        $captured = [];

        // Listen for the failed event
        $events->listen(function(TranslationFailed $event) use (&$captured) {
            $captured[] = $event;
        });

        $translator = new EventMachineTranslator(
            translator: new FailingTranslator('fail'),
            events: $events,
        );

        $this->expectException(\Exception::class);

        try {
            $translator->translate('x', 'en');
        } finally {
            // Event was dispatched
            $this->assertCount(1, $captured);

            $event = $captured[0];
            $this->assertInstanceOf(TranslationFailed::class, $event);

            // Check event data
            $this->assertSame(['x'], $event->texts());
            $this->assertSame('en', $event->locale());
            $this->assertSame('fail', $event->translator()->name());
            $this->assertInstanceOf(\Exception::class, $event->exception());
        }
    }
    
    public function testDispatchesSuccessEventForTranslateMany(): void
    {
        $events = new Events();
        $captured = [];

        $events->listen(function(TranslationSuccess $event) use (&$captured) {
            $captured[] = $event;
        });

        $translator = new EventMachineTranslator(
            translator: new GammaTranslator('t1', 'OK'),
            events: $events,
        );

        $result = $translator->translateMany(['a', 'b'], 'en');

        $this->assertSame(['OK', 'OK'], $result);
        $this->assertCount(1, $captured);

        $event = $captured[0];

        // Event data checks
        $this->assertSame(['a', 'b'], $event->texts());
        $this->assertSame(['OK', 'OK'], $event->translated());
        $this->assertSame('en', $event->locale());
        $this->assertSame('t1', $event->translator()->name());
    }
    
    public function testDispatchesFailedEventForTranslateMany(): void
    {
        $events = new Events();
        $captured = [];

        $events->listen(function(TranslationFailed $event) use (&$captured) {
            $captured[] = $event;
        });

        $translator = new EventMachineTranslator(
            translator: new FailingTranslator('fail'),
            events: $events,
        );

        $this->expectException(\Exception::class);

        try {
            $translator->translateMany(['a', 'b'], 'en');
        } finally {
            $this->assertCount(1, $captured);

            $event = $captured[0];

            // Event data checks
            $this->assertSame(['a', 'b'], $event->texts());
            $this->assertSame('en', $event->locale());
            $this->assertSame('fail', $event->translator()->name());
            $this->assertInstanceOf(\Exception::class, $event->exception());
        }
    }
}