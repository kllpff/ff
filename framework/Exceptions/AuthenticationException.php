<?php

namespace FF\Exceptions;

use Exception;
use Throwable;

/**
 * AuthenticationException - Custom exception for authentication errors
 */
class AuthenticationException extends Exception
{
    public function __construct(string $message = 'Authentication error occurred', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}