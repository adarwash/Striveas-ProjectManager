<?php
/**
 * Microsoft 365 Email Configuration
 * Add this file or copy these settings to your main config.php
 */

// SMTP Settings for SENDING emails (Microsoft 365)
define('SMTP_HOST', 'smtp.office365.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'support@yourdomain.com'); // Your Microsoft 365 email
define('SMTP_PASSWORD', 'your-app-password-here');  // App password from Microsoft
define('SMTP_ENCRYPTION', 'tls');
define('SMTP_AUTH', true);
define('FROM_EMAIL', 'support@yourdomain.com');
define('FROM_NAME', SITENAME);

// IMAP Settings for RECEIVING emails (Microsoft 365)
define('IMAP_HOST', 'outlook.office365.com');
define('IMAP_PORT', 993);
define('IMAP_USERNAME', 'support@yourdomain.com'); // Same as SMTP username
define('IMAP_PASSWORD', 'your-app-password-here');  // Same app password
define('IMAP_ENCRYPTION', 'ssl');
define('IMAP_FOLDER', 'INBOX');

// Alternative: OAuth2 Settings (More secure but complex)
// define('OAUTH2_PROVIDER', 'microsoft');
// define('OAUTH2_CLIENT_ID', 'your-azure-app-id');
// define('OAUTH2_CLIENT_SECRET', 'your-azure-app-secret');
// define('OAUTH2_TENANT_ID', 'your-tenant-id'); // or 'common' for multi-tenant
?>
