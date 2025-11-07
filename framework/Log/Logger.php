<?php

namespace FF\Log;

use DateTime;

/**
 * Logger - Application Logger
 *
 * Provides simple file-based logging with log-level filtering and
 * protections against log injection and directory traversal attacks.
 */
class Logger
{
    public const DEBUG = 0;
    public const INFO = 1;
    public const NOTICE = 2;
    public const WARNING = 3;
    public const ERROR = 4;
    public const CRITICAL = 5;
    public const ALERT = 6;
    public const EMERGENCY = 7;

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

    protected string $logFile;
    protected int $minLevel;
    protected string $baseDirectory;

    /**
     * @param string $logFile    Desired log file (relative to base logs directory).
     * @param int    $minLevel   Minimum severity level to record.
     * @param string|null $baseDirectory Optional base directory for logs.
     */
    public function __construct(string $logFile, int $minLevel = self::DEBUG, ?string $baseDirectory = null)
    {
        $this->baseDirectory = $baseDirectory ?: $this->defaultBaseDirectory();
        $this->logFile = $this->resolveLogPath($logFile);
        $this->minLevel = $minLevel;

        $this->ensureDirectory(dirname($this->logFile));
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log(self::DEBUG, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(self::INFO, $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log(self::NOTICE, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(self::WARNING, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(self::ERROR, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log(self::ALERT, $message, $context);
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    public function log(int $level, string $message, array $context = []): void
    {
        if ($level < $this->minLevel) {
            return;
        }

        $levelName = self::$levels[$level] ?? 'UNKNOWN';
        $timestamp = (new DateTime())->format('Y-m-d H:i:s');

        $message = $this->sanitizeMessage($message);
        $context = $this->sanitizeContext($context);

        $logEntry = "[$timestamp] $levelName: $message";

        if (!empty($context)) {
            $encoded = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($encoded !== false) {
                $logEntry .= ' ' . $encoded;
            }
        }

        $logEntry .= PHP_EOL;

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    public function getLogFile(): string
    {
        return $this->logFile;
    }

    public function setMinLevel(int $level): self
    {
        $this->minLevel = $level;
        return $this;
    }

    public function clear(): void
    {
        file_put_contents($this->logFile, '');
    }

    public function tail(int $lines = 20): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $handle = fopen($this->logFile, 'r');
        if (!$handle) {
            return [];
        }

        $entries = [];
        $buffer = '';

        fseek($handle, 0, SEEK_END);
        $position = ftell($handle);

        while ($position > 0 && count($entries) < $lines) {
            $chunk = min(1024, $position);
            $position -= $chunk;
            fseek($handle, $position);
            $buffer = fread($handle, $chunk) . $buffer;
            $parts = explode(PHP_EOL, $buffer);

            if (count($parts) > $lines) {
                $entries = array_slice($parts, -$lines);
                break;
            }
        }

        fclose($handle);

        if (empty($entries)) {
            $entries = array_filter(explode(PHP_EOL, $buffer));
        }

        return array_values(array_filter(array_reverse($entries), fn ($line) => $line !== ''));
    }

    protected function defaultBaseDirectory(): string
    {
        if (defined('BASE_PATH')) {
            return rtrim(BASE_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        }

        return sys_get_temp_dir();
    }

    protected function resolveLogPath(string $logFile): string
    {
        $logFile = trim($logFile);
        if ($logFile === '') {
            $logFile = 'app.log';
        }

        if ($this->isAbsolutePath($logFile)) {
            $normalized = $this->normalizePath($logFile);
        } else {
            $relative = $this->normalizeRelativePath($logFile);
            $normalized = $this->normalizePath($this->baseDirectory . DIRECTORY_SEPARATOR . $relative);
        }

        if (!$this->pathWithinBase($normalized)) {
            throw new \InvalidArgumentException('Log file must reside within the logs directory.');
        }

        return $normalized;
    }

    protected function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (!$this->pathWithinBase($directory)) {
            throw new \InvalidArgumentException('Log directory must reside within the logs directory.');
        }
    }

    protected function sanitizeMessage(string $message): string
    {
        $message = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/', ' ', $message);
        $message = str_replace(["\r", "\n"], ' ', $message);

        return trim($message);
    }

    protected function sanitizeContext(array $context): array
    {
        $sanitized = [];

        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeContext($value);
                continue;
            }

            if ($value instanceof \JsonSerializable) {
                $sanitized[$key] = $this->sanitizeContext((array)$value->jsonSerialize());
                continue;
            }

            if ($value instanceof \Stringable) {
                $sanitized[$key] = $this->sanitizeMessage((string)$value);
                continue;
            }

            if ($value === null || is_scalar($value)) {
                $sanitized[$key] = is_string($value) ? $this->sanitizeMessage($value) : $value;
                continue;
            }

            if (is_object($value) && method_exists($value, '__toString')) {
                $sanitized[$key] = $this->sanitizeMessage((string)$value);
                continue;
            }

            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $sanitized[$key] = $this->sanitizeMessage($encoded !== false ? $encoded : gettype($value));
        }

        return $sanitized;
    }

    protected function normalizePath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $segments = [];
        $prefix = '';

        if (preg_match('/^[A-Za-z]:/', $path)) {
            $prefix = strtoupper(substr($path, 0, 2));
            $path = substr($path, 2);
        }

        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $prefix .= DIRECTORY_SEPARATOR;
        }

        foreach (explode(DIRECTORY_SEPARATOR, $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);
                continue;
            }

            if (preg_match('/[\x00]/', $segment)) {
                continue;
            }

            $segments[] = $segment;
        }

        $normalized = $prefix . implode(DIRECTORY_SEPARATOR, $segments);
        return $normalized !== '' ? $normalized : ($prefix !== '' ? $prefix : '.');
    }

    protected function normalizeRelativePath(string $path): string
    {
        $normalized = $this->normalizePath($path);
        $normalized = ltrim($normalized, DIRECTORY_SEPARATOR);
        return $normalized !== '' ? $normalized : 'app.log';
    }

    protected function pathWithinBase(string $path): bool
    {
        $base = $this->normalizePath($this->baseDirectory);
        $normalized = $this->normalizePath($path);

        $baseCanonical = str_replace('\\', '/', strtolower(rtrim($base, DIRECTORY_SEPARATOR)));
        $pathCanonical = str_replace('\\', '/', strtolower($normalized));

        return str_starts_with($pathCanonical, $baseCanonical);
    }

    protected function isAbsolutePath(string $path): bool
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            return true;
        }

        return (bool)preg_match('/^[A-Za-z]:[\\\\\\/]/', $path);
    }
}
