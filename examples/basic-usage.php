<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PDO4You\PDO4You;
use PDO4You\Platform\SqlitePlatform;

/**
 * Modern Usage Example: PDO4You
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDO4You - Modern Example</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; padding: 20px; max-width: 800px; margin: auto; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; border: 1px solid #ddd; }
        h1 { color: #333; }
        .step { color: #007bff; font-weight: bold; }
    </style>
</head>
<body>
    <h1>PDO4You - Modern Usage Example</h1>

    <?php
    try {
        // 1. Setup - Injecting Platform
        echo "<p class='step'>1. Setup: Creates an in-memory SQLite PDO connection</p>";
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $db = new PDO4You($pdo, new SqlitePlatform());

        // 2. Prepare environment
        $db->exec("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, surname TEXT)");
        echo "<p>Table 'users' created.</p>";

        // 3. Insert record
        echo "<p class='step'>2. Operations: Inserting a new record</p>";
        $db->exec("INSERT INTO users (name, surname) VALUES ('John', 'Doe')");

        // 4. Get last ID
        $lastId = $db->lastId();
        echo "<p>Last inserted ID: <strong>{$lastId}</strong></p>";

        // 5. Verify insertion
        echo "<p class='step'>3. Verification: Selecting the record</p>";
        $user = $db->select("SELECT * FROM users WHERE id = ?", [$lastId]);

        echo "<p>Resulting record:</p>";
        echo "<pre>" . htmlspecialchars(print_r($user, true)) . "</pre>";

    } catch (Exception $e) {
        echo "<h1 style='color:red;'>Error:</h1><pre>" . $e->getMessage() . "</pre>";
    }
    ?>
</body>
</html>
