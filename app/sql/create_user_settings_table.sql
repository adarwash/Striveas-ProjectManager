IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[UserSettings]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[UserSettings] (
        [id] INT IDENTITY(1,1) PRIMARY KEY,
        [user_id] INT NOT NULL,
        [email_notifications] BIT DEFAULT 1,
        [task_reminders] BIT DEFAULT 1,
        [project_updates] BIT DEFAULT 1,
        [created_at] DATETIME DEFAULT GETDATE(),
        [updated_at] DATETIME DEFAULT GETDATE(),
        CONSTRAINT [FK_UserSettings_Users] FOREIGN KEY ([user_id]) REFERENCES [Users]([id]) ON DELETE CASCADE
    )
END 