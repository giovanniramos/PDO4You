<?php

declare(strict_types=1);

namespace PDO4You\Platform;

use InvalidArgumentException;

class PgSqlPlatform implements DatabasePlatform
{
    public function getLastInsertIdSql(?string $sequence = null): string
    {
        if ($sequence !== null && $sequence !== '') {
            if (!preg_match('/^[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)?$/', $sequence)) {
                throw new InvalidArgumentException(
                    sprintf('Invalid sequence identifier provided: "%s"', $sequence)
                );
            }

            return "SELECT currval('{$sequence}') AS lastId";
        }

        return "SELECT lastval() AS lastId";
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
