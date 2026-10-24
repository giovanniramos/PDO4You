<?php

declare(strict_types=1);

namespace PDO4You\Tests\Unit;

use PDO4You\Pagination;
use PHPUnit\Framework\TestCase;

class PaginationUnitTest extends TestCase
{
    public function testDefaultValues(): void
    {
        // Arrange & Act
        $pagination = new Pagination();

        // Assert
        $this->assertSame(1, $pagination->getCurrentPage());
        $this->assertSame(10, $pagination->getPerPage());
        $this->assertSame(0, $pagination->getTotal());
        $this->assertSame(0, $pagination->getOffset());
        $this->assertSame(0, $pagination->getTotalPages());
        $this->assertFalse($pagination->hasPrevious());
        $this->assertFalse($pagination->hasNext());
    }

    public function testConstructorNormalizesOutOfBoundsValues(): void
    {
        // Arrange & Act
        $pagination = new Pagination(currentPage: -5, perPage: 0, total: -10);

        // Assert
        $this->assertSame(1, $pagination->getCurrentPage());
        $this->assertSame(1, $pagination->getPerPage());
        $this->assertSame(0, $pagination->getTotal());
    }

    public function testOffsetAndLimitCalculation(): void
    {
        // Arrange & Act
        $pagination = new Pagination(3, 15, 100);

        // Assert
        $this->assertSame(3, $pagination->getCurrentPage());
        $this->assertSame(15, $pagination->getPerPage());
        $this->assertSame(30, $pagination->getOffset());
        $this->assertSame(15, $pagination->getLimit());
        $this->assertSame(7, $pagination->getTotalPages());
    }

    public function testTotalPagesAndNavigation(): void
    {
        // Arrange
        $pagination = new Pagination(2, 10);

        // Act
        $pagination->setTotal(25);
        $metadata = $pagination->getMetadata();

        // Assert
        $this->assertSame(25, $pagination->getTotal());
        $this->assertSame(3, $pagination->getTotalPages());
        $this->assertTrue($pagination->hasPrevious());
        $this->assertTrue($pagination->hasNext());

        $this->assertSame([
            'currentPage' => 2,
            'perPage'     => 10,
            'total'       => 25,
            'totalPages'  => 3,
            'offset'      => 10,
            'hasPrevious' => true,
            'hasNext'     => true,
        ], $metadata);
    }

    public function testWithTotalReturnsNewImmutableInstance(): void
    {
        // Arrange
        $original = new Pagination(1, 10);

        // Act
        $updated = $original->withTotal(50);

        // Assert
        $this->assertNotSame($original, $updated);
        $this->assertSame(0, $original->getTotal());
        $this->assertSame(50, $updated->getTotal());
        $this->assertSame(5, $updated->getTotalPages());
    }
}
