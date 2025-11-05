<?php

namespace FF\Framework\Support;

/**
 * Str - String Utilities
 * 
 * Provides utility methods for string manipulation.
 */
class Str
{
    /**
     * Check if string contains substring
     * 
     * @param string $haystack The string to search in
     * @param string $needle The substring to find
     * @return bool
     */
    public static function contains(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) !== false;
    }

    /**
     * Check if string starts with substring
     * 
     * @param string $haystack The string
     * @param string $needle The substring
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    /**
     * Check if string ends with substring
     * 
     * @param string $haystack The string
     * @param string $needle The substring
     * @return bool
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    /**
     * Convert to camelCase
     * 
     * @param string $value The value
     * @return string
     */
    public static function camel(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', $value);
        $value = ucwords($value);
        return lcfirst(str_replace(' ', '', $value));
    }

    /**
     * Convert to snake_case
     * 
     * @param string $value The value
     * @return string
     */
    public static function snake(string $value): string
    {
        $value = preg_replace('/([A-Z])/', '_$1', $value);
        return strtolower(ltrim($value, '_'));
    }

    /**
     * Convert to kebab-case
     * 
     * @param string $value The value
     * @return string
     */
    public static function kebab(string $value): string
    {
        return str_replace('_', '-', self::snake($value));
    }

    /**
     * Convert to PascalCase
     * 
     * @param string $value The value
     * @return string
     */
    public static function pascal(string $value): string
    {
        return ucfirst(self::camel($value));
    }

    /**
     * Make string singular
     * 
     * @param string $value The string
     * @return string
     */
    public static function singular(string $value): string
    {
        $rules = [
            '/s$/' => '',
            '/ies$/' => 'y',
            '/es$/' => '',
        ];

        foreach ($rules as $pattern => $replacement) {
            if (preg_match($pattern, $value)) {
                return preg_replace($pattern, $replacement, $value);
            }
        }

        return $value;
    }

    /**
     * Make string plural
     * 
     * @param string $value The string
     * @return string
     */
    public static function plural(string $value): string
    {
        $rules = [
            '/y$/' => 'ies',
            '/s$/' => 'ses',
            '/o$/' => 'oes',
            '/$/' => 's',
        ];

        foreach ($rules as $pattern => $replacement) {
            if (preg_match($pattern, $value)) {
                return preg_replace($pattern, $replacement, $value);
            }
        }

        return $value . 's';
    }

    /**
     * Generate a slug from a string
     * 
     * @param string $value The value
     * @param string $separator The separator (default: hyphen)
     * @return string
     */
    public static function slug(string $value, string $separator = '-'): string
    {
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', $separator, $value);
        return trim($value, $separator);
    }

    /**
     * Generate a random string
     * 
     * @param int $length The length
     * @return string
     */
    public static function random(int $length = 16): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Generate a UUID
     * 
     * @return string
     */
    public static function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    /**
     * Generate a short unique ID
     * 
     * @param int $length The length (default: 12)
     * @return string
     */
    public static function shortId(int $length = 12): string
    {
        return substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes($length * 2))), 0, $length);
    }

    /**
     * Limit string length
     * 
     * @param string $value The string
     * @param int $limit The limit
     * @param string $end The ending string
     * @return string
     */
    public static function limit(string $value, int $limit, string $end = '...'): string
    {
        if (strlen($value) <= $limit) {
            return $value;
        }

        return substr($value, 0, $limit) . $end;
    }

    /**
     * Replace template variables
     * 
     * @param string $template The template
     * @param array $variables The variables
     * @return string
     */
    public static function replaceVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace(':' . $key, $value, $template);
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return $template;
    }

    /**
     * Truncate a string by words
     * 
     * @param string $value The string
     * @param int $words Number of words
     * @param string $end The ending
     * @return string
     */
    public static function words(string $value, int $words = 10, string $end = '...'): string
    {
        $parts = explode(' ', $value);

        if (count($parts) <= $words) {
            return $value;
        }

        return implode(' ', array_slice($parts, 0, $words)) . $end;
    }
}
