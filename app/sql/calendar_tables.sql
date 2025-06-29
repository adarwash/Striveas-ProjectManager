-- SQL script to create tables for external calendar integration (MS SQL Server version)

-- Table to store external calendar connections
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'external_calendars')
BEGIN
    CREATE TABLE external_calendars (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT NOT NULL,
        name NVARCHAR(255) NOT NULL,
        source NVARCHAR(20) NOT NULL,
        source_id NVARCHAR(512) NOT NULL,
        color NVARCHAR(20) NOT NULL DEFAULT '#039be5',
        auto_refresh BIT NOT NULL DEFAULT 0,
        access_token NVARCHAR(MAX) NULL,
        refresh_token NVARCHAR(MAX) NULL,
        token_expires DATETIME NULL,
        last_synced DATETIME NULL,
        active BIT NOT NULL DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT GETDATE(),
        updated_at DATETIME NOT NULL DEFAULT GETDATE(),
        CONSTRAINT FK_external_calendars_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );

    -- Create index on user_id
    CREATE INDEX IX_external_calendars_user_id ON external_calendars (user_id);
END

-- Table to store events from external calendars
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'calendar_events')
BEGIN
    CREATE TABLE calendar_events (
        id INT IDENTITY(1,1) PRIMARY KEY,
        calendar_id INT NOT NULL,
        event_uid NVARCHAR(255) NOT NULL,
        title NVARCHAR(255) NOT NULL,
        description NVARCHAR(MAX) NULL,
        location NVARCHAR(255) NULL,
        start_time DATETIME NOT NULL,
        end_time DATETIME NOT NULL,
        all_day BIT NOT NULL DEFAULT 0,
        recurrence_rule NVARCHAR(MAX) NULL,
        url NVARCHAR(512) NULL,
        created_at DATETIME NOT NULL DEFAULT GETDATE(),
        updated_at DATETIME NOT NULL DEFAULT GETDATE(),
        CONSTRAINT FK_calendar_events_external_calendars FOREIGN KEY (calendar_id) REFERENCES external_calendars(id) ON DELETE CASCADE
    );

    -- Create unique constraint for calendar_id and event_uid
    CREATE UNIQUE INDEX UX_calendar_events_event_uid ON calendar_events (calendar_id, event_uid);
    
    -- Create index on calendar_id
    CREATE INDEX IX_calendar_events_calendar_id ON calendar_events (calendar_id);
END

-- Create trigger to update the updated_at timestamp for external_calendars
IF NOT EXISTS (SELECT * FROM sys.triggers WHERE name = 'TR_external_calendars_update_timestamp')
BEGIN
    EXEC('CREATE TRIGGER TR_external_calendars_update_timestamp
    ON external_calendars
    AFTER UPDATE
    AS
    BEGIN
        SET NOCOUNT ON;
        UPDATE external_calendars
        SET updated_at = GETDATE()
        FROM external_calendars ec
        INNER JOIN inserted i ON ec.id = i.id;
    END');
END

-- Create trigger to update the updated_at timestamp for calendar_events
IF NOT EXISTS (SELECT * FROM sys.triggers WHERE name = 'TR_calendar_events_update_timestamp')
BEGIN
    EXEC('CREATE TRIGGER TR_calendar_events_update_timestamp
    ON calendar_events
    AFTER UPDATE
    AS
    BEGIN
        SET NOCOUNT ON;
        UPDATE calendar_events
        SET updated_at = GETDATE()
        FROM calendar_events ce
        INNER JOIN inserted i ON ce.id = i.id;
    END');
END 