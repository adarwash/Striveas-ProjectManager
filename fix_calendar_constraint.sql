-- Quick fix to add microsoft365 to calendar source constraint
-- For existing databases that already have calendar tables

-- Drop the existing constraint
ALTER TABLE [external_calendars] DROP CONSTRAINT [CHK_external_calendars_source];

-- Add the new constraint with microsoft365
ALTER TABLE [external_calendars] 
ADD CONSTRAINT [CHK_external_calendars_source] 
CHECK ([source] IN ('google','outlook','ical','microsoft365'));

-- Update any existing 'outlook' entries to 'microsoft365' for clarity
UPDATE [external_calendars] 
SET [source] = 'microsoft365' 
WHERE [source] = 'outlook';

PRINT 'Calendar constraint updated successfully - microsoft365 support added'; 