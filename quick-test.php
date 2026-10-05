<?php

declare(strict_types=1);

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

use PDO4You\PDO4You;
use PDO4You\Platform\SqlitePlatform;

try {
    // 1. Initialize SQLite in-memory connection
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // 2. Instantiate PDO4You with SqlitePlatform
    $db = new PDO4You($pdo, new SqlitePlatform());

    // 3. Create a test table
    $db->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Table 'users' created successfully.\n";

    // 4. Insert records using batch execution
    $db->exec(
        "INSERT INTO users (name, email) VALUES (?, ?)",
        [
            ['Alice Smith', 'alice@example.com'],
            ['Bob Jones', 'bob@example.com']
        ]
    );
    echo "Records inserted successfully. Last ID: " . $db->lastId() . "\n";

    // 5. Query records
    $users = $db->select("SELECT * FROM users");
    echo "Retrieved users:\n";
    print_r($users);

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}