-- Create Weekly Routers Table
-- This table stores weekly router schedules set by IT managers for technicians

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[weekly_routers]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[weekly_routers] (
        [id] INT IDENTITY(1,1) PRIMARY KEY,
        [router_name] NVARCHAR(100) NOT NULL,
        [router_ip] NVARCHAR(45) NOT NULL,
        [location] NVARCHAR(200) NOT NULL,
        [assigned_technician_id] INT NULL,
        [week_start_date] DATE NOT NULL,
        [week_end_date] DATE NOT NULL,
        [maintenance_type] NVARCHAR(50) NOT NULL, -- 'routine', 'repair', 'upgrade', 'inspection'
        [priority] NVARCHAR(20) DEFAULT 'Medium', -- 'Low', 'Medium', 'High', 'Critical'
        [description] NVARCHAR(MAX) NULL,
        [status] NVARCHAR(20) DEFAULT 'Scheduled', -- 'Scheduled', 'In Progress', 'Completed', 'Cancelled'
        [estimated_hours] DECIMAL(4,2) NULL,
        [actual_hours] DECIMAL(4,2) NULL,
        [notes] NVARCHAR(MAX) NULL,
        [created_by] INT NOT NULL, -- IT Manager who created the schedule
        [created_at] DATETIME DEFAULT GETDATE(),
        [updated_at] DATETIME NULL,
        [completed_at] DATETIME NULL
    )

    -- Add indexes for better performance
    CREATE INDEX [IX_weekly_routers_technician] ON [weekly_routers]([assigned_technician_id]);
    CREATE INDEX [IX_weekly_routers_week] ON [weekly_routers]([week_start_date], [week_end_date]);
    CREATE INDEX [IX_weekly_routers_status] ON [weekly_routers]([status]);
    CREATE INDEX [IX_weekly_routers_created_by] ON [weekly_routers]([created_by]);

    -- Add foreign key constraints
    ALTER TABLE [weekly_routers]
    ADD CONSTRAINT [FK_weekly_routers_technician]
    FOREIGN KEY ([assigned_technician_id]) REFERENCES [users]([id])
    ON DELETE SET NULL;

    ALTER TABLE [weekly_routers]
    ADD CONSTRAINT [FK_weekly_routers_creator]
    FOREIGN KEY ([created_by]) REFERENCES [users]([id])
    ON DELETE NO ACTION;

    -- Add check constraints
    ALTER TABLE [weekly_routers]
    ADD CONSTRAINT [CHK_weekly_routers_priority]
    CHECK ([priority] IN ('Low', 'Medium', 'High', 'Critical'));

    ALTER TABLE [weekly_routers]
    ADD CONSTRAINT [CHK_weekly_routers_status]
    CHECK ([status] IN ('Scheduled', 'In Progress', 'Completed', 'Cancelled'));

    ALTER TABLE [weekly_routers]
    ADD CONSTRAINT [CHK_weekly_routers_maintenance_type]
    CHECK ([maintenance_type] IN ('routine', 'repair', 'upgrade', 'inspection'));
END 