<?php
declare(strict_types=1);

namespace App\Infrastructure\DI\Exception;

use RuntimeException;

/**
 * DependencyResolutionException
 *
 * Thrown when the auto-registration system cannot resolve a dependency.
 */
class DependencyResolutionException extends RuntimeException
{
    public static function cannotResolveType(string $typeName, string $className): self
    {
        return new self("Cannot resolve dependency {$typeName} for {$className}");
    }

    public static function cannotResolvePrimitive(string $className): self
    {
        return new self("Cannot resolve primitive dependency for {$className}");
    }
}
