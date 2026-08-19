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

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Executes an arbitrary SQL statement and returns the number of affected rows.
     */
    public function exec(string $query): int|false
    {
        return $this->pdo->exec($query);
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

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
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
}
