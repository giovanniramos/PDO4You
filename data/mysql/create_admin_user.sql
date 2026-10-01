-- ============================================================================
-- Create Admin User and Grant Privileges (Local Development Only)
-- WARNING: Never use default or weak credentials in production environments.
-- ============================================================================

CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED BY 'pass';

CREATE DATABASE IF NOT EXISTS pdo4you CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON pdo4you.* TO 'admin'@'localhost';

FLUSH PRIVILEGES;
