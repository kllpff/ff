<?php

namespace FF\Framework\Database;

/**
 * Grammar - SQL Grammar Compiler
 * 
 * Compiles QueryBuilder statements into SQL strings.
 * Handles SELECT, INSERT, UPDATE, DELETE statements.
 */
class Grammar
{
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
        $sql .= implode(', ', $columns);

        $sql .= ' FROM ' . $query->table;

        if (!empty($query->joins)) {
            foreach ($query->joins as $join) {
                $sql .= $join;
            }
        }

        if (!empty($query->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $query->wheres);
        }

        if (!empty($query->orders)) {
            $sql .= ' ORDER BY ' . implode(', ', $query->orders);
        }

        if ($query->limit !== null) {
            $sql .= ' LIMIT ' . $query->limit;
        }

        if ($query->offset !== null) {
            $sql .= ' OFFSET ' . $query->offset;
        }

        return $sql;
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
        $columns = array_keys($values);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = 'INSERT INTO ' . $query->table;
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
            $updates[] = $column . ' = ?';
        }

        $sql = 'UPDATE ' . $query->table;
        $sql .= ' SET ' . implode(', ', $updates);

        if (!empty($query->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $query->wheres);
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
        $sql = 'DELETE FROM ' . $query->table;

        if (!empty($query->wheres)) {
            $sql .= ' WHERE ' . implode(' AND ', $query->wheres);
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
        $query->selects = ["COUNT($column) as aggregate"];
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
        $query->selects = ["MAX($column) as aggregate"];
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
        $query->selects = ["MIN($column) as aggregate"];
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
        $query->selects = ["AVG($column) as aggregate"];
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
        $query->selects = ["SUM($column) as aggregate"];
        return $this->compileSelect($query);
    }
}
