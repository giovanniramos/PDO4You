<?php

declare(strict_types=1);

namespace PDO4You\Exception;

class QueryException extends PDO4YouException
{
    /**
     * Initializes a new QueryException instance.
     *
     * @param string $message The exception message.
     * @param string $sql The SQL query that triggered the exception.
     * @param array<string|int, mixed> $params The bound parameters used in the query.
     * @param int $code The exception code.
     * @param \Throwable|null $previous The previous throwable used for exception chaining.
     */
    public function __construct(
        string $message,
        private readonly string $sql,
        private readonly array $params = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
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
}
