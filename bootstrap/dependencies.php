<?php
declare(strict_types=1);

use App\Infrastructure\DI\Container;
use App\Infrastructure\RateLimit\SimpleRateLimitService;
use Psr\Container\ContainerInterface;

/** @var array<string,mixed> $settings */
$settings = require __DIR__ . '/config.php';

/** @var ContainerInterface $container */
$container = Container::build($settings, [
    SimpleRateLimitService::class => fn(Container $c) => new SimpleRateLimitService(
        $c->get('settings')['rate_limit']['max_requests'] ?? 60,
        $c->get('settings')['rate_limit']['window_size'] ?? 60
    ),
]);

return $container;
