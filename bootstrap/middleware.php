<?php
declare(strict_types=1);

use App\Infrastructure\Middleware\RequestIdMiddleware;
use App\Infrastructure\Middleware\ErrorHandlerMiddleware;
use App\Infrastructure\Middleware\RateLimitMiddleware;
use App\Infrastructure\Middleware\CorsMiddleware;
use App\Infrastructure\Middleware\SecurityHeadersMiddleware;
use App\Infrastructure\Middleware\AutoValidationMiddleware;
use App\Infrastructure\Middleware\AutoSerializationMiddleware;
use App\Infrastructure\Middleware\SessionMiddleware;
use App\Infrastructure\Middleware\CsrfMiddleware;
use App\Infrastructure\RateLimit\RateLimitService;
use App\Infrastructure\Logging\LoggerInterface;
use App\Infrastructure\Validation\AutoValidator;
use App\Infrastructure\Serialization\AutoSerializer;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */

// Core middlewares that run for all requests (in order)
$middlewares = [
    // 1. Request ID - Must be first for tracking
    new RequestIdMiddleware(),
    
    // 2. Error Handler - Must be early to catch all errors
    new ErrorHandlerMiddleware(
        $container->get(LoggerInterface::class)
    ),
    
    // 3. CORS - Must be before security headers
    new CorsMiddleware(
        $container->get('settings')['cors']['origins'] ?? ['http://localhost:3000'],
        $container->get('settings')['cors']['methods'] ?? ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        $container->get('settings')['cors']['headers'] ?? ['Content-Type', 'Authorization'],
        $container->get('settings')['cors']['credentials'] ?? false
    ),
    
    // 4. Security Headers - After CORS
    new SecurityHeadersMiddleware(
        $container->get('settings')['security']['headers'] ?? [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin'
        ]
    ),
    
    // 5. Rate Limiting - Before business logic
    new RateLimitMiddleware(
        $container->get(RateLimitService::class)
    ),
];

// Determine context based on request
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$isApiRequest = str_starts_with($requestUri, '/api');
$isWebRequest = !$isApiRequest;

// Add context-specific middlewares
if ($isWebRequest) {
    // Web-specific middlewares
    array_push($middlewares,
        // Session support for web pages
        new SessionMiddleware(),
        // CSRF protection for web forms
        new CsrfMiddleware()
    );
}

// Add business logic middlewares (for all requests)
array_push($middlewares,
    // Auto-validation - Before business logic
    new AutoValidationMiddleware(
        $container,
        $container->get(AutoValidator::class)
    ),
    
    // Auto-serialization - After business logic
    new AutoSerializationMiddleware(
        $container->get(AutoSerializer::class)
    )
);

return $middlewares;
