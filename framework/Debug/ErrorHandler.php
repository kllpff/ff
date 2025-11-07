<?php

namespace FF\Debug;

use FF\Log\Logger;

/**
 * ErrorHandler - Error Handler
 * 
 * Handles PHP errors and converts them to exceptions.
 * Logs errors and displays them appropriately based on environment.
 */
class ErrorHandler
{
    /**
     * The logger instance
     * 
     * @var Logger|null
     */
    protected ?Logger $logger = null;

    /**
     * Whether to throw exceptions on errors
     * 
     * @var bool
     */
    protected bool $throwExceptions = true;

    /**
     * The debug mode flag
     * 
     * @var bool
     */
    protected bool $debugMode = false;

    /**
     * Create a new ErrorHandler instance
     * 
     * @param Logger|null $logger The logger instance
     * @param bool $debugMode Whether in debug mode
     */
    public function __construct(?Logger $logger = null, bool $debugMode = false)
    {
        $this->logger = $logger;
        $this->debugMode = $debugMode;
    }

    /**
     * Register the error handler
     * 
     * @return void
     */
    public function register(): void
    {
        set_error_handler([$this, 'handle']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Handle a PHP error
     * 
     * @param int $level Error level
     * @param string $message Error message
     * @param string $file File where error occurred
     * @param int $line Line number
     * @return bool
     */
    public function handle(int $level, string $message, string $file = '', int $line = 0): bool
    {
        $severity = $this->getLevelName($level);

        // Log the error
        if ($this->logger) {
            $this->logger->error("$severity: $message in $file:$line", [
                'level' => $level,
                'file' => $file,
                'line' => $line,
                'request_id' => function_exists('request_id') ? request_id() : null,
            ]);
        }

        // In debug mode, throw exception
        if ($this->debugMode && $this->throwExceptions) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }

        // Don't execute PHP internal error handler
        return true;
    }

    /**
     * Handle fatal errors at shutdown
     * 
     * @return void
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if (!$error) {
            return;
        }

        // Handle fatal errors
        if ($error['type'] === E_ERROR || $error['type'] === E_CORE_ERROR) {
            $this->handle(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    /**
     * Get the name of an error level
     * 
     * @param int $level The error level
     * @return string The level name
     */
    protected function getLevelName(int $level): string
    {
        $levels = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ];

        return $levels[$level] ?? 'Unknown Error';
    }

    /**
     * Set whether to throw exceptions on errors
     * 
     * @param bool $throw
     * @return void
     */
    public function setThrowExceptions(bool $throw): void
    {
        $this->throwExceptions = $throw;
    }
}
