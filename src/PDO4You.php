<?php

declare(strict_types=1);

namespace PDO4You;

use PDO;
use PDOException;

/**
 * PDO4You: A lightweight, modern PHP database abstraction layer built on top of PDO.
 *
 * @author Giovanni Ramos <giovanniramos@msn.com>
 * @copyright 2010-2026, Giovanni Ramos
 * @since 2010-09-07
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/giovanniramos/PDO4You
 * @package PDO4You
 * @version 5.0.0
 */
class PDO4You
{
    /** @var \PDO */
    private \PDO $pdo;

    /**
     * @var Platform\DatabasePlatform
     */
    private Platform\DatabasePlatform $platform;


    /**
     * Initializes the PDO4You instance with a PDO connection and a database platform.
     *
     * @param \PDO $pdo The PDO connection instance.
     * @param Platform\DatabasePlatform $platform The database platform implementation.
     */
    public function __construct(\PDO $pdo, Platform\DatabasePlatform $platform)
    {
        $this->pdo = $pdo;
        $this->platform = $platform;
    }

    /**
     * Returns the last inserted ID for the current connection, optionally using a sequence name.
     * This method delegates to the platform-specific implementation.
     *
     * @param string|null $sequence The name of the sequence object from which the ID should be returned.
     * @return string|false The last inserted ID as a string, or false on failure.
     *
     * @see Platform\DatabasePlatform::getLastInsertIdSql()
     */
    public function lastId(?string $sequence = null): string|false
    {
        $sql = $this->platform->getLastInsertIdSql($sequence);
        $result = $this->pdo->query($sql);

        if ($result === false) {
            return false;
        }

        return (string) $result->fetchColumn() ?: false;
    }

    /**
     * Executes an arbitrary SQL statement and returns the number of affected rows.
     * Supports optional parameters for single or batch prepared statements.
     *
     * @param string $query The SQL statement to execute.
     * @param array<string|int, mixed> $params Optional parameters for prepared statements.
     * @return int|false The number of affected rows, or false on failure.
     */
    public function exec(string $query, array $params = []): int|false
    {
        if (empty($params)) {
            return $this->pdo->exec($query);
        }

        $stmt = $this->pdo->prepare($query);

        // Batch execution for multiple records: [['John', 'Doe'], ['Jane', 'Doe']]
        if (is_array(reset($params))) {
            $totalAffected = 0;

            foreach ($params as $row) {
                if (!$stmt->execute((array) $row)) {
                    return false;
                }
                $totalAffected += $stmt->rowCount();
            }

            return $totalAffected;
        }

        // Single execution for a single record: ['John', 'Doe']
        if (!$stmt->execute($params)) {
            return false;
        }

        return $stmt->rowCount();
    }

    /**
     * Executes a SELECT query and returns the results as an associative array.
     *
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Optional parameters for prepared statements.
     * @return array<int, array<string, mixed>> The resulting rows as an associative array.
     */
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Executes a SELECT query and returns the results as an array of objects.
     *
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Optional parameters for prepared statements.
     * @return array<int, \stdClass> The resulting rows as an array of objects.
     */
    public function selectObj(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Executes a SELECT query and returns the results as a numeric array.
     *
     * @param string $sql The SQL query to execute.
     * @param array<string|int, mixed> $params Optional parameters for prepared statements.
     * @return array<int, array<int, mixed>> The resulting rows as a numeric array.
     */
    public function selectNum(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_NUM);
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object.
     *
     * @param string $sql The SQL statement to execute.
     * @param array<string|int, mixed> $params Optional parameters for prepared statements.
     * @return \PDOStatement|false The resulting PDOStatement object, or false on failure.
     */
    public function query(string $query, array $params = []): \PDOStatement|false
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @param string|null $name The name of the sequence object from which the ID should be returned.
     * @return string|false The last inserted ID as a string, or false on failure.
     */
    public function lastInsertId(?string $name = null): string|false
    {
        return $this->pdo->lastInsertId($name);
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
}
