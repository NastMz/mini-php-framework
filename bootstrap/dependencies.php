<?php
declare(strict_types=1);

use App\Infrastructure\DI\Container;
use App\Infrastructure\RateLimit\SimpleRateLimitService;
use App\Infrastructure\RateLimit\RateLimitService;
use App\Infrastructure\Logging\LoggerInterface;
use App\Infrastructure\Templating\TemplateEngine;
use App\Infrastructure\Database\DatabaseHelper;
use Psr\Container\ContainerInterface;
use App\Infrastructure\Logging\CompositeLogger;

/** @var array<string,mixed> $settings */
$settings = require_once __DIR__ . '/config.php';

/** @var ContainerInterface $container */
$container = Container::build($settings, [
    // SQLite PDO connection with auto-creation
    PDO::class => fn(Container $c) => DatabaseHelper::createPdo(
        $c->get('settings')['database']
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
    LoggerInterface::class => fn() => new CompositeLogger(__DIR__ . '/../logs/app.log'),
    TemplateEngine::class => fn() => new TemplateEngine(
        __DIR__ . '/../views',
        __DIR__ . '/../storage/cache/templates'
    ),
]);

return $container;
