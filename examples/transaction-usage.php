<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PDO4You\PDO4You;
use PDO4You\Platform\SqlitePlatform;

// 1. Connection setup
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db = new PDO4You($pdo, new SqlitePlatform());

// 2. Schema definition
$db->exec("CREATE TABLE accounts (id INTEGER PRIMARY KEY, balance INTEGER)");

// 3. Insert initial data
$db->exec("INSERT INTO accounts (id, balance) VALUES (1, 100), (2, 50)");

// 4. Perform Transaction
$db->beginTransaction();
try {
    $db->exec("UPDATE accounts SET balance = balance - 30 WHERE id = ?", [1]);
    $db->exec("UPDATE accounts SET balance = balance + 30 WHERE id = ?", [2]);
    $db->commit();
    $status = ['success' => true, 'message' => 'Transaction committed successfully!'];
} catch (Exception $e) {
    $db->rollBack();
    $status = ['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()];
}

// 5. Return data for the viewer
return [
    'status' => $status,
    'accounts' => $db->select("SELECT * FROM accounts")
];
