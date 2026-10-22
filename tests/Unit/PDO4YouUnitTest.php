<?php

declare(strict_types=1);

namespace PDO4You\Tests\Unit;

use InvalidArgumentException;
use PDO;
use PDOException;
use PDOStatement;
use PDO4You\PDO4You;
use PDO4You\Exception\QueryException;
use PDO4You\Platform\DatabasePlatform;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

class DummyUserDto
{
    public int $id;
    public string $name;
}

class PDO4YouUnitTest extends TestCase
{
    /** @var PDO&MockObject */
    private PDO $pdoMock;
    private PDO4You $db;

    protected function setUp(): void
    {
        // Mocking the core PDO extension
        $this->pdoMock = $this->createMock(PDO::class);

        // Set the default driver for MySQL on Mac (using quoted phrases)
        $this->pdoMock->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn('mysql');

        $this->db = new PDO4You($this->pdoMock);
    }

    /**
     * Helper for creating mocks of executed statements with default expectations.
     */
    private function createExecutedStatementMock(int $rowCount = 1): MockObject&PDOStatement
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('rowCount')->willReturn($rowCount);

        return $stmtMock;
    }

    public function testDtoHydration(): void
    {
        // Arrange
        $stmtMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->method('execute')
            ->willReturn(true);

        $stmtMock->method('fetchAll')
            ->with(PDO::FETCH_CLASS, DummyUserDto::class)
            ->willReturn([new DummyUserDto()]);

        // Act
        $results = $this->db->select('SELECT id, name FROM users', [], DummyUserDto::class);

        // Assert
        $this->assertCount(1, $results);
        $this->assertInstanceOf(DummyUserDto::class, $results[0]);
    }

    public function testLastIdUsesPlatformStrategy(): void
    {
        // Arrange
        $stmtMock = $this->createMock(PDOStatement::class);
        $platformMock = $this->createMock(DatabasePlatform::class);

        $platformMock->expects($this->once())
            ->method('getLastInsertIdSql')
            ->with(null)
            ->willReturn('SELECT 123');

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('SELECT 123')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->with([])
            ->willReturn(true);

        $stmtMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('123');

        $db = new PDO4You($this->pdoMock, $platformMock);

        // Act
        $result = $db->lastId();

        // Assert
        $this->assertSame('123', $result);
    }

    public function testQueryObservabilityListener(): void
    {
        // Arrange
        $stmtMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->with(['id' => 1])
            ->willReturn(true);

        $observedSql = null;
        $observedParams = null;
        $observedDuration = null;

        PDO4You::onQuery(
            function (
                string $sql,
                array $params,
                float $duration
            ) use (
                &$observedSql,
                &$observedParams,
                &$observedDuration
            ): void {
                $observedSql = $sql;
                $observedParams = $params;
                $observedDuration = $duration;
            }
        );

        try {
            // Act
            $this->db->select('SELECT * FROM users WHERE id = :id', ['id' => 1]);
        } finally {
            // Always reset the static listener after the test.
            PDO4You::onQuery(null);
        }

        // Assert
        $this->assertSame('SELECT * FROM users WHERE id = :id', $observedSql);
        $this->assertSame(['id' => 1], $observedParams);
        $this->assertIsFloat($observedDuration);
    }

    public function testQueryExceptionWrapping(): void
    {
        // Arrange
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Syntax error'));

        // Assert (Expectations)
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Query preparation failed: Syntax error');

        // Act
        $this->db->select('INVALID SQL');
    }

    /**
     * @throws Throwable
     */
    public function testTransactionRollbackOnException(): void
    {
        // Arrange
        $this->pdoMock->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);

        $this->pdoMock->method('inTransaction')
            ->willReturn(true);

        $this->pdoMock->expects($this->once())
            ->method('rollBack')
            ->willReturn(true);

        $this->pdoMock->expects($this->never())
            ->method('commit');

        // Assert (Expectations)
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error inside transaction');

        // Act
        $this->db->transaction(function () {
            throw new RuntimeException('Error inside transaction');
        });
    }

    public function testInsertHelperQuotesIdentifiers(): void
    {
        // Arrange
        $stmtMock = $this->createExecutedStatementMock(1);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO `users` (`name`, `surname`) VALUES (:_pdo4you_ins_0_name, :_pdo4you_ins_1_surname)')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->with([
                '_pdo4you_ins_0_name' => 'John',
                '_pdo4you_ins_1_surname' => 'Doe',
            ])
            ->willReturn(true);

        // Act
        $affected = $this->db->insert('users', ['name' => 'John', 'surname' => 'Doe']);

        // Assert
        $this->assertSame(1, $affected);
    }

    public function testInsertWithEmptyDataReturnsZero(): void
    {
        $this->pdoMock->expects($this->never())->method('prepare');

        $affected = $this->db->insert('users', []);

        $this->assertSame(0, $affected);
    }

    public function testUpdateHelperQuotesIdentifiers(): void
    {
        // Arrange
        $stmtMock = $this->createExecutedStatementMock(1);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('UPDATE `users` SET `name` = :_pdo4you_upd_0_name WHERE id = :id')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->with([
                '_pdo4you_upd_0_name' => 'Jane',
                'id' => 1,
            ])
            ->willReturn(true);

        // Act
        $affected = $this->db->update('users', ['name' => 'Jane'], 'id = :id', ['id' => 1]);

        // Assert
        $this->assertSame(1, $affected);
    }

    public function testUpdateSupportsPositionalWhereParameters(): void
    {
        // Arrange
        $stmtMock = $this->createExecutedStatementMock(1);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('UPDATE `users` SET `name` = ? WHERE id = ?')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->with(['Jane', 1])
            ->willReturn(true);

        // Act
        $affected = $this->db->update('users', ['name' => 'Jane'], 'id = ?', [1]);

        // Assert
        $this->assertSame(1, $affected);
    }

    public function testUpdateWithEmptyDataReturnsZero(): void
    {
        $this->pdoMock->expects($this->never())->method('prepare');

        $affected = $this->db->update('users', [], 'id = :id', ['id' => 1]);

        $this->assertSame(0, $affected);
    }

    public function testDeleteHelperQuotesIdentifiers(): void
    {
        // Arrange
        $stmtMock = $this->createExecutedStatementMock(1);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM `users` WHERE id = :id')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->with(['id' => 1])
            ->willReturn(true);

        // Act
        $affected = $this->db->delete('users', 'id = :id', ['id' => 1]);

        // Assert
        $this->assertSame(1, $affected);
    }

    public function testQualifiedTableNamesSupportDotNotation(): void
    {
        // Arrange (ex: schema.tabela -> `tenant_db`.`users`)
        $stmtMock = $this->createExecutedStatementMock(1);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM `tenant_db`.`users` WHERE id = :id')
            ->willReturn($stmtMock);

        // Act
        $affected = $this->db->delete('tenant_db.users', 'id = :id', ['id' => 1]);

        // Assert
        $this->assertSame(1, $affected);
    }

    public function testInvalidTableNameThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SQL identifier provided: "users; DROP TABLE users"');

        $this->db->delete('users; DROP TABLE users', 'id = 1');
    }

    public function testInvalidColumnNameThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SQL identifier provided: "name` = 1 --"');

        $this->db->update('users', ['name` = 1 --' => 'val'], 'id = 1');
    }

    public function testSqlSrvPlatformLastInsertIdSql(): void
    {
        $platform = new \PDO4You\Platform\SqlSrvPlatform();
        $this->assertSame('SELECT CAST(SCOPE_IDENTITY() AS BIGINT) AS lastId', $platform->getLastInsertIdSql());
    }

    public function testQuoteIdentifierForSqlSrvDriver(): void
    {
        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn('sqlsrv');

        $db = new PDO4You($pdoMock);

        $stmtMock = $this->createExecutedStatementMock(1);

        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM [dbo].[users] WHERE id = :id')
            ->willReturn($stmtMock);

        $affected = $db->delete('dbo.users', 'id = :id', ['id' => 1]);
        $this->assertSame(1, $affected);
    }
}
