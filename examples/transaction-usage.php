<?php

use PDO4You\PDO4You;
use PDO4You\Platform\SqlitePlatform;

try {
    // 1. Setup: Creates an in-memory SQLite PDO connection
    echo "<p class='step'>1. Setup: Creating PDO connection and injecting Platform</p>";
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $db = new PDO4You($pdo, new SqlitePlatform());

    // 2. Schema definition
    $db->exec("CREATE TABLE accounts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        account_number TEXT UNIQUE NOT NULL,
        balance REAL NOT NULL DEFAULT 0.0 CHECK (balance >= 0),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Insert initial data
    $db->exec("
        INSERT INTO accounts (id, user_id, account_number, balance) VALUES
        (1, 1001, 'ACC001', 100.00),
        (2, 1002, 'ACC002', 50.00)
     ");
    echo "<p>Initial state: Account 1 = 100.00, Account 2 = 50.00</p>";

    // 4. Perform Transaction
    echo "<p class='step'>2. Performing transaction: Transfer 30.00 from Account 1 to Account 2</p>";

    $db->beginTransaction();
    try {
        // -- Debit from account X
        $db->exec("UPDATE accounts SET balance = balance - 30.00 WHERE id = ?", [1]);

        // -- Credit to account Y
        $db->exec("UPDATE accounts SET balance = balance + 30.00 WHERE id = ?", [2]);

        // -- Uncomment the following line to simulate an error and trigger a rollback
        // throw new Exception("Simulated error during transaction");
        $db->commit();
        echo "<p class='success'>✓ Transaction committed successfully!</p>";
    } catch (\Exception $e) {
        $db->rollBack();
        echo "<p class='error'>Transaction failed and rolled back: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    // 5. Selecting the records
    echo "<p class='step'>3. Verification: Selecting the records</p>";
    $accounts = $db->select("SELECT id, printf('%.2f', balance) AS balance FROM accounts");

    echo "<p>Final state:</p>";
    echo "<pre>" . htmlspecialchars(print_r($accounts, true)) . "</pre>";

} catch (\Exception $e) {
    echo "<div class='error'>";
    echo "<h3>An error occurred:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}
