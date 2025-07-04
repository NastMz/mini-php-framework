<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;

/**
 * SecurityHeadersMiddleware
 *
 * Adds security-related HTTP headers to responses to enhance security.
 * This includes headers like Content-Security-Policy, X-Frame-Options, etc.
 */
class SecurityHeadersMiddleware implements MiddlewareInterface
{
    private array $headers;

    /**
     * Constructor
     *
     * @param array<string, string> $headers Custom headers to set; defaults provided if not specified
     */
    public function __construct(array $headers = [])
    {
        // Default security headers; override by passing your own in constructor
        $this->headers = array_merge([
            'X-Content-Type-Options'    => 'nosniff',
            'X-Frame-Options'           => 'DENY',
            'Referrer-Policy'           => 'strict-origin-when-cross-origin',
            'Permissions-Policy'        => "geolocation=(), microphone=(), camera=()",
            'Content-Security-Policy'   => "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:;",
            'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains; preload',
        ], $headers);
    }

    /**
     * Process the request and add security headers to the response.
     *
     * @param RequestInterface $req  The incoming request
     * @param RequestHandlerInterface $next The next handler in the middleware chain
     * @return ResponseInterface The response with security headers applied
     */
    public function process(RequestInterface $req, RequestHandlerInterface $next): ResponseInterface
    {
        $res = $next->handle($req);

        foreach ($this->headers as $name => $value) {
            $res = $res->withHeader($name, $value);
        }

        return $res;
    }
}
