<?php

declare(strict_types=1);

namespace PDO4You\Tests\Integration;

use PDO4You\PDO4You;
use PHPUnit\Framework\TestCase;

class PDO4YouIntegrationTest extends TestCase
{
    public function testConnectWithSqliteDsn(): void
    {
        // Arrange
        $db = PDO4You::connect('sqlite::memory:');

        // Act
        $db->exec("CREATE TABLE test_table (id INTEGER PRIMARY KEY, name TEXT)");

        $affected = $db->exec("INSERT INTO test_table (name) VALUES (?)", ['Alice']);

        $rows = $db->select("SELECT * FROM test_table");

        // Assert
        $this->assertInstanceOf(PDO4You::class, $db);
        $this->assertSame(1, $affected);
        $this->assertCount(1, $rows);
        $this->assertSame('Alice', $rows[0]['name']);
    }

    public function testConnectWithMySqlDsn(): void
    {
        // Arrange
        $dsn = 'mysql:host=127.0.0.1;dbname=pdo4you';
        $db = PDO4You::connect($dsn, 'admin', 'pass');

        // Act
        $db->exec(
            "CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                surname VARCHAR(255) NOT NULL
            )"
        );

        $affected = $db->exec("INSERT INTO users (name, surname) VALUES (?, ?)", ['John', 'Doe']);

        $rows = $db->select("SELECT * FROM users WHERE name = ?", ['John']);

        // Assert
        $this->assertInstanceOf(PDO4You::class, $db);
        $this->assertSame(1, $affected);
        $this->assertNotEmpty($rows);
        $this->assertSame('John', $rows[0]['name']);
        $this->assertSame('Doe', $rows[0]['surname']);
    }
}
