-- Create daily activities table
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='daily_activities' AND xtype='U')
BEGIN
    CREATE TABLE daily_activities (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        activity_date DATE NOT NULL DEFAULT GETDATE(),
        description NVARCHAR(MAX) NULL,
        check_in DATETIME NULL,
        check_out DATETIME NULL,
        total_hours DECIMAL(5,2) NULL,
        status NVARCHAR(20) DEFAULT 'Pending' CHECK (status IN ('Pending', 'Approved', 'Rejected')),
        created_at DATETIME DEFAULT GETDATE(),
        updated_at DATETIME NULL,
        CONSTRAINT fk_daily_activities_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )

    -- Add indexes for better performance
    CREATE INDEX idx_daily_activities_user ON daily_activities (user_id)
    CREATE INDEX idx_daily_activities_date ON daily_activities (activity_date)
    CREATE INDEX idx_daily_activities_status ON daily_activities (status)
END 