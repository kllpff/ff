<?php

namespace FF\Events;

/**
 * EventDispatcher - Event Management System
 */
class EventDispatcher
{
    /**
     * @var array<string,array<int,array{callback:callable,once:bool}>>
     */
    protected array $listeners = [];

    /**
     * Register a listener for the given event name/class.
     */
    public function listen(string $event, callable $listener, bool $once = false): void
    {
        $event = $this->normalizeEventName($event);

        $this->listeners[$event][] = [
            'callback' => $listener,
            'once' => $once,
        ];
    }

    public function once(string $event, callable $listener): void
    {
        $this->listen($event, $listener, true);
    }

    /**
     * Dispatch an event instance or name.
     *
     * @param string|object $event
     */
    public function dispatch($event, ...$payload): array
    {
        [$eventName, $payload] = $this->prepareEventPayload($event, $payload);
        $results = [];

        if (empty($this->listeners[$eventName])) {
            return $results;
        }

        foreach ($this->listeners[$eventName] as $index => $listener) {
            $callback = $listener['callback'];
            $results[] = $callback(...$payload);

            if ($listener['once']) {
                unset($this->listeners[$eventName][$index]);
            }
        }

        if (empty($this->listeners[$eventName])) {
            unset($this->listeners[$eventName]);
        } else {
            $this->listeners[$eventName] = array_values($this->listeners[$eventName]);
        }

        return $results;
    }

    public function emit($event, ...$payload): array
    {
        return $this->dispatch($event, ...$payload);
    }

    public function getListeners(string $event): array
    {
        $event = $this->normalizeEventName($event);
        return $this->listeners[$event] ?? [];
    }

    public function hasListeners(string $event): bool
    {
        $event = $this->normalizeEventName($event);
        return !empty($this->listeners[$event]);
    }

    public function forget(string $event, callable $listener): void
    {
        $event = $this->normalizeEventName($event);

        if (empty($this->listeners[$event])) {
            return;
        }

        $this->listeners[$event] = array_values(array_filter(
            $this->listeners[$event],
            fn ($entry) => ($entry['callback'] ?? null) !== $listener
        ));

        if (empty($this->listeners[$event])) {
            unset($this->listeners[$event]);
        }
    }

    public function forgetAll(string $event): void
    {
        unset($this->listeners[$this->normalizeEventName($event)]);
    }

    public function flush(): void
    {
        $this->listeners = [];
    }

    protected function normalizeEventName(string $event): string
    {
        return strtolower(trim($event));
    }

    /**
     * Prepare normalized event name and payload list.
     *
     * @param string|object $event
     * @param array $payload
     * @return array{0:string,1:array}
     */
    protected function prepareEventPayload($event, array $payload): array
    {
        if ($event instanceof Event) {
            $payload = array_merge([$event], $payload);
            return [$this->normalizeEventName($event->getName()), $payload];
        }

        if (is_object($event)) {
            $payload = array_merge([$event], $payload);
            return [$this->normalizeEventName(get_class($event)), $payload];
        }

        if (!is_string($event) || trim($event) === '') {
            throw new \InvalidArgumentException('Event name must be a non-empty string or an event object.');
        }

        return [$this->normalizeEventName($event), $payload];
    }
}
