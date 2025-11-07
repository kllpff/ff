<?php

namespace FF\Validation;

/**
 * Validator - Data Validation
 * 
 * Validates data against rules. Supports various validation rules
 * like required, email, min, max, regex, etc.
 */
class Validator
{
    /**
     * The data to validate
     * 
     * @var array
     */
    protected array $data = [];

    /**
     * The validation rules
     * 
     * @var array
     */
    protected array $rules = [];

    /**
     * Validation errors
     * 
     * @var array
     */
    protected array $errors = [];

    /**
     * Error messages
     * 
     * @var array
     */
    protected array $messages = [];

    /**
     * Create a new Validator instance
     * 
     * @param array $data The data to validate
     * @param array $rules The validation rules
     * @param array $messages Custom error messages
     */
    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = $messages;
    }

    /**
     * Validate the data
     * 
     * @return bool True if all validations pass
     */
    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $rules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;

            foreach ($rules as $rule) {
                $this->validateField($field, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Validate a single field against a rule
     * 
     * @param string $field The field name
     * @param string $rule The rule string
     * @return void
     */
    protected function validateField(string $field, string $rule): void
    {
        // Parse rule (e.g., "min:5", "regex:/pattern/")
        $ruleName = $rule;
        $ruleParams = [];

        if (strpos($rule, ':') !== false) {
            [$ruleName, $params] = explode(':', $rule, 2);
            $ruleParams = explode(',', $params);
        }

        $method = 'validate' . ucfirst($ruleName);

        if (method_exists($this, $method)) {
            $value = $this->data[$field] ?? null;

            if ($ruleName !== 'required' && !$this->hasValue($value)) {
                return;
            }

            if (!$this->$method($field, $value, $ruleParams)) {
                $this->addError($field, $ruleName, $ruleParams);
            }
        }
    }

    /**
     * Validate required rule
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters
     * @return bool
     */
    protected function validateRequired(string $field, $value, array $params): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return !empty($value);
        }

        return $value !== false && $value !== '';
    }

    /**
     * Validate email rule
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters
     * @return bool
     */
    protected function validateEmail(string $field, $value, array $params): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate min rule
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters
     * @return bool
     */
    protected function validateMin(string $field, $value, array $params): bool
    {
        $min = (int)($params[0] ?? 0);
        if (is_numeric($value)) {
            return (float)$value >= $min;
        }

        if (is_countable($value)) {
            return count($value) >= $min;
        }

        return $this->stringLength((string)$value) >= $min;
    }

    /**
     * Validate max rule
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters
     * @return bool
     */
    protected function validateMax(string $field, $value, array $params): bool
    {
        $max = (int)($params[0] ?? 0);
        // File size in KB when validating uploaded files
        if ($value instanceof \FF\Http\UploadedFile) {
            return $value->getSize() <= ($max * 1024);
        }
        if (is_numeric($value)) {
            return (float)$value <= $max;
        }

        if (is_countable($value)) {
            return count($value) <= $max;
        }

        return $this->stringLength((string)$value) <= $max;
    }

    /**
     * Validate regex rule
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters
     * @return bool
     */
    protected function validateRegex(string $field, $value, array $params): bool
    {
        $pattern = $params[0] ?? '';
        if ($pattern === '') {
            return false;
        }

        $previousBacktrack = ini_get('pcre.backtrack_limit');
        $previousRecursion = ini_get('pcre.recursion_limit');

        ini_set('pcre.backtrack_limit', '1000000');
        ini_set('pcre.recursion_limit', '1000000');

        $result = @preg_match($pattern, (string)$value);
        $error = preg_last_error();

        if ($previousBacktrack !== false) {
            ini_set('pcre.backtrack_limit', (string)$previousBacktrack);
        }
        if ($previousRecursion !== false) {
            ini_set('pcre.recursion_limit', (string)$previousRecursion);
        }

        return $result === 1 && $error === PREG_NO_ERROR;
    }

    /**
     * Validate url rule
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters
     * @return bool
     */
    protected function validateUrl(string $field, $value, array $params): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate integer rule
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters
     * @return bool
     */
    protected function validateInteger(string $field, $value, array $params): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate numeric rule
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters
     * @return bool
     */
    protected function validateNumeric(string $field, $value, array $params): bool
    {
        return is_numeric($value);
    }

    /**
     * Validate that value is an UploadedFile and is valid
     */
    protected function validateFile(string $field, $value, array $params): bool
    {
        if (!$value instanceof \FF\Http\UploadedFile) {
            return false;
        }
        return $value->isValid();
    }

    /**
     * Validate that uploaded file extension is allowed
     */
    protected function validateMimes(string $field, $value, array $params): bool
    {
        if (!$value instanceof \FF\Http\UploadedFile) {
            return false;
        }
        $allowed = array_map(static function ($ext) {
            return strtolower(trim((string)$ext));
        }, $params);
        return in_array($value->getClientExtension(), $allowed, true);
    }

    /**
     * Validate accepted rule (for checkboxes)
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters
     * @return bool
     */
    protected function validateAccepted(string $field, $value, array $params): bool
    {
        return in_array($value, ['yes', '1', 'true', 'on'], true);
    }

    /**
     * Validate in rule
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters
     * @return bool
     */
    protected function validateIn(string $field, $value, array $params): bool
    {
        return in_array($value, $params, true);
    }

    /**
     * Validate not_in rule
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters
     * @return bool
     */
    protected function validateNotIn(string $field, $value, array $params): bool
    {
        return !in_array($value, $params, true);
    }

    /**
     * Validate confirmed rule (for password confirmation)
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters
     * @return bool
     */
    protected function validateConfirmed(string $field, $value, array $params): bool
    {
        $confirmField = $field . '_confirmation';
        return isset($this->data[$confirmField]) && $this->data[$confirmField] === $value;
    }

    /**
     * Validate unique rule (for database uniqueness)
     * 
     * @param string $field The field
     * @param mixed $value The value
     * @param array $params Rule parameters (table, column)
     * @return bool
     */
    protected function validateUnique(string $field, $value, array $params): bool
    {
        // This will be fully implemented with database checking
        // For now, assume unique
        return true;
    }

    /**
     * Add validation error
     * 
     * @param string $field The field
     * @param string $rule The rule
     * @param array $params Rule parameters
     * @return void
     */
    protected function addError(string $field, string $rule, array $params): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        // Get custom message or generate default
        $messageKey = "{$field}.{$rule}";
        $message = $this->messages[$messageKey] ?? $this->getDefaultMessage($field, $rule, $params);

        $this->errors[$field][] = $message;
    }

    /**
     * Get default error message for a rule
     * 
     * @param string $field The field
     * @param string $rule The rule
     * @param array $params Rule parameters
     * @return string
     */
    protected function getDefaultMessage(string $field, string $rule, array $params): string
    {
        $messages = [
            'required' => "The {$field} field is required.",
            'email' => "The {$field} must be a valid email address.",
            'min' => "The {$field} must be at least {$params[0]} characters.",
            'max' => "The {$field} must not exceed {$params[0]} characters.",
            'url' => "The {$field} must be a valid URL.",
            'integer' => "The {$field} must be an integer.",
            'numeric' => "The {$field} must be numeric.",
            'regex' => "The {$field} format is invalid.",
            'in' => "The {$field} value is not acceptable.",
            'confirmed' => "The {$field} confirmation does not match.",
            'unique' => "The {$field} has already been taken.",
            'file' => "The {$field} must be a valid uploaded file.",
            'mimes' => "The {$field} must be a file of type: " . implode(', ', $params) . ".",
        ];

        return $messages[$rule] ?? "The {$field} validation failed for rule: {$rule}";
    }

    /**
     * Get all validation errors
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get errors for a specific field
     * 
     * @param string $field The field name
     * @return array
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Check if there are validation errors
     * 
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get first error message
     * 
     * @return string|null
     */
    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }

    /**
     * Determine if a value is considered present (non-empty).
     */
    protected function hasValue($value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return !empty($value);
        }

        return true;
    }

    /**
     * Get the length of a string using multibyte support when available.
     */
    protected function stringLength(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value, 'UTF-8');
        }

        return strlen($value);
    }
}
