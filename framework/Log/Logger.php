<?php

namespace FF\Framework\Log;

use DateTime;

/**
 * Logger - Application Logger
 * 
 * Logs messages at different levels (debug, info, notice, warning, error, critical, alert, emergency).
 * Supports file-based logging with configurable verbosity.
 */
class Logger
{
    /**
     * Log levels
     */
    public const DEBUG = 0;
    public const INFO = 1;
    public const NOTICE = 2;
    public const WARNING = 3;
    public const ERROR = 4;
    public const CRITICAL = 5;
    public const ALERT = 6;
    public const EMERGENCY = 7;

    /**
     * Level names
     * 
     * @var array
     */
    protected static array $levels = [
        self::DEBUG => 'DEBUG',
        self::INFO => 'INFO',
        self::NOTICE => 'NOTICE',
        self::WARNING => 'WARNING',
        self::ERROR => 'ERROR',
        self::CRITICAL => 'CRITICAL',
        self::ALERT => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    ];

    /**
     * The log file path
     * 
     * @var string
     */
    protected string $logFile;

    /**
     * Minimum log level to record
     * 
     * @var int
     */
    protected int $minLevel;

    /**
     * Create a new Logger instance
     * 
     * @param string $logFile The log file path
     * @param int $minLevel Minimum level to log
     */
    public function __construct(string $logFile, int $minLevel = self::DEBUG)
    {
        $this->logFile = $logFile;
        $this->minLevel = $minLevel;

        // Create log directory if it doesn't exist
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Log a debug message
     * 
     * @param string $message The message
     * @param array $context Context data
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Log an info message
     * 
     * @param string $message The message
     * @param array $context Context data
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Log a notice message
     * 
     * @param string $message The message
     * @param array $context Context data
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Log a warning message
     * 
     * @param string $message The message
     * @param array $context Context data
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Log an error message
     * 
     * @param string $message The message
     * @param array $context Context data
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Log a critical message
     * 
     * @param string $message The message
     * @param array $context Context data
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Log an alert message
     * 
     * @param string $message The message
     * @param array $context Context data
     * @return void
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * Log an emergency message
     * 
     * @param string $message The message
     * @param array $context Context data
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * Log a message at the specified level
     * 
     * @param int $level The log level
     * @param string $message The message
     * @param array $context Context data
     * @return void
     */
    public function log(int $level, string $message, array $context = []): void
    {
        // Check if level meets minimum threshold
        if ($level < $this->minLevel) {
            return;
        }

        $levelName = self::$levels[$level] ?? 'UNKNOWN';
        $timestamp = (new DateTime())->format('Y-m-d H:i:s');

        // Format log entry
        $logEntry = "[$timestamp] $levelName: $message";

        if (!empty($context)) {
            $logEntry .= " " . json_encode($context);
        }

        $logEntry .= "\n";

        // Write to log file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get the current log file
     * 
     * @return string
     */
    public function getLogFile(): string
    {
        return $this->logFile;
    }

    /**
     * Set the minimum log level
     * 
     * @param int $level The minimum level
     * @return self
     */
    public function setMinLevel(int $level): self
    {
        $this->minLevel = $level;
        return $this;
    }

    /**
     * Clear the log file
     * 
     * @return void
     */
    public function clear(): void
    {
        file_put_contents($this->logFile, '');
    }

    /**
     * Get recent log entries
     * 
     * @param int $lines Number of lines to retrieve
     * @return array
     */
    public function tail(int $lines = 20): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $handle = fopen($this->logFile, 'r');
        $entries = [];
        $buffer = '';

        fseek($handle, 0, SEEK_END);
        $size = ftell($handle);
        $position = $size;

        while ($position >= 0 && count($entries) < $lines) {
            $chunkSize = min(1024, $position);
            $position -= $chunkSize;

            fseek($handle, $position);
            $buffer = fread($handle, $chunkSize) . $buffer;

            $lines_found = explode("\n", $buffer);
            if (count($lines_found) > $lines) {
                $entries = array_slice($lines_found, -$lines);
                break;
            }
        }

        fclose($handle);

        return array_filter(array_reverse($entries), fn($l) => !empty($l));
    }
}
