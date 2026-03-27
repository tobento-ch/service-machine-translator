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
 * Exception thrown when a translation provider reports that its usage quota
 * has been exceeded.
 *
 * This exception indicates that the translator service refused the request
 * because the allowed quota (daily, monthly, rate limit, or free-tier limit)
 * has been reached. It is typically thrown when the provider returns an
 * error such as "quotaExceeded", "dailyLimitExceeded", HTTP 429, or
 * provider-specific quota status codes.
 *
 * Unlike TranslateException, which represents general translation failures,
 * this exception specifically signals that retrying the request immediately
 * will not succeed until the quota resets or the plan is upgraded.
 */
class QuotaExceededException extends TranslateException
{
    //
}