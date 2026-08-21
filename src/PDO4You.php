<?php

declare(strict_types=1);

namespace PDO4You;

use PDO;
use PDOException;

/**
 * PDO4You: A modern lightweight database wrapper using PDO.
 *
 * Refactored to support Dependency Injection and Instance-based usage.
 */
class PDO4You
{
    private PDO $pdo;
    private Platform\DatabasePlatform $platform;

    public function __construct(PDO $pdo, Platform\DatabasePlatform $platform)
    {
        $this->pdo = $pdo;
        $this->platform = $platform;
    }

    /**
     * Returns the last inserted ID for the current connection, optionally using a sequence name.
     * This method delegates to the platform-specific implementation.
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
     */
    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executes a SELECT query and returns the results as an array of objects.
     */
    public function selectObj(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Executes a SELECT query and returns the results as a numeric array.
     */
    public function selectNum(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object.
     */
    public function query(string $query): \PDOStatement|false
    {
        return $this->pdo->query($query);
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     */
    public function lastInsertId(?string $name = null): string|false
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * Begins a transaction.
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commits a transaction.
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rolls back a transaction.
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}
