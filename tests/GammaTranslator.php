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

use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

/**
 * Test double: returns fixed value
 */
class GammaTranslator implements MachineTranslatorInterface
{
    public function __construct(
        protected string $name,
        protected string $value = 'gamma',
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function translate(string $text, string $locale): string
    {
        return $this->value;
    }

    public function translateMany(array $texts, string $locale): array
    {
        return array_fill(0, count($texts), $this->value);
    }
}