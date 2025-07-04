<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;

/**
 * HttpCacheMiddleware
 *
 * Implements HTTP caching for GET requests using ETag and Cache-Control headers.
 * If the client sends an If-None-Match header that matches the ETag, a 304 Not Modified response is returned.
 */
class HttpCacheMiddleware implements MiddlewareInterface
{
    /**
     * Constructs a new HttpCacheMiddleware.
     *
     * @param int $maxAge Maximum age for the cache in seconds (default is 60 seconds).
     */
    public function __construct(private int $maxAge = 60) {}

    /**
     * Process the request and handle caching.
     *
     * @param RequestInterface $req The incoming request
     * @param RequestHandlerInterface $next The next handler in the middleware chain
     * @return ResponseInterface The response after processing
     */
    public function process(RequestInterface $req, RequestHandlerInterface $next): ResponseInterface
    {
        // Only cache GET
        if ($req->getMethod() !== 'GET') {
            return $next->handle($req);
        }

        // Run the handler and capture its response
        $res  = $next->handle($req);
        $body = $res->getBody();

        // Compute ETag
        $etag = '"' . md5($body) . '"';

        // If client sent If-None-Match and it matches, return 304
        $ifNone = $req->getHeader('If-None-Match') ?? '';
        if ($ifNone === $etag) {
            return (new Response())
                ->withStatus(304)
                ->withHeader('Cache-Control', "public, max-age={$this->maxAge}")
                ->withHeader('ETag', $etag);
        }

        // Otherwise, attach caching headers to the full response
        return $res
            ->withHeader('Cache-Control', "public, max-age={$this->maxAge}")
            ->withHeader('ETag', $etag);
    }
}
