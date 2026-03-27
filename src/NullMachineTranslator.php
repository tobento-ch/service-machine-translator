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

class NullMachineTranslator implements MachineTranslatorInterface
{
    /**
     * Create a new instance.
     *
     * @param string $name The translator name.
     */
    public function __construct(
        protected string $name = 'null',
    ) {}

    /**
     * Returns the translator name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the text unchanged.
     *
     * @param string $text
     * @param string $locale
     * @return string
     * @throws TranslateException Never thrown in this implementation.
     */
    public function translate(string $text, string $locale): string
    {
        return $text;
    }
    
    /**
     * Returns the texts unchanged.
     *
     * @param array<int, string> $texts
     * @param string $locale
     * @return array<int, string>
     * @throws TranslateException Never thrown in this implementation.
     */
    public function translateMany(array $texts, string $locale): array
    {
        return $texts;
    }
}