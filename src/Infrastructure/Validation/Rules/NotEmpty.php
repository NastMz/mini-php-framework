<?php
declare(strict_types=1);

namespace App\Infrastructure\Validation\Rules;

use App\Infrastructure\Validation\ValidatorInterface;

/**
 * NotEmpty
 *
 * Validator that checks if a value is not empty.
 * Allows "0" but not empty strings, null, or empty arrays.
 */
class NotEmpty implements ValidatorInterface
{
    /**
     * Constructor.
     *
     * @param string $fieldName The name of the field being validated.
     */
    public function validate(mixed $value): bool
    {
        // Allow "0", but not empty strings/null/empty arrays
        return ! (empty($value) && $value !== '0');
    }

    /**
     * Error message when validate() returns false.
     *
     * @return string The error message.
     */
    public function getErrorMessage(): string
    {
        return 'must not be empty';
    }
}
