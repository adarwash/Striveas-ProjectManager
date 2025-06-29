-- Create Users table for authentication
USE HiveITPortal;

-- Drop the table if it already exists
IF OBJECT_ID('dbo.Users', 'U') IS NOT NULL
    DROP TABLE dbo.Users;

-- Create the Users table
CREATE TABLE dbo.Users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    username NVARCHAR(50) NOT NULL UNIQUE,
    password NVARCHAR(255) NOT NULL,
    email NVARCHAR(100) NULL,
    full_name NVARCHAR(100) NULL,
    role NVARCHAR(20) DEFAULT 'user',
    is_active BIT DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE(),
    last_login DATETIME NULL
);

-- Create an index on username for faster lookups
CREATE INDEX idx_username ON dbo.Users(username);

-- Insert a test/admin user
-- Note: In a production environment, you should use proper password hashing
INSERT INTO dbo.Users (username, password, email, full_name, role)
VALUES 
    ('admin', 'admin123', 'admin@example.com', 'System Administrator', 'admin'),
    ('test', 'test123', 'test@example.com', 'Test User', 'user');

-- Display the created users
SELECT * FROM dbo.Users; 