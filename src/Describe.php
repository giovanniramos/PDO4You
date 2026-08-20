<?php

declare(strict_types=1);

namespace PDO4You;

use PDO;

/**
 * Service to describe database schema information.
 */
class Describe
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get tables in the database.
     * Note: This implementation is basic and driver-dependent.
     */
    public function getTables(): array
    {
        // For MySQL as an example.
        $stmt = $this->pdo->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get columns information for a specific table.
     */
    public function getColumns(string $tableName): array
    {
        $stmt = $this->pdo->prepare("DESCRIBE " . $this->pdo->quote($tableName));
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
