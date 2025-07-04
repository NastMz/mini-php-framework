<?php
declare(strict_types=1);

use App\Infrastructure\Config\EnvLoader;

return [
    'app' => [
        'name' => EnvLoader::get('APP_NAME', 'MiniFramework'),
        'env' => EnvLoader::get('APP_ENV', 'production'),
        'debug' => EnvLoader::get('APP_DEBUG', 'false') === 'true',
        'key' => EnvLoader::get('APP_KEY'),
    ],
    'database' => [
        'dsn' => EnvLoader::get('DB_DSN', 'sqlite:' . __DIR__ . '/../storage/database/app.sqlite'),
        'host' => EnvLoader::get('DB_HOST', 'localhost'),
        'port' => EnvLoader::get('DB_PORT', '3306'),
        'database' => EnvLoader::get('DB_DATABASE', 'miniframework'),
        'username' => EnvLoader::get('DB_USERNAME', 'root'),
        'password' => EnvLoader::get('DB_PASSWORD', ''),
    ],
    'rate_limit' => [
        'max_requests' => (int)EnvLoader::get('RATE_LIMIT_MAX_REQUESTS', '60'),
        'window_size' => (int)EnvLoader::get('RATE_LIMIT_WINDOW_SIZE', '60'),
    ],
    'cache' => [
        'driver' => EnvLoader::get('CACHE_DRIVER', 'file'),
        'max_age' => (int)EnvLoader::get('CACHE_MAX_AGE', '120'),
    ],
    'session' => [
        'driver' => EnvLoader::get('SESSION_DRIVER', 'file'),
        'lifetime' => (int)EnvLoader::get('SESSION_LIFETIME', '120'),
    ],
    'mail' => [
        'driver' => EnvLoader::get('MAIL_DRIVER', 'smtp'),
        'host' => EnvLoader::get('MAIL_HOST', 'smtp.gmail.com'),
        'port' => (int)EnvLoader::get('MAIL_PORT', '587'),
        'username' => EnvLoader::get('MAIL_USERNAME'),
        'password' => EnvLoader::get('MAIL_PASSWORD'),
        'encryption' => EnvLoader::get('MAIL_ENCRYPTION', 'tls'),
    ],
];
