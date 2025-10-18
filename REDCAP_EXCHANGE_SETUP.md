# RedCap Exchange Email Integration Setup

This guide explains how to set up the `hooks.php` file for RedCap to send emails via Microsoft Exchange using OAuth 2.0.

## 📁 Files Required

- `hooks.php` - Single-file email integration for RedCap
- `.env` file (same as CPHIA project) - **OR** - `bootstrap.php` file

## 🔧 Configuration

The `hooks.php` file automatically uses the same configuration as the CPHIA 2025 project. It will:

1. **First**: Try to load from `.env` file (if present)
2. **Second**: Use constants defined by `bootstrap.php` (if loaded)
3. **Third**: Use default values

### Option 1: Use .env file (Recommended)
Place a `.env` file in the same directory as `hooks.php` with these values:

```bash
# Microsoft Exchange OAuth Configuration
EXCHANGE_TENANT_ID=your_tenant_id_here
EXCHANGE_CLIENT_ID=your_client_id_here
EXCHANGE_CLIENT_SECRET=your_client_secret_here
EXCHANGE_REDIRECT_URI=http://your-redcap-server.com/oauth/callback
EXCHANGE_SCOPE=https://graph.microsoft.com/.default
EXCHANGE_AUTH_METHOD=client_credentials

# Email Configuration
MAIL_FROM_ADDRESS=notifications@africacdc.net
MAIL_FROM_NAME=Africa CDC RedCap
EXCHANGE_DEBUG=true
```

### Option 2: Use bootstrap.php
Include the `bootstrap.php` file before including `hooks.php`:

```php
// In your RedCap project
require_once '/path/to/cphia/bootstrap.php';
require_once '/path/to/hooks.php';
```

## 🚀 Installation Steps

### 1. Upload the hooks.php file
```bash
# Copy hooks.php to your RedCap hooks directory
cp hooks.php /path/to/redcap/hooks/
```

### 2. Configure Email Settings

**Option A: Use .env file (Recommended)**
```bash
# Create .env file in the same directory as hooks.php
cat > /path/to/redcap/hooks/.env << EOF
EXCHANGE_TENANT_ID=your_tenant_id_here
EXCHANGE_CLIENT_ID=your_client_id_here
EXCHANGE_CLIENT_SECRET=your_client_secret_here
EXCHANGE_REDIRECT_URI=http://your-redcap-server.com/oauth/callback
EXCHANGE_SCOPE=https://graph.microsoft.com/.default
EXCHANGE_AUTH_METHOD=client_credentials
MAIL_FROM_ADDRESS=notifications@africacdc.net
MAIL_FROM_NAME=Africa CDC RedCap
EXCHANGE_DEBUG=true
EOF
```

**Option B: Use bootstrap.php**
```bash
# Copy bootstrap.php to RedCap directory
cp bootstrap.php /path/to/redcap/hooks/
```

### 3. Configure RedCap
In your RedCap project settings:
1. Go to **Project Setup** → **Additional Customizations**
2. Add the hooks.php file path
3. Enable email functionality

## 🔐 OAuth Callback Setup

The `hooks.php` file includes built-in OAuth callback handling for Microsoft Graph API authentication.

### OAuth Callback URL
When setting up your Azure AD application, use this callback URL:
```
https://your-redcap-server.com/path/to/hooks.php?action=oauth_callback
```

### OAuth Functions Available

#### `redcap_oauth_status()`
Check OAuth configuration and authentication status:
```php
$status = redcap_oauth_status();
echo "Configured: " . ($status['configured'] ? 'Yes' : 'No');
echo "Authenticated: " . ($status['authenticated'] ? 'Yes' : 'No');
```

#### `redcap_get_oauth_url($redirectUri)`
Generate OAuth authorization URL:
```php
$authUrl = redcap_get_oauth_url('https://your-redcap-server.com/callback');
// Redirect user to $authUrl to start OAuth flow
```

#### `redcap_handle_oauth_callback()`
Handles OAuth callbacks automatically when accessed via:
```
/path/to/hooks.php?action=oauth_callback&code=AUTH_CODE&state=STATE
```

## 🧪 Testing the Integration

### Test via PHP Script
Create a test file `test_redcap_email.php`:

```php
<?php
// Load RedCap environment
require_once '/path/to/redcap/hooks.php';

// Test email function
$result = redcap_test_email('your-test-email@example.com');

echo "Test Results:\n";
echo "Configured: " . ($result['configured'] ? 'Yes' : 'No') . "\n";
echo "Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
if ($result['error']) {
    echo "Error: " . $result['error'] . "\n";
}
?>
```

### Test via RedCap Interface
1. Go to your RedCap project
2. Navigate to **Alerts & Notifications**
3. Create a test alert
4. Send to your email address
5. Check if email is received

## 📧 Usage in RedCap

The `redcap_email()` function is automatically called by RedCap with these parameters:

```php
redcap_email(
    $to,           // Recipient email(s) - comma-separated
    $from,         // Sender email
    $subject,      // Email subject
    $message,      // Email body (HTML or text)
    $cc,           // CC recipients - comma-separated
    $bcc,          // BCC recipients - comma-separated
    $attachments   // Array of file paths
);
```

## 🔍 Troubleshooting

### Check Logs
```bash
# Check RedCap error logs
tail -f /path/to/redcap/logs/redcap_error.log

# Check system error logs
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```

### Test Scripts

#### Basic Functionality Test
```bash
# Test the hooks
php test_redcap_hooks.php
```

#### OAuth Callback Test
```bash
# Test OAuth callback functionality
php test_oauth_callback.php
```

The OAuth callback test script verifies:
- OAuth status checking
- OAuth URL generation  
- OAuth callback simulation
- Error handling

#### Configuration Check
```bash
# Check configuration
php -r "require_once 'hooks.php'; echo 'Configured: ' . (new ExchangeEmailService())->isConfigured() ? 'Yes' : 'No';"
```

### Common Issues

1. **"Service not configured"**
   - Check environment variables are set
   - Verify OAuth credentials are correct

2. **"Failed to authenticate"**
   - Check Azure app permissions
   - Verify client credentials are correct
   - Ensure app has Mail.Send permission

3. **"Failed to send email"**
   - Check sender email address is valid
   - Verify recipient email addresses
   - Check Microsoft Graph API status

### Debug Mode
Enable debug mode for detailed logging:
```bash
export EXCHANGE_DEBUG=true
```

## 🔐 Security Considerations

1. **Environment Variables**: Store sensitive credentials in environment variables, not in code
2. **File Permissions**: Ensure hooks.php has proper permissions (644)
3. **HTTPS**: Always use HTTPS for OAuth redirect URIs
4. **Token Storage**: Tokens are stored in PHP sessions (temporary)

## 📊 Monitoring

### Check Service Status
```php
<?php
require_once '/path/to/redcap/hooks.php';

$emailService = new ExchangeEmailService();
$status = $emailService->getServiceStatus();

echo "Service Status: " . $status['status'] . "\n";
echo "Configured: " . ($status['configured'] ? 'Yes' : 'No') . "\n";
echo "Has Tokens: " . ($status['has_tokens'] ? 'Yes' : 'No') . "\n";
?>
```

## 🆘 Support

If you encounter issues:

1. Check the logs first
2. Verify environment variables
3. Test with a simple email
4. Check Azure app configuration
5. Contact system administrator

## 📝 Notes

- This integration uses OAuth 2.0 Client Credentials flow
- No user interaction required for sending emails
- Tokens are automatically refreshed
- Compatible with RedCap's email system
- Uses Microsoft Graph API v1.0
