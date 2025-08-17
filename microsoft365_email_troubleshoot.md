# Microsoft 365 Email Connection Troubleshooting Guide

## ⚠️ Common Issues & Solutions

### 1. **IMAP is Disabled (Most Common Issue)**

**Check if IMAP is enabled for your mailbox:**

1. **Admin Center Method:**
   - Go to https://admin.microsoft.com
   - Navigate to **Users** → **Active users**
   - Select your support email account
   - Click **Mail** tab
   - Click **Manage email apps**
   - ✅ Ensure **IMAP** is enabled (checked)
   - ✅ Also enable **Authenticated SMTP** for sending

2. **PowerShell Method (for bulk enabling):**
   ```powershell
   # Connect to Exchange Online
   Connect-ExchangeOnline -UserPrincipalName admin@yourdomain.com
   
   # Enable IMAP for specific user
   Set-CASMailbox -Identity "support@yourdomain.com" -ImapEnabled $true
   
   # Enable for all users
   Get-CASMailbox -ResultSize Unlimited | Set-CASMailbox -ImapEnabled $true
   ```

### 2. **Security Defaults Blocking Legacy Auth**

Modern Microsoft 365 tenants have **Security Defaults** enabled which blocks IMAP/SMTP:

**To check and disable (if needed):**
1. Go to https://portal.azure.com
2. Navigate to **Azure Active Directory** → **Properties**
3. Click **Manage Security defaults**
4. If enabled, you have two options:
   - **Option A**: Disable Security Defaults (less secure)
   - **Option B**: Use Conditional Access policies instead

### 3. **Conditional Access Policies**

If you have Conditional Access, you might need to exclude your service account:

1. Go to **Azure AD** → **Security** → **Conditional Access**
2. Create new policy or modify existing
3. **Exclude** your support email account from blocking legacy authentication
4. Or create an exception for IMAP/SMTP protocols

### 4. **Modern Authentication (OAuth2) Required**

Some organizations require OAuth2 instead of basic auth:

**Check if Basic Auth is disabled:**
1. Go to https://admin.microsoft.com
2. **Settings** → **Org settings** → **Modern authentication**
3. Check if **Basic authentication** protocols are enabled for:
   - ✅ IMAP
   - ✅ Authenticated SMTP
   - ✅ POP

### 5. **App Password Issues**

**Verify App Password is set up correctly:**
1. Sign in to https://mysignins.microsoft.com/security-info
2. Click **+ Add sign-in method** → **App password**
3. Generate a new app password
4. **Important**: Copy it immediately (no spaces!)
5. Name it "ProjectTracker Email"

### 6. **IP Restrictions or Firewall**

**Check if your server IP is blocked:**
1. Azure AD → **Security** → **Named locations**
2. Check if there are IP restrictions
3. Add your server's IP as trusted if needed

## 🔧 Step-by-Step Verification Process

### Step 1: Test Basic Connectivity
```bash
# Test if you can reach Microsoft 365
telnet outlook.office365.com 993
# Should connect, type 'quit' to exit

# Test SMTP
telnet smtp.office365.com 587
```

### Step 2: Test with OpenSSL
```bash
# Test IMAP with SSL
openssl s_client -connect outlook.office365.com:993 -crlf

# After connection, try logging in:
# a LOGIN support@yourdomain.com YourAppPassword
# a SELECT INBOX
# a LOGOUT
```

### Step 3: Test with PHP Script
```php
<?php
// Save as test_connection.php
$host = '{outlook.office365.com:993/imap/ssl/novalidate-cert}INBOX';
$username = 'support@yourdomain.com';
$password = 'YourAppPassword'; // App password, not regular password

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Clear any cached errors
imap_errors();
imap_alerts();

echo "Attempting connection to: $host\n";
echo "Username: $username\n\n";

$connection = @imap_open($host, $username, $password);

if ($connection) {
    echo "✅ SUCCESS! Connected to Microsoft 365\n";
    $check = imap_check($connection);
    echo "Messages: " . $check->Nmsgs . "\n";
    imap_close($connection);
} else {
    echo "❌ FAILED to connect\n";
    echo "Errors:\n";
    print_r(imap_errors());
    echo "\nAlerts:\n";
    print_r(imap_alerts());
}
?>
```

## 📋 Required Azure/Microsoft 365 Settings Checklist

### For the Email Account (support@yourdomain.com):
- [ ] **IMAP enabled** in mailbox settings
- [ ] **Authenticated SMTP enabled** in mailbox settings
- [ ] **2FA enabled** on the account
- [ ] **App password created** and saved
- [ ] **Licensed** (needs Exchange Online license)

### For the Tenant:
- [ ] **Basic authentication enabled** for IMAP/SMTP (or OAuth2 configured)
- [ ] **Security defaults** disabled or configured with exceptions
- [ ] **No Conditional Access** policies blocking IMAP
- [ ] **No IP restrictions** blocking your server

### For Azure AD (if using OAuth2):
- [ ] App registration created
- [ ] Correct API permissions granted
- [ ] Admin consent provided
- [ ] Client secret generated and saved

## 🚨 Quick Fix Commands

### Enable IMAP for a user (PowerShell):
```powershell
Connect-ExchangeOnline
Set-CASMailbox -Identity "support@yourdomain.com" -ImapEnabled $true -PopEnabled $true -SmtpClientAuthenticationDisabled $false
Get-CASMailbox -Identity "support@yourdomain.com" | Format-List ImapEnabled,PopEnabled,SmtpClientAuthenticationDisabled
```

### Check Authentication Policies:
```powershell
Get-AuthenticationPolicy | Format-List *
Get-User -Identity "support@yourdomain.com" | Format-List AuthenticationPolicy
```

### Remove Authentication Policy (if blocking):
```powershell
Set-User -Identity "support@yourdomain.com" -AuthenticationPolicy $null
```

## 💡 Alternative Solutions

### If IMAP is completely blocked by your organization:

1. **Use Microsoft Graph API** instead:
   - More secure
   - Doesn't require IMAP
   - Requires Azure app registration

2. **Use Exchange Web Services (EWS)**:
   - Alternative to IMAP
   - Being phased out but still works

3. **Use Power Automate**:
   - Create a flow to forward emails to a webhook
   - Process emails via HTTP instead of IMAP

4. **Email Forwarding**:
   - Forward emails to a service that supports webhooks
   - Process via API instead of IMAP

## 🔍 Debugging Output to Share

If still having issues, run this and share the output:
```bash
php -r "phpinfo();" | grep -i imap
nslookup outlook.office365.com
telnet outlook.office365.com 993
```

Also check these logs:
- PHP error log: `/var/log/apache2/error.log`
- System mail log: `/var/log/mail.log`
