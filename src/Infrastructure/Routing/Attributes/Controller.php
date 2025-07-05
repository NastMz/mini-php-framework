<?php
declare(strict_types=1);

namespace App\Infrastructure\Routing\Attributes;

use Attribute;

/**
 * Controller attribute to define base route prefix and middleware
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Controller
{
    public function __construct(
        public readonly string $prefix = '',
        public readonly array $middleware = []
    ) {}
}
