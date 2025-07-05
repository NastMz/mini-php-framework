<?php
declare(strict_types=1);

namespace App\Infrastructure\Validation\Attributes;

use Attribute;

/**
 * Validate attribute for automatic request validation
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Validate
{
    public function __construct(
        public readonly array $rules = [],
        public readonly array $messages = []
    ) {}
}

/**
 * Required field validation
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Required
{
    public function __construct(
        public readonly string $message = 'This field is required'
    ) {}
}

/**
 * Email validation
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Email
{
    public function __construct(
        public readonly string $message = 'Must be a valid email address'
    ) {}
}

/**
 * Min length validation
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class MinLength
{
    public function __construct(
        public readonly int $length,
        public readonly string $message = 'Must be at least {length} characters long'
    ) {}
}

/**
 * Max length validation
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class MaxLength
{
    public function __construct(
        public readonly int $length,
        public readonly string $message = 'Must be at most {length} characters long'
    ) {}
}

/**
 * Numeric validation
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Numeric
{
    public function __construct(
        public readonly string $message = 'Must be a valid number'
    ) {}
}

/**
 * In array validation
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class In
{
    public function __construct(
        public readonly array $values,
        public readonly string $message = 'Must be one of: {values}'
    ) {}
}
