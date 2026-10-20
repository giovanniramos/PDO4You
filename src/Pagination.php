<?php

declare(strict_types=1);

namespace PDO4You;

/**
 * Service to calculate pagination metadata.
 */
class Pagination
{
    private int $currentPage;
    private int $perPage;
    private int $total = 0;

    public function __construct(int $currentPage = 1, int $perPage = 10)
    {
        $this->currentPage = max(1, $currentPage);
        $this->perPage = max(1, $perPage);
    }

    /**
     * Sets the total number of records for pagination calculations.
     *
     * @param int $total The total number of records.
     *
     * @return void
     */
    public function setTotal(int $total): void
    {
        $this->total = max(0, $total);
    }

    /**
     * Sets the total number of records (legacy alias).
     *
     * @param int $totalRecords The total number of records.
     *
     * @return void
     *
     * @deprecated Use setTotal() instead. Will be removed in v6.0.
     */
    public function setTotalRecords(int $totalRecords): void
    {
        $this->setTotal($totalRecords);
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
        if ($this->perPage <= 0 || $this->total <= 0) {
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
     * @return array{currentPage: int, perPage: int, total: int, totalPages: int, offset: int, hasPrevious: bool, hasNext: bool}
     */
    public function getMetadata(): array
    {
        return [
            'currentPage'  => $this->currentPage,
            'perPage'      => $this->perPage,
            'total'        => $this->total,
            'totalPages'   => $this->getTotalPages(),
            'offset'       => $this->getOffset(),
            'hasPrevious'  => $this->hasPrevious(),
            'hasNext'      => $this->hasNext(),
        ];
    }
}
