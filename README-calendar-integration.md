# Shared Calendar Integration

This feature allows users to link their external calendars (Google Calendar, Microsoft Outlook, and iCal feeds) with the Project Tracker application.

## Features

- Link multiple external calendars
- Display events from external calendars alongside project tasks
- Color-code events by calendar source
- Automatic or manual syncing of calendar events
- Edit or remove linked calendars

## Setup Instructions

### 1. Database Setup

Run the SQL script to create the necessary tables:

```sql
mysql -u your_username -p your_database_name < app/sql/calendar_tables.sql
```

Alternatively, you can import the SQL file through phpMyAdmin or run the queries directly in your database management tool.

### 2. Google Calendar Integration (OAuth Setup - Optional)

For Google Calendar integration to work properly, you need to:

1. Create a project in the [Google Cloud Console](https://console.cloud.google.com/)
2. Enable the Google Calendar API
3. Create OAuth 2.0 credentials (Web application type)
4. Set authorized redirect URIs to `https://your-domain.com/dashboard/googleCallback`
5. Add your client ID and client secret to your environment configuration

### 3. Microsoft Outlook Integration (OAuth Setup - Optional)

For Microsoft Outlook integration to work properly, you need to:

1. Register an application in the [Microsoft Azure Portal](https://portal.azure.com/)
2. Add Microsoft Graph API permissions for Calendars.Read
3. Configure redirect URI to `https://your-domain.com/dashboard/outlookCallback`
4. Add your client ID and client secret to your environment configuration

### 4. iCal/URL Integration

The iCal integration works out of the box without any additional configuration. Users can add any valid iCal feed URL.

## Usage

1. Navigate to the Calendar page
2. Click "Link Calendar" button
3. Choose the calendar type (Google, Outlook, or iCal URL)
4. Follow the prompts to connect the external calendar
5. Once connected, external calendar events will appear on the calendar alongside tasks

## Troubleshooting

- **Events not displaying**: Check if the calendar is marked as "Active" and try syncing it manually.
- **OAuth errors**: Ensure your OAuth credentials are correctly configured and that the redirect URIs match exactly.
- **iCal sync issues**: Verify that the URL is a valid iCal feed (typically ending in .ics) and is publicly accessible.

## Technical Details

This integration uses:

- OAuth 2.0 for secure authentication with Google and Microsoft
- iCal parsing for handling calendar feed URLs
- AJAX for asynchronous syncing of calendars
- FullCalendar.js for the calendar display

## Future Enhancements

Planned improvements for future versions:

- Two-way synchronization (write events back to external calendars)
- Support for more calendar providers
- Calendar sharing between team members
- Recurring event support
- Calendar event reminders/notifications 