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
$db->exec("CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    surname TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// 3. Insert record
$db->exec("INSERT INTO users (name, surname) VALUES (?, ?)", ['John', 'Doe']);
$lastId = $db->lastId();

// 4. Verify insertion
return [
    'lastId' => $lastId,
    'user'   => $db->select("SELECT id, name, surname, created_at FROM users WHERE id = ?", [$lastId])
];
