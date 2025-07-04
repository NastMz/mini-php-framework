<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;

/**
 * RequestIdMiddleware
 *
 * Generates a unique request ID for each incoming request and attaches it to the request and response.
 * This is useful for tracking requests in logs and debugging.
 */
class RequestIdMiddleware implements MiddlewareInterface
{
    /**
     * Process the request and generate a unique request ID.
     *
     * @param RequestInterface $request The incoming request
     * @param RequestHandlerInterface $next The next handler in the middleware chain
     * @return ResponseInterface The response after processing
     */
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $id = bin2hex(random_bytes(8));
        // attach to request
        $request = $request->withAttribute('requestId', $id);
        // proceed and then add header
        $response = $next->handle($request);
        return $response->withHeader('X-Request-ID', $id);
    }
}
