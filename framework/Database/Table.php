<?php

namespace FF\Framework\Database;

/**
 * Table - Schema Table Definition
 */
class Table
{
    protected string $name;
    protected bool $creating;
    protected array $columns = [];
    protected array $commands = [];
    protected array $primaryKeys = [];
    protected array $uniqueKeys = [];
    protected array $foreignKeys = [];
    protected array $indexes = [];

    public function __construct(string $name, bool $creating = false)
    {
        $this->name = $name;
        $this->creating = $creating;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function increments(string $column): Column
    {
        return $this->bigIncrements($column);
    }

    public function bigIncrements(string $column): Column
    {
        $col = $this->bigInteger($column)->autoIncrement();
        $this->primaryKeys[] = [$column];
        return $col;
    }

    public function integer(string $column): Column
    {
        return $this->addColumn('integer', $column);
    }

    public function bigInteger(string $column): Column
    {
        return $this->addColumn('bigInteger', $column);
    }

    public function string(string $column, int $length = 255): Column
    {
        $col = $this->addColumn('string', $column);
        $col->setLength($length);
        return $col;
    }

    public function text(string $column): Column
    {
        return $this->addColumn('text', $column);
    }

    public function boolean(string $column): Column
    {
        return $this->addColumn('boolean', $column);
    }

    public function float(string $column): Column
    {
        return $this->addColumn('float', $column);
    }

    public function decimal(string $column, int $precision = 8, int $scale = 2): Column
    {
        $col = $this->addColumn('decimal', $column);
        $col->setPrecision($precision);
        $col->setScale($scale);
        return $col;
    }

    public function dateTime(string $column): Column
    {
        return $this->addColumn('dateTime', $column)->nullable();
    }

    public function timestamp(string $column): Column
    {
        return $this->addColumn('timestamp', $column);
    }

    public function json(string $column): Column
    {
        return $this->addColumn('json', $column)->nullable();
    }

    public function primary(array $columns): void
    {
        $this->primaryKeys[] = $columns;
    }

    public function unique(string $name, array $columns): void
    {
        $this->uniqueKeys[$name] = $columns;
    }

    public function foreign(string $name, array $columns): void
    {
        $this->foreignKeys[$name] = ['columns' => $columns];
    }

    public function index(string $name, array $columns): void
    {
        $this->indexes[$name] = $columns;
    }

    public function dropColumn(string $column): void
    {
        $this->commands[] = ['action' => 'drop', 'column' => $column];
    }

    protected function addColumn(string $type, string $column): Column
    {
        $col = new Column($column, $type);
        $this->columns[] = $col;
        return $col;
    }

    public function getColumns(): array { return $this->columns; }
    public function getCommands(): array { return $this->commands; }
    public function getPrimaryKeys(): array { return $this->primaryKeys; }
    public function getUniqueKeys(): array { return $this->uniqueKeys; }
    public function getForeignKeys(): array { return $this->foreignKeys; }
    public function getIndexes(): array { return $this->indexes; }
}
