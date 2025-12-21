/*
 * Create activity_logs table (SQL Server)
 * Used as the unified audit log + performance feed event source.
 */

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[activity_logs]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[activity_logs](
        [id] INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
        [user_id] INT NOT NULL,
        [entity_type] NVARCHAR(50) NOT NULL,
        [entity_id] INT NOT NULL,
        [action] NVARCHAR(50) NOT NULL,
        [description] NVARCHAR(MAX) NULL,
        [metadata] NVARCHAR(MAX) NULL,
        [created_at] DATETIME NOT NULL CONSTRAINT DF_activity_logs_created_at DEFAULT (GETDATE()),
        [ip_address] NVARCHAR(45) NULL
    );

    CREATE INDEX IX_activity_logs_user_created_at ON [dbo].[activity_logs] ([user_id], [created_at] DESC);
    CREATE INDEX IX_activity_logs_entity_created_at ON [dbo].[activity_logs] ([entity_type], [entity_id], [created_at] DESC);
    CREATE INDEX IX_activity_logs_created_at ON [dbo].[activity_logs] ([created_at] DESC);
END
GO

-- Ensure created_at has a default even if the table existed without one
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[activity_logs]') AND type in (N'U'))
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM sys.default_constraints dc
        INNER JOIN sys.columns c
            ON c.default_object_id = dc.object_id
        INNER JOIN sys.tables t
            ON t.object_id = dc.parent_object_id
        WHERE t.name = 'activity_logs' AND c.name = 'created_at'
    )
    BEGIN
        ALTER TABLE [dbo].[activity_logs] ADD CONSTRAINT DF_activity_logs_created_at DEFAULT (GETDATE()) FOR [created_at];
    END
END
GO

