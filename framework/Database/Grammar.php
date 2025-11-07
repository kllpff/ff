<?php

namespace FF\Database;

/**
 * Grammar - SQL Grammar Compiler
 * 
 * Compiles QueryBuilder statements into SQL strings.
 * Handles SELECT, INSERT, UPDATE, DELETE statements.
 */
class Grammar
{
    /**
     * Validate and normalize SQL identifiers (tables, columns, qualified names).
     * Mirrors SchemaBuilder::formatIdentifier for consistency.
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
     * Validate SELECT column identifier, allowing wildcard "*" and "table.*".
     */
    protected function formatSelectIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);

        if ($identifier === '*') {
            return '*';
        }

        // Allow qualified wildcard: table.*
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*\.\*$/', $identifier)) {
            return $identifier;
        }

        // Fallback to standard identifier validation (supports aliases)
        return $this->formatIdentifier($identifier, 'column');
    }

    /**
     * Sanitize a single SELECT expression (identifier, alias, or aggregate).
     */
    protected function sanitizeSelectExpression(string $expr): string
    {
        $expr = trim($expr);

        // Handle aggregates: COUNT, SUM, AVG, MIN, MAX
        if (preg_match('/^(COUNT|SUM|AVG|MIN|MAX)\s*\(\s*(.*?)\s*\)\s*(?:AS\s+([A-Za-z_][A-Za-z0-9_]*))?$/i', $expr, $m)) {
            $func = strtoupper($m[1]);
            $inner = $m[2];
            $alias = $m[3] ?? null;

            if ($func === 'COUNT') {
                $innerSafe = $this->formatSelectIdentifier($inner);
            } else {
                // Strict: aggregates on identifiers only (no *)
                $innerSafe = $this->formatIdentifier($inner, 'column');
            }

            $out = sprintf('%s(%s)', $func, $innerSafe);
            if ($alias !== null) {
                $out .= ' AS ' . $this->formatIdentifier($alias, 'column alias');
            }
            return $out;
        }

        // Handle aliases without aggregate: "col AS alias" or "col alias"
        if (stripos($expr, ' as ') !== false) {
            [$root, $alias] = preg_split('/\s+as\s+/i', $expr);
            return sprintf('%s AS %s', $this->formatSelectIdentifier($root), $this->formatIdentifier($alias, 'column alias'));
        }

        if (preg_match('/\s+/', $expr)) {
            $parts = preg_split('/\s+/', $expr);
            if (count($parts) === 2) {
                return sprintf('%s AS %s', $this->formatSelectIdentifier($parts[0]), $this->formatIdentifier($parts[1], 'column alias'));
            }
            throw new \InvalidArgumentException("Invalid select expression: {$expr}");
        }

        // Identifier or wildcard
        return $this->formatSelectIdentifier($expr);
    }

    /**
     * Compile a SELECT statement
     * 
     * @param QueryBuilder $query The query builder
     * @return string The compiled SQL
     */
    public function compileSelect(QueryBuilder $query): string
    {
        $sql = 'SELECT ';

        if ($query->distinct) {
            $sql .= 'DISTINCT ';
        }

        $columns = $query->selects ?: ['*'];
        $safeColumns = array_map([$this, 'sanitizeSelectExpression'], $columns);
        $sql .= implode(', ', $safeColumns);

        $sql .= ' FROM ' . $this->formatIdentifier($query->table, 'table');

        if (!empty($query->joins)) {
            foreach ($query->joins as $join) {
                $sql .= $join;
            }
        }

        if (!empty($query->wheres)) {
            $sql .= $this->compileWhereClause($query->wheres);
        }

        if (!empty($query->orders)) {
            $sql .= ' ORDER BY ' . implode(', ', $query->orders);
        }

        if ($query->limit !== null) {
            $sql .= ' LIMIT ' . (int)$query->limit;
        }

        if ($query->offset !== null) {
            $sql .= ' OFFSET ' . (int)$query->offset;
        }

        return $sql;
    }

    /**
     * Compile WHERE clause with proper AND/OR connectors
     *
     * @param array $wheres Items of ['connector' => 'AND'|'OR', 'sql' => string]
     */
    protected function compileWhereClause(array $wheres): string
    {
        $out = '';
        foreach ($wheres as $i => $w) {
            $connector = $i === 0 ? '' : (' ' . (($w['connector'] ?? 'AND')) . ' ');
            $out .= $connector . ($w['sql'] ?? (string)$w);
        }
        return ' WHERE ' . $out;
    }

    /**
     * Compile an INSERT statement
     * 
     * @param QueryBuilder $query The query builder
     * @param array $values The values to insert
     * @return string The compiled SQL
     */
    public function compileInsert(QueryBuilder $query, array $values): string
    {
        $columns = array_map(function ($c) {
            return $this->formatIdentifier((string)$c, 'column');
        }, array_keys($values));
        $placeholders = array_fill(0, count($columns), '?');

        $sql = 'INSERT INTO ' . $this->formatIdentifier($query->table, 'table');
        $sql .= ' (' . implode(', ', $columns) . ')';
        $sql .= ' VALUES (' . implode(', ', $placeholders) . ')';

        return $sql;
    }

    /**
     * Compile an UPDATE statement
     * 
     * @param QueryBuilder $query The query builder
     * @param array $values The values to update
     * @return string The compiled SQL
     */
    public function compileUpdate(QueryBuilder $query, array $values): string
    {
        $updates = [];
        foreach (array_keys($values) as $column) {
            $updates[] = $this->formatIdentifier((string)$column, 'column') . ' = ?';
        }

        $sql = 'UPDATE ' . $this->formatIdentifier($query->table, 'table');
        $sql .= ' SET ' . implode(', ', $updates);

        if (!empty($query->wheres)) {
            $sql .= $this->compileWhereClause($query->wheres);
        }

        return $sql;
    }

    /**
     * Compile a DELETE statement
     * 
     * @param QueryBuilder $query The query builder
     * @return string The compiled SQL
     */
    public function compileDelete(QueryBuilder $query): string
    {
        $sql = 'DELETE FROM ' . $this->formatIdentifier($query->table, 'table');

        if (!empty($query->wheres)) {
            $sql .= $this->compileWhereClause($query->wheres);
        }

        return $sql;
    }

    /**
     * Compile a COUNT aggregation
     * 
     * @param QueryBuilder $query The query builder
     * @param string $column The column to count
     * @return string The compiled SQL
     */
    public function compileCount(QueryBuilder $query, string $column = '*'): string
    {
        // COUNT allows * and table.*
        $safe = $this->formatSelectIdentifier($column);
        $query->selects = ["COUNT($safe) as aggregate"];
        return $this->compileSelect($query);
    }

    /**
     * Compile a MAX aggregation
     * 
     * @param QueryBuilder $query The query builder
     * @param string $column The column
     * @return string The compiled SQL
     */
    public function compileMax(QueryBuilder $query, string $column): string
    {
        $safe = $this->formatIdentifier($column, 'column');
        $query->selects = ["MAX($safe) as aggregate"];
        return $this->compileSelect($query);
    }

    /**
     * Compile a MIN aggregation
     * 
     * @param QueryBuilder $query The query builder
     * @param string $column The column
     * @return string The compiled SQL
     */
    public function compileMin(QueryBuilder $query, string $column): string
    {
        $safe = $this->formatIdentifier($column, 'column');
        $query->selects = ["MIN($safe) as aggregate"];
        return $this->compileSelect($query);
    }

    /**
     * Compile an AVG aggregation
     * 
     * @param QueryBuilder $query The query builder
     * @param string $column The column
     * @return string The compiled SQL
     */
    public function compileAvg(QueryBuilder $query, string $column): string
    {
        $safe = $this->formatIdentifier($column, 'column');
        $query->selects = ["AVG($safe) as aggregate"];
        return $this->compileSelect($query);
    }

    /**
     * Compile a SUM aggregation
     * 
     * @param QueryBuilder $query The query builder
     * @param string $column The column
     * @return string The compiled SQL
     */
    public function compileSum(QueryBuilder $query, string $column): string
    {
        $safe = $this->formatIdentifier($column, 'column');
        $query->selects = ["SUM($safe) as aggregate"];
        return $this->compileSelect($query);
    }
}
