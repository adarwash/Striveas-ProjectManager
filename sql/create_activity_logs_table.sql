-- Create Activity Logs Table
-- This table stores activity logs for various entities in the system

IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[activity_logs]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[activity_logs] (
        [id] INT IDENTITY(1,1) PRIMARY KEY,
        [user_id] INT NOT NULL,
        [entity_type] NVARCHAR(50) NOT NULL, -- 'project', 'task', 'document', etc.
        [entity_id] INT NOT NULL,
        [action] NVARCHAR(50) NOT NULL, -- 'created', 'updated', 'deleted', etc.
        [description] NVARCHAR(MAX) NULL,
        [metadata] NVARCHAR(MAX) NULL, -- JSON string for additional data
        [created_at] DATETIME DEFAULT GETDATE(),
        [ip_address] NVARCHAR(45) NULL
    )

    -- Add indexes for better performance
    CREATE INDEX [IX_activity_logs_entity] ON [activity_logs]([entity_type], [entity_id]);
    CREATE INDEX [IX_activity_logs_user] ON [activity_logs]([user_id]);
    CREATE INDEX [IX_activity_logs_created_at] ON [activity_logs]([created_at]);

    -- Add foreign key constraint
    ALTER TABLE [activity_logs]
    ADD CONSTRAINT [FK_activity_logs_users]
    FOREIGN KEY ([user_id]) REFERENCES [users]([id])
    ON DELETE CASCADE;
END 