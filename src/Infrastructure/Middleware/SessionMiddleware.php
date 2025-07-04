<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;

/**
 * Session Middleware
 *
 * Starts a session if not already started.
 * This middleware should be placed early in the middleware stack.
 */
class SessionMiddleware implements MiddlewareInterface
{
    /**
     * Process the request and start a session if not already started.
     *
     * @param RequestInterface $request The incoming request
     * @param RequestHandlerInterface $next The next handler in the middleware chain
     * @return ResponseInterface The response after processing
     */
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $next->handle($request);
    }
}
