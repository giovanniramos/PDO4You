<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

/**
 * Demonstrating basic connection, table creation, batch record insertion, and selection.
 *
 * @title Basic Usage
 * @order 1
 */

use PDO4You\PDO4You;
use PDO4You\Platform\SqlitePlatform;

try {
    // 1. Setup & Connection: Initializing in-memory SQLite connection and platform strategy
    echo "<p class='step'>1. Setup: Initializing in-memory SQLite connection and platform</p>";
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db = new PDO4You($pdo, new SqlitePlatform());
    echo "<p class='success'>✓ PDO4You instance created successfully.</p>";

    // 2. Schema Definition: Create users table with automatic timestamp
    $db->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            surname TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "<p class='success'>✓ Table 'users' created.</p>";

    // 3. Data Insertion: Insert multiple records using batch execution
    echo "<p class='step'>2. Operations: Insert multiple records using batch execution</p>";
    $db->exec("INSERT INTO users (name, surname) VALUES (?, ?)", [['John', 'Doe'], ['Jane', 'Doe']]);

    // 4. Last Insert ID: Retrieve the last generated ID
    $lastId = $db->lastId();
    echo "<p>Last inserted ID: <strong>{$lastId}</strong></p>";

    // 5. Verification: Selecting the last inserted record
    echo "<p class='step'>3. Verification: Selecting the last inserted record</p>";
    $user = $db->select("SELECT id, name, surname, created_at FROM users WHERE id = ?", [$lastId]);

    echo "<p>Resulting record:</p>";
    echo "<pre>" . htmlspecialchars(print_r($user, true)) . "</pre>";

} catch (\PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>Database Error:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
} catch (\Throwable $e) {
    echo "<div class='error'>";
    echo "<h3>Unexpected Error:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}
