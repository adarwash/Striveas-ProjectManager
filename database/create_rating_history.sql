-- Create Rating History table for tracking changes in performance ratings
USE HiveITPortal;

-- Drop the table if it already exists
IF OBJECT_ID('dbo.EmployeeRatingHistory', 'U') IS NOT NULL
    DROP TABLE dbo.EmployeeRatingHistory;

-- Create the EmployeeRatingHistory table
CREATE TABLE dbo.EmployeeRatingHistory (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    old_rating DECIMAL(3,2) NULL,
    new_rating DECIMAL(3,2) NOT NULL,
    notes NVARCHAR(MAX) NULL,
    changed_by INT NOT NULL,
    changed_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES dbo.Users(id),
    FOREIGN KEY (changed_by) REFERENCES dbo.Users(id)
);

-- Create an index on user_id for faster lookups
CREATE INDEX idx_rating_history_user_id ON dbo.EmployeeRatingHistory(user_id);

-- Example insert (uncomment if you want sample data)
/*
INSERT INTO dbo.EmployeeRatingHistory (user_id, old_rating, new_rating, notes, changed_by)
VALUES 
    (2, 3.0, 3.5, 'Improved communication skills', 1),
    (2, 3.5, 4.0, 'Exceeded project goals this quarter', 1);
*/ 