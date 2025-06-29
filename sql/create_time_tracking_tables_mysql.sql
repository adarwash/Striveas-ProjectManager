-- Create Time Tracking tables for MySQL
-- Use ProjectTracker database
USE ProjectTracker;

-- Drop tables if they exist (in correct order to handle foreign keys)
DROP TABLE IF EXISTS TimeBreaks;
DROP TABLE IF EXISTS TimeEntries;
DROP TABLE IF EXISTS BreakTypes;

-- Create TimeEntries table for main clock in/out records
CREATE TABLE TimeEntries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    clock_in_time DATETIME NOT NULL,
    clock_out_time DATETIME NULL,
    total_hours DECIMAL(8,2) NULL,
    total_break_minutes INT DEFAULT 0,
    notes TEXT NULL,
    status VARCHAR(20) DEFAULT 'active', -- 'active', 'completed', 'incomplete'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE
);

-- Create TimeBreaks table for break tracking
CREATE TABLE TimeBreaks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    time_entry_id INT NOT NULL,
    break_start DATETIME NOT NULL,
    break_end DATETIME NULL,
    break_duration_minutes INT NULL,
    break_type VARCHAR(50) DEFAULT 'regular', -- 'regular', 'lunch', 'meeting', 'personal'
    notes VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (time_entry_id) REFERENCES TimeEntries(id) ON DELETE CASCADE
);

-- Create BreakTypes table
CREATE TABLE BreakTypes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    max_duration_minutes INT NULL,
    is_paid BOOLEAN DEFAULT TRUE,
    color_code VARCHAR(7) DEFAULT '#007bff',
    is_active BOOLEAN DEFAULT TRUE
);

-- Create indexes for better performance
CREATE INDEX idx_time_entries_user_date ON TimeEntries(user_id, clock_in_time);
CREATE INDEX idx_time_entries_status ON TimeEntries(status);
CREATE INDEX idx_time_breaks_entry ON TimeBreaks(time_entry_id);
CREATE INDEX idx_time_breaks_dates ON TimeBreaks(break_start, break_end);

-- Create a view for daily time summaries
CREATE VIEW DailyTimeSummary AS
SELECT 
    te.user_id,
    u.username,
    u.full_name,
    DATE(te.clock_in_time) as work_date,
    te.id as time_entry_id,
    te.clock_in_time,
    te.clock_out_time,
    te.total_hours,
    te.total_break_minutes,
    te.status,
    CASE 
        WHEN te.clock_out_time IS NULL THEN 
            TIMESTAMPDIFF(MINUTE, te.clock_in_time, NOW()) - IFNULL(te.total_break_minutes, 0)
        ELSE 
            TIMESTAMPDIFF(MINUTE, te.clock_in_time, te.clock_out_time) - IFNULL(te.total_break_minutes, 0)
    END as net_work_minutes
FROM TimeEntries te
INNER JOIN Users u ON te.user_id = u.id;

-- Create a view for break summaries
CREATE VIEW BreakSummary AS
SELECT 
    tb.time_entry_id,
    tb.break_type,
    COUNT(*) as break_count,
    SUM(tb.break_duration_minutes) as total_break_minutes
FROM TimeBreaks tb
WHERE tb.break_end IS NOT NULL
GROUP BY tb.time_entry_id, tb.break_type;

-- Insert default break types
INSERT INTO BreakTypes (name, max_duration_minutes, is_paid, color_code) VALUES
('Regular Break', 15, TRUE, '#28a745'),
('Lunch Break', 60, FALSE, '#ffc107'),
('Meeting', NULL, TRUE, '#007bff'),
('Personal', 30, FALSE, '#dc3545'),
('Restroom', 10, TRUE, '#6c757d');

-- Display created tables and sample data
SELECT 'TimeEntries' as TableName, COUNT(*) as RecordCount FROM TimeEntries
UNION ALL
SELECT 'TimeBreaks', COUNT(*) FROM TimeBreaks
UNION ALL
SELECT 'BreakTypes', COUNT(*) FROM BreakTypes; 