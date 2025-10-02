# CPHIA 2025 Laravel Email Package

**Production-ready email service for CPHIA 2025 using Microsoft Graph API**

## 🚀 Features

- ✅ **Microsoft Graph API** - Most reliable email method
- ✅ **OAuth 2.0 Security** - No password storage required
- ✅ **Automatic Token Refresh** - No user interaction needed
- ✅ **Laravel Compatible** - Easy integration
- ✅ **Production Tested** - Ready for live systems
- ✅ **Beautiful Templates** - CPHIA 2025 branded emails

## 📦 Installation

### 1. Copy Package Files

Copy the following files to your Laravel project:

```
src/
├── LaravelEmailService.php
└── ExchangeOAuth.php

EmailServiceProvider.php
```

### 2. Install Dependencies

```bash
composer require vlucas/phpdotenv
```

### 3. Environment Configuration

Add to your `.env` file:

```env
# Microsoft Graph OAuth Configuration
EXCHANGE_TENANT_ID=your_tenant_id
EXCHANGE_CLIENT_ID=your_client_id
EXCHANGE_CLIENT_SECRET=your_client_secret
EXCHANGE_REDIRECT_URI=http://your-domain.com/oauth/callback
EXCHANGE_SCOPE=https://graph.microsoft.com/Mail.Send

# Email Configuration
MAIL_FROM_ADDRESS=notifications@africacdc.org
MAIL_FROM_NAME=CPHIA 2025
```

### 4. Register Service Provider

Add to `config/app.php`:

```php
'providers' => [
    // ... other providers
    Cphia2025\EmailServiceProvider::class,
],
```

### 5. Publish Configuration

```bash
php artisan vendor:publish --provider="Cphia2025\EmailServiceProvider"
```

## 🔧 Usage

### Basic Email Sending

```php
use Cphia2025\LaravelEmailService;

$emailService = new LaravelEmailService();

// Send simple email
$emailService->sendEmail(
    'user@example.com',
    'Test Subject',
    '<h1>Hello World!</h1>',
    true // is HTML
);
```

### Registration Confirmation

```php
$registrationData = [
    'registration_id' => 'REG-2025-001',
    'name' => 'John Doe',
    'package' => 'Individual Registration',
    'amount' => 200.00,
    'created_at' => now()
];

$emailService->sendRegistrationConfirmation(
    'user@example.com',
    $registrationData
);
```

### Payment Confirmation

```php
$paymentData = [
    'payment_id' => 'PAY-2025-001',
    'name' => 'John Doe',
    'amount' => 200.00,
    'transaction_date' => now(),
    'payment_method' => 'Credit Card'
];

$emailService->sendPaymentConfirmation(
    'user@example.com',
    $paymentData
);
```

### Admin Notifications

```php
$emailService->sendAdminNotification(
    'New Registration Received',
    'A new registration has been submitted for CPHIA 2025.'
);
```

## 🔐 OAuth Setup (One-Time Only)

### 1. Azure App Registration

1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to "Azure Active Directory" > "App registrations"
3. Click "New registration"
4. Fill in details:
   - **Name**: CPHIA 2025 Email Service
   - **Redirect URI**: `http://your-domain.com/oauth/callback`
5. Note down **Application (client) ID** and **Directory (tenant) ID**

### 2. Generate Client Secret

1. Go to "Certificates & secrets"
2. Click "New client secret"
3. Add description: "CPHIA 2025 Email Service"
4. Copy the **secret value** (you won't see it again!)

### 3. API Permissions

1. Go to "API permissions"
2. Click "Add a permission"
3. Select "Microsoft Graph"
4. Choose "Application permissions"
5. Add: `Mail.Send`
6. Click "Grant admin consent"

### 4. Complete OAuth Setup

Visit: `http://your-domain.com/oauth/setup` to complete the one-time OAuth setup.

## 🧪 Testing

### Test Email Service

```php
use Cphia2025\LaravelEmailService;

$emailService = new LaravelEmailService();

// Test connection
$result = $emailService->testConnection();
if ($result['status'] === 'ready') {
    echo "Email service is ready!";
} else {
    echo "Error: " . $result['error'];
}

// Send test email
$emailService->sendTestEmail('test@example.com');
```

## 📧 Email Templates

The package includes beautiful, responsive email templates:

- **Registration Confirmation** - With CPHIA 2025 branding
- **Payment Confirmation** - Professional payment receipts
- **Test Email** - Service verification
- **Admin Notifications** - System alerts

All templates are:
- ✅ Mobile responsive
- ✅ CPHIA 2025 branded
- ✅ Professional design
- ✅ HTML formatted

## 🔄 Laravel Integration

### Service Container

The service is automatically registered in Laravel's service container:

```php
// In your controller
public function sendEmail(Request $request)
{
    $emailService = app(LaravelEmailService::class);
    
    $emailService->sendEmail(
        $request->email,
        'Welcome to CPHIA 2025!',
        '<h1>Thank you for registering!</h1>'
    );
}
```

### Queue Integration

```php
// In your job
use Cphia2025\LaravelEmailService;

class SendRegistrationEmail implements ShouldQueue
{
    public function handle()
    {
        $emailService = new LaravelEmailService();
        $emailService->sendRegistrationConfirmation(
            $this->email,
            $this->registrationData
        );
    }
}
```

### Artisan Commands

```php
// Create custom command
php artisan make:command TestEmailService

// In the command
use Cphia2025\LaravelEmailService;

public function handle()
{
    $emailService = new LaravelEmailService();
    $emailService->sendTestEmail('admin@example.com');
    $this->info('Test email sent!');
}
```

## 🛠️ Troubleshooting

### Common Issues

1. **"Email service not configured"**
   - Check your `.env` file has all required variables
   - Ensure OAuth credentials are correct

2. **"No valid OAuth tokens"**
   - Complete the one-time OAuth setup
   - Visit `/oauth/setup` to authenticate

3. **"Failed to send email"**
   - Check recipient email address
   - Verify OAuth permissions are granted
   - Check Azure app registration settings

### Debug Mode

Enable debug mode in your `.env`:

```env
EXCHANGE_DEBUG=true
```

## 📋 Requirements

- PHP 7.4+
- Laravel 6.0+
- Microsoft 365/Azure AD account
- Valid OAuth app registration

## 🎯 Production Checklist

- [ ] OAuth app registered in Azure
- [ ] Environment variables configured
- [ ] OAuth setup completed (one-time)
- [ ] Test email sent successfully
- [ ] Email templates customized (if needed)
- [ ] Queue jobs configured (if using queues)
- [ ] Error handling implemented
- [ ] Logging configured

## 📞 Support

For issues or questions:

- **Email**: notifications@africacdc.org
- **System**: CPHIA 2025 Registration System
- **Organization**: Africa CDC | African Union

## 📄 License

This package is part of the CPHIA 2025 Registration System.

---

**CPHIA 2025 - Conference on Public Health in Africa**  
*Africa CDC | African Union*