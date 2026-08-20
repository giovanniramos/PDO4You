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
        // 1. Criar Mock do PDO
        $pdoMock = $this->createMock(PDO::class);

        // 2. Criar Mock da Plataforma
        $platformMock = $this->createMock(DatabasePlatform::class);

        // 3. Configurar expectativa: a plataforma deve ser chamada
        $platformMock->expects($this->once())
                     ->method('getLastInsertIdSql')
                     ->willReturn('SELECT 123');

        // 4. Configurar mock do PDO para retornar o valor esperado
        $pdoStatementMock = $this->createMock(\PDOStatement::class);
        $pdoStatementMock->method('fetchColumn')->willReturn('123');
        $pdoMock->method('query')->willReturn($pdoStatementMock);

        // 5. Instanciar a classe
        $db = new PDO4You($pdoMock, $platformMock);

        // 6. Executar e validar
        $this->assertEquals('123', $db->lastId());
    }
}
