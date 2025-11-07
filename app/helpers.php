<?php

use FF\View\HtmlValue;

/**
 * Application Helper Functions
 * 
 * Custom helper functions available throughout the application
 */

/**
 * HTML escape helper - shorthand for htmlspecialchars
 * 
 * @param mixed $string The string to escape (or null)
 * @param int $flags HTML special characters flags
 * @param string $encoding Character encoding
 * @return string The escaped string
 */
function h($string = '', $flags = ENT_QUOTES, $encoding = 'UTF-8'): string
{
    if ($string === null) {
        return '';
    }

    if ($string instanceof HtmlValue) {
        return $string->escape($flags, $encoding);
    }

    return htmlspecialchars((string)$string, $flags, $encoding);
}

/**
 * Safely get and escape a value from array/object
 * 
 * @param array|object $array The array or object
 * @param string $key The key to get
 * @param mixed $default Default value
 * @return string Escaped string
 */
function hs($array, string $key, $default = ''): string
{
    $value = get($array, $key, $default);
    return h($value);
}

/**
 * Convert array to object
 * 
 * @param array $array Array to convert
 * @return object
 */
function arrayToObject(array $array): object
{
    return json_decode(json_encode($array));
}

/**
 * Convert object to array
 * 
 * @param object $object Object to convert
 * @return array
 */
function objectToArray($object): array
{
    return json_decode(json_encode($object), true);
}

/**
 * Mark a string as safe HTML for view rendering.
 *
 * @param string $value
 * @return HtmlValue
 */
function raw_html(string $value): HtmlValue
{
    return HtmlValue::raw($value);
}
