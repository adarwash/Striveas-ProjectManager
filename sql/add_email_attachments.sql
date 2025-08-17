-- Add attachment support to Email system
-- Run this script to add attachment functionality to existing database

-- Add attachment tracking to EmailInbox table
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'[dbo].[EmailInbox]') AND name = 'has_attachments')
BEGIN
    ALTER TABLE dbo.EmailInbox ADD has_attachments BIT DEFAULT 0;
END;

IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID(N'[dbo].[EmailInbox]') AND name = 'attachment_count')
BEGIN
    ALTER TABLE dbo.EmailInbox ADD attachment_count INT DEFAULT 0;
END;

-- Create EmailAttachments table for storing email attachment metadata
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[EmailAttachments]') AND type in (N'U'))
BEGIN
    CREATE TABLE dbo.EmailAttachments (
        id INT IDENTITY(1,1) PRIMARY KEY,
        email_inbox_id INT NOT NULL,
        
        -- Microsoft Graph API identifiers
        ms_attachment_id NVARCHAR(255) NULL,
        content_id NVARCHAR(255) NULL, -- For inline attachments
        
        -- File information
        filename NVARCHAR(255) NOT NULL,
        original_filename NVARCHAR(255) NOT NULL,
        file_path NVARCHAR(500) NULL, -- Relative path to stored file
        file_size BIGINT NOT NULL, -- Size in bytes
        mime_type NVARCHAR(100) NOT NULL,
        file_hash NVARCHAR(64) NULL, -- SHA256 hash for deduplication
        
        -- Attachment metadata
        is_inline BIT DEFAULT 0, -- Whether it's an inline attachment (embedded in email)
        is_downloaded BIT DEFAULT 0, -- Whether file has been downloaded from Graph API
        download_error NVARCHAR(500) NULL, -- Error message if download failed
        
        -- Timestamps
        created_at DATETIME DEFAULT GETDATE(),
        downloaded_at DATETIME NULL,
        
        -- Foreign key
        FOREIGN KEY (email_inbox_id) REFERENCES dbo.EmailInbox(id) ON DELETE CASCADE
    );
    
    -- Create index for faster lookups
    CREATE INDEX IX_EmailAttachments_EmailInboxId ON dbo.EmailAttachments(email_inbox_id);
    CREATE INDEX IX_EmailAttachments_MSAttachmentId ON dbo.EmailAttachments(ms_attachment_id);
END;

PRINT 'Email attachment support tables created successfully';

-- Ensure TicketAssignments table exists
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[TicketAssignments]') AND type in (N'U'))
BEGIN
    CREATE TABLE dbo.TicketAssignments (
        id INT IDENTITY(1,1) PRIMARY KEY,
        ticket_id INT NOT NULL,
        user_id INT NOT NULL,
        assigned_by INT NOT NULL,
        role NVARCHAR(50) DEFAULT 'assignee',
        assigned_at DATETIME DEFAULT GETDATE(),
        removed_at DATETIME NULL,
        FOREIGN KEY (ticket_id) REFERENCES dbo.Tickets(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES dbo.Users(id),
        FOREIGN KEY (assigned_by) REFERENCES dbo.Users(id),
        UNIQUE(ticket_id, user_id, role)
    );
    CREATE INDEX IX_TicketAssignments_Ticket ON dbo.TicketAssignments(ticket_id);
    CREATE INDEX IX_TicketAssignments_User ON dbo.TicketAssignments(user_id);
    PRINT 'TicketAssignments table created.';
END;


-- Ensure TicketDashboard view exposes id fields used by application filters
IF OBJECT_ID('dbo.TicketDashboard', 'V') IS NOT NULL
BEGIN
    DROP VIEW dbo.TicketDashboard;
END;
GO
CREATE VIEW dbo.TicketDashboard AS
SELECT 
    t.id,
    t.ticket_number,
    t.subject,
    t.created_at,
    t.updated_at,
    t.due_date,
    t.status_id,
    t.priority_id,
    t.category_id,
    t.assigned_to,
    t.created_by,
    t.client_id,
    -- Status info
    ts.name as status_name,
    ts.display_name as status_display,
    ts.color_code as status_color,
    ts.is_closed,
    -- Priority info  
    tp.name as priority_name,
    tp.display_name as priority_display,
    tp.color_code as priority_color,
    tp.level as priority_level,
    -- Category info
    tc.name as category_name,
    tc.description as category_description,
    -- People
    creator.username as created_by_username,
    creator.full_name as created_by_name,
    creator.email as created_by_email,
    assignee.username as assigned_to_username,
    assignee.full_name as assigned_to_name,
    assignee.email as assigned_to_email,
    -- Metrics
    DATEDIFF(hour, t.created_at, COALESCE(t.resolved_at, GETDATE())) as age_hours,
    CASE 
        WHEN t.due_date IS NOT NULL AND GETDATE() > t.due_date AND ts.is_closed = 0 
        THEN 1 ELSE 0 
    END as is_overdue,
    -- Message count
    (SELECT COUNT(*) FROM dbo.TicketMessages tm WHERE tm.ticket_id = t.id) as message_count
FROM dbo.Tickets t
LEFT JOIN dbo.TicketStatuses ts ON t.status_id = ts.id
LEFT JOIN dbo.TicketPriorities tp ON t.priority_id = tp.id  
LEFT JOIN dbo.TicketCategories tc ON t.category_id = tc.id
LEFT JOIN dbo.Users creator ON t.created_by = creator.id
LEFT JOIN dbo.Users assignee ON t.assigned_to = assignee.id;

-- Ensure EmailQueue table exists
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[EmailQueue]') AND type in (N'U'))
BEGIN
    CREATE TABLE dbo.EmailQueue (
        id INT IDENTITY(1,1) PRIMARY KEY,
        to_address NVARCHAR(255) NOT NULL,
        cc_address NVARCHAR(500) NULL,
        bcc_address NVARCHAR(500) NULL,
        subject NVARCHAR(500) NOT NULL,
        body_text NVARCHAR(MAX) NULL,
        body_html NVARCHAR(MAX) NULL,
        ticket_id INT NULL,
        message_id INT NULL,
        template_name NVARCHAR(100) NULL,
        status NVARCHAR(50) DEFAULT 'pending',
        priority INT DEFAULT 5,
        attempts INT DEFAULT 0,
        max_attempts INT DEFAULT 3,
        error_message NVARCHAR(500) NULL,
        created_at DATETIME DEFAULT GETDATE(),
        send_after DATETIME DEFAULT GETDATE(),
        sent_at DATETIME NULL,
        last_attempt_at DATETIME NULL
    );
    CREATE INDEX IX_EmailQueue_Status ON dbo.EmailQueue(status);
    CREATE INDEX IX_EmailQueue_Priority ON dbo.EmailQueue(priority, send_after);
    PRINT 'EmailQueue table created.';
END;

