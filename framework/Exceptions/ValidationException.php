<?php

namespace FF\Exceptions;

use Exception;
use Throwable;

/**
 * ValidationException - Custom exception for validation errors
 */
class ValidationException extends Exception
{
    protected array $errors;

    public function __construct(array $errors, string $message = 'Validation failed', int $code = 0, ?Throwable $previous = null)
    {
        $this->errors = $errors;
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}