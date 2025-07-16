-- Migration script for Microsoft 365 Calendar Integration (MS SQL Server)
-- Run this script to update existing databases

-- Check if external_calendars table exists and add microsoft365 to source if needed
IF EXISTS (SELECT * FROM sysobjects WHERE name='external_calendars' AND xtype='U')
BEGIN
    -- Check if the constraint exists and drop it to modify the column
    IF EXISTS (SELECT * FROM sys.check_constraints WHERE name LIKE '%external_calendars%' AND parent_object_id = OBJECT_ID('external_calendars'))
    BEGIN
        DECLARE @constraint_name NVARCHAR(128)
        SELECT @constraint_name = name 
        FROM sys.check_constraints 
        WHERE parent_object_id = OBJECT_ID('external_calendars') 
        AND definition LIKE '%source%'
        
        IF @constraint_name IS NOT NULL
        BEGIN
            EXEC('ALTER TABLE [external_calendars] DROP CONSTRAINT [' + @constraint_name + ']')
        END
    END
    
    -- Add the new constraint with microsoft365
    ALTER TABLE [external_calendars] 
    ADD CONSTRAINT [CK_external_calendars_source] 
    CHECK ([source] IN ('google','outlook','ical','microsoft365'))
    
    -- Update any existing 'outlook' entries to 'microsoft365' for better clarity
    UPDATE [external_calendars] 
    SET [source] = 'microsoft365' 
    WHERE [source] = 'outlook'
END

-- Add url field to calendar_events table if it doesn't exist
IF EXISTS (SELECT * FROM sysobjects WHERE name='calendar_events' AND xtype='U')
BEGIN
    IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('calendar_events') AND name = 'url')
    BEGIN
        ALTER TABLE [calendar_events] 
        ADD [url] nvarchar(1000) NULL
    END
END

-- Add indexes if they don't exist

-- Index on token_expires for better performance during token refresh checks
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name='IX_external_calendars_token_expires')
BEGIN
    CREATE INDEX [IX_external_calendars_token_expires] ON [external_calendars] ([token_expires])
END

-- Index on source for better performance when filtering by calendar type
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name='IX_external_calendars_source')
BEGIN
    CREATE INDEX [IX_external_calendars_source] ON [external_calendars] ([source])
END

-- Index on calendar events start_time for better performance
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name='IX_calendar_events_start_time')
BEGIN
    CREATE INDEX [IX_calendar_events_start_time] ON [calendar_events] ([start_time])
END

-- Clean up any orphaned calendar events (events without valid calendars)
DELETE ce 
FROM [calendar_events] ce
LEFT JOIN [external_calendars] ec ON ce.calendar_id = ec.id
WHERE ec.id IS NULL

PRINT 'Microsoft 365 Calendar Integration migration completed successfully.' 