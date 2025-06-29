-- Create Time Tracking tables
USE ProjectTracker;

-- Drop tables if they exist (in correct order to handle foreign keys)
IF OBJECT_ID('dbo.TimeBreaks', 'U') IS NOT NULL
    DROP TABLE dbo.TimeBreaks;
IF OBJECT_ID('dbo.TimeEntries', 'U') IS NOT NULL
    DROP TABLE dbo.TimeEntries;

-- Create TimeEntries table for main clock in/out records
CREATE TABLE dbo.TimeEntries (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    clock_in_time DATETIME NOT NULL,
    clock_out_time DATETIME NULL,
    total_hours DECIMAL(8,2) NULL,
    total_break_minutes INT DEFAULT 0,
    notes NVARCHAR(500) NULL,
    status NVARCHAR(20) DEFAULT 'active', -- 'active', 'completed', 'incomplete'
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (user_id) REFERENCES dbo.Users(id) ON DELETE CASCADE
);

-- Create TimeBreaks table for break tracking
CREATE TABLE dbo.TimeBreaks (
    id INT IDENTITY(1,1) PRIMARY KEY,
    time_entry_id INT NOT NULL,
    break_start DATETIME NOT NULL,
    break_end DATETIME NULL,
    break_duration_minutes INT NULL,
    break_type NVARCHAR(50) DEFAULT 'regular', -- 'regular', 'lunch', 'meeting', 'personal'
    notes NVARCHAR(255) NULL,
    created_at DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (time_entry_id) REFERENCES dbo.TimeEntries(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_time_entries_user_date ON dbo.TimeEntries(user_id, clock_in_time);
CREATE INDEX idx_time_entries_status ON dbo.TimeEntries(status);
CREATE INDEX idx_time_breaks_entry ON dbo.TimeBreaks(time_entry_id);
CREATE INDEX idx_time_breaks_dates ON dbo.TimeBreaks(break_start, break_end);
GO

-- Create a view for daily time summaries
CREATE VIEW dbo.DailyTimeSummary AS
SELECT 
    te.user_id,
    u.username,
    u.full_name,
    CAST(te.clock_in_time AS DATE) as work_date,
    te.id as time_entry_id,
    te.clock_in_time,
    te.clock_out_time,
    te.total_hours,
    te.total_break_minutes,
    te.status,
    CASE 
        WHEN te.clock_out_time IS NULL THEN 
            DATEDIFF(MINUTE, te.clock_in_time, GETDATE()) - ISNULL(te.total_break_minutes, 0)
        ELSE 
            DATEDIFF(MINUTE, te.clock_in_time, te.clock_out_time) - ISNULL(te.total_break_minutes, 0)
    END as net_work_minutes
FROM dbo.TimeEntries te
INNER JOIN dbo.Users u ON te.user_id = u.id;
GO

-- Create a view for break summaries
CREATE VIEW dbo.BreakSummary AS
SELECT 
    tb.time_entry_id,
    tb.break_type,
    COUNT(*) as break_count,
    SUM(tb.break_duration_minutes) as total_break_minutes
FROM dbo.TimeBreaks tb
WHERE tb.break_end IS NOT NULL
GROUP BY tb.time_entry_id, tb.break_type;
GO

-- Insert some example break types configuration (optional)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[BreakTypes]') AND type in (N'U'))
BEGIN
    CREATE TABLE dbo.BreakTypes (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name NVARCHAR(50) NOT NULL,
        max_duration_minutes INT NULL,
        is_paid BIT DEFAULT 1,
        color_code NVARCHAR(7) DEFAULT '#007bff',
        is_active BIT DEFAULT 1
    );
    
    INSERT INTO dbo.BreakTypes (name, max_duration_minutes, is_paid, color_code) VALUES
    ('Regular Break', 15, 1, '#28a745'),
    ('Lunch Break', 60, 0, '#ffc107'),
    ('Meeting', NULL, 1, '#007bff'),
    ('Personal', 30, 0, '#dc3545'),
    ('Restroom', 10, 1, '#6c757d');
END
GO

-- Display created tables and sample data
SELECT 'TimeEntries' as TableName, COUNT(*) as RecordCount FROM dbo.TimeEntries
UNION ALL
SELECT 'TimeBreaks', COUNT(*) FROM dbo.TimeBreaks
UNION ALL
SELECT 'BreakTypes', COUNT(*) FROM dbo.BreakTypes; 