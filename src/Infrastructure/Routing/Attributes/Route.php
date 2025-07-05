<?php
declare(strict_types=1);

namespace App\Infrastructure\Routing\Attributes;

use Attribute;
use App\Infrastructure\Routing\HttpMethod;

/**
 * Route Attribute
 *
 * Allows defining routes directly on controller methods using PHP 8 attributes.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(
        public readonly HttpMethod $method,
        public readonly string $path,
        public readonly ?string $name = null,
        public readonly array $middleware = [],
        public readonly array $where = []
    ) {
        if (!str_starts_with($path, '/')) {
            throw new \InvalidArgumentException("Route path must start with '/': {$path}");
        }
    }
}
