<?php

declare(strict_types=1);

require_once __DIR__ . '/../autoload.php';

/**
 * Demonstrating connection via DSN strings with automatic platform resolution.
 *
 * @title DSN Usage
 * @order 2
 */

use PDO4You\PDO4You;

try {
    // 1. Setup & Connection: Initialize connection via DSN factory with automatic platform resolution
    echo "<p class='step'>1. Setup: Connecting via DSN (sqlite::memory:)</p>";
    $db = PDO4You::connect('sqlite::memory:');
    echo "<p class='success'>✓ Connected successfully and platform resolved automatically.</p>";

    // 2. Schema Definition: Create products table
    $db->exec("
        CREATE TABLE products (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL,
            price REAL NOT NULL
        )
    ");
    echo "<p class='success'>✓ Table 'products' created.</p>";

    // 3. Data Insertion: Insert product records using prepared statements
    echo "<p class='step'>2. Operations: Inserting products</p>";
    $db->exec(
        "INSERT INTO products (name, price) VALUES (?, ?)",
        [
            ['Laptop', 1200.50],
            ['Smartphone', 799.99],
        ]
    );
    $lastId = $db->lastId();
    echo "<p>Last inserted ID: <strong>" . htmlspecialchars($lastId) . "</strong></p>";

    // 4. Verification: Query products from the database
    echo "<p class='step'>3. Verification: Querying products</p>";
    $products = $db->select("SELECT id, name, price FROM products");

    echo "<p>Resulting records:</p>";
    echo "<pre>" . htmlspecialchars(print_r($products, true)) . "</pre>";

} catch (\Throwable $e) {
    echo "<div class='error'>";
    echo "<h3>An error occurred:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}
