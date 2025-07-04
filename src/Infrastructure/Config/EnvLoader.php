<?php
declare(strict_types=1);

namespace App\Infrastructure\Config;

class EnvLoader
{
    private static array $cache = [];

    public static function load(string $path = null): void
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
                if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                    $value = substr($value, 1, -1);
                } elseif (str_starts_with($value, "'") && str_ends_with($value, "'")) {
                    $value = substr($value, 1, -1);
                }
                
                self::$cache[$key] = $value;
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    public static function get(string $key, string $default = null): ?string
    {
        return self::$cache[$key] ?? $_ENV[$key] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        self::$cache[$key] = $value;
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}
