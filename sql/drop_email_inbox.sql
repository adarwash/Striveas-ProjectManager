/*
Drop Email Inbox tables (SQL Server).

This removes the inbound email ingestion/history tables used by the legacy Email Inbox feature.
It does NOT remove EmailQueue (outbound notifications).

Run on the ProjectTracker database.
*/

SET NOCOUNT ON;

DECLARE @sql NVARCHAR(MAX) = N'';

-- Drop any foreign keys that reference EmailInbox or EmailAttachments
SELECT @sql = @sql + N'ALTER TABLE [' + SCHEMA_NAME(pt.schema_id) + N'].[' + pt.name + N'] DROP CONSTRAINT [' + fk.name + N'];' + CHAR(10)
FROM sys.foreign_keys fk
JOIN sys.tables pt ON fk.parent_object_id = pt.object_id
JOIN sys.tables rt ON fk.referenced_object_id = rt.object_id
WHERE rt.name IN (N'EmailInbox', N'EmailAttachments')
   OR pt.name IN (N'EmailInbox', N'EmailAttachments');

IF (@sql <> N'')
BEGIN
    EXEC sp_executesql @sql;
END

-- Drop tables (order matters)
IF OBJECT_ID(N'dbo.EmailAttachments', N'U') IS NOT NULL
    DROP TABLE dbo.EmailAttachments;

IF OBJECT_ID(N'dbo.EmailInbox', N'U') IS NOT NULL
    DROP TABLE dbo.EmailInbox;
