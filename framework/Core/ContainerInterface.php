<?php

namespace FF\Core;

/**
 * ContainerInterface - Dependency Injection Container Interface
 * 
 * Defines the contract for dependency injection container implementations.
 */
interface ContainerInterface
{
    /**
     * Register a binding in the container
     * 
     * @param string $abstract The abstract name/interface
     * @param mixed $concrete The concrete implementation (class name, closure, or instance)
     * @return void
     */
    public function bind(string $abstract, $concrete): void;

    /**
     * Register a singleton binding in the container
     * 
     * Only one instance will be created and reused
     * 
     * @param string $abstract The abstract name/interface
     * @param mixed $concrete The concrete implementation
     * @return void
     */
    public function singleton(string $abstract, $concrete): void;

    /**
     * Resolve and get an instance from the container
     * 
     * @param string $abstract The abstract name/interface
     * @return mixed The resolved instance
     * @throws \Exception If the abstract cannot be resolved
     */
    public function get(string $abstract);

    /**
     * Check if a binding exists in the container
     * 
     * @param string $abstract The abstract name/interface
     * @return bool True if binding exists
     */
    public function has(string $abstract): bool;
}
