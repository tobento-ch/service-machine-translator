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
 * Exception thrown when a translation operation fails.
 *
 * This exception represents errors that occur during the execution of a
 * translation request, such as network failures, invalid API responses,
 * unsupported languages, or provider-specific translation errors. It is
 * distinct from creation or lookup failures, and indicates that a translator
 * was successfully created but could not complete the requested translation.
 */
class TranslateException extends MachineTranslatorException
{
    //
}