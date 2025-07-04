<?php
declare(strict_types=1);

namespace App\Infrastructure\Validation;

use RuntimeException;

/**
 * ValidationException
 *
 * Exception thrown when validation fails.
 * Contains field-level error messages.
 */
class ValidationException extends RuntimeException
{
    /**
     * @param string[] $errors  List of error messages
     */
    public function __construct(private array $errors)
    {
        parent::__construct('Validation failed');
    }

    /**
     * @return string[]  Field-level error messages
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
