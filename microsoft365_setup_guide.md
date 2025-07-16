# Microsoft 365 Calendar Integration Setup Guide

This guide will help you set up Microsoft 365 calendar integration for your ProjectTracker application.

## Prerequisites

1. A Microsoft 365 developer account or Azure subscription
2. Access to Azure Portal
3. Your ProjectTracker application running with HTTPS (required for OAuth)

## Step 1: Register Your Application in Azure

1. Go to the [Azure Portal](https://portal.azure.com/)
2. Navigate to **Azure Active Directory** > **App registrations**
3. Click **New registration**
4. Fill in the registration form:
   - **Name**: `ProjectTracker Calendar Integration`
   - **Supported account types**: Choose based on your needs
     - **Accounts in this organizational directory only** - Single tenant
     - **Accounts in any organizational directory** - Multi-tenant
     - **Accounts in any organizational directory and personal Microsoft accounts** - Multi-tenant + personal accounts
   - **Redirect URI**: 
     - Platform: **Web**
     - URI: `https://your-domain.com/dashboard/microsoftCallback`

5. Click **Register**

## Step 2: Configure API Permissions

1. In your app registration, go to **API permissions**
2. Click **Add a permission**
3. Select **Microsoft Graph**
4. Choose **Delegated permissions**
5. Add the following permissions:
   - `Calendars.Read` - Read user calendars
   - `offline_access` - Maintain access to data you have given it access to

6. Click **Add permissions**
7. **Important**: Click **Grant admin consent** if you have admin privileges

## Step 3: Create Client Secret

1. Go to **Certificates & secrets**
2. Click **New client secret**
3. Add a description: `ProjectTracker Calendar Secret`
4. Choose expiration: **24 months** (recommended)
5. Click **Add**
6. **Important**: Copy the secret value immediately - you won't be able to see it again!

## Step 4: Configure Your Application

Add the following environment variables to your `.env` file or server configuration:

```bash
# Microsoft 365 Calendar Integration
MICROSOFT_CLIENT_ID=your-application-client-id
MICROSOFT_CLIENT_SECRET=your-client-secret-value
APP_URL=https://your-domain.com
```

### Example `.env` Configuration

```bash
# Database Configuration
DB_HOST=localhost
DB_NAME=projecttracker
DB_USER=your-db-user
DB_PASS=your-db-password

# Application Configuration
APP_URL=https://projecttracker.example.com
APP_NAME=ProjectTracker

# Microsoft 365 Integration
MICROSOFT_CLIENT_ID=12345678-1234-1234-1234-123456789012
MICROSOFT_CLIENT_SECRET=your-secret-value-here
```

## Step 5: Database Setup

Run the migration script to update your database:

```sql
-- Run this in your MySQL database
SOURCE microsoft365_calendar_migration.sql;
```

Or manually run the commands:

```sql
-- Add microsoft365 to source enum
ALTER TABLE `external_calendars` 
MODIFY COLUMN `source` enum('google','outlook','ical','microsoft365') NOT NULL;

-- Add URL field for calendar events
ALTER TABLE `calendar_events` 
ADD COLUMN `url` varchar(1000) DEFAULT NULL AFTER `all_day`;
```

## Step 6: Test the Integration

1. Navigate to your calendar page: `https://your-domain.com/dashboard/calendar`
2. Click **Link Calendar**
3. Select the **Microsoft 365** tab
4. Fill in the form and click **Connect with Microsoft 365**
5. You should be redirected to Microsoft's login page
6. After authorization, you'll be redirected back with your calendar connected

## Security Best Practices

### Token Security
- Access tokens are automatically refreshed using refresh tokens
- Tokens are stored encrypted in your database
- Tokens expire automatically for security

### Permissions
- Only request the minimum permissions needed (`Calendars.Read`)
- Users can revoke access at any time through their Microsoft account settings

### HTTPS Requirement
- Microsoft OAuth requires HTTPS for production use
- Ensure your application runs over HTTPS

## Troubleshooting

### Common Issues

1. **"Invalid redirect URI" error**
   - Ensure the redirect URI in Azure matches exactly: `https://your-domain.com/dashboard/microsoftCallback`
   - Make sure you're using HTTPS

2. **"Application not found" error**
   - Check that `MICROSOFT_CLIENT_ID` is correct
   - Verify the app registration exists in Azure

3. **"Invalid client secret" error**
   - Check that `MICROSOFT_CLIENT_SECRET` is correct
   - Client secrets expire - generate a new one if needed

4. **"Insufficient privileges" error**
   - Ensure API permissions are granted
   - Admin consent may be required for organizational accounts

5. **Calendar not syncing**
   - Check server logs for API errors
   - Verify tokens haven't expired
   - Ensure the user has calendars in their Microsoft 365 account

### Debug Mode

To enable debug logging for calendar integration, add this to your error handling:

```php
// In app/models/Calendar.php - syncMicrosoftCalendar method
error_log('Microsoft calendar sync debug: ' . json_encode($response));
```

### Manual Token Refresh

If tokens become invalid, users can:
1. Remove the calendar connection
2. Re-add it to go through OAuth again

## Features

### What's Included
- **Secure OAuth Flow**: Industry-standard OAuth 2.0 implementation
- **Automatic Token Refresh**: Tokens are refreshed automatically before expiring
- **Event Sync**: Events from the past month to 3 months in the future
- **Event Details**: Title, description, location, start/end times, all-day events
- **Calendar Colors**: Customizable calendar colors in the interface
- **Auto-sync**: Optional daily automatic synchronization

### Supported Event Types
- Regular calendar events
- All-day events
- Events with locations
- Recurring events (basic support)

### Not Currently Supported
- Writing/creating events (read-only integration)
- Multiple calendar selection (primary calendar only)
- Complex recurring event patterns
- Event attendees/participants

## API Rate Limits

Microsoft Graph API has rate limits:
- **Default**: 10,000 requests per 10 minutes per app per tenant
- The integration is designed to stay well within these limits
- Automatic retry logic with exponential backoff

## Support

For issues with:
- **Azure setup**: Check Microsoft's Azure documentation
- **API errors**: Check Microsoft Graph API documentation
- **Application integration**: Check your server logs and verify configuration

## Security Notes

- Never commit secrets to version control
- Rotate client secrets regularly (every 12-24 months)
- Monitor access logs for suspicious activity
- Users can revoke access through their Microsoft account settings 