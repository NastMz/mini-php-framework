<?php
declare(strict_types=1);

namespace App\Infrastructure\Config;

use RuntimeException;

/**
 * ConfigurationException
 *
 * Exception thrown when there are configuration issues.
 * This should result in a 503 Service Unavailable response.
 */
class ConfigurationException extends RuntimeException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
