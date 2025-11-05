<?php

namespace FF\Framework\Core;

use FF\Framework\Http\Request;
use FF\Framework\Http\Response;
use FF\Framework\Http\Router;
use FF\Framework\Debug\ExceptionHandler;

/**
 * Kernel - HTTP Kernel
 * 
 * Handles HTTP request processing, middleware pipeline execution,
 * and routing to appropriate controllers.
 */
class Kernel
{
    /**
     * The application instance
     * 
     * @var Application
     */
    protected Application $app;

    /**
     * Array of global HTTP middleware
     * 
     * @var array
     */
    protected array $middleware = [];

    /**
     * Create a new Kernel instance
     * 
     * @param Application $app The application instance
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an HTTP request and return a response
     * 
     * @param Request $request The HTTP request
     * @return Response The HTTP response
     */
    public function handle(Request $request): Response
    {
        try {
            return $this->sendRequest($request);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Send the request through the application
     * 
     * @param Request $request The HTTP request
     * @return Response The HTTP response
     */
    protected function sendRequest(Request $request): Response
    {
        // Get the router from the container
        $router = $this->app->make(Router::class);
        
        // Set the container on the router
        if (method_exists($router, 'setContainer')) {
            $router->setContainer($this->app);
        }

        // Load routes from config
        $this->loadRoutes($router);

        // Dispatch the request to the router
        $response = $router->dispatch($request);

        // Ensure we have a Response object
        if (!($response instanceof Response)) {
            $response = new Response($response ?? '');
        }

        return $response;
    }

    /**
     * Load routes from configuration
     * 
     * @param Router $router The router instance
     * @return void
     */
    protected function loadRoutes(Router $router): void
    {
        $routesPath = $this->app->configPath('routes.php');
        
        if (file_exists($routesPath)) {
            $callback = require $routesPath;
            
            // If routes config returns a callable, call it with the router
            if (is_callable($callback)) {
                call_user_func($callback, $router);
            }
        }
    }

    /**
     * Handle an exception during request processing
     * 
     * @param \Throwable $e The exception
     * @return Response The error response
     */
    protected function handleException(\Throwable $e): Response
    {
        $handler = new ExceptionHandler($this->app);
        return $handler->handle($e);
    }

    /**
     * Register global middleware
     * 
     * @param string|array $middleware The middleware class(es) to register
     * @return self
     */
    public function registerMiddleware($middleware): self
    {
        if (is_string($middleware)) {
            $this->middleware[] = $middleware;
        } else if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        }

        return $this;
    }

    /**
     * Get all registered global middleware
     * 
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}
