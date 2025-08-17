# Microsoft Graph API Setup Guide for Email Integration

## Step 1: Register App in Azure AD

1. **Go to Azure Portal**: https://portal.azure.com
2. Navigate to **Azure Active Directory** → **App registrations**
3. Click **"+ New registration"**
4. Configure:
   - **Name**: `ProjectTracker Email Integration`
   - **Supported account types**: Select "Accounts in this organizational directory only"
   - **Redirect URI**: Leave blank (we're using client credentials flow)
5. Click **"Register"**
6. **Copy these values** (you'll need them):
   - **Application (client) ID**: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
   - **Directory (tenant) ID**: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`

## Step 2: Create Client Secret

1. In your app registration, go to **"Certificates & secrets"**
2. Click **"+ New client secret"**
3. Add description: `ProjectTracker Secret`
4. Choose expiry: **24 months** (recommended)
5. Click **"Add"**
6. **IMMEDIATELY COPY THE SECRET VALUE** (you won't see it again!)
   - Secret Value: `xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

## Step 3: Grant API Permissions

1. Go to **"API permissions"**
2. Click **"+ Add a permission"**
3. Choose **"Microsoft Graph"**
4. Choose **"Application permissions"** (NOT Delegated)
5. Search and add these permissions:
   - `Mail.Read` - Read mail in all mailboxes
   - `Mail.Send` - Send mail as any user
   - `Mail.ReadWrite` - Read and write mail in all mailboxes
   - `User.Read.All` - Read all users' profiles (optional, for user lookup)

6. **IMPORTANT**: Click **"Grant admin consent for [Your Organization]"**
   - You'll see green checkmarks ✓ next to each permission

## Step 4: Configure ProjectTracker

### Option A: Add to config.php

Edit `/var/www/ProjectTracker/config/config.php` and add:

```php
// Microsoft Graph API Configuration
define('GRAPH_TENANT_ID', 'your-tenant-id-here');
define('GRAPH_CLIENT_ID', 'your-client-id-here');
define('GRAPH_CLIENT_SECRET', 'your-client-secret-here');
define('GRAPH_SUPPORT_EMAIL', 'support@yourdomain.com');
```

### Option B: Add to Database (Recommended)

Run this SQL in your database:

```sql
INSERT INTO Settings (setting_key, setting_value, description) VALUES
('graph_tenant_id', 'your-tenant-id-here', 'Azure AD Tenant ID'),
('graph_client_id', 'your-client-id-here', 'Azure AD App Client ID'),
('graph_client_secret', 'your-client-secret-here', 'Azure AD App Client Secret'),
('graph_support_email', 'support@yourdomain.com', 'Support email to monitor');
```

## Step 5: Test the Connection

Create and run this test script:

```php
<?php
require_once '/var/www/ProjectTracker/app/services/MicrosoftGraphService.php';
require_once '/var/www/ProjectTracker/config/config.php';

// Test configuration
define('GRAPH_TENANT_ID', 'your-tenant-id');
define('GRAPH_CLIENT_ID', 'your-client-id');
define('GRAPH_CLIENT_SECRET', 'your-secret');

try {
    $graph = new MicrosoftGraphService();
    
    // Test getting access token
    echo "Getting access token...\n";
    $token = $graph->getAccessToken();
    echo "✅ Access token obtained!\n\n";
    
    // Test reading emails
    echo "Reading emails from support@yourdomain.com...\n";
    $emails = $graph->getEmails('support@yourdomain.com', 5);
    echo "✅ Found " . count($emails) . " emails\n";
    
    foreach ($emails as $email) {
        echo "  - " . $email['subject'] . " from " . $email['from']['emailAddress']['address'] . "\n";
    }
    
    echo "\n🎉 Graph API is working!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
```

## Step 6: Set Up Automated Processing

### Create Processing Script

Save as `/var/www/ProjectTracker/app/scripts/process_graph_emails.php`:

```php
<?php
require_once dirname(__DIR__) . '/../config/config.php';
require_once dirname(__DIR__) . '/services/MicrosoftGraphService.php';
require_once dirname(__DIR__) . '/models/Ticket.php';
require_once dirname(__DIR__) . '/core/EasySQL.php';

try {
    $graph = new MicrosoftGraphService();
    $processed = $graph->processEmailsToTickets('support@yourdomain.com');
    
    if ($processed !== false) {
        echo date('Y-m-d H:i:s') . " - Processed {$processed} emails\n";
    } else {
        echo date('Y-m-d H:i:s') . " - Error processing emails\n";
    }
} catch (Exception $e) {
    echo date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n";
}
```

### Add to Crontab

```bash
# Edit crontab
crontab -e

# Add this line to check emails every 5 minutes
*/5 * * * * /usr/bin/php /var/www/ProjectTracker/app/scripts/process_graph_emails.php >> /var/log/email_processing.log 2>&1
```

## Troubleshooting

### Common Issues:

1. **"Access token failed"**
   - Check tenant ID, client ID, and secret are correct
   - Ensure secret hasn't expired

2. **"Insufficient privileges"**
   - Make sure you clicked "Grant admin consent"
   - Verify permissions show green checkmarks

3. **"User not found"**
   - Check the email address exists in your tenant
   - Ensure the email account is licensed

4. **"InvalidAuthenticationToken"**
   - Token might be expired, it auto-refreshes
   - Check system time is correct

### Verify Permissions in Azure

1. Go to your app registration
2. Click "API permissions"
3. You should see:
   - Mail.Read ✓ (Application, Granted)
   - Mail.Send ✓ (Application, Granted)
   - Mail.ReadWrite ✓ (Application, Granted)

## Advantages Over IMAP

✅ **No IMAP/POP configuration needed**
✅ **Works with Security Defaults enabled**
✅ **More secure (OAuth2 vs passwords)**
✅ **Better performance and reliability**
✅ **Access to full email metadata**
✅ **Can manage folders and categories**
✅ **Supports modern authentication**
✅ **No app passwords needed**

## Next Steps

1. Test email processing manually
2. Set up cron job for automation
3. Monitor logs for any issues
4. Consider adding webhook support for real-time processing
