-- Table for storing external calendar connections (MS SQL Server)
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='external_calendars' AND xtype='U')
CREATE TABLE [external_calendars] (
  [id] int IDENTITY(1,1) NOT NULL,
  [user_id] int NOT NULL,
  [name] nvarchar(255) NOT NULL,
  [source] nvarchar(20) NOT NULL CHECK ([source] IN ('google','outlook','ical','microsoft365')),
  [source_id] nvarchar(512) NOT NULL,
  [color] nvarchar(20) NOT NULL DEFAULT '#039be5',
  [auto_refresh] bit NOT NULL DEFAULT 0,
  [access_token] ntext NULL,
  [refresh_token] ntext NULL,
  [token_expires] datetime NULL,
  [last_synced] datetime NULL,
  [active] bit NOT NULL DEFAULT 1,
  [created_at] datetime NOT NULL DEFAULT GETDATE(),
  [updated_at] datetime NOT NULL DEFAULT GETDATE(),
  CONSTRAINT [PK_external_calendars] PRIMARY KEY ([id]),
  CONSTRAINT [FK_external_calendars_users] FOREIGN KEY ([user_id]) REFERENCES [users] ([id]) ON DELETE CASCADE
);

-- Create indexes for external_calendars
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name='IX_external_calendars_user_id')
CREATE INDEX [IX_external_calendars_user_id] ON [external_calendars] ([user_id]);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name='IX_external_calendars_source')
CREATE INDEX [IX_external_calendars_source] ON [external_calendars] ([source]);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name='IX_external_calendars_token_expires')
CREATE INDEX [IX_external_calendars_token_expires] ON [external_calendars] ([token_expires]);

-- Table for storing events from external calendars (MS SQL Server)
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='calendar_events' AND xtype='U')
CREATE TABLE [calendar_events] (
  [id] int IDENTITY(1,1) NOT NULL,
  [calendar_id] int NOT NULL,
  [event_uid] nvarchar(255) NOT NULL,
  [title] nvarchar(255) NOT NULL,
  [description] ntext NULL,
  [location] nvarchar(255) NULL,
  [start_time] datetime NOT NULL,
  [end_time] datetime NOT NULL,
  [all_day] bit NOT NULL DEFAULT 0,
  [url] nvarchar(1000) NULL,
  [recurrence_rule] ntext NULL,
  [created_at] datetime NOT NULL DEFAULT GETDATE(),
  [updated_at] datetime NOT NULL DEFAULT GETDATE(),
  CONSTRAINT [PK_calendar_events] PRIMARY KEY ([id]),
  CONSTRAINT [FK_calendar_events_calendars] FOREIGN KEY ([calendar_id]) REFERENCES [external_calendars] ([id]) ON DELETE CASCADE,
  CONSTRAINT [UQ_calendar_events_uid] UNIQUE ([calendar_id], [event_uid])
);

-- Create indexes for calendar_events
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name='IX_calendar_events_calendar_id')
CREATE INDEX [IX_calendar_events_calendar_id] ON [calendar_events] ([calendar_id]);

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE name='IX_calendar_events_start_time')
CREATE INDEX [IX_calendar_events_start_time] ON [calendar_events] ([start_time]);

-- Create trigger for updated_at on external_calendars
IF NOT EXISTS (SELECT * FROM sys.triggers WHERE name='TR_external_calendars_updated_at')
EXEC('
CREATE TRIGGER [TR_external_calendars_updated_at] 
ON [external_calendars]
FOR UPDATE
AS
BEGIN
    UPDATE [external_calendars] 
    SET [updated_at] = GETDATE() 
    WHERE [id] IN (SELECT [id] FROM inserted)
END
');

-- Create trigger for updated_at on calendar_events
IF NOT EXISTS (SELECT * FROM sys.triggers WHERE name='TR_calendar_events_updated_at')
EXEC('
CREATE TRIGGER [TR_calendar_events_updated_at] 
ON [calendar_events]
FOR UPDATE
AS
BEGIN
    UPDATE [calendar_events] 
    SET [updated_at] = GETDATE() 
    WHERE [id] IN (SELECT [id] FROM inserted)
END
'); 