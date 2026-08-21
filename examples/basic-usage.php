<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PDO4You\PDO4You;
use PDO4You\Platform\SqlitePlatform;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDO4You - Modern Example</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; padding: 24px; max-width: 800px; margin: auto; background-color: #f9fafb; color: #1f2937; }
        pre { background: #1e293b; color: #f8fafc; padding: 14px; border-radius: 6px; overflow-x: auto; font-size: 0.9em; margin: 0; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .step { color: #2563eb; font-weight: 600; margin-top: 16px; }
        .success { color: #16a34a; font-weight: 500; }
        .error { color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; padding: 16px; border-radius: 8px; }
        h3 { margin-top: 0; }
    </style>
</head>
<body>
    <div class="card">
        <h1>PDO4You — Modern Example</h1>

        <?php
        try {
            // 1. Setup - Injecting Platform
            echo "<p class='step'>1. Setup: Initializing in-memory SQLite connection</p>";
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            $db = new PDO4You($pdo, new SqlitePlatform());
            echo "<p class='success'>✓ PDO4You instance created successfully.</p>";

            // 2. Schema definition
            $db->exec("CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                surname TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            echo "<p class='success'>✓ Table 'users' created.</p>";

            // 3. Insert multiple records
            echo "<p class='step'>2. Operations: Insert multiple records using batch execution</p>";
            $db->exec("INSERT INTO users (name, surname) VALUES (?, ?)", [['John', 'Doe'], ['Jane', 'Doe']]);

            // 4. Retrieve last inserted ID
            $lastId = $db->lastId();
            echo "<p>Last inserted ID: <strong>{$lastId}</strong></p>";

            // 5. Selecting the last inserted record
            echo "<p class='step'>3. Verification: Selecting the last inserted record</p>";
            $user = $db->select("SELECT id, name, surname, created_at FROM users WHERE id = ?", [$lastId]);

            echo "<p>Resulting record:</p>";
            echo "<pre>" . htmlspecialchars(print_r($user, true), ENT_QUOTES, 'UTF-8') . "</pre>";

        } catch (PDOException $e) {
            // Handle database-related exceptions
            echo "<div class='error'>";
            echo "<h3>Database Error:</h3>";
            echo "<pre>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</pre>";
            echo "</div>";
        } catch (Throwable $e) {
            // Handle any other unexpected exceptions
            echo "<div class='error'>";
            echo "<h3>Unexpected Error:</h3>";
            echo "<pre>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</pre>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
