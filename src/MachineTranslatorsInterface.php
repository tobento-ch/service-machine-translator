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

use Tobento\Service\MachineTranslator\Exception\MachineTranslatorNotFoundException;

interface MachineTranslatorsInterface
{
    /**
     * Returns the machine translator registered under the given name.
     *
     * @param string $name The translator name.
     * @return MachineTranslatorInterface
     * @throws MachineTranslatorNotFoundException If no translator is registered with the given name.
     */
    public function get(string $name): MachineTranslatorInterface;

    /**
     * Checks whether a machine translator with the given name exists.
     *
     * @param string $name The translator name.
     * @return bool True if the translator exists, otherwise false.
     */
    public function has(string $name): bool;

    /**
     * Returns all registered translator names.
     *
     * @return array<int, string> A list of translator names.
     */
    public function names(): array;
}