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

use Tobento\Service\MachineTranslator\Exception\TranslateException;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

/**
 * Test double: always throws TranslateException
 */
class FailingTranslator implements MachineTranslatorInterface
{
    public function __construct(protected string $name) {}

    public function name(): string
    {
        return $this->name;
    }

    public function translate(string $text, string $locale): string
    {
        throw new TranslateException('fail');
    }

    public function translateMany(array $texts, string $locale): array
    {
        throw new TranslateException('fail');
    }
}