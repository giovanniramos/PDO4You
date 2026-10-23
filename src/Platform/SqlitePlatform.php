<?php

declare(strict_types=1);

namespace PDO4You\Platform;

class SqlitePlatform implements DatabasePlatform
{
    public function getLastInsertIdSql(?string $sequence = null): string
    {
        return "SELECT LAST_INSERT_ROWID() AS lastId";
    }

    public function quoteIdentifierPart(string $part): string
    {
        return '"' . str_replace('"', '""', $part) . '"';
    }

    public function requiresSameBatchIdentityCapture(): bool
    {
        return false;
    }
}
