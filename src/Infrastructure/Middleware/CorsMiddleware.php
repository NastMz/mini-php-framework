<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;

/**
 * CORS Middleware
 *
 * Handles Cross-Origin Resource Sharing (CORS) requests.
 * Allows configuration of allowed origins, methods, headers, and credentials.
 */
class CorsMiddleware implements MiddlewareInterface
{
    /**
     * @param array<string> $allowedOrigins   List of allowed origins (e.g. ['*', 'https://example.com'])
     * @param array<string> $allowedMethods   List of allowed HTTP methods (e.g. ['GET', 'POST'])
     * @param array<string> $allowedHeaders   List of allowed headers (e.g. ['Content-Type', 'Authorization'])
     * @param bool          $allowCredentials Whether to allow credentials (cookies, HTTP auth) in CORS requests
     */
    public function __construct(
        private array $allowedOrigins   = ['*'],
        private array $allowedMethods   = ['GET','POST','PUT','DELETE','OPTIONS'],
        private array $allowedHeaders   = ['Content-Type','Authorization'],
        private bool  $allowCredentials = true
    ) {}

    /**
     * Process the request and handle CORS headers.
     *
     * @param RequestInterface $req  The incoming request
     * @param RequestHandlerInterface $next The next handler in the middleware chain
     * @return ResponseInterface The response with CORS headers applied
     */
    public function process(RequestInterface $req, RequestHandlerInterface $next): ResponseInterface
    {
        // Handle preflight
        if ($req->getMethod() === 'OPTIONS') {
            return (new Response())
                ->withStatus(204)
                ->withHeader('Access-Control-Allow-Origin', $this->determineOrigin($req))
                ->withHeader('Access-Control-Allow-Methods', implode(',', $this->allowedMethods))
                ->withHeader('Access-Control-Allow-Headers', implode(',', $this->allowedHeaders))
                ->withHeader('Access-Control-Allow-Credentials', $this->allowCredentials ? 'true' : 'false');
        }

        // Otherwise, process and then inject CORS headers
        $res = $next->handle($req);

        return $res
            ->withHeader('Access-Control-Allow-Origin', $this->determineOrigin($req))
            ->withHeader('Access-Control-Allow-Methods', implode(',', $this->allowedMethods))
            ->withHeader('Access-Control-Allow-Headers', implode(',', $this->allowedHeaders))
            ->withHeader('Access-Control-Allow-Credentials', $this->allowCredentials ? 'true' : 'false');
    }

    /**
     * Determine the origin for CORS based on the request headers.
     *
     * @param RequestInterface $req The incoming request
     * @return string The determined origin, or 'null' if not allowed
     */
    private function determineOrigin(RequestInterface $req): string
    {
        $origin = $req->getHeader('Origin') ?? '';
        if (in_array('*', $this->allowedOrigins, true) || in_array($origin, $this->allowedOrigins, true)) {
            return $origin ?: '*';
        }
        return 'null';
    }
}
