<?php

declare(strict_types=1);

namespace PDO4You\Platform;

use InvalidArgumentException;

/**
 * Interface for database platform-specific implementations.
 */
interface DatabasePlatform
{
    /**
     * Returns the correct SQL query to retrieve the last inserted ID.
     *
     * @param string|null $sequence Optional sequence name.
     *
     * @return string
     *
     * @throws InvalidArgumentException If the sequence identifier is invalid.
     */
    public function getLastInsertIdSql(?string $sequence = null): string;

    /**
     * Quotes a single SQL identifier token (e.g., table or column name)
     * according to the platform's dialect.
     *
     * @param string $part A validated identifier token.
     *
     * @return string
     */
    public function quoteIdentifierPart(string $part): string;

    /**
     * Indicates whether the platform requires capturing the generated identity
     * within the exact same SQL batch as the INSERT statement.
     *
     * @return bool
     */
    public function requiresSameBatchIdentityCapture(): bool;
}
