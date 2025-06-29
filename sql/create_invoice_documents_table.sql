USE [HiveITPortal]
GO

-- Create the invoice_documents table if it doesn't exist
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'invoice_documents')
BEGIN
    CREATE TABLE [dbo].[invoice_documents] (
        [id] INT IDENTITY(1,1) PRIMARY KEY,
        [invoice_id] INT NOT NULL,
        [file_name] NVARCHAR(255) NOT NULL,
        [file_path] NVARCHAR(1000) NOT NULL,
        [file_type] NVARCHAR(50) NOT NULL,
        [file_size] INT NOT NULL,
        [uploaded_by] INT NOT NULL,
        [uploaded_at] DATETIME NOT NULL DEFAULT GETDATE(),
        CONSTRAINT [FK_invoice_documents_invoices] FOREIGN KEY ([invoice_id]) 
            REFERENCES [dbo].[Invoices]([id]) ON DELETE CASCADE,
        CONSTRAINT [FK_invoice_documents_users] FOREIGN KEY ([uploaded_by]) 
            REFERENCES [dbo].[Users]([id])
    );

    -- Create indexes for better performance
    CREATE INDEX [IX_invoice_documents_invoice_id] ON [dbo].[invoice_documents]([invoice_id]);
    CREATE INDEX [IX_invoice_documents_uploaded_by] ON [dbo].[invoice_documents]([uploaded_by]);
END
GO 