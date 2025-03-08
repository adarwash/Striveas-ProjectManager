-- Check if the table already exists
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='EmployeePerformanceNotes' AND xtype='U')
BEGIN
    PRINT 'Creating EmployeePerformanceNotes table...';
    
    -- Create the table
    CREATE TABLE EmployeePerformanceNotes (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        note_text NVARCHAR(MAX) NOT NULL,
        note_type NVARCHAR(50) DEFAULT 'general',
        created_by INT NOT NULL,
        created_at DATETIME DEFAULT GETDATE(),
        CONSTRAINT fk_performance_notes_user FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE,
        CONSTRAINT fk_performance_notes_creator FOREIGN KEY (created_by) REFERENCES Users(id)
    );
    
    PRINT 'EmployeePerformanceNotes table created successfully.';
END
ELSE
BEGIN
    PRINT 'EmployeePerformanceNotes table already exists.';
END
GO 