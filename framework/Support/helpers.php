<?php

use FF\Core\Application;

/**
 * Get the application instance or a binding from the container
 *
 * @param string|null $binding Optional binding name to resolve
 * @return mixed The application instance or resolved binding
 */
function app(?string $binding = null)
{
    global $application;

    if (!isset($application) || !($application instanceof Application)) {
        throw new \Exception('Application not initialized');
    }

    if ($binding === null) {
        return $application;
    }

    return $application->get($binding);
}

/**
 * Get an environment variable
 *
 * @param string $key The environment variable name
 * @param mixed $default Default value if not found
 * @return mixed The environment variable value
 */
function env(string $key, $default = null)
{
    return $_ENV[$key] ?? $default;
}

/**
 * Get the base path
 *
 * @param string $path Optional path to append
 * @return string The base path
 */
function base_path(string $path = ''): string
{
    return app()->basePath($path);
}

/**
 * Get the app path
 *
 * @param string $path Optional path to append
 * @return string The app path
 */
function app_path(string $path = ''): string
{
    return app()->appPath($path);
}

/**
 * Get the config path
 *
 * @param string $path Optional path to append
 * @return string The config path
 */
function config_path(string $path = ''): string
{
    return app()->configPath($path);
}

/**
 * Get the public path
 *
 * @param string $path Optional path to append
 * @return string The public path
 */
function public_path(string $path = ''): string
{
    return app()->publicPath($path);
}

/**
 * Get the storage path
 *
 * @param string $path Optional path to append
 * @return string The storage path
 */
function storage_path(string $path = ''): string
{
    return app()->storagePath($path);
}

/**
 * Get a configuration value
 *
 * @param string $key The config key (dot notation: 'database.driver')
 * @param mixed $default Default value
 * @return mixed The config value
 */
function config(string $key, $default = null)
{
    try {
        $config = app('config');
    } catch (\Throwable $e) {
        $config = [];
    }

    if (!is_array($config)) {
        $config = [];
    }

    $parts = explode('.', $key);
    $value = $config;

    foreach ($parts as $part) {
        if (is_array($value) && array_key_exists($part, $value)) {
            $value = $value[$part];
        } else {
            return $default;
        }
    }

    return $value;
}

/**
 * Dump and die - output variable and stop execution
 *
 * @param mixed ...$args Variables to dump
 * @return void
 */
function dd(...$args): void
{
    foreach ($args as $arg) {
        var_dump($arg);
    }
    die();
}

/**
 * Dump - output variable
 *
 * @param mixed ...$args Variables to dump
 * @return void
 */
function dump(...$args): void
{
    foreach ($args as $arg) {
        var_dump($arg);
    }
}

/**
 * Get a string representation of value
 *
 * @param mixed $value The value
 * @return string The string representation
 */
function stringify($value): string
{
    if (is_array($value)) {
        return json_encode($value);
    } else if (is_object($value)) {
        return get_class($value) . ' Object';
    } else if (is_bool($value)) {
        return $value ? 'true' : 'false';
    } else if (is_null($value)) {
        return 'null';
    }

    return (string)$value;
}

/**
 * Hash a value using bcrypt
 *
 * @param string $value The value to hash
 * @return string The hashed value
 */
function hash_password(string $value): string
{
    return app('hash')->make($value);
}

/**
 * Verify a password
 *
 * @param string $plain The plain password
 * @param string $hash The hash to verify against
 * @return bool
 */
function verify_password(string $plain, string $hash): bool
{
    return app('hash')->check($plain, $hash);
}

/**
 * Get a value from an array or object using dot notation
 *
 * @param array|object $array The array or object to search
 * @param string $key The key to search for (can use dot notation: "user.name")
 * @param mixed $default Default value if key doesn't exist
 * @return mixed The value or default
 */
function get($array, string $key, $default = null)
{
    if (is_object($array)) {
        $array = (array)$array;
    }

    if (!is_array($array)) {
        return $default;
    }

    // Simple key lookup
    if (isset($array[$key])) {
        return $array[$key];
    }

    // Dot notation lookup
    if (strpos($key, '.') !== false) {
        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } elseif (is_object($value) && isset($value->$k)) {
                $value = $value->$k;
            } else {
                return $default;
            }
        }

        return $value;
    }

    return $default;
}

/**
 * Check if a value exists in array/object
 *
 * @param array|object $array The array or object
 * @param string $key The key to check
 * @return bool
 */
function has($array, string $key): bool
{
    if (is_object($array)) {
        return isset($array->$key);
    }

    return isset($array[$key]);
}
