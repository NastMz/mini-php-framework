<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Serialization\AutoSerializer;
use App\Infrastructure\Serialization\Attributes\JsonSerializable;
use ReflectionClass;

/**
 * AutoSerializationMiddleware
 *
 * Automatically serializes response objects based on attributes
 */
class AutoSerializationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private AutoSerializer $serializer
    ) {}

    /**
     * Process the request and auto-serialize response objects
     */
    public function process(RequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $response = $next->handle($request);

        // Get response body
        $body = $response->getBody();
        
        if (empty($body)) {
            return $response;
        }

        // Try to decode as JSON first
        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Not JSON, return as-is
            return $response;
        }

        // Look for objects that need serialization
        $serializedData = $this->serializeData($data);

        // If data changed, update response
        if ($serializedData !== $data) {
            $response = $response->withHeader('Content-Type', 'application/json')
                                ->write(json_encode($serializedData));
        }

        return $response;
    }

    /**
     * Recursively serialize data
     */
    private function serializeData(mixed $data): mixed
    {
        if (is_object($data)) {
            return $this->serializeObject($data);
        }

        if (is_array($data)) {
            return array_map([$this, 'serializeData'], $data);
        }

        return $data;
    }

    /**
     * Serialize a single object
     */
    private function serializeObject(object $object): mixed
    {
        // Check if object has JsonSerializable attribute
        $reflection = new ReflectionClass($object);
        $attributes = $reflection->getAttributes(JsonSerializable::class);
        
        if (!empty($attributes)) {
            return $this->serializer->toArray($object);
        }

        // If no attribute, try to convert to array anyway
        if (method_exists($object, 'toArray')) {
            return $object->toArray();
        }

        // Convert to array using reflection
        return $this->serializer->toArray($object);
    }
}
