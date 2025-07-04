<?php
declare(strict_types=1);

namespace App\Infrastructure\Config;

/**
 * EnvLoader
 *
 * A simple environment variable loader that reads from a .env file.
 * It caches the values for quick access and allows setting new values.
 */
class EnvLoader
{
    private static array $cache = [];

    /**
     * Load environment variables from a .env file.
     *
     * @param string|null $path Path to the .env file. Defaults to the root directory.
     */
    public static function load(?string $path = null): void
    {
        if ($path === null) {
            $path = __DIR__ . '/../../../.env';
        }

        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue; // Skip comments
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                    $value = substr($value, 1, -1);
                }
                
                self::$cache[$key] = $value;
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    /**
     * Get an environment variable value.
     *
     * @param string $key The environment variable key.
     * @param string|null $default Default value if the key is not found.
     * @return string|null The value of the environment variable or default if not set.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        return self::$cache[$key] ?? $_ENV[$key] ?? $default;
    }

    /**
     * Set an environment variable value.
     *
     * @param string $key The environment variable key.
     * @param string $value The value to set.
     */
    public static function set(string $key, string $value): void
    {
        self::$cache[$key] = $value;
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}
