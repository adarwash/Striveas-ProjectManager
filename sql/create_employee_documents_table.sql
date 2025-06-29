USE [ProjectTracker]
GO

-- Create the employee_documents table if it doesn't exist
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'employee_documents')
BEGIN
    CREATE TABLE [dbo].[employee_documents] (
        [id] INT IDENTITY(1,1) PRIMARY KEY,
        [user_id] INT NOT NULL,
        [file_name] NVARCHAR(255) NOT NULL,
        [file_path] NVARCHAR(1000) NOT NULL,
        [file_type] NVARCHAR(50) NOT NULL,
        [file_size] INT NOT NULL,
        [document_type] NVARCHAR(50) NULL, -- contract, CV, certificate, etc.
        [description] NVARCHAR(255) NULL,
        [uploaded_by] INT NOT NULL,
        [uploaded_at] DATETIME NOT NULL DEFAULT GETDATE(),
        CONSTRAINT [FK_employee_documents_users] FOREIGN KEY ([user_id]) 
            REFERENCES [dbo].[Users]([id]) ON DELETE CASCADE,
        CONSTRAINT [FK_employee_documents_uploader] FOREIGN KEY ([uploaded_by]) 
            REFERENCES [dbo].[Users]([id])
    );

    -- Create indexes for better performance
    CREATE INDEX [IX_employee_documents_user_id] ON [dbo].[employee_documents]([user_id]);
    CREATE INDEX [IX_employee_documents_uploaded_by] ON [dbo].[employee_documents]([uploaded_by]);
END
GO 