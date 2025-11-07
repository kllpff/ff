<?php

namespace FF\Database;

use InvalidArgumentException;
use PDO;
use PDOStatement;
use FF\Exceptions\DatabaseException;

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
        $this->driver = $this->sanitizeDriver($config['driver'] ?? 'mysql');
        $this->host = $this->sanitizeHost($config['host'] ?? 'localhost');
        $this->database = $this->sanitizeDatabaseName($config['database'] ?? '');
        $this->username = $this->sanitizeUsername($config['username'] ?? 'root');
        $this->password = $config['password'] ?? '';
        $this->port = $this->sanitizePort($config['port'] ?? 3306);

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
     * @throws DatabaseException If connection fails
     */
    protected function connect(): void
    {
        try {
            if ($this->driver === 'mysql') {
                $dsn = sprintf(
                    'mysql:host=%s;port=%d;dbname=%s',
                    $this->host,
                    $this->port,
                    $this->database
                );
                // charset will be added via config, if provided
            } elseif ($this->driver === 'pgsql') {
                $dsn = sprintf(
                    'pgsql:host=%s;port=%d;dbname=%s',
                    $this->host,
                    $this->port,
                    $this->database
                );
            } elseif ($this->driver === 'sqlite') {
                $dsn = 'sqlite:' . $this->database;
            } else {
                throw new DatabaseException("Unsupported database driver: {$this->driver}");
            }

            $this->pdo = new PDO(
                $dsn,
                $this->username,
                $this->password,
                $this->options
            );
        } catch (\PDOException $e) {
            throw new DatabaseException("Database connection failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Ensure the driver is one of the supported options.
     */
    protected function sanitizeDriver(string $driver): string
    {
        $driver = strtolower(trim($driver));
        $allowed = ['mysql', 'pgsql', 'sqlite'];

        if (!in_array($driver, $allowed, true)) {
            throw new InvalidArgumentException('Unsupported database driver specified.');
        }

        return $driver;
    }

    /**
     * Validate and sanitize the host component of the DSN.
     */
    protected function sanitizeHost(string $host): string
    {
        $host = trim($host);
        if ($host === '') {
            throw new InvalidArgumentException('Database host must not be empty.');
        }

        if ($this->driver === 'sqlite') {
            return $host;
        }

        if (strpbrk($host, "\r\n\0\"'`") !== false || strpos($host, ';') !== false) {
            throw new InvalidArgumentException('Database host contains invalid characters.');
        }

        return $host;
    }

    /**
     * Validate the database name to avoid DSN injection.
     */
    protected function sanitizeDatabaseName(string $database): string
    {
        $database = trim($database);

        if ($this->driver === 'sqlite') {
            if ($database === '') {
                throw new InvalidArgumentException('SQLite database path must not be empty.');
            }

            if (strpbrk($database, "\r\n\0") !== false) {
                throw new InvalidArgumentException('SQLite database path contains invalid characters.');
            }

            return $database;
        }

        if ($database === '') {
            throw new InvalidArgumentException('Database name must not be empty.');
        }

        if (strpbrk($database, "\r\n\0\"'`;") !== false) {
            throw new InvalidArgumentException('Database name contains invalid characters.');
        }

        return $database;
    }

    /**
     * Validate the username to prevent malformed DSNs.
     */
    protected function sanitizeUsername(string $username): string
    {
        $username = trim($username);
        if ($username === '') {
            throw new InvalidArgumentException('Database username must not be empty.');
        }

        if (strpbrk($username, "\r\n\0\"'`") !== false) {
            throw new InvalidArgumentException('Database username contains invalid characters.');
        }

        return $username;
    }

    /**
     * Validate and normalize the port value.
     */
    protected function sanitizePort($port): int
    {
        if (!is_numeric($port)) {
            throw new InvalidArgumentException('Database port must be numeric.');
        }

        $port = (int)$port;

        if ($port <= 0 || $port > 65535) {
            throw new InvalidArgumentException('Database port must be between 1 and 65535.');
        }

        return $port;
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
        try {
            $statement = $this->prepare($sql);
            $this->bindValues($statement, $bindings);
            $statement->execute();
            $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds

            $this->logQuery($sql, $bindings, $duration);

            return $statement->fetchAll();
        } catch (\Throwable $e) {
            $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds
            try {
                \logger()->error('DB query failed', [
                    'sql' => $sql,
                    'bindings' => $bindings,
                    'error' => $e->getMessage(),
                    'driver' => $this->driver,
                    'host' => $this->host,
                    'database' => $this->database,
                    'duration_ms' => $duration,
                ]);
            } catch (\Throwable $logError) {
                // Logger not available
            }
            throw new DatabaseException('Database query failed: ' . $e->getMessage(), 0, $e);
        }
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
        try {
            $statement = $this->prepare($sql);
            $this->bindValues($statement, $bindings);
            $statement->execute();
            $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds

            $this->logQuery($sql, $bindings, $duration);

            return $statement->rowCount();
        } catch (\Throwable $e) {
            $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds
            try {
                \logger()->error('DB update failed', [
                    'sql' => $sql,
                    'bindings' => $bindings,
                    'error' => $e->getMessage(),
                    'driver' => $this->driver,
                    'host' => $this->host,
                    'database' => $this->database,
                    'duration_ms' => $duration,
                ]);
            } catch (\Throwable $logError) {
                // Logger not available
            }
            throw new DatabaseException('Database update failed: ' . $e->getMessage(), 0, $e);
        }
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
        try {
            $statement = $this->prepare($sql);
            $this->bindValues($statement, $bindings);
            $result = $statement->execute();
            $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds

            $this->logQuery($sql, $bindings, $duration);

            return $result;
        } catch (\Throwable $e) {
            $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds
            try {
                \logger()->error('DB statement failed', [
                    'sql' => $sql,
                    'bindings' => $bindings,
                    'error' => $e->getMessage(),
                    'driver' => $this->driver,
                    'host' => $this->host,
                    'database' => $this->database,
                    'duration_ms' => $duration,
                ]);
            } catch (\Throwable $logError) {
                // Logger not available
            }
            throw new DatabaseException('Database statement failed: ' . $e->getMessage(), 0, $e);
        }
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
        try {
            $this->pdo->beginTransaction();
        } catch (\Throwable $e) {
            try {
                \logger()->error('DB beginTransaction failed', [
                    'error' => $e->getMessage(),
                    'driver' => $this->driver,
                    'host' => $this->host,
                    'database' => $this->database,
                ]);
            } catch (\Throwable $logError) {
                // Logger not available
            }
            throw new DatabaseException('Begin transaction failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Commit a transaction
     * 
     * @return void
     */
    public function commit(): void
    {
        try {
            $this->pdo->commit();
        } catch (\Throwable $e) {
            try {
                \logger()->error('DB commit failed', [
                    'error' => $e->getMessage(),
                    'driver' => $this->driver,
                    'host' => $this->host,
                    'database' => $this->database,
                ]);
            } catch (\Throwable $logError) {
                // Logger not available
            }
            throw new DatabaseException('Commit failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Rollback a transaction
     * 
     * @return void
     */
    public function rollback(): void
    {
        try {
            $this->pdo->rollBack();
        } catch (\Throwable $e) {
            try {
                \logger()->error('DB rollback failed', [
                    'error' => $e->getMessage(),
                    'driver' => $this->driver,
                    'host' => $this->host,
                    'database' => $this->database,
                ]);
            } catch (\Throwable $logError) {
                // Logger not available
            }
            throw new DatabaseException('Rollback failed: ' . $e->getMessage(), 0, $e);
        }
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
            try {
                \logger()->error('DB transaction failed; rolled back', [
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                    'driver' => $this->driver,
                    'host' => $this->host,
                    'database' => $this->database,
                ]);
            } catch (\Throwable $logError) {
                // Logger not available
            }
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
