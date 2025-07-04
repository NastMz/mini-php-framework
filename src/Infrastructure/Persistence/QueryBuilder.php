<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;
use PDOStatement;

/**
 * QueryBuilder
 *
 * A simple query builder for common SQL operations.
 * Supports SELECT, INSERT, UPDATE, DELETE with parameter binding.
 */
class QueryBuilder
{
    private string $sql    = '';
    private array  $params = [];

    /**
     * QueryBuilder constructor.
     *
     * @param PDO $pdo PDO instance for database connection
     */
    public function __construct(private PDO $pdo) {}

    /**
     * Start a SELECT query.
     *
     * @param string $table The table to select from
     * @param array $columns The columns to select, defaults to all (*)
     * @return self
     */
    public function select(string $table, array $columns = ['*']): self
    {
        $cols      = implode(', ', $columns);
        $this->sql = "SELECT {$cols} FROM {$table}";
        return $this;
    }

    /**
     * Add a JOIN clause to the query.
     *
     * @param string $table The table to join
     * @param string $on The ON condition for the join
     * @return self
     */
    public function join(string $table, string $on): self
    {
        $this->sql .= " JOIN {$table} ON {$on}";
        return $this;
    }

    /**
     * Add an ORDER BY clause to the query.
     *
     * @param string $column The column to order by
     * @param string $direction The direction of the order (ASC or DESC), defaults to ASC
     * @return self
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->sql .= " ORDER BY {$column} {$direction}";
        return $this;
    }
    
    /**
     * Add a LIMIT clause to the query.
     *
     * @param int $limit The maximum number of rows to return
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->sql .= " LIMIT :limit";
        $this->params['limit'] = $limit;
        return $this;
    }

    /**
     * Add a WHERE clause to the query.
     *
     * @param string $expr The WHERE expression
     * @param array $params Parameters for the WHERE expression
     * @return self
     */
    public function where(string $expr, array $params = []): self
    {
        $this->sql    .= " WHERE {$expr}";
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Add an AND condition to the WHERE clause.
     *
     * @param string $expr The AND expression
     * @param array $params Parameters for the AND expression
     * @return self
     */
    public function andWhere(string $expr, array $params = []): self
    {
        $this->sql    .= " AND {$expr}";
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Add an OR condition to the WHERE clause.
     *
     * @param string $expr The OR expression
     * @param array $params Parameters for the OR expression
     * @return self
     */
    public function orWhere(string $expr, array $params = []): self
    {
        $this->sql    .= " OR {$expr}";
        $this->params = array_merge($this->params, $params);
        return $this;
    }
    
    /**
     * Start an INSERT query.
     *
     * @param string $table The table to insert into
     * @param array $data Associative array of column => value pairs
     * @return self
     */
    public function insert(string $table, array $data): self
    {
        $cols         = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($k) => ":{$k}", array_keys($data)));
        $this->sql    = "INSERT INTO {$table} ({$cols}) VALUES ({$placeholders})";
        $this->params = $data;
        return $this;
    }

    /**
     * Start an UPDATE query.
     *
     * @param string $table The table to update
     * @param array $data Associative array of column => value pairs
     * @return self
     */
    public function update(string $table, array $data): self
    {
        $sets        = implode(', ', array_map(fn($c) => "{$c}= :{$c}", array_keys($data)));
        $this->sql   = "UPDATE {$table} SET {$sets}";
        $this->params = $data;
        return $this;
    }

    /**
     * Start a DELETE query.
     *
     * @param string $table The table to delete from
     * @return self
     */
    public function delete(string $table): self
    {
        $this->sql = "DELETE FROM {$table}";
        return $this;
    }

    /**
     * Execute the built query and return the PDOStatement.
     *
     * @return PDOStatement The executed statement
     */
    public function execute(): PDOStatement
    {
        $stmt = $this->pdo->prepare($this->sql);
        $stmt->execute($this->params);
        return $stmt;
    }

    /**
     * Get the built SQL query string.
     *
     * @return string The SQL query
     */
    public function getSQL(): string
    {
        return $this->sql;
    }

    /**
     * Get the parameters bound to the query.
     *
     * @return array The parameters
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
