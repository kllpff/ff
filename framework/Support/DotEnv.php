<?php

namespace FF\Support;

/**
 * Simple .env file parser
 * Loads environment variables from .env file into $_ENV and $_SERVER
 */
class DotEnv
{
    private string $path;
    private bool $immutable = false;

    public function __construct(string $path, bool $immutable = false)
    {
        $this->path = $path;
        $this->immutable = $immutable;
    }

    /**
     * Create an immutable instance
     */
    public static function createImmutable(string $path): self
    {
        return new self($path, true);
    }

    /**
     * Create a mutable instance
     */
    public static function create(string $path): self
    {
        return new self($path, false);
    }

    /**
     * Load .env file (throws exception if file doesn't exist)
     */
    public function load(): void
    {
        $filePath = $this->path . '/.env';

        if (!file_exists($filePath)) {
            throw new \RuntimeException("Environment file not found: {$filePath}");
        }

        $this->parse($filePath);
    }

    /**
     * Safely load .env file (doesn't throw if file doesn't exist)
     */
    public function safeLoad(): void
    {
        $filePath = $this->path . '/.env';

        if (!file_exists($filePath)) {
            return;
        }

        $this->parse($filePath);
    }

    /**
     * Parse .env file and load variables
     */
    private function parse(string $filePath): void
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            // Skip comments and empty lines
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            // Skip lines without equals sign
            if (strpos($line, '=') === false) {
                continue;
            }

            [$name, $value] = $this->parseLine($line);

            if ($name === null || $name === '') {
                continue;
            }

            $this->setVariable($name, $value);
        }
    }

    /**
     * Parse a single line into name and value
     */
    private function parseLine(string $line): array
    {
        $parts = explode('=', $line, 2);

        if (count($parts) !== 2) {
            return [null, null];
        }

        $name = trim($parts[0]);
        $value = trim($parts[1]);

        // Remove quotes if present (matching pairs)
        $len = strlen($value);
        if ($len >= 2) {
            $first = $value[0];
            $last = $value[$len - 1];

            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        // Handle escape sequences
        $value = $this->unescapeValue($value);

        return [$name, $value];
    }

    /**
     * Unescape special characters
     */
    private function unescapeValue(string $value): string
    {
        $value = str_replace('\\n', "\n", $value);
        $value = str_replace('\\r', "\r", $value);
        $value = str_replace('\\t', "\t", $value);
        $value = str_replace('\\"', '"', $value);
        $value = str_replace("\\'", "'", $value);
        $value = str_replace('\\\\', '\\', $value);

        return $value;
    }

    /**
     * Set environment variable
     */
    private function setVariable(string $name, string $value): void
    {
        // Set in $_ENV
        $_ENV[$name] = $value;

        // Set in $_SERVER
        $_SERVER[$name] = $value;

        // Set using putenv (for compatibility with getenv())
        putenv("{$name}={$value}");
    }

    /**
     * Check if variable is already set
     */
    private function isSet(string $name): bool
    {
        return isset($_ENV[$name]) || isset($_SERVER[$name]) || getenv($name) !== false;
    }
}
