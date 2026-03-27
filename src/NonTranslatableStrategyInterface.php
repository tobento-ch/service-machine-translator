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

/**
 * Strategy for marking parts of a text as non-translatable
 * before sending it to a machine translation provider.
 */
interface NonTranslatableStrategyInterface
{
    /**
     * Protects non-translatable segments in the given text.
     *
     * @param string $text The original text.
     * @return string The text with protection markers applied.
     */
    public function protect(string $text): string;

    /**
     * Removes protection markers from the translated text.
     *
     * @param string $text The translated text.
     * @return string The cleaned text without protection markers.
     */
    public function unprotect(string $text): string;
}