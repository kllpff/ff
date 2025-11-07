<?php

namespace FF\Security;

/**
 * Hash - Password Hashing Service
 * 
 * Provides methods for hashing and verifying passwords using BCrypt.
 * Uses PHP's built-in password_hash and password_verify functions.
 */
class Hash
{
    /**
     * The default hashing algorithm
     * 
     * @var string
     */
    protected static string $algorithm = PASSWORD_BCRYPT;

    /**
     * Hash a value using BCrypt
     * 
     * @param string $value The value to hash
     * @param array $options Hashing options
     * @return string The hashed value
     */
    public static function make(string $value, array $options = []): string
    {
        $options = array_merge([
            'cost' => 12,
        ], $options);

        return password_hash($value, static::$algorithm, $options);
    }

    /**
     * Verify that a value matches a hash
     * 
     * @param string $value The plain value
     * @param string $hashedValue The hashed value
     * @return bool True if value matches hash
     */
    public static function check(string $value, string $hashedValue): bool
    {
        if (empty($hashedValue)) {
            return false;
        }

        return password_verify($value, $hashedValue);
    }

    /**
     * Check if a hash needs to be rehashed (algorithm or cost changed)
     * 
     * @param string $hashedValue The hash to check
     * @param array $options Current options
     * @return bool True if needs rehashing
     */
    public static function needsRehash(string $hashedValue, array $options = []): bool
    {
        $options = array_merge([
            'cost' => 12,
        ], $options);

        return password_needs_rehash($hashedValue, static::$algorithm, $options);
    }

    /**
     * Set the default hashing algorithm
     * 
     * @param string $algorithm The algorithm
     * @return void
     */
    public static function setAlgorithm(string $algorithm): void
    {
        static::$algorithm = $algorithm;
    }
}
