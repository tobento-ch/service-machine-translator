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

use Tobento\Service\MachineTranslator\NonTranslatableStrategyInterface;

class FakeStrategy implements NonTranslatableStrategyInterface
{
    public bool $protectCalled = false;
    public bool $unprotectCalled = false;

    public function protect(string $text): string
    {
        $this->protectCalled = true;
        return '[[PROTECT]]' . $text . '[[/PROTECT]]';
    }

    public function unprotect(string $text): string
    {
        $this->unprotectCalled = true;
        return str_replace(['[[PROTECT]]', '[[/PROTECT]]'], '', $text);
    }
}