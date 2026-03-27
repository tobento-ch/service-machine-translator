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

namespace Tobento\Service\MachineTranslator;

use Tobento\Service\MachineTranslator\Exception\TranslateException;

interface MachineTranslatorInterface
{
    /**
     * Returns the translator name (e.g. "google", "deepl", "azure").
     *
     * @return string
     */
    public function name(): string;

    /**
     * Translates the given text into the specified locale.
     *
     * @param string $text The text to translate.
     * @param string $locale The target locale (e.g. "de", "fr", "es").
     * @return string The translated text.
     * @throws TranslateException If translation fails.
     */
    public function translate(string $text, string $locale): string;
    
    /**
     * Translates multiple texts into the specified locale.
     *
     * @param array<int, string> $texts
     * @param string $locale
     * @return array<int, string> Translated texts in the same order.
     * @throws TranslateException
     */
    public function translateMany(array $texts, string $locale): array;
}