<?php

declare(strict_types=1);

namespace PDO4You;

/**
 * Service to calculate pagination metadata.
 */
class Pagination
{
    private int $currentPage;
    private int $limitPerPage;
    private int $totalRecords;

    public function __construct(int $currentPage = 1, int $limitPerPage = 10)
    {
        $this->currentPage = max(1, $currentPage);
        $this->limitPerPage = max(1, $limitPerPage);
    }

    /**
     * Sets the total number of records for pagination calculations.
     */
    public function setTotalRecords(int $totalRecords): void
    {
        $this->totalRecords = $totalRecords;
    }

    /**
     * Gets the offset for the current page.
     */
    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->limitPerPage;
    }

    /**
     * Gets the limit per page.
     */
    public function getLimit(): int
    {
        return $this->limitPerPage;
    }

    /**
     * Gets the total number of pages based on total records and limit per page.
     */
    public function getTotalPages(): int
    {
        if ($this->limitPerPage <= 0) {
            return 0;
        }
        return (int) ceil($this->totalRecords / $this->limitPerPage);
    }

    /**
     * Gets the pagination metadata.
     */
    public function getMetadata(): array
    {
        return [
            'currentPage' => $this->currentPage,
            'limitPerPage' => $this->limitPerPage,
            'totalRecords' => $this->totalRecords,
            'totalPages' => $this->getTotalPages(),
            'offset' => $this->getOffset(),
        ];
    }
}
