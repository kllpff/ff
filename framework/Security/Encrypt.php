<?php

namespace FF\Framework\Security;

/**
 * Encrypt - Encryption Service
 * 
 * Provides AES-256-CBC encryption/decryption for sensitive data.
 * Uses OpenSSL for encryption operations.
 */
class Encrypt
{
    /**
     * The encryption cipher
     * 
     * @var string
     */
    protected static string $cipher = 'AES-256-CBC';

    /**
     * The encryption key
     * 
     * @var string
     */
    protected string $key;

    /**
     * Create a new Encrypt instance
     * 
     * @param string $key The encryption key
     */
    public function __construct(string $key = '')
    {
        $this->key = $key ?: ($_ENV['APP_KEY'] ?? '');

        if (empty($this->key)) {
            throw new \Exception('No encryption key provided. Set APP_KEY environment variable.');
        }

        // Ensure key is correct length (32 bytes for AES-256)
        if (strlen($this->key) < 32) {
            // Pad key if too short
            $this->key = str_pad($this->key, 32, '0');
        } else if (strlen($this->key) > 32) {
            // Truncate key if too long
            $this->key = substr($this->key, 0, 32);
        }
    }

    /**
     * Encrypt a value
     * 
     * @param string $value The value to encrypt
     * @return string The encrypted value (base64 encoded)
     * @throws \Exception If encryption fails
     */
    public function encrypt(string $value): string
    {
        // Generate random IV
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(static::$cipher));

        // Encrypt the value
        $encrypted = openssl_encrypt(
            $value,
            static::$cipher,
            $this->key,
            0, // No raw output (base64 output)
            $iv
        );

        if ($encrypted === false) {
            throw new \Exception('Encryption failed');
        }

        // Return IV + encrypted value (both base64 encoded)
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt a value
     * 
     * @param string $encryptedValue The encrypted value (base64 encoded)
     * @return string The decrypted value
     * @throws \Exception If decryption fails
     */
    public function decrypt(string $encryptedValue): string
    {
        // Decode from base64
        $data = base64_decode($encryptedValue);

        if ($data === false) {
            throw new \Exception('Invalid encrypted value');
        }

        // Get IV length
        $ivLength = openssl_cipher_iv_length(static::$cipher);

        // Extract IV and encrypted payload
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        // Decrypt
        $decrypted = openssl_decrypt(
            $encrypted,
            static::$cipher,
            $this->key,
            0, // No raw output (expects base64)
            $iv
        );

        if ($decrypted === false) {
            throw new \Exception('Decryption failed - invalid key or corrupted data');
        }

        return $decrypted;
    }

    /**
     * Static method to encrypt (creates temporary instance)
     * 
     * @param string $value The value to encrypt
     * @param string $key Optional encryption key
     * @return string The encrypted value
     */
    public static function hash(string $value, string $key = ''): string
    {
        $encryptor = new self($key);
        return $encryptor->encrypt($value);
    }

    /**
     * Static method to decrypt
     * 
     * @param string $encryptedValue The encrypted value
     * @param string $key Optional encryption key
     * @return string The decrypted value
     */
    public static function reveal(string $encryptedValue, string $key = ''): string
    {
        $encryptor = new self($key);
        return $encryptor->decrypt($encryptedValue);
    }

    /**
     * Generate a random encryption key
     * 
     * @return string A random 32-byte key
     */
    public static function generateKey(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(16)); // 32 hex chars = 16 bytes
    }
}
