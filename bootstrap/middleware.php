<?php
declare(strict_types=1);

// bootstrap/middleware.php

use App\Infrastructure\Middleware\MiddlewareInterface;
use App\Infrastructure\Middleware\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
/** @var array<MiddlewareInterface> $middlewares */
$middlewares = [
    new \App\Infrastructure\Middleware\CorsMiddleware(
        ['https://your-app.com'],
        ['GET','POST','OPTIONS'],
        ['Content-Type','X-CSRF-Token'],
        true
    ),
    new \App\Infrastructure\Middleware\SecurityHeadersMiddleware([
        // CSP seguro sin 'unsafe-inline'
        'Content-Security-Policy' => "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:;"
    ]),
];

// Example:
// $middlewares[] = $container->get(App\Infrastructure\Middleware\SessionMiddleware::class);
// $middlewares[] = $container->get(App\Infrastructure\Middleware\CorsMiddleware::class);
// $middlewares[] = $container->get(App\Infrastructure\Middleware\SecurityHeadersMiddleware::class);
// $middlewares[] = $container->get(App\Infrastructure\Middleware\CsrfMiddleware::class);
// $middlewares[] = $container->get(App\Infrastructure\Middleware\ErrorHandlerMiddleware::class);

return $middlewares;
