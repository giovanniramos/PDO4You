<?php

declare(strict_types=1);

namespace PDO4You\Tests\Integration;

use PDO4You\Pagination;
use PDO4You\PDO4You;
use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PDO4YouIntegrationTest extends TestCase
{
    public function testSqliteFullWorkflowAndAdvancedFeatures(): void
    {
        // Arrange
        $db = PDO4You::connect('sqlite::memory:');

        $db->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                active INTEGER NOT NULL DEFAULT 1
            )
        ");

        // Act 1: Batch Insert & Last ID
        $affected = $db->exec(
            "INSERT INTO users (name, active) VALUES (?, ?)",
            [
                ['Alice', true],
                ['Bob', false],
                ['Charlie', true],
            ]
        );
        $lastId = $db->lastId();

        // Act 2: Pagination with Native Prepared Statement LIMIT/OFFSET (int binding)
        $total = (int) $db->selectVal("SELECT COUNT(*) FROM users");
        $pagination = new Pagination(currentPage: 1, perPage: 2, total: $total);

        $pageRows = $db->select(
            "SELECT id, name FROM users ORDER BY id ASC LIMIT :limit OFFSET :offset",
            [
                'limit'  => $pagination->getLimit(),
                'offset' => $pagination->getOffset(),
            ]
        );

        // Act 3: Nested Transactions (Savepoint Rollback)
        $db->transaction(function (PDO4You $conn): void {
            $conn->insert('users', ['name' => 'Diana', 'active' => 1]);

            try {
                $conn->transaction(function (PDO4You $inner): void {
                    $inner->insert('users', ['name' => 'RolledBackUser', 'active' => 0]);
                    throw new RuntimeException('Abort inner savepoint');
                });
            } catch (RuntimeException) {
                // Inner savepoint rolled back; outer transaction continues
            }
        });

        // Act 4: Lazy Cursor Iteration
        $names = [];
        foreach ($db->cursor("SELECT name FROM users ORDER BY id ASC") as $row) {
            $names[] = $row['name'];
        }

        // Assert
        $this->assertSame(3, $affected);
        $this->assertSame('3', $lastId);
        $this->assertCount(2, $pageRows);
        $this->assertSame(['Alice', 'Bob', 'Charlie', 'Diana'], $names);
    }

    public function testConnectWithMySqlDsn(): void
    {
        // Arrange
        $dbhost = getenv('DB_MYSQL_HOST') ?: '127.0.0.1';
        $dbuser = getenv('DB_MYSQL_USER') ?: 'admin';
        $dbpass = getenv('DB_MYSQL_PASS') ?: 'pass';

        try {
            $dsn = "mysql:host={$dbhost};dbname=pdo4you;charset=utf8mb4";
            $db = PDO4You::connect($dsn, $dbuser, $dbpass);
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
        $db->exec("DELETE FROM users");

        $affected = $db->insert('users', ['name' => 'Bob', 'surname' => 'Jones']);
        $lastId = $db->lastId();
        $row = $db->selectOne("SELECT * FROM users WHERE id = ?", [(int) $lastId]);

        // Assert
        $this->assertSame(1, $affected);
        $this->assertNotNull($row);
        $this->assertSame('Bob', $row['name']);
        $this->assertSame('Jones', $row['surname']);
    }

    public function testConnectWithPgSqlDsn(): void
    {
        // Arrange
        $dbhost = getenv('DB_PGSQL_HOST') ?: '127.0.0.1';
        $dbuser = getenv('DB_PGSQL_USER') ?: 'admin';
        $dbpass = getenv('DB_PGSQL_PASS') ?: 'pass';

        try {
            $dsn = "pgsql:host={$dbhost};port=5432;dbname=pdo4you";
            $db = PDO4You::connect($dsn, $dbuser, $dbpass);
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
        $db->exec("DELETE FROM users");

        $affected = $db->insert('users', ['name' => 'Charlie', 'surname' => 'Brown']);
        $lastId = $db->lastId();
        $row = $db->selectOne("SELECT * FROM users WHERE id = ?", [(int) $lastId]);

        // Assert
        $this->assertSame(1, $affected);
        $this->assertNotNull($row);
        $this->assertSame('Charlie', $row['name']);
        $this->assertSame('Brown', $row['surname']);
    }

    #[\PHPUnit\Framework\Attributes\Group('sqlsrv')]
    public function testConnectWithSqlSrvDsn(): void
    {
        // Arrange
        $dbhost = getenv('DB_SQLSRV_HOST') ?: '.\SQLEXPRESS';
        $dbuser = getenv('DB_SQLSRV_USER') ?: null;
        $dbpass = getenv('DB_SQLSRV_PASS') ?: null;

        try {
            $dsn = "sqlsrv:Server={$dbhost};Database=pdo4you;TrustServerCertificate=true";
            $db = PDO4You::connect($dsn, $dbuser, $dbpass);
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
        $db->exec("DELETE FROM users");

        $affected = $db->insert('users', ['name' => 'David', 'surname' => 'Smith']);
        $lastId = $db->lastId();
        $row = $db->selectOne("SELECT * FROM users WHERE id = ?", [(int) $lastId]);

        // Assert
        $this->assertSame(1, $affected);
        $this->assertNotEmpty($lastId);
        $this->assertNotNull($row);
        $this->assertSame('David', $row['name']);
        $this->assertSame('Smith', $row['surname']);
    }
}
