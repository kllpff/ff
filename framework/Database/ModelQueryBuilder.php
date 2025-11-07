<?php

namespace FF\Database;

/**
 * ModelQueryBuilder - Query Builder for Models
 * 
 * Extends QueryBuilder to return Model instances instead of arrays.
 */
class ModelQueryBuilder extends QueryBuilder
{
    /**
     * The model class
     * 
     * @var string
     */
    protected string $modelClass;

    /**
     * Create a new ModelQueryBuilder instance
     * 
     * @param Connection $connection The database connection
     * @param string $modelClass The model class name
     */
    public function __construct(Connection $connection, string $modelClass)
    {
        parent::__construct($connection);
        $this->modelClass = $modelClass;
    }

    /**
     * Get the first result as a Model instance
     * 
     * @return object|null The model instance or null
     */
    public function first(): ?object
    {
        $result = parent::first();
        if (!$result) {
            return null;
        }
        return new $this->modelClass($result);
    }

    /**
     * Get all results as Model instances
     * 
     * @return array Array of model instances
     */
    public function get(): array
    {
        $results = parent::get();
        return array_map(fn($result) => new $this->modelClass($result), $results);
    }

    /**
     * Find a record by ID and return as Model
     * 
     * @param mixed $id The ID value
     * @return object|null The model instance or null
     */
    public function find($id): ?object
    {
        $result = parent::find($id);
        if (!$result) {
            return null;
        }
        return new $this->modelClass($result);
    }
}
