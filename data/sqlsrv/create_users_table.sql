-- ============================================================================
-- Create Users Table (SQL Server)
-- ============================================================================

-- 1. Drop existing table if it exists
IF OBJECT_ID(N'dbo.users', N'U') IS NOT NULL
BEGIN
    DROP TABLE dbo.users;
END
GO

-- 2. Create users table
CREATE TABLE dbo.users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(255) NOT NULL,
    surname NVARCHAR(255) NOT NULL,
    mail NVARCHAR(255) NULL DEFAULT NULL,
    status NVARCHAR(50) NOT NULL DEFAULT N'active',
    created_at DATETIME2(0) NOT NULL DEFAULT CURRENT_TIMESTAMP
);
GO