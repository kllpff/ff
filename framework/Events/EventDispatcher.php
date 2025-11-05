<?php

namespace FF\Framework\Events;

use Closure;

/**
 * EventDispatcher - Event Management System
 * 
 * Manages event registration and dispatch.
 * Allows listeners to subscribe to events and react to them.
 */
class EventDispatcher
{
    /**
     * Registered event listeners
     * 
     * @var array
     */
    protected array $listeners = [];

    /**
     * Register an event listener
     * 
     * @param string $event The event name
     * @param callable $listener The listener callback
     * @return void
     */
    public function listen(string $event, callable $listener): void
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $listener;
    }

    /**
     * Register a one-time event listener
     * 
     * @param string $event The event name
     * @param callable $listener The listener callback
     * @return void
     */
    public function once(string $event, callable $listener): void
    {
        $onceListener = function (...$args) use ($listener, $event) {
            $listener(...$args);
            $this->forget($event, $listener);
        };

        $this->listen($event, $onceListener);
    }

    /**
     * Dispatch an event to all listeners
     * 
     * @param string $event The event name
     * @param mixed ...$args Arguments to pass to listeners
     * @return array Results from all listeners
     */
    public function dispatch(string $event, ...$args): array
    {
        $results = [];

        if (!isset($this->listeners[$event])) {
            return $results;
        }

        foreach ($this->listeners[$event] as $listener) {
            $result = call_user_func_array($listener, $args);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Get all listeners for an event
     * 
     * @param string $event The event name
     * @return array The listeners
     */
    public function getListeners(string $event): array
    {
        return $this->listeners[$event] ?? [];
    }

    /**
     * Check if event has listeners
     * 
     * @param string $event The event name
     * @return bool
     */
    public function hasListeners(string $event): bool
    {
        return isset($this->listeners[$event]) && !empty($this->listeners[$event]);
    }

    /**
     * Remove a listener from an event
     * 
     * @param string $event The event name
     * @param callable $listener The listener to remove
     * @return void
     */
    public function forget(string $event, callable $listener): void
    {
        if (!isset($this->listeners[$event])) {
            return;
        }

        $this->listeners[$event] = array_filter(
            $this->listeners[$event],
            fn($l) => $l !== $listener
        );
    }

    /**
     * Remove all listeners for an event
     * 
     * @param string $event The event name
     * @return void
     */
    public function forgetAll(string $event): void
    {
        unset($this->listeners[$event]);
    }

    /**
     * Clear all listeners
     * 
     * @return void
     */
    public function flush(): void
    {
        $this->listeners = [];
    }

    /**
     * Alias for dispatch
     * 
     * @param string $event The event name
     * @param mixed ...$args Arguments
     * @return array
     */
    public function emit(string $event, ...$args): array
    {
        return $this->dispatch($event, ...$args);
    }
}
