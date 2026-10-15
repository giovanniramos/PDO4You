<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

/**
 * Demonstrating pagination metadata calculation, offset, and limit queries.
 *
 * @title Pagination
 * @order 5
 */

use PDO4You\PDO4You;
use PDO4You\Pagination;

try {
    // 1. Setup & Connection: Initialize connection and seed sample data
    echo "<p class='step'>1. Setup: Connecting and seeding test records</p>";
    $db = PDO4You::connect('sqlite::memory:');
    echo "<p class='success'>✓ Connected successfully via DSN.</p>";

    // Create schema
    $db->exec("
        CREATE TABLE items (
            id INTEGER PRIMARY KEY,
            title TEXT NOT NULL
        )
    ");

    // Insert 25 sample items using batch execution
    $batch = [];
    for ($i = 1; $i <= 25; $i++) {
        $batch[] = ["Item #{$i}"];
    }
    $db->exec("INSERT INTO items (title) VALUES (?)", $batch);
    echo "<p class='success'>✓ Table 'items' created and 25 records seeded.</p>";

    // 2. Pagination Configuration: Calculate offset and limits
    $currentPage = 2;
    $perPage = 5;
    echo "<p class='step'>2. Pagination: Calculating offsets and limits for page $currentPage ($perPage items per page)</p>";

    $totalRecords = (int) $db->selectVal("SELECT COUNT(*) FROM items");

    $pagination = new Pagination($currentPage, $perPage);
    $pagination->setTotal($totalRecords);

    $metadata = $pagination->getMetadata();
    echo "<pre>" . htmlspecialchars(print_r($metadata, true)) . "</pre>";

    // 3. Paginated Query: Fetch records using limit and offset parameters
    echo "<p class='step'>3. Query: Fetching paginated results using offset and limit</p>";
    $items = $db->select(
        "SELECT id, title FROM items ORDER BY id LIMIT :limit OFFSET :offset",
        [
            'limit' => $pagination->getLimit(),
            'offset' => $pagination->getOffset()
        ]
    );

    echo "<p>Page {$currentPage} results:</p>";
    echo "<pre>" . htmlspecialchars(print_r($items, true)) . "</pre>";

} catch (\Throwable $e) {
    echo "<div class='error'>";
    echo "<h3>An error occurred:</h3>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}
