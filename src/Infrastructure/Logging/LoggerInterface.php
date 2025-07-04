<?php
declare(strict_types=1);

namespace App\Infrastructure\Logging;

/**
 * LoggerInterface
 *
 * Interface for logging services.
 */
interface LoggerInterface
{
    /**
     * Logs a message at the given level.
     *
     * @param string $level The log level (e.g., 'info', 'error', 'debug').
     * @param string $message The log message.
     * @param array $context Additional context for the log message.
     */
    public function log(string $level, string $message, array $context = []): void;

    /**
     * Logs an informational message.
     *
     * @param string $message The log message.
     * @param array $context Additional context for the log message.
     */
    public function info(string $message, array $context = []): void;

    /**
     * Logs an error message.
     *
     * @param string $message The log message.
     * @param array $context Additional context for the log message.
     */
    public function error(string $message, array $context = []): void;

    /**
     * Logs a debug message.
     *
     * @param string $message The log message.
     * @param array $context Additional context for the log message.
     */
    public function debug(string $message, array $context = []): void;
}
