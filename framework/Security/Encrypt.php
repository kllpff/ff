<?php

namespace FF\Security;

/**
 * Encrypt - Encryption Service
 * 
 * Provides AEAD encryption (AES-256-GCM) for sensitive data.
 * Uses OpenSSL for authenticated encryption operations.
 */
class Encrypt
{
    /**
     * The encryption cipher
     * 
     * @var string
     */
    protected static string $cipher = 'aes-256-gcm';

    /**
     * Lengths for IV and authentication tag
     */
    protected const IV_LENGTH = 12;   // Recommended IV size for GCM
    protected const TAG_LENGTH = 16;  // 128-bit authentication tag

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

        // Support Laravel-style base64 prefixed keys
        if (str_starts_with($this->key, 'base64:')) {
            $base64Key = substr($this->key, 7);
            $decoded = base64_decode($base64Key, true);
            if ($decoded !== false) {
                $this->key = $decoded;
            }
        }

        // Derive 32-byte key material (binary string) for AES-256
        $this->key = hash('sha256', $this->key, true);
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
        // Generate random IV suitable for GCM
        $iv = random_bytes(self::IV_LENGTH);

        $tag = '';

        $ciphertext = openssl_encrypt(
            $value,
            static::$cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            ''
        );

        if ($ciphertext === false || $tag === false || strlen($tag) !== self::TAG_LENGTH) {
            throw new \Exception('Encryption failed');
        }

        // Encode IV + tag + ciphertext
        return base64_encode($iv . $tag . $ciphertext);
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
        $data = base64_decode($encryptedValue, true);

        if ($data === false) {
            throw new \Exception('Invalid encrypted value');
        }

        $expectedMinLength = self::IV_LENGTH + self::TAG_LENGTH;
        if (strlen($data) <= $expectedMinLength) {
            throw new \Exception('Invalid encrypted payload');
        }

        // Extract IV and encrypted payload
        $iv = substr($data, 0, self::IV_LENGTH);
        $tag = substr($data, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($data, $expectedMinLength);

        // Decrypt
        $decrypted = openssl_decrypt(
            $ciphertext,
            static::$cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            ''
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
        return bin2hex(random_bytes(32));
    }
}
