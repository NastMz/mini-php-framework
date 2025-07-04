<?php
declare(strict_types=1);

// bootstrap/middleware.php

use App\Infrastructure\Middleware\MiddlewareInterface;
use App\Infrastructure\Middleware\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
/** @var array<MiddlewareInterface> $middlewares */
$middlewares = [];

// Example:
// $middlewares[] = $container->get(App\Infrastructure\Middleware\SessionMiddleware::class);
// $middlewares[] = $container->get(App\Infrastructure\Middleware\CorsMiddleware::class);
// $middlewares[] = $container->get(App\Infrastructure\Middleware\SecurityHeadersMiddleware::class);
// $middlewares[] = $container->get(App\Infrastructure\Middleware\CsrfMiddleware::class);
// $middlewares[] = $container->get(App\Infrastructure\Middleware\ErrorHandlerMiddleware::class);

return $middlewares;
