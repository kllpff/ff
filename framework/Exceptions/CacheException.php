<?php

namespace FF\Exceptions;

use Exception;
use Throwable;

/**
 * CacheException - Custom exception for cache-related errors
 */
class CacheException extends Exception
{
    public function __construct(string $message = 'Cache error occurred', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}