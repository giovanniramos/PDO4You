<?php

declare(strict_types=1);

namespace PDO4You\Platform;

class PgSqlPlatform implements DatabasePlatform
{
    public function getLastInsertIdSql(?string $sequence = null): string
    {
        $seq = $sequence ? "currval('{$sequence}')" : "lastval()";
        return "SELECT {$seq} AS lastId";
    }
}
