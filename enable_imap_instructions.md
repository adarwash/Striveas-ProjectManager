# Enable IMAP for Microsoft 365 - Complete Steps

## You're Almost There! 

You have Modern Authentication enabled, which is good, but IMAP needs to be specifically allowed.

## Step 1: Enable IMAP in User's Mailbox Settings

You need to check TWO places:

### A. Organization-wide Settings (What you showed me):
✅ Authenticated SMTP is enabled (good!)
❓ But we need to check if IMAP4 is also in the list

### B. User-specific Mail App Settings (MOST IMPORTANT):
1. Go to: https://admin.microsoft.com
2. Click **Users** → **Active users**
3. Click on your support email account (e.g., support@yourdomain.com)
4. Click on the **Mail** tab
5. Click **Manage email apps**
6. You'll see checkboxes for:
   - [ ] **IMAP** ← CHECK THIS!
   - [ ] **POP** (optional)
   - [ ] **Authenticated SMTP** ← Should already be checked
   - [ ] **Exchange Web Services**
   - [ ] **Mobile (Exchange ActiveSync)**
   - [ ] **Outlook Desktop (MAPI)**

7. **Check the IMAP box** and click **Save changes**
8. **WAIT 15-30 minutes** for changes to propagate

## Step 2: Verify with PowerShell (Optional but Recommended)

```powershell
# Connect to Exchange Online
Connect-ExchangeOnline -UserPrincipalName admin@yourdomain.com

# Check current status
Get-CASMailbox -Identity "support@yourdomain.com" | Select-Object ImapEnabled, PopEnabled, SmtpClientAuthenticationDisabled

# Enable IMAP if it shows False
Set-CASMailbox -Identity "support@yourdomain.com" -ImapEnabled $true

# Verify it's enabled
Get-CASMailbox -Identity "support@yourdomain.com" | Select-Object ImapEnabled
# Should show: ImapEnabled : True
```

## Step 3: Create App Password (If Not Already Done)

Since you have Modern Authentication enabled, you MUST use an App Password:

1. Go to: https://mysignins.microsoft.com/security-info
2. Sign in with the support account
3. Click **+ Add method** → **App password**
4. Give it a name like "ProjectTracker"
5. Copy the 16-character password (no spaces!)
6. Use THIS password in your IMAP connection, NOT the regular password

## Step 4: Test Connection

After enabling IMAP and waiting 15-30 minutes, test with this PHP script:

```php
<?php
$host = '{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX';
$username = 'support@yourdomain.com';
$password = 'xxxx xxxx xxxx xxxx'; // Your 16-char app password (remove spaces)

$connection = imap_open($host, $username, $password);
if ($connection) {
    echo "✅ SUCCESS! Connected to Microsoft 365 IMAP\n";
    imap_close($connection);
} else {
    echo "❌ Failed. Errors:\n";
    print_r(imap_errors());
}
?>
```

## Why This Happens

Microsoft 365 with Modern Authentication enabled means:
- ✅ OAuth2 is preferred (most secure)
- ⚠️ Basic Auth (IMAP/POP/SMTP) needs explicit permission
- 🔒 App Passwords are required instead of regular passwords

Your organization has Modern Auth enabled (good for security) but you need to specifically allow IMAP for the mailbox that will receive support emails.

## Alternative: Use OAuth2 Instead

If your organization doesn't want to enable IMAP with Basic Auth, you can use OAuth2 instead:

1. Register an app in Azure AD
2. Grant it Mail.Read permissions
3. Use OAuth2 tokens instead of passwords
4. This is more complex but more secure

## Quick Checklist

Before IMAP will work, ensure ALL of these are true:
- [ ] IMAP is enabled in the USER's mailbox settings (not just org-wide)
- [ ] You're using an App Password (not regular password)
- [ ] You've waited 15-30 minutes after enabling IMAP
- [ ] The account has an Exchange Online license
- [ ] No Conditional Access policies are blocking IMAP

The #1 issue is usually that IMAP isn't enabled at the USER level, even if SMTP is!
