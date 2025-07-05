<?php
declare(strict_types=1);

namespace App\Infrastructure\Logging;

/**
 * FileLogger
 *
 * A simple file-based logger that implements the LoggerInterface.
 * It writes log entries in JSON format to a specified file.
 */
class FileLogger implements LoggerInterface
{
    /**
     * Constructs a new FileLogger.
     *
     * @param string $filePath The path to the log file where entries will be written.
     */
    public function __construct(private string $filePath) {}

    /**
     * Logs a message at the given level.
     *
     * @param string $level The log level (e.g., 'info', 'error', 'debug').
     * @param string $message The log message.
     * @param array $context Additional context for the log message.
     */
    public function log(string $level, string $message, array $context = []): void
    {
        // Ensure the log directory exists
        $logDir = dirname($this->filePath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $entry = [
            'timestamp' => date('c'),
            'level'     => $level,
            'message'   => $message,
            'context'   => $context,
        ];
        
        file_put_contents(
            $this->filePath,
            json_encode($entry, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * Logs an informational message.
     *
     * @param string $message The log message.
     * @param array $context Additional context for the log message.
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Logs an error message.
     *
     * @param string $message The log message.
     * @param array $context Additional context for the log message.
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Logs a debug message.
     *
     * @param string $message The log message.
     * @param array $context Additional context for the log message.
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Logs a warning message.
     *
     * @param string $message The log message.
     * @param array $context Additional context for the log message.
     */
    public function warn(string $message, array $context = []): void
    {
        $this->log('warn', $message, $context);
    }
}
