-- Clean Setup Script for Ticketing System
-- Handles existing foreign keys and tables properly
USE ProjectTracker;
GO

-- Disable foreign key constraints temporarily
EXEC sp_msforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT all"
GO

-- Drop foreign key constraints if they exist
DECLARE @sql NVARCHAR(MAX) = '';
SELECT @sql = @sql + 'ALTER TABLE ' + QUOTENAME(SCHEMA_NAME(schema_id)) + '.' + QUOTENAME(OBJECT_NAME(parent_object_id)) + ' DROP CONSTRAINT ' + QUOTENAME(name) + ';' + CHAR(13)
FROM sys.foreign_keys 
WHERE referenced_object_id IN (
    OBJECT_ID('dbo.Tickets'),
    OBJECT_ID('dbo.TicketMessages'), 
    OBJECT_ID('dbo.TicketCategories'),
    OBJECT_ID('dbo.TicketStatuses'),
    OBJECT_ID('dbo.TicketPriorities'),
    OBJECT_ID('dbo.EmailInbox'),
    OBJECT_ID('dbo.EmailQueue')
);
EXEC sp_executesql @sql;
GO

-- Drop existing tables in correct order
IF OBJECT_ID('dbo.EmailQueue', 'U') IS NOT NULL DROP TABLE dbo.EmailQueue;
IF OBJECT_ID('dbo.EmailInbox', 'U') IS NOT NULL DROP TABLE dbo.EmailInbox;
IF OBJECT_ID('dbo.TicketMessages', 'U') IS NOT NULL DROP TABLE dbo.TicketMessages;
IF OBJECT_ID('dbo.Tickets', 'U') IS NOT NULL DROP TABLE dbo.Tickets;
IF OBJECT_ID('dbo.TicketCategories', 'U') IS NOT NULL DROP TABLE dbo.TicketCategories;
IF OBJECT_ID('dbo.TicketPriorities', 'U') IS NOT NULL DROP TABLE dbo.TicketPriorities;
IF OBJECT_ID('dbo.TicketStatuses', 'U') IS NOT NULL DROP TABLE dbo.TicketStatuses;
GO

-- Drop existing views
IF OBJECT_ID('dbo.TicketDashboard', 'V') IS NOT NULL DROP VIEW dbo.TicketDashboard;
GO

-- Drop existing triggers
IF OBJECT_ID('dbo.tr_tickets_generate_number', 'TR') IS NOT NULL DROP TRIGGER dbo.tr_tickets_generate_number;
IF OBJECT_ID('dbo.tr_tickets_update_timestamp', 'TR') IS NOT NULL DROP TRIGGER dbo.tr_tickets_update_timestamp;
GO

PRINT 'Existing objects cleaned up successfully';
GO

-- Create Ticket Statuses lookup table
CREATE TABLE dbo.TicketStatuses (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(50) NOT NULL UNIQUE,
    display_name NVARCHAR(100) NOT NULL,
    color_code NVARCHAR(7) DEFAULT '#6c757d',
    is_closed BIT DEFAULT 0,
    sort_order INT DEFAULT 0,
    is_active BIT DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE()
);
GO

-- Create Ticket Priorities lookup table  
CREATE TABLE dbo.TicketPriorities (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(50) NOT NULL UNIQUE,
    display_name NVARCHAR(100) NOT NULL,
    color_code NVARCHAR(7) DEFAULT '#6c757d',
    level INT DEFAULT 3,
    sort_order INT DEFAULT 0,
    is_active BIT DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE()
);
GO

-- Create Ticket Categories lookup table
CREATE TABLE dbo.TicketCategories (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(100) NOT NULL UNIQUE,
    description NVARCHAR(500) NULL,
    color_code NVARCHAR(7) DEFAULT '#007bff',
    parent_category_id INT NULL,
    auto_assign_to INT NULL,
    sla_hours INT DEFAULT 24,
    is_active BIT DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE()
);
GO

-- Main Tickets table
CREATE TABLE dbo.Tickets (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_number NVARCHAR(20) NULL,
    subject NVARCHAR(255) NOT NULL,
    description NVARCHAR(MAX) NULL,
    status_id INT NOT NULL DEFAULT 1,
    priority_id INT NOT NULL DEFAULT 3,
    category_id INT NULL,
    created_by INT NOT NULL,
    assigned_to INT NULL,
    client_id INT NULL,
    email_thread_id NVARCHAR(255) NULL,
    inbound_email_address NVARCHAR(255) NULL,
    original_message_id NVARCHAR(500) NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    due_date DATETIME NULL,
    resolved_at DATETIME NULL,
    closed_at DATETIME NULL,
    first_response_at DATETIME NULL,
    source NVARCHAR(50) DEFAULT 'web',
    tags NVARCHAR(500) NULL,
    is_internal BIT DEFAULT 0,
    project_id INT NULL,
    task_id INT NULL
);
GO

-- Ticket Messages table
CREATE TABLE dbo.TicketMessages (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NULL,
    message_type NVARCHAR(50) DEFAULT 'comment',
    subject NVARCHAR(255) NULL,
    content NVARCHAR(MAX) NOT NULL,
    content_format NVARCHAR(20) DEFAULT 'text',
    email_message_id NVARCHAR(500) NULL,
    email_from NVARCHAR(255) NULL,
    email_to NVARCHAR(255) NULL,
    email_cc NVARCHAR(500) NULL,
    email_headers NVARCHAR(MAX) NULL,
    is_public BIT DEFAULT 1,
    is_system_message BIT DEFAULT 0,
    created_at DATETIME DEFAULT GETDATE(),
    email_sent_at DATETIME NULL
);
GO

-- Email Inbox table
CREATE TABLE dbo.EmailInbox (
    id INT IDENTITY(1,1) PRIMARY KEY,
    message_id NVARCHAR(500) NOT NULL UNIQUE,
    subject NVARCHAR(500) NOT NULL,
    from_address NVARCHAR(255) NOT NULL,
    to_address NVARCHAR(500) NOT NULL,
    cc_address NVARCHAR(500) NULL,
    bcc_address NVARCHAR(500) NULL,
    reply_to NVARCHAR(255) NULL,
    body_text NVARCHAR(MAX) NULL,
    body_html NVARCHAR(MAX) NULL,
    raw_headers NVARCHAR(MAX) NULL,
    processing_status NVARCHAR(50) DEFAULT 'pending',
    processing_error NVARCHAR(500) NULL,
    ticket_id INT NULL,
    email_date DATETIME NOT NULL,
    received_at DATETIME DEFAULT GETDATE(),
    processed_at DATETIME NULL,
    uid_validity INT NULL,
    uid INT NULL,
    flags NVARCHAR(100) NULL
);
GO

-- Email Queue table
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
GO

PRINT 'Tables created successfully';
GO

-- Add foreign key constraints
ALTER TABLE dbo.Tickets ADD CONSTRAINT FK_Tickets_Status 
    FOREIGN KEY (status_id) REFERENCES dbo.TicketStatuses(id);

ALTER TABLE dbo.Tickets ADD CONSTRAINT FK_Tickets_Priority 
    FOREIGN KEY (priority_id) REFERENCES dbo.TicketPriorities(id);

ALTER TABLE dbo.Tickets ADD CONSTRAINT FK_Tickets_Category 
    FOREIGN KEY (category_id) REFERENCES dbo.TicketCategories(id);

ALTER TABLE dbo.Tickets ADD CONSTRAINT FK_Tickets_CreatedBy 
    FOREIGN KEY (created_by) REFERENCES dbo.Users(id);

ALTER TABLE dbo.Tickets ADD CONSTRAINT FK_Tickets_AssignedTo 
    FOREIGN KEY (assigned_to) REFERENCES dbo.Users(id);

ALTER TABLE dbo.TicketMessages ADD CONSTRAINT FK_TicketMessages_Ticket 
    FOREIGN KEY (ticket_id) REFERENCES dbo.Tickets(id) ON DELETE CASCADE;

ALTER TABLE dbo.TicketMessages ADD CONSTRAINT FK_TicketMessages_User 
    FOREIGN KEY (user_id) REFERENCES dbo.Users(id);

ALTER TABLE dbo.EmailInbox ADD CONSTRAINT FK_EmailInbox_Ticket 
    FOREIGN KEY (ticket_id) REFERENCES dbo.Tickets(id);

ALTER TABLE dbo.EmailQueue ADD CONSTRAINT FK_EmailQueue_Ticket 
    FOREIGN KEY (ticket_id) REFERENCES dbo.Tickets(id);

ALTER TABLE dbo.EmailQueue ADD CONSTRAINT FK_EmailQueue_Message 
    FOREIGN KEY (message_id) REFERENCES dbo.TicketMessages(id);

ALTER TABLE dbo.TicketCategories ADD CONSTRAINT FK_TicketCategories_Parent 
    FOREIGN KEY (parent_category_id) REFERENCES dbo.TicketCategories(id);

ALTER TABLE dbo.TicketCategories ADD CONSTRAINT FK_TicketCategories_AutoAssign 
    FOREIGN KEY (auto_assign_to) REFERENCES dbo.Users(id);
GO

PRINT 'Foreign keys created successfully';
GO

-- Create indexes
CREATE INDEX idx_tickets_status ON dbo.Tickets(status_id);
CREATE INDEX idx_tickets_priority ON dbo.Tickets(priority_id);
CREATE INDEX idx_tickets_assigned ON dbo.Tickets(assigned_to);
CREATE INDEX idx_tickets_created_by ON dbo.Tickets(created_by);
CREATE INDEX idx_tickets_created_at ON dbo.Tickets(created_at);
CREATE INDEX idx_tickets_number ON dbo.Tickets(ticket_number);

CREATE INDEX idx_ticket_messages_ticket ON dbo.TicketMessages(ticket_id);
CREATE INDEX idx_ticket_messages_created ON dbo.TicketMessages(created_at);

CREATE INDEX idx_email_inbox_message_id ON dbo.EmailInbox(message_id);
CREATE INDEX idx_email_inbox_status ON dbo.EmailInbox(processing_status);
CREATE INDEX idx_email_inbox_received ON dbo.EmailInbox(received_at);

CREATE INDEX idx_email_queue_status ON dbo.EmailQueue(status);
CREATE INDEX idx_email_queue_priority ON dbo.EmailQueue(priority, send_after);
GO

PRINT 'Indexes created successfully';
GO

-- Insert default data
INSERT INTO dbo.TicketStatuses (name, display_name, color_code, is_closed, sort_order) VALUES
('new', 'New', '#007bff', 0, 1),
('open', 'Open', '#28a745', 0, 2),
('in_progress', 'In Progress', '#ffc107', 0, 3),
('pending', 'Pending Customer', '#fd7e14', 0, 4),
('resolved', 'Resolved', '#6f42c1', 1, 5),
('closed', 'Closed', '#6c757d', 1, 6),
('cancelled', 'Cancelled', '#dc3545', 1, 7);

INSERT INTO dbo.TicketPriorities (name, display_name, color_code, level, sort_order) VALUES
('lowest', 'Lowest', '#6c757d', 1, 1),
('low', 'Low', '#17a2b8', 2, 2),
('normal', 'Normal', '#28a745', 3, 3),
('high', 'High', '#ffc107', 4, 4),
('critical', 'Critical', '#dc3545', 5, 5);

INSERT INTO dbo.TicketCategories (name, description, color_code, sla_hours) VALUES
('general', 'General Inquiries', '#007bff', 24),
('technical', 'Technical Support', '#dc3545', 4),
('billing', 'Billing and Payments', '#28a745', 48),
('feature_request', 'Feature Requests', '#6f42c1', 168),
('bug_report', 'Bug Reports', '#fd7e14', 8),
('hardware', 'Hardware Issues', '#e83e8c', 2),
('software', 'Software Issues', '#20c997', 4),
('network', 'Network Issues', '#fd7e14', 1);
GO

PRINT 'Default data inserted successfully';
GO

-- Create trigger to auto-generate ticket numbers
CREATE TRIGGER tr_tickets_generate_number
ON dbo.Tickets
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;
    
    UPDATE dbo.Tickets 
    SET ticket_number = 'TKT-' + FORMAT(YEAR(GETDATE()), '0000') + '-' + FORMAT(id, '000000')
    WHERE id IN (SELECT id FROM inserted) AND ticket_number IS NULL;
END;
GO

-- Create trigger to update timestamps
CREATE TRIGGER tr_tickets_update_timestamp
ON dbo.Tickets
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    UPDATE dbo.Tickets 
    SET updated_at = GETDATE()
    WHERE id IN (SELECT id FROM inserted);
END;
GO

PRINT 'Triggers created successfully';
GO

-- Create TicketDashboard view
CREATE VIEW dbo.TicketDashboard AS
SELECT 
    t.id,
    t.ticket_number,
    t.subject,
    t.created_at,
    t.updated_at,
    t.due_date,
    
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
    
    -- Message count (simplified for now)
    0 as message_count

FROM dbo.Tickets t
LEFT JOIN dbo.TicketStatuses ts ON t.status_id = ts.id
LEFT JOIN dbo.TicketPriorities tp ON t.priority_id = tp.id  
LEFT JOIN dbo.TicketCategories tc ON t.category_id = tc.id
LEFT JOIN dbo.Users creator ON t.created_by = creator.id
LEFT JOIN dbo.Users assignee ON t.assigned_to = assignee.id;
GO

PRINT 'View created successfully';
GO

-- Re-enable foreign key constraints
EXEC sp_msforeachtable "ALTER TABLE ? CHECK CONSTRAINT all"
GO

PRINT 'Ticketing system database setup completed successfully!';
GO