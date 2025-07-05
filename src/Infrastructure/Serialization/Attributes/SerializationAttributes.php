<?php
declare(strict_types=1);

namespace App\Infrastructure\Serialization\Attributes;

use Attribute;

/**
 * JsonSerializable attribute for automatic JSON serialization
 */
#[Attribute(Attribute::TARGET_CLASS)]
class JsonSerializable
{
    public function __construct(
        public readonly array $exclude = [],
        public readonly array $include = [],
        public readonly bool $camelCase = false
    ) {}
}

/**
 * JsonProperty attribute for property-level serialization control
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonProperty
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly bool $exclude = false,
        public readonly ?string $format = null
    ) {}
}

/**
 * JsonIgnore attribute to exclude properties from serialization
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonIgnore
{
    public function __construct(
        public readonly bool $serialize = true,
        public readonly bool $deserialize = true
    ) {}
}
