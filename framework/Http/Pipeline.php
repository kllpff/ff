<?php

namespace FF\Framework\Http;

use Closure;
use FF\Framework\Core\Container;

/**
 * Pipeline - Middleware Pipeline
 * 
 * Processes a request through a series of middleware classes.
 * Implements the pipe-and-filter pattern for middleware chaining.
 */
class Pipeline
{
    /**
     * The container instance for resolving middleware
     * 
     * @var Container
     */
    protected Container $container;

    /**
     * The request object
     * 
     * @var Request
     */
    protected Request $request;

    /**
     * The middleware to execute
     * 
     * @var array
     */
    protected array $middleware = [];

    /**
     * Create a new Pipeline instance
     * 
     * @param Container $container The container for resolving middleware
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Set the request to send through the pipeline
     * 
     * @param Request $request The request
     * @return self
     */
    public function send(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Set the middleware to execute
     * 
     * @param array $middleware Array of middleware class names
     * @return self
     */
    public function through(array $middleware): self
    {
        $this->middleware = $middleware;
        return $this;
    }

    /**
     * Execute the pipeline and get the final result
     * 
     * @param Closure $destination The final handler
     * @return mixed The response
     */
    public function then(Closure $destination)
    {
        // Build the middleware stack from right to left
        $pipeline = $destination;

        // Reverse middleware so we build the stack correctly
        foreach (array_reverse($this->middleware) as $middlewareName) {
            $pipeline = $this->createMiddlewareCallable($middlewareName, $pipeline);
        }

        // Execute the pipeline with the request
        return $pipeline($this->request);
    }

    /**
     * Create a callable for a middleware
     * 
     * @param string $middlewareName The middleware class name
     * @param Closure $next The next middleware in the chain
     * @return Closure
     */
    protected function createMiddlewareCallable(string $middlewareName, Closure $next): Closure
    {
        return function (Request $request) use ($middlewareName, $next) {
            // Resolve the middleware from the container
            $middleware = $this->container->make($middlewareName);

            // Call the middleware's handle method
            return $middleware->handle($request, function ($request) use ($next) {
                return $next($request);
            });
        };
    }
}
