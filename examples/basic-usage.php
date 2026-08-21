<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PDO4You\PDO4You;
use PDO4You\Platform\SqlitePlatform;

$logs = [];
$userRecord = null;
$errorMessage = null;

try {
    // 1. Connection setup
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db = new PDO4You($pdo, new SqlitePlatform());
    $logs[] = "SQLite in-memory connection initialized.";

    // 2. Schema definition
    $db->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        surname TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    $logs[] = "Table 'users' created.";

    // 3. Insert record
    $db->exec("INSERT INTO users (name, surname) VALUES (?, ?)", ['John', 'Doe']);
    $lastId = $db->lastId();
    $logs[] = "Record inserted with ID: {$lastId}.";

    // 4. Verify insertion
    $userRecord = $db->select("SELECT id, name, surname, created_at FROM users WHERE id = ?", [$lastId]);

} catch (Throwable $e) {
    $errorMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDO4You - Modern Example</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; padding: 24px; max-width: 760px; margin: auto; background-color: #f9fafb; color: #1f2937; }
        .card { background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        pre { background: #1e293b; color: #f8fafc; border: 1px solid #334155; padding: 14px; border-radius: 6px; overflow-x: auto; margin:0; }
        .error { color: #991b1b; background: #fee2e2; border: 1px solid #fecaca; padding: 14px; border-radius: 6px; }
        .log-item { background: #eff6ff; padding: 10px 14px; border-radius: 6px; margin-bottom: 8px; color: #1d4ed8; font-weight: 500; }
    </style>
</head>
<body>
    <div class="card">
        <h1>PDO4You — Execution Overview</h1>

        <?php if ($errorMessage !== null): ?>
            <div class="error">
                <strong>Error Encountered:</strong>
                <pre><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></pre>
            </div>
        <?php else: ?>
            <h2>Completed Steps</h2>
            <?php foreach ($logs as $log): ?>
                <div class="log-item">✓ <?= htmlspecialchars($log, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endforeach; ?>

            <h2>Resulting Record</h2>
            <pre><?= htmlspecialchars(print_r($userRecord, true), ENT_QUOTES, 'UTF-8') ?></pre>
        <?php endif; ?>
    </div>
</body>
</html>
