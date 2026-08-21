<?php

declare(strict_types=1);

namespace PDO4You\Platform;

/**
 * Interface for database platform-specific implementations.
 */
interface DatabasePlatform
{
    /**
     * Returns the correct SQL query to retrieve the last inserted ID.
     */
    public function getLastInsertIdSql(?string $sequence = null): string;
}
