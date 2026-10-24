-- ============================================================================
-- Create Database, User, and Grant Privileges (Local Development Only)
-- WARNING: Never use default or weak credentials in production environments.
-- ============================================================================

-- 1. Create database if it does not already exist
CREATE DATABASE IF NOT EXISTS pdo4you
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- 2. Create admin user with access restricted to localhost
CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED BY 'pass';

-- 3. Grant all privileges on the database to the admin user
GRANT ALL PRIVILEGES ON pdo4you.* TO 'admin'@'localhost';

-- 4. Reload grant tables to apply privileges immediately
FLUSH PRIVILEGES;