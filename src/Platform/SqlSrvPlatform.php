<?php

declare(strict_types=1);

namespace PDO4You\Platform;

class SqlSrvPlatform implements DatabasePlatform
{
    public function getLastInsertIdSql(?string $sequence = null): string
    {
        return "SELECT CAST(SCOPE_IDENTITY() AS BIGINT) AS lastId";
    }

    public function quoteIdentifierPart(string $part): string
    {
        return '[' . str_replace(']', ']]', $part) . ']';
    }

    public function requiresSameBatchIdentityCapture(): bool
    {
        return true;
    }

    public function getSavepointSql(string $savepoint): string
    {
        return "SAVE TRANSACTION {$savepoint}";
    }

    public function getReleaseSavepointSql(string $savepoint): ?string
    {
        // SQL Server does not support or require an explicit RELEASE SAVEPOINT statement.
        return null;
    }

    public function getRollbackSavepointSql(string $savepoint): string
    {
        return "ROLLBACK TRANSACTION {$savepoint}";
    }
}
