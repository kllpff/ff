<?php

namespace FF\Core;

use FF\Http\Request;
use FF\Http\Response;
use FF\Http\Router;
use FF\Debug\ExceptionHandler;
use FF\Http\Middleware\MiddlewareInterface;

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

        $destination = function (Request $request) use ($router) {
            return $router->dispatch($request);
        };

        $response = $this->runMiddlewarePipeline($request, $destination);

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

    /**
     * Execute the middleware pipeline.
     *
     * @param Request $request
     * @param callable $destination
     * @return mixed
     */
    protected function runMiddlewarePipeline(Request $request, callable $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            function (callable $next, $middleware) {
                return $this->wrapMiddleware($middleware, $next);
            },
            $destination
        );

        return $pipeline($request);
    }

    /**
     * Wrap a middleware around the next callable in the chain.
     *
     * @param string|MiddlewareInterface $middleware
     * @param callable $next
     * @return callable
     */
    protected function wrapMiddleware($middleware, callable $next): callable
    {
        return function (Request $request) use ($middleware, $next) {
            $instance = $this->resolveMiddleware($middleware);

            return $instance->handle($request, function (Request $request) use ($next) {
                return $next($request);
            });
        };
    }

    /**
     * Resolve a middleware definition to an instance.
     *
     * @param string|MiddlewareInterface $middleware
     * @return MiddlewareInterface
     */
    protected function resolveMiddleware($middleware): MiddlewareInterface
    {
        if (is_string($middleware)) {
            $instance = $this->app->make($middleware);
        } else {
            $instance = $middleware;
        }

        if (!$instance instanceof MiddlewareInterface) {
            throw new \Exception('Invalid middleware provided to kernel.');
        }

        return $instance;
    }
}
