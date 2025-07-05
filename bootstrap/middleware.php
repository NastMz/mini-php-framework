<?php
declare(strict_types=1);

use App\Infrastructure\Middleware\MiddlewareInterface;
use App\Infrastructure\Middleware\RequestHandlerInterface;
use App\Infrastructure\Middleware\RequestIdMiddleware;
use App\Infrastructure\Middleware\ErrorHandlerMiddleware;
use App\Infrastructure\Middleware\RateLimitMiddleware;
use App\Infrastructure\Middleware\CorsMiddleware;
use App\Infrastructure\Middleware\SecurityHeadersMiddleware;
use App\Infrastructure\Middleware\HttpCacheMiddleware;
use App\Infrastructure\Middleware\AutoValidationMiddleware;
use App\Infrastructure\Middleware\AutoSerializationMiddleware;
use App\Infrastructure\RateLimit\RateLimitService;
use App\Infrastructure\Middleware\SessionMiddleware;
use App\Infrastructure\Middleware\CsrfMiddleware;
use App\Infrastructure\Logging\LoggerInterface;
use App\Infrastructure\Validation\AutoValidator;
use App\Infrastructure\Serialization\AutoSerializer;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */

$middlewares = [
    new RequestIdMiddleware(),
    new ErrorHandlerMiddleware(
        $container->get(LoggerInterface::class)
    ),
    new RateLimitMiddleware(
        $container->get(RateLimitService::class)
    ),
    new CorsMiddleware(
        ['https://your-app.com', 'http://localhost:3000'], // Add development domains
        ['GET','POST','PUT','DELETE','OPTIONS'],
        ['Content-Type','Authorization','X-CSRF-Token'],
        true
    ),
    new SecurityHeadersMiddleware([
        'Content-Security-Policy' => "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:;"
    ]),
    new HttpCacheMiddleware(
        (int)($container->get('settings')['cache']['max_age'] ?? 60)
    ),
    new AutoValidationMiddleware(
        $container,
        $container->get(AutoValidator::class)
    ),
    new AutoSerializationMiddleware(
        $container->get(AutoSerializer::class)
    ),
];

$isWebApp = !str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api');

if ($isWebApp) {
    // Only for web applications
    array_unshift($middlewares, new SessionMiddleware());
    $middlewares[] = new CsrfMiddleware();
}

return $middlewares;
