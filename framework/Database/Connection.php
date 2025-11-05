<?php

namespace FF\Framework\Database;

use PDO;
use PDOStatement;

/**
 * Connection - Database Connection Handler
 * 
 * Manages PDO database connections and provides methods for executing queries,
 * prepared statements, and transactions.
 */
class Connection
{
    /**
     * The PDO instance
     * 
     * @var PDO|null
     */
    protected ?PDO $pdo = null;

    /**
     * The database driver
     * 
     * @var string
     */
    protected string $driver;

    /**
     * The host
     * 
     * @var string
     */
    protected string $host;

    /**
     * The database name
     * 
     * @var string
     */
    protected string $database;

    /**
     * The username
     * 
     * @var string
     */
    protected string $username;

    /**
     * The password
     * 
     * @var string
     */
    protected string $password;

    /**
     * The port
     * 
     * @var int
     */
    protected int $port;

    /**
     * PDO options for connection
     * 
     * @var array
     */
    protected array $options = [];

    /**
     * Create a new Connection instance
     * 
     * @param array $config Database configuration
     */
    public function __construct(array $config)
    {
        $this->driver = $config['driver'] ?? 'mysql';
        $this->host = $config['host'] ?? 'localhost';
        $this->database = $config['database'] ?? '';
        $this->username = $config['username'] ?? 'root';
        $this->password = $config['password'] ?? '';
        $this->port = $config['port'] ?? 3306;

        // Set default PDO options
        $this->options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        // Merge with custom options
        if (isset($config['options'])) {
            $this->options = array_merge($this->options, $config['options']);
        }

        $this->connect();
    }

    /**
     * Establish the database connection
     * 
     * @return void
     * @throws \Exception If connection fails
     */
    protected function connect(): void
    {
        try {
            if ($this->driver === 'mysql') {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database}";
                // charset will be added via config, if provided
            } else if ($this->driver === 'pgsql') {
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->database}";
            } else if ($this->driver === 'sqlite') {
                $dsn = "sqlite:{$this->database}";
            } else {
                throw new \Exception("Unsupported database driver: {$this->driver}");
            }

            $this->pdo = new PDO(
                $dsn,
                $this->username,
                $this->password,
                $this->options
            );
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Execute a SELECT query
     * 
     * @param string $sql The SQL query
     * @param array $bindings Parameter bindings
     * @return array The query results
     */
    public function query(string $sql, array $bindings = []): array
    {
        $start = microtime(true);
        $statement = $this->prepare($sql);
        $this->bindValues($statement, $bindings);
        $statement->execute();
        $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds
        
        $this->logQuery($sql, $bindings, $duration);

        return $statement->fetchAll();
    }

    /**
     * Execute an INSERT query
     * 
     * @param string $sql The SQL query
     * @param array $bindings Parameter bindings
     * @return bool True on success
     */
    public function insert(string $sql, array $bindings = []): bool
    {
        return $this->statement($sql, $bindings);
    }

    /**
     * Execute an UPDATE query
     * 
     * @param string $sql The SQL query
     * @param array $bindings Parameter bindings
     * @return int Number of affected rows
     */
    public function update(string $sql, array $bindings = []): int
    {
        $start = microtime(true);
        $statement = $this->prepare($sql);
        $this->bindValues($statement, $bindings);
        $statement->execute();
        $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds
        
        $this->logQuery($sql, $bindings, $duration);

        return $statement->rowCount();
    }

    /**
     * Execute a DELETE query
     * 
     * @param string $sql The SQL query
     * @param array $bindings Parameter bindings
     * @return int Number of affected rows
     */
    public function delete(string $sql, array $bindings = []): int
    {
        return $this->update($sql, $bindings);
    }

    /**
     * Execute any statement
     * 
     * @param string $sql The SQL query
     * @param array $bindings Parameter bindings
     * @return bool True on success
     */
    public function statement(string $sql, array $bindings = []): bool
    {
        $start = microtime(true);
        $statement = $this->prepare($sql);
        $this->bindValues($statement, $bindings);
        $result = $statement->execute();
        $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds
        
        $this->logQuery($sql, $bindings, $duration);

        return $result;
    }

    /**
     * Prepare a statement
     * 
     * @param string $sql The SQL query
     * @return PDOStatement
     */
    protected function prepare(string $sql): PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * Bind values to a prepared statement
     * 
     * @param PDOStatement $statement The prepared statement
     * @param array $bindings The bindings to bind
     * @return void
     */
    protected function bindValues(PDOStatement $statement, array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $parameter = is_int($key) ? $key + 1 : ':' . $key;
            $statement->bindValue(
                $parameter,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * Begin a transaction
     * 
     * @return void
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit a transaction
     * 
     * @return void
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Rollback a transaction
     * 
     * @return void
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Execute a callback within a transaction
     * 
     * @param callable $callback The callback to execute
     * @return mixed The callback result
     */
    public function transaction(callable $callback)
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Get the last inserted row ID
     * 
     * @return int
     */
    public function lastInsertId(): int
    {
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Get the number of affected rows from the last statement
     * 
     * @return int
     */
    public function affectedRows(): int
    {
        return 0; // Will be tracked via statements
    }

    /**
     * Get the PDO instance
     * 
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Disconnect from the database
     * 
     * @return void
     */
    public function disconnect(): void
    {
        $this->pdo = null;
    }

    protected function logQuery(string $sql, array $bindings, float $duration): void
    {
        try {
            $debugBar = \app('debugbar');
            if ($debugBar && method_exists($debugBar, 'logQuery')) {
                $debugBar->logQuery($sql, $bindings, $duration);
            }
        } catch (\Exception $e) {
            // DebugBar not available
        }
    }
}
