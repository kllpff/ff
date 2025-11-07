<?php

namespace FF\Security;

use FF\Database\Model;

/**
 * Auth - Authentication Service
 * 
 * Manages user authentication, login, logout, and session handling.
 * Provides methods for user identification and permission checking.
 */
class Auth
{
    /**
     * The authenticated user
     * 
     * @var Model|null
     */
    protected ?Model $user = null;

    /**
     * The user model class
     * 
     * @var string
     */
    protected string $userModel;

    /**
     * Session key for storing user ID
     * 
     * @var string
     */
    protected const SESSION_KEY = 'auth_user_id';

    /**
     * Create a new Auth instance
     * 
     * @param string $userModel The user model class name
     */
    public function __construct(string $userModel = 'App\Models\User')
    {
        $this->userModel = $userModel;
        $this->loadUserFromSession();
    }

    /**
     * Authenticate a user with credentials
     * 
     * @param string $username The username or email
     * @param string $password The password
     * @param bool $remember Whether to remember the user
     * @return bool True if authentication succeeds
     */
    public function attempt(string $username, string $password, bool $remember = false): bool
    {
        // This will be fully implemented in Stage 5.5
        // For now, provide stub
        return false;
    }

    /**
     * Log in a specific user
     * 
     * @param Model $user The user to log in
     * @return void
     */
    public function login(Model $user): void
    {
        $this->ensureSessionStarted();

        if (!session_regenerate_id(true)) {
            throw new \RuntimeException('Failed to regenerate session ID during login.');
        }

        $this->user = $user;
        $_SESSION[self::SESSION_KEY] = $user->getAttribute('id');
    }

    /**
     * Check if a user is authenticated
     * 
     * @return bool
     */
    public function check(): bool
    {
        return $this->user !== null;
    }

    /**
     * Check if user is not authenticated
     * 
     * @return bool
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get the authenticated user
     * 
     * @return Model|null The user or null
     */
    public function user(): ?Model
    {
        return $this->user;
    }

    /**
     * Get the authenticated user ID
     * 
     * @return int|null The user ID or null
     */
    public function id(): ?int
    {
        return $this->user ? $this->user->getAttribute('id') : null;
    }

    /**
     * Log out the current user
     * 
     * @return void
     */
    public function logout(): void
    {
        $this->ensureSessionStarted();

        $this->user = null;
        unset($_SESSION[self::SESSION_KEY]);

        // Regenerate the session identifier to prevent fixation after logout
        if (!session_regenerate_id(true)) {
            throw new \RuntimeException('Failed to regenerate session ID during logout.');
        }
    }

    /**
     * Load user from session if exists
     * 
     * @return void
     */
    protected function loadUserFromSession(): void
    {
        $this->ensureSessionStarted();

        if (isset($_SESSION[self::SESSION_KEY])) {
            $userId = $_SESSION[self::SESSION_KEY];
            
            // Attempt to load user from model
            try {
                $model = $this->userModel;
                if (class_exists($model)) {
                    $this->user = $model::find($userId);
                }
            } catch (\Exception $e) {
                // User not found or model error - remain guest
            }
        }
    }

    /**
     * Check if user has a specific permission
     * 
     * @param string $permission The permission name
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->check()) {
            return false;
        }

        // This will be fully implemented with permission system
        // For now, return false
        return false;
    }

    /**
     * Check if user has a specific role
     * 
     * @param string $role The role name
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        if (!$this->check()) {
            return false;
        }

        // This will be fully implemented with role system
        // For now, return false
        return false;
    }

    /**
     * Register a new user
     * 
     * @param array $data User data
     * @return Model|null The created user or null
     */
    public function register(array $data): ?Model
    {
        // This will be fully implemented in Stage 5.5
        // For now, provide stub
        return null;
    }

    /**
     * Start the PHP session if it has not been started already.
     *
     * @return void
     */
    protected function ensureSessionStarted(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}
