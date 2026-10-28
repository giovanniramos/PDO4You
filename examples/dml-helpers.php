<?php

declare(strict_types=1);

require_once __DIR__ . '/../autoload.php';

/**
 * Demonstrating fluent DML methods (insert, update, delete) and managed transactions.
 *
 * @title DML Helpers
 * @order 3
 */

use PDO4You\PDO4You;

try {
    // 1. Setup & Connection
    echo "<p class='step'>1. Setup: Connecting to in-memory SQLite database</p>";
    $db = PDO4You::connect('sqlite::memory:');
    echo "<p class='success'>✓ Connected successfully via DSN.</p>";

    // Create schema
    $db->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            surname TEXT NOT NULL,
            email TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'active'
        )
    ");
    echo "<p class='success'>✓ Table 'users' created.</p>";

    // 2. Insert Helper
    echo "<p class='step'>2. DML Operation: insert() method</p>";
    $affectedInsert = $db->insert('users', [
        'name'    => 'John',
        'surname' => 'Wick',
        'email'   => 'john.wick@continental.com',
        'status'  => 'active',
    ]);
    $lastId = (int) $db->lastId();
    echo "<p>Inserted rows: <strong>{$affectedInsert}</strong> | Last Insert ID: <strong>{$lastId}</strong></p>";

    // 3. Update Helper
    echo "<p class='step'>3. DML Operation: update() method</p>";
    $affectedUpdate = $db->update(
        'users',
        ['status' => 'suspended', 'email' => 'john.excommunicado@continental.com'],
        'id = :id',
        ['id' => $lastId]
    );
    echo "<p>Updated rows: <strong>{$affectedUpdate}</strong></p>";

    $updatedUser = $db->selectOne("SELECT name, surname, email, status FROM users WHERE id = ?", [$lastId]);
    echo "<pre>" . htmlspecialchars(print_r($updatedUser, true)) . "</pre>";

    // 4. Delete Helper
    echo "<p class='step'>4. DML Operation: delete() method</p>";
    $db->insert('users', [
        'name'    => 'Cassian',
        'surname' => 'Bodyguard',
        'email'   => 'cassian@roma.com',
        'status'  => 'pending',
    ]);
    $targetId = (int) $db->lastId();

    $affectedDelete = $db->delete('users', 'id = :id', ['id' => $targetId]);
    echo "<p>Deleted rows: <strong>{$affectedDelete}</strong> (Target ID: {$targetId})</p>";

    // 5. Atomic and secure transaction with DML Helpers
    echo "<p class='step'>5. Transactions: Managed Transaction using insert() and update()</p>";
    $transactionResult = $db->transaction(function (PDO4You $conn): string {
        $conn->insert('users', [
            'name'    => 'Winston',
            'surname' => 'Scott',
            'email'   => 'winston@continental.com',
            'status'  => 'active',
        ]);

        $conn->insert('users', [
            'name'    => 'Charon',
            'surname' => 'Concierge',
            'email'   => 'charon@continental.com',
            'status'  => 'active',
        ]);

        return $conn->lastId();
    });

    echo "<p>Last inserted user ID in transaction: <strong>" . htmlspecialchars($transactionResult) . "</strong></p>";

} catch (\Throwable $e) {
    echo "<div class='error'>";
    echo "<h3>An error occurred:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}
