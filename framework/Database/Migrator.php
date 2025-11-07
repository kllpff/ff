<?php

namespace FF\Database;

/**
 * Migrator - Database Migration Manager
 */
class Migrator
{
    protected Connection $connection;
    protected string $migrationsPath;

    public function __construct(Connection $connection, string $migrationsPath = 'database/migrations')
    {
        $this->connection = $connection;
        $this->migrationsPath = base_path($migrationsPath);
        $this->createMigrationsTable();
    }

    /**
     * Run all pending migrations
     */
    public function run(): void
    {
        $migrations = $this->getPendingMigrations();

        foreach ($migrations as $migration) {
            $this->runMigration($migration);
        }
    }

    /**
     * Run a specific migration
     */
    protected function runMigration(string $path): void
    {
        $class = $this->getMigrationClass($path);

        if (class_exists($class)) {
            $this->connection->beginTransaction();
            try {
                $migration = new $class();
                $migration->up();
                $this->recordMigration(basename($path, '.php'));
                $this->connection->commit();
            } catch (\Exception $e) {
                $this->connection->rollback();
                throw $e;
            }
        }
    }

    /**
     * Rollback all migrations
     */
    public function rollback(): void
    {
        $migrations = $this->getRanMigrations();

        foreach (array_reverse($migrations) as $migration) {
            $this->rollbackMigration($migration);
        }
    }

    /**
     * Rollback a specific migration
     */
    protected function rollbackMigration(string $migration): void
    {
        $path = $this->findMigrationPath($migration);

        if ($path && file_exists($path)) {
            $class = $this->getMigrationClass($path);

            if (class_exists($class)) {
                $this->connection->beginTransaction();
                try {
                    $instance = new $class();
                    $instance->down();
                    $this->forgetMigration($migration);
                    $this->connection->commit();
                } catch (\Exception $e) {
                    $this->connection->rollback();
                    throw $e;
                }
            }
        }
    }

    /**
     * Get pending migrations
     */
    protected function getPendingMigrations(): array
    {
        $files = $this->getMigrationFiles();
        $ran = $this->getRanMigrations();

        return array_filter($files, function($file) use ($ran) {
            $name = basename($file, '.php');
            return !in_array($name, $ran);
        });
    }

    /**
     * Get migration files
     */
    protected function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = scandir($this->migrationsPath);
        $files = array_filter($files, function($file) {
            return strpos($file, '.php') !== false;
        });

        return array_map(fn($f) => $this->migrationsPath . '/' . $f, $files);
    }

    /**
     * Get migrations that have been run
     */
    protected function getRanMigrations(): array
    {
        $qb = new QueryBuilder($this->connection);
        $result = $qb->table('migrations')->select(['migration'])->orderBy('batch', 'asc')->get();
        return array_column($result, 'migration');
    }

    /**
     * Record migration
     */
    protected function recordMigration(string $migration): void
    {
        $batch = $this->getNextBatchNumber();
        $qb = new QueryBuilder($this->connection);
        $qb->table('migrations')->insert([
            'migration' => $migration,
            'batch' => $batch,
            'executed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Forget migration
     */
    protected function forgetMigration(string $migration): void
    {
        $qb = new QueryBuilder($this->connection);
        $qb->table('migrations')->where('migration', $migration)->delete();
    }

    /**
     * Get next batch number
     */
    protected function getNextBatchNumber(): int
    {
        $qb = new QueryBuilder($this->connection);
        $result = $qb->table('migrations')->select(['MAX(batch) as max_batch'])->get();
        return ($result[0]['max_batch'] ?? 0) + 1;
    }

    /**
     * Create migrations table
     */
    protected function createMigrationsTable(): void
    {
        $schema = new SchemaBuilder($this->connection);

        try {
            (new QueryBuilder($this->connection))
                ->table('migrations')
                ->limit(1)
                ->get();
        } catch (\Exception $e) {
            $schema->create('migrations', function($table) {
                $table->increments('id');
                $table->string('migration', 255);
                $table->integer('batch');
                $table->timestamp('executed_at');
            });
        }
    }

    /**
     * Get migration class name
     */
    protected function getMigrationClass(string $path): string
    {
        $filename = basename($path, '.php');
        $parts = explode('_', $filename, 2);
        $className = isset($parts[1]) ? $this->studlyCase($parts[1]) : $this->studlyCase($filename);

        return "Database\\Migrations\\$className";
    }

    /**
     * Find migration path by name
     */
    protected function findMigrationPath(string $migration): ?string
    {
        $files = $this->getMigrationFiles();

        foreach ($files as $file) {
            if (basename($file, '.php') === $migration) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Convert string to studly case
     */
    protected function studlyCase(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $value)));
    }
}
