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

use Tobento\Service\MachineTranslator\MachineTranslatorFactoryInterface;
use Tobento\Service\MachineTranslator\MachineTranslatorInterface;

class GammaTranslatorFactory implements MachineTranslatorFactoryInterface
{
    public function createTranslator(string $name, array $config = []): MachineTranslatorInterface
    {
        $value = $config['value'] ?? 'gamma';

        return new GammaTranslator(name: $name, value: $value);
    }
}