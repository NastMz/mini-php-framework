<?php
declare(strict_types=1);

namespace App\Infrastructure\Validation;

/**
 * FieldValidator
 *
 * Validates a single field against multiple rules.
 * Returns an error message if validation fails, or null if it passes.
 */
class FieldValidator
{
    /** @var ValidatorInterface[] */
    private array $rules = [];

    /**
     * Constructor.
     *
     * @param string $fieldName The name of the field being validated.
     */
    public function __construct(private string $fieldName) {}

    /**
     * Add a rule to apply on this field.
     */
    public function addRule(ValidatorInterface $rule): self
    {
        $this->rules[] = $rule;
        return $this;
    }

    /**
     * Validate a value: return error message or null if OK.
     */
    public function validate(mixed $value): ?string
    {
        foreach ($this->rules as $rule) {
            if (! $rule->validate($value)) {
                // Prefix error with field name
                return "{$this->fieldName}: {$rule->getErrorMessage()}";
            }
        }
        return null;
    }

    /**
     * Get the field’s name.
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }
}
