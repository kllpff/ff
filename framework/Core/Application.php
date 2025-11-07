<?php

namespace FF\Core;

use FF\Support\DotEnv;

/**
 * Application - Main Application Class
 * 
 * Manages the application lifecycle, bootstrapping, configuration loading,
 * service provider registration, and environment setup.
 */
class Application extends Container
{
    /**
     * Base path of the application
     * 
     * @var string
     */
    protected string $basePath;

    /**
     * Array of registered service providers
     * 
     * @var array
     */
    protected array $providers = [];

    /**
     * Array of booted service providers
     * 
     * @var array
     */
    protected array $booted = [];

    /**
     * Whether the application has been bootstrapped
     * 
     * @var bool
     */
    protected bool $bootstrapped = false;

    /**
     * Create a new Application instance
     * 
     * @param string $basePath The base path of the application
     */
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        
        // Register the container itself
        $this->singleton(ContainerInterface::class, $this);
        $this->singleton(self::class, $this);
        $this->singleton('app', $this);
    }

    /**
     * Get the base path of the application
     * 
     * @param string $path Optional path to append
     * @return string
     */
    public function basePath(string $path = ''): string
    {
        if ($path) {
            return $this->basePath . '/' . ltrim($path, '/');
        }
        return $this->basePath;
    }

    /**
     * Get the path to the config directory
     * 
     * @param string $path Optional path to append
     * @return string
     */
    public function configPath(string $path = ''): string
    {
        return $this->basePath('config' . ($path ? '/' . ltrim($path, '/') : ''));
    }

    /**
     * Get the path to the app directory
     * 
     * @param string $path Optional path to append
     * @return string
     */
    public function appPath(string $path = ''): string
    {
        return $this->basePath('app' . ($path ? '/' . ltrim($path, '/') : ''));
    }

    /**
     * Get the path to the public directory
     * 
     * @param string $path Optional path to append
     * @return string
     */
    public function publicPath(string $path = ''): string
    {
        return $this->basePath('public' . ($path ? '/' . ltrim($path, '/') : ''));
    }

    /**
     * Get the path to the storage directory (tmp)
     * 
     * @param string $path Optional path to append
     * @return string
     */
    public function storagePath(string $path = ''): string
    {
        return $this->basePath('tmp' . ($path ? '/' . ltrim($path, '/') : ''));
    }

    /**
     * Load environment variables from .env file
     *
     * @return self
     */
    public function loadEnvironment(): self
    {
        try {
            $envPath = $this->basePath('.env');
            if (file_exists($envPath)) {
                $dotenv = DotEnv::createImmutable($this->basePath());
                $dotenv->load();
            }
        } catch (\Exception $e) {
            // .env file not found or cannot be loaded - that's okay
            $this->logBootstrapError('loadEnvironment', $e);
        }

        return $this;
    }

    /**
     * Load configuration from config directory
     * 
     * Loads all PHP files from config/ directory and registers them
     * in the container using their filename as the key.
     * 
     * @return self
     */
    public function loadConfiguration(): self
    {
        $configPath = $this->configPath();

        if (!is_dir($configPath)) {
            return $this;
        }

        $configs = [];
        $files = scandir($configPath);

        if ($files === false) {
            return $this;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (substr($file, -4) === '.php') {
                $key = substr($file, 0, -4);
                $filePath = $configPath . '/' . $file;
                $configs[$key] = require $filePath;
            }
        }

        // Register configs in the container
        foreach ($configs as $key => $config) {
            $this->singleton('config.' . $key, $config);
        }

        // Also register a main config accessor
        $this->singleton('config', $configs);

        return $this;
    }

    /**
     * Validate the APP_KEY environment variable
     *
     * Ensures APP_KEY is set and meets minimum security requirements.
     * Required for encryption and CSRF protection.
     *
     * @return self
     * @throws \RuntimeException If APP_KEY is invalid
     */
    protected function validateAppKey(): self
    {
        $appKey = $_ENV['APP_KEY'] ?? env('APP_KEY', '');

        if (empty($appKey)) {
            throw new \RuntimeException(
                'APP_KEY is not set. Please generate a secure key and add it to your .env file.'
            );
        }

        // Check minimum length (32 characters recommended for encryption)
        if (strlen($appKey) < 32) {
            throw new \RuntimeException(
                'APP_KEY is too short. It must be at least 32 characters long for security. ' .
                'Current length: ' . strlen($appKey)
            );
        }

        return $this;
    }

    /**
     * Register service providers
     * 
     * Registers an array of service provider classes.
     * Calls the register() method on each provider.
     * 
     * @param array $providers Array of service provider class names
     * @return self
     */
    public function registerProviders(array $providers): self
    {
        foreach ($providers as $providerClass) {
            $provider = is_string($providerClass) 
                ? new $providerClass($this)
                : $providerClass;

            $this->providers[$providerClass] = $provider;
            
            if (method_exists($provider, 'register')) {
                $provider->register();
            }
        }

        return $this;
    }

    /**
     * Boot all registered service providers
     * 
     * Calls the boot() method on each registered provider.
     * 
     * @return self
     */
    public function bootProviders(): self
    {
        foreach ($this->providers as $providerClass => $provider) {
            if (!isset($this->booted[$providerClass])) {
                if (method_exists($provider, 'boot')) {
                    $provider->boot();
                }
                $this->booted[$providerClass] = true;
            }
        }

        $this->bootstrapped = true;

        return $this;
    }

    /**
     * Bootstrap the application
     *
     * Performs full initialization: load environment, load configuration,
     * register and boot service providers.
     *
     * @param array $providers Optional array of service providers to register
     * @return self
     */
    public function bootstrap(array $providers = []): self
    {
        if ($this->bootstrapped) {
            return $this;
        }

        // Load helper functions first
        if (file_exists(__DIR__ . '/../helpers.php')) {
            require_once __DIR__ . '/../helpers.php';
        }

        $this->loadEnvironment();
        $this->loadConfiguration();
        $this->validateAppKey();
        $this->bootstrapLogger();
        $this->bootstrapDatabase();
        $this->bootstrapSession();
        $this->bootstrapDebugBar();

        if (!empty($providers)) {
            $this->registerProviders($providers);
            $this->bootProviders();
        }

        $this->bootstrapped = true;

        return $this;
    }

    /**
     * Bootstrap logger
     * 
     * Initialize the logger instance
     * 
     * @return void
     */
    protected function bootstrapLogger(): void
    {
        try {
            $logFile = $this->basePath('storage/logs/app.log');
            $logger = new \FF\Log\Logger($logFile);
            $this->singleton('logger', $logger);
            $this->singleton(\FF\Log\Logger::class, $logger);
            
            // Register other services
            $this->singleton('cache', function() {
                $cacheDriver = $_ENV['CACHE_DRIVER'] ?? env('CACHE_DRIVER', 'array');
                return new \FF\Cache\Cache($cacheDriver, $this->basePath('storage/cache'));
            });
            $this->singleton(\FF\Cache\Cache::class, function() {
                return $this->make('cache');
            });
            
            $this->singleton('rateLimiter', function() {
                return new \FF\Security\RateLimiter($this->make('cache'));
            });
            $this->singleton(\FF\Security\RateLimiter::class, function() {
                return $this->make('rateLimiter');
            });
            
            $this->singleton('encrypt', function() {
                $key = $_ENV['APP_KEY'] ?? env('APP_KEY', '');
                return new \FF\Security\Encrypt($key);
            });
            $this->singleton(\FF\Security\Encrypt::class, function() {
                return $this->make('encrypt');
            });

            $this->singleton('csrf', function() {
                return new \FF\Security\CsrfGuard($this->make(\FF\Security\Encrypt::class));
            });
            $this->singleton(\FF\Security\CsrfGuard::class, function() {
                return $this->make('csrf');
            });
        } catch (\Exception $e) {
            // Services failed to initialize - that's okay
            $this->logBootstrapError('bootstrapLogger', $e);
        }
    }

    /**
     * Bootstrap database connection
     * 
     * Set the database connection for all models
     * 
     * @return void
     */
    protected function bootstrapDatabase(): void
    {
        try {
            // Create database connection with config array
            $connection = new \FF\Database\Connection([
                'driver' => $_ENV['DB_CONNECTION'] ?? env('DB_CONNECTION', 'mysql'),
                'host' => $_ENV['DB_HOST'] ?? env('DB_HOST', 'localhost'),
                'username' => $_ENV['DB_USERNAME'] ?? env('DB_USERNAME', 'root'),
                'password' => $_ENV['DB_PASSWORD'] ?? env('DB_PASSWORD', ''),
                'database' => $_ENV['DB_DATABASE'] ?? env('DB_DATABASE', 'test'),
                'port' => $_ENV['DB_PORT'] ?? env('DB_PORT', 3306),
            ]);
            
            // Set connection on all models
            \FF\Database\Model::setConnection($connection);
        } catch (\Exception $e) {
            // Database connection not available - that's okay for some cases
            $this->logBootstrapError('bootstrapDatabase', $e);
        }
    }

    /**
     * Bootstrap session
     * 
     * Initialize the session manager
     * 
     * @return void
     */
    protected function bootstrapSession(): void
    {
        try {
            $sessionConfig = config('session', []);
            $session = new \FF\Session\SessionManager($sessionConfig);
            $this->singleton('session', $session);
        } catch (\Exception $e) {
            // Session failed to initialize - that's okay
            $this->logBootstrapError('bootstrapSession', $e);
        }
    }

    /**
     * Bootstrap DebugBar
     * 
     * Initialize the debug bar for development mode
     * 
     * @return void
     */
    protected function bootstrapDebugBar(): void
    {
        if ($this->isDebugMode()) {
            try {
                $debugBar = new \FF\Debug\DebugBar(true);
                $this->singleton('debugbar', $debugBar);
            } catch (\Exception $e) {
                // DebugBar failed to load - that's okay
                $this->logBootstrapError('bootstrapDebugBar', $e);
            }
        }
    }

    /**
     * Check if the application is in debug mode
     * 
     * @return bool
     */
    public function isDebugMode(): bool
    {
        return (bool)($_ENV['APP_DEBUG'] ?? env('APP_DEBUG', false) ?? false);
    }

    /**
     * Check if the application is running in production
     * 
     * @return bool
     */
    public function isProduction(): bool
    {
        return ($_ENV['APP_ENV'] ?? env('APP_ENV', 'production')) === 'production';
    }

    /**
     * Get the application name
     * 
     * @return string
     */
    public function getName(): string
    {
        return $_ENV['APP_NAME'] ?? env('APP_NAME', 'FF');
    }

    /**
     * Get the application environment
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $_ENV['APP_ENV'] ?? env('APP_ENV', 'production');
    }

    /**
     * Log bootstrap errors in debug mode
     *
     * Logs errors that occur during bootstrap to help with debugging.
     * Only logs when APP_DEBUG is enabled.
     *
     * @param string $method The bootstrap method that failed
     * @param \Exception $exception The exception that was caught
     * @return void
     */
    protected function logBootstrapError(string $method, \Exception $exception): void
    {
        // Only log in debug mode
        if (!$this->isDebugMode()) {
            return;
        }

        // Try to use logger if available, otherwise use error_log
        try {
            if ($this->has('logger')) {
                $logger = $this->make('logger');
                $logger->warning("Bootstrap {$method} failed", [
                    'error' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ]);
            } else {
                // Fallback to error_log if logger not available yet
                error_log(sprintf(
                    "[FF Bootstrap Warning] %s failed: %s in %s:%d",
                    $method,
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine()
                ));
            }
        } catch (\Exception $e) {
            // If logging fails, fail silently to avoid breaking bootstrap
        }
    }
}
