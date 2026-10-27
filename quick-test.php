<?php

declare(strict_types=1);

// Load Composer autoloader
require_once __DIR__ . '/autoload.php';

use PDO4You\Pagination;
use PDO4You\PDO4You;

if (!extension_loaded('pdo_sqlite')) {
    fwrite(STDERR, "✗ Error: The 'pdo_sqlite' extension is required to run quick-test.php (enable 'extension=pdo_sqlite' in your php.ini).\n");
    exit(1);
}

try {
    // 1. Initialize SQLite in-memory connection via DSN factory (auto-resolves SqlitePlatform)
    $db = PDO4You::connect('sqlite::memory:');

    // 2. Register an instance-scoped query listener for CLI profiling
    $db->addQueryListener(static function (string $sql, array $params, float $durationMs): void {
        $compactSql = preg_replace('/\s+/', ' ', trim($sql));
        printf("[Query %.3fms] %s\n", $durationMs, $compactSql);
    });

    // 3. Create a test table
    $db->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'active'
        )
    ");
    echo "✓ Table 'users' created successfully.\n";

    // 4. Insert records inside a managed transaction (batch + DML helper)
    $lastId = $db->transaction(static function (PDO4You $conn): int {
        $conn->exec(
            "INSERT INTO users (name, status) VALUES (?, ?)",
            [
                ['Alice Smith', 'active'],
                ['Bob Jones', 'active'],
            ]
        );

        // insert() returns the number of affected rows (1)
        $conn->insert('users', [
            'name'  => 'Charlie Brown',
            'status' => 'active',
        ]);

        // Retrieve the generated ID (ID = 3)
        $id = (int) $conn->lastId();

        // Update using his newly generated ID
        $conn->update(
            'users',
            ['status' => 'pending'],
            'id = :id',
            ['id' => $id]
        );

        return $id;
    });

    echo "✓ Records inserted and updated successfully. Last ID: {$lastId}\n";

    // 5. Paginate and query records
    $total = (int) $db->selectVal("SELECT COUNT(*) FROM users");
    $pagination = new Pagination(currentPage: 1, perPage: 10, total: $total);

    $users = $db->select(
        "SELECT id, name, status FROM users ORDER BY id ASC LIMIT :limit OFFSET :offset",
        [
            'limit'  => $pagination->getLimit(),
            'offset' => $pagination->getOffset(),
        ]
    );

    echo "\nRetrieved users ({$pagination->getTotal()} total):\n";
    print_r($users);

} catch (\Throwable $e) {
    fwrite(STDERR, "✗ Error: " . $e->getMessage() . "\n");
    exit(1);
}
