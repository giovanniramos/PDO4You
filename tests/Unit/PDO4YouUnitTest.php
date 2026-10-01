<?php

declare(strict_types=1);

namespace PDO4You\Tests\Unit;

use PDO;
use PDOException;
use PDOStatement;
use PDO4You\PDO4You;
use PDO4You\Exception\QueryException;
use PDO4You\Platform\DatabasePlatform;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DummyUserDto
{
    public int $id;
    public string $name;
}

class PDO4YouUnitTest extends TestCase
{
    public function testDtoHydration(): void
    {
        // Arrange
        $pdoMock = $this->createMock(PDO::class);
        $pdoStatementMock = $this->createMock(PDOStatement::class);

        $pdoMock->method('prepare')
            ->willReturn($pdoStatementMock);

        $pdoStatementMock->method('execute')
            ->willReturn(true);

        $pdoStatementMock->method('fetchAll')
            ->with(PDO::FETCH_CLASS, DummyUserDto::class)
            ->willReturn([new DummyUserDto()]);

        $db = new PDO4You($pdoMock);

        // Act
        $results = $db->select('SELECT id, name FROM users', [], DummyUserDto::class);

        // Assert
        $this->assertCount(1, $results);
        $this->assertInstanceOf(DummyUserDto::class, $results[0]);
    }

    public function testLastIdUsesPlatformStrategy(): void
    {
        // Arrange
        $pdoMock = $this->createMock(PDO::class);
        $platformMock = $this->createMock(DatabasePlatform::class);
        $pdoStatementMock = $this->createMock(PDOStatement::class);

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

    public function testQueryObservabilityListener(): void
    {
        // Arrange
        $pdoMock = $this->createMock(PDO::class);
        $pdoStatementMock = $this->createMock(PDOStatement::class);

        $pdoMock->expects($this->once())
             ->method('prepare')
             ->willReturn($pdoStatementMock);

        $pdoStatementMock->expects($this->once())
             ->method('execute')
             ->with(['id' => 1])
             ->willReturn(true);

        $observedSql = null;
        $observedParams = null;
        $observedDuration = null;

        PDO4You::onQuery(function (string $sql, array $params, float $duration) use (&$observedSql, &$observedParams, &$observedDuration) {
            $observedSql = $sql;
            $observedParams = $params;
            $observedDuration = $duration;
        });

        $db = new PDO4You($pdoMock);

        // Act
        $db->select('SELECT * FROM users WHERE id = :id', ['id' => 1]);
        PDO4You::onQuery(null); // Reset

        // Assert
        $this->assertSame('SELECT * FROM users WHERE id = :id', $observedSql);
        $this->assertSame(['id' => 1], $observedParams);
        $this->assertIsFloat($observedDuration);
    }

    public function testQueryExceptionWrapping(): void
    {
        // Arrange
        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('prepare')->willThrowException(new PDOException('Syntax error'));

        $db = new PDO4You($pdoMock);

        // Assert (Expectations)
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Query failed: Syntax error');

        // Act
        $db->select('INVALID SQL');
    }

    public function testTransactionRollbackOnException(): void
    {
        // Arrange
        $pdoMock = $this->createMock(PDO::class);

        $pdoMock->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);

        $pdoMock->method('inTransaction')
            ->willReturn(true);

        $pdoMock->expects($this->once())
            ->method('rollBack')
            ->willReturn(true);

        $pdoMock->expects($this->never())
            ->method('commit');

        $db = new PDO4You($pdoMock);

        // Assert (Expectations)
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error inside transaction');

        // Act
        $db->transaction(function () {
            throw new RuntimeException('Error inside transaction');
        });
    }
}
