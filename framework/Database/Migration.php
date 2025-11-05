<?php

namespace FF\Framework\Database;

use Closure;

/**
 * Migration - Database Migration Base Class
 * 
 * Base class for all database migrations
 */
abstract class Migration
{
    /**
     * Connection for this migration
     */
    protected ?Connection $connection = null;

    /**
     * Run the migration
     */
    abstract public function up(): void;

    /**
     * Rollback the migration
     */
    abstract public function down(): void;

    /**
     * Get connection
     */
    protected function getConnection(): Connection
    {
        return $this->connection ?? app(Connection::class);
    }

    /**
     * Create table
     */
    protected function create(string $table, Closure $callback): void
    {
        $builder = new SchemaBuilder($this->getConnection());
        $builder->create($table, $callback);
    }

    /**
     * Modify table
     */
    protected function table(string $table, Closure $callback): void
    {
        $builder = new SchemaBuilder($this->getConnection());
        $builder->table($table, $callback);
    }

    /**
     * Drop table
     */
    protected function drop(string $table): void
    {
        $builder = new SchemaBuilder($this->getConnection());
        $builder->drop($table);
    }

    /**
     * Drop table if exists
     */
    protected function dropIfExists(string $table): void
    {
        $builder = new SchemaBuilder($this->getConnection());
        $builder->dropIfExists($table);
    }
}
