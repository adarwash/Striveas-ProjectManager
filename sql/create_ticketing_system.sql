-- Comprehensive Ticketing System with Email Integration
-- SQL Server Implementation for ProjectTracker
USE ProjectTracker;
GO

-- Drop existing tables if they exist (in correct order for foreign keys)
IF OBJECT_ID('dbo.TicketAttachments', 'U') IS NOT NULL DROP TABLE dbo.TicketAttachments;
IF OBJECT_ID('dbo.TicketAssignments', 'U') IS NOT NULL DROP TABLE dbo.TicketAssignments;
IF OBJECT_ID('dbo.TicketLabels', 'U') IS NOT NULL DROP TABLE dbo.TicketLabels;
IF OBJECT_ID('dbo.TicketMessages', 'U') IS NOT NULL DROP TABLE dbo.TicketMessages;
IF OBJECT_ID('dbo.EmailQueue', 'U') IS NOT NULL DROP TABLE dbo.EmailQueue;
IF OBJECT_ID('dbo.EmailInbox', 'U') IS NOT NULL DROP TABLE dbo.EmailInbox;
IF OBJECT_ID('dbo.Tickets', 'U') IS NOT NULL DROP TABLE dbo.Tickets;
IF OBJECT_ID('dbo.TicketCategories', 'U') IS NOT NULL DROP TABLE dbo.TicketCategories;
IF OBJECT_ID('dbo.TicketPriorities', 'U') IS NOT NULL DROP TABLE dbo.TicketPriorities;
IF OBJECT_ID('dbo.TicketStatuses', 'U') IS NOT NULL DROP TABLE dbo.TicketStatuses;
GO

-- Create Ticket Statuses lookup table
CREATE TABLE dbo.TicketStatuses (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(50) NOT NULL UNIQUE,
    display_name NVARCHAR(100) NOT NULL,
    color_code NVARCHAR(7) DEFAULT '#6c757d', -- Bootstrap color codes
    is_closed BIT DEFAULT 0, -- Whether this status means ticket is closed
    sort_order INT DEFAULT 0,
    is_active BIT DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE()
);

-- Create Ticket Priorities lookup table  
CREATE TABLE dbo.TicketPriorities (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(50) NOT NULL UNIQUE,
    display_name NVARCHAR(100) NOT NULL,
    color_code NVARCHAR(7) DEFAULT '#6c757d',
    level INT DEFAULT 3, -- 1=Lowest, 2=Low, 3=Normal, 4=High, 5=Critical
    sort_order INT DEFAULT 0,
    is_active BIT DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE()
);

-- Create Ticket Categories lookup table
CREATE TABLE dbo.TicketCategories (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(100) NOT NULL UNIQUE,
    description NVARCHAR(500) NULL,
    color_code NVARCHAR(7) DEFAULT '#007bff',
    parent_category_id INT NULL, -- For subcategories
    auto_assign_to INT NULL, -- Auto-assign tickets in this category to specific user
    sla_hours INT DEFAULT 24, -- Service Level Agreement in hours
    is_active BIT DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE()
);

-- Main Tickets table
CREATE TABLE dbo.Tickets (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_number NVARCHAR(20) NOT NULL UNIQUE, -- e.g., TKT-2024-001234
    subject NVARCHAR(255) NOT NULL,
    description NVARCHAR(MAX),
    
    -- Status and Priority
    status_id INT NOT NULL DEFAULT 1,
    priority_id INT NOT NULL DEFAULT 3,
    category_id INT NULL,
    
    -- People involved
    created_by INT NOT NULL, -- User who created the ticket
    assigned_to INT NULL, -- Primary assignee
    client_id INT NULL, -- If ticket is for a specific client
    
    -- Email integration fields
    email_thread_id NVARCHAR(255) NULL, -- For tracking email conversations
    inbound_email_address NVARCHAR(255) NULL, -- Original email that created ticket
    original_message_id NVARCHAR(500) NULL, -- Email Message-ID header
    
    -- Dates and timeline
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    due_date DATETIME NULL,
    resolved_at DATETIME NULL,
    closed_at DATETIME NULL,
    first_response_at DATETIME NULL, -- SLA tracking
    
    -- Metadata
    source NVARCHAR(50) DEFAULT 'web', -- 'web', 'email', 'api', 'phone'
    tags NVARCHAR(500) NULL, -- Comma-separated tags
    is_internal BIT DEFAULT 0, -- Internal IT ticket vs client ticket
    
    -- Relationships
    project_id INT NULL, -- Link to project if applicable
    task_id INT NULL, -- Link to task if applicable
    
    FOREIGN KEY (status_id) REFERENCES dbo.TicketStatuses(id),
    FOREIGN KEY (priority_id) REFERENCES dbo.TicketPriorities(id),
    FOREIGN KEY (category_id) REFERENCES dbo.TicketCategories(id),
    FOREIGN KEY (created_by) REFERENCES dbo.Users(id),
    FOREIGN KEY (assigned_to) REFERENCES dbo.Users(id)
    -- Note: Uncomment these if Projects and Tasks tables exist:
    -- FOREIGN KEY (project_id) REFERENCES dbo.Projects(id),
    -- FOREIGN KEY (task_id) REFERENCES dbo.Tasks(id)
);

-- Ticket Messages/Conversation table
CREATE TABLE dbo.TicketMessages (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NULL, -- NULL for system messages or external emails
    message_type NVARCHAR(50) DEFAULT 'comment', -- 'comment', 'status_change', 'assignment', 'email_inbound', 'email_outbound', 'system'
    
    -- Message content
    subject NVARCHAR(255) NULL, -- For email messages
    content NVARCHAR(MAX) NOT NULL,
    content_format NVARCHAR(20) DEFAULT 'text', -- 'text', 'html', 'markdown'
    
    -- Email-specific fields
    email_message_id NVARCHAR(500) NULL, -- Email Message-ID header
    email_from NVARCHAR(255) NULL,
    email_to NVARCHAR(255) NULL,
    email_cc NVARCHAR(500) NULL,
    email_headers NVARCHAR(MAX) NULL, -- JSON of all email headers
    
    -- Visibility and metadata
    is_public BIT DEFAULT 1, -- Visible to client (vs internal notes)
    is_system_message BIT DEFAULT 0,
    
    -- Timestamps
    created_at DATETIME DEFAULT GETDATE(),
    email_sent_at DATETIME NULL, -- When email was actually sent
    
    FOREIGN KEY (ticket_id) REFERENCES dbo.Tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES dbo.Users(id)
);

-- Ticket Assignments (for multiple assignees support)
CREATE TABLE dbo.TicketAssignments (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    assigned_by INT NOT NULL, -- Who made the assignment
    role NVARCHAR(50) DEFAULT 'assignee', -- 'assignee', 'watcher', 'collaborator'
    assigned_at DATETIME DEFAULT GETDATE(),
    removed_at DATETIME NULL, -- For tracking assignment history
    
    FOREIGN KEY (ticket_id) REFERENCES dbo.Tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES dbo.Users(id),
    FOREIGN KEY (assigned_by) REFERENCES dbo.Users(id),
    UNIQUE(ticket_id, user_id, role) -- Prevent duplicate assignments
);

-- Ticket Attachments
CREATE TABLE dbo.TicketAttachments (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NULL, -- Can be NULL for message-specific attachments
    message_id INT NULL, -- Attachment belongs to specific message
    
    -- File information
    filename NVARCHAR(255) NOT NULL,
    original_filename NVARCHAR(255) NOT NULL,
    file_path NVARCHAR(500) NOT NULL, -- Relative path to stored file
    file_size INT NOT NULL, -- Size in bytes
    mime_type NVARCHAR(100) NOT NULL,
    file_hash NVARCHAR(64) NULL, -- SHA256 hash for deduplication
    
    -- Metadata
    uploaded_by INT NOT NULL,
    uploaded_at DATETIME DEFAULT GETDATE(),
    is_inline BIT DEFAULT 0, -- Whether it's an inline email attachment
    
    FOREIGN KEY (ticket_id) REFERENCES dbo.Tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (message_id) REFERENCES dbo.TicketMessages(id) ON DELETE NO ACTION,
    FOREIGN KEY (uploaded_by) REFERENCES dbo.Users(id)
);

-- Email Inbox - for logging all email communications
CREATE TABLE dbo.EmailInbox (
    id INT IDENTITY(1,1) PRIMARY KEY,
    
    -- Email headers
    message_id NVARCHAR(500) NOT NULL UNIQUE, -- Email Message-ID header
    subject NVARCHAR(500) NOT NULL,
    from_address NVARCHAR(255) NOT NULL,
    to_address NVARCHAR(500) NOT NULL,
    cc_address NVARCHAR(500) NULL,
    bcc_address NVARCHAR(500) NULL,
    reply_to NVARCHAR(255) NULL,
    
    -- Content
    body_text NVARCHAR(MAX) NULL,
    body_html NVARCHAR(MAX) NULL,
    raw_headers NVARCHAR(MAX) NULL, -- Complete email headers as JSON
    
    -- Processing status
    processing_status NVARCHAR(50) DEFAULT 'pending', -- 'pending', 'processed', 'error', 'ignored'
    processing_error NVARCHAR(500) NULL,
    ticket_id INT NULL, -- Link to created/updated ticket
    
    -- Timestamps
    email_date DATETIME NOT NULL, -- Date from email headers
    received_at DATETIME DEFAULT GETDATE(), -- When we received/processed it
    processed_at DATETIME NULL,
    
    -- Email server details
    uid_validity INT NULL, -- IMAP UID validity
    uid INT NULL, -- IMAP UID
    flags NVARCHAR(100) NULL, -- Email flags (read, flagged, etc.)
    
    FOREIGN KEY (ticket_id) REFERENCES dbo.Tickets(id)
);

-- Email Queue - for outbound emails
CREATE TABLE dbo.EmailQueue (
    id INT IDENTITY(1,1) PRIMARY KEY,
    
    -- Email details
    to_address NVARCHAR(255) NOT NULL,
    cc_address NVARCHAR(500) NULL,
    bcc_address NVARCHAR(500) NULL,
    subject NVARCHAR(500) NOT NULL,
    body_text NVARCHAR(MAX) NULL,
    body_html NVARCHAR(MAX) NULL,
    
    -- Context
    ticket_id INT NULL,
    message_id INT NULL, -- Link to ticket message this email represents
    template_name NVARCHAR(100) NULL, -- Email template used
    
    -- Processing
    status NVARCHAR(50) DEFAULT 'pending', -- 'pending', 'sending', 'sent', 'failed', 'cancelled'
    priority INT DEFAULT 5, -- 1=highest, 10=lowest
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    error_message NVARCHAR(500) NULL,
    
    -- Timestamps
    created_at DATETIME DEFAULT GETDATE(),
    send_after DATETIME DEFAULT GETDATE(), -- Allow delayed sending
    sent_at DATETIME NULL,
    last_attempt_at DATETIME NULL,
    
    FOREIGN KEY (ticket_id) REFERENCES dbo.Tickets(id),
    FOREIGN KEY (message_id) REFERENCES dbo.TicketMessages(id)
);

-- Insert default statuses
INSERT INTO dbo.TicketStatuses (name, display_name, color_code, is_closed, sort_order) VALUES
('new', 'New', '#007bff', 0, 1),
('open', 'Open', '#28a745', 0, 2),
('in_progress', 'In Progress', '#ffc107', 0, 3),
('pending', 'Pending Customer', '#fd7e14', 0, 4),
('resolved', 'Resolved', '#6f42c1', 1, 5),
('closed', 'Closed', '#6c757d', 1, 6),
('cancelled', 'Cancelled', '#dc3545', 1, 7);

-- Insert default priorities
INSERT INTO dbo.TicketPriorities (name, display_name, color_code, level, sort_order) VALUES
('lowest', 'Lowest', '#6c757d', 1, 1),
('low', 'Low', '#17a2b8', 2, 2),
('normal', 'Normal', '#28a745', 3, 3),
('high', 'High', '#ffc107', 4, 4),
('critical', 'Critical', '#dc3545', 5, 5);

-- Insert default categories
INSERT INTO dbo.TicketCategories (name, description, color_code, sla_hours) VALUES
('general', 'General Inquiries', '#007bff', 24),
('technical', 'Technical Support', '#dc3545', 4),
('billing', 'Billing and Payments', '#28a745', 48),
('feature_request', 'Feature Requests', '#6f42c1', 168),
('bug_report', 'Bug Reports', '#fd7e14', 8),
('hardware', 'Hardware Issues', '#e83e8c', 2),
('software', 'Software Issues', '#20c997', 4),
('network', 'Network Issues', '#fd7e14', 1);

-- Create indexes for performance
CREATE INDEX idx_tickets_status ON dbo.Tickets(status_id);
CREATE INDEX idx_tickets_priority ON dbo.Tickets(priority_id);
CREATE INDEX idx_tickets_assigned ON dbo.Tickets(assigned_to);
CREATE INDEX idx_tickets_created_by ON dbo.Tickets(created_by);
CREATE INDEX idx_tickets_created_at ON dbo.Tickets(created_at);
CREATE INDEX idx_tickets_number ON dbo.Tickets(ticket_number);
CREATE INDEX idx_tickets_email_thread ON dbo.Tickets(email_thread_id);

CREATE INDEX idx_ticket_messages_ticket ON dbo.TicketMessages(ticket_id);
CREATE INDEX idx_ticket_messages_user ON dbo.TicketMessages(user_id);
CREATE INDEX idx_ticket_messages_created ON dbo.TicketMessages(created_at);
CREATE INDEX idx_ticket_messages_type ON dbo.TicketMessages(message_type);

CREATE INDEX idx_email_inbox_message_id ON dbo.EmailInbox(message_id);
CREATE INDEX idx_email_inbox_status ON dbo.EmailInbox(processing_status);
CREATE INDEX idx_email_inbox_received ON dbo.EmailInbox(received_at);
CREATE INDEX idx_email_inbox_ticket ON dbo.EmailInbox(ticket_id);

CREATE INDEX idx_email_queue_status ON dbo.EmailQueue(status);
CREATE INDEX idx_email_queue_priority ON dbo.EmailQueue(priority, send_after);
CREATE INDEX idx_email_queue_ticket ON dbo.EmailQueue(ticket_id);

-- Create trigger to auto-generate ticket numbers
GO
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

-- Create view for ticket dashboard
CREATE VIEW dbo.TicketDashboard AS
SELECT 
    t.id,
    t.ticket_number,
    t.subject,
    t.created_at,
    t.updated_at,
    t.due_date,
    -- Expose raw ids for filtering in application layer
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
GO

-- Add foreign key constraints after all tables are created
ALTER TABLE dbo.TicketCategories 
ADD CONSTRAINT FK_TicketCategories_Parent 
FOREIGN KEY (parent_category_id) REFERENCES dbo.TicketCategories(id);

ALTER TABLE dbo.TicketCategories 
ADD CONSTRAINT FK_TicketCategories_AutoAssign 
FOREIGN KEY (auto_assign_to) REFERENCES dbo.Users(id);

PRINT 'Ticketing system database schema created successfully!';