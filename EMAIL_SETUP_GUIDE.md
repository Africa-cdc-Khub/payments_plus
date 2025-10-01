# CPHIA 2025 Email Setup Guide - MS Exchange/Office 365

## Current Issue: SMTP Authentication Disabled

The test email failed with the error:
```
535 5.7.139 Authentication unsuccessful, SmtpClientAuthentication is disabled for the Tenant
```

This means that **SMTP authentication is disabled** for the Africa CDC Office 365 tenant, which is a common security setting in modern Office 365 environments.

## Solutions

### Option 1: Enable SMTP Authentication (Recommended for Development)

**For the Africa CDC IT Administrator:**

1. **Go to Microsoft 365 Admin Center**
   - Navigate to https://admin.microsoft.com
   - Sign in with admin credentials

2. **Enable SMTP Authentication**
   - Go to **Settings** → **Mail** → **Authentication policies**
   - Find the policy applied to the `notifications@africacdc.org` account
   - Enable **SMTP AUTH** for this account
   - Or create a new authentication policy with SMTP AUTH enabled

3. **Alternative: PowerShell Method**
   ```powershell
   # Connect to Exchange Online
   Connect-ExchangeOnline
   
   # Enable SMTP for specific user
   Set-CASMailbox -Identity notifications@africacdc.org -SmtpClientAuthenticationDisabled $false
   
   # Or enable for entire organization (less secure)
   Set-TransportConfig -SmtpClientAuthenticationDisabled $false
   ```

### Option 2: Use Microsoft Graph API (Recommended for Production)

Since SMTP is disabled, we can use Microsoft Graph API with OAuth 2.0 authentication instead.

**Benefits:**
- More secure than SMTP
- No need to enable SMTP authentication
- Better integration with Office 365
- Supports modern authentication

**Implementation:**
- Use the OAuth implementation we created earlier
- Requires Azure AD app registration
- Uses Microsoft Graph API to send emails

### Option 3: Use App Passwords (If Available)

If the tenant allows app passwords:

1. **Generate App Password**
   - Go to https://myaccount.microsoft.com
   - Sign in with `notifications@africacdc.org`
   - Go to **Security** → **App passwords**
   - Generate a new app password

2. **Update Configuration**
   - Use the app password instead of the regular password
   - Update the `.env` file with the app password

## Current Configuration Status

✅ **Correctly Configured:**
- Mail Host: `smtp.office365.com`
- Port: `587`
- Encryption: `PHPMailer::ENCRYPTION_STARTTLS`
- Email addresses: Fixed (added @ symbol)

❌ **Blocked by Tenant Policy:**
- SMTP Authentication is disabled
- Cannot authenticate with username/password

## Next Steps

1. **Contact Africa CDC IT Administrator** to:
   - Enable SMTP authentication for `notifications@africacdc.org`
   - Or provide OAuth credentials for Microsoft Graph API
   - Or generate an app password if allowed

2. **Alternative: Use Different Email Service**
   - Gmail SMTP (if allowed)
   - SendGrid
   - Amazon SES
   - Other SMTP providers

## Test Commands

Once SMTP is enabled, test with:
```bash
curl -s "http://localhost:8000/test_exchange_email.php"
```

## Security Considerations

- **SMTP Authentication**: Less secure, requires enabling in tenant
- **OAuth 2.0**: More secure, modern authentication
- **App Passwords**: Good middle ground if OAuth is not available

## Support

For technical support with email configuration, contact:
- Africa CDC IT Team
- System Administrator
- Microsoft 365 Support
