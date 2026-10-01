<?php

declare(strict_types=1);

namespace PDO4You;

use PDO;
use PDOStatement;
use PDOException;
use InvalidArgumentException;
use Throwable;
use Closure;
use PDO4You\Exception\QueryException;

/**
 * PDO4You: A lightweight, modern PHP database abstraction layer built on top of PDO.
 *
 * @author Giovanni Ramos <giovanniramos@msn.com>
 * @copyright 2010-2026, Giovanni Ramos
 * @since 2010-09-07
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/giovanniramos/PDO4You
 * @package PDO4You
 * @version 5.2.0
 */
class PDO4You
{
    /** @var PDO */
    private PDO $pdo;

    /** @var Platform\DatabasePlatform|null */
    private ?Platform\DatabasePlatform $platform;

    /** @var (Closure(string, array, float): void)|null */
    private static ?Closure $queryListener = null;

    /**
     * Initializes the PDO4You instance with a PDO connection and an optional database platform.
     *
     * @param PDO $pdo The PDO connection instance.
     * @param Platform\DatabasePlatform|null $platform The database platform implementation.
     */
    public function __construct(PDO $pdo, ?Platform\DatabasePlatform $platform = null)
    {
        $this->pdo = $pdo;
        $this->platform = $platform;
    }

    /**
     * Creates a PDO4You instance from a DSN string, automatically resolving the database platform.
     *
     * @param string $dsn The Data Source Name.
     * @param string|null $username The username for the DSN connection.
     * @param string|null $password The password for the DSN connection.
     * @param array<int|string, mixed>|null $options Driver-specific connection options.
     * @return self
     * @throws InvalidArgumentException If the DSN driver is unsupported or unrecognized.
     */
    public static function connect(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null): self
    {
        $platform = self::resolvePlatformFromDsn($dsn);

        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $mergedOptions = array_replace($defaultOptions, $options ?? []);

        $pdo = new PDO($dsn, $username, $password, $mergedOptions);

        return new self($pdo, $platform);
    }

    /**
     * Resolves the database platform instance based on the DSN prefix.
     *
     * @param string $dsn The Data Source Name.
     * @return Platform\DatabasePlatform
     * @throws InvalidArgumentException If the driver is not supported.
     */
    private static function resolvePlatformFromDsn(string $dsn): Platform\DatabasePlatform
    {
        $driver = strtolower(explode(':', $dsn)[0] ?? '');

        return match ($driver) {
            'mysql' => new Platform\MySqlPlatform(),
            'pgsql' => new Platform\PgSqlPlatform(),
            'sqlite' => new Platform\SqlitePlatform(),
            default => throw new InvalidArgumentException(sprintf('Unsupported or unrecognized DSN driver: "%s"', $driver)),
        };
    }

    /**
     * Registers a callback for query observability, logging, and profiling.
     *
     * @param (callable(string $sql, array $params, float $durationMs): void)|null $listener
     */
    public static function onQuery(?callable $listener): void
    {
        self::$queryListener = $listener !== null ? Closure::fromCallable($listener) : null;
    }

    /**
     * Notifies the registered query listener safely without breaking execution.
     *
     * @param string $sql
     * @param array<string|int, mixed> $params
     * @param float $duration
     */
    private static function notifyListener(string $sql, array $params, float $duration): void
    {
        if (self::$queryListener === null) {
            return;
        }

        try {
            (self::$queryListener)($sql, $params, $duration);
        } catch (Throwable) {
            // Silently suppress listener errors to avoid disrupting query lifecycle
        }
    }

    // ==========================================
    // Core Execution & Observability
    // ==========================================

    /**
     * Executes a prepared SQL statement and notifies the query observer.
     *
     * @param string $sql The SQL statement to execute.
     * @param array<string|int, mixed> $params Parameters for the prepared statement.
     * @return PDOStatement The executed PDO statement.
     *
     * @throws QueryException If the query execution fails.
     */
    public function executeStatement(string $sql, array $params = []): PDOStatement
    {
        $start = microtime(true);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $duration = (microtime(true) - $start) * 1000;
            self::notifyListener($sql, $params, $duration);

            return $stmt;
        } catch (PDOException $e) {
            $duration = (microtime(true) - $start) * 1000;
            self::notifyListener($sql, $params, $duration);

            throw new QueryException(
                message: 'Query failed: ' . $e->getMessage(),
                sql: $sql,
                params: $params,
                code: (int) $e->getCode(),
                previous: $e
            );
        }
    }

    // ==========================================
    // Query Methods & DTO Hydration
    // ==========================================

    /**
     * Executes a SELECT query and returns the result set.
     *
     * When a class map is provided, each row is hydrated into an instance
     * of the specified class. Otherwise, rows are returned as associative arrays.
     *
     * @template T of object
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Parameters for prepared statements.
     * @param class-string<T>|null $classMap Optional class used to hydrate the result rows.
     * @return array<int, T>|array<int, array<string, mixed>> The resulting rows.
     */
    public function select(string $sql, array $params = [], ?string $classMap = null): array
    {
        $stmt = $this->executeStatement($sql, $params);

        if ($classMap !== null) {
            return $stmt->fetchAll(PDO::FETCH_CLASS, $classMap);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executes a SELECT query and returns a single row or null.
     *
     * When a class map is provided, the row is hydrated into an instance
     * of the specified class. Otherwise, the row is returned as an associative array.
     *
     * @template T of object
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Parameters for prepared statements.
     * @param class-string<T>|null $classMap Optional class used to hydrate the result row.
     * @return T|array<string, mixed>|null The first result row or null if no rows are found.
     */
    public function selectOne(string $sql, array $params = [], ?string $classMap = null): mixed
    {
        $results = $this->select($sql, $params, $classMap);

        return $results[0] ?? null;
    }

    /**
     * Executes a SELECT query and returns a single scalar value from the first column.
     * Useful for aggregate queries like COUNT, SUM, MAX or specific single-value lookups.
     *
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Parameters for prepared statements.
     * @param int $columnIndex 0-indexed column position.
     * @return mixed The scalar value or null if no records are found.
     */
    public function selectVal(string $sql, array $params = [], int $columnIndex = 0): mixed
    {
        $stmt = $this->executeStatement($sql, $params);
        $value = $stmt->fetchColumn($columnIndex);

        return $value === false ? null : $value;
    }

    /**
     * Executes a SELECT query and returns the result set as objects.
     *
     * When a class map is provided, each row is hydrated into an instance
     * of the specified class. Otherwise, rows are returned as stdClass instances.
     *
     * @template T of object
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Parameters for prepared statements.
     * @param class-string<T>|null $classMap Optional class used to hydrate the result rows.
     * @return array<int, T|\stdClass> The resulting rows as an array of objects.
     */
    public function selectObj(string $sql, array $params = [], ?string $classMap = null): array
    {
        $stmt = $this->executeStatement($sql, $params);

        if ($classMap !== null) {
            return $stmt->fetchAll(PDO::FETCH_CLASS, $classMap);
        }

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Executes a SELECT query and returns the result set as a numeric array.
     *
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Parameters for prepared statements.
     * @return array<int, array<int, mixed>> The resulting rows as a numeric array.
     */
    public function selectNum(string $sql, array $params = []): array
    {
        $stmt = $this->executeStatement($sql, $params);

        return $stmt->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Executes a prepared SQL statement and returns the resulting PDOStatement.
     *
     * @param string $sql The SQL statement to execute.
     * @param array<string|int, mixed> $params Parameters for the prepared statement.
     * @return PDOStatement|false The resulting PDOStatement, or false if execution fails.
     */
    public function query(string $sql, array $params = []): PDOStatement|false
    {
        try {
            return $this->executeStatement($sql, $params);
        } catch (QueryException) {
            return false;
        }
    }

    // ==========================================
    // DML Helpers (Insert, Update, Delete, Exec)
    // ==========================================

    /**
     * Executes an SQL statement and returns the number of affected rows.
     * Supports both single-row and batch executions with observability and error handling.
     *
     * @param string $sql The SQL statement to execute.
     * @param array<string|int, mixed> $params Parameters for the prepared statement,
     *         or an array of parameter sets for batch execution.
     * @return int The total number of affected rows.
     *
     * @throws QueryException If the statement execution fails.
     */
    public function exec(string $sql, array $params = []): int
    {
        if ($params === []) {
            return $this->executeStatement($sql)->rowCount();
        }

        // Batch execution for multiple records: [['John', 'Doe'], ['Jane', 'Doe']]
        if (is_array(reset($params))) {
            $start = microtime(true);
            $totalAffected = 0;

            try {
                $stmt = $this->pdo->prepare($sql);

                foreach ($params as $row) {
                    $stmt->execute((array) $row);
                    $totalAffected += $stmt->rowCount();
                }

                $duration = (microtime(true) - $start) * 1000;
                self::notifyListener($sql, $params, $duration);

                return $totalAffected;
            } catch (PDOException $e) {
                $duration = (microtime(true) - $start) * 1000;
                self::notifyListener($sql, $params, $duration);

                throw new QueryException(
                    message: 'Batch execution failed: ' . $e->getMessage(),
                    sql: $sql,
                    params: $params,
                    code: (int) $e->getCode(),
                    previous: $e
                );
            }
        }

        // Single execution for a single record: ['John', 'Doe']
        return $this->executeStatement($sql, $params)->rowCount();
    }

    /**
     * Returns the last inserted ID for the current connection.
     *
     * Uses the platform-specific implementation when available.
     *
     * @param string|null $sequence The name of the sequence object, if applicable.
     * @return string The last inserted ID as a string.
     *
     * @throws QueryException If the ID query fails.
     *
     * @see Platform\DatabasePlatform::getLastInsertIdSql()
     */
    public function lastId(?string $sequence = null): string
    {
        if ($this->platform !== null) {
            $sql = $this->platform->getLastInsertIdSql($sequence);
            $stmt = $this->executeStatement($sql);

            $value = $stmt->fetchColumn();

            if ($value === false) {
                throw new QueryException(
                    message: 'The database did not return a last inserted ID.',
                    sql: $sql,
                    params: []
                );
            }

            return (string) $value;
        }

        try {
            return (string) $this->pdo->lastInsertId($sequence);
        } catch (PDOException $e) {
            throw new QueryException(
                message: 'Unable to retrieve the last inserted ID: ' . $e->getMessage(),
                sql: '',
                params: [],
                code: (int) $e->getCode(),
                previous: $e
            );
        }
    }

    /**
     * Alias of lastId().
     *
     * @param string|null $name The name of the sequence object, if applicable.
     * @return string The last inserted ID as a string.
     *
     * @throws QueryException If the ID cannot be retrieved.
     */
    public function lastInsertId(?string $name = null): string
    {
        return $this->lastId($name);
    }

    // ==========================================
    // Transaction Management
    // ==========================================

    /**
     * Executes a callback within a managed transaction.
     * Automatically commits on success and rolls back on exception.
     *
     * @template T
     * @param callable(self): T $callback
     * @return T
     *
     * @throws Throwable
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();

            return $result;
        } catch (Throwable $e) {
            if ($this->inTransaction()) {
                $this->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Begins a transaction.
     *
     * @return bool True on success, false on failure.
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commits a transaction.
     *
     * @return bool True on success, false on failure.
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rolls back a transaction.
     *
     * @return bool True on success, false on failure.
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Checks if a transaction is currently active.
     *
     * @return bool True if a transaction is currently active, false otherwise.
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    // ==========================================
    // Accessors
    // ==========================================

    /**
     * Returns the underlying PDO connection instance.
     *
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Returns the active database platform instance, if resolved.
     *
     * @return Platform\DatabasePlatform|null
     */
    public function getPlatform(): ?Platform\DatabasePlatform
    {
        return $this->platform;
    }
}
