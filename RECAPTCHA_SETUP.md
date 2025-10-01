# reCAPTCHA Setup Instructions

## Overview
The CPHIA 2025 Registration System now includes reCAPTCHA v2 protection to prevent automated submissions and spam.

## Setup Steps

### 1. Get reCAPTCHA Keys
1. Go to [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
2. Click "Create" to add a new site
3. Choose reCAPTCHA v2 ("I'm not a robot" Checkbox)
4. Add your domain (e.g., `localhost` for development, `cphia2025.com` for production)
5. Copy the **Site Key** and **Secret Key**

### 2. Configure Environment Variables
Add the following to your `.env` file:

```env
# reCAPTCHA Configuration
RECAPTCHA_SITE_KEY=your-recaptcha-site-key-here
RECAPTCHA_SECRET_KEY=your-recaptcha-secret-key-here
RECAPTCHA_ENABLED=true
```

### 3. Test Configuration
1. Start your local server: `php -S localhost:8000`
2. Visit the registration page
3. You should see the reCAPTCHA widget before the submit button
4. Complete the reCAPTCHA verification
5. Submit the form to test the validation

## Features

### Security Benefits
- **Bot Protection**: Prevents automated form submissions
- **Spam Prevention**: Reduces spam registrations
- **Rate Limiting**: Works with existing rate limiting system
- **Security Logging**: Failed reCAPTCHA attempts are logged

### Implementation Details
- **Client-side Validation**: JavaScript checks reCAPTCHA completion
- **Server-side Validation**: PHP validates reCAPTCHA with Google's API
- **Conditional Display**: Only shows when reCAPTCHA is enabled
- **Error Handling**: Clear error messages for failed verification

### Configuration Options
- **Enable/Disable**: Set `RECAPTCHA_ENABLED=false` to disable
- **Site Key**: Public key for client-side widget
- **Secret Key**: Private key for server-side validation
- **Domain Validation**: Keys are tied to specific domains

## Troubleshooting

### Common Issues
1. **reCAPTCHA not showing**: Check if `RECAPTCHA_SITE_KEY` is set
2. **Validation failing**: Verify `RECAPTCHA_SECRET_KEY` is correct
3. **Domain errors**: Ensure domain matches reCAPTCHA configuration
4. **CSP errors**: Check Content Security Policy allows Google domains

### Debug Mode
Set `APP_DEBUG=true` in your `.env` file to see detailed error messages.

## Security Notes
- Keep your Secret Key secure and never expose it in client-side code
- Use different keys for development and production environments
- Regularly rotate your reCAPTCHA keys
- Monitor reCAPTCHA analytics for suspicious activity

## Support
For issues with reCAPTCHA integration, check:
1. Google reCAPTCHA documentation
2. Browser console for JavaScript errors
3. Server logs for PHP errors
4. Network tab for API call failures
