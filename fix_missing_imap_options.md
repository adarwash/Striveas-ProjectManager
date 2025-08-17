# Fix: IMAP/POP Options Not Showing in Microsoft 365

## Why IMAP/POP Options Are Missing

Microsoft has been changing how these settings are managed. If you don't see IMAP4/POP3 options in the Modern Authentication page, it's because:

1. **They're now controlled at the user mailbox level only**
2. **Your tenant might have them disabled globally via PowerShell**
3. **Authentication policies might be overriding the settings**

## Solution 1: Enable via User Mailbox Settings (Web Interface)

### This is where IMAP is actually controlled now:

1. **Go to**: https://admin.microsoft.com
2. Navigate to **Users** → **Active users**
3. **Click on your support email user**
4. Click on **Mail** tab
5. Look for **"Email apps"** or **"Manage email apps"**
6. You should see:
   - ☐ Outlook on the web
   - ☐ Outlook desktop (MAPI)
   - ☐ Exchange Web Services
   - ☐ **IMAP** ← Enable this!
   - ☐ **POP** ← Optional
   - ☐ **Authenticated SMTP** ← Enable this too!

If these options aren't there either, use PowerShell (Solution 2).

## Solution 2: Enable via PowerShell (Most Reliable)

### Run these commands in PowerShell:

```powershell
# 1. Install Exchange Online module (if not already installed)
Install-Module -Name ExchangeOnlineManagement -Force

# 2. Connect to Exchange Online
Connect-ExchangeOnline -UserPrincipalName admin@yourdomain.com

# 3. Check current authentication policy
Get-OrganizationConfig | Select DefaultAuthenticationPolicy

# 4. Check if IMAP is disabled globally
Get-OrganizationConfig | Select IMAP4Enabled, POP3Enabled

# 5. Enable IMAP/POP globally (if needed)
Set-OrganizationConfig -IMAP4Enabled $true -POP3Enabled $true

# 6. Enable IMAP for specific user
Set-CASMailbox -Identity "support@yourdomain.com" -ImapEnabled $true -PopEnabled $true -SmtpClientAuthenticationDisabled $false

# 7. Verify it's enabled
Get-CASMailbox -Identity "support@yourdomain.com" | Format-List ImapEnabled, PopEnabled, SmtpClientAuthenticationDisabled

# 8. Remove any authentication policy that might be blocking
Set-User -Identity "support@yourdomain.com" -AuthenticationPolicy $null
```

## Solution 3: Check Authentication Policies

Authentication policies might be overriding your settings:

```powershell
# Check if there's a blocking policy
Get-AuthenticationPolicy | Select Name, AllowBasicAuth*

# Check what policy is applied to your user
Get-User -Identity "support@yourdomain.com" | Select AuthenticationPolicy

# If a policy is blocking IMAP, create a new one that allows it:
New-AuthenticationPolicy -Name "AllowIMAP" -AllowBasicAuthImap -AllowBasicAuthSmtp

# Apply it to your support user
Set-User -Identity "support@yourdomain.com" -AuthenticationPolicy "AllowIMAP"
```

## Solution 4: Use Exchange Admin Center (EAC)

Sometimes the settings are in the classic Exchange admin:

1. Go to: https://admin.exchange.microsoft.com
2. Navigate to **Recipients** → **Mailboxes**
3. Find and click your support mailbox
4. Click **Manage email apps settings**
5. Enable:
   - ✅ IMAP
   - ✅ POP
   - ✅ SMTP

## Solution 5: Check Security Defaults

Security Defaults might be blocking everything:

1. Go to: https://portal.azure.com
2. Navigate to **Azure Active Directory** → **Properties**
3. Scroll down and click **Manage Security defaults**
4. If it's **Enabled**, you have two options:
   - **Disable it** (less secure but allows IMAP)
   - **Switch to Conditional Access** (more complex but granular control)

## Quick PowerShell One-Liner to Enable Everything

Run this to force-enable IMAP for your support account:

```powershell
Connect-ExchangeOnline; Set-CASMailbox -Identity "support@yourdomain.com" -ImapEnabled $true -PopEnabled $true -SmtpClientAuthenticationDisabled $false -OWAEnabled $true -ActiveSyncEnabled $true -EwsEnabled $true -MapiEnabled $true
```

## If Nothing Works: Use Graph API Instead

If your organization has completely locked down IMAP/POP, you'll need to use Microsoft Graph API:

```php
// Modern way to access emails without IMAP
// Requires Azure App Registration with Mail.Read permission
$tenantId = 'your-tenant-id';
$clientId = 'your-app-id';
$clientSecret = 'your-app-secret';

// Get access token
$tokenEndpoint = "https://login.microsoftonline.com/$tenantId/oauth2/v2.0/token";
$tokenData = [
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'scope' => 'https://graph.microsoft.com/.default',
    'grant_type' => 'client_credentials'
];

// Use token to read emails via Graph API
// GET https://graph.microsoft.com/v1.0/users/support@yourdomain.com/messages
```

## Most Likely Issue

Since IMAP/POP options aren't showing in the UI, your organization likely has:
1. **Security Defaults enabled** (blocking basic auth entirely)
2. **Authentication policies** that override UI settings
3. **IMAP disabled at the organization level**

**The PowerShell commands in Solution 2 will bypass the UI and force-enable IMAP directly.**
