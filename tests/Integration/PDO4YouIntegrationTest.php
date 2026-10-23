<?php

declare(strict_types=1);

namespace PDO4You\Tests\Integration;

use PDO4You\PDO4You;
use PHPUnit\Framework\TestCase;
use PDOException;

class PDO4YouIntegrationTest extends TestCase
{
    public function testConnectWithSqliteDsn(): void
    {
        // Arrange & Act
        $db = PDO4You::connect('sqlite::memory:');

        $db->exec("CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)");
        $affected = $db->exec("INSERT INTO users (name) VALUES (?)", ['Alice']);
        $rows = $db->select("SELECT * FROM users");

        // Assert
        $this->assertNotNull($db);
        $this->assertSame(1, $affected);
        $this->assertCount(1, $rows);
        $this->assertSame('Alice', $rows[0]['name']);
    }

    public function testConnectWithMySqlDsn(): void
    {
        // Arrange
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $dsn = "mysql:host={$host};dbname=pdo4you";

        try {
            $db = PDO4You::connect($dsn, 'admin', 'pass');
        } catch (PDOException $e) {
            $this->markTestSkipped('MySQL server is not available: ' . $e->getMessage());
        }

        // Act
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                surname VARCHAR(255) NOT NULL
            )
        ");

        $affected = $db->exec("INSERT INTO users (name, surname) VALUES (?, ?)", ['Bob', 'Jones']);
        $rows = $db->select("SELECT * FROM users WHERE name = ?", ['Bob']);

        // Assert
        $this->assertNotNull($db);
        $this->assertSame(1, $affected);
        $this->assertNotEmpty($rows);
        $this->assertSame('Bob', $rows[0]['name']);
        $this->assertSame('Jones', $rows[0]['surname']);
    }

    public function testConnectWithPgSqlDsn(): void
    {
        // Arrange
        $host = '127.0.0.1';
        $dsn = "pgsql:host={$host};dbname=pdo4you";

        try {
            $db = PDO4You::connect($dsn, 'admin', 'pass');
        } catch (PDOException $e) {
            $this->markTestSkipped('PostgreSQL server is not available: ' . $e->getMessage());
        }

        // Act
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                surname VARCHAR(255) NOT NULL
            )
        ");

        $affected = $db->exec("INSERT INTO users (name, surname) VALUES (?, ?)", ['Charlie', 'Brown']);
        $rows = $db->select("SELECT * FROM users WHERE name = ?", ['Charlie']);

        // Assert
        $this->assertNotNull($db);
        $this->assertGreaterThan(0, $affected);
        $this->assertNotEmpty($rows);
        $this->assertSame('Charlie', $rows[0]['name']);
        $this->assertSame('Brown', $rows[0]['surname']);
    }

    public function testConnectWithSqlSrvDsn(): void
    {
        // Arrange
        $host = '.\SQLEXPRESS';
        $dsn = "sqlsrv:Server={$host};Database=pdo4you";

        try {
            $db = PDO4You::connect($dsn, '', '');
        } catch (PDOException $e) {
            $this->markTestSkipped('SQL Server is not available: ' . $e->getMessage());
        }

        // Act
        $db->exec("
            IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='users' and xtype='U')
            CREATE TABLE users (
                id INT IDENTITY(1,1) PRIMARY KEY,
                name NVARCHAR(255) NOT NULL,
                surname NVARCHAR(255) NOT NULL
            )
        ");

        $affected = $db->exec("INSERT INTO users (name, surname) VALUES (?, ?)", ['David', 'Smith']);
        $rows = $db->select("SELECT * FROM users WHERE name = ?", ['David']);

        // Assert
        $this->assertNotNull($db);
        $this->assertGreaterThan(0, $affected);
        $this->assertNotEmpty($rows);
        $this->assertSame('David', $rows[0]['name']);
        $this->assertSame('Smith', $rows[0]['surname']);
    }
}
