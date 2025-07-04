<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;

/**
 * Interface MiddlewareInterface
 *
 * Represents a middleware component in the application's request processing pipeline.
 * Middleware can modify the request, response, or both, and can also delegate to the next middleware.
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming request and either delegate to the next middleware
     * or return a Response.
     *
     * @param RequestInterface      $request
     * @param RequestHandlerInterface $next
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface;
}
