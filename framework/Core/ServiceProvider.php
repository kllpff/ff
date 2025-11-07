<?php

namespace FF\Core;

/**
 * ServiceProvider - Base Service Provider Class
 * 
 * Service providers are responsible for bootstrapping components
 * and registering bindings into the container.
 */
abstract class ServiceProvider
{
    /**
     * The application instance
     * 
     * @var Application
     */
    protected Application $app;

    /**
     * Create a new service provider instance
     * 
     * @param Application $app The application instance
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register any application services
     * 
     * This method is called when the service provider is registered.
     * Use this to register bindings into the container.
     * 
     * @return void
     */
    abstract public function register(): void;

    /**
     * Bootstrap any application services
     * 
     * This method is called after all services have been registered.
     * Use this to perform additional setup after all bindings are in place.
     * 
     * @return void
     */
    public function boot(): void
    {
        // Optional - can be overridden in child classes
    }

    /**
     * Get the application instance
     * 
     * @return Application
     */
    protected function getApp(): Application
    {
        return $this->app;
    }
}
