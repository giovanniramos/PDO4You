<?php

declare(strict_types=1);

namespace PDO4You\Platform;

class PgSqlPlatform implements DatabasePlatform
{
    public function getLastInsertIdSql(?string $sequence = null): string
    {
        if ($sequence !== null) {
            if (!preg_match('/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)?$/', $sequence)) {
                throw new \InvalidArgumentException(sprintf('Invalid sequence identifier: "%s"', $sequence));
            }
            return "SELECT currval('{$sequence}') AS lastId";
        }

        return "SELECT lastval() AS lastId";
    }
}
