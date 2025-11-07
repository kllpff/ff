<?php

namespace FF\Exceptions;

use Exception;
use Throwable;

/**
 * DatabaseException - Custom exception for database-related errors
 */
class DatabaseException extends Exception
{
    public function __construct(string $message = 'Database error occurred', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}