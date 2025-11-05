<?php

namespace FF\Framework\Core;

use Dotenv\Dotenv;

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
                $dotenv = Dotenv::createImmutable($this->basePath());
                $dotenv->load();
            }
        } catch (\Exception $e) {
            // .env file not found or cannot be loaded - that's okay
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
            $logger = new \FF\Framework\Log\Logger($logFile);
            $this->singleton('logger', $logger);
            $this->singleton(\FF\Framework\Log\Logger::class, $logger);
            
            // Register other services
            $this->singleton('cache', function() {
                $cacheDriver = $_ENV['CACHE_DRIVER'] ?? env('CACHE_DRIVER', 'array');
                return new \FF\Framework\Cache\Cache($cacheDriver, $this->basePath('storage/cache'));
            });
            $this->singleton(\FF\Framework\Cache\Cache::class, function() {
                return $this->make('cache');
            });
            
            $this->singleton('rateLimiter', function() {
                return new \FF\Framework\Security\RateLimiter($this->make('cache'));
            });
            $this->singleton(\FF\Framework\Security\RateLimiter::class, function() {
                return $this->make('rateLimiter');
            });
            
            $this->singleton('encrypt', function() {
                $key = $_ENV['APP_KEY'] ?? env('APP_KEY', '');
                return new \FF\Framework\Security\Encrypt($key);
            });
            $this->singleton(\FF\Framework\Security\Encrypt::class, function() {
                return $this->make('encrypt');
            });
        } catch (\Exception $e) {
            // Services failed to initialize - that's okay
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
            $connection = new \FF\Framework\Database\Connection([
                'driver' => $_ENV['DB_CONNECTION'] ?? env('DB_CONNECTION', 'mysql'),
                'host' => $_ENV['DB_HOST'] ?? env('DB_HOST', 'localhost'),
                'username' => $_ENV['DB_USERNAME'] ?? env('DB_USERNAME', 'root'),
                'password' => $_ENV['DB_PASSWORD'] ?? env('DB_PASSWORD', ''),
                'database' => $_ENV['DB_DATABASE'] ?? env('DB_DATABASE', 'test'),
                'port' => $_ENV['DB_PORT'] ?? env('DB_PORT', 3306),
            ]);
            
            // Set connection on all models
            \FF\Framework\Database\Model::setConnection($connection);
        } catch (\Exception $e) {
            // Database connection not available - that's okay for some cases
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
            $session = new \FF\Framework\Session\SessionManager();
            $this->singleton('session', $session);
        } catch (\Exception $e) {
            // Session failed to initialize - that's okay
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
                $debugBar = new \FF\Framework\Debug\DebugBar(true);
                $this->singleton('debugbar', $debugBar);
            } catch (\Exception $e) {
                // DebugBar failed to load - that's okay
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
}
