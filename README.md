# PDO4You

A modern, lightweight, and testable database wrapper for PHP.

PDO4You is designed to simplify PDO usage without the overhead of a full ORM. Built with modern PHP (8.2+), it supports dependency injection, PSR-4 autoloading, and platform-specific database strategies.

## Development

To set up the development environment, clone the repository and run:

```bash
composer install
```

## Installation

Install the package via Composer:

```bash
composer require giovanniramos/pdo4you
```

## Usage

### 1. Setup

Instantiate the class by injecting your PDO connection and the appropriate platform driver.

```php
use PDO;
use PDO4You\PDO4You;
use PDO4You\Platform\MySqlPlatform;

// 1. Create a native PDO connection
$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'password');

// 2. Select the platform
$platform = new MySqlPlatform();

// 3. Inject into PDO4You
$db = new PDO4You($pdo, $platform);
```

### 2. Operations

```php
// SELECT (returns associative array)
$users = $db->select("SELECT * FROM users WHERE status = ?", ['active']);

// EXECUTE (insert, update, delete)
$db->exec("UPDATE users SET status = 'inactive' WHERE id = 1");

// GET LAST ID (using the platform strategy)
$newId = $db->lastId();
```

## Running Tests

You can run the test suite using one of the following methods:

### 1. Using Docker (Isolated Environment)
If you have Docker installed, run:
```bash
docker-compose up --build
```

### 2. Using Command Line (Local Environment)
If you have PHP 8.2+ and Composer installed:

```bash
composer install
./vendor/bin/phpunit tests
```

## License

MIT
