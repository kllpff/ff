<?php

namespace FF\Framework\Security;

/**
 * Sanitizer - Input Sanitization
 * 
 * Provides methods for sanitizing user input to prevent XSS attacks
 * and other injection vulnerabilities.
 */
class Sanitizer
{
    /**
     * Sanitize a string value
     * Removes HTML tags and encodes special characters.
     * 
     * @param string $value The value to sanitize
     * @return string The sanitized value
     */
    public static function string(string $value): string
    {
        return htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize an email address
     * 
     * @param string $email The email to sanitize
     * @return string The sanitized email
     */
    public static function email(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize a URL
     * 
     * @param string $url The URL to sanitize
     * @return string The sanitized URL
     */
    public static function url(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * Sanitize an integer value
     * 
     * @param mixed $value The value to sanitize
     * @return int The sanitized integer
     */
    public static function int($value): int
    {
        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize a float value
     * 
     * @param mixed $value The value to sanitize
     * @return float The sanitized float
     */
    public static function float($value): float
    {
        return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Escape HTML special characters
     * 
     * @param string $value The value to escape
     * @return string The escaped value
     */
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize an array recursively
     * 
     * @param array $data The array to sanitize
     * @return array The sanitized array
     */
    public static function array(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::array($value);
            } else if (is_string($value)) {
                $sanitized[$key] = self::string($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Check if a value looks like an SQL injection attempt
     * 
     * @param string $value The value to check
     * @return bool True if potentially dangerous
     */
    public static function isSuspiciousSql(string $value): bool
    {
        $sqlKeywords = ['UNION', 'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'CREATE', 'ALTER'];

        foreach ($sqlKeywords as $keyword) {
            if (stripos($value, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove null bytes from a string
     * 
     * @param string $value The value
     * @return string
     */
    public static function removeNullBytes(string $value): string
    {
        return str_replace("\0", '', $value);
    }

    /**
     * Encode value for safe output in HTML
     * 
     * @param mixed $value The value
     * @return string
     */
    public static function htmlEncode($value): string
    {
        if (is_array($value)) {
            return '<pre>' . self::escape(json_encode($value, JSON_PRETTY_PRINT)) . '</pre>';
        }

        return self::escape((string)$value);
    }
}
