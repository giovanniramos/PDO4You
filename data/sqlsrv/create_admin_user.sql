-- ============================================================================
-- Create Database, Login, User, and Grant Privileges (Local Development Only)
-- WARNING: Never use default or weak credentials in production environments.
-- ============================================================================

-- 1. Create database if it does not already exist
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = N'pdo4you')
BEGIN
    CREATE DATABASE [pdo4you] COLLATE Latin1_General_100_CI_AS_SC_UTF8;
END
GO

-- 2. Create server-level login with the specified password
IF NOT EXISTS (SELECT name FROM sys.server_principals WHERE name = N'admin')
BEGIN
    CREATE LOGIN [admin] WITH PASSWORD = N'pass', CHECK_POLICY = OFF;
END
GO

-- 3. Switch to database context and map the user to the server login
USE [pdo4you];
GO

IF NOT EXISTS (SELECT name FROM sys.database_principals WHERE name = N'admin')
BEGIN
    CREATE USER [admin] FOR LOGIN [admin];
END
GO

-- 4. Grant full privileges within the database (equivalent to GRANT ALL)
ALTER ROLE [db_owner] ADD MEMBER [admin];
GO