<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Logging\LoggerInterface;
use App\Infrastructure\Routing\RouteNotFoundException;

/**
 * ErrorHandlerMiddleware
 *
 * Middleware that catches unhandled exceptions and logs them.
 * It returns a 500 Internal Server Error response with a JSON body.
 */
class ErrorHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @param LoggerInterface $logger The logger to use for logging errors
     */
    public function __construct(private LoggerInterface $logger) {}

    /**
     * Process the request and handle any unhandled exceptions.
     *
     * @param RequestInterface $request The incoming request
     * @param RequestHandlerInterface $next The next handler in the middleware chain
     * @return ResponseInterface The response after processing
     */
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        try {
            return $next->handle($request);
        } catch (RouteNotFoundException $e) {
            // Handle 404 Not Found
            $this->logger->info('Route not found', [
                'message' => $e->getMessage(),
                'requestId' => $request->getAttribute('requestId'),
            ]);

            $response = new Response();
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode([
                    'error' => 'Not Found',
                    'message' => 'The requested resource was not found',
                    'requestId' => $request->getAttribute('requestId'),
                ]));
        } catch (\Throwable $e) {
            // Handle other exceptions as 500 Internal Server Error
            $context = [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
                'requestId' => $request->getAttribute('requestId'),
            ];
            $this->logger->error('Unhandled exception', $context);

            $response = new Response();
            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode([
                    'error'     => 'Internal Server Error',
                    'requestId' => $request->getAttribute('requestId'),
                ]));
        }
    }
}
