<?php

namespace FF\Framework\Database;

use Closure;

/**
 * SchemaBuilder - Database Schema Management
 * 
 * Handles table creation, modification, and deletion
 */
class SchemaBuilder
{
    protected Connection $connection;
    protected ?Table $table = null;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Create a new table
     */
    public function create(string $table, Closure $callback): void
    {
        $this->table = new Table($table, true);
        $callback($this->table);
        $this->executeCreate();
    }

    /**
     * Modify an existing table
     */
    public function table(string $table, Closure $callback): void
    {
        $this->table = new Table($table, false);
        $callback($this->table);
        $this->executeAlter();
    }

    /**
     * Drop a table
     */
    public function drop(string $table): void
    {
        $sql = "DROP TABLE $table";
        $this->connection->statement($sql);
    }

    /**
     * Drop table if exists
     */
    public function dropIfExists(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS $table";
        $this->connection->statement($sql);
    }

    /**
     * Execute table creation
     */
    protected function executeCreate(): void
    {
        $sql = $this->compileCreate();
        $this->connection->statement($sql);
    }

    /**
     * Execute table alteration
     */
    protected function executeAlter(): void
    {
        if (empty($this->table->getCommands())) {
            return;
        }

        foreach ($this->table->getCommands() as $command) {
            $sql = $this->compileCommand($command);
            $this->connection->statement($sql);
        }
    }

    /**
     * Compile CREATE TABLE statement
     */
    protected function compileCreate(): string
    {
        $name = $this->table->getName();
        $columns = $this->compileColumns();
        $keys = $this->compileKeys();

        return "CREATE TABLE $name (\n$columns" . ($keys ? ",\n$keys" : '') . "\n)";
    }

    /**
     * Compile column definitions
     */
    protected function compileColumns(): string
    {
        $lines = [];

        foreach ($this->table->getColumns() as $column) {
            $definition = $this->compileColumn($column);
            $lines[] = "  $definition";
        }

        return implode(",\n", $lines);
    }

    /**
     * Compile single column
     */
    protected function compileColumn(Column $column): string
    {
        $sql = $column->getName() . ' ' . $this->getColumnType($column);

        if ($column->isNullable() === false) {
            $sql .= ' NOT NULL';
        }

        if ($column->getDefault() !== null) {
            $default = $column->getDefault();
            if (is_string($default)) {
                $sql .= " DEFAULT '$default'";
            } else {
                $sql .= " DEFAULT $default";
            }
        }

        if ($column->isAutoIncrement()) {
            $sql .= ' AUTO_INCREMENT';
        }

        if ($column->getComment()) {
            $comment = $column->getComment();
            $sql .= " COMMENT '$comment'";
        }

        return $sql;
    }

    /**
     * Get column type definition
     */
    protected function getColumnType(Column $column): string
    {
        $type = $column->getType();

        switch ($type) {
            case 'string':
                $length = $column->getLength() ?? 255;
                return "VARCHAR($length)";
            case 'text':
                return 'LONGTEXT';
            case 'integer':
                return 'INT';
            case 'bigInteger':
                return 'BIGINT';
            case 'boolean':
                return 'BOOLEAN';
            case 'float':
                return 'FLOAT';
            case 'decimal':
                $precision = $column->getPrecision() ?? 8;
                $scale = $column->getScale() ?? 2;
                return "DECIMAL($precision,$scale)";
            case 'timestamp':
                return 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP';
            case 'dateTime':
                return 'DATETIME';
            case 'date':
                return 'DATE';
            case 'time':
                return 'TIME';
            case 'json':
                return 'JSON';
            default:
                return 'VARCHAR(255)';
        }
    }

    /**
     * Compile key constraints
     */
    protected function compileKeys(): string
    {
        $lines = [];

        foreach ($this->table->getPrimaryKeys() as $key) {
            $columns = implode(', ', $key);
            $lines[] = "  PRIMARY KEY ($columns)";
        }

        foreach ($this->table->getUniqueKeys() as $name => $columns) {
            $columnStr = implode(', ', $columns);
            $lines[] = "  UNIQUE KEY `$name` ($columnStr)";
        }

        foreach ($this->table->getForeignKeys() as $name => $fk) {
            $local = implode(', ', $fk['columns']);
            $foreign = implode(', ', $fk['references']);
            $lines[] = "  CONSTRAINT `$name` FOREIGN KEY ($local) REFERENCES `{$fk['table']}` ($foreign)";
        }

        foreach ($this->table->getIndexes() as $name => $columns) {
            $columnStr = implode(', ', $columns);
            $lines[] = "  INDEX `$name` ($columnStr)";
        }

        return implode(",\n", $lines);
    }

    /**
     * Compile ALTER TABLE command
     */
    protected function compileCommand(array $command): string
    {
        $table = $this->table->getName();
        $action = $command['action'];

        switch ($action) {
            case 'add':
                $column = $command['column'];
                $definition = $this->compileColumn($column);
                return "ALTER TABLE $table ADD COLUMN $definition";

            case 'drop':
                $column = $command['column'];
                return "ALTER TABLE $table DROP COLUMN $column";

            case 'rename':
                $old = $command['old'];
                $new = $command['new'];
                return "ALTER TABLE $table RENAME COLUMN $old TO $new";

            case 'modify':
                $column = $command['column'];
                $definition = $this->compileColumn($column);
                return "ALTER TABLE $table MODIFY COLUMN $definition";

            default:
                return '';
        }
    }
}
