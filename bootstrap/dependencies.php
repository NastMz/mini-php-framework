<?php
declare(strict_types=1);

use App\Infrastructure\DI\Container;
use App\Infrastructure\RateLimit\SimpleRateLimitService;
use App\Infrastructure\RateLimit\RateLimitService;
use App\Infrastructure\Logging\LoggerInterface;
use App\Infrastructure\Templating\TemplateEngine;
use App\Infrastructure\Database\DatabaseHelper;
use App\Infrastructure\Service\FileUploadService;
use App\Infrastructure\Storage\LocalFileStorage;
use App\Domain\Service\FileStorageInterface;
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
    
    // File Storage Services
    FileStorageInterface::class => fn(Container $c) => new LocalFileStorage(
        __DIR__ . '/../public/uploads', // Store in public directory for direct access
        '/uploads' // Base URL for accessing uploaded files
    ),
    FileUploadService::class => fn(Container $c) => new FileUploadService(
        $c->get(FileStorageInterface::class),
        $c->get('settings')['upload']['max_size'],
        $c->get('settings')['upload']['allowed_types']
    ),
    
    TemplateEngine::class => fn() => new TemplateEngine(
        __DIR__ . '/../views',
        __DIR__ . '/../storage/cache/templates'
    ),
]);

return $container;
