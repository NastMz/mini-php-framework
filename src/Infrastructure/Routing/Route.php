<?php
declare(strict_types=1);

namespace App\Infrastructure\Routing;

use App\Infrastructure\Middleware\RequestHandlerInterface;

/**
 * Class Route
 *
 * Represents a single route in the application's routing system.
 * Contains the HTTP method, path pattern, and handler for the route.
 */
final class Route
{
    /**
     * @param HttpMethod          $method      HTTP verb
     * @param string              $pathPattern URI pattern, e.g. '/user/{id}'
     * @param callable|string     $handler     Either a callable or 'ControllerClass::method'
     */
    public function __construct(
        public readonly HttpMethod $method,
        public readonly string     $pathPattern,
        public readonly mixed      $handler
    ) {
        if (!str_starts_with($pathPattern, '/')) {
            throw new \InvalidArgumentException("Route path must start with '/': {$pathPattern}");
        }
        
        if (!is_callable($handler) && !is_string($handler)) {
            throw new \InvalidArgumentException('Handler must be a callable or "Class::method" string');
        }
    }
}
