<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\RateLimit\RateLimitServiceInterface;

/**
 * RateLimitMiddleware
 *
 * This middleware checks if the request exceeds the rate limit for the client's IP address.
 * If the limit is exceeded, it returns a 429 Too Many Requests response with a Retry-After header.
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    /**
     * Constructs a new RateLimitMiddleware.
     *
     * @param RateLimitServiceInterface $limiter The rate limit service to use for checking limits.
     */
    public function __construct(private RateLimitServiceInterface $limiter) {}

    /**
     * Process the request and check rate limits.
     *
     * @param RequestInterface $req The incoming request
     * @param RequestHandlerInterface $next The next handler in the middleware chain
     * @return ResponseInterface The response after processing
     */
    public function process(RequestInterface $req, RequestHandlerInterface $next): ResponseInterface
    {
        $ip = $req->getHeader('X-Real-IP')
            ?? $req->getHeader('X-Forwarded-For')
            ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        if (! $this->limiter->allow($ip)) {
            $retryAfter = $this->limiter->getWindowSize() - (time() % $this->limiter->getWindowSize());
            return (new Response())
                ->withStatus(429)
                ->withHeader('Content-Type','application/json')
                ->withHeader('Retry-After', (string)$retryAfter)
                ->write(json_encode([
                    'error'       => 'Rate limit exceeded',
                    'retry_after' => $retryAfter
                ]));
        }

        return $next->handle($req);
    }
}
