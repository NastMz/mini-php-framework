<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Validation\AutoValidator;
use App\Infrastructure\Validation\ValidationResult;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;

/**
 * AutoValidationMiddleware
 *
 * Automatically validates requests based on controller method attributes
 */
class AutoValidationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ContainerInterface $container,
        private AutoValidator $validator
    ) {}

    /**
     * Process the request and validate based on controller method attributes
     */
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        // Get the route handler information
        $handler = $request->getAttribute('handler');
        
        if (!$handler || !is_string($handler) || !str_contains($handler, '::')) {
            return $next->handle($request);
        }

        [$className, $methodName] = explode('::', $handler, 2);
        
        if (!class_exists($className)) {
            return $next->handle($request);
        }

        try {
            $reflection = new ReflectionClass($className);
            $method = $reflection->getMethod($methodName);
            
            // Validate the request
            $validationResult = $this->validator->validateMethod($request, $method);
            
            if ($validationResult->fails()) {
                return $this->createValidationErrorResponse($validationResult);
            }
            
        } catch (\ReflectionException $e) {
            // If we can't reflect the method, just continue
        }

        return $next->handle($request);
    }

    /**
     * Create validation error response
     */
    private function createValidationErrorResponse(ValidationResult $result): ResponseInterface
    {
        $errors = $result->getErrors();
        
        // Flatten nested errors
        $flattenedErrors = [];
        foreach ($errors as $field => $fieldErrors) {
            if (is_array($fieldErrors)) {
                $flattenedErrors[$field] = $fieldErrors;
            } else {
                $flattenedErrors[$field] = [$fieldErrors];
            }
        }

        return (new Response())
            ->withStatus(422)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode([
                'error' => 'Validation failed',
                'message' => 'The given data was invalid.',
                'errors' => $flattenedErrors,
                'requestId' => $request->getAttribute('requestId')
            ]));
    }
}
