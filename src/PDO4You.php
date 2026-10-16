<?php

declare(strict_types=1);

namespace PDO4You;

use Closure;
use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use Throwable;
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
 * @version 5.4.0
 */
class PDO4You
{
    /** @var (Closure(string, array<string|int, mixed>, float): void)|null */
    private static ?Closure $queryListener = null;

    /**
     * Initializes the PDO4You instance.
     *
     * @param PDO $pdo The PDO connection instance.
     * @param Platform\DatabasePlatform|null $platform The database platform implementation.
     */
    public function __construct(
        private readonly PDO $pdo,
        private readonly ?Platform\DatabasePlatform $platform = null
    ) {
    }

    /**
     * Creates a PDO4You instance from a DSN string.
     *
     * @param string $dsn The Data Source Name.
     * @param string|null $username The username for the DSN connection.
     * @param string|null $password The password for the DSN connection.
     * @param array<int|string, mixed> $options Driver-specific connection options.
     *
     * @return self
     *
     * @throws InvalidArgumentException If the DSN driver is unsupported.
     * @throws PDOException If the connection cannot be established.
     */
    public static function connect(
        string $dsn,
        ?string $username = null,
        ?string $password = null,
        array $options = []
    ): self {
        $platform = self::resolvePlatformFromDsn($dsn);

        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO(
            $dsn,
            $username,
            $password,
            array_replace($defaultOptions, $options)
        );

        return new self($pdo, $platform);
    }

    /**
     * Resolves the database platform from the DSN driver.
     *
     * @param string $dsn The Data Source Name.
     *
     * @return Platform\DatabasePlatform
     *
     * @throws InvalidArgumentException If the driver is not supported.
     */
    private static function resolvePlatformFromDsn(string $dsn): Platform\DatabasePlatform
    {
        $driver = strtolower(explode(':', $dsn, 2)[0] ?? '');

        return match ($driver) {
            'mysql' => new Platform\MySqlPlatform(),
            'pgsql' => new Platform\PgSqlPlatform(),
            'sqlite' => new Platform\SqlitePlatform(),
            default => throw new InvalidArgumentException(
                sprintf('Unsupported or unrecognized DSN driver: "%s"', $driver)
            ),
        };
    }

    /**
     * Registers a callback for query observability, logging, and profiling.
     *
     * @param callable(string, array<string|int, mixed>, float): void|null $listener
     */
    public static function onQuery(?callable $listener): void
    {
        self::$queryListener = $listener !== null
            ? Closure::fromCallable($listener)
            : null;
    }

    /**
     * Notifies the registered query listener.
     *
     * Listener failures are intentionally isolated from the database lifecycle.
     *
     * @param string $sql The SQL statement.
     * @param array<string|int, mixed> $params Query parameters.
     * @param float $durationMs Execution duration in milliseconds.
     */
    private static function notifyListener(
        string $sql,
        array $params,
        float $durationMs
    ): void {
        if (self::$queryListener === null) {
            return;
        }

        try {
            (self::$queryListener)($sql, $params, $durationMs);
        } catch (Throwable) {
            // Observability failures do not compromise core execution.
        }
    }

    /**
     * Prepares an SQL statement.
     *
     * @param string $sql The SQL statement.
     * @param array<string|int, mixed> $params Query parameters used for error context.
     *
     * @return PDOStatement
     *
     * @throws QueryException If statement preparation fails.
     */
    private function prepareStatement(
        string $sql,
        array $params = []
    ): PDOStatement {
        try {
            return $this->pdo->prepare($sql);
        } catch (PDOException $e) {
            throw new QueryException(
                message: 'Query preparation failed: ' . $e->getMessage(),
                sql: $sql,
                params: $params,
                code: (int) $e->getCode(),
                previous: $e
            );
        }
    }

    /**
     * Executes a prepared statement.
     *
     * Query execution time is measured independently for each execution.
     *
     * @param PDOStatement $stmt The prepared PDO statement.
     * @param string $sql The SQL statement.
     * @param array<string|int, mixed> $params Query parameters.
     *
     * @return PDOStatement
     *
     * @throws QueryException If statement execution fails.
     */
    private function executePreparedStatement(
        PDOStatement $stmt,
        string $sql,
        array $params
    ): PDOStatement {
        $start = microtime(true);

        try {
            $stmt->execute($params);

            $durationMs = (microtime(true) - $start) * 1000;

            self::notifyListener($sql, $params, $durationMs);

            return $stmt;
        } catch (PDOException $e) {
            $durationMs = (microtime(true) - $start) * 1000;

            self::notifyListener($sql, $params, $durationMs);

            throw new QueryException(
                message: 'Query execution failed: ' . $e->getMessage(),
                sql: $sql,
                params: $params,
                code: (int) $e->getCode(),
                previous: $e
            );
        }
    }

    /**
     * Executes a prepared SQL statement.
     *
     * @param string $sql The SQL statement.
     * @param array<string|int, mixed> $params Query parameters.
     *
     * @return PDOStatement
     *
     * @throws QueryException If preparation or execution fails.
     */
    public function executeStatement(
        string $sql,
        array $params = []
    ): PDOStatement {
        $stmt = $this->prepareStatement($sql, $params);

        return $this->executePreparedStatement($stmt, $sql, $params);
    }

    /**
     * Executes a SELECT query and returns the result set.
     *
     * When a class map is provided, each row is hydrated into an instance
     * of the specified class. Otherwise, rows are returned as associative arrays.
     *
     * @template T of object
     *
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Query parameters.
     * @param class-string<T>|null $classMap Optional result class.
     *
     * @return array<int, T>|array<int, array<string, mixed>>
     */
    public function select(
        string $sql,
        array $params = [],
        ?string $classMap = null
    ): array {
        $stmt = $this->executeStatement($sql, $params);

        return $classMap !== null
            ? $stmt->fetchAll(PDO::FETCH_CLASS, $classMap)
            : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executes a SELECT query and returns the first row.
     *
     * Unlike select(), this method fetches only one row and therefore
     * avoids loading the complete result set into memory.
     *
     * @template T of object
     *
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Query parameters.
     * @param class-string<T>|null $classMap Optional result class.
     *
     * @return T|array<string, mixed>|null
     */
    public function selectOne(
        string $sql,
        array $params = [],
        ?string $classMap = null
    ): mixed {
        $stmt = $this->executeStatement($sql, $params);

        if ($classMap !== null) {
            $stmt->setFetchMode(PDO::FETCH_CLASS, $classMap);
            $result = $stmt->fetch();

            return $result === false ? null : $result;
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
    }

    /**
     * Executes a SELECT query and returns a single scalar value.
     *
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Query parameters.
     * @param int $columnIndex Zero-based column index.
     *
     * @return mixed The scalar value or null if no row exists.
     */
    public function selectVal(
        string $sql,
        array $params = [],
        int $columnIndex = 0
    ): mixed {
        $stmt = $this->executeStatement($sql, $params);
        $value = $stmt->fetchColumn($columnIndex);

        return $value === false ? null : $value;
    }

    /**
     * Executes a SELECT query and returns objects.
     *
     * @template T of object
     *
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Query parameters.
     * @param class-string<T>|null $classMap Optional result class.
     *
     * @return array<int, T|\stdClass>
     */
    public function selectObj(
        string $sql,
        array $params = [],
        ?string $classMap = null
    ): array {
        $stmt = $this->executeStatement($sql, $params);

        return $classMap !== null
            ? $stmt->fetchAll(PDO::FETCH_CLASS, $classMap)
            : $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Executes a SELECT query and returns numeric rows.
     *
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Query parameters.
     *
     * @return array<int, array<int, mixed>>
     */
    public function selectNum(
        string $sql,
        array $params = []
    ): array {
        $stmt = $this->executeStatement($sql, $params);

        return $stmt->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Executes an SQL statement and returns the resulting PDOStatement.
     *
     * Database failures are propagated as QueryException.
     *
     * @param string $sql The SQL statement.
     * @param array<string|int, mixed> $params Query parameters.
     *
     * @return PDOStatement
     *
     * @throws QueryException If execution fails.
     */
    public function query(
        string $sql,
        array $params = []
    ): PDOStatement {
        return $this->executeStatement($sql, $params);
    }

    /**
     * Executes an SQL statement and returns affected rows.
     *
     * Supports both single execution and batch execution.
     *
     * @param string $sql The SQL statement.
     * @param array<string|int, mixed> $params Parameters for a single execution
     *        or parameter sets for batch execution.
     *
     * @return int Total number of affected rows.
     *
     * @throws QueryException If preparation or execution fails.
     */
    public function exec(
        string $sql,
        array $params = []
    ): int {
        if ($params === []) {
            return $this->executeStatement($sql)->rowCount();
        }

        if (!$this->isBatchParameters($params)) {
            return $this->executeStatement($sql, $params)->rowCount();
        }

        $stmt = $this->prepareStatement($sql, $params);
        $totalAffected = 0;

        foreach ($params as $row) {
            $totalAffected += $this->executePreparedStatement($stmt, $sql, (array) $row)->rowCount();
        }

        return $totalAffected;
    }

    /**
     * Determines whether the supplied parameters represent batch execution.
     *
     * @param array<string|int, mixed> $params
     *
     * @return bool
     */
    private function isBatchParameters(array $params): bool
    {
        if ($params === []) {
            return false;
        }

        $first = reset($params);

        return is_array($first) && array_is_list($params);
    }

    /**
     * Inserts a row into a table and returns the number of affected rows.
     *
     * @param string $table The table name.
     * @param array<string, mixed> $data Associative array of column-value pairs.
     *
     * @return int Number of affected rows.
     *
     * @throws InvalidArgumentException If table or column names are invalid.
     * @throws QueryException If execution fails.
     */
    public function insert(string $table, array $data): int
    {
        if ($data === []) {
            return 0;
        }

        $escapedTable = $this->quoteIdentifier($table);

        $escapedColumns = [];
        $placeholders = [];
        $bindParams = [];

        foreach ($data as $column => $value) {
            $escapedColumns[] = $this->quoteIdentifier($column);
            $cleanParam = preg_replace('/[^a-zA-Z0-9_]/', '_', $column);
            $paramName = "_ins_{$cleanParam}";

            $placeholders[] = ":{$paramName}";
            $bindParams[$paramName] = $value;
        }

        $columnsSql = implode(', ', $escapedColumns);
        $valuesSql = implode(', ', $placeholders);

        $sql = "INSERT INTO {$escapedTable} ({$columnsSql}) VALUES ({$valuesSql})";

        return $this->executeStatement($sql, $bindParams)->rowCount();
    }

    /**
     * Updates rows in a table and returns the number of affected rows.
     *
     * @param string $table The table name.
     * @param array<string, mixed> $data Associative array of columns to update.
     * @param string $where The WHERE clause condition.
     * @param array<string|int, mixed> $whereParams Parameters for the WHERE clause.
     *
     * @return int Number of affected rows.
     *
     * @throws InvalidArgumentException If table or column names are invalid.
     * @throws QueryException If execution fails.
     */
    public function update(
        string $table,
        array $data,
        string $where,
        array $whereParams = []
    ): int {
        if ($data === []) {
            return 0;
        }

        $escapedTable = $this->quoteIdentifier($table);

        $sets = [];
        $bindParams = [];

        foreach ($data as $column => $value) {
            $escapedColumn = $this->quoteIdentifier($column);
            $cleanParam = preg_replace('/[^a-zA-Z0-9_]/', '_', $column);
            $paramName = "_set_{$cleanParam}";

            $sets[] = "{$escapedColumn} = :{$paramName}";
            $bindParams[$paramName] = $value;
        }

        foreach ($whereParams as $key => $value) {
            $bindParams[$key] = $value;
        }

        $sql = "UPDATE {$escapedTable} SET " . implode(', ', $sets) . " WHERE {$where}";

        return $this->executeStatement($sql, $bindParams)->rowCount();
    }

    /**
     * Deletes rows from a table and returns the number of affected rows.
     *
     * @param string $table The table name.
     * @param string $where The WHERE clause condition.
     * @param array<string|int, mixed> $whereParams Parameters for the WHERE clause.
     *
     * @return int Number of affected rows.
     *
     * @throws InvalidArgumentException If table name is invalid.
     * @throws QueryException If execution fails.
     */
    public function delete(
        string $table,
        string $where,
        array $whereParams = []
    ): int {
        $escapedTable = $this->quoteIdentifier($table);
        $sql = "DELETE FROM {$escapedTable} WHERE {$where}";

        return $this->executeStatement($sql, $whereParams)->rowCount();
    }

    /**
     * Validates and quotes an SQL identifier (table or column name),
     * supporting qualified identifiers (e.g., 'schema.table').
     *
     * @param string $identifier The identifier to escape.
     *
     * @return string
     *
     * @throws InvalidArgumentException If identifier format is invalid.
     */
    private function quoteIdentifier(string $identifier): string
    {
        $parts = explode('.', $identifier);
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $quoteChar = ($driver === 'mysql') ? '`' : '"';

        $escapedParts = [];
        foreach ($parts as $part) {
            $this->assertValidIdentifier($part);
            $escapedParts[] = $quoteChar . str_replace($quoteChar, $quoteChar . $quoteChar, $part) . $quoteChar;
        }

        return implode('.', $escapedParts);
    }

    /**
     * Asserts that a single identifier token contains only safe characters.
     *
     * @param string $part
     *
     * @throws InvalidArgumentException If token contains illegal characters.
     */
    private function assertValidIdentifier(string $part): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $part)) {
            throw new InvalidArgumentException(
                sprintf('Invalid SQL identifier provided: "%s"', $part)
            );
        }
    }

    /**
     * Returns the last inserted ID for the current connection.
     *
     * @param string|null $sequence Sequence name, if applicable.
     *
     * @return string
     *
     * @throws QueryException If the ID cannot be retrieved.
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
     * @param string|null $name Sequence name, if applicable.
     *
     * @return string
     *
     * @throws QueryException If the ID cannot be retrieved.
     */
    public function lastInsertId(?string $name = null): string
    {
        return $this->lastId($name);
    }

    /**
     * Executes a callback inside a managed transaction.
     *
     * The transaction is committed when the callback succeeds and rolled back
     * when the callback throws.
     *
     * @template T
     *
     * @param callable(self): T $callback
     *
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
            try {
                if ($this->inTransaction()) {
                    $this->rollBack();
                }
            } catch (Throwable) {
                // Preserve the original exception when rollback itself fails.
            }

            throw $e;
        }
    }

    /**
     * Begins a transaction.
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commits the current transaction.
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rolls back the current transaction.
     *
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Checks whether a transaction is active.
     *
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * Returns the underlying PDO connection.
     *
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Returns the active database platform.
     *
     * @return Platform\DatabasePlatform|null
     */
    public function getPlatform(): ?Platform\DatabasePlatform
    {
        return $this->platform;
    }
}
