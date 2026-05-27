<?php
/**
 * Validator Helper
 * 
 * Provides field validation rules and custom validation messages.
 */

namespace HRMS\Helpers;

use HRMS\Exceptions\ValidationException;

class Validator
{
    private array $data;
    private array $errors = [];
    private array $rules = [];
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    /**
     * Create a new validator instance
     */
    public static function make(array $data, array $rules): self
    {
        $validator = new self($data);
        $validator->rules = $rules;
        return $validator;
    }
    
    /**
     * Validate the data against rules
     */
    public function validate(): array
    {
        foreach ($this->rules as $field => $rules) {
            $ruleList = is_string($rules) ? explode('|', $rules) : $rules;
            $value = $this->data[$field] ?? null;
            
            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
        
        if (!empty($this->errors)) {
            throw new ValidationException($this->errors);
        }
        
        return $this->data;
    }
    
    /**
     * Check if validation passes
     */
    public function passes(): bool
    {
        try {
            $this->validate();
            return true;
        } catch (ValidationException $e) {
            return false;
        }
    }
    
    /**
     * Check if validation fails
     */
    public function fails(): bool
    {
        return !$this->passes();
    }
    
    /**
     * Get validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }
    
    /**
     * Apply a single rule
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        $params = [];
        
        if (strpos($rule, ':') !== false) {
            [$rule, $paramStr] = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }
        
        $method = 'validate' . ucfirst($rule);
        
        if (method_exists($this, $method)) {
            $this->$method($field, $value, $params);
        }
    }
    
    /**
     * Required validation
     */
    private function validateRequired(string $field, $value, array $params): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->errors[$field] = $this->formatFieldName($field) . ' is required';
        }
    }
    
    /**
     * Email validation
     */
    private function validateEmail(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Invalid email format';
        }
    }
    
    /**
     * Minimum length validation
     */
    private function validateMin(string $field, $value, array $params): void
    {
        $min = (int) ($params[0] ?? 0);
        
        if ($value !== null && $value !== '') {
            if (is_string($value) && strlen($value) < $min) {
                $this->errors[$field] = $this->formatFieldName($field) . " must be at least {$min} characters";
            } elseif (is_numeric($value) && $value < $min) {
                $this->errors[$field] = $this->formatFieldName($field) . " must be at least {$min}";
            }
        }
    }
    
    /**
     * Maximum length validation
     */
    private function validateMax(string $field, $value, array $params): void
    {
        $max = (int) ($params[0] ?? 0);
        
        if ($value !== null && $value !== '') {
            if (is_string($value) && strlen($value) > $max) {
                $this->errors[$field] = $this->formatFieldName($field) . " must not exceed {$max} characters";
            } elseif (is_numeric($value) && $value > $max) {
                $this->errors[$field] = $this->formatFieldName($field) . " must not exceed {$max}";
            }
        }
    }
    
    /**
     * Numeric validation
     */
    private function validateNumeric(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->errors[$field] = $this->formatFieldName($field) . ' must be a number';
        }
    }
    
    /**
     * Integer validation
     */
    private function validateInteger(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field] = $this->formatFieldName($field) . ' must be an integer';
        }
    }
    
    /**
     * Date validation (YYYY-MM-DD)
     */
    private function validateDate(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '') {
            $date = \DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                $this->errors[$field] = $this->formatFieldName($field) . ' must be a valid date (YYYY-MM-DD)';
            }
        }
    }
    
    /**
     * In array validation
     */
    private function validateIn(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !in_array($value, $params)) {
            $allowed = implode(', ', $params);
            $this->errors[$field] = $this->formatFieldName($field) . " must be one of: {$allowed}";
        }
    }
    
    /**
     * Boolean validation
     */
    private function validateBoolean(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !in_array($value, [true, false, 0, 1, '0', '1'], true)) {
            $this->errors[$field] = $this->formatFieldName($field) . ' must be true or false';
        }
    }
    
    /**
     * Array validation
     */
    private function validateArray(string $field, $value, array $params): void
    {
        if ($value !== null && !is_array($value)) {
            $this->errors[$field] = $this->formatFieldName($field) . ' must be an array';
        }
    }
    
    /**
     * String validation
     */
    private function validateString(string $field, $value, array $params): void
    {
        if ($value !== null && $value !== '' && !is_string($value)) {
            $this->errors[$field] = $this->formatFieldName($field) . ' must be a string';
        }
    }
    
    /**
     * Regex validation
     */
    private function validateRegex(string $field, $value, array $params): void
    {
        $pattern = $params[0] ?? '';
        
        if ($value !== null && $value !== '' && !preg_match($pattern, $value)) {
            $this->errors[$field] = $this->formatFieldName($field) . ' format is invalid';
        }
    }
    
    /**
     * Format field name for display
     */
    private function formatFieldName(string $field): string
    {
        return ucfirst(str_replace('_', ' ', $field));
    }
}
