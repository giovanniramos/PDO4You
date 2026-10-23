# PDO4You

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Version](https://img.shields.io/badge/version-6.0.0-blue.svg)](https://github.com/giovanniramos/PDO4You/releases)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

*[Leia a documentação em Português-BR](README-pt-BR.md)*

**A modern, lightweight, and testable database wrapper for PHP.**

**PDO4You** simplifies PDO usage without adding the overhead and complexity of a full ORM.

Built for **PHP 8.2+**, PDO4You keeps SQL under your control while adding a clean, secure abstraction layer for common database operations, dependency injection, and platform-specific strategies.

> **PDO4You does not replace PDO. It works alongside it.**

---

## ✨ Features

* 🚀 **Lightweight** — A thin layer over PDO with minimal overhead.
* 🧩 **Simple** — Keep writing native SQL while maintaining full control over your queries.
* 💉 **Testable** — First-class support for dependency injection (`PDO` and `DatabasePlatform`).
* ⚡ **Fast Setup** — `connect()` factory and automatic platform detection from DSN strings or existing `PDO` instances.
* 📦 **PSR-4** — Standard Composer-compatible autoloading.
* 🐘 **Modern PHP** — Strict typing, `#[\SensitiveParameter]`, `readonly` properties, and PHP 8.2+ features.
* 🔌 **Platform Strategies** — Dialect abstraction (identifier quoting and last insert ID retrieval) for **MySQL/MariaDB**, **PostgreSQL**, **SQLite**, and **SQL Server**.
* 🛡️ **Native Security & Typed Binding** — True prepared statements with automatic scalar type detection (`PARAM_INT`, `PARAM_BOOL`, `PARAM_NULL`, `PARAM_LOB`).
* 🔄 **Managed Transactions & Savepoints** — Safe transaction handling through closures with automatic rollback and nested transaction support via `SAVEPOINT`.
* 🌊 **Lazy Streaming with `cursor()`** — Memory-efficient $O(1)$ iteration over large datasets using PHP `Generator`.
* 📊 **Observability** — Instance-scoped listeners (safe for Swoole/Octane/RoadRunner) and global callbacks for query logging and profiling.
* 🚫 **No ORM** — No complex entities or mandatory mappings.

---

## 📦 Installation

Install PDO4You via Composer:

```bash
composer require giovanniramos/pdo4you
```

---

## ⚡ Quick Start

You can initialize PDO4You in two ways: using the `connect()` DSN factory or by injecting an existing `PDO` connection.

### 1. DSN Factory Initialization (Recommended)

The static `connect()` method sets secure defaults and automatically resolves the proper platform strategy from the DSN driver (`mysql`, `pgsql`, `sqlite`, or `sqlsrv`):

```php
<?php

use PDO4You\PDO4You;

$db = PDO4You::connect(
    'mysql:host=localhost;dbname=mydb;charset=utf8mb4',
    'username',
    'password'
);
```

### 2. Dependency Injection (`PDO`)

Ideal for Dependency Injection (DI) containers or when reusing existing database connections. The platform strategy is automatically detected from the `PDO` driver (or can be explicitly provided):

```php
<?php

use PDO;
use PDO4You\PDO4You;
use PDO4You\Platform\MySqlPlatform;

$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'username', 'password');

// Automatic platform detection from the PDO driver:
$db = new PDO4You($pdo);

// Or explicitly injecting a platform strategy:
$db = new PDO4You($pdo, new MySqlPlatform());
```

---

## 🧑‍💻 Usage

### Queries (`SELECT`)

PDO4You provides dedicated methods for every read scenario:

#### `select()` — Associative arrays or DTO hydration
```php
// Returns an array of associative arrays
$users = $db->select(
    'SELECT * FROM users WHERE status = ?',
    ['active']
);

// Direct hydration into class/DTO instances (PDO::FETCH_CLASS)
$users = $db->select(
    'SELECT id, name, email FROM users WHERE status = ?',
    ['active'],
    UserDto::class
);
```

#### `cursor()` — Lazy Streaming with `Generator` ($O(1)$ Memory)
Ideal for processing large datasets or exports row-by-row without loading the entire result set into memory:

```php
foreach ($db->cursor('SELECT id, name FROM users WHERE active = ?', [true], UserDto::class) as $user) {
    echo $user->name . PHP_EOL;
}
```

#### `selectOne()` — Fetch a single row
Fetches only the first record (or `null`) and immediately closes the statement cursor:

```php
$user = $db->selectOne('SELECT * FROM users WHERE id = ?', [1]);

if ($user !== null) {
    echo $user['name'];
}
```

#### `selectVal()` — Scalar value
Directly returns a single column value from the first row (useful for counts, sums, and lookups):

```php
$total = (int) $db->selectVal('SELECT COUNT(*) FROM users WHERE status = ?', ['active']);
```

#### `selectObj()` — Array of objects (`stdClass` or custom class)
```php
$users = $db->selectObj('SELECT name, email FROM users');

foreach ($users as $user) {
    echo $user->name;
}
```

#### `selectNum()` — Numerically indexed arrays
```php
$rows = $db->selectNum('SELECT id, name FROM users');
// $rows[0][0] = id, $rows[0][1] = name
```

---

### Fluent DML Helpers (`insert()`, `update()`, `delete()`)

Dedicated methods for common write operations featuring automatic dialect-aware identifier quoting (including qualified `schema.table` names) and protection against empty `WHERE` clauses:

#### `insert()` — Insert a record
```php
$affected = $db->insert('users', [
    'name'    => 'John',
    'surname' => 'Wick',
    'status'  => 'active',
]);

$lastId = $db->lastId();
```

#### `update()` — Update records
Supports both named and positional parameters in the `WHERE` clause:

```php
$affected = $db->update(
    'users',
    ['status' => 'inactive'],
    'id = :id',
    ['id' => 5]
);
```

#### `delete()` — Delete records
```php
$affected = $db->delete(
    'users',
    'id = :id',
    ['id' => 10]
);
```

---

### Direct & Batch Execution (`exec()`)

The `exec()` method executes parameterized statements and returns the number of affected rows.

#### Single Execution
```php
$affected = $db->exec(
    'UPDATE users SET status = ? WHERE id = ?',
    ['inactive', 5]
);
```

#### Batch Execution (Batch Insert / Batch Update)
When passing a list of parameter arrays, PDO4You prepares the statement once and executes all parameter sets efficiently:

```php
$totalInserted = $db->exec(
    'INSERT INTO users (name, surname) VALUES (?, ?)',
    [
        ['John', 'Doe'],
        ['Jane', 'Doe'],
        ['Alice', 'Smith'],
    ]
);
```

---

### Pagination (`Pagination`)

The `Pagination` service calculates offsets, limits, and navigation metadata:

```php
use PDO4You\Pagination;

$total = (int) $db->selectVal('SELECT COUNT(*) FROM items');
$pagination = new Pagination(currentPage: 2, perPage: 10, total: $total);

$items = $db->select(
    'SELECT id, title FROM items ORDER BY id ASC LIMIT :limit OFFSET :offset',
    [
        'limit'  => $pagination->getLimit(),
        'offset' => $pagination->getOffset(),
    ]
);

$meta = $pagination->getMetadata();
// ['currentPage' => 2, 'perPage' => 10, 'total' => 25, 'totalPages' => 3, 'offset' => 10, 'hasPrevious' => true, 'hasNext' => true]
```

---

### Last Inserted ID (`lastId()`)

Retrieves the identifier generated by the last insert operation using the active platform strategy (including automatic same-batch `SCOPE_IDENTITY()` capture on SQL Server):

```php
$db->insert('users', ['name' => 'John']);

$userId = $db->lastId();

// For PostgreSQL with specific sequences:
// $userId = $db->lastId('users_id_seq');
```

---

### Managed Transactions & Savepoints

The `transaction()` method wraps operations in a safe atomic block. Nested calls automatically use SQL `SAVEPOINT`s:

```php
$db->transaction(function (PDO4You $conn) {
    $conn->exec('UPDATE accounts SET balance = balance - 100 WHERE id = ?', [1]);
    $conn->exec('UPDATE accounts SET balance = balance + 100 WHERE id = ?', [2]);

    // Nested transaction (Savepoint)
    $conn->transaction(function (PDO4You $nested) {
        $nested->insert('audit_logs', ['action' => 'transfer']);
    });
});
```

Manual transaction controls (`beginTransaction()`, `commit()`, `rollBack()`, and `inTransaction()`) are also available.

---

### Observability and Logging (`addQueryListener` / `onQuery`)

Monitor execution duration (in milliseconds) and parameters for every query. Listener exceptions are isolated so observability failures never disrupt database operations:

```php
// Instance-scoped listener (recommended for Octane, Swoole, RoadRunner, and multi-DB apps)
$db->addQueryListener(function (string $sql, array $params, float $durationMs) {
    if ($durationMs > 50.0) {
        error_log(sprintf('[Slow Query: %.2fms] %s', $durationMs, $sql));
    }
});

// Or global static listener
PDO4You::onQuery(function (string $sql, array $params, float $durationMs) {
    // ...
});
```

---

### Structured Error Handling (`QueryException`)

All PDO preparation and execution errors are wrapped in `PDO4You\Exception\QueryException`, preserving full diagnostic context:

```php
use PDO4You\Exception\QueryException;

try {
    $db->insert('users', ['email' => 'duplicate@example.com']);
} catch (QueryException $e) {
    echo $e->getMessage();    // Detailed error message
    echo $e->getSqlState();   // ANSI SQLSTATE code (e.g., '23000', '23505')
    echo $e->getDriverCode(); // Driver-specific error code (e.g., 1062 on MySQL)
    echo $e->getSql();        // The failed SQL query
    print_r($e->getParams()); // Bound parameters
}
```

---

## 🔌 Supported Platforms

PDO4You uses `PDO4You\Platform\DatabasePlatform` implementations to encapsulate engine-specific behavior:

* `PDO4You\Platform\MySqlPlatform` — Support for **MySQL** and **MariaDB** (`` ` `` identifier quoting and `LAST_INSERT_ID()`).
* `PDO4You\Platform\PgSqlPlatform` — Support for **PostgreSQL** (`"` identifier quoting, `lastval()`, and validated `currval()`).
* `PDO4You\Platform\SqlitePlatform` — Support for **SQLite** (`"` identifier quoting and `last_insert_rowid()`).
* `PDO4You\Platform\SqlSrvPlatform` — Support for **Microsoft SQL Server** (`[ ]` identifier quoting and same-batch `SCOPE_IDENTITY()` capture).

---

## 🏗️ Architecture

PDO4You adds a thin layer over PDO, keeping SQL explicit while encapsulating database-specific behavior through the *Strategy* pattern (`DatabasePlatform`).

![PDO4You Architecture](docs/images/pdo4you-architecture-en.png)

---

## 🎯 Why PDO4You?

PDO already provides an excellent native API for database access, while full ORMs introduce robust abstraction layers. **PDO4You** was designed to fill the space between them: a lightweight convenience layer without giving up control over SQL.

- **Simpler than an ORM** — no entities, models, or mandatory mapping.
- **More convenient than raw PDO** — direct selection methods, consistent error handling, and batch execution.
- **SQL remains under your control** — you write the queries and decide how data is accessed.

---

## ⚡ Quick Test

After installing the dependencies, you can quickly verify that PDO4You is working using the script included in the project root:

```bash
php quick-test.php
```

---

## 🧪 Examples

The project includes a suite of practical usage examples.

To view the examples through a web interface, start PHP's built-in server:

```bash
php -S localhost:8000 -t examples
```

Then open the following URL in your browser:

```text
http://localhost:8000
```

The examples use an in-memory SQLite database by default, allowing you to experiment with the library without configuring a database server.

---

## 🛠️ Development

Clone the repository and install the dependencies:

```bash
git clone https://github.com/giovanniramos/PDO4You.git
cd PDO4You
composer install
```

Run the tests with PHPUnit:

```bash
./vendor/bin/phpunit tests
```

To run the tests in an isolated Docker environment:

```bash
docker compose build -q --no-cache
docker compose run --rm test
```

---

## 📋 Requirements

* **PHP:** 8.2 or higher
* **PDO Extensions:** `ext-pdo` and the corresponding driver (`pdo_mysql`, `pdo_pgsql`, `pdo_sqlite`, or `pdo_sqlsrv`)
* **Composer**

---

## 📄 License

PDO4You is distributed under the **MIT** license. See the [LICENSE](LICENSE) file for details.
