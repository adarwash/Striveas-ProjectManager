-- Create Notes table if it doesn't exist
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Notes]') AND type in (N'U'))
BEGIN
    CREATE TABLE [dbo].[Notes] (
        [id] INT IDENTITY(1,1) PRIMARY KEY,
        [title] NVARCHAR(200) NOT NULL,
        [content] NVARCHAR(MAX) NOT NULL,
        [type] NVARCHAR(50) NOT NULL, -- 'project', 'task', or 'personal'
        [reference_id] INT NULL,  -- project_id or task_id, NULL for personal notes
        [created_by] INT NOT NULL,
        [created_at] DATETIME DEFAULT GETDATE(),
        [updated_at] DATETIME DEFAULT GETDATE(),
        CONSTRAINT [FK_Notes_Users] FOREIGN KEY ([created_by]) REFERENCES [Users]([id]) ON DELETE CASCADE
    )
END

-- Add indexes for better performance
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name = 'IX_Notes_Type_Reference' AND object_id = OBJECT_ID('Notes'))
BEGIN
    CREATE INDEX [IX_Notes_Type_Reference] ON [Notes]([type], [reference_id])
END 