<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Security\CsrfTokenManager;

/**
 * CSRF Middleware
 *
 * Validates CSRF tokens for state-changing requests (POST, PUT, PATCH, DELETE).
 * This middleware should be placed after SessionMiddleware.
 */
class CsrfMiddleware implements MiddlewareInterface
{
    private array $protectedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Process the request and validate CSRF token if necessary.
     *
     * @param RequestInterface $request The incoming request
     * @param RequestHandlerInterface $next The next handler in the middleware chain
     * @return ResponseInterface The response after processing
     */
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        // Only validate on state-changing methods
        if (in_array($request->getMethod(), $this->protectedMethods, true)) {
            $bodyToken   = $request->getParsedBody()['_csrf_token'] ?? null;
            $headerToken = $request->getHeader('X-CSRF-Token');
            $token       = $bodyToken ?? $headerToken;

            if (!CsrfTokenManager::validateToken($token)) {
                return (new Response())
                    ->withStatus(403)
                    ->withHeader('Content-Type', 'text/plain')
                    ->write('Invalid or missing CSRF token');
            }
        }

        return $next->handle($request);
    }
}
