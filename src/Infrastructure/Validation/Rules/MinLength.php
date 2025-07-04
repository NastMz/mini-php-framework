<?php
declare(strict_types=1);

namespace App\Infrastructure\Validation\Rules;

use App\Infrastructure\Validation\ValidatorInterface;

/**
 * MinLength
 *
 * Validator that checks if a string has a minimum length.
 * Returns true if the string length is greater than or equal to the specified minimum.
 */
class MinLength implements ValidatorInterface
{
    /**
     * Constructor.
     *
     * @param int $min The minimum length the string must have.
     */
    public function __construct(private int $min) {}

    /**
     * Validate the value against the minimum length.
     *
     * @param mixed $value The value to validate.
     * @return bool True if the value is a string and meets the minimum length, false otherwise.
     */
    public function validate(mixed $value): bool
    {
        return is_string($value) && mb_strlen($value) >= $this->min;
    }

    /**
     * Error message when validate() returns false.
     *
     * @return string The error message indicating the minimum length required.
     */
    public function getErrorMessage(): string
    {
        return "minimum length is {$this->min}";
    }
}
