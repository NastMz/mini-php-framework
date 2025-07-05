<?php
declare(strict_types=1);

namespace App\Infrastructure\Logging;

/**
 * CompositeLogger
 *
 * A logger that writes to both file and console simultaneously.
 * It formats console output with colors and readable format,
 * while keeping JSON format for file logging.
 */
class CompositeLogger implements LoggerInterface
{
    /**
     * Constructs a new CompositeLogger.
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
        $timestamp = date('c');
        
        // Write to file (JSON format)
        $this->writeToFile($timestamp, $level, $message, $context);
        
        // Write to console (human-readable format)
        $this->writeToConsole($timestamp, $level, $message, $context);
    }

    /**
     * Writes the log entry to file in JSON format.
     */
    private function writeToFile(string $timestamp, string $level, string $message, array $context): void
    {
        // Ensure the log directory exists
        $logDir = dirname($this->filePath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $entry = [
            'timestamp' => $timestamp,
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
     * Writes the log entry to console with colors and readable format.
     */
    private function writeToConsole(string $timestamp, string $level, string $message, array $context): void
    {
        // Color codes for different log levels
        $colors = [
            'error' => "\033[31m",  // Red
            'warn'  => "\033[33m",  // Yellow
            'info'  => "\033[32m",  // Green
            'debug' => "\033[36m",  // Cyan
        ];
        
        $reset = "\033[0m";  // Reset color
        $color = $colors[$level] ?? "\033[37m";  // Default to white
        
        // Format: [TIMESTAMP] LEVEL: MESSAGE
        $consoleMessage = sprintf(
            "%s[%s] %s%s:%s %s",
            $color,
            date('Y-m-d H:i:s', strtotime($timestamp)),
            strtoupper($level),
            $reset,
            $color,
            $message
        );
        
        // Add context if present
        if (!empty($context)) {
            $contextStr = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $consoleMessage .= "\n" . $color . "Context: " . $contextStr;
        }
        
        $consoleMessage .= $reset . PHP_EOL;
        
        // Write to stderr for error level, stdout for others
        if ($level === 'error') {
            // Use error_log for errors (goes to PHP error log)
            error_log($consoleMessage, 4);
        } else {
            // For non-web environments, write to stdout
            if (php_sapi_name() === 'cli') {
                fwrite(\STDOUT, $consoleMessage);
            } else {
                // For web environments, use error_log to write to server log
                error_log($consoleMessage, 4);
            }
        }
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
