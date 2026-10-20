<?php

declare(strict_types=1);

namespace PDO4You\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PDO4You\Pagination;

class PaginationUnitTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $pagination = new Pagination();

        $this->assertSame(1, $pagination->getCurrentPage());
        $this->assertSame(10, $pagination->getPerPage());
        $this->assertSame(0, $pagination->getTotal());
        $this->assertSame(0, $pagination->getOffset());
        $this->assertSame(0, $pagination->getTotalPages());
        $this->assertFalse($pagination->hasPrevious());
        $this->assertFalse($pagination->hasNext());
    }

    public function testOffsetAndLimitCalculation(): void
    {
        $pagination = new Pagination(3, 15);

        $this->assertSame(3, $pagination->getCurrentPage());
        $this->assertSame(15, $pagination->getPerPage());
        $this->assertSame(30, $pagination->getOffset());
        $this->assertSame(15, $pagination->getLimit());
    }

    public function testTotalPagesAndNavigation(): void
    {
        $pagination = new Pagination(2, 10);
        $pagination->setTotal(25);

        $this->assertSame(25, $pagination->getTotal());
        $this->assertSame(3, $pagination->getTotalPages());
        $this->assertTrue($pagination->hasPrevious());
        $this->assertTrue($pagination->hasNext());

        $metadata = $pagination->getMetadata();
        $this->assertSame(2, $metadata['currentPage']);
        $this->assertSame(10, $metadata['perPage']);
        $this->assertSame(25, $metadata['total']);
        $this->assertSame(3, $metadata['totalPages']);
        $this->assertSame(10, $metadata['offset']);
        $this->assertTrue($metadata['hasPrevious']);
        $this->assertTrue($metadata['hasNext']);
    }

    public function testLegacySetTotalRecordsDelegatesToSetTotal(): void
    {
        // Arrange
        $pagination = new Pagination(currentPage: 1, perPage: 10);

        // Act
        $pagination->setTotalRecords(25);

        // Assert
        $this->assertSame(25, $pagination->getTotal());
        $this->assertSame(3, $pagination->getTotalPages());
    }
}
