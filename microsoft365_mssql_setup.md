# Microsoft 365 Calendar Integration - MS SQL Server Setup

Quick setup guide for Microsoft 365 calendar integration with MS SQL Server database.

## Step 1: Update Database

Run the MS SQL Server migration script in your database:

```sql
-- Execute this in SQL Server Management Studio or your preferred SQL client
EXEC('SOURCE microsoft365_calendar_migration_mssql.sql');
```

Or copy and paste the contents of `microsoft365_calendar_migration_mssql.sql` directly.

## Step 2: Environment Configuration

Add these to your `.env` or configuration file:

```bash
MICROSOFT_CLIENT_ID=your-azure-app-id
MICROSOFT_CLIENT_SECRET=your-azure-app-secret  
APP_URL=http://192.168.2.12
```

## Step 3: Azure App Registration

1. Go to [Azure Portal](https://portal.azure.com/)
2. Navigate to **Azure Active Directory** > **App registrations** > **New registration**
3. Set these values:
   - **Name**: `ProjectTracker Calendar`
   - **Redirect URI**: `http://192.168.2.12/dashboard/microsoftCallback`
4. After registration, go to **API permissions**:
   - Add **Microsoft Graph** > **Delegated permissions**
   - Add `Calendars.Read` and `offline_access`
5. Go to **Certificates & secrets**:
   - Create new client secret
   - Copy the secret value

## Step 4: Test

1. Navigate to `http://192.168.2.12/dashboard/calendar`
2. Click **Link Calendar**
3. You should see three tabs: **Google Calendar**, **Microsoft 365**, **iCal/URL**
4. Click **Microsoft 365** tab
5. Fill in the form and click **Connect with Microsoft 365**

## Troubleshooting

### Microsoft 365 tab not showing
- Clear browser cache
- Check browser console for JavaScript errors
- Verify Bootstrap CSS/JS is loaded

### Database issues
- Ensure the migration script ran successfully
- Check that tables `external_calendars` and `calendar_events` exist
- Verify the `source` column accepts 'microsoft365' value

### OAuth issues
- Verify redirect URI matches exactly: `http://192.168.2.12/dashboard/microsoftCallback`
- Check client ID and secret are correct
- Ensure API permissions are granted in Azure

## Quick Test Query

Check if tables were created correctly:

```sql
-- Check tables exist
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_NAME IN ('external_calendars', 'calendar_events');

-- Check source enum includes microsoft365
SELECT COLUMN_NAME, DATA_TYPE, CHECK_CONSTRAINTS
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'external_calendars' AND COLUMN_NAME = 'source';
``` 