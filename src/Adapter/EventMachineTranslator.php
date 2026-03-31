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

namespace Tobento\Service\MachineTranslator\Adapter;

use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Tobento\Service\MachineTranslator\Event;
use Tobento\Service\MachineTranslator\Exception\TranslateException;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

class EventMachineTranslator implements MachineTranslatorInterface
{
    /**
     * Create a new instance.
     *
     * @param MachineTranslatorInterface $translator
     * @param null|EventDispatcherInterface $events
     */
    final public function __construct(
        protected MachineTranslatorInterface $translator,
        protected null|EventDispatcherInterface $events,
    ) {}

    /**
     * Returns the translator.
     *
     * @return MachineTranslatorInterface
     */
    public function translator(): MachineTranslatorInterface
    {
        return $this->translator;
    }
    
    /**
     * Returns the translator name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->translator()->name();
    }

    /**
     * Translates the given text into the specified locale.
     *
     * @param string $text The text to translate.
     * @param string $locale The target locale (e.g. "de", "fr", "es").
     * @return string The translated text.
     * @throws TranslateException If translation fails.
     */
    public function translate(string $text, string $locale): string
    {
        try {
            $translated = $this->translator()->translate(text: $text, locale: $locale);
            
            $this->events?->dispatch(new Event\TranslationSuccess(
                translator: $this->translator(),
                texts: [$text],
                locale: $locale,
                translated: [$translated],
            ));
            
            return $translated;
        } catch (Throwable $e) {
            $this->events?->dispatch(new Event\TranslationFailed(
                translator: $this->translator(),
                texts: [$text],
                locale: $locale,
                exception: $e,
            ));
            
            throw $e;
        }
    }
    
    /**
     * Translates multiple texts into the specified locale.
     *
     * @param array<int, string> $texts
     * @param string $locale
     * @return array<int, string> Translated texts in the same order.
     * @throws TranslateException
     */
    public function translateMany(array $texts, string $locale): array
    {
        try {
            $translated = $this->translator()->translateMany(texts: $texts, locale: $locale);
            
            $this->events?->dispatch(new Event\TranslationSuccess(
                translator: $this->translator(),
                texts: $texts,
                locale: $locale,
                translated: $translated,
            ));
            
            return $translated;
        } catch (Throwable $e) {
            $this->events?->dispatch(new Event\TranslationFailed(
                translator: $this->translator(),
                texts: $texts,
                locale: $locale,
                exception: $e,
            ));
            
            throw $e;
        }
    }
}