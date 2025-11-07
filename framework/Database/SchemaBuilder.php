<?php

namespace FF\Database;

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
    
    /**
     * Validate and normalize SQL identifiers (tables, columns, indexes).
     */
    protected function formatIdentifier(string $identifier, string $context): string
    {
        $identifier = trim($identifier);

        // Support identifier aliases via "AS" or space
        if (stripos($identifier, ' as ') !== false) {
            [$root, $alias] = preg_split('/\s+as\s+/i', $identifier);
            return sprintf(
                '%s AS %s',
                $this->formatIdentifier($root, $context),
                $this->formatIdentifier($alias, "$context alias")
            );
        }

        if (preg_match('/\s+/', $identifier)) {
            $parts = preg_split('/\s+/', $identifier);
            if (count($parts) === 2) {
                return sprintf(
                    '%s AS %s',
                    $this->formatIdentifier($parts[0], $context),
                    $this->formatIdentifier($parts[1], "$context alias")
                );
            }

            throw new \InvalidArgumentException("Invalid {$context}: {$identifier}");
        }

        // Allow dot-separated segments (e.g. schema.table, table.column)
        $pattern = '/^(?:[A-Za-z_][A-Za-z0-9_]*)(?:\.[A-Za-z_][A-Za-z0-9_]*)*$/';
        if (!preg_match($pattern, $identifier)) {
            throw new \InvalidArgumentException("Invalid {$context}: {$identifier}");
        }

        return $identifier;
    }

    /**
     * Quote literal values safely using PDO quoting (for DEFAULTs, COMMENTS).
     */
    protected function quoteLiteral($value): string
    {
        if (is_string($value)) {
            return $this->connection->getPdo()->quote($value);
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if ($value === null) {
            return 'NULL';
        }
        if (is_numeric($value)) {
            return (string)$value;
        }
        // Fallback: string-quote any other types
        return $this->connection->getPdo()->quote((string)$value);
    }

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
        $sql = "DROP TABLE " . $this->formatIdentifier($table, 'table');
        $this->connection->statement($sql);
    }

    /**
     * Drop table if exists
     */
    public function dropIfExists(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS " . $this->formatIdentifier($table, 'table');
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
        $name = $this->formatIdentifier($this->table->getName(), 'table');
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
        $sql = $this->formatIdentifier($column->getName(), 'column') . ' ' . $this->getColumnType($column);

        if ($column->isNullable() === false) {
            $sql .= ' NOT NULL';
        }

        if ($column->getDefault() !== null) {
            $default = $column->getDefault();
            $sql .= ' DEFAULT ' . $this->quoteLiteral($default);
        }

        if ($column->isAutoIncrement()) {
            $sql .= ' AUTO_INCREMENT';
        }

        if ($column->getComment()) {
            $comment = $column->getComment();
            $sql .= ' COMMENT ' . $this->quoteLiteral($comment);
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
            $columns = implode(', ', array_map(fn($c) => '`' . $this->formatIdentifier($c, 'column') . '`', $key));
            $lines[] = "  PRIMARY KEY ($columns)";
        }

        foreach ($this->table->getUniqueKeys() as $name => $columns) {
            $safeName = $this->formatIdentifier($name, 'index name');
            $columnStr = implode(', ', array_map(fn($c) => '`' . $this->formatIdentifier($c, 'column') . '`', $columns));
            $lines[] = "  UNIQUE KEY `{$safeName}` ($columnStr)";
        }

        foreach ($this->table->getForeignKeys() as $name => $fk) {
            $safeName = $this->formatIdentifier($name, 'constraint name');
            $local = implode(', ', array_map(fn($c) => '`' . $this->formatIdentifier($c, 'column') . '`', $fk['columns']));
            $foreign = implode(', ', array_map(fn($c) => '`' . $this->formatIdentifier($c, 'column') . '`', $fk['references']));
            $safeForeignTable = $this->formatIdentifier($fk['table'], 'table');
            $lines[] = "  CONSTRAINT `{$safeName}` FOREIGN KEY ($local) REFERENCES `{$safeForeignTable}` ($foreign)";
        }

        foreach ($this->table->getIndexes() as $name => $columns) {
            $safeName = $this->formatIdentifier($name, 'index name');
            $columnStr = implode(', ', array_map(fn($c) => '`' . $this->formatIdentifier($c, 'column') . '`', $columns));
            $lines[] = "  INDEX `{$safeName}` ($columnStr)";
        }

        return implode(",\n", $lines);
    }

    /**
     * Compile ALTER TABLE command
     */
    protected function compileCommand(array $command): string
    {
        $table = $this->formatIdentifier($this->table->getName(), 'table');
        $action = $command['action'];

        switch ($action) {
            case 'add':
                $column = $command['column'];
                $definition = $this->compileColumn($column);
                return "ALTER TABLE $table ADD COLUMN $definition";

            case 'drop':
                $column = $this->formatIdentifier($command['column'], 'column');
                return "ALTER TABLE $table DROP COLUMN $column";

            case 'rename':
                $old = $this->formatIdentifier($command['old'], 'column');
                $new = $this->formatIdentifier($command['new'], 'column');
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
