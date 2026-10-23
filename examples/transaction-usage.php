<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Demonstrating secure database transactions, nested savepoints, and rollback handling.
 *
 * @title Transactions
 * @order 4
 */

use PDO4You\PDO4You;

try {
    // 1. Setup & Connection: Initialize connection via DSN factory
    echo "<p class='step'>1. Setup: Initializing in-memory SQLite connection</p>";
    $db = PDO4You::connect('sqlite::memory:');

    // 2. Schema Definition: Create accounts table with balance constraints
    $db->exec("
        CREATE TABLE accounts (
            id INTEGER PRIMARY KEY,
            user_id INTEGER NOT NULL,
            account_number TEXT UNIQUE NOT NULL,
            balance REAL NOT NULL DEFAULT 0.0 CHECK (balance >= 0),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // 3. Data Seeding: Insert initial test accounts with starting balances
    $db->exec("
        INSERT INTO accounts (id, user_id, account_number, balance) VALUES
            (1, 1001, 'ACC001', 100.00),
            (2, 1002, 'ACC002', 50.00)
    ");
    echo "<p>Initial state: Account 1 = 100.00 | Account 2 = 50.00</p>";

    // 4. Managed Transaction Execution with Nested Savepoint: Transfer funds atomically
    echo "<p class='step'>2. Performing Transaction: Transferring 30.00 from Account 1 to Account 2</p>";

    $db->transaction(function (PDO4You $conn): void {
        // Debit funds from source account
        $conn->exec("UPDATE accounts SET balance = balance - 30.00 WHERE id = ?", [1]);

        // Credit funds to target account
        $conn->exec("UPDATE accounts SET balance = balance + 30.00 WHERE id = ?", [2]);

        // Optional nested transaction (Savepoint): if it fails, only the savepoint rolls back
        try {
            $conn->transaction(function (PDO4You $nested): void {
                // Attempt an invalid overdraft (violates CHECK balance >= 0)
                $nested->exec("UPDATE accounts SET balance = balance - 500.00 WHERE id = ?", [1]);
            });
        } catch (\Throwable) {
            echo "<p class='success'>✓ Nested savepoint rolled back safely without aborting main transfer.</p>";
        }
    });

    echo "<p class='success'>✓ Main transaction committed successfully.</p>";

    // 5. Verification: Query final account balances
    echo "<p class='step'>3. Verification: Inspecting final account balances</p>";
    $accounts = $db->select("SELECT id, account_number, printf('%.2f', balance) AS balance FROM accounts");

    echo "<p>Final state:</p>";
    echo "<pre>" . htmlspecialchars(print_r($accounts, true)) . "</pre>";

} catch (\Throwable $e) {
    echo "<div class='error'>";
    echo "<h3>An error occurred:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}
