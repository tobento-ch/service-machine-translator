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

use Tobento\Service\MachineTranslator\Exception\MachineTranslatorCreationException;

interface MachineTranslatorFactoryInterface
{
    /**
     * Creates and returns a new MachineTranslator instance.
     *
     * @param string $name The translator name used for identification.
     * @param array $config  Provider-specific configuration values used to construct the translator instance.
     * @return MachineTranslatorInterface
     * @throws MachineTranslatorCreationException If the translator cannot be created.
     */
    public function createTranslator(string $name, array $config = []): MachineTranslatorInterface;
}
