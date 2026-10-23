-- ============================================================================
-- Create Users Table (MySQL / MariaDB)
-- ============================================================================

-- 1. Drop existing table if it exists
DROP TABLE IF EXISTS `users`;

-- 2. Create users table
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `surname` VARCHAR(255) NOT NULL,
    `mail` VARCHAR(255) DEFAULT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;