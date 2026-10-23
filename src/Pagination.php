<?php

declare(strict_types=1);

namespace PDO4You;

/**
 * Service to calculate pagination metadata.
 *
 * @package PDO4You
 * @version 6.0.0
 */
class Pagination
{
    public readonly int $currentPage;
    public readonly int $perPage;
    private int $total;

    /**
     * Initializes the Pagination calculator.
     *
     * @param int $currentPage Current page number (1-indexed).
     * @param int $perPage Number of items per page.
     * @param int $total Total number of records available.
     */
    public function __construct(
        int $currentPage = 1,
        int $perPage = 10,
        int $total = 0
    ) {
        $this->currentPage = max(1, $currentPage);
        $this->perPage = max(1, $perPage);
        $this->total = max(0, $total);
    }

    /**
     * Returns a new Pagination instance with the specified total record count.
     *
     * @param int $total The total number of records.
     *
     * @return self
     */
    public function withTotal(int $total): self
    {
        return new self($this->currentPage, $this->perPage, $total);
    }

    /**
     * Sets the total number of records for pagination calculations.
     *
     * @param int $total The total number of records.
     *
     * @return self
     */
    public function setTotal(int $total): self
    {
        $this->total = max(0, $total);

        return $this;
    }

    /**
     * Gets the current page number.
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Gets the number of items per page.
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Gets the total number of records.
     *
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * Gets the offset for the current page.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->perPage;
    }

    /**
     * Gets the limit for database queries.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->perPage;
    }

    /**
     * Gets the total number of pages based on total records and items per page.
     *
     * @return int
     */
    public function getTotalPages(): int
    {
        if ($this->total === 0) {
            return 0;
        }

        return (int) ceil($this->total / $this->perPage);
    }

    /**
     * Checks if there is a previous page.
     *
     * @return bool
     */
    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Checks if there is a next page.
     *
     * @return bool
     */
    public function hasNext(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }

    /**
     * Gets comprehensive pagination metadata.
     *
     * @return array{
     *     currentPage: int,
     *     perPage: int,
     *     total: int,
     *     totalPages: int,
     *     offset: int,
     *     hasPrevious: bool,
     *     hasNext: bool
     * }
     */
    public function getMetadata(): array
    {
        return [
            'currentPage' => $this->currentPage,
            'perPage'     => $this->perPage,
            'total'       => $this->total,
            'totalPages'  => $this->getTotalPages(),
            'offset'      => $this->getOffset(),
            'hasPrevious' => $this->hasPrevious(),
            'hasNext'     => $this->hasNext(),
        ];
    }
}
