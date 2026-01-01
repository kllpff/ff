<?php

namespace FF\Database;

use JsonSerializable;

/**
 * Model - Base Eloquent-style Model Class
 * 
 * Provides ORM functionality for database records.
 * Supports CRUD operations, mass assignment, and attribute access.
 */
abstract class Model implements JsonSerializable
{
    /**
     * The table name
     * 
     * @var string
     */
    protected string $table = '';

    /**
     * The primary key column
     * 
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Columns that can be mass-assigned
     * 
     * @var array
     */
    protected array $fillable = [];

    /**
     * Columns that cannot be mass-assigned
     * 
     * @var array
     */
    protected array $guarded = [];

    /**
     * Model attributes
     * 
     * @var array
     */
    protected array $attributes = [];

    /**
     * Original attributes (for change tracking)
     * 
     * @var array
     */
    protected array $original = [];

    /**
     * Whether the model exists in the database
     * 
     * @var bool
     */
    protected bool $exists = false;

    /**
     * The database connection
     * 
     * @var Connection|null
     */
    protected static ?Connection $connection = null;

    /**
     * Create a new Model instance
     * 
     * @param array $attributes Initial attributes
     */
    public function __construct(array $attributes = [])
    {
        if (!$this->table) {
            $this->table = strtolower(class_basename(static::class)) . 's';
        }

        $this->fill($attributes);
        $this->original = $this->attributes;
    }

    /**
     * Get the table name
     * 
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get the primary key name
     * 
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * Mark the model as existing or not
     * 
     * @param bool $exists
     * @return self
     */
    public function markExists(bool $exists = true): self
    {
        $this->exists = $exists;
        $this->original = $this->attributes;
        return $this;
    }

    /**
     * Get a new QueryBuilder for this model
     * 
     * @return ModelQueryBuilder
     */
    public static function query(): ModelQueryBuilder
    {
        return (new static())->newQuery();
    }

    /**
     * Get a new QueryBuilder instance for this model
     * 
     * @return ModelQueryBuilder
     */
    protected function newQuery(): ModelQueryBuilder
    {
        $builder = new ModelQueryBuilder(static::getConnection(), static::class);
        return $builder->table($this->table);
    }

    /**
     * Get all records
     * 
     * @return array Array of model instances
     */
    public static function all(): array
    {
        return static::query()->get();
    }

    /**
     * Find a record by ID
     * 
     * @param mixed $id The ID
     * @return static|null The model instance or null
     */
    public static function find($id)
    {
        return static::query()->find($id);
    }

    /**
     * Count the number of records for the model's table
     * 
     * @return int The total count
     */
    public static function count(): int
    {
        return static::query()->count();
    }

    /**
     * Find a record by ID or throw exception
     * 
     * @param mixed $id The ID
     * @return static The model instance
     * @throws \Exception If not found
     */
    public static function findOrFail($id): static
    {
        $model = static::find($id);
        if (!$model) {
            throw new \Exception(static::class . " with ID $id not found");
        }
        return $model;
    }

    /**
     * Create and save a new record
     * 
     * @param array $attributes The attributes
     * @return static The created model
     */
    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Start a query with a WHERE condition
     * 
     * @param string $column The column
     * @param mixed $operator The operator or value
     * @param mixed $value The value
     * @return QueryBuilder
     */
    public static function where(string $column, $operator = null, $value = null): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    /**
     * Fill model attributes (mass assignment)
     * 
     * @param array $attributes The attributes
     * @return self
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * Force fill attributes (ignore guarded)
     * 
     * @param array $attributes The attributes
     * @return self
     */
    public function forceFill(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Set a model attribute
     * 
     * @param string $key The attribute key
     * @param mixed $value The value
     * @return self
     */
    public function setAttribute(string $key, $value): self
    {
        // Check if attribute can be mass-assigned
        if ($this->isFillable($key)) {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    /**
     * Get a model attribute
     * 
     * @param string $key The attribute key
     * @return mixed The value or null
     */
    public function getAttribute(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Check if attribute can be mass-assigned
     * 
     * @param string $key The attribute key
     * @return bool
     */
    protected function isFillable(string $key): bool
    {
        // Prefer explicit fillable if defined
        if (!empty($this->fillable)) {
            return in_array($key, $this->fillable, true);
        }

        // Otherwise, enforce guarded rules
        if (!empty($this->guarded)) {
            if (in_array('*', $this->guarded, true)) {
                return false;
            }
            return !in_array($key, $this->guarded, true);
        }

        // Default: allow assignment when neither fillable nor guarded specified
        return true;
    }

    /**
     * Magic getter for attributes
     * 
     * @param string $key The attribute key
     * @return mixed The value
     */
    public function __get(string $key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic setter for attributes
     * 
     * @param string $key The attribute key
     * @param mixed $value The value
     * @return void
     */
    public function __set(string $key, $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Magic isset for attributes (ensures empty() works with overloaded properties)
     * 
     * @param string $key The attribute key
     * @return bool True if attribute exists and is not null
     */
    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->attributes) && $this->attributes[$key] !== null;
    }

    /**
     * Convert model to array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Convert model to JSON
     * 
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->attributes);
    }

    /**
     * Implement JsonSerializable
     * 
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->attributes;
    }

    /**
     * Save the model (INSERT or UPDATE)
     * 
     * @return bool True on success
     */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->update($this->attributes) > 0;
        } else {
            $id = $this->newQuery()->insertGetId($this->attributes);
            $this->attributes[$this->primaryKey] = $id;
            $this->exists = true;
            $this->original = $this->attributes;
            return true;
        }
    }

    /**
     * Update the model
     * 
     * @param array $attributes The attributes to update
     * @return int Number of affected rows
     */
    public function update(array $attributes = []): int
    {
        if (!empty($attributes)) {
            $this->fill($attributes);
        }

        $query = $this->newQuery()->where($this->primaryKey, $this->getAttribute($this->primaryKey));
        return $query->update($this->attributes);
    }

    /**
     * Delete the model
     * 
     * @return int Number of deleted rows
     */
    public function delete(): int
    {
        return $this->newQuery()
            ->where($this->primaryKey, $this->getAttribute($this->primaryKey))
            ->delete();
    }

    /**
     * Reload the model from the database
     * 
     * @return self
     */
    public function fresh(): self
    {
        if (!$this->exists) {
            throw new \Exception('Cannot refresh a model that does not exist in database');
        }

        return static::find($this->getAttribute($this->primaryKey));
    }

    /**
     * Refresh the current model instance
     * 
     * @return self
     */
    public function refresh(): self
    {
        $fresh = $this->fresh();
        $this->attributes = $fresh->attributes;
        $this->original = $fresh->original;
        return $this;
    }

    /**
     * Set the database connection
     * 
     * @param Connection $connection
     * @return void
     */
    public static function setConnection(Connection $connection): void
    {
        static::$connection = $connection;
    }

    /**
     * Get the database connection
     * 
     * @return Connection
     */
    public static function getConnection(): Connection
    {
        if (!static::$connection) {
            throw new \Exception('No database connection set for model: ' . static::class);
        }
        return static::$connection;
    }
}

/**
 * Helper function to get the class name without namespace
 * 
 * @param string $className The full class name
 * @return string The class name
 */
function class_basename(string $className): string
{
    $parts = explode('\\', $className);
    return end($parts);
}
