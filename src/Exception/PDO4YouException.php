<?php

declare(strict_types=1);

namespace PDO4You\Exception;

use RuntimeException;
use Throwable;

/**
 * Base exception for all runtime errors thrown by PDO4You.
 *
 * @package PDO4You\Exception
 * @version 6.0.0
 */
class PDO4YouException extends RuntimeException implements Throwable
{
}
