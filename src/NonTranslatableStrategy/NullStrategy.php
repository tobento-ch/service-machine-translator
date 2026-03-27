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

namespace Tobento\Service\MachineTranslator\NonTranslatableStrategy;

use Tobento\Service\MachineTranslator\NonTranslatableStrategyInterface;

/**
 * A non-translatable strategy that performs no protection.
 * Useful when no placeholder handling is required.
 */
class NullStrategy implements NonTranslatableStrategyInterface
{
    /**
     * Returns the text unchanged.
     *
     * @param string $text The original text.
     * @return string The unmodified text.
     */
    public function protect(string $text): string
    {
        return $text;
    }

    /**
     * Returns the text unchanged.
     *
     * @param string $text The translated text.
     * @return string The unmodified text.
     */
    public function unprotect(string $text): string
    {
        return $text;
    }
}