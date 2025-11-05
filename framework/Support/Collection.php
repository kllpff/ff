<?php

namespace FF\Framework\Support;

use Countable;
use Iterator;
use ArrayAccess;

/**
 * Collection - Array Collection Wrapper
 * 
 * Provides a fluent, chainable interface for working with arrays.
 * Implements commonly needed array operations.
 */
class Collection implements Countable, Iterator, ArrayAccess
{
    /**
     * The items in the collection
     * 
     * @var array
     */
    protected array $items = [];

    /**
     * Current position for Iterator
     * 
     * @var int
     */
    protected int $position = 0;

    /**
     * Create a new Collection instance
     * 
     * @param array $items The initial items
     */
    public function __construct(array $items = [])
    {
        $this->items = array_values($items); // Re-index numeric keys
        $this->position = 0;
    }

    /**
     * Create a new collection from array
     * 
     * @param array $items The items
     * @return self
     */
    public static function make(array $items = []): self
    {
        return new self($items);
    }

    /**
     * Get all items in the collection
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get the number of items
     * 
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Check if collection is empty
     * 
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }

    /**
     * Check if collection is not empty
     * 
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Get the first item
     * 
     * @param callable|null $callback Optional filter callback
     * @param mixed $default Default value
     * @return mixed
     */
    public function first(?callable $callback = null, $default = null)
    {
        if ($callback === null) {
            return $this->items[0] ?? $default;
        }

        foreach ($this->items as $item) {
            if ($callback($item)) {
                return $item;
            }
        }

        return $default;
    }

    /**
     * Get the last item
     * 
     * @param callable|null $callback Optional filter callback
     * @param mixed $default Default value
     * @return mixed
     */
    public function last(?callable $callback = null, $default = null)
    {
        if ($callback === null) {
            return end($this->items) ?: $default;
        }

        for ($i = count($this->items) - 1; $i >= 0; $i--) {
            if ($callback($this->items[$i])) {
                return $this->items[$i];
            }
        }

        return $default;
    }

    /**
     * Filter items
     * 
     * @param callable $callback
     * @return self
     */
    public function filter(callable $callback): self
    {
        return new self(array_filter($this->items, $callback));
    }

    /**
     * Map items
     * 
     * @param callable $callback
     * @return self
     */
    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->items));
    }

    /**
     * Reduce items to single value
     * 
     * @param callable $callback
     * @param mixed $initial Initial value
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Check if any item matches callback
     * 
     * @param callable $callback
     * @return bool
     */
    public function any(callable $callback): bool
    {
        foreach ($this->items as $item) {
            if ($callback($item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if all items match callback
     * 
     * @param callable $callback
     * @return bool
     */
    public function every(callable $callback): bool
    {
        foreach ($this->items as $item) {
            if (!$callback($item)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get unique items
     * 
     * @return self
     */
    public function unique(): self
    {
        return new self(array_unique($this->items));
    }

    /**
     * Sort items
     * 
     * @param callable|null $callback Optional comparison callback
     * @return self
     */
    public function sort(?callable $callback = null): self
    {
        $items = $this->items;
        
        if ($callback) {
            usort($items, $callback);
        } else {
            sort($items);
        }

        return new self($items);
    }

    /**
     * Reverse the collection
     * 
     * @return self
     */
    public function reverse(): self
    {
        return new self(array_reverse($this->items));
    }

    /**
     * Get items in chunks
     * 
     * @param int $size The chunk size
     * @return self Collection of chunks
     */
    public function chunk(int $size): self
    {
        $chunks = array_chunk($this->items, $size);
        return new self(array_map(fn($chunk) => new self($chunk), $chunks));
    }

    /**
     * Pluck a value from each item
     * 
     * @param string $key The key to pluck
     * @return self
     */
    public function pluck(string $key): self
    {
        return $this->map(function ($item) use ($key) {
            if (is_array($item)) {
                return $item[$key] ?? null;
            } else if (is_object($item)) {
                return $item->$key ?? null;
            }
            return null;
        });
    }

    /**
     * Group items by a key
     * 
     * @param callable|string $callback The grouping callback or property
     * @return self Collection of groups
     */
    public function groupBy($callback): self
    {
        $groups = [];

        foreach ($this->items as $item) {
            $key = is_callable($callback) ? $callback($item) : (is_array($item) ? $item[$callback] : $item->$callback);

            if (!isset($groups[$key])) {
                $groups[$key] = [];
            }

            $groups[$key][] = $item;
        }

        return new self(array_map(fn($group) => new self($group), $groups));
    }

    /**
     * Push an item to the collection
     * 
     * @param mixed $item The item to add
     * @return self
     */
    public function push($item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Merge with another collection or array
     * 
     * @param array|self $items Items to merge
     * @return self
     */
    public function merge($items): self
    {
        if ($items instanceof self) {
            $items = $items->all();
        }

        return new self(array_merge($this->items, $items));
    }

    /**
     * Get a slice of items
     * 
     * @param int $offset The offset
     * @param int|null $length The length
     * @return self
     */
    public function slice(int $offset, ?int $length = null): self
    {
        return new self(array_slice($this->items, $offset, $length));
    }

    /**
     * Join items into a string
     * 
     * @param string $separator The separator
     * @return string
     */
    public function join(string $separator = ', '): string
    {
        return implode($separator, $this->items);
    }

    /**
     * Convert to array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Convert to JSON
     * 
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->items);
    }

    // Iterator interface implementation

    public function current(): mixed
    {
        return $this->items[$this->position] ?? null;
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function next(): void
    {
        $this->position++;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }

    // ArrayAccess interface implementation

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }
}
