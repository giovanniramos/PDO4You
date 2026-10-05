<?php

declare(strict_types=1);

/**
 * PDO4You Advanced Features Example:
 * Demonstrating DTO Hydration, Batch Operations, Query Observability, and Scalar Lookups.
 *
 * @sample 4
 */

use PDO4You\PDO4You;

// Define a Data Transfer Object (DTO) for strong-typed hydration
class UserDto
{
    public int $id;
    public string $name;
    public string $surname;
    public string $status;

    public function getFullName(): string
    {
        return "{$this->name} {$this->surname}";
    }
}

try {
    // 1. Setup & Query Observability
    echo "<p class='step'>1. Setup: Connecting and configuring Query Observability</p>";
    $db = PDO4You::connect('sqlite::memory:');
    echo "<p class='success'>✓ Connected successfully via DSN (sqlite::memory:).</p>";

    $queriesLog = [];
    PDO4You::onQuery(function (string $sql, array $params, float $durationMs) use (&$queriesLog) {
        $queriesLog[] = sprintf("[%.4fms] %s", $durationMs, $sql);
    });

    // Create schema
    $db->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            surname TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'active'
        )
    ");
    echo "<p class='success'>✓ Table 'users' created.</p>";

    // 2. Batch Execution
    echo "<p class='step'>2. Operations: Batch Execution (Multiple Inserts)</p>";
    $batchParams = [
        ['Alice', 'Smith', 'active'],
        ['Bob', 'Jones', 'pending'],
        ['Charlie', 'Brown', 'active'],
    ];

    $affected = $db->exec("INSERT INTO users (name, surname, status) VALUES (?, ?, ?)", $batchParams);
    echo "<p>Inserted rows count: <strong>{$affected}</strong></p>";

    // 3. Scalar Lookup
    echo "<p class='step'>3. Verification: Scalar Lookup (selectVal)</p>";
    $totalActive = $db->selectVal("SELECT COUNT(*) FROM users WHERE status = ?", ['active']);
    echo "<p>Total active users: <strong>{$totalActive}</strong></p>";

    // 4. Strong-Typed DTO Hydration
    echo "<p class='step'>4. Hydration: Strong-Typed DTO Hydration (select / selectOne)</p>";
    /** @var UserDto[] $users */
    $users = $db->select("SELECT id, name, surname, status FROM users", [], UserDto::class);

    echo "<p>Hydrated DTO list:</p>";
    $userListOutput = [];
    foreach ($users as $user) {
        $userListOutput[] = "- ID #{$user->id}: {$user->getFullName()} [Status: {$user->status}]";
    }
    echo "<pre>" . htmlspecialchars(implode("\n", $userListOutput)) . "</pre>";

    /** @var UserDto|null $singleUser */
    $singleUser = $db->selectOne("SELECT id, name, surname, status FROM users WHERE name = ?", ['Bob'], UserDto::class);
    if ($singleUser !== null) {
        echo "<p>Found single user DTO: <strong>{$singleUser->getFullName()}</strong></p>";
    }

    // 5. Managed Transaction with Automatic Rollback
    echo "<p class='step'>5. Transactions: Managed Transaction with Automatic Rollback on Exception</p>";
    try {
        $db->transaction(function (PDO4You $conn) {
            $conn->exec("INSERT INTO users (name, surname, status) VALUES (?, ?, ?)", ['Diana', 'Prince', 'active']);
            throw new \RuntimeException('Simulated payment failure, rolling back transaction!');
        });
    } catch (\RuntimeException $e) {
        echo "<p class='success'>✓ Caught expected exception: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    $countAfterRollback = $db->selectVal("SELECT COUNT(*) FROM users");
    echo "<p>Total users after rollback attempt: <strong>{$countAfterRollback}</strong> (Expected: 3)</p>";

    // 6. Query Profile Log
    echo "<p class='step'>6. Observability: Captured SQL Queries</p>";
    echo "<pre>" . htmlspecialchars(implode("\n", $queriesLog)) . "</pre>";

} catch (\Throwable $e) {
    echo "<div class='error'>";
    echo "<h3>An error occurred:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
} finally {
    // Ensure query listener is always reset to prevent state leaks
    PDO4You::onQuery(null);
}
