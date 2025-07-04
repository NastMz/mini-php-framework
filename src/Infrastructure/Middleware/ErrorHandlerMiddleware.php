<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;

/**
 * Class ErrorHandlerMiddleware
 *
 * This middleware catches exceptions thrown during request processing and returns a generic 500 response.
 * It logs the full exception details for debugging purposes.
 */
class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /**
     * Processes the incoming request and handles any exceptions that occur.
     *
     * @param RequestInterface $request The incoming HTTP request
     * @param RequestHandlerInterface $next The next middleware or request handler in the pipeline
     * @return ResponseInterface The HTTP response, either from the next handler or a generic error response
     */
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        try {
            return $next->handle($request);
        } catch (\Throwable $e) {
            // Log full exception
            error_log(sprintf(
                "[%s] %s in %s:%d\n%s",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            ));

            // Return generic 500 response
            $response = new Response();
            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode([
                    'error' => 'Internal Server Error',
                    // 'details' => $e->getMessage(), // include in dev only
                ]));
        }
    }
}
