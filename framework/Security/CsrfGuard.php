<?php

namespace FF\Framework\Security;

use FF\Framework\Http\Request;
use FF\Framework\Http\Response;

/**
 * CsrfGuard - CSRF Protection
 * 
 * Generates and validates CSRF tokens to prevent Cross-Site Request Forgery attacks.
 * Tokens are stored in session and validated on state-changing requests.
 */
class CsrfGuard
{
    /**
     * The session key for storing CSRF token
     * 
     * @var string
     */
    protected const SESSION_KEY = '_csrf_token';

    /**
     * The request input field name for CSRF token
     * 
     * @var string
     */
    protected const INPUT_NAME = '_token';

    /**
     * The header name for CSRF token
     * 
     * @var string
     */
    protected const HEADER_NAME = 'X-CSRF-Token';

    /**
     * The encryption service
     * 
     * @var Encrypt
     */
    protected Encrypt $encryptor;

    /**
     * Create a new CsrfGuard instance
     * 
     * @param Encrypt $encryptor The encryption service
     */
    public function __construct(Encrypt $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * Generate a CSRF token
     * 
     * @return string A CSRF token
     */
    public function generate(): string
    {
        $token = bin2hex(random_bytes(32));

        // Store in session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[self::SESSION_KEY] = $token;
        }

        return $token;
    }

    /**
     * Get the current token or generate one if it doesn't exist
     * 
     * @return string
     */
    public function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (isset($_SESSION[self::SESSION_KEY])) {
            return $_SESSION[self::SESSION_KEY];
        }

        return $this->generate();
    }

    /**
     * Validate a CSRF token from the request
     * 
     * @param Request $request The request
     * @return bool True if token is valid
     */
    public function validate(Request $request): bool
    {
        $token = $this->getTokenFromRequest($request);

        if (!$token || !$this->tokensMatch($token)) {
            return false;
        }

        // If valid, generate new token for next request
        $this->generate();

        return true;
    }

    /**
     * Get CSRF token from request (header or input)
     * 
     * @param Request $request The request
     * @return string|null
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        // Check header first
        $token = $request->header(self::HEADER_NAME);

        if (!$token) {
            // Check POST data
            $token = $request->post(self::INPUT_NAME);
        }

        return $token ?: null;
    }

    /**
     * Check if tokens match
     * 
     * @param string $requestToken The token from request
     * @return bool
     */
    protected function tokensMatch(string $requestToken): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $sessionToken = $_SESSION[self::SESSION_KEY] ?? null;

        if (!$sessionToken) {
            return false;
        }

        // Use hash_equals to prevent timing attacks
        return hash_equals($sessionToken, $requestToken);
    }

    /**
     * Get the input field name for CSRF token
     * 
     * @return string
     */
    public static function getInputName(): string
    {
        return self::INPUT_NAME;
    }

    /**
     * Get the header name for CSRF token
     * 
     * @return string
     */
    public static function getHeaderName(): string
    {
        return self::HEADER_NAME;
    }

    /**
     * Get HTML hidden input field for forms
     * 
     * @return string HTML input element
     */
    public function field(): string
    {
        return '<input type="hidden" name="' . self::INPUT_NAME . '" value="' . $this->token() . '">';
    }
}
