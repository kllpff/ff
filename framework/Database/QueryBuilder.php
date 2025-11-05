<?php

namespace FF\Framework\Database;

/**
 * QueryBuilder - Database Query Builder
 * 
 * Provides a fluent interface for building and executing SQL queries.
 * Supports SELECT, INSERT, UPDATE, DELETE, and aggregation functions.
 */
class QueryBuilder
{
    /**
     * The database connection
     * 
     * @var Connection
     */
    protected Connection $connection;

    /**
     * The SQL grammar compiler
     * 
     * @var Grammar
     */
    protected Grammar $grammar;

    /**
     * The table name
     * 
     * @var string
     */
    public string $table = '';

    /**
     * Selected columns for SELECT
     * 
     * @var array
     */
    public array $selects = [];

    /**
     * WHERE conditions
     * 
     * @var array
     */
    public array $wheres = [];

    /**
     * JOIN clauses
     * 
     * @var array
     */
    public array $joins = [];

    /**
     * ORDER BY clauses
     * 
     * @var array
     */
    public array $orders = [];

    /**
     * LIMIT value
     * 
     * @var int|null
     */
    public ?int $limit = null;

    /**
     * OFFSET value
     * 
     * @var int|null
     */
    public ?int $offset = null;

    /**
     * Distinct flag
     * 
     * @var bool
     */
    public bool $distinct = false;

    /**
     * Parameter bindings for prepared statements
     * 
     * @var array
     */
    protected array $bindings = [];

    /**
     * Create a new QueryBuilder instance
     * 
     * @param Connection $connection The database connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->grammar = new Grammar();
    }

    /**
     * Specify the table
     * 
     * @param string $table The table name
     * @return self
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Specify columns to select
     * 
     * @param mixed $columns The columns (string, array, or variadic)
     * @return self
     */
    public function select($columns = ['*']): self
    {
        if (is_string($columns)) {
            $this->selects = [$columns];
        } else if (is_array($columns)) {
            $this->selects = $columns;
        } else {
            $this->selects = func_get_args();
        }
        return $this;
    }

    /**
     * Add a column to select
     * 
     * @param string $column The column
     * @return self
     */
    public function addSelect(string $column): self
    {
        $this->selects[] = $column;
        return $this;
    }

    /**
     * Select distinct rows
     * 
     * @return self
     */
    public function distinct(): self
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * Add a WHERE condition
     * 
     * @param string $column The column
     * @param string|null $operator The operator (optional)
     * @param mixed $value The value
     * @return self
     */
    public function where(string $column, $operator = null, $value = null): self
    {
        // Handle overloaded parameters
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = "$column $operator ?";
        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Add an OR WHERE condition
     * 
     * @param string $column The column
     * @param string|null $operator The operator
     * @param mixed $value The value
     * @return self
     */
    public function orWhere(string $column, $operator = null, $value = null): self
    {
        // Handle overloaded parameters
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = "OR $column $operator ?";
        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Add a WHERE IN condition
     * 
     * @param string $column The column
     * @param array $values The values
     * @return self
     */
    public function whereIn(string $column, array $values): self
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->wheres[] = "$column IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    /**
     * Add a WHERE NOT IN condition
     * 
     * @param string $column The column
     * @param array $values The values
     * @return self
     */
    public function whereNotIn(string $column, array $values): self
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->wheres[] = "$column NOT IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    /**
     * Add a WHERE NULL condition
     * 
     * @param string $column The column
     * @return self
     */
    public function whereNull(string $column): self
    {
        $this->wheres[] = "$column IS NULL";
        return $this;
    }

    /**
     * Add a WHERE NOT NULL condition
     * 
     * @param string $column The column
     * @return self
     */
    public function whereNotNull(string $column): self
    {
        $this->wheres[] = "$column IS NOT NULL";
        return $this;
    }

    /**
     * Add a WHERE BETWEEN condition
     * 
     * @param string $column The column
     * @param array $values Array with two values [min, max]
     * @return self
     */
    public function whereBetween(string $column, array $values): self
    {
        $this->wheres[] = "$column BETWEEN ? AND ?";
        $this->bindings = array_merge($this->bindings, $values);

        return $this;
    }

    /**
     * Add a raw WHERE condition
     * 
     * @param string $sql The raw SQL
     * @param array $bindings The bindings
     * @return self
     */
    public function whereRaw(string $sql, array $bindings = []): self
    {
        $this->wheres[] = $sql;
        $this->bindings = array_merge($this->bindings, $bindings);

        return $this;
    }

    /**
     * Add a JOIN clause
     * 
     * @param string $table The table to join
     * @param string $first The first column
     * @param string $operator The operator
     * @param string $second The second column
     * @return self
     */
    public function join(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = " INNER JOIN $table ON $first $operator $second";
        return $this;
    }

    /**
     * Add a LEFT JOIN clause
     * 
     * @param string $table The table to join
     * @param string $first The first column
     * @param string $operator The operator
     * @param string $second The second column
     * @return self
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = " LEFT JOIN $table ON $first $operator $second";
        return $this;
    }

    /**
     * Add a RIGHT JOIN clause
     * 
     * @param string $table The table to join
     * @param string $first The first column
     * @param string $operator The operator
     * @param string $second The second column
     * @return self
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = " RIGHT JOIN $table ON $first $operator $second";
        return $this;
    }

    /**
     * Add an INNER JOIN clause
     * 
     * @param string $table The table to join
     * @param string $first The first column
     * @param string $operator The operator
     * @param string $second The second column
     * @return self
     */
    public function innerJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second);
    }

    /**
     * Add an ORDER BY clause
     * 
     * @param string $column The column to order by
     * @param string $direction The direction (asc or desc)
     * @return self
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = "$column " . strtoupper($direction);
        return $this;
    }

    /**
     * Order by latest (descending)
     * 
     * @param string $column The column (default: created_at)
     * @return self
     */
    public function latest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Order by oldest (ascending)
     * 
     * @param string $column The column (default: created_at)
     * @return self
     */
    public function oldest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'asc');
    }

    /**
     * Set the LIMIT
     * 
     * @param int $value The limit value
     * @return self
     */
    public function limit(int $value): self
    {
        $this->limit = $value;
        return $this;
    }

    /**
     * Set the OFFSET
     * 
     * @param int $value The offset value
     * @return self
     */
    public function offset(int $value): self
    {
        $this->offset = $value;
        return $this;
    }

    /**
     * Alias for offset()
     * 
     * @param int $value The skip value
     * @return self
     */
    public function skip(int $value): self
    {
        return $this->offset($value);
    }

    /**
     * Alias for limit()
     * 
     * @param int $value The take value
     * @return self
     */
    public function take(int $value): self
    {
        return $this->limit($value);
    }

    /**
     * Execute the query and get all results
     * 
     * @return array The results
     */
    public function get(): array
    {
        $sql = $this->grammar->compileSelect($this);
        return $this->connection->query($sql, $this->bindings);
    }

    /**
     * Get the first result
     * 
     * @return mixed The first row or null
     */
    public function first()
    {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }

    /**
     * Get the first result as a Model instance
     * 
     * @param string|null $model The model class to instantiate
     * @return object|null The model instance or null
     */
    public function firstModel(?string $model = null): ?object
    {
        $result = $this->first();
        if (!$result) {
            return null;
        }
        
        if ($model) {
            return new $model((array)$result);
        }
        
        return (object)$result;
    }

    /**
     * Find a record by ID
     * 
     * @param mixed $id The ID value
     * @return mixed The record or null
     */
    public function find($id)
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Get a single column value from the first result
     * 
     * @param string $column The column to retrieve
     * @return mixed The column value or null
     */
    public function value(string $column)
    {
        $result = $this->select($column)->first();
        return $result[$column] ?? null;
    }

    /**
     * Get all values of a specific column
     * 
     * @param string $column The column to retrieve
     * @param string|null $key Optional key column for associative array
     * @return array The column values
     */
    public function pluck(string $column, string $key = null): array
    {
        $results = $this->select($key ? [$key, $column] : [$column])->get();
        $plucked = [];

        if ($key) {
            foreach ($results as $result) {
                $plucked[$result[$key]] = $result[$column];
            }
        } else {
            foreach ($results as $result) {
                $plucked[] = $result[$column];
            }
        }

        return $plucked;
    }

    /**
     * Count the number of records
     * 
     * @param string $column The column to count (default: *)
     * @return int The count
     */
    public function count(string $column = '*'): int
    {
        $sql = $this->grammar->compileCount($this, $column);
        $results = $this->connection->query($sql, $this->bindings);
        return (int)($results[0]['aggregate'] ?? 0);
    }

    /**
     * Get the maximum value of a column
     * 
     * @param string $column The column
     * @return mixed The maximum value
     */
    public function max(string $column)
    {
        $sql = $this->grammar->compileMax($this, $column);
        $results = $this->connection->query($sql, $this->bindings);
        return $results[0]['aggregate'] ?? null;
    }

    /**
     * Get the minimum value of a column
     * 
     * @param string $column The column
     * @return mixed The minimum value
     */
    public function min(string $column)
    {
        $sql = $this->grammar->compileMin($this, $column);
        $results = $this->connection->query($sql, $this->bindings);
        return $results[0]['aggregate'] ?? null;
    }

    /**
     * Get the average value of a column
     * 
     * @param string $column The column
     * @return float The average value
     */
    public function avg(string $column): float
    {
        $sql = $this->grammar->compileAvg($this, $column);
        $results = $this->connection->query($sql, $this->bindings);
        return (float)($results[0]['aggregate'] ?? 0);
    }

    /**
     * Get the sum of a column
     * 
     * @param string $column The column
     * @return float The sum
     */
    public function sum(string $column): float
    {
        $sql = $this->grammar->compileSum($this, $column);
        $results = $this->connection->query($sql, $this->bindings);
        return (float)($results[0]['aggregate'] ?? 0);
    }

    /**
     * Check if any records exist
     * 
     * @return bool
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Insert a record
     * 
     * @param array $values The values to insert
     * @return bool True on success
     */
    public function insert(array $values): bool
    {
        $sql = $this->grammar->compileInsert($this, $values);
        return $this->connection->insert($sql, array_values($values));
    }

    /**
     * Insert a record and return the inserted ID
     * 
     * @param array $values The values to insert
     * @return int The last inserted ID
     */
    public function insertGetId(array $values): int
    {
        $this->insert($values);
        return $this->connection->lastInsertId();
    }

    /**
     * Update records
     * 
     * @param array $values The values to update
     * @return int Number of affected rows
     */
    public function update(array $values): int
    {
        $sql = $this->grammar->compileUpdate($this, $values);
        return $this->connection->update($sql, array_merge(array_values($values), $this->bindings));
    }

    /**
     * Increment a column value
     * 
     * @param string $column The column
     * @param int $amount The amount to increment
     * @return int Number of affected rows
     */
    public function increment(string $column, int $amount = 1): int
    {
        return $this->update([$column => $this->connection->getPdo()->quote("$column + $amount")]);
    }

    /**
     * Decrement a column value
     * 
     * @param string $column The column
     * @param int $amount The amount to decrement
     * @return int Number of affected rows
     */
    public function decrement(string $column, int $amount = 1): int
    {
        return $this->update([$column => $this->connection->getPdo()->quote("$column - $amount")]);
    }

    /**
     * Delete records
     * 
     * @return int Number of affected rows
     */
    public function delete(): int
    {
        $sql = $this->grammar->compileDelete($this);
        return $this->connection->delete($sql, $this->bindings);
    }

    /**
     * Truncate the table (delete all records)
     * 
     * @return void
     */
    public function truncate(): void
    {
        $sql = "TRUNCATE TABLE " . $this->table;
        $this->connection->statement($sql);
    }

    /**
     * Get the SQL string for debugging
     * 
     * @return string
     */
    public function toSql(): string
    {
        return $this->grammar->compileSelect($this);
    }
}
