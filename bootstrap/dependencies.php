<?php
declare(strict_types=1);

use App\Infrastructure\DI\Container;
use App\Infrastructure\RateLimit\SimpleRateLimitService;
use App\Infrastructure\RateLimit\RateLimitService;
use Psr\Container\ContainerInterface;

/** @var array<string,mixed> $settings */
$settings = require __DIR__ . '/config.php';

/** @var ContainerInterface $container */
$container = Container::build($settings, [
    // SQLite PDO connection
    PDO::class => fn(Container $c) => new PDO(
        $c->get('settings')['database']['dsn'],
        null,
        null,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    ),
    
    // Database-based rate limiter
    RateLimitService::class => fn(Container $c) => new RateLimitService(
        $c->get(PDO::class),
        $c->get('settings')['rate_limit']['max_requests'] ?? 60,
        $c->get('settings')['rate_limit']['window_size'] ?? 60
    ),
    
    // File-based rate limiter (backup)
    SimpleRateLimitService::class => fn(Container $c) => new SimpleRateLimitService(
        $c->get('settings')['rate_limit']['max_requests'] ?? 60,
        $c->get('settings')['rate_limit']['window_size'] ?? 60
    ),
]);

return $container;
