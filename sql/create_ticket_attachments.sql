/*
Create TicketAttachments table for storing inbound/outbound ticket files.
SQL Server.

Run this once if you already have a database without this table.
*/

SET NOCOUNT ON;

IF OBJECT_ID(N'dbo.TicketAttachments', N'U') IS NULL
BEGIN
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
        [is_inline] [bit] NULL CONSTRAINT [DF_TicketAttachments_is_inline] DEFAULT ((0)),
        [is_downloaded] [bit] NULL CONSTRAINT [DF_TicketAttachments_is_downloaded] DEFAULT ((0)),
        [download_error] [nvarchar](500) NULL,
        [created_at] [datetime] NULL CONSTRAINT [DF_TicketAttachments_created_at] DEFAULT (getdate()),
        [downloaded_at] [datetime] NULL,
        CONSTRAINT [PK_TicketAttachments] PRIMARY KEY CLUSTERED ([id] ASC)
    ) ON [PRIMARY];

    ALTER TABLE [dbo].[TicketAttachments] WITH CHECK ADD CONSTRAINT [FK_TicketAttachments_Tickets]
        FOREIGN KEY([ticket_id]) REFERENCES [dbo].[Tickets] ([id]) ON DELETE CASCADE;

    -- Note: we intentionally do NOT add an FK to TicketMessages here.
    -- SQL Server can reject it due to multiple cascade paths when TicketMessages also cascades from Tickets.
END
