<?php

declare(strict_types=1);

namespace PDO4You;

use ArrayIterator;
use Closure;
use Generator;
use InvalidArgumentException;
use Iterator;
use PDO;
use PDOException;
use PDOStatement;
use SensitiveParameter;
use stdClass;
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
 * @version 6.0.0
 */
class PDO4You
{
    /** @var (Closure(string, array<string|int, mixed>, float): void)|null */
    private static ?Closure $globalQueryListener = null;

    /** @var list<Closure(string, array<string|int, mixed>, float): void> */
    private array $queryListeners = [];

    /** @var Platform\DatabasePlatform|null Active database platform strategy. */
    private readonly ?Platform\DatabasePlatform $platform;

    /** @var string|null ID captured from the most recent same-batch INSERT. */
    private ?string $lastInsertedId = null;

    /** @var int|null Affected-row count captured before consuming appended rowsets. */
    private ?int $lastStatementRowCount = null;

    /** @var int Current nesting level for managed transactions (savepoints). */
    private int $transactionLevel = 0;

    /**
     * Initializes the PDO4You instance.
     *
     * If no platform is explicitly provided, it will be automatically resolved
     * from the underlying PDO driver when supported.
     *
     * @param PDO $pdo The PDO connection instance.
     * @param Platform\DatabasePlatform|null $platform The database platform implementation.
     */
    public function __construct(
        private readonly PDO $pdo,
        ?Platform\DatabasePlatform $platform = null
    ) {
        $this->platform = $platform ?? $this->detectPlatformFromPdo($pdo);
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
        #[SensitiveParameter]
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
     * Resolves the database platform from the DSN string.
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

        return self::resolvePlatformFromDriver($driver);
    }

    /**
     * Resolves the database platform from a driver name.
     *
     * @param string $driver The normalized PDO driver name.
     *
     * @return Platform\DatabasePlatform
     *
     * @throws InvalidArgumentException If the driver is not supported.
     */
    private static function resolvePlatformFromDriver(string $driver): Platform\DatabasePlatform
    {
        return match (strtolower($driver)) {
            'mysql' => new Platform\MySqlPlatform(),
            'pgsql' => new Platform\PgSqlPlatform(),
            'sqlite' => new Platform\SqlitePlatform(),
            'sqlsrv' => new Platform\SqlSrvPlatform(),
            default => throw new InvalidArgumentException(
                sprintf('Unsupported or unrecognized DSN driver: "%s"', $driver)
            ),
        };
    }

    /**
     * Attempts to resolve the database platform from an existing PDO instance.
     *
     * @param PDO $pdo
     *
     * @return Platform\DatabasePlatform|null
     */
    private function detectPlatformFromPdo(PDO $pdo): ?Platform\DatabasePlatform
    {
        try {
            $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            return self::resolvePlatformFromDriver($driver);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Registers a global callback for query observability, logging, and profiling.
     *
     * @param (callable(string, array<string|int, mixed>, float): void)|null $listener
     */
    public static function onQuery(?callable $listener): void
    {
        self::$globalQueryListener = $listener !== null
            ? Closure::fromCallable($listener)
            : null;
    }

    /**
     * Registers an instance-scoped callback for query observability.
     * Safe for long-running processes (Octane, Swoole, RoadRunner) and multi-DB setups.
     *
     * @param callable(string, array<string|int, mixed>, float): void $listener
     *
     * @return self
     */
    public function addQueryListener(callable $listener): self
    {
        $this->queryListeners[] = Closure::fromCallable($listener);

        return $this;
    }

    /**
     * Clears all instance-scoped query listeners.
     *
     * @return self
     */
    public function clearQueryListeners(): self
    {
        $this->queryListeners = [];

        return $this;
    }

    /**
     * Notifies registered instance and global query listeners.
     *
     * Listener failures are intentionally isolated from the database lifecycle.
     *
     * @param string $sql The SQL statement.
     * @param array<string|int, mixed> $params Query parameters.
     * @param float $durationMs Execution duration in milliseconds.
     */
    private function notifyListeners(
        string $sql,
        array $params,
        float $durationMs
    ): void {
        if (self::$globalQueryListener !== null) {
            try {
                (self::$globalQueryListener)($sql, $params, $durationMs);
            } catch (Throwable) {
                // Observability failures do not compromise core execution.
            }
        }

        foreach ($this->queryListeners as $listener) {
            try {
                $listener($sql, $params, $durationMs);
            } catch (Throwable) {
                // Observability failures do not compromise core execution.
            }
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
                code: $e->getCode(),
                previous: $e
            );
        }
    }

    /**
     * Binds parameters to a prepared statement using explicit scalar PDO types.
     *
     * This ensures native prepared statements handle integers (e.g., LIMIT/OFFSET),
     * booleans, and nulls accurately across all database drivers.
     *
     * @param PDOStatement $stmt The prepared statement.
     * @param array<string|int, mixed> $params Parameters to bind.
     */
    private function bindValues(PDOStatement $stmt, array $params): void
    {
        if ($params === []) {
            return;
        }

        $isList = array_is_list($params);

        foreach ($params as $key => $value) {
            $parameter = $isList
                ? ((int) $key + 1)
                : (is_int($key) ? $key + 1 : (str_starts_with($key, ':') ? $key : ':' . $key));

            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                is_resource($value) => PDO::PARAM_LOB,
                default => PDO::PARAM_STR,
            };

            $stmt->bindValue($parameter, $value, $type);
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
            $this->bindValues($stmt, $params);
            $stmt->execute();

            $durationMs = (microtime(true) - $start) * 1000;

            $this->notifyListeners($sql, $params, $durationMs);

            return $stmt;
        } catch (PDOException $e) {
            $durationMs = (microtime(true) - $start) * 1000;

            $this->notifyListeners($sql, $params, $durationMs);

            throw new QueryException(
                message: 'Query execution failed: ' . $e->getMessage(),
                sql: $sql,
                params: $params,
                code: $e->getCode(),
                previous: $e
            );
        }
    }

    /**
     * Strips SQL comments, string literals, quoted identifiers, and balanced
     * parenthesized blocks, leaving only the top-level SQL statement skeleton.
     *
     * @param string $sql
     *
     * @return string
     */
    private function extractTopLevelSql(string $sql): string
    {
        $stripPattern = '/
            (?&parens)
            | \'(?:\'\'|[^\'])*+\'
            | "(?:""|[^"])*+"
            | \[(?:]]|[^\]])*+\]
            | --[^\r\n]*+
            | \/\*.*?\*\/
            (?(DEFINE)
                (?<parens>\((?:(?>[^()\'"\[\]\-\/]+)|\'(?:\'\'|[^\'])*+\'|"(?:""|[^"])*+"|\[(?:]]|[^\]])*+\]|--[^\r\n]*+|\/\*.*?\*\/|-(?!-)|\/(?!\*)|(?&parens))*+\))
            )
        /isx';

        return preg_replace($stripPattern, ' ', $sql) ?? $sql;
    }

    /**
     * Checks whether the SQL batch contains a top-level INSERT statement
     * (including batches preceded by comments, CTEs, DECLARE, or SET statements).
     *
     * @param string $sql
     *
     * @return bool
     */
    private function isInsertQuery(string $sql): bool
    {
        $topLevelSql = $this->extractTopLevelSql($sql);

        return preg_match(
            '/(?:^|[;\s])\b(?<!\bFOR\s)(?<!\bAFTER\s)(?<!\bOF\s)INSERT\b/is',
            $topLevelSql
        ) === 1;
    }

    /**
     * Consumes all rowsets of an INSERT batch, extracts the trailing appended
     * `SCOPE_IDENTITY()` rowset, and if the batch produced any caller-facing
     * result sets (such as `INSERT ... OUTPUT`, `INSERT ...; SELECT ...`, or
     * `INSERT ...; EXEC ...`), closes the native cursor and returns a buffered
     * PDOStatement preserving all caller rowsets.
     *
     * @param PDOStatement $stmt
     *
     * @return PDOStatement
     */
    private function consumeAndBufferBatchRowsets(PDOStatement $stmt): PDOStatement
    {
        /** @var list<array{columns: int, rowCount: int, rows: list<array<string, mixed>>}> $collectedRowsets */
        $collectedRowsets = [];
        $lastColumnRowsetIndex = null;

        do {
            $colCount = $stmt->columnCount();
            $rowCount = 0;

            if ($colCount === 0) {
                if ($this->lastStatementRowCount === null || $this->lastStatementRowCount <= 0) {
                    $rowCount = $stmt->rowCount();
                    if ($rowCount > 0) {
                        $this->lastStatementRowCount = $rowCount;
                    }
                } else {
                    $rowCount = $this->lastStatementRowCount;
                }
            }

            $rows = [];
            if ($colCount > 0) {
                while (is_array($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
                    $rows[] = $row;
                }

                if ($rows === []) {
                    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (is_array($all) && $all !== []) {
                        $rows = $all;
                    } else {
                        $scalar = $stmt->fetchColumn();
                        if ($scalar !== false && $scalar !== null) {
                            $rows[] = ['lastId' => $scalar];
                        }
                    }
                }

                $lastColumnRowsetIndex = count($collectedRowsets);
            }

            $collectedRowsets[] = [
                'columns'  => $colCount,
                'rowCount' => $rowCount,
                'rows'     => $rows,
            ];
        } while ($stmt->nextRowset());

        // Extract and remove the trailing appended SCOPE_IDENTITY() rowset.
        if ($lastColumnRowsetIndex !== null) {
            $identityRowset = $collectedRowsets[$lastColumnRowsetIndex];
            unset($collectedRowsets[$lastColumnRowsetIndex]);
            $collectedRowsets = array_values($collectedRowsets);

            if (isset($identityRowset['rows'][0]) && $identityRowset['rows'][0] !== []) {
                $val = reset($identityRowset['rows'][0]);
                if ($val !== false && $val !== null) {
                    $this->lastInsertedId = (string) $val;
                }
            }
        }

        $hasCallerColumnRowsets = false;
        foreach ($collectedRowsets as $rs) {
            if ($rs['columns'] > 0) {
                $hasCallerColumnRowsets = true;
                if ($this->lastInsertedId === null && isset($rs['rows'][0]) && $rs['rows'][0] !== []) {
                    $firstVal = reset($rs['rows'][0]);
                    if ($firstVal !== false && $firstVal !== null) {
                        $this->lastInsertedId = (string) $firstVal;
                    }
                }
                break;
            }
        }

        if (!$hasCallerColumnRowsets) {
            return $stmt;
        }

        $stmt->closeCursor();

        $effectiveRowCount = ($this->lastStatementRowCount !== null && $this->lastStatementRowCount > 0)
            ? $this->lastStatementRowCount
            : count($collectedRowsets[0]['rows']);
        $this->lastStatementRowCount = $effectiveRowCount;

        return $this->createBufferedOutputStatement($collectedRowsets, $effectiveRowCount);
    }

    /**
     * Creates a buffered PDOStatement that replays pre-fetched caller rowsets
     * (supporting both direct fetch and `nextRowset()` navigation) after the
     * underlying statement's appended identity rowset has been drained.
     *
     * @param list<array{columns: int, rowCount: int, rows: list<array<string, mixed>>}> $rowsets
     * @param int $fallbackRowCount
     *
     * @return PDOStatement
     */
    private function createBufferedOutputStatement(
        array $rowsets,
        int $fallbackRowCount
    ): PDOStatement {
        return new class ($rowsets, $fallbackRowCount) extends PDOStatement {
            private int $rowsetIndex = 0;
            private int $cursor = 0;
            private int $defaultFetchMode = PDO::FETCH_ASSOC;
            private ?string $fetchClass = null;
            /** @var array<int, mixed> */
            private array $fetchConstructorArgs = [];

            /**
             * @param list<array{columns: int, rowCount: int, rows: list<array<string, mixed>>}> $rowsets
             */
            public function __construct(
                private readonly array $rowsets,
                private readonly int $fallbackRowCount
            ) {
            }

            /**
             * Advances to the first rowset containing columns when a fetch operation
             * is invoked directly without a prior nextRowset() call.
             */
            private function ensureActiveResultRowset(): void
            {
                if (
                    isset($this->rowsets[$this->rowsetIndex])
                    && $this->rowsets[$this->rowsetIndex]['columns'] === 0
                    && $this->cursor === 0
                ) {
                    $count = count($this->rowsets);
                    for ($i = $this->rowsetIndex + 1; $i < $count; $i++) {
                        if ($this->rowsets[$i]['columns'] > 0) {
                            $this->rowsetIndex = $i;
                            $this->cursor = 0;
                            break;
                        }
                    }
                }
            }

            public function setFetchMode(int $mode, mixed ...$args): bool
            {
                $this->defaultFetchMode = $mode;
                if ($mode === PDO::FETCH_CLASS) {
                    $this->fetchClass = isset($args[0]) && is_string($args[0]) ? $args[0] : stdClass::class;
                    $this->fetchConstructorArgs = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
                }

                return true;
            }

            public function fetch(
                int $mode = PDO::FETCH_DEFAULT,
                int $cursorOrientation = PDO::FETCH_ORI_NEXT,
                int $cursorOffset = 0
            ): mixed {
                $this->ensureActiveResultRowset();

                $rows = $this->rowsets[$this->rowsetIndex]['rows'] ?? [];
                if (!isset($rows[$this->cursor])) {
                    return false;
                }

                $row = $rows[$this->cursor++];
                $effectiveMode = $mode === PDO::FETCH_DEFAULT ? $this->defaultFetchMode : $mode;

                return $this->formatRow($row, $effectiveMode, $this->fetchClass, $this->fetchConstructorArgs);
            }

            public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
            {
                $this->ensureActiveResultRowset();

                $effectiveMode = $mode === PDO::FETCH_DEFAULT ? $this->defaultFetchMode : $mode;
                $className = $this->fetchClass;
                $ctorArgs = $this->fetchConstructorArgs;
                $colIndex = 0;

                if ($effectiveMode === PDO::FETCH_CLASS && isset($args[0]) && is_string($args[0])) {
                    $className = $args[0];
                    $ctorArgs = isset($args[1]) && is_array($args[1]) ? $args[1] : [];
                } elseif ($effectiveMode === PDO::FETCH_COLUMN && isset($args[0]) && is_int($args[0])) {
                    $colIndex = $args[0];
                }

                $rows = $this->rowsets[$this->rowsetIndex]['rows'] ?? [];
                $results = [];
                while (isset($rows[$this->cursor])) {
                    $row = $rows[$this->cursor++];
                    $results[] = $effectiveMode === PDO::FETCH_COLUMN
                        ? (array_values($row)[$colIndex] ?? null)
                        : $this->formatRow($row, $effectiveMode, $className, $ctorArgs);
                }

                return $results;
            }

            public function fetchColumn(int $column = 0): mixed
            {
                $this->ensureActiveResultRowset();

                $rows = $this->rowsets[$this->rowsetIndex]['rows'] ?? [];
                if (!isset($rows[$this->cursor])) {
                    return false;
                }

                $values = array_values($rows[$this->cursor++]);

                return array_key_exists($column, $values) ? $values[$column] : false;
            }

            public function fetchObject(?string $class = 'stdClass', array $constructorArgs = []): object|false
            {
                $this->ensureActiveResultRowset();

                $rows = $this->rowsets[$this->rowsetIndex]['rows'] ?? [];
                if (!isset($rows[$this->cursor])) {
                    return false;
                }

                $row = $rows[$this->cursor++];

                return $this->hydrateObject($row, $class ?? stdClass::class, $constructorArgs);
            }

            public function nextRowset(): bool
            {
                if (!isset($this->rowsets[$this->rowsetIndex + 1])) {
                    return false;
                }

                $this->rowsetIndex++;
                $this->cursor = 0;

                return true;
            }

            public function rowCount(): int
            {
                $current = $this->rowsets[$this->rowsetIndex]['rowCount'] ?? 0;

                return $current > 0 ? $current : $this->fallbackRowCount;
            }

            public function columnCount(): int
            {
                return $this->rowsets[$this->rowsetIndex]['columns'] ?? 0;
            }

            public function closeCursor(): bool
            {
                $rows = $this->rowsets[$this->rowsetIndex]['rows'] ?? [];
                $this->cursor = count($rows);

                return true;
            }

            public function getIterator(): Iterator
            {
                $items = [];
                while (($row = $this->fetch()) !== false) {
                    $items[] = $row;
                }

                return new ArrayIterator($items);
            }

            /**
             * @param array<string, mixed> $row
             * @param array<int, mixed> $ctorArgs
             */
            private function formatRow(
                array $row,
                int $mode,
                ?string $className,
                array $ctorArgs
            ): mixed {
                return match ($mode) {
                    PDO::FETCH_NUM => array_values($row),
                    PDO::FETCH_BOTH => $row + array_values($row),
                    PDO::FETCH_OBJ => (object) $row,
                    PDO::FETCH_COLUMN => array_values($row)[0] ?? null,
                    PDO::FETCH_CLASS => $this->hydrateObject($row, $className ?? stdClass::class, $ctorArgs),
                    default => $row,
                };
            }

            /**
             * @param array<string, mixed> $row
             * @param array<int, mixed> $ctorArgs
             */
            private function hydrateObject(array $row, string $className, array $ctorArgs): object
            {
                $instance = $ctorArgs === []
                    ? new $className()
                    : new $className(...$ctorArgs);

                foreach ($row as $key => $value) {
                    $instance->{$key} = $value;
                }

                return $instance;
            }
        };
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
        $this->lastStatementRowCount = null;

        $captureBatchIdentity = $this->platform !== null
            && $this->platform->requiresSameBatchIdentityCapture()
            && $this->isInsertQuery($sql)
            && stripos($sql, '__pdo4you_last_insert_id') === false;

        if ($captureBatchIdentity) {
            $this->lastInsertedId = null;
            $identitySql = $this->platform->getLastInsertIdSql();
            $sqlToExecute = rtrim($sql, " \t\n\r\0\x0B;")
                . ";\n" . $identitySql . " -- __pdo4you_last_insert_id";
        } else {
            $sqlToExecute = $sql;
        }

        $stmt = $this->prepareStatement($sqlToExecute, $params);
        $stmt = $this->executePreparedStatement($stmt, $sql, $params);

        if ($captureBatchIdentity) {
            // Preserve rowCount() before advancing through the appended result set.
            $this->lastStatementRowCount = $stmt->rowCount();

            return $this->consumeAndBufferBatchRowsets($stmt);
        }

        return $stmt;
    }

    /**
     * Returns the affected-row count captured before appended rowsets were consumed.
     */
    private function statementRowCount(PDOStatement $stmt): int
    {
        return $this->lastStatementRowCount ?? $stmt->rowCount();
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
     * Executes a SELECT query and yields rows lazily one by one using a Generator.
     *
     * Ideal for processing large datasets with O(1) memory consumption.
     *
     * @template T of object
     *
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Query parameters.
     * @param class-string<T>|null $classMap Optional result class.
     *
     * @return Generator<int, T|array<string, mixed>>
     */
    public function cursor(
        string $sql,
        array $params = [],
        ?string $classMap = null
    ): Generator {
        $stmt = $this->executeStatement($sql, $params);

        try {
            if ($classMap !== null) {
                $stmt->setFetchMode(PDO::FETCH_CLASS, $classMap);
                while (($row = $stmt->fetch()) !== false) {
                    yield $row;
                }
                return;
            }

            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                yield $row;
            }
        } finally {
            $stmt->closeCursor();
        }
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

        try {
            if ($classMap !== null) {
                $stmt->setFetchMode(PDO::FETCH_CLASS, $classMap);
                $result = $stmt->fetch();

                return $result === false ? null : $result;
            }

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result === false ? null : $result;
        } finally {
            $stmt->closeCursor();
        }
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

        try {
            $value = $stmt->fetchColumn($columnIndex);

            return $value === false ? null : $value;
        } finally {
            $stmt->closeCursor();
        }
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
            return $this->statementRowCount($this->executeStatement($sql));
        }

        if (!$this->isBatchParameters($params)) {
            return $this->statementRowCount($this->executeStatement($sql, $params));
        }

        $requiresBatchIdentityCapture = $this->platform !== null
            && $this->platform->requiresSameBatchIdentityCapture()
            && $this->isInsertQuery($sql);

        if ($requiresBatchIdentityCapture) {
            $totalAffected = 0;
            foreach ($params as $row) {
                $totalAffected += $this->statementRowCount(
                    $this->executeStatement($sql, (array) $row)
                );
            }
            return $totalAffected;
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

        $i = 0;
        foreach ($data as $column => $value) {
            $escapedColumns[] = $this->quoteIdentifier($column);
            $cleanParam = preg_replace('/[^a-zA-Z0-9_]/', '_', $column);
            $paramName = "_pdo4you_ins_{$i}_{$cleanParam}";

            $placeholders[] = ":{$paramName}";
            $bindParams[$paramName] = $value;
            $i++;
        }

        $columnsSql = implode(', ', $escapedColumns);
        $valuesSql = implode(', ', $placeholders);

        $sql = "INSERT INTO {$escapedTable} ({$columnsSql}) VALUES ({$valuesSql})";

        return $this->statementRowCount($this->executeStatement($sql, $bindParams));
    }

    /**
     * Updates rows in a table and returns the number of affected rows.
     *
     * @param string $table The table name.
     * @param array<string, mixed> $data Associative array of columns to update.
     * @param string $where The WHERE clause condition (must not be empty).
     * @param array<string|int, mixed> $whereParams Parameters for the WHERE clause.
     *
     * @return int Number of affected rows.
     *
     * @throws InvalidArgumentException If table/column names are invalid, WHERE is empty, or parameter collision occurs.
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

        if (trim($where) === '') {
            throw new InvalidArgumentException('The WHERE clause cannot be empty for update operations.');
        }

        $escapedTable = $this->quoteIdentifier($table);
        $isPositional = $whereParams !== [] && array_is_list($whereParams);

        $sets = [];
        $bindParams = [];

        if ($isPositional) {
            foreach (array_keys($data) as $column) {
                $escapedColumn = $this->quoteIdentifier($column);
                $sets[] = "{$escapedColumn} = ?";
            }

            $bindParams = array_merge(array_values($data), array_values($whereParams));
        } else {
            $i = 0;
            foreach ($data as $column => $value) {
                $escapedColumn = $this->quoteIdentifier($column);
                $cleanParam = preg_replace('/[^a-zA-Z0-9_]/', '_', $column);
                $paramName = "_pdo4you_upd_{$i}_{$cleanParam}";

                $sets[] = "{$escapedColumn} = :{$paramName}";
                $bindParams[$paramName] = $value;
                $i++;
            }

            foreach ($whereParams as $key => $value) {
                $normalizedKey = ltrim((string) $key, ':');

                if (array_key_exists($normalizedKey, $bindParams)) {
                    throw new InvalidArgumentException(
                        sprintf('Parameter collision: WHERE parameter ":%s" conflicts with generated SET bindings.', $normalizedKey)
                    );
                }

                $bindParams[$normalizedKey] = $value;
            }
        }

        $sql = "UPDATE {$escapedTable} SET " . implode(', ', $sets) . " WHERE {$where}";

        return $this->statementRowCount($this->executeStatement($sql, $bindParams));
    }

    /**
     * Deletes rows from a table and returns the number of affected rows.
     *
     * @param string $table The table name.
     * @param string $where The WHERE clause condition (must not be empty).
     * @param array<string|int, mixed> $whereParams Parameters for the WHERE clause.
     *
     * @return int Number of affected rows.
     *
     * @throws InvalidArgumentException If table name is invalid or WHERE is empty.
     * @throws QueryException If execution fails.
     */
    public function delete(
        string $table,
        string $where,
        array $whereParams = []
    ): int {
        if (trim($where) === '') {
            throw new InvalidArgumentException('The WHERE clause cannot be empty for delete operations.');
        }

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
        $escapedParts = [];

        foreach ($parts as $part) {
            $this->assertValidIdentifier($part);

            if ($this->platform !== null) {
                $escapedParts[] = $this->platform->quoteIdentifierPart($part);
            } else {
                $escapedParts[] = '"' . str_replace('"', '""', $part) . '"';
            }
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
     * @throws InvalidArgumentException If the sequence identifier is invalid.
     * @throws QueryException If the ID cannot be retrieved.
     *
     * @see Platform\DatabasePlatform::getLastInsertIdSql()
     */
    public function lastId(?string $sequence = null): string
    {
        if (
            $this->platform !== null
            && $this->platform->requiresSameBatchIdentityCapture()
            && $this->lastInsertedId !== null
        ) {
            return $this->lastInsertedId;
        }

        if ($this->platform !== null) {
            $sql = $this->platform->getLastInsertIdSql($sequence);
            $stmt = $this->executeStatement($sql);

            try {
                $value = $stmt->fetchColumn();
            } finally {
                $stmt->closeCursor();
            }

            if ($value === false || $value === null || $value === '') {
                throw new QueryException(
                    message: 'The database did not return a valid last inserted ID.',
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
                code: $e->getCode(),
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
     * @throws InvalidArgumentException If the sequence identifier is invalid.
     * @throws QueryException If the ID cannot be retrieved.
     */
    public function lastInsertId(?string $name = null): string
    {
        return $this->lastId($name);
    }

    /**
     * Executes a callback inside a managed transaction.
     *
     * Supports nested calls via platform-specific SQL SAVEPOINTs. The transaction
     * (or savepoint) is committed when the callback succeeds and rolled back when it throws.
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
        $isRootTransaction = $this->transactionLevel === 0 && !$this->inTransaction();
        $savepointName = 'pdo4you_sp_' . ($this->transactionLevel + 1);

        if ($isRootTransaction) {
            $this->beginTransaction();
        } else {
            $savepointSql = $this->platform !== null
                ? $this->platform->getSavepointSql($savepointName)
                : "SAVEPOINT {$savepointName}";
            $this->pdo->exec($savepointSql);
        }

        $this->transactionLevel++;

        try {
            $result = $callback($this);

            if ($isRootTransaction) {
                $this->commit();
            } else {
                $releaseSql = $this->platform !== null
                    ? $this->platform->getReleaseSavepointSql($savepointName)
                    : "RELEASE SAVEPOINT {$savepointName}";

                if ($releaseSql !== null) {
                    $this->pdo->exec($releaseSql);
                }
            }

            return $result;
        } catch (Throwable $e) {
            try {
                if ($isRootTransaction) {
                    if ($this->inTransaction()) {
                        $this->rollBack();
                    }
                } else {
                    $rollbackSql = $this->platform !== null
                        ? $this->platform->getRollbackSavepointSql($savepointName)
                        : "ROLLBACK TO SAVEPOINT {$savepointName}";
                    $this->pdo->exec($rollbackSql);
                }
            } catch (Throwable) {
                // Preserve the original exception when rollback itself fails.
            }

            throw $e;
        } finally {
            $this->transactionLevel = max(0, $this->transactionLevel - 1);
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
