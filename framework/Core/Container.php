<?php

namespace FF\Core;

use Closure;
use ReflectionClass;
use ReflectionMethod;

/**
 * Container - Dependency Injection Container
 * 
 * Manages dependency bindings, resolution, and instantiation.
 * Supports automatic constructor injection via Reflection.
 */
class Container implements ContainerInterface
{
    /**
     * Array of registered bindings
     * 
     * @var array
     */
    protected array $bindings = [];

    /**
     * Array of singleton instances
     * 
     * @var array
     */
    protected array $instances = [];

    /**
     * Register a binding in the container
     * 
     * @param string $abstract The abstract name/interface
     * @param mixed $concrete The concrete implementation (class name, closure, or instance)
     * @return void
     */
    public function bind(string $abstract, $concrete): void
    {
        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Register a singleton binding in the container
     * 
     * Only one instance will be created and reused
     * 
     * @param string $abstract The abstract name/interface
     * @param mixed $concrete The concrete implementation
     * @return void
     */
    public function singleton(string $abstract, $concrete): void
    {
        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = true; // Mark as singleton
    }

    /**
     * Resolve and get an instance from the container
     * 
     * @param string $abstract The abstract name/interface
     * @return mixed The resolved instance
     * @throws \Exception If the abstract cannot be resolved
     */
    public function get(string $abstract)
    {
        // Check if it's a singleton and already instantiated
        if (isset($this->instances[$abstract]) && $this->instances[$abstract] !== true) {
            return $this->instances[$abstract];
        }

        return $this->resolve($abstract);
    }

    /**
     * Resolve a binding - the core resolution logic
     * 
     * @param string $abstract The abstract name/interface
     * @return mixed The resolved instance
     * @throws \Exception If the abstract cannot be resolved
     */
    public function resolve(string $abstract)
    {
        // Check if binding exists
        if (!isset($this->bindings[$abstract])) {
            // Try to auto-resolve if it's a concrete class
            if (class_exists($abstract)) {
                return $this->resolveClass($abstract);
            }
            throw new \Exception("Unable to resolve '{$abstract}' - binding not found");
        }

        $concrete = $this->bindings[$abstract];

        // If it's a closure, call it with the container
        if ($concrete instanceof Closure) {
            $instance = $concrete($this);
        } else if (is_string($concrete) && class_exists($concrete)) {
            // If it's a class name string that exists, resolve it
            $instance = $this->resolveClass($concrete);
        } else {
            // Otherwise assume it's already an instance or primitive value
            $instance = $concrete;
        }

        // Store singleton instance if marked as singleton
        if (isset($this->instances[$abstract]) && $this->instances[$abstract] === true) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Resolve a class by auto-injecting constructor dependencies
     * 
     * Uses Reflection to analyze constructor parameters and auto-inject dependencies
     * 
     * @param string $className The class name to resolve
     * @return mixed The instantiated class
     * @throws \Exception If constructor parameters cannot be resolved
     */
    protected function resolveClass(string $className)
    {
        try {
            $reflection = new ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new \Exception("Cannot resolve class '{$className}': {$e->getMessage()}");
        }

        // Check if class is instantiable
        if (!$reflection->isInstantiable()) {
            throw new \Exception("Class '{$className}' is not instantiable");
        }

        // Get the constructor
        $constructor = $reflection->getConstructor();

        // If no constructor, just instantiate
        if (!$constructor) {
            return new $className();
        }

        // Get constructor parameters
        $parameters = $constructor->getParameters();
        $dependencies = [];

        // Resolve each parameter dependency
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if (!$type) {
                // No type hint - check if has default value
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception(
                        "Cannot resolve parameter '{$parameter->getName()}' in {$className}::__construct()"
                    );
                }
                continue;
            }

            $typeName = $type->getName();

            // Try to resolve the dependency
            if ($this->has($typeName)) {
                $dependencies[] = $this->get($typeName);
            } else if (class_exists($typeName)) {
                $dependencies[] = $this->resolveClass($typeName);
            } else if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new \Exception(
                    "Cannot resolve type '{$typeName}' for parameter '{$parameter->getName()}' in {$className}::__construct()"
                );
            }
        }

        // Instantiate with resolved dependencies
        return new $className(...$dependencies);
    }

    /**
     * Check if a binding exists in the container
     * 
     * @param string $abstract The abstract name/interface
     * @return bool True if binding exists
     */
    public function has(string $abstract): bool
    {
        return isset($this->bindings[$abstract]) || class_exists($abstract);
    }

    /**
     * Make an instance (alias for get)
     * 
     * @param string $abstract The abstract name/interface
     * @return mixed The resolved instance
     */
    public function make(string $abstract)
    {
        return $this->get($abstract);
    }

    /**
     * Clear all instances and bindings
     * 
     * @return void
     */
    public function flush(): void
    {
        $this->bindings = [];
        $this->instances = [];
    }
}
