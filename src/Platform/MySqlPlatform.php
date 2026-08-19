<?php

declare(strict_types=1);

namespace PDO4You\Platform;

class MySqlPlatform implements DatabasePlatform
{
    public function getLastInsertIdSql(?string $sequence = null): string
    {
        return "SELECT LAST_INSERT_ID() AS lastId";
    }
}
