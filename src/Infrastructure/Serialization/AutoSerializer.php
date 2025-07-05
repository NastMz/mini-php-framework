<?php
declare(strict_types=1);

namespace App\Infrastructure\Serialization;

use App\Infrastructure\Serialization\Attributes\JsonSerializable;
use App\Infrastructure\Serialization\Attributes\JsonProperty;
use App\Infrastructure\Serialization\Attributes\JsonIgnore;
use ReflectionClass;
use ReflectionProperty;

/**
 * AutoSerializer
 *
 * Automatically serializes objects based on attributes
 */
class AutoSerializer
{
    /**
     * Serialize an object to array
     */
    public function toArray(object $object): array
    {
        $reflection = new ReflectionClass($object);
        $result = [];

        // Get class-level serialization attributes
        $classAttributes = $reflection->getAttributes(JsonSerializable::class);
        $classConfig = null;
        
        if (!empty($classAttributes)) {
            $classConfig = $classAttributes[0]->newInstance();
        }

        // Process each property
        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();
            
            // Skip if property is in exclude list
            if ($classConfig && in_array($propertyName, $classConfig->exclude)) {
                continue;
            }

            // Skip if include list is specified and property is not in it
            if ($classConfig && !empty($classConfig->include) && !in_array($propertyName, $classConfig->include)) {
                continue;
            }

            // Check property-level attributes
            $jsonIgnoreAttributes = $property->getAttributes(JsonIgnore::class);
            if (!empty($jsonIgnoreAttributes)) {
                $jsonIgnore = $jsonIgnoreAttributes[0]->newInstance();
                if ($jsonIgnore->serialize) {
                    continue;
                }
            }

            // Get property value
            $property->setAccessible(true);
            $value = $property->getValue($object);

            // Check for JsonProperty attribute
            $jsonPropertyAttributes = $property->getAttributes(JsonProperty::class);
            if (!empty($jsonPropertyAttributes)) {
                $jsonProperty = $jsonPropertyAttributes[0]->newInstance();
                
                if ($jsonProperty->exclude) {
                    continue;
                }
                
                $serializedName = $jsonProperty->name ?? $propertyName;
                
                // Apply format if specified
                if ($jsonProperty->format && $value !== null) {
                    $value = $this->formatValue($value, $jsonProperty->format);
                }
            } else {
                $serializedName = $propertyName;
            }

            // Apply camelCase conversion if specified
            if ($classConfig && $classConfig->camelCase) {
                $serializedName = $this->toCamelCase($serializedName);
            }

            // Handle nested objects
            if (is_object($value)) {
                $value = $this->toArray($value);
            } elseif (is_array($value)) {
                $value = $this->serializeArray($value);
            }

            $result[$serializedName] = $value;
        }

        return $result;
    }

    /**
     * Serialize to JSON string
     */
    public function toJson(object $object, int $flags = 0): string
    {
        return json_encode($this->toArray($object), $flags);
    }

    /**
     * Format value based on format string
     */
    private function formatValue(mixed $value, string $format): mixed
    {
        return match ($format) {
            'date' => $value instanceof \DateTime ? $value->format('Y-m-d') : $value,
            'datetime' => $value instanceof \DateTime ? $value->format('Y-m-d H:i:s') : $value,
            'iso8601' => $value instanceof \DateTime ? $value->format('c') : $value,
            'timestamp' => $value instanceof \DateTime ? $value->getTimestamp() : $value,
            default => $value
        };
    }

    /**
     * Convert snake_case to camelCase
     */
    private function toCamelCase(string $string): string
    {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }

    /**
     * Serialize array values
     */
    private function serializeArray(array $array): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            if (is_object($value)) {
                $result[$key] = $this->toArray($value);
            } elseif (is_array($value)) {
                $result[$key] = $this->serializeArray($value);
            } else {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
}
