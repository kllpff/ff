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
        // Avoid calling parent::first() because it internally calls $this->get(),
        // which in this subclass returns Model instances and leads to double-wrapping.
        $this->limit(1);
        $rows = parent::get(); // raw array rows from QueryBuilder
        $row = $rows[0] ?? null;
        if (!$row) {
            return null;
        }
        $model = new $this->modelClass([]);
        // Hydrate all attributes from DB, bypassing mass-assignment restrictions
        $model->forceFill((array) $row);
        // Make sure the primary key and existence flag are hydrated
        $pk = $model->getPrimaryKey();
        if (is_array($row) && array_key_exists($pk, $row)) {
            $model->forceFill([$pk => $row[$pk]]);
        }
        $model->markExists(true);
        return $model;
    }

    /**
     * Get all results as Model instances
     *
     * @return array Array of model instances
     */
    public function get(): array
    {
        $results = parent::get();
        return array_map(function ($row) {
            $model = new $this->modelClass([]);
            // Hydrate all attributes from DB, bypassing mass-assignment restrictions
            if (is_array($row)) {
                $model->forceFill($row);
                $pk = $model->getPrimaryKey();
                if (array_key_exists($pk, $row)) {
                    $model->forceFill([$pk => $row[$pk]]);
                }
            }
            return $model->markExists(true);
        }, $results);
    }

    /**
     * Find a record by ID and return as Model
     *
     * @param mixed $id The ID value
     * @return object|null The model instance or null
     */
    public function find($id): ?object
    {
        return $this->where('id', $id)->first();
    }
}
