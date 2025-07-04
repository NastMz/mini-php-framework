<?php
declare(strict_types=1);

namespace App\Infrastructure\Validation;

/**
 * ValidatorInterface
 *
 * Interface for validation rules.
 */
interface ValidatorInterface
{
    /**
     * Return true if the value passes the rule.
     */
    public function validate(mixed $value): bool;

    /**
     * Error message when validate() returns false.
     */
    public function getErrorMessage(): string;
}
