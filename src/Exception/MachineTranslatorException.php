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

use Exception;

/**
 * Base exception for all machine‑translation related errors.
 *
 * All exceptions thrown within the machine translator service extend this class,
 * allowing consumers to catch MachineTranslatorException to handle any
 * translation-specific failure in a unified way. This includes errors occurring
 * during translator creation, configuration, API communication, or translation
 * processing.
 */
class MachineTranslatorException extends Exception
{
    
}