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
 * Exception thrown when a machine translator cannot be created.
 *
 * This exception is used by MachineTranslatorFactoryInterface implementations
 * to indicate that an error occurred during the construction or initialization
 * of a machine translator instance. Typical causes include invalid or missing
 * configuration values, unavailable dependencies, or failures while preparing
 * the underlying translation provider.
 */
class MachineTranslatorCreationException extends MachineTranslatorException
{
    //
}