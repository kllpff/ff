<?php

namespace FF\Session;

/**
 * SessionManager - Session Management
 * 
 * Manages session lifecycle, data storage, and configuration.
 * Provides methods for getting, setting, and removing session data.
 */
class SessionManager
{
    /**
     * Session configuration
     * 
     * @var array
     */
    protected array $config = [];

    /**
     * Whether session has been started
     * 
     * @var bool
     */
    protected bool $started = false;

    /**
     * Session data
     * 
     * @var array
     */
    protected array $data = [];

    /**
     * Inactivity timeout in seconds.
     */
    protected int $timeout = 0;

    /**
     * Create a new SessionManager instance
     * 
     * @param array $config Session configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'driver' => 'file',
            'lifetime' => 120,
            'timeout' => 7200,
            'expire_on_close' => false,
            'secure' => false,
            'http_only' => true,
            'same_site' => 'Lax',
            'domain' => '',
        ], $config);

        $this->timeout = max(0, (int)$this->config['timeout']);

        $this->configurePHP();
    }

    /**
     * Configure PHP session settings
     * 
     * @return void
     */
    protected function configurePHP(): void
    {
        ini_set('session.name', 'FFSESSID');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', $this->config['lifetime'] * 60);

        // Determine if connection is secure (HTTPS)
        $isHttps = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );

        // Override secure flag if HTTPS detected but config says false
        $secureFlag = $this->config['secure'];
        if ($isHttps && !$secureFlag) {
            $secureFlag = true; // Auto-enable secure flag on HTTPS
        }

        // Set cookie parameters
        session_set_cookie_params([
            'lifetime' => $this->config['expire_on_close'] ? 0 : $this->config['lifetime'] * 60,
            'path' => '/',
            'domain' => $this->determineCookieDomain(),
            'secure' => $secureFlag,
            'httponly' => $this->config['http_only'],
            'samesite' => $this->config['same_site'],
        ]);
    }

    /**
     * Start the session
     * 
     * @return void
     */
    public function start(): void
    {
        if ($this->started) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->data = $_SESSION ?? [];
        $this->enforceTimeout();
        $this->updateActivityTimestamp();
        $this->started = true;
    }

    /**
     * Check if session is started
     * 
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Get a session value
     * 
     * @param string $key The key (supports dot notation: 'user.name')
     * @param mixed $default Default value if not found
     * @return mixed The session value
     */
    public function get(string $key, $default = null)
    {
        return $this->getNestedValue($this->data, $key, $default);
    }

    /**
     * Get all session data
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Set a session value
     * 
     * @param string $key The key (supports dot notation: 'user.name')
     * @param mixed $value The value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->setNestedValue($this->data, $key, $value);
        $this->setNestedValue($_SESSION, $key, $value);
    }

    /**
     * Put data into session (alias for set)
     * 
     * @param array $data The data to put
     * @return void
     */
    public function put($key, $value = null): void
    {
        if (is_array($key) && $value === null) {
            foreach ($key as $itemKey => $itemValue) {
                $this->set($itemKey, $itemValue);
            }
            return;
        }

        if (!is_string($key)) {
            throw new \InvalidArgumentException('Session::put expects a string key or associative array.');
        }

        $this->set($key, $value);
    }

    /**
     * Check if a key exists in session
     * 
     * @param string $key The key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Remove a value from session
     * 
     * @param string $key The key
     * @return void
     */
    public function forget(string $key): void
    {
        $this->unsetNestedValue($this->data, $key);
        $this->unsetNestedValue($_SESSION, $key);
    }

    /**
     * Remove multiple values from session
     * 
     * @param array $keys The keys to remove
     * @return void
     */
    public function forget_multiple(array $keys): void
    {
        foreach ($keys as $key) {
            $this->forget($key);
        }
    }

    /**
     * Clear all session data
     * 
     * @return void
     */
    public function flush(): void
    {
        $this->data = [];
        $_SESSION = [];
        session_regenerate_id(true);
    }

    protected function enforceTimeout(): void
    {
        if ($this->timeout <= 0) {
            return;
        }

        $lastActivity = $_SESSION['_meta']['last_activity'] ?? null;

        if ($lastActivity !== null && (time() - (int)$lastActivity) > $this->timeout) {
            $this->flush();
        }
    }

    protected function updateActivityTimestamp(): void
    {
        $timestamp = time();
        $_SESSION['_meta']['last_activity'] = $timestamp;
        $this->data['_meta']['last_activity'] = $timestamp;
    }

    protected function determineCookieDomain(): string
    {
        $domain = trim((string)$this->config['domain']);

        if ($domain === '') {
            $appUrl = $_ENV['APP_URL'] ?? \env('APP_URL', '');
            $parsed = $appUrl ? parse_url($appUrl, PHP_URL_HOST) : null;
            $domain = $parsed ?: 'localhost';
        }

        return $this->sanitizeDomain($domain);
    }

    protected function sanitizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));

        if ($domain === '' || $domain === 'localhost') {
            return '';
        }

        if (!preg_match('/^[a-z0-9.-]+$/', $domain)) {
            return '';
        }

        return $domain;
    }

    /**
     * Regenerate session ID (after login)
     * 
     * @return void
     */
    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Flash data to session (one-time use)
     * 
     * @param string $key The key
     * @param mixed $value The value
     * @return void
     */
    public function flash(string $key, $value): void
    {
        $this->set('_flash.' . $key, $value);
    }

    /**
     * Get flashed data and remove it
     * 
     * @param string $key The key
     * @param mixed $default Default if not found
     * @return mixed
     */
    public function getFlash(string $key, $default = null)
    {
        $flashKey = '_flash.' . $key;
        $value = $this->get($flashKey, $default);
        $this->forget($flashKey);
        return $value;
    }

    /**
     * Get nested value using dot notation
     * 
     * @param array $array The array
     * @param string $key The key (dot notation: 'user.profile.name')
     * @param mixed $default Default value
     * @return mixed
     */
    protected function getNestedValue(array $array, string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $k) {
            if (!is_array($value) || !isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set nested value using dot notation
     * 
     * @param array $array The array reference
     * @param string $key The key (dot notation)
     * @param mixed $value The value
     * @return void
     */
    protected function setNestedValue(&$array, string $key, $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
    }

    /**
     * Remove nested value using dot notation.
     *
     * @param array $array
     * @param string $key
     * @return void
     */
    protected function unsetNestedValue(&$array, string $key): void
    {
        if (!is_array($array)) {
            $array = [];
        }

        $keys = explode('.', $key);
        $current = &$array;
        $lastKey = array_pop($keys);

        foreach ($keys as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return;
            }
            $current = &$current[$segment];
        }

        if (is_array($current) && array_key_exists($lastKey, $current)) {
            unset($current[$lastKey]);
        }
    }

    /**
     * Destroy the session
     * 
     * @return void
     */
    public function destroy(): void
    {
        session_destroy();
        $this->data = [];
        $this->started = false;
    }
}
