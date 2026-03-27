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

namespace Tobento\Service\MachineTranslator\Exception;

/**
 * Exception thrown when a requested machine translator is not registered.
 *
 * This exception is used by translator registries (such as MachineTranslators
 * or LazyMachineTranslators) to indicate that no translator exists under the
 * specified name. It represents a lookup failure rather than an error during
 * translator creation or initialization.
 */
class MachineTranslatorNotFoundException extends MachineTranslatorException
{
    //
}