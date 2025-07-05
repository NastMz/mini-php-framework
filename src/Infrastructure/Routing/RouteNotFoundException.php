<?php
declare(strict_types=1);

namespace App\Infrastructure\Routing;

use RuntimeException;

/**
 * RouteNotFoundException
 *
 * Exception thrown when a requested route is not found.
 * This should result in a 404 Not Found response.
 */
class RouteNotFoundException extends RuntimeException
{
    public function __construct(string $method, string $path)
    {
        parent::__construct("No route found for {$method} {$path}");
    }
}
