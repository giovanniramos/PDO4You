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
use PDO4You\Platform\PgSqlPlatform;
use PDO4You\Platform\SqlSrvPlatform;
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
        $this->pdoMock = $this->createMock(PDO::class);

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

    public function testCursorYieldsRowsLazilyAndClosesCursor(): void
    {
        // Arrange
        $stmtMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('SELECT id, name FROM users')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $stmtMock->expects($this->exactly(3))
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturnOnConsecutiveCalls(
                ['id' => 1, 'name' => 'Alice'],
                ['id' => 2, 'name' => 'Bob'],
                false
            );

        $stmtMock->expects($this->once())
            ->method('closeCursor');

        // Act
        $generator = $this->db->cursor('SELECT id, name FROM users');
        $results = iterator_to_array($generator);

        // Assert
        $this->assertCount(2, $results);
        $this->assertSame('Alice', $results[0]['name']);
        $this->assertSame('Bob', $results[1]['name']);
    }

    public function testLastIdUsesPlatformStrategy(): void
    {
        // Arrange
        $stmtMock = $this->createMock(PDOStatement::class);
        $platformMock = $this->createMock(DatabasePlatform::class);

        $platformMock->method('requiresSameBatchIdentityCapture')
            ->willReturn(false);

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
            ->willReturn(true);

        $stmtMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('123');

        $stmtMock->expects($this->once())
            ->method('closeCursor');

        $db = new PDO4You($this->pdoMock, $platformMock);

        // Act
        $result = $db->lastId();

        // Assert
        $this->assertSame('123', $result);
    }

    public function testQueryObservabilityGlobalAndInstanceListeners(): void
    {
        // Arrange
        $stmtMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bindValue')
            ->with(':id', 1, PDO::PARAM_INT);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $globalObservedSql = null;
        $instanceObservedSql = null;
        $observedParams = null;
        $observedDuration = null;

        PDO4You::onQuery(
            function (string $sql) use (&$globalObservedSql): void {
                $globalObservedSql = $sql;
            }
        );

        $this->db->addQueryListener(
            function (
                string $sql,
                array $params,
                float $duration
            ) use (
                &$instanceObservedSql,
                &$observedParams,
                &$observedDuration
            ): void {
                $instanceObservedSql = $sql;
                $observedParams = $params;
                $observedDuration = $duration;
            }
        );

        try {
            // Act
            $this->db->select('SELECT * FROM users WHERE id = :id', ['id' => 1]);
        } finally {
            PDO4You::onQuery(null);
            $this->db->clearQueryListeners();
        }

        // Assert
        $this->assertSame('SELECT * FROM users WHERE id = :id', $globalObservedSql);
        $this->assertSame('SELECT * FROM users WHERE id = :id', $instanceObservedSql);
        $this->assertSame(['id' => 1], $observedParams);
        $this->assertIsFloat($observedDuration);
    }

    public function testQueryExceptionWrappingOnPrepare(): void
    {
        // Arrange
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willThrowException(new PDOException('Syntax error'));

        // Assert
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('Query preparation failed: Syntax error');

        // Act
        $this->db->select('INVALID SQL');
    }

    public function testQueryExceptionPreservesSqlStateAndDriverCodeOnExecute(): void
    {
        // Arrange
        $stmtMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $pdoException = new PDOException('Duplicate entry', 23000);
        $pdoException->errorInfo = ['23000', 1062, "Duplicate entry 'john@example.com' for key 'email'"];

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willThrowException($pdoException);

        // Act & Assert
        try {
            $this->db->exec('INSERT INTO users (email) VALUES (?)', ['john@example.com']);
            $this->fail('Expected QueryException was not thrown.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('Query execution failed: Duplicate entry', $e->getMessage());
            $this->assertSame('INSERT INTO users (email) VALUES (?)', $e->getSql());
            $this->assertSame(['john@example.com'], $e->getParams());
            $this->assertSame('23000', $e->getSqlState());
            $this->assertSame(1062, $e->getDriverCode());
        }
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
            ->willReturnOnConsecutiveCalls(false, true);

        $this->pdoMock->expects($this->once())
            ->method('rollBack')
            ->willReturn(true);

        $this->pdoMock->expects($this->never())
            ->method('commit');

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error inside transaction');

        // Act
        $this->db->transaction(function () {
            throw new RuntimeException('Error inside transaction');
        });
    }

    /**
     * @throws Throwable
     */
    public function testNestedTransactionsUseSavepoints(): void
    {
        // Arrange
        $this->pdoMock->expects($this->once())
            ->method('beginTransaction')
            ->willReturn(true);

        $this->pdoMock->method('inTransaction')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->pdoMock->expects($this->exactly(2))
            ->method('exec')
            ->willReturnCallback(function (string $sql): int {
                static $calls = 0;
                $calls++;
                if ($calls === 1) {
                    $this->assertSame('SAVEPOINT pdo4you_sp_2', $sql);
                } elseif ($calls === 2) {
                    $this->assertSame('RELEASE SAVEPOINT pdo4you_sp_2', $sql);
                }
                return 0;
            });

        $this->pdoMock->expects($this->once())
            ->method('commit')
            ->willReturn(true);

        // Act
        $result = $this->db->transaction(function (PDO4You $conn): string {
            return $conn->transaction(fn (): string => 'committed');
        });

        // Assert
        $this->assertSame('committed', $result);
    }

    public function testInsertHelperQuotesIdentifiersAndBindsValues(): void
    {
        // Arrange
        $stmtMock = $this->createExecutedStatementMock(1);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO `users` (`name`, `surname`) VALUES (:_pdo4you_ins_0_name, :_pdo4you_ins_1_surname)')
            ->willReturn($stmtMock);

        $bound = [];
        $stmtMock->expects($this->exactly(2))
            ->method('bindValue')
            ->willReturnCallback(function (string|int $param, mixed $val, int $type) use (&$bound): bool {
                $bound[$param] = [$val, $type];
                return true;
            });

        // Act
        $affected = $this->db->insert('users', ['name' => 'John', 'surname' => 'Doe']);

        // Assert
        $this->assertSame(1, $affected);
        $this->assertSame([
            ':_pdo4you_ins_0_name' => ['John', PDO::PARAM_STR],
            ':_pdo4you_ins_1_surname' => ['Doe', PDO::PARAM_STR],
        ], $bound);
    }

    public function testInsertWithEmptyDataReturnsZero(): void
    {
        $this->pdoMock->expects($this->never())->method('prepare');

        $affected = $this->db->insert('users', []);

        $this->assertSame(0, $affected);
    }

    public function testUpdateHelperQuotesIdentifiersAndBindsTypedValues(): void
    {
        // Arrange
        $stmtMock = $this->createExecutedStatementMock(1);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('UPDATE `users` SET `name` = :_pdo4you_upd_0_name WHERE id = :id')
            ->willReturn($stmtMock);

        $bound = [];
        $stmtMock->expects($this->exactly(2))
            ->method('bindValue')
            ->willReturnCallback(function (string|int $param, mixed $val, int $type) use (&$bound): bool {
                $bound[$param] = [$val, $type];
                return true;
            });

        // Act
        $affected = $this->db->update('users', ['name' => 'Jane'], 'id = :id', ['id' => 1]);

        // Assert
        $this->assertSame(1, $affected);
        $this->assertSame([
            ':_pdo4you_upd_0_name' => ['Jane', PDO::PARAM_STR],
            ':id' => [1, PDO::PARAM_INT],
        ], $bound);
    }

    public function testUpdateSupportsPositionalWhereParameters(): void
    {
        // Arrange
        $stmtMock = $this->createExecutedStatementMock(1);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('UPDATE `users` SET `name` = ? WHERE id = ?')
            ->willReturn($stmtMock);

        $bound = [];
        $stmtMock->expects($this->exactly(2))
            ->method('bindValue')
            ->willReturnCallback(function (string|int $param, mixed $val, int $type) use (&$bound): bool {
                $bound[$param] = [$val, $type];
                return true;
            });

        // Act
        $affected = $this->db->update('users', ['name' => 'Jane'], 'id = ?', [1]);

        // Assert
        $this->assertSame(1, $affected);
        $this->assertSame([
            1 => ['Jane', PDO::PARAM_STR],
            2 => [1, PDO::PARAM_INT],
        ], $bound);
    }

    public function testUpdateWithEmptyWhereClauseThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The WHERE clause cannot be empty for update operations.');

        $this->db->update('users', ['name' => 'Jane'], '   ');
    }

    public function testDeleteWithEmptyWhereClauseThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The WHERE clause cannot be empty for delete operations.');

        $this->db->delete('users', '');
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
            ->method('bindValue')
            ->with(':id', 1, PDO::PARAM_INT);

        // Act
        $affected = $this->db->delete('users', 'id = :id', ['id' => 1]);

        // Assert
        $this->assertSame(1, $affected);
    }

    public function testQualifiedTableNamesSupportDotNotation(): void
    {
        // Arrange
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

    public function testPgSqlPlatformRejectsMaliciousSequenceName(): void
    {
        $platform = new PgSqlPlatform();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid sequence identifier provided:');

        $platform->getLastInsertIdSql("users_id_seq'); DROP TABLE users; --");
    }

    public function testSqlSrvPlatformLastInsertIdSql(): void
    {
        $platform = new SqlSrvPlatform();
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

    public function testSqlSrvInsertCapturesIdentityInSameBatchAndPreservesRowCount(): void
    {
        // Arrange
        $pdoMock = $this->createMock(PDO::class);
        $pdoMock->method('getAttribute')
            ->with(PDO::ATTR_DRIVER_NAME)
            ->willReturn('sqlsrv');

        $stmtMock = $this->createMock(PDOStatement::class);

        $expectedSql = "INSERT INTO [users] ([name]) VALUES (:_pdo4you_ins_0_name);\n"
            . "SELECT CAST(SCOPE_IDENTITY() AS BIGINT) AS lastId -- __pdo4you_last_insert_id";

        $pdoMock->expects($this->once())
            ->method('prepare')
            ->with($expectedSql)
            ->willReturn($stmtMock);

        $stmtMock->expects($this->once())
            ->method('bindValue')
            ->with(':_pdo4you_ins_0_name', 'David', PDO::PARAM_STR);

        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $stmtMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $stmtMock->expects($this->exactly(2))
            ->method('columnCount')
            ->willReturnOnConsecutiveCalls(0, 1);

        $stmtMock->expects($this->exactly(2))
            ->method('nextRowset')
            ->willReturnOnConsecutiveCalls(true, false);

        $stmtMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('42');

        $db = new PDO4You($pdoMock);

        // Act
        $affected = $db->insert('users', ['name' => 'David']);
        $lastId = $db->lastId();

        // Assert
        $this->assertSame(1, $affected);
        $this->assertSame('42', $lastId);
    }
}
