<?php
declare(strict_types=1);

use App\Infrastructure\Middleware\MiddlewareInterface;
use App\Infrastructure\Middleware\RequestHandlerInterface;
use Psr\Container\ContainerInterface;


/** @var ContainerInterface $container */
$validators = [
    // Define validation rules for specific routes
    // 'POST /user' => [
    //     (new FieldValidator('name'))
    //         ->addRule(new NotEmpty())
    //         ->addRule(new MinLength(3)),
    //     (new FieldValidator('email'))
    //         ->addRule(new NotEmpty()),
    // ],
    // ... other routes ...
];

$middlewares = [
    new \App\Infrastructure\Middleware\RateLimitMiddleware(
        $container->get(\App\Infrastructure\RateLimit\RateLimitService::class)
    ),
    new \App\Infrastructure\Middleware\CorsMiddleware(
        ['https://your-app.com', 'http://localhost:3000'], // Add development domains
        ['GET','POST','PUT','DELETE','OPTIONS'],
        ['Content-Type','Authorization','X-CSRF-Token'],
        true
    ),
    new \App\Infrastructure\Middleware\SecurityHeadersMiddleware([
        'Content-Security-Policy' => "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:;"
    ]),
    new \App\Infrastructure\Middleware\HttpCacheMiddleware(
        (int)($container->get('settings')['cache']['max_age'] ?? 60)
    ),
    new \App\Infrastructure\Middleware\ValidationMiddleware($validators),
];

$isWebApp = !str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api');

if ($isWebApp) {
    // Only for web applications
    array_unshift($middlewares, new \App\Infrastructure\Middleware\SessionMiddleware());
    $middlewares[] = new \App\Infrastructure\Middleware\CsrfMiddleware();
}

return $middlewares;
