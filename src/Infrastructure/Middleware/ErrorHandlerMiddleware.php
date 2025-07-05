<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Logging\LoggerInterface;
use App\Infrastructure\Routing\RouteNotFoundException;
use App\Infrastructure\Routing\MethodNotAllowedException;
use App\Infrastructure\Validation\ValidationException;
use App\Infrastructure\Security\EncryptionException;
use App\Infrastructure\Config\ConfigurationException;
use App\Infrastructure\Service\FileUploadException;
use App\Infrastructure\Service\FileSizeException;
use App\Infrastructure\Service\FileTypeException;
use App\Infrastructure\Service\FileCorruptedException;

/**
 * ErrorHandlerMiddleware
 *
 * Middleware that catches unhandled exceptions and logs them.
 * It returns appropriate HTTP status codes based on exception type.
 */
class ErrorHandlerMiddleware implements MiddlewareInterface
{
    private const CONTENT_TYPE_JSON = 'application/json';

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
        } catch (FileSizeException $e) {
            // Handle 413 Payload Too Large
            $this->logger->info('File too large', [
                'message' => $e->getMessage(),
                'requestId' => $request->getAttribute('requestId'),
            ]);

            $response = new Response();
            return $response
                ->withStatus(413)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode([
                    'error' => 'Payload Too Large',
                    'message' => $e->getMessage(),
                    'requestId' => $request->getAttribute('requestId'),
                ]));
        } catch (FileTypeException $e) {
            // Handle 415 Unsupported Media Type
            $this->logger->info('Unsupported file type', [
                'message' => $e->getMessage(),
                'requestId' => $request->getAttribute('requestId'),
            ]);

            $response = new Response();
            return $response
                ->withStatus(415)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode([
                    'error' => 'Unsupported Media Type',
                    'message' => $e->getMessage(),
                    'requestId' => $request->getAttribute('requestId'),
                ]));
        } catch (FileCorruptedException | FileUploadException $e) {
            // Handle 422 Unprocessable Entity for file corruption and other upload issues
            $this->logger->info('File upload error', [
                'message' => $e->getMessage(),
                'requestId' => $request->getAttribute('requestId'),
            ]);

            $response = new Response();
            return $response
                ->withStatus(422)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode([
                    'error' => 'Unprocessable Entity',
                    'message' => $e->getMessage(),
                    'requestId' => $request->getAttribute('requestId'),
                ]));
        } catch (ConfigurationException $e) {
            // Handle 503 Service Unavailable for configuration issues
            $this->logger->error('Configuration error', [
                'message' => $e->getMessage(),
                'requestId' => $request->getAttribute('requestId'),
            ]);

            $response = new Response();
            return $response
                ->withStatus(503)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode([
                    'error' => 'Service Unavailable',
                    'message' => 'Service temporarily unavailable due to configuration issues',
                    'requestId' => $request->getAttribute('requestId'),
                ]));
        } catch (MethodNotAllowedException $e) {
            // Handle 405 Method Not Allowed
            $this->logger->info('HTTP method not allowed', [
                'message' => $e->getMessage(),
                'requestId' => $request->getAttribute('requestId'),
            ]);

            $response = new Response();
            return $response
                ->withStatus(405)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode([
                    'error' => 'Method Not Allowed',
                    'message' => $e->getMessage(),
                    'requestId' => $request->getAttribute('requestId'),
                ]));
        } catch (RouteNotFoundException $e) {
            // Handle 404 Not Found
            $this->logger->info('Route not found', [
                'message' => $e->getMessage(),
                'requestId' => $request->getAttribute('requestId'),
            ]);

            $response = new Response();
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode([
                    'error' => 'Not Found',
                    'message' => 'The requested resource was not found',
                    'requestId' => $request->getAttribute('requestId'),
                ]));
        } catch (EncryptionException $e) {
            // Handle 400 Bad Request for encryption/decryption errors
            $this->logger->info('Encryption operation failed', [
                'message' => $e->getMessage(),
                'requestId' => $request->getAttribute('requestId'),
            ]);

            $response = new Response();
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode([
                    'error' => 'Bad Request',
                    'message' => $e->getMessage(),
                    'requestId' => $request->getAttribute('requestId'),
                ]));
        } catch (ValidationException $e) {
            // Handle 422 Unprocessable Entity
            $this->logger->info('Validation failed', [
                'errors' => $e->getErrors(),
                'requestId' => $request->getAttribute('requestId'),
            ]);

            $response = new Response();
            return $response
                ->withStatus(422)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode([
                    'error' => 'Validation Failed',
                    'errors' => $e->getErrors(),
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
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode([
                    'error'     => 'Internal Server Error',
                    'requestId' => $request->getAttribute('requestId'),
                ]));
        }
    }
}
