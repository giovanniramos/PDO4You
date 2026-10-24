<?php

declare(strict_types=1);

namespace PDO4You\Exception;

use PDOException;
use SensitiveParameter;
use Throwable;

/**
 * Represents an error raised during SQL statement preparation or execution.
 *
 * Preserves the executed SQL, bound parameters, ANSI SQLSTATE code,
 * and driver-specific error code for structured error handling and debugging.
 *
 * @package PDO4You\Exception
 * @version 6.0.0
 */
class QueryException extends PDO4YouException
{
    /** @var string|null The ANSI SQLSTATE error code (e.g., '23000', '42S02'). */
    private readonly ?string $sqlState;

    /** @var int|null The driver-specific error code (e.g., MySQL 1062). */
    private readonly ?int $driverCode;

    /**
     * Initializes a new QueryException instance.
     *
     * @param string $message The exception message.
     * @param string $sql The SQL query that triggered the exception.
     * @param array<string|int, mixed> $params The bound parameters used in the query.
     * @param int|string $code The exception or SQLSTATE code.
     * @param Throwable|null $previous The previous throwable used for exception chaining.
     */
    public function __construct(
        string $message,
        private readonly string $sql,
        #[SensitiveParameter]
        private readonly array $params = [],
        int|string $code = 0,
        ?Throwable $previous = null
    ) {
        $sqlState = null;
        $driverCode = null;

        if ($previous instanceof PDOException) {
            $errorInfo = $previous->errorInfo;
            $sqlState = is_array($errorInfo) && isset($errorInfo[0])
                ? (string) $errorInfo[0]
                : (string) $previous->getCode();

            if (is_array($errorInfo) && isset($errorInfo[1]) && is_numeric($errorInfo[1])) {
                $driverCode = (int) $errorInfo[1];
            }
        } elseif (is_string($code) && $code !== '') {
            $sqlState = $code;
        }

        $this->sqlState = ($sqlState !== null && $sqlState !== '' && $sqlState !== '0')
            ? $sqlState
            : null;

        $this->driverCode = $driverCode;

        $numericCode = is_int($code)
            ? $code
            : ($driverCode ?? (is_numeric($code) ? (int) $code : 0));

        parent::__construct($message, $numericCode, $previous);
    }

    /**
     * Gets the SQL query that triggered the exception.
     *
     * @return string The raw or prepared SQL query string.
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * Gets the parameters bound to the executed SQL query.
     *
     * @return array<string|int, mixed> The associative or indexed list of bound query parameters.
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Gets the ANSI SQLSTATE error code associated with the failed query.
     *
     * @return string|null The 5-character SQLSTATE code (e.g., '23000', '42S02') or null if unavailable.
     */
    public function getSqlState(): ?string
    {
        return $this->sqlState;
    }

    /**
     * Gets the driver-specific numeric error code, if available.
     *
     * @return int|null The driver error code (e.g., 1062 for MySQL duplicate entry) or null.
     */
    public function getDriverCode(): ?int
    {
        return $this->driverCode;
    }
}
