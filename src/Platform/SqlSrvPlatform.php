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
}
