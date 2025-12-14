-- =============================================
-- Function: Create ProjectTracker Database
-- Description: Creates the ProjectTracker database if it doesn't exist
-- =============================================

-- Check if database exists, create if not
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = 'ProjectTracker')
BEGIN
    CREATE DATABASE [ProjectTracker]
    PRINT 'ProjectTracker database created successfully.'
END
ELSE
BEGIN
    PRINT 'ProjectTracker database already exists.'
END
GO

-- Use the ProjectTracker database
USE [ProjectTracker]
GO
/****** Object:  User [ProjectTracker]    Script Date: 23/09/2025 13:45:15 ******/
CREATE USER [ProjectTracker] FOR LOGIN [ProjectTracker] WITH DEFAULT_SCHEMA=[dbo]
GO
ALTER ROLE [db_accessadmin] ADD MEMBER [ProjectTracker]
GO
ALTER ROLE [db_ddladmin] ADD MEMBER [ProjectTracker]
GO
ALTER ROLE [db_datareader] ADD MEMBER [ProjectTracker]
GO
ALTER ROLE [db_datawriter] ADD MEMBER [ProjectTracker]
GO
/****** Object:  UserDefinedFunction [dbo].[fn_UserHasEnhancedPermission]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE FUNCTION [dbo].[fn_UserHasEnhancedPermission]
(
    @UserId INT,
    @PermissionName NVARCHAR(100),
    @ResourceType NVARCHAR(50) = NULL,
    @ResourceId INT = NULL
)
RETURNS BIT
AS
BEGIN
    DECLARE @HasPermission BIT = 0;
    
    -- Check resource-specific permissions first
    IF @ResourceType IS NOT NULL AND @ResourceId IS NOT NULL
    BEGIN
        SELECT @HasPermission = 1
        FROM [dbo].[ResourcePermissions]
        WHERE [user_id] = @UserId 
        AND [permission_name] = @PermissionName 
        AND [resource_type] = @ResourceType 
        AND [resource_id] = @ResourceId
        AND [granted] = 1
        AND ([expires_at] IS NULL OR [expires_at] > GETDATE());
        
        IF @HasPermission = 1
            RETURN @HasPermission;
    END
    
    -- Check standard permissions
    SELECT @HasPermission = 1
    FROM [dbo].[vw_UserEffectivePermissions]
    WHERE [user_id] = @UserId 
    AND [permission_name] = @PermissionName;
    
    RETURN ISNULL(@HasPermission, 0);
END

GO
/****** Object:  Table [dbo].[TimeEntries]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TimeEntries](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[clock_in_time] [datetime] NOT NULL,
	[clock_out_time] [datetime] NULL,
	[total_hours] [decimal](8, 2) NULL,
	[total_break_minutes] [int] NULL,
	[notes] [nvarchar](500) NULL,
	[status] [nvarchar](20) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[site_id] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Users]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Users](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[username] [nvarchar](50) NOT NULL,
	[password] [nvarchar](255) NOT NULL,
	[email] [nvarchar](100) NULL,
	[full_name] [nvarchar](100) NULL,
	[role] [nvarchar](20) NULL,
	[is_active] [bit] NULL,
	[created_at] [datetime] NULL,
	[last_login] [datetime] NULL,
	[position] [nvarchar](100) NULL,
	[bio] [nvarchar](max) NULL,
	[profile_picture] [nvarchar](255) NULL,
	[role_id] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
UNIQUE NONCLUSTERED 
(
	[username] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  View [dbo].[DailyTimeSummary]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Create a view for daily time summaries
CREATE VIEW [dbo].[DailyTimeSummary] AS
SELECT 
    te.user_id,
    u.username,
    u.full_name,
    CAST(te.clock_in_time AS DATE) as work_date,
    te.id as time_entry_id,
    te.clock_in_time,
    te.clock_out_time,
    te.total_hours,
    te.total_break_minutes,
    te.status,
    CASE 
        WHEN te.clock_out_time IS NULL THEN 
            DATEDIFF(MINUTE, te.clock_in_time, GETDATE()) - ISNULL(te.total_break_minutes, 0)
        ELSE 
            DATEDIFF(MINUTE, te.clock_in_time, te.clock_out_time) - ISNULL(te.total_break_minutes, 0)
    END as net_work_minutes
FROM dbo.TimeEntries te
INNER JOIN dbo.Users u ON te.user_id = u.id;
GO
/****** Object:  Table [dbo].[TimeBreaks]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TimeBreaks](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[time_entry_id] [int] NOT NULL,
	[break_start] [datetime] NOT NULL,
	[break_end] [datetime] NULL,
	[break_duration_minutes] [int] NULL,
	[break_type] [nvarchar](50) NULL,
	[notes] [nvarchar](255) NULL,
	[created_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  View [dbo].[BreakSummary]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Create a view for break summaries
CREATE VIEW [dbo].[BreakSummary] AS
SELECT 
    tb.time_entry_id,
    tb.break_type,
    COUNT(*) as break_count,
    SUM(tb.break_duration_minutes) as total_break_minutes
FROM dbo.TimeBreaks tb
WHERE tb.break_end IS NOT NULL
GROUP BY tb.time_entry_id, tb.break_type;
GO
/****** Object:  Table [dbo].[TicketCategories]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketCategories](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) NOT NULL,
	[description] [nvarchar](500) NULL,
	[color_code] [nvarchar](7) NULL,
	[parent_category_id] [int] NULL,
	[auto_assign_to] [int] NULL,
	[sla_hours] [int] NULL,
	[is_active] [bit] NULL,
	[created_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
UNIQUE NONCLUSTERED 
(
	[name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Clients]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Clients](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[contact_person] [nvarchar](255) NULL,
	[email] [nvarchar](255) NULL,
	[phone] [nvarchar](50) NULL,
	[address] [nvarchar](max) NULL,
	[industry] [nvarchar](100) NULL,
	[status] [nvarchar](50) NULL,
	[notes] [nvarchar](max) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ClientEmailDomains]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ClientEmailDomains](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[client_id] [int] NOT NULL,
	[domain] [nvarchar](255) NOT NULL,
	[domain_type] [nvarchar](20) NULL,
	[priority] [int] NULL,
	[is_active] [bit] NULL,
	[auto_assign_category_id] [int] NULL,
	[notes] [nvarchar](500) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[created_by] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [UK_ClientEmailDomains_Domain_Type] UNIQUE NONCLUSTERED 
(
	[domain] ASC,
	[domain_type] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  View [dbo].[ClientDomainLookup]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Create a view for easy domain lookup
CREATE VIEW [dbo].[ClientDomainLookup] AS
SELECT 
    ced.id,
    ced.domain,
    ced.domain_type,
    ced.priority,
    ced.is_active,
    ced.auto_assign_category_id,
    c.id as client_id,
    c.name as client_name,
    c.contact_person,
    c.email as client_email,
    c.status as client_status,
    tc.name as category_name
FROM dbo.ClientEmailDomains ced
INNER JOIN dbo.Clients c ON ced.client_id = c.id
LEFT JOIN dbo.TicketCategories tc ON ced.auto_assign_category_id = tc.id
WHERE ced.is_active = 1 AND c.status = 'Active';
GO
/****** Object:  Table [dbo].[Roles]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Roles](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](50) NOT NULL,
	[display_name] [nvarchar](100) NOT NULL,
	[description] [nvarchar](255) NULL,
	[is_active] [bit] NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
UNIQUE NONCLUSTERED 
(
	[name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Permissions]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Permissions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) NOT NULL,
	[display_name] [nvarchar](150) NOT NULL,
	[description] [nvarchar](255) NULL,
	[module] [nvarchar](50) NOT NULL,
	[action] [nvarchar](50) NOT NULL,
	[is_active] [bit] NULL,
	[created_at] [datetime] NULL,
	[resource_type] [nvarchar](50) NULL,
	[is_conditional] [bit] NOT NULL,
	[conditions] [nvarchar](max) NULL,
	[priority] [int] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
UNIQUE NONCLUSTERED 
(
	[name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[RolePermissions]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[RolePermissions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[role_id] [int] NOT NULL,
	[permission_id] [int] NOT NULL,
	[created_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
UNIQUE NONCLUSTERED 
(
	[role_id] ASC,
	[permission_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[UserPermissions]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[UserPermissions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[permission_id] [int] NOT NULL,
	[granted] [bit] NULL,
	[created_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
UNIQUE NONCLUSTERED 
(
	[user_id] ASC,
	[permission_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ResourcePermissions]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ResourcePermissions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[permission_name] [nvarchar](100) NOT NULL,
	[resource_type] [nvarchar](50) NOT NULL,
	[resource_id] [int] NOT NULL,
	[granted] [bit] NOT NULL,
	[expires_at] [datetime2](7) NULL,
	[conditions] [nvarchar](max) NULL,
	[granted_by] [int] NULL,
	[created_at] [datetime2](7) NOT NULL,
	[updated_at] [datetime2](7) NOT NULL,
 CONSTRAINT [PK_ResourcePermissions] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  View [dbo].[vw_UserEffectivePermissions]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE VIEW [dbo].[vw_UserEffectivePermissions] AS
SELECT DISTINCT 
    u.[id] as [user_id],
    u.[username],
    p.[name] as [permission_name],
    p.[display_name],
    p.[module],
    p.[action],
    p.[resource_type],
    'role' as [permission_source],
    NULL as [resource_id],
    NULL as [expires_at]
FROM [dbo].[Users] u
INNER JOIN [dbo].[Roles] r ON u.[role_id] = r.[id]
INNER JOIN [dbo].[RolePermissions] rp ON r.[id] = rp.[role_id]
INNER JOIN [dbo].[Permissions] p ON rp.[permission_id] = p.[id]
WHERE r.[is_active] = 1 AND p.[is_active] = 1

UNION ALL

SELECT DISTINCT 
    u.[id] as [user_id],
    u.[username],
    p.[name] as [permission_name],
    p.[display_name],
    p.[module],
    p.[action],
    p.[resource_type],
    'direct' as [permission_source],
    NULL as [resource_id],
    NULL as [expires_at]
FROM [dbo].[Users] u
INNER JOIN [dbo].[UserPermissions] up ON u.[id] = up.[user_id]
INNER JOIN [dbo].[Permissions] p ON up.[permission_id] = p.[id]
WHERE up.[granted] = 1 AND p.[is_active] = 1

UNION ALL

SELECT DISTINCT 
    u.[id] as [user_id],
    u.[username],
    rp.[permission_name],
    p.[display_name],
    p.[module],
    p.[action],
    p.[resource_type],
    'resource' as [permission_source],
    rp.[resource_id],
    rp.[expires_at]
FROM [dbo].[Users] u
INNER JOIN [dbo].[ResourcePermissions] rp ON u.[id] = rp.[user_id]
LEFT JOIN [dbo].[Permissions] p ON rp.[permission_name] = p.[name]
WHERE rp.[granted] = 1 AND (rp.[expires_at] IS NULL OR rp.[expires_at] > GETDATE());

GO
/****** Object:  Table [dbo].[TicketStatuses]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketStatuses](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](50) NOT NULL,
	[display_name] [nvarchar](100) NOT NULL,
	[color_code] [nvarchar](7) NULL,
	[is_closed] [bit] NULL,
	[sort_order] [int] NULL,
	[is_active] [bit] NULL,
	[created_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
UNIQUE NONCLUSTERED 
(
	[name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TicketPriorities]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketPriorities](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](50) NOT NULL,
	[display_name] [nvarchar](100) NOT NULL,
	[color_code] [nvarchar](7) NULL,
	[level] [int] NULL,
	[sort_order] [int] NULL,
	[is_active] [bit] NULL,
	[created_at] [datetime] NULL,
	[response_time_hours] [int] NULL,
	[resolution_time_hours] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
UNIQUE NONCLUSTERED 
(
	[name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Tickets]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Tickets](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[ticket_number] [nvarchar](20) NULL,
	[subject] [nvarchar](255) NOT NULL,
	[description] [nvarchar](max) NULL,
	[status_id] [int] NOT NULL,
	[priority_id] [int] NOT NULL,
	[category_id] [int] NULL,
	[created_by] [int] NOT NULL,
	[assigned_to] [int] NULL,
	[client_id] [int] NULL,
	[email_thread_id] [nvarchar](255) NULL,
	[inbound_email_address] [nvarchar](255) NULL,
	[original_message_id] [nvarchar](500) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[due_date] [datetime] NULL,
	[resolved_at] [datetime] NULL,
	[closed_at] [datetime] NULL,
	[first_response_at] [datetime] NULL,
	[source] [nvarchar](50) NULL,
	[tags] [nvarchar](500) NULL,
	[is_internal] [bit] NULL,
	[project_id] [int] NULL,
	[task_id] [int] NULL,
	[sla_response_deadline] [datetime] NULL,
	[sla_resolution_deadline] [datetime] NULL,
	[sla_response_breached] [bit] NULL,
	[sla_resolution_breached] [bit] NULL,
	[sla_response_breached_at] [datetime] NULL,
	[sla_resolution_breached_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TicketMessages]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketMessages](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[ticket_id] [int] NOT NULL,
	[user_id] [int] NULL,
	[message_type] [nvarchar](50) NULL,
	[subject] [nvarchar](255) NULL,
	[content] [nvarchar](max) NOT NULL,
	[content_format] [nvarchar](20) NULL,
	[email_message_id] [nvarchar](500) NULL,
	[email_from] [nvarchar](255) NULL,
	[email_to] [nvarchar](255) NULL,
	[email_cc] [nvarchar](500) NULL,
	[email_headers] [nvarchar](max) NULL,
	[is_public] [bit] NULL,
	[is_system_message] [bit] NULL,
	[created_at] [datetime] NULL,
	[email_sent_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO

/****** Object:  Table [dbo].[TicketAttachments]    Script Date: 14/12/2025 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketAttachments](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[ticket_id] [int] NOT NULL,
	[ticket_message_id] [int] NULL,
	[ms_message_id] [nvarchar](500) NULL,
	[ms_attachment_id] [nvarchar](255) NULL,
	[content_id] [nvarchar](255) NULL,
	[filename] [nvarchar](255) NOT NULL,
	[original_filename] [nvarchar](255) NOT NULL,
	[file_path] [nvarchar](500) NULL,
	[file_size] [bigint] NOT NULL,
	[mime_type] [nvarchar](100) NOT NULL,
	[is_inline] [bit] NULL,
	[is_downloaded] [bit] NULL,
	[download_error] [nvarchar](500) NULL,
	[created_at] [datetime] NULL,
	[downloaded_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  View [dbo].[TicketDashboard]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER OFF
GO

-- Update TicketDashboard view to include client information
CREATE VIEW [dbo].[TicketDashboard] AS
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
    
    -- Client info
    c.name as client_name,
    c.industry as client_industry,
    
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
LEFT JOIN dbo.Clients c ON t.client_id = c.id
LEFT JOIN dbo.Users creator ON t.created_by = creator.id
LEFT JOIN dbo.Users assignee ON t.assigned_to = assignee.id;
GO
/****** Object:  Table [dbo].[activity_logs]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[activity_logs](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[entity_type] [nvarchar](50) NOT NULL,
	[entity_id] [int] NOT NULL,
	[action] [nvarchar](50) NOT NULL,
	[description] [nvarchar](max) NULL,
	[metadata] [nvarchar](max) NULL,
	[created_at] [datetime] NULL,
	[ip_address] [nvarchar](45) NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[BreakTypes]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[BreakTypes](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](50) NOT NULL,
	[max_duration_minutes] [int] NULL,
	[is_paid] [bit] NULL,
	[color_code] [nvarchar](7) NULL,
	[is_active] [bit] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[calendar_events]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[calendar_events](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[calendar_id] [int] NOT NULL,
	[event_uid] [nvarchar](255) NOT NULL,
	[title] [nvarchar](255) NOT NULL,
	[description] [nvarchar](max) NULL,
	[location] [nvarchar](255) NULL,
	[start_time] [datetime2](7) NOT NULL,
	[end_time] [datetime2](7) NOT NULL,
	[all_day] [bit] NOT NULL,
	[recurrence_rule] [nvarchar](max) NULL,
	[created_at] [datetime2](7) NOT NULL,
	[updated_at] [datetime2](7) NOT NULL,
	[url] [varchar](1000) NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [UQ_calendar_events_uid] UNIQUE NONCLUSTERED 
(
	[calendar_id] ASC,
	[event_uid] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ClientDomains]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ClientDomains](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[client_id] [int] NOT NULL,
	[domain] [nvarchar](255) NOT NULL,
	[is_primary] [bit] NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [UQ_ClientDomains_Domain] UNIQUE NONCLUSTERED 
(
	[domain] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[ContextualPermissions]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[ContextualPermissions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[context_type] [nvarchar](50) NOT NULL,
	[context_id] [int] NOT NULL,
	[permission_name] [nvarchar](100) NOT NULL,
	[granted] [bit] NOT NULL,
	[created_at] [datetime2](7) NOT NULL,
 CONSTRAINT [PK_ContextualPermissions] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[daily_activities]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[daily_activities](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[activity_date] [date] NOT NULL,
	[description] [nvarchar](max) NULL,
	[check_in] [datetime] NULL,
	[check_out] [datetime] NULL,
	[total_hours] [decimal](5, 2) NULL,
	[status] [nvarchar](20) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[departments]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[departments](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) NOT NULL,
	[description] [nvarchar](max) NULL,
	[budget] [decimal](15, 2) NOT NULL,
	[created_at] [datetime] NOT NULL,
	[updated_at] [datetime] NULL,
	[currency] [nvarchar](3) NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[EmailQueue]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[EmailQueue](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[to_address] [nvarchar](255) NOT NULL,
	[cc_address] [nvarchar](500) NULL,
	[bcc_address] [nvarchar](500) NULL,
	[subject] [nvarchar](500) NOT NULL,
	[body_text] [nvarchar](max) NULL,
	[body_html] [nvarchar](max) NULL,
	[ticket_id] [int] NULL,
	[message_id] [int] NULL,
	[template_name] [nvarchar](100) NULL,
	[status] [nvarchar](50) NULL,
	[priority] [int] NULL,
	[attempts] [int] NULL,
	[max_attempts] [int] NULL,
	[error_message] [nvarchar](500) NULL,
	[created_at] [datetime] NULL,
	[send_after] [datetime] NULL,
	[sent_at] [datetime] NULL,
	[last_attempt_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[employee_documents]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[employee_documents](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[file_name] [nvarchar](255) NOT NULL,
	[file_path] [nvarchar](1000) NOT NULL,
	[file_type] [nvarchar](50) NOT NULL,
	[file_size] [int] NOT NULL,
	[document_type] [nvarchar](50) NULL,
	[description] [nvarchar](255) NULL,
	[uploaded_by] [int] NOT NULL,
	[uploaded_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[EmployeeAbsence]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[EmployeeAbsence](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[start_date] [date] NOT NULL,
	[end_date] [date] NOT NULL,
	[reason] [nvarchar](255) NULL,
	[approved_by] [int] NULL,
	[approved_at] [datetime] NULL,
	[created_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[EmployeeManagement]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[EmployeeManagement](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[performance_rating] [decimal](3, 2) NULL,
	[tasks_completed] [int] NULL,
	[tasks_pending] [int] NULL,
	[last_absence_start] [date] NULL,
	[last_absence_end] [date] NULL,
	[total_absence_days] [int] NULL,
	[notes] [nvarchar](max) NULL,
	[last_review_date] [date] NULL,
	[next_review_date] [date] NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[EmployeePerformanceNotes]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[EmployeePerformanceNotes](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[note_text] [nvarchar](max) NOT NULL,
	[note_type] [nvarchar](50) NULL,
	[created_by] [int] NOT NULL,
	[created_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[EmployeeRatingHistory]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[EmployeeRatingHistory](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[old_rating] [decimal](3, 2) NULL,
	[new_rating] [decimal](3, 2) NOT NULL,
	[notes] [nvarchar](max) NULL,
	[changed_by] [int] NOT NULL,
	[changed_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[EmployeeSites]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[EmployeeSites](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[site_id] [int] NOT NULL,
	[role] [nvarchar](50) NULL,
	[assignment_date] [datetime] NULL,
	[end_date] [datetime] NULL,
	[is_primary] [bit] NULL,
	[notes] [nvarchar](255) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[external_calendars]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[external_calendars](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[name] [nvarchar](255) NOT NULL,
	[source] [nvarchar](10) NOT NULL,
	[source_id] [nvarchar](512) NOT NULL,
	[color] [nvarchar](20) NOT NULL,
	[auto_refresh] [bit] NOT NULL,
	[access_token] [nvarchar](max) NULL,
	[refresh_token] [nvarchar](max) NULL,
	[token_expires] [datetime2](7) NULL,
	[last_synced] [datetime2](7) NULL,
	[active] [bit] NOT NULL,
	[created_at] [datetime2](7) NOT NULL,
	[updated_at] [datetime2](7) NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[FieldPermissions]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[FieldPermissions](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[module] [nvarchar](50) NOT NULL,
	[field_name] [nvarchar](100) NOT NULL,
	[access_level] [nvarchar](20) NOT NULL,
	[resource_id] [int] NULL,
	[created_at] [datetime2](7) NOT NULL,
 CONSTRAINT [PK_FieldPermissions] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[invoice_documents]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[invoice_documents](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[invoice_id] [int] NOT NULL,
	[file_name] [nvarchar](255) NOT NULL,
	[file_path] [nvarchar](1000) NOT NULL,
	[file_type] [nvarchar](50) NOT NULL,
	[file_size] [int] NOT NULL,
	[uploaded_by] [int] NOT NULL,
	[uploaded_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Invoices]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Invoices](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[invoice_number] [nvarchar](50) NOT NULL,
	[supplier_id] [int] NOT NULL,
	[invoice_date] [date] NOT NULL,
	[due_date] [date] NULL,
	[total_amount] [decimal](18, 2) NOT NULL,
	[status] [nvarchar](20) NULL,
	[payment_date] [date] NULL,
	[payment_reference] [nvarchar](100) NULL,
	[notes] [nvarchar](max) NULL,
	[created_by] [int] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[note_shares]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[note_shares](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[note_id] [int] NOT NULL,
	[shared_with_user_id] [int] NOT NULL,
	[shared_by_user_id] [int] NOT NULL,
	[permission] [nvarchar](20) NULL,
	[shared_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [UQ_note_shares] UNIQUE NONCLUSTERED 
(
	[note_id] ASC,
	[shared_with_user_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Notes]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Notes](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[title] [nvarchar](200) NOT NULL,
	[content] [nvarchar](max) NOT NULL,
	[type] [nvarchar](50) NOT NULL,
	[reference_id] [int] NULL,
	[created_by] [int] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PermissionGroupMembers]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PermissionGroupMembers](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[group_id] [int] NOT NULL,
	[permission_id] [int] NOT NULL,
	[created_at] [datetime2](7) NOT NULL,
 CONSTRAINT [PK_PermissionGroupMembers] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [UQ_PermissionGroupMembers] UNIQUE NONCLUSTERED 
(
	[group_id] ASC,
	[permission_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[PermissionGroups]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[PermissionGroups](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) NOT NULL,
	[display_name] [nvarchar](150) NOT NULL,
	[description] [nvarchar](255) NULL,
	[module] [nvarchar](50) NOT NULL,
	[is_active] [bit] NOT NULL,
	[created_at] [datetime2](7) NOT NULL,
 CONSTRAINT [PK_PermissionGroups] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [UQ_PermissionGroups_Name] UNIQUE NONCLUSTERED 
(
	[name] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[project_documents]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[project_documents](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[project_id] [int] NOT NULL,
	[file_name] [nvarchar](255) NOT NULL,
	[file_path] [nvarchar](1000) NOT NULL,
	[file_type] [nvarchar](50) NOT NULL,
	[file_size] [int] NOT NULL,
	[document_type] [nvarchar](50) NULL,
	[description] [nvarchar](255) NULL,
	[uploaded_by] [int] NOT NULL,
	[uploaded_at] [datetime] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[project_sites]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[project_sites](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[project_id] [int] NOT NULL,
	[site_id] [int] NOT NULL,
	[link_date] [datetime] NULL,
	[notes] [nvarchar](500) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [uk_project_site] UNIQUE NONCLUSTERED 
(
	[project_id] ASC,
	[site_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[project_team_members]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[project_team_members](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[project_id] [int] NOT NULL,
	[user_id] [int] NOT NULL,
	[role] [nvarchar](50) NULL,
	[added_at] [datetime] NULL,
	[added_by] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [UQ_project_team_members] UNIQUE NONCLUSTERED 
(
	[project_id] ASC,
	[user_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[project_users]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[project_users](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[project_id] [int] NOT NULL,
	[user_id] [int] NOT NULL,
	[role] [nvarchar](50) NULL,
	[created_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [UQ_ProjectUsers] UNIQUE NONCLUSTERED 
(
	[project_id] ASC,
	[user_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[projects]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[projects](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[title] [nvarchar](100) NOT NULL,
	[description] [nvarchar](max) NULL,
	[start_date] [date] NOT NULL,
	[end_date] [date] NULL,
	[status] [nvarchar](20) NOT NULL,
	[user_id] [int] NOT NULL,
	[created_at] [datetime] NOT NULL,
	[updated_at] [datetime] NULL,
	[budget] [decimal](15, 2) NOT NULL,
	[department_id] [int] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[settings]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[settings](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[setting_key] [nvarchar](255) NOT NULL,
	[setting_value] [nvarchar](max) NULL,
	[setting_scope] [nvarchar](50) NOT NULL,
	[user_id] [int] NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [uk_setting] UNIQUE NONCLUSTERED 
(
	[setting_key] ASC,
	[setting_scope] ASC,
	[user_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[SiteClients]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[SiteClients](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[site_id] [int] NOT NULL,
	[client_id] [int] NOT NULL,
	[relationship_type] [nvarchar](50) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [UQ_SiteClient] UNIQUE NONCLUSTERED 
(
	[site_id] ASC,
	[client_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Sites]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Sites](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) NOT NULL,
	[location] [nvarchar](200) NULL,
	[address] [nvarchar](255) NULL,
	[site_code] [nvarchar](20) NULL,
	[type] [nvarchar](50) NULL,
	[status] [nvarchar](20) NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Skills]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Skills](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](100) NOT NULL,
	[description] [nvarchar](255) NULL,
	[category] [nvarchar](50) NULL,
	[created_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[SLABreachLog]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[SLABreachLog](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[ticket_id] [int] NOT NULL,
	[breach_type] [nvarchar](20) NOT NULL,
	[breached_at] [datetime] NOT NULL,
	[original_deadline] [datetime] NOT NULL,
	[actual_time] [datetime] NOT NULL,
	[hours_overdue] [decimal](10, 2) NOT NULL,
	[created_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Suppliers]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Suppliers](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [nvarchar](200) NOT NULL,
	[contact_name] [nvarchar](100) NULL,
	[email] [nvarchar](100) NULL,
	[phone] [nvarchar](50) NULL,
	[address] [nvarchar](255) NULL,
	[city] [nvarchar](100) NULL,
	[state] [nvarchar](100) NULL,
	[postal_code] [nvarchar](20) NULL,
	[country] [nvarchar](100) NULL,
	[website] [nvarchar](255) NULL,
	[notes] [nvarchar](max) NULL,
	[status] [nvarchar](20) NULL,
	[created_by] [int] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[system_logs]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[system_logs](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[type] [nvarchar](50) NOT NULL,
	[message] [nvarchar](max) NOT NULL,
	[user_id] [int] NULL,
	[user] [nvarchar](255) NULL,
	[ip_address] [nvarchar](45) NULL,
	[user_agent] [nvarchar](max) NULL,
	[additional_data] [nvarchar](max) NULL,
	[timestamp] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[task_users]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[task_users](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[task_id] [int] NOT NULL,
	[user_id] [int] NOT NULL,
	[created_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
 CONSTRAINT [UQ_task_user] UNIQUE NONCLUSTERED 
(
	[task_id] ASC,
	[user_id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[tasks]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[tasks](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[project_id] [int] NOT NULL,
	[title] [nvarchar](100) NOT NULL,
	[description] [nvarchar](max) NULL,
	[status] [nvarchar](20) NOT NULL,
	[priority] [nvarchar](10) NOT NULL,
	[due_date] [date] NULL,
	[assigned_to] [int] NULL,
	[created_by] [int] NOT NULL,
	[created_at] [datetime] NOT NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[TicketAssignments]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[TicketAssignments](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[ticket_id] [int] NOT NULL,
	[user_id] [int] NOT NULL,
	[assigned_by] [int] NOT NULL,
	[role] [nvarchar](50) NULL,
	[assigned_at] [datetime] NULL,
	[removed_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
UNIQUE NONCLUSTERED 
(
	[ticket_id] ASC,
	[user_id] ASC,
	[role] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[UserSettings]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[UserSettings](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[email_notifications] [bit] NULL,
	[task_reminders] [bit] NULL,
	[project_updates] [bit] NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[UserSkills]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[UserSkills](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_id] [int] NOT NULL,
	[skill_id] [int] NOT NULL,
	[proficiency_level] [int] NULL,
	[created_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[weekly_routers]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[weekly_routers](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[router_name] [nvarchar](100) NOT NULL,
	[router_ip] [nvarchar](45) NOT NULL,
	[location] [nvarchar](200) NOT NULL,
	[assigned_technician_id] [int] NULL,
	[week_start_date] [date] NOT NULL,
	[week_end_date] [date] NOT NULL,
	[maintenance_type] [nvarchar](50) NOT NULL,
	[priority] [nvarchar](20) NULL,
	[description] [nvarchar](max) NULL,
	[status] [nvarchar](20) NULL,
	[estimated_hours] [decimal](4, 2) NULL,
	[actual_hours] [decimal](4, 2) NULL,
	[notes] [nvarchar](max) NULL,
	[created_by] [int] NOT NULL,
	[created_at] [datetime] NULL,
	[updated_at] [datetime] NULL,
	[completed_at] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
ALTER TABLE [dbo].[activity_logs] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[BreakTypes] ADD  DEFAULT ((1)) FOR [is_paid]
GO
ALTER TABLE [dbo].[BreakTypes] ADD  DEFAULT ('#007bff') FOR [color_code]
GO
ALTER TABLE [dbo].[BreakTypes] ADD  DEFAULT ((1)) FOR [is_active]
GO
ALTER TABLE [dbo].[calendar_events] ADD  DEFAULT ((0)) FOR [all_day]
GO
ALTER TABLE [dbo].[calendar_events] ADD  DEFAULT (sysutcdatetime()) FOR [created_at]
GO
ALTER TABLE [dbo].[calendar_events] ADD  DEFAULT (sysutcdatetime()) FOR [updated_at]
GO
ALTER TABLE [dbo].[ClientDomains] ADD  DEFAULT ((0)) FOR [is_primary]
GO
ALTER TABLE [dbo].[ClientDomains] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[ClientEmailDomains] ADD  DEFAULT ('exact') FOR [domain_type]
GO
ALTER TABLE [dbo].[ClientEmailDomains] ADD  DEFAULT ((1)) FOR [priority]
GO
ALTER TABLE [dbo].[ClientEmailDomains] ADD  DEFAULT ((1)) FOR [is_active]
GO
ALTER TABLE [dbo].[ClientEmailDomains] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[ClientEmailDomains] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[Clients] ADD  DEFAULT ('Active') FOR [status]
GO
ALTER TABLE [dbo].[Clients] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[ContextualPermissions] ADD  DEFAULT ((1)) FOR [granted]
GO
ALTER TABLE [dbo].[ContextualPermissions] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[daily_activities] ADD  DEFAULT (getdate()) FOR [activity_date]
GO
ALTER TABLE [dbo].[daily_activities] ADD  DEFAULT ('Pending') FOR [status]
GO
ALTER TABLE [dbo].[daily_activities] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[departments] ADD  DEFAULT ((0)) FOR [budget]
GO
ALTER TABLE [dbo].[departments] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[departments] ADD  DEFAULT ('USD') FOR [currency]
GO
ALTER TABLE [dbo].[EmailQueue] ADD  DEFAULT ('pending') FOR [status]
GO
ALTER TABLE [dbo].[EmailQueue] ADD  DEFAULT ((5)) FOR [priority]
GO
ALTER TABLE [dbo].[EmailQueue] ADD  DEFAULT ((0)) FOR [attempts]
GO
ALTER TABLE [dbo].[EmailQueue] ADD  DEFAULT ((3)) FOR [max_attempts]
GO
ALTER TABLE [dbo].[EmailQueue] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[EmailQueue] ADD  DEFAULT (getdate()) FOR [send_after]
GO
ALTER TABLE [dbo].[employee_documents] ADD  DEFAULT (getdate()) FOR [uploaded_at]
GO
ALTER TABLE [dbo].[EmployeeAbsence] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[EmployeeManagement] ADD  DEFAULT ((0.00)) FOR [performance_rating]
GO
ALTER TABLE [dbo].[EmployeeManagement] ADD  DEFAULT ((0)) FOR [tasks_completed]
GO
ALTER TABLE [dbo].[EmployeeManagement] ADD  DEFAULT ((0)) FOR [tasks_pending]
GO
ALTER TABLE [dbo].[EmployeeManagement] ADD  DEFAULT ((0)) FOR [total_absence_days]
GO
ALTER TABLE [dbo].[EmployeeManagement] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[EmployeeManagement] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[EmployeePerformanceNotes] ADD  DEFAULT ('general') FOR [note_type]
GO
ALTER TABLE [dbo].[EmployeePerformanceNotes] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[EmployeeRatingHistory] ADD  DEFAULT (getdate()) FOR [changed_at]
GO
ALTER TABLE [dbo].[EmployeeSites] ADD  DEFAULT ('Regular Staff') FOR [role]
GO
ALTER TABLE [dbo].[EmployeeSites] ADD  DEFAULT (getdate()) FOR [assignment_date]
GO
ALTER TABLE [dbo].[EmployeeSites] ADD  DEFAULT ((1)) FOR [is_primary]
GO
ALTER TABLE [dbo].[EmployeeSites] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[EmployeeSites] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[external_calendars] ADD  DEFAULT ('#039be5') FOR [color]
GO
ALTER TABLE [dbo].[external_calendars] ADD  DEFAULT ((0)) FOR [auto_refresh]
GO
ALTER TABLE [dbo].[external_calendars] ADD  DEFAULT ((1)) FOR [active]
GO
ALTER TABLE [dbo].[external_calendars] ADD  DEFAULT (sysutcdatetime()) FOR [created_at]
GO
ALTER TABLE [dbo].[external_calendars] ADD  DEFAULT (sysutcdatetime()) FOR [updated_at]
GO
ALTER TABLE [dbo].[FieldPermissions] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Invoices] ADD  DEFAULT ('pending') FOR [status]
GO
ALTER TABLE [dbo].[Invoices] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Invoices] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[note_shares] ADD  DEFAULT ('view') FOR [permission]
GO
ALTER TABLE [dbo].[note_shares] ADD  DEFAULT (getdate()) FOR [shared_at]
GO
ALTER TABLE [dbo].[Notes] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Notes] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[PermissionGroupMembers] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[PermissionGroups] ADD  DEFAULT ((1)) FOR [is_active]
GO
ALTER TABLE [dbo].[PermissionGroups] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Permissions] ADD  DEFAULT ((1)) FOR [is_active]
GO
ALTER TABLE [dbo].[Permissions] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Permissions] ADD  DEFAULT ((0)) FOR [is_conditional]
GO
ALTER TABLE [dbo].[Permissions] ADD  DEFAULT ((0)) FOR [priority]
GO
ALTER TABLE [dbo].[project_documents] ADD  DEFAULT (getdate()) FOR [uploaded_at]
GO
ALTER TABLE [dbo].[project_sites] ADD  DEFAULT (getdate()) FOR [link_date]
GO
ALTER TABLE [dbo].[project_sites] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[project_team_members] ADD  DEFAULT ('member') FOR [role]
GO
ALTER TABLE [dbo].[project_team_members] ADD  DEFAULT (getdate()) FOR [added_at]
GO
ALTER TABLE [dbo].[project_users] ADD  DEFAULT ('Member') FOR [role]
GO
ALTER TABLE [dbo].[project_users] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[projects] ADD  DEFAULT ('Active') FOR [status]
GO
ALTER TABLE [dbo].[projects] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[projects] ADD  DEFAULT ((0)) FOR [budget]
GO
ALTER TABLE [dbo].[ResourcePermissions] ADD  DEFAULT ((1)) FOR [granted]
GO
ALTER TABLE [dbo].[ResourcePermissions] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[ResourcePermissions] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[RolePermissions] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Roles] ADD  DEFAULT ((1)) FOR [is_active]
GO
ALTER TABLE [dbo].[Roles] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Roles] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[settings] ADD  DEFAULT ('system') FOR [setting_scope]
GO
ALTER TABLE [dbo].[settings] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[settings] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[SiteClients] ADD  DEFAULT ('Standard') FOR [relationship_type]
GO
ALTER TABLE [dbo].[SiteClients] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Sites] ADD  DEFAULT ('Active') FOR [status]
GO
ALTER TABLE [dbo].[Sites] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Sites] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[Skills] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[SLABreachLog] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Suppliers] ADD  DEFAULT ('active') FOR [status]
GO
ALTER TABLE [dbo].[Suppliers] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Suppliers] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[system_logs] ADD  DEFAULT (getdate()) FOR [timestamp]
GO
ALTER TABLE [dbo].[task_users] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[tasks] ADD  DEFAULT ('Pending') FOR [status]
GO
ALTER TABLE [dbo].[tasks] ADD  DEFAULT ('Medium') FOR [priority]
GO
ALTER TABLE [dbo].[tasks] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[TicketAssignments] ADD  DEFAULT ('assignee') FOR [role]
GO
ALTER TABLE [dbo].[TicketAssignments] ADD  DEFAULT (getdate()) FOR [assigned_at]
GO
ALTER TABLE [dbo].[TicketCategories] ADD  DEFAULT ('#007bff') FOR [color_code]
GO
ALTER TABLE [dbo].[TicketCategories] ADD  DEFAULT ((24)) FOR [sla_hours]
GO
ALTER TABLE [dbo].[TicketCategories] ADD  DEFAULT ((1)) FOR [is_active]
GO
ALTER TABLE [dbo].[TicketCategories] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[TicketMessages] ADD  DEFAULT ('comment') FOR [message_type]
GO
ALTER TABLE [dbo].[TicketMessages] ADD  DEFAULT ('text') FOR [content_format]
GO
ALTER TABLE [dbo].[TicketMessages] ADD  DEFAULT ((1)) FOR [is_public]
GO
ALTER TABLE [dbo].[TicketMessages] ADD  DEFAULT ((0)) FOR [is_system_message]
GO
ALTER TABLE [dbo].[TicketMessages] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[TicketAttachments] ADD  DEFAULT ((0)) FOR [is_inline]
GO
ALTER TABLE [dbo].[TicketAttachments] ADD  DEFAULT ((0)) FOR [is_downloaded]
GO
ALTER TABLE [dbo].[TicketAttachments] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[TicketPriorities] ADD  DEFAULT ('#6c757d') FOR [color_code]
GO
ALTER TABLE [dbo].[TicketPriorities] ADD  DEFAULT ((3)) FOR [level]
GO
ALTER TABLE [dbo].[TicketPriorities] ADD  DEFAULT ((0)) FOR [sort_order]
GO
ALTER TABLE [dbo].[TicketPriorities] ADD  DEFAULT ((1)) FOR [is_active]
GO
ALTER TABLE [dbo].[TicketPriorities] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[TicketPriorities] ADD  DEFAULT ((24)) FOR [response_time_hours]
GO
ALTER TABLE [dbo].[TicketPriorities] ADD  DEFAULT ((72)) FOR [resolution_time_hours]
GO
ALTER TABLE [dbo].[Tickets] ADD  DEFAULT ((1)) FOR [status_id]
GO
ALTER TABLE [dbo].[Tickets] ADD  DEFAULT ((3)) FOR [priority_id]
GO
ALTER TABLE [dbo].[Tickets] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Tickets] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[Tickets] ADD  DEFAULT ('web') FOR [source]
GO
ALTER TABLE [dbo].[Tickets] ADD  DEFAULT ((0)) FOR [is_internal]
GO
ALTER TABLE [dbo].[Tickets] ADD  DEFAULT ((0)) FOR [sla_response_breached]
GO
ALTER TABLE [dbo].[Tickets] ADD  DEFAULT ((0)) FOR [sla_resolution_breached]
GO
ALTER TABLE [dbo].[TicketStatuses] ADD  DEFAULT ('#6c757d') FOR [color_code]
GO
ALTER TABLE [dbo].[TicketStatuses] ADD  DEFAULT ((0)) FOR [is_closed]
GO
ALTER TABLE [dbo].[TicketStatuses] ADD  DEFAULT ((0)) FOR [sort_order]
GO
ALTER TABLE [dbo].[TicketStatuses] ADD  DEFAULT ((1)) FOR [is_active]
GO
ALTER TABLE [dbo].[TicketStatuses] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[TimeBreaks] ADD  DEFAULT ('regular') FOR [break_type]
GO
ALTER TABLE [dbo].[TimeBreaks] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[TimeEntries] ADD  DEFAULT ((0)) FOR [total_break_minutes]
GO
ALTER TABLE [dbo].[TimeEntries] ADD  DEFAULT ('active') FOR [status]
GO
ALTER TABLE [dbo].[TimeEntries] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[TimeEntries] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[UserPermissions] ADD  DEFAULT ((1)) FOR [granted]
GO
ALTER TABLE [dbo].[UserPermissions] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[Users] ADD  DEFAULT ('user') FOR [role]
GO
ALTER TABLE [dbo].[Users] ADD  DEFAULT ((1)) FOR [is_active]
GO
ALTER TABLE [dbo].[Users] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[UserSettings] ADD  DEFAULT ((1)) FOR [email_notifications]
GO
ALTER TABLE [dbo].[UserSettings] ADD  DEFAULT ((1)) FOR [task_reminders]
GO
ALTER TABLE [dbo].[UserSettings] ADD  DEFAULT ((1)) FOR [project_updates]
GO
ALTER TABLE [dbo].[UserSettings] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[UserSettings] ADD  DEFAULT (getdate()) FOR [updated_at]
GO
ALTER TABLE [dbo].[UserSkills] ADD  DEFAULT ((1)) FOR [proficiency_level]
GO
ALTER TABLE [dbo].[UserSkills] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[weekly_routers] ADD  DEFAULT ('Medium') FOR [priority]
GO
ALTER TABLE [dbo].[weekly_routers] ADD  DEFAULT ('Scheduled') FOR [status]
GO
ALTER TABLE [dbo].[weekly_routers] ADD  DEFAULT (getdate()) FOR [created_at]
GO
ALTER TABLE [dbo].[activity_logs]  WITH NOCHECK ADD  CONSTRAINT [FK_activity_logs_users] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[activity_logs] CHECK CONSTRAINT [FK_activity_logs_users]
GO
ALTER TABLE [dbo].[calendar_events]  WITH NOCHECK ADD  CONSTRAINT [FK_calendar_events_calendars] FOREIGN KEY([calendar_id])
REFERENCES [dbo].[external_calendars] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[calendar_events] CHECK CONSTRAINT [FK_calendar_events_calendars]
GO
ALTER TABLE [dbo].[ClientDomains]  WITH CHECK ADD  CONSTRAINT [FK_ClientDomains_Clients] FOREIGN KEY([client_id])
REFERENCES [dbo].[Clients] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[ClientDomains] CHECK CONSTRAINT [FK_ClientDomains_Clients]
GO
ALTER TABLE [dbo].[ClientEmailDomains]  WITH CHECK ADD  CONSTRAINT [FK_ClientEmailDomains_Category] FOREIGN KEY([auto_assign_category_id])
REFERENCES [dbo].[TicketCategories] ([id])
ON DELETE SET NULL
GO
ALTER TABLE [dbo].[ClientEmailDomains] CHECK CONSTRAINT [FK_ClientEmailDomains_Category]
GO
ALTER TABLE [dbo].[ClientEmailDomains]  WITH CHECK ADD  CONSTRAINT [FK_ClientEmailDomains_Client] FOREIGN KEY([client_id])
REFERENCES [dbo].[Clients] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[ClientEmailDomains] CHECK CONSTRAINT [FK_ClientEmailDomains_Client]
GO
ALTER TABLE [dbo].[ClientEmailDomains]  WITH CHECK ADD  CONSTRAINT [FK_ClientEmailDomains_CreatedBy] FOREIGN KEY([created_by])
REFERENCES [dbo].[Users] ([id])
ON DELETE SET NULL
GO
ALTER TABLE [dbo].[ClientEmailDomains] CHECK CONSTRAINT [FK_ClientEmailDomains_CreatedBy]
GO
ALTER TABLE [dbo].[ContextualPermissions]  WITH NOCHECK ADD  CONSTRAINT [FK_ContextualPermissions_Users] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[ContextualPermissions] CHECK CONSTRAINT [FK_ContextualPermissions_Users]
GO
ALTER TABLE [dbo].[daily_activities]  WITH NOCHECK ADD  CONSTRAINT [fk_daily_activities_user] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[daily_activities] CHECK CONSTRAINT [fk_daily_activities_user]
GO
ALTER TABLE [dbo].[employee_documents]  WITH NOCHECK ADD  CONSTRAINT [FK_employee_documents_uploader] FOREIGN KEY([uploaded_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[employee_documents] CHECK CONSTRAINT [FK_employee_documents_uploader]
GO
ALTER TABLE [dbo].[employee_documents]  WITH NOCHECK ADD  CONSTRAINT [FK_employee_documents_users] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[employee_documents] CHECK CONSTRAINT [FK_employee_documents_users]
GO
ALTER TABLE [dbo].[EmployeeAbsence]  WITH NOCHECK ADD FOREIGN KEY([approved_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[EmployeeAbsence]  WITH NOCHECK ADD FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[EmployeeManagement]  WITH NOCHECK ADD FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[EmployeePerformanceNotes]  WITH NOCHECK ADD  CONSTRAINT [fk_performance_notes_creator] FOREIGN KEY([created_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[EmployeePerformanceNotes] CHECK CONSTRAINT [fk_performance_notes_creator]
GO
ALTER TABLE [dbo].[EmployeePerformanceNotes]  WITH NOCHECK ADD  CONSTRAINT [fk_performance_notes_user] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[EmployeePerformanceNotes] CHECK CONSTRAINT [fk_performance_notes_user]
GO
ALTER TABLE [dbo].[EmployeeRatingHistory]  WITH NOCHECK ADD FOREIGN KEY([changed_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[EmployeeRatingHistory]  WITH NOCHECK ADD FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[EmployeeSites]  WITH NOCHECK ADD  CONSTRAINT [FK_EmployeeSites_Sites] FOREIGN KEY([site_id])
REFERENCES [dbo].[Sites] ([id])
GO
ALTER TABLE [dbo].[EmployeeSites] CHECK CONSTRAINT [FK_EmployeeSites_Sites]
GO
ALTER TABLE [dbo].[EmployeeSites]  WITH NOCHECK ADD  CONSTRAINT [FK_EmployeeSites_Users] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[EmployeeSites] CHECK CONSTRAINT [FK_EmployeeSites_Users]
GO
ALTER TABLE [dbo].[external_calendars]  WITH NOCHECK ADD  CONSTRAINT [FK_external_calendars_users] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[external_calendars] CHECK CONSTRAINT [FK_external_calendars_users]
GO
ALTER TABLE [dbo].[FieldPermissions]  WITH NOCHECK ADD  CONSTRAINT [FK_FieldPermissions_Users] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[FieldPermissions] CHECK CONSTRAINT [FK_FieldPermissions_Users]
GO
ALTER TABLE [dbo].[invoice_documents]  WITH NOCHECK ADD FOREIGN KEY([invoice_id])
REFERENCES [dbo].[Invoices] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[invoice_documents]  WITH NOCHECK ADD FOREIGN KEY([uploaded_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[Invoices]  WITH NOCHECK ADD  CONSTRAINT [FK_Invoices_Suppliers] FOREIGN KEY([supplier_id])
REFERENCES [dbo].[Suppliers] ([id])
GO
ALTER TABLE [dbo].[Invoices] CHECK CONSTRAINT [FK_Invoices_Suppliers]
GO
ALTER TABLE [dbo].[Invoices]  WITH NOCHECK ADD  CONSTRAINT [FK_Invoices_Users] FOREIGN KEY([created_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[Invoices] CHECK CONSTRAINT [FK_Invoices_Users]
GO
ALTER TABLE [dbo].[note_shares]  WITH NOCHECK ADD  CONSTRAINT [FK_note_shares_notes] FOREIGN KEY([note_id])
REFERENCES [dbo].[Notes] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[note_shares] CHECK CONSTRAINT [FK_note_shares_notes]
GO
ALTER TABLE [dbo].[note_shares]  WITH NOCHECK ADD  CONSTRAINT [FK_note_shares_shared_by] FOREIGN KEY([shared_by_user_id])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[note_shares] CHECK CONSTRAINT [FK_note_shares_shared_by]
GO
ALTER TABLE [dbo].[note_shares]  WITH NOCHECK ADD  CONSTRAINT [FK_note_shares_shared_with] FOREIGN KEY([shared_with_user_id])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[note_shares] CHECK CONSTRAINT [FK_note_shares_shared_with]
GO
ALTER TABLE [dbo].[Notes]  WITH NOCHECK ADD  CONSTRAINT [FK_Notes_Users] FOREIGN KEY([created_by])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[Notes] CHECK CONSTRAINT [FK_Notes_Users]
GO
ALTER TABLE [dbo].[PermissionGroupMembers]  WITH NOCHECK ADD  CONSTRAINT [FK_PermissionGroupMembers_Groups] FOREIGN KEY([group_id])
REFERENCES [dbo].[PermissionGroups] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[PermissionGroupMembers] CHECK CONSTRAINT [FK_PermissionGroupMembers_Groups]
GO
ALTER TABLE [dbo].[PermissionGroupMembers]  WITH NOCHECK ADD  CONSTRAINT [FK_PermissionGroupMembers_Permissions] FOREIGN KEY([permission_id])
REFERENCES [dbo].[Permissions] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[PermissionGroupMembers] CHECK CONSTRAINT [FK_PermissionGroupMembers_Permissions]
GO
ALTER TABLE [dbo].[project_documents]  WITH NOCHECK ADD  CONSTRAINT [FK_project_documents_projects] FOREIGN KEY([project_id])
REFERENCES [dbo].[projects] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[project_documents] CHECK CONSTRAINT [FK_project_documents_projects]
GO
ALTER TABLE [dbo].[project_documents]  WITH NOCHECK ADD  CONSTRAINT [FK_project_documents_uploader] FOREIGN KEY([uploaded_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[project_documents] CHECK CONSTRAINT [FK_project_documents_uploader]
GO
ALTER TABLE [dbo].[project_sites]  WITH NOCHECK ADD  CONSTRAINT [fk_project_sites_project] FOREIGN KEY([project_id])
REFERENCES [dbo].[projects] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[project_sites] CHECK CONSTRAINT [fk_project_sites_project]
GO
ALTER TABLE [dbo].[project_sites]  WITH NOCHECK ADD  CONSTRAINT [fk_project_sites_site] FOREIGN KEY([site_id])
REFERENCES [dbo].[Sites] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[project_sites] CHECK CONSTRAINT [fk_project_sites_site]
GO
ALTER TABLE [dbo].[project_team_members]  WITH NOCHECK ADD  CONSTRAINT [FK_project_team_members_added_by] FOREIGN KEY([added_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[project_team_members] CHECK CONSTRAINT [FK_project_team_members_added_by]
GO
ALTER TABLE [dbo].[project_team_members]  WITH NOCHECK ADD  CONSTRAINT [FK_project_team_members_projects] FOREIGN KEY([project_id])
REFERENCES [dbo].[projects] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[project_team_members] CHECK CONSTRAINT [FK_project_team_members_projects]
GO
ALTER TABLE [dbo].[project_team_members]  WITH NOCHECK ADD  CONSTRAINT [FK_project_team_members_users] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[project_team_members] CHECK CONSTRAINT [FK_project_team_members_users]
GO
ALTER TABLE [dbo].[project_users]  WITH NOCHECK ADD  CONSTRAINT [FK_ProjectUsers_Projects] FOREIGN KEY([project_id])
REFERENCES [dbo].[projects] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[project_users] CHECK CONSTRAINT [FK_ProjectUsers_Projects]
GO
ALTER TABLE [dbo].[project_users]  WITH NOCHECK ADD  CONSTRAINT [FK_ProjectUsers_Users] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[project_users] CHECK CONSTRAINT [FK_ProjectUsers_Users]
GO
ALTER TABLE [dbo].[projects]  WITH NOCHECK ADD FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[ResourcePermissions]  WITH NOCHECK ADD  CONSTRAINT [FK_ResourcePermissions_GrantedBy] FOREIGN KEY([granted_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[ResourcePermissions] CHECK CONSTRAINT [FK_ResourcePermissions_GrantedBy]
GO
ALTER TABLE [dbo].[ResourcePermissions]  WITH NOCHECK ADD  CONSTRAINT [FK_ResourcePermissions_Users] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[ResourcePermissions] CHECK CONSTRAINT [FK_ResourcePermissions_Users]
GO
ALTER TABLE [dbo].[RolePermissions]  WITH NOCHECK ADD FOREIGN KEY([permission_id])
REFERENCES [dbo].[Permissions] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[RolePermissions]  WITH NOCHECK ADD FOREIGN KEY([role_id])
REFERENCES [dbo].[Roles] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[SiteClients]  WITH NOCHECK ADD  CONSTRAINT [FK_SiteClients_Clients] FOREIGN KEY([client_id])
REFERENCES [dbo].[Clients] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[SiteClients] CHECK CONSTRAINT [FK_SiteClients_Clients]
GO
ALTER TABLE [dbo].[SiteClients]  WITH NOCHECK ADD  CONSTRAINT [FK_SiteClients_Sites] FOREIGN KEY([site_id])
REFERENCES [dbo].[Sites] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[SiteClients] CHECK CONSTRAINT [FK_SiteClients_Sites]
GO
ALTER TABLE [dbo].[SLABreachLog]  WITH CHECK ADD FOREIGN KEY([ticket_id])
REFERENCES [dbo].[Tickets] ([id])
GO
ALTER TABLE [dbo].[Suppliers]  WITH NOCHECK ADD  CONSTRAINT [FK_Suppliers_Users] FOREIGN KEY([created_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[Suppliers] CHECK CONSTRAINT [FK_Suppliers_Users]
GO
ALTER TABLE [dbo].[task_users]  WITH NOCHECK ADD  CONSTRAINT [FK_task_users_task] FOREIGN KEY([task_id])
REFERENCES [dbo].[tasks] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[task_users] CHECK CONSTRAINT [FK_task_users_task]
GO
ALTER TABLE [dbo].[task_users]  WITH NOCHECK ADD  CONSTRAINT [FK_task_users_user] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[task_users] CHECK CONSTRAINT [FK_task_users_user]
GO
ALTER TABLE [dbo].[tasks]  WITH NOCHECK ADD FOREIGN KEY([assigned_to])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[tasks]  WITH NOCHECK ADD FOREIGN KEY([created_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[tasks]  WITH NOCHECK ADD FOREIGN KEY([project_id])
REFERENCES [dbo].[projects] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[TicketAssignments]  WITH CHECK ADD FOREIGN KEY([assigned_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[TicketAssignments]  WITH CHECK ADD FOREIGN KEY([ticket_id])
REFERENCES [dbo].[Tickets] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[TicketAssignments]  WITH CHECK ADD FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[TicketCategories]  WITH CHECK ADD  CONSTRAINT [FK_TicketCategories_AutoAssign] FOREIGN KEY([auto_assign_to])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[TicketCategories] CHECK CONSTRAINT [FK_TicketCategories_AutoAssign]
GO
ALTER TABLE [dbo].[TicketCategories]  WITH CHECK ADD  CONSTRAINT [FK_TicketCategories_Parent] FOREIGN KEY([parent_category_id])
REFERENCES [dbo].[TicketCategories] ([id])
GO
ALTER TABLE [dbo].[TicketCategories] CHECK CONSTRAINT [FK_TicketCategories_Parent]
GO
ALTER TABLE [dbo].[TicketMessages]  WITH CHECK ADD FOREIGN KEY([ticket_id])
REFERENCES [dbo].[Tickets] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[TicketMessages]  WITH CHECK ADD FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[TicketAttachments]  WITH CHECK ADD  CONSTRAINT [FK_TicketAttachments_Tickets] FOREIGN KEY([ticket_id])
REFERENCES [dbo].[Tickets] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[TicketAttachments] CHECK CONSTRAINT [FK_TicketAttachments_Tickets]
GO
ALTER TABLE [dbo].[Tickets]  WITH CHECK ADD  CONSTRAINT [FK_Tickets_AssignedTo] FOREIGN KEY([assigned_to])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[Tickets] CHECK CONSTRAINT [FK_Tickets_AssignedTo]
GO
ALTER TABLE [dbo].[Tickets]  WITH CHECK ADD  CONSTRAINT [FK_Tickets_Category] FOREIGN KEY([category_id])
REFERENCES [dbo].[TicketCategories] ([id])
GO
ALTER TABLE [dbo].[Tickets] CHECK CONSTRAINT [FK_Tickets_Category]
GO
ALTER TABLE [dbo].[Tickets]  WITH CHECK ADD  CONSTRAINT [FK_Tickets_CreatedBy] FOREIGN KEY([created_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[Tickets] CHECK CONSTRAINT [FK_Tickets_CreatedBy]
GO
ALTER TABLE [dbo].[Tickets]  WITH CHECK ADD  CONSTRAINT [FK_Tickets_Priority] FOREIGN KEY([priority_id])
REFERENCES [dbo].[TicketPriorities] ([id])
GO
ALTER TABLE [dbo].[Tickets] CHECK CONSTRAINT [FK_Tickets_Priority]
GO
ALTER TABLE [dbo].[Tickets]  WITH CHECK ADD  CONSTRAINT [FK_Tickets_Status] FOREIGN KEY([status_id])
REFERENCES [dbo].[TicketStatuses] ([id])
GO
ALTER TABLE [dbo].[Tickets] CHECK CONSTRAINT [FK_Tickets_Status]
GO
ALTER TABLE [dbo].[TimeBreaks]  WITH NOCHECK ADD FOREIGN KEY([time_entry_id])
REFERENCES [dbo].[TimeEntries] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[TimeEntries]  WITH NOCHECK ADD FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[TimeEntries]  WITH NOCHECK ADD  CONSTRAINT [FK_TimeEntries_Sites] FOREIGN KEY([site_id])
REFERENCES [dbo].[Sites] ([id])
GO
ALTER TABLE [dbo].[TimeEntries] CHECK CONSTRAINT [FK_TimeEntries_Sites]
GO
ALTER TABLE [dbo].[UserPermissions]  WITH NOCHECK ADD FOREIGN KEY([permission_id])
REFERENCES [dbo].[Permissions] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[UserPermissions]  WITH NOCHECK ADD FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[Users]  WITH NOCHECK ADD FOREIGN KEY([role_id])
REFERENCES [dbo].[Roles] ([id])
GO
ALTER TABLE [dbo].[UserSettings]  WITH NOCHECK ADD  CONSTRAINT [FK_UserSettings_Users] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[UserSettings] CHECK CONSTRAINT [FK_UserSettings_Users]
GO
ALTER TABLE [dbo].[UserSkills]  WITH NOCHECK ADD  CONSTRAINT [FK_UserSkills_Skills] FOREIGN KEY([skill_id])
REFERENCES [dbo].[Skills] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[UserSkills] CHECK CONSTRAINT [FK_UserSkills_Skills]
GO
ALTER TABLE [dbo].[UserSkills]  WITH NOCHECK ADD  CONSTRAINT [FK_UserSkills_Users] FOREIGN KEY([user_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[UserSkills] CHECK CONSTRAINT [FK_UserSkills_Users]
GO
ALTER TABLE [dbo].[weekly_routers]  WITH NOCHECK ADD  CONSTRAINT [FK_weekly_routers_creator] FOREIGN KEY([created_by])
REFERENCES [dbo].[Users] ([id])
GO
ALTER TABLE [dbo].[weekly_routers] CHECK CONSTRAINT [FK_weekly_routers_creator]
GO
ALTER TABLE [dbo].[weekly_routers]  WITH NOCHECK ADD  CONSTRAINT [FK_weekly_routers_technician] FOREIGN KEY([assigned_technician_id])
REFERENCES [dbo].[Users] ([id])
ON DELETE SET NULL
GO
ALTER TABLE [dbo].[weekly_routers] CHECK CONSTRAINT [FK_weekly_routers_technician]
GO
ALTER TABLE [dbo].[ClientEmailDomains]  WITH CHECK ADD  CONSTRAINT [CK_ClientEmailDomains_DomainType] CHECK  (([domain_type]='subdomain' OR [domain_type]='wildcard' OR [domain_type]='exact'))
GO
ALTER TABLE [dbo].[ClientEmailDomains] CHECK CONSTRAINT [CK_ClientEmailDomains_DomainType]
GO
ALTER TABLE [dbo].[ClientEmailDomains]  WITH CHECK ADD  CONSTRAINT [CK_ClientEmailDomains_Priority] CHECK  (([priority]>(0)))
GO
ALTER TABLE [dbo].[ClientEmailDomains] CHECK CONSTRAINT [CK_ClientEmailDomains_Priority]
GO
ALTER TABLE [dbo].[ContextualPermissions]  WITH NOCHECK ADD  CONSTRAINT [CK_ContextualPermissions_ContextType] CHECK  (([context_type]='site_manager' OR [context_type]='department_member' OR [context_type]='department_head' OR [context_type]='task_assignee' OR [context_type]='project_member'))
GO
ALTER TABLE [dbo].[ContextualPermissions] CHECK CONSTRAINT [CK_ContextualPermissions_ContextType]
GO
ALTER TABLE [dbo].[daily_activities]  WITH NOCHECK ADD CHECK  (([status]='Rejected' OR [status]='Approved' OR [status]='Pending'))
GO
ALTER TABLE [dbo].[external_calendars]  WITH NOCHECK ADD  CONSTRAINT [CK_external_calendars_source] CHECK  (([source]='microsoft365' OR [source]='ical' OR [source]='outlook' OR [source]='google'))
GO
ALTER TABLE [dbo].[external_calendars] CHECK CONSTRAINT [CK_external_calendars_source]
GO
ALTER TABLE [dbo].[FieldPermissions]  WITH NOCHECK ADD  CONSTRAINT [CK_FieldPermissions_AccessLevel] CHECK  (([access_level]='hidden' OR [access_level]='write' OR [access_level]='read'))
GO
ALTER TABLE [dbo].[FieldPermissions] CHECK CONSTRAINT [CK_FieldPermissions_AccessLevel]
GO
ALTER TABLE [dbo].[FieldPermissions]  WITH NOCHECK ADD  CONSTRAINT [CK_FieldPermissions_Module] CHECK  (([module]='departments' OR [module]='reports' OR [module]='invoices' OR [module]='sites' OR [module]='clients' OR [module]='users' OR [module]='tasks' OR [module]='projects'))
GO
ALTER TABLE [dbo].[FieldPermissions] CHECK CONSTRAINT [CK_FieldPermissions_Module]
GO
ALTER TABLE [dbo].[ResourcePermissions]  WITH NOCHECK ADD  CONSTRAINT [CK_ResourcePermissions_ResourceType] CHECK  (([resource_type]='invoice' OR [resource_type]='department' OR [resource_type]='user' OR [resource_type]='site' OR [resource_type]='client' OR [resource_type]='task' OR [resource_type]='project'))
GO
ALTER TABLE [dbo].[ResourcePermissions] CHECK CONSTRAINT [CK_ResourcePermissions_ResourceType]
GO
ALTER TABLE [dbo].[weekly_routers]  WITH NOCHECK ADD  CONSTRAINT [CHK_weekly_routers_maintenance_type] CHECK  (([maintenance_type]='inspection' OR [maintenance_type]='upgrade' OR [maintenance_type]='repair' OR [maintenance_type]='routine'))
GO
ALTER TABLE [dbo].[weekly_routers] CHECK CONSTRAINT [CHK_weekly_routers_maintenance_type]
GO
ALTER TABLE [dbo].[weekly_routers]  WITH NOCHECK ADD  CONSTRAINT [CHK_weekly_routers_priority] CHECK  (([priority]='Critical' OR [priority]='High' OR [priority]='Medium' OR [priority]='Low'))
GO
ALTER TABLE [dbo].[weekly_routers] CHECK CONSTRAINT [CHK_weekly_routers_priority]
GO
ALTER TABLE [dbo].[weekly_routers]  WITH NOCHECK ADD  CONSTRAINT [CHK_weekly_routers_status] CHECK  (([status]='Cancelled' OR [status]='Completed' OR [status]='In Progress' OR [status]='Scheduled'))
GO
ALTER TABLE [dbo].[weekly_routers] CHECK CONSTRAINT [CHK_weekly_routers_status]
GO
/****** Object:  StoredProcedure [dbo].[sp_CleanupExpiredPermissions]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE PROCEDURE [dbo].[sp_CleanupExpiredPermissions]
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @DeletedCount INT = 0;
    
    BEGIN TRY
        BEGIN TRANSACTION;
        
        DELETE FROM [dbo].[ResourcePermissions] 
        WHERE [expires_at] IS NOT NULL AND [expires_at] <= GETDATE();
        
        SET @DeletedCount = @@ROWCOUNT;
        
        COMMIT TRANSACTION;
        
        PRINT CAST(@DeletedCount AS NVARCHAR(10)) + ' expired permissions cleaned up.';
        
        RETURN @DeletedCount;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
            
        DECLARE @ErrorMessage NVARCHAR(4000) = ERROR_MESSAGE();
        DECLARE @ErrorSeverity INT = ERROR_SEVERITY();
        DECLARE @ErrorState INT = ERROR_STATE();
        
        RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);
        
        RETURN -1;
    END CATCH
END

GO
/****** Object:  StoredProcedure [dbo].[sp_GetClientByEmailDomain]    Script Date: 23/09/2025 13:45:15 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Create stored procedure for domain matching
CREATE PROCEDURE [dbo].[sp_GetClientByEmailDomain]
    @Email NVARCHAR(255)
AS
BEGIN
    DECLARE @Domain NVARCHAR(255);
    DECLARE @ClientId INT = NULL;
    DECLARE @CategoryId INT = NULL;
    
    -- Extract domain from email
    SET @Domain = LOWER(SUBSTRING(@Email, CHARINDEX('@', @Email) + 1, LEN(@Email)));
    
    -- Try exact domain match first (highest priority)
    SELECT TOP 1 
        @ClientId = client_id,
        @CategoryId = auto_assign_category_id
    FROM dbo.ClientDomainLookup
    WHERE domain = @Domain AND domain_type = 'exact'
    ORDER BY priority ASC;
    
    -- If no exact match, try wildcard matching
    IF @ClientId IS NULL
    BEGIN
        SELECT TOP 1 
            @ClientId = client_id,
            @CategoryId = auto_assign_category_id
        FROM dbo.ClientDomainLookup
        WHERE domain_type = 'wildcard' 
        AND @Domain LIKE REPLACE(domain, '*', '%')
        ORDER BY priority ASC;
    END
    
    -- If no wildcard match, try subdomain matching
    IF @ClientId IS NULL
    BEGIN
        SELECT TOP 1 
            @ClientId = client_id,
            @CategoryId = auto_assign_category_id
        FROM dbo.ClientDomainLookup
        WHERE domain_type = 'subdomain' 
        AND @Domain LIKE '%.' + domain
        ORDER BY priority ASC;
    END
    
    -- Return result
    SELECT 
        @ClientId as client_id,
        @CategoryId as category_id,
        @Domain as matched_domain;
END;
GO

-- =============================================
-- Default Data Setup
-- Description: Insert default roles, permissions, and admin user for initial system setup
-- =============================================

PRINT 'Starting default data insertion...';

-- Insert default roles
PRINT 'Creating default roles...';
INSERT INTO [dbo].[Roles] ([name], [display_name], [description], [is_active], [created_at], [updated_at])
VALUES 
    ('admin', 'System Administrator', 'Full system access with all permissions', 1, GETDATE(), GETDATE()),
    ('manager', 'Manager', 'Management level access for overseeing operations', 1, GETDATE(), GETDATE()),
    ('user', 'Standard User', 'Basic user access for regular operations', 1, GETDATE(), GETDATE()),
    ('technician', 'Technician', 'Technical staff with field access', 1, GETDATE(), GETDATE()),
    ('client', 'Client User', 'Limited access for client portal', 1, GETDATE(), GETDATE());

-- Insert comprehensive permissions for the system
PRINT 'Creating comprehensive permissions for super admin...';
INSERT INTO [dbo].[Permissions] ([name], [display_name], [description], [module], [action], [is_active], [created_at], [is_conditional], [priority])
VALUES 
    -- Dashboard and UI
    ('dashboard.access', 'Access Dashboard', 'Access to main dashboard', 'dashboard', 'access', 1, GETDATE(), 0, 1),
    ('calendar.access', 'Access Calendar', 'Access to calendar view', 'calendar', 'access', 1, GETDATE(), 0, 2),
    ('gantt.access', 'Access Gantt Chart', 'Access to Gantt chart view', 'gantt', 'access', 1, GETDATE(), 0, 3),
    
    -- User Management
    ('users.view', 'View Users', 'Can view user profiles and lists', 'users', 'view', 1, GETDATE(), 0, 10),
    ('users.create', 'Create Users', 'Can create new user accounts', 'users', 'create', 1, GETDATE(), 0, 11),
    ('users.edit', 'Edit Users', 'Can modify user accounts', 'users', 'edit', 1, GETDATE(), 0, 12),
    ('users.update', 'Update Users', 'Can update user accounts', 'users', 'update', 1, GETDATE(), 0, 13),
    ('users.delete', 'Delete Users', 'Can delete user accounts', 'users', 'delete', 1, GETDATE(), 0, 14),
    ('users.manage_permissions', 'Manage User Permissions', 'Can assign permissions to users', 'users', 'manage_permissions', 1, GETDATE(), 0, 15),
    ('users.manage', 'Manage Users', 'Full user management capabilities', 'users', 'manage', 1, GETDATE(), 0, 16),
    
    -- Project Management
    ('projects.view', 'View Projects', 'Can view projects', 'projects', 'view', 1, GETDATE(), 0, 20),
    ('projects.read', 'Read Projects', 'Can read project information', 'projects', 'read', 1, GETDATE(), 0, 21),
    ('projects.create', 'Create Projects', 'Can create new projects', 'projects', 'create', 1, GETDATE(), 0, 22),
    ('projects.edit', 'Edit Projects', 'Can modify projects', 'projects', 'edit', 1, GETDATE(), 0, 23),
    ('projects.update', 'Update Projects', 'Can update projects', 'projects', 'update', 1, GETDATE(), 0, 24),
    ('projects.delete', 'Delete Projects', 'Can delete projects', 'projects', 'delete', 1, GETDATE(), 0, 25),
    ('projects.manage', 'Manage Projects', 'Full project management capabilities', 'projects', 'manage', 1, GETDATE(), 0, 26),
    
    -- Task Management
    ('tasks.view', 'View Tasks', 'Can view tasks', 'tasks', 'view', 1, GETDATE(), 0, 30),
    ('tasks.read', 'Read Tasks', 'Can read task information', 'tasks', 'read', 1, GETDATE(), 0, 31),
    ('tasks.create', 'Create Tasks', 'Can create new tasks', 'tasks', 'create', 1, GETDATE(), 0, 32),
    ('tasks.edit', 'Edit Tasks', 'Can modify tasks', 'tasks', 'edit', 1, GETDATE(), 0, 33),
    ('tasks.update', 'Update Tasks', 'Can update tasks', 'tasks', 'update', 1, GETDATE(), 0, 34),
    ('tasks.delete', 'Delete Tasks', 'Can delete tasks', 'tasks', 'delete', 1, GETDATE(), 0, 35),
    ('tasks.assign', 'Assign Tasks', 'Can assign tasks to users', 'tasks', 'assign', 1, GETDATE(), 0, 36),
    ('tasks.manage', 'Manage Tasks', 'Full task management capabilities', 'tasks', 'manage', 1, GETDATE(), 0, 37),
    
    -- Ticket Management
    ('tickets.view', 'View Tickets', 'Can view support tickets', 'tickets', 'view', 1, GETDATE(), 0, 40),
    ('tickets.read', 'Read Tickets', 'Can read ticket information', 'tickets', 'read', 1, GETDATE(), 0, 41),
    ('tickets.create', 'Create Tickets', 'Can create new tickets', 'tickets', 'create', 1, GETDATE(), 0, 42),
    ('tickets.edit', 'Edit Tickets', 'Can modify tickets', 'tickets', 'edit', 1, GETDATE(), 0, 43),
    ('tickets.update', 'Update Tickets', 'Can update tickets', 'tickets', 'update', 1, GETDATE(), 0, 44),
    ('tickets.delete', 'Delete Tickets', 'Can delete tickets', 'tickets', 'delete', 1, GETDATE(), 0, 45),
    ('tickets.assign', 'Assign Tickets', 'Can assign tickets to users', 'tickets', 'assign', 1, GETDATE(), 0, 46),
    ('tickets.close', 'Close Tickets', 'Can close tickets', 'tickets', 'close', 1, GETDATE(), 0, 47),
    ('tickets.manage', 'Manage Tickets', 'Full ticket management capabilities', 'tickets', 'manage', 1, GETDATE(), 0, 48),
    
    -- Client Management
    ('clients.view', 'View Clients', 'Can view client information', 'clients', 'view', 1, GETDATE(), 0, 50),
    ('clients.read', 'Read Clients', 'Can read client information', 'clients', 'read', 1, GETDATE(), 0, 51),
    ('clients.create', 'Create Clients', 'Can create new clients', 'clients', 'create', 1, GETDATE(), 0, 52),
    ('clients.edit', 'Edit Clients', 'Can modify client information', 'clients', 'edit', 1, GETDATE(), 0, 53),
    ('clients.update', 'Update Clients', 'Can update client information', 'clients', 'update', 1, GETDATE(), 0, 54),
    ('clients.delete', 'Delete Clients', 'Can delete clients', 'clients', 'delete', 1, GETDATE(), 0, 55),
    ('clients.assign_sites', 'Assign Sites to Clients', 'Can assign sites to clients', 'clients', 'assign_sites', 1, GETDATE(), 0, 56),
    ('clients.manage', 'Manage Clients', 'Full client management capabilities', 'clients', 'manage', 1, GETDATE(), 0, 57),
    
    -- Time Management
    ('time.view', 'View Time Entries', 'Can view time tracking data', 'time', 'view', 1, GETDATE(), 0, 60),
    ('time.access', 'Access Time Tracking', 'Access to time tracking system', 'time', 'access', 1, GETDATE(), 0, 61),
    ('time.create', 'Create Time Entries', 'Can create time entries', 'time', 'create', 1, GETDATE(), 0, 62),
    ('time.edit', 'Edit Time Entries', 'Can modify time entries', 'time', 'edit', 1, GETDATE(), 0, 63),
    ('time.update', 'Update Time Entries', 'Can update time entries', 'time', 'update', 1, GETDATE(), 0, 64),
    ('time.delete', 'Delete Time Entries', 'Can delete time entries', 'time', 'delete', 1, GETDATE(), 0, 65),
    ('time.admin', 'Time Tracking Admin', 'Administrative access to time tracking', 'time', 'admin', 1, GETDATE(), 0, 66),
    ('time.reports', 'Time Tracking Reports', 'Access to time tracking reports and analytics', 'time', 'reports', 1, GETDATE(), 0, 67),
    
    -- Email Management
    ('email.inbox', 'Email Inbox Access', 'Access to email inbox', 'email', 'inbox', 1, GETDATE(), 0, 70),
    ('email.manage', 'Manage Email', 'Can manage email system', 'email', 'manage', 1, GETDATE(), 0, 71),
    ('email.delete', 'Delete Email', 'Can delete emails', 'email', 'delete', 1, GETDATE(), 0, 72),
    
    -- Notes Module
    ('notes.view', 'View Notes', 'Can view notes', 'notes', 'view', 1, GETDATE(), 0, 75),
    ('notes.read', 'Read Notes', 'Can read notes', 'notes', 'read', 1, GETDATE(), 0, 76),
    ('notes.create', 'Create Notes', 'Can create new notes', 'notes', 'create', 1, GETDATE(), 0, 77),
    ('notes.edit', 'Edit Notes', 'Can modify notes', 'notes', 'edit', 1, GETDATE(), 0, 78),
    ('notes.update', 'Update Notes', 'Can update notes', 'notes', 'update', 1, GETDATE(), 0, 79),
    ('notes.delete', 'Delete Notes', 'Can delete notes', 'notes', 'delete', 1, GETDATE(), 0, 80),
    
    -- Suppliers Module
    ('suppliers.view', 'View Suppliers', 'Can view supplier information', 'suppliers', 'view', 1, GETDATE(), 0, 85),
    ('suppliers.read', 'Read Suppliers', 'Can read supplier information', 'suppliers', 'read', 1, GETDATE(), 0, 86),
    ('suppliers.create', 'Create Suppliers', 'Can create new suppliers', 'suppliers', 'create', 1, GETDATE(), 0, 87),
    ('suppliers.edit', 'Edit Suppliers', 'Can modify supplier information', 'suppliers', 'edit', 1, GETDATE(), 0, 88),
    ('suppliers.update', 'Update Suppliers', 'Can update supplier information', 'suppliers', 'update', 1, GETDATE(), 0, 89),
    ('suppliers.delete', 'Delete Suppliers', 'Can delete suppliers', 'suppliers', 'delete', 1, GETDATE(), 0, 90),
    
    -- Sites Module
    ('sites.view', 'View Sites', 'Can view site information', 'sites', 'view', 1, GETDATE(), 0, 95),
    ('sites.read', 'Read Sites', 'Can read site information', 'sites', 'read', 1, GETDATE(), 0, 96),
    ('sites.create', 'Create Sites', 'Can create new sites', 'sites', 'create', 1, GETDATE(), 0, 97),
    ('sites.edit', 'Edit Sites', 'Can modify site information', 'sites', 'edit', 1, GETDATE(), 0, 98),
    ('sites.update', 'Update Sites', 'Can update site information', 'sites', 'update', 1, GETDATE(), 0, 99),
    ('sites.delete', 'Delete Sites', 'Can delete sites', 'sites', 'delete', 1, GETDATE(), 0, 100),
    
    -- Invoices Module
    ('invoices.view', 'View Invoices', 'Can view invoices', 'invoices', 'view', 1, GETDATE(), 0, 105),
    ('invoices.read', 'Read Invoices', 'Can read invoice information', 'invoices', 'read', 1, GETDATE(), 0, 106),
    ('invoices.create', 'Create Invoices', 'Can create new invoices', 'invoices', 'create', 1, GETDATE(), 0, 107),
    ('invoices.edit', 'Edit Invoices', 'Can modify invoices', 'invoices', 'edit', 1, GETDATE(), 0, 108),
    ('invoices.update', 'Update Invoices', 'Can update invoices', 'invoices', 'update', 1, GETDATE(), 0, 109),
    ('invoices.delete', 'Delete Invoices', 'Can delete invoices', 'invoices', 'delete', 1, GETDATE(), 0, 110),
    
    -- Employee Management
    ('employees.view', 'View Employees', 'Can view employee information', 'employees', 'view', 1, GETDATE(), 0, 115),
    ('employees.read', 'Read Employees', 'Can read employee information', 'employees', 'read', 1, GETDATE(), 0, 116),
    ('employees.create', 'Create Employees', 'Can create new employee records', 'employees', 'create', 1, GETDATE(), 0, 117),
    ('employees.edit', 'Edit Employees', 'Can modify employee information', 'employees', 'edit', 1, GETDATE(), 0, 118),
    ('employees.update', 'Update Employees', 'Can update employee information', 'employees', 'update', 1, GETDATE(), 0, 119),
    ('employees.delete', 'Delete Employees', 'Can delete employee records', 'employees', 'delete', 1, GETDATE(), 0, 120),
    
    -- Departments Module
    ('departments.view', 'View Departments', 'Can view departments', 'departments', 'view', 1, GETDATE(), 0, 125),
    ('departments.read', 'Read Departments', 'Can read department information', 'departments', 'read', 1, GETDATE(), 0, 126),
    ('departments.create', 'Create Departments', 'Can create new departments', 'departments', 'create', 1, GETDATE(), 0, 127),
    ('departments.edit', 'Edit Departments', 'Can modify departments', 'departments', 'edit', 1, GETDATE(), 0, 128),
    ('departments.update', 'Update Departments', 'Can update departments', 'departments', 'update', 1, GETDATE(), 0, 129),
    ('departments.delete', 'Delete Departments', 'Can delete departments', 'departments', 'delete', 1, GETDATE(), 0, 130),
    
    -- Reports & Analytics
    ('reports.view', 'View Reports', 'Can view system reports', 'reports', 'view', 1, GETDATE(), 0, 135),
    ('reports.read', 'Read Reports', 'Can read reports', 'reports', 'read', 1, GETDATE(), 0, 136),
    ('reports.export', 'Export Reports', 'Can export report data', 'reports', 'export', 1, GETDATE(), 0, 137),
    ('reports.advanced', 'Advanced Reports', 'Access to advanced reporting features', 'reports', 'advanced', 1, GETDATE(), 0, 138),
    
    -- System Administration
    ('admin.access', 'Admin Access', 'Can access admin dashboard and functions', 'admin', 'access', 1, GETDATE(), 0, 140),
    ('admin.system_settings', 'System Settings', 'Can modify system settings', 'admin', 'system_settings', 1, GETDATE(), 0, 141),
    ('admin.view_logs', 'View System Logs', 'Can view system activity logs', 'admin', 'view_logs', 1, GETDATE(), 0, 142),
    ('admin.manage_roles', 'Manage Roles', 'Can create and modify user roles', 'admin', 'manage_roles', 1, GETDATE(), 0, 143),
    ('admin.permissions', 'Manage Permissions', 'Can manage system permissions and role assignments', 'admin', 'permissions', 1, GETDATE(), 0, 144);

-- Assign all permissions to admin role
PRINT 'Assigning permissions to admin role...';
INSERT INTO [dbo].[RolePermissions] ([role_id], [permission_id], [created_at])
SELECT r.id, p.id, GETDATE() 
FROM [dbo].[Roles] r, [dbo].[Permissions] p 
WHERE r.name = 'admin' AND p.is_active = 1;

-- Insert default ticket statuses
PRINT 'Creating default ticket statuses...';
INSERT INTO [dbo].[TicketStatuses] ([name], [display_name], [color_code], [is_closed], [sort_order], [is_active], [created_at])
VALUES 
    ('new', 'New', '#007bff', 0, 1, 1, GETDATE()),
    ('open', 'Open', '#28a745', 0, 2, 1, GETDATE()),
    ('in_progress', 'In Progress', '#ffc107', 0, 3, 1, GETDATE()),
    ('pending', 'Pending', '#6f42c1', 0, 4, 1, GETDATE()),
    ('resolved', 'Resolved', '#17a2b8', 1, 5, 1, GETDATE()),
    ('closed', 'Closed', '#6c757d', 1, 6, 1, GETDATE());

-- Insert default ticket priorities
PRINT 'Creating default ticket priorities...';
INSERT INTO [dbo].[TicketPriorities] ([name], [display_name], [color_code], [level], [sort_order], [is_active], [created_at], [response_time_hours], [resolution_time_hours])
VALUES 
    ('low', 'Low Priority', '#6c757d', 1, 1, 1, GETDATE(), 48, 168),
    ('normal', 'Normal Priority', '#007bff', 2, 2, 1, GETDATE(), 24, 72),
    ('medium', 'Medium Priority', '#ffc107', 3, 3, 1, GETDATE(), 12, 48),
    ('high', 'High Priority', '#fd7e14', 4, 4, 1, GETDATE(), 4, 24),
    ('critical', 'Critical Priority', '#dc3545', 5, 5, 1, GETDATE(), 1, 8);

-- Create default admin user
PRINT 'Creating default admin user...';
INSERT INTO [dbo].[Users] ([username], [password], [email], [full_name], [role], [is_active], [created_at], [role_id], [position])
SELECT 
    'admin',
    -- Default password: 'admin123' (stored as plain text for compatibility with current auth system)
    -- IMPORTANT: This should be changed immediately after first login!
    -- TODO: Implement proper password hashing in the application
    'admin123',
    'admin@projecttracker.local',
    'System Administrator',
    'admin',
    1,
    GETDATE(),
    r.id,
    'System Administrator'
FROM [dbo].[Roles] r WHERE r.name = 'admin';

-- Create some default ticket categories
PRINT 'Creating default ticket categories...';
INSERT INTO [dbo].[TicketCategories] ([name], [description], [color_code], [sla_hours], [is_active], [created_at])
VALUES 
    ('General Support', 'General support requests and inquiries', '#007bff', 24, 1, GETDATE()),
    ('Technical Issue', 'Technical problems and bug reports', '#dc3545', 12, 1, GETDATE()),
    ('Feature Request', 'New feature requests and enhancements', '#28a745', 72, 1, GETDATE()),
    ('Account Support', 'Account-related issues and requests', '#ffc107', 24, 1, GETDATE()),
    ('System Maintenance', 'System maintenance and updates', '#6f42c1', 48, 1, GETDATE());

PRINT 'Default data insertion completed successfully!';
PRINT '';
PRINT '=== IMPORTANT SECURITY NOTICE ===';
PRINT 'Default admin user created:';
PRINT 'Username: admin';
PRINT 'Password: admin123';
PRINT 'Email: admin@projecttracker.local';
PRINT '';
PRINT 'PLEASE CHANGE THE DEFAULT PASSWORD IMMEDIATELY AFTER FIRST LOGIN!';
PRINT '=== END SECURITY NOTICE ===';
PRINT '';

GO
