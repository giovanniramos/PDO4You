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
    <title>PDO4You - Transaction Example</title>
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
        <h1>PDO4You — Transaction Example</h1>

        <?php
        try {
            // 1. Setup: Creates an in-memory SQLite PDO connection
            echo "<p class='step'>1. Setup: Creating PDO connection and injecting Platform</p>";
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            $db = new PDO4You($pdo, new SqlitePlatform());

            // 2. Prepare environment
            $db->exec("CREATE TABLE accounts (id INTEGER PRIMARY KEY, balance INTEGER)");
            $db->exec("INSERT INTO accounts (id, balance) VALUES (1, 100), (2, 50)");

            echo "<p>Initial state: Account 1 (100), Account 2 (50)</p>";

            // 3. Perform Transaction
            echo "<p class='step'>2. Performing transaction: Transfer 30 from Account 1 to Account 2</p>";

            $db->beginTransaction();

            try {
                $db->exec("UPDATE accounts SET balance = balance - 30 WHERE id = ?", [1]);
                $db->exec("UPDATE accounts SET balance = balance + 30 WHERE id = ?", [2]);

                $db->commit();
                echo "<p class='success'>✓ Transaction committed successfully!</p>";
            } catch (Exception $e) {
                $db->rollBack();
                echo "<p class='error'>Transaction failed and rolled back: " . htmlspecialchars($e->getMessage()) . "</p>";
            }

            // 4. Selecting the records
            echo "<p class='step'>3. Verification: Selecting the records</p>";
            $accounts = $db->select("SELECT * FROM accounts");

            echo "<p>Final state:</p>";
            echo "<pre>" . htmlspecialchars(print_r($accounts, true), ENT_QUOTES, 'UTF-8') . "</pre>";

        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h3>An error occurred:</h3>";
            echo "<pre>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</pre>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
