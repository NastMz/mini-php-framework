<?php
declare(strict_types=1);

namespace App\Infrastructure\Validation;

/**
 * ValidationResult
 *
 * Represents the result of a validation operation
 */
class ValidationResult
{
    public function __construct(
        public readonly bool $isValid,
        public readonly array $errors = []
    ) {}

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return $this->isValid;
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !$this->isValid;
    }

    /**
     * Get all error messages
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get errors for a specific field
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Get first error message
     */
    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (is_array($fieldErrors) && !empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }
}
