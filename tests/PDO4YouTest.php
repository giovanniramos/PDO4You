<?php

declare(strict_types=1);

namespace PDO4You\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use PDO4You\PDO4You;
use PDO4You\Platform\DatabasePlatform;

class PDO4YouTest extends TestCase
{
    public function testLastIdUsesPlatformStrategy(): void
    {
        // Arrange
        $pdoMock = $this->createMock(PDO::class);
        $platformMock = $this->createMock(DatabasePlatform::class);
        $pdoStatementMock = $this->createMock(\PDOStatement::class);

        $platformMock->expects($this->once())
            ->method('getLastInsertIdSql')
            ->with(null)
            ->willReturn('SELECT 123');

        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with('SELECT 123')
            ->willReturn($pdoStatementMock);

        $pdoStatementMock->expects($this->once())
            ->method('execute')
            ->with([])
            ->willReturn(true);

        $pdoStatementMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('123');

        $db = new PDO4You($pdoMock, $platformMock);

        // Act
        $result = $db->lastId();

        // Assert
        $this->assertSame('123', $result);
    }

    public function testConnectWithSqliteDsn(): void
    {
        // Arrange
        $db = PDO4You::connect('sqlite::memory:');
        // Act
        $db->exec("CREATE TABLE test_table (id INTEGER PRIMARY KEY, name TEXT)");

        $affected = $db->exec(
            "INSERT INTO test_table (name) VALUES (?)",
            ['Alice']
        );

        $rows = $db->select("SELECT * FROM test_table");

        $this->assertInstanceOf(PDO4You::class, $db);

        $this->assertEquals(1, $affected);

        $rows = $db->select("SELECT * FROM test_table");
        $this->assertCount(1, $rows);
        $this->assertEquals('Alice', $rows[0]['name']);
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

        $affected = $db->exec(
            "INSERT INTO users (name, surname) VALUES (?, ?)",
            ['John', 'Doe']
        );

        $rows = $db->select(
            "SELECT * FROM users WHERE name = ?",
            ['John']
        );

        // Assert
        $this->assertInstanceOf(PDO4You::class, $db);
        $this->assertSame(1, $affected);
        $this->assertNotEmpty($rows);
        $this->assertSame('John', $rows[0]['name']);
        $this->assertSame('Doe', $rows[0]['surname']);
    }

    public function testConnectWithUnsupportedDsnThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PDO4You::connect('unsupported_driver:memory:');
    }
}
