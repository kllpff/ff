<?php

namespace FF\Framework\Http\Controllers;

use FF\Framework\Core\Container;
use FF\Framework\Http\Request;

/**
 * Controller - Base Controller Class
 * 
 * Base class for all application controllers.
 * Provides common functionality for request handling and response generation.
 */
abstract class Controller
{
    /**
     * The container instance
     * 
     * @var Container
     */
    protected Container $container;

    /**
     * Create a new Controller instance
     * 
     * @param Container $container The container instance
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Validate incoming request data
     * 
     * @param Request $request The incoming request
     * @param array $rules Validation rules
     * @return array The validated data
     * @throws \Exception If validation fails
     */
    public function validate(Request $request, array $rules): array
    {
        // Validation will be fully implemented in Stage 7
        // For now, return all input
        return $request->all();
    }

    /**
     * Register middleware for this controller
     * 
     * @param string|array $middleware The middleware to apply
     * @return void
     */
    public function middleware($middleware): void
    {
        // Middleware assignment to controller methods
        // Full implementation in Stage 3.4
    }

    /**
     * Get the container instance
     * 
     * @return Container
     */
    protected function getContainer(): Container
    {
        return $this->container;
    }
}
