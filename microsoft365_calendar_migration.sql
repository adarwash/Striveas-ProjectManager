-- Migration script for Microsoft 365 Calendar Integration
-- Run this script to update existing databases

-- Add 'microsoft365' to the source enum in external_calendars table
ALTER TABLE `external_calendars` 
MODIFY COLUMN `source` enum('google','outlook','ical','microsoft365') NOT NULL;

-- Add url field to calendar_events table if it doesn't exist
ALTER TABLE `calendar_events` 
ADD COLUMN `url` varchar(1000) DEFAULT NULL AFTER `all_day`;

-- Update any existing 'outlook' entries to 'microsoft365' for better clarity
UPDATE `external_calendars` 
SET `source` = 'microsoft365' 
WHERE `source` = 'outlook';

-- Add index on token_expires for better performance during token refresh checks
ALTER TABLE `external_calendars` 
ADD INDEX `idx_token_expires` (`token_expires`);

-- Add index on source for better performance when filtering by calendar type
ALTER TABLE `external_calendars` 
ADD INDEX `idx_source` (`source`);

-- Add index on calendar events start_time for better performance
ALTER TABLE `calendar_events` 
ADD INDEX `idx_start_time` (`start_time`);

-- Optional: Clean up any orphaned calendar events (events without valid calendars)
DELETE ce FROM `calendar_events` ce
LEFT JOIN `external_calendars` ec ON ce.calendar_id = ec.id
WHERE ec.id IS NULL; 