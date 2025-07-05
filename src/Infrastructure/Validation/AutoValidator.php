<?php
declare(strict_types=1);

namespace App\Infrastructure\Validation;

use App\Infrastructure\Validation\Attributes\Validate;
use App\Infrastructure\Validation\Attributes\Required;
use App\Infrastructure\Validation\Attributes\Email;
use App\Infrastructure\Validation\Attributes\MinLength;
use App\Infrastructure\Validation\Attributes\MaxLength;
use App\Infrastructure\Validation\Attributes\Numeric;
use App\Infrastructure\Validation\Attributes\In;
use App\Infrastructure\Http\RequestInterface;
use ReflectionMethod;
use ReflectionParameter;

/**
 * AutoValidator
 *
 * Automatically validates requests based on controller method attributes
 */
class AutoValidator
{
    /**
     * Validate request based on method attributes
     */
    public function validateMethod(RequestInterface $request, ReflectionMethod $method): ValidationResult
    {
        $errors = [];
        $data = $request->getParsedBody();

        // Check for method-level validation attributes
        $validateAttributes = $method->getAttributes(Validate::class);
        if (!empty($validateAttributes)) {
            $validate = $validateAttributes[0]->newInstance();
            $errors = array_merge($errors, $this->validateWithRules($data, $validate->rules, $validate->messages));
        }

        // Check parameter-level validation attributes
        foreach ($method->getParameters() as $parameter) {
            $paramName = $parameter->getName();
            
            // Skip framework parameters
            if (in_array($paramName, ['request', 'response'])) {
                continue;
            }

            $value = $data[$paramName] ?? null;
            $paramErrors = $this->validateParameter($parameter, $value);
            
            if (!empty($paramErrors)) {
                $errors[$paramName] = $paramErrors;
            }
        }

        return new ValidationResult(empty($errors), $errors);
    }

    /**
     * Validate a single parameter
     */
    private function validateParameter(ReflectionParameter $parameter, mixed $value): array
    {
        $errors = [];
        
        // Required validation
        $requiredAttributes = $parameter->getAttributes(Required::class);
        if (!empty($requiredAttributes)) {
            $required = $requiredAttributes[0]->newInstance();
            if (empty($value)) {
                $errors[] = $required->message;
                return $errors; // Stop validation if required field is empty
            }
        }

        // Skip other validations if value is empty and not required
        if (empty($value)) {
            return $errors;
        }

        // Email validation
        $emailAttributes = $parameter->getAttributes(Email::class);
        if (!empty($emailAttributes)) {
            $email = $emailAttributes[0]->newInstance();
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = $email->message;
            }
        }

        // MinLength validation
        $minLengthAttributes = $parameter->getAttributes(MinLength::class);
        if (!empty($minLengthAttributes)) {
            $minLength = $minLengthAttributes[0]->newInstance();
            if (strlen((string)$value) < $minLength->length) {
                $errors[] = str_replace('{length}', (string)$minLength->length, $minLength->message);
            }
        }

        // MaxLength validation
        $maxLengthAttributes = $parameter->getAttributes(MaxLength::class);
        if (!empty($maxLengthAttributes)) {
            $maxLength = $maxLengthAttributes[0]->newInstance();
            if (strlen((string)$value) > $maxLength->length) {
                $errors[] = str_replace('{length}', (string)$maxLength->length, $maxLength->message);
            }
        }

        // Numeric validation
        $numericAttributes = $parameter->getAttributes(Numeric::class);
        if (!empty($numericAttributes)) {
            $numeric = $numericAttributes[0]->newInstance();
            if (!is_numeric($value)) {
                $errors[] = $numeric->message;
            }
        }

        // In validation
        $inAttributes = $parameter->getAttributes(In::class);
        if (!empty($inAttributes)) {
            $in = $inAttributes[0]->newInstance();
            if (!in_array($value, $in->values, true)) {
                $errors[] = str_replace('{values}', implode(', ', $in->values), $in->message);
            }
        }

        return $errors;
    }

    /**
     * Validate with custom rules
     */
    private function validateWithRules(array $data, array $rules, array $messages): array
    {
        $errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $fieldErrors = [];
            
            $rulesArray = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);
            
            foreach ($rulesArray as $rule) {
                $result = $this->validateRule($value, $rule);
                if (!$result) {
                    $fieldErrors[] = $messages[$field] ?? "Field {$field} is invalid";
                }
            }
            
            if (!empty($fieldErrors)) {
                $errors[$field] = $fieldErrors;
            }
        }

        return $errors;
    }

    /**
     * Validate a single rule
     */
    private function validateRule(mixed $value, string $rule): bool
    {
        if ($rule === 'required') {
            return !empty($value);
        }
        
        if ($rule === 'email') {
            return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
        }
        
        if (str_starts_with($rule, 'min:')) {
            $min = (int)substr($rule, 4);
            return strlen((string)$value) >= $min;
        }
        
        if (str_starts_with($rule, 'max:')) {
            $max = (int)substr($rule, 4);
            return strlen((string)$value) <= $max;
        }
        
        return true; // Unknown rule passes
    }
}
