-- ============================================================================
-- Complete Reset Script (Local Dev)
-- Execution: psql -U postgres -f setup.sql
-- ============================================================================

-- 1. Ensure connection to default postgres database
\c postgres

-- 2. Terminate existing connections to pdo4you database
SELECT pg_terminate_backend(pid)
FROM pg_stat_activity
WHERE datname = 'pdo4you'
  AND pid <> pg_backend_pid();

-- 3. Clean up global dependencies and residual admin privileges
DO $$
BEGIN
   IF EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'admin') THEN
      EXECUTE 'REVOKE ALL ON SCHEMA public FROM admin';
      EXECUTE 'DROP OWNED BY admin CASCADE';
   END IF;
END
$$;

-- 4. Recreate database and user
DROP DATABASE IF EXISTS pdo4you;
DROP ROLE IF EXISTS admin;

CREATE ROLE admin WITH LOGIN PASSWORD 'pass';
CREATE DATABASE pdo4you WITH OWNER = admin;
GRANT ALL PRIVILEGES ON DATABASE pdo4you TO admin;

-- 5. Connect directly to the newly created database
\c pdo4you

-- 6. Configure public schema permissions
GRANT ALL ON SCHEMA public TO admin;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO admin;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO admin;

-- 7. Create users table
DROP TABLE IF EXISTS users CASCADE;

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    mail VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE users OWNER TO admin;
GRANT ALL PRIVILEGES ON TABLE users TO admin;
