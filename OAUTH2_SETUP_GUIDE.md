# OAuth2 Setup Guide for Email Authentication

This guide will help you set up OAuth2 authentication for your email system, which provides better security than traditional password authentication.

## 🔧 **Supported Providers**

- **Microsoft 365** (Office 365, Outlook.com)
- **Google Workspace** (Gmail, Google Apps)

## 📋 **Microsoft 365 OAuth2 Setup**

### **Step 1: Create Azure App Registration**

1. **Go to Azure Portal**: https://portal.azure.com
2. **Navigate to**: Azure Active Directory → App registrations
3. **Click**: "New registration"
4. **Configure**:
   - **Name**: "ProjectTracker Email Access"
   - **Supported account types**: "Accounts in any organizational directory and personal Microsoft accounts"
   - **Redirect URI**: `https://yourdomain.com/ProjectTracker/oauth2/callback`
5. **Click**: "Register"

### **Step 2: Configure API Permissions**

1. **Go to**: API permissions
2. **Click**: "Add a permission"
3. **Select**: Microsoft Graph
4. **Choose**: Delegated permissions
5. **Add these permissions**:
   - `IMAP.AccessAsUser.All`
   - `POP.AccessAsUser.All`
   - `offline_access`
6. **Click**: "Grant admin consent"

### **Step 3: Create Client Secret**

1. **Go to**: Certificates & secrets
2. **Click**: "New client secret"
3. **Description**: "ProjectTracker Secret"
4. **Expires**: Choose duration (24 months recommended)
5. **Copy the secret value** (you won't see it again!)

### **Step 4: Get Configuration Values**

- **Client ID**: Found on the Overview page
- **Client Secret**: The value you copied in Step 3
- **Redirect URI**: `https://yourdomain.com/ProjectTracker/oauth2/callback`

## 📋 **Google Workspace OAuth2 Setup**

### **Step 1: Create Google Cloud Project**

1. **Go to**: https://console.cloud.google.com
2. **Create new project** or select existing one
3. **Enable Gmail API**:
   - Go to APIs & Services → Library
   - Search for "Gmail API"
   - Click "Enable"

### **Step 2: Configure OAuth Consent Screen**

1. **Go to**: APIs & Services → OAuth consent screen
2. **Choose**: External (for general use)
3. **Fill required fields**:
   - App name: "ProjectTracker"
   - User support email: Your email
   - Developer contact: Your email
4. **Add scopes**: `https://mail.google.com/`
5. **Save and continue**

### **Step 3: Create OAuth2 Credentials**

1. **Go to**: APIs & Services → Credentials
2. **Click**: "Create Credentials" → "OAuth client ID"
3. **Application type**: Web application
4. **Name**: "ProjectTracker Email"
5. **Authorized redirect URIs**: `https://yourdomain.com/ProjectTracker/oauth2/callback`
6. **Create and copy**:
   - Client ID
   - Client Secret

## ⚙️ **ProjectTracker Configuration**

### **Step 1: Configure OAuth2 Settings**

1. **Go to**: Admin Settings → Email Tab
2. **Select**: OAuth2 authentication method
3. **Choose**: Your provider (Microsoft or Google)
4. **Enter**:
   - Client ID
   - Client Secret
   - Redirect URI (auto-filled)
5. **Save Settings**

### **Step 2: Authorize Access**

1. **Click**: "Authorize with OAuth2"
2. **Login** to your email provider
3. **Grant permissions** to ProjectTracker
4. **You'll be redirected back** with success message

### **Step 3: Test Connection**

1. **Click**: "Test OAuth2 Connection"
2. **Verify**: Connection is successful
3. **Configure**: Email server settings (host, port, etc.)
4. **Test**: "Test Connection" button

## 🔐 **Security Benefits**

### **OAuth2 vs Password Authentication**

| Feature | OAuth2 | Password |
|---------|--------|----------|
| **Security** | ✅ High | ⚠️ Medium |
| **Token Expiry** | ✅ Yes | ❌ No |
| **Revocable** | ✅ Yes | ❌ No |
| **2FA Compatible** | ✅ Yes | ⚠️ Limited |
| **Audit Trail** | ✅ Yes | ❌ No |
| **Setup Complexity** | ⚠️ Medium | ✅ Simple |

### **Key Advantages**

- **No password storage**: Tokens are used instead of passwords
- **Automatic refresh**: Tokens refresh automatically
- **Granular permissions**: Only email access, not full account
- **Revocable**: Can be revoked without changing passwords
- **Audit trail**: Provider logs all access

## 🚨 **Troubleshooting**

### **Common Issues**

#### **"Invalid Client" Error**
- ✅ Check Client ID is correct
- ✅ Verify app is properly registered
- ✅ Ensure redirect URI matches exactly

#### **"Access Denied" Error**
- ✅ Check API permissions are granted
- ✅ Verify admin consent (for Microsoft)
- ✅ Ensure user has email access

#### **"Token Expired" Error**
- ✅ Click "Re-authorize OAuth2"
- ✅ Check refresh token is valid
- ✅ Verify client secret is correct

#### **"Scope Error"**
- ✅ Check required scopes are added
- ✅ Verify permissions in provider console
- ✅ Re-authorize if scopes changed

### **Debug Steps**

1. **Check logs**: `/var/www/ProjectTracker/logs/`
2. **Verify settings**: Admin Settings → Email Tab
3. **Test OAuth2**: Use "Test OAuth2 Connection" button
4. **Check tokens**: Database table `oauth2_tokens`
5. **Provider logs**: Check your provider's audit logs

## 📞 **Support**

If you encounter issues:

1. **Check this guide** for common solutions
2. **Review error logs** in the application
3. **Verify provider settings** in Azure/Google Console
4. **Test with a different user** to isolate account issues

## 🔄 **Migration from Password Auth**

To migrate from password to OAuth2:

1. **Keep current settings** as backup
2. **Configure OAuth2** following this guide
3. **Test thoroughly** before switching
4. **Update authentication method** to OAuth2
5. **Verify email processing** works correctly

---

**Note**: OAuth2 setup requires technical knowledge. If you're not comfortable with these steps, consider asking your IT administrator for assistance.