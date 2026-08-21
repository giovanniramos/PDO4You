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
        // 1. Create a PDO mock
        $pdoMock = $this->createMock(PDO::class);

        // 2. Create Platform Mockup
        $platformMock = $this->createMock(DatabasePlatform::class);

        // 3. Set expectation: the platform must be called
        $platformMock->expects($this->once())
                     ->method('getLastInsertIdSql')
                     ->willReturn('SELECT 123');

        // 4. Configure the PDO mock to return the expected value
        $pdoStatementMock = $this->createMock(\PDOStatement::class);
        $pdoStatementMock->method('fetchColumn')->willReturn('123');
        $pdoMock->method('query')->willReturn($pdoStatementMock);

        // 5. Instantiate PDO4You with the mocks
        $db = new PDO4You($pdoMock, $platformMock);

        // 6. Call lastId and assert the result
        $this->assertEquals('123', $db->lastId());
    }
}
