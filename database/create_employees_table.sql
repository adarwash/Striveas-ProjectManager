-- Create Employees Management table for tracking employee performance and data
USE HiveITPortal;

-- Drop the table if it already exists
IF OBJECT_ID('dbo.EmployeeManagement', 'U') IS NOT NULL
    DROP TABLE dbo.EmployeeManagement;

-- Create the EmployeeManagement table
CREATE TABLE dbo.EmployeeManagement (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    performance_rating DECIMAL(3,2) DEFAULT 0.00,
    tasks_completed INT DEFAULT 0,
    tasks_pending INT DEFAULT 0,
    last_absence_start DATE NULL,
    last_absence_end DATE NULL,
    total_absence_days INT DEFAULT 0,
    notes NVARCHAR(MAX) NULL,
    last_review_date DATE NULL,
    next_review_date DATE NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES dbo.Users(id)
);

-- Create an index on user_id for faster lookups
CREATE INDEX idx_employee_user_id ON dbo.EmployeeManagement(user_id);

-- Create table for absence records
IF OBJECT_ID('dbo.EmployeeAbsence', 'U') IS NOT NULL
    DROP TABLE dbo.EmployeeAbsence;

CREATE TABLE dbo.EmployeeAbsence (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason NVARCHAR(255) NULL,
    approved_by INT NULL,
    approved_at DATETIME NULL,
    created_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES dbo.Users(id),
    FOREIGN KEY (approved_by) REFERENCES dbo.Users(id)
);

-- Insert example records (you can remove this in production)
-- First make sure we have some users
IF EXISTS (SELECT 1 FROM dbo.Users WHERE id = 1)
BEGIN
    -- Add sample employee management records
    INSERT INTO dbo.EmployeeManagement (user_id, performance_rating, tasks_completed, 
                                         tasks_pending, notes, last_review_date, next_review_date)
    VALUES 
        (1, 4.75, 28, 5, 'Excellent performance on project X', '2023-12-15', '2024-06-15'),
        (2, 3.50, 15, 8, 'Meeting expectations but room for improvement', '2023-11-10', '2024-05-10');
        
    -- Add sample absence records
    INSERT INTO dbo.EmployeeAbsence (user_id, start_date, end_date, reason, approved_by, approved_at)
    VALUES
        (2, '2024-01-15', '2024-01-18', 'Sick leave', 1, '2024-01-14 09:30:00'),
        (2, '2024-02-20', '2024-02-24', 'Family emergency', 1, '2024-02-19 14:15:00');
END 