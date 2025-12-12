<?php

declare(strict_types=1);

namespace App\Logging;

use Illuminate\Log\Logger;

/**
 * Tap class for Laravel logging configuration.
 *
 * This class is used with the 'tap' option in logging channels
 * to push the SanitizeProcessor to the logger instance.
 */
final class SanitizeProcessorTap
{
    /**
     * Customize the given logger instance.
     *
     * @param  Logger  $logger  The logger instance to customize
     */
    public function __invoke(Logger $logger): void
    {
        $logger->pushProcessor(new SanitizeProcessor());
    }
}
