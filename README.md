# CPHIA 2025 Registration System

A comprehensive registration and payment system for the 4th International Conference on Public Health in Africa (CPHIA 2025).

## Features

- **Package Selection**: Individual, Group, and Exhibition registration packages
- **Dynamic Registration**: Support for both individual and group registrations
- **Payment Integration**: Secure payment processing with CyberSource
- **Advanced Email System**: PHPMailer-powered notifications with HTML templates
- **Admin Notifications**: Real-time alerts for registrations and payments
- **Modern UI**: Beautiful, responsive design with Africa CDC branding
- **Group Management**: Add multiple participants for group registrations
- **Payment Tracking**: Complete transaction history and status tracking
- **Email Templates**: Customizable HTML email templates
- **Multi-recipient Support**: Send notifications to both users and admins

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Steps

1. **Clone/Download the project** to your web server directory

2. **Install Dependencies**:
   ```bash
   composer install
   ```
   Or manually download `vlucas/phpdotenv` and place it in the `vendor` directory.

3. **Configure Environment**:
   - Copy `env.example` to `.env`
   - Update the `.env` file with your actual configuration:
     ```env
     # Database Configuration
     DB_HOST=localhost
     DB_NAME=cphia_payments
     DB_USER=root
     DB_PASS=password
     
     # Payment Gateway
     CYBERSOURCE_MERCHANT_ID=your_merchant_id
     CYBERSOURCE_PROFILE_ID=your_profile_id
     CYBERSOURCE_ACCESS_KEY=your_access_key
     CYBERSOURCE_SECRET_KEY=your_secret_key
     
     # Email Configuration
     MAIL_FROM_ADDRESS=noreply@cphia2025.com
     MAIL_FROM_NAME="CPHIA 2025"
     ```

4. **Run Installation Script**:
   - Visit `http://your-domain/install.php` for guided setup
   - Or visit `http://your-domain/setup.php` for direct database setup
   - This will create all necessary tables and insert package data

5. **Configure Payment Gateway** (Optional):
   - Update CyberSource credentials in the `.env` file
   - Test payment processing in sandbox mode

## File Structure

```
payments_plus/
├── index.php              # Main registration page
├── checkout.php           # Payment checkout page
├── response.php           # Payment response handler
├── functions.php          # Core business logic
├── db_connector.php       # Database connection
├── migrations.php         # Database setup
├── setup.php             # Initial setup script
├── css/
│   └── style.css         # Main stylesheet
├── js/
│   └── registration.js   # Registration form logic
├── sa-sop/               # Payment gateway integration
│   ├── config.php
│   ├── payment_form.php
│   └── ...
└── images/               # Logo and assets
```

## Usage

### Registration Process

1. **Select Package**: Choose from Individual, Group, or Exhibition packages
2. **Registration Type**: Select individual or group registration
3. **Personal Information**: Fill in contact and address details
4. **Group Participants**: For group registrations, add participant details
5. **Payment**: Receive email with payment link or proceed directly to checkout
6. **Confirmation**: Receive confirmation after successful payment

### Package Types

- **Individual Registration**: 
  - African Nationals: $200
  - Non-African Nationals: $400

- **Group Registration**:
  - Side Event Package 1: $6,000 (up to 10 people)
  - Side Event Package 2: $10,000 (up to 20 people)

- **Exhibition Packages**:
  - Resilience Bronze: $2,500 (up to 5 people)
  - Resilience Bronze Plus: $10,000 (up to 10 people)
  - Peace Silver: $30,000 (up to 15 people)
  - Ubuntu Gold: $50,000 (up to 25 people)
  - Uhuru Platinum: $75,000 (up to 50 people)

## Environment Variables

The system uses environment variables for configuration. All settings are defined in the `.env` file:

### Database Configuration
- `DB_HOST`: Database host (default: localhost)
- `DB_NAME`: Database name (default: cphia_payments)
- `DB_USER`: Database username (default: root)
- `DB_PASS`: Database password (default: password)
- `DB_CHARSET`: Database charset (default: utf8)

### Payment Gateway (CyberSource)
- `CYBERSOURCE_MERCHANT_ID`: Your CyberSource merchant ID
- `CYBERSOURCE_PROFILE_ID`: Your CyberSource profile ID
- `CYBERSOURCE_ACCESS_KEY`: Your CyberSource access key
- `CYBERSOURCE_SECRET_KEY`: Your CyberSource secret key
- `CYBERSOURCE_DF_ORG_ID`: Device fingerprint organization ID
- `CYBERSOURCE_BASE_URL`: CyberSource base URL (sandbox/live)

### Email Configuration
- `MAIL_DRIVER`: Email driver (default: smtp)
- `MAIL_HOST`: SMTP host (default: smtp.gmail.com)
- `MAIL_PORT`: SMTP port (default: 587)
- `MAIL_USERNAME`: SMTP username
- `MAIL_PASSWORD`: SMTP password
- `MAIL_ENCRYPTION`: Encryption type (default: tls)
- `MAIL_FROM_ADDRESS`: From email address
- `MAIL_FROM_NAME`: From name
- `ADMIN_EMAIL`: Admin notification email address
- `ADMIN_NAME`: Admin name for notifications
- `ADMIN_NOTIFICATIONS`: Enable admin notifications (true/false)
- `EMAIL_TEMPLATE_PATH`: Path to email templates
- `EMAIL_LOGO_URL`: Logo URL for emails

### Application Settings
- `APP_NAME`: Application name
- `APP_ENV`: Environment (local, production)
- `APP_DEBUG`: Debug mode (true/false)
- `APP_URL`: Application URL
- `ENABLE_EMAIL_NOTIFICATIONS`: Enable email notifications
- `ENABLE_PAYMENT_EMAILS`: Enable payment emails

### Conference Information
- `CONFERENCE_NAME`: Full conference name
- `CONFERENCE_SHORT_NAME`: Short conference name
- `CONFERENCE_DATES`: Conference dates
- `CONFERENCE_LOCATION`: Conference location
- `CONFERENCE_VENUE`: Conference venue

## Customization

### Styling
- Modify `css/style.css` to change colors, fonts, and layout
- Africa CDC color scheme is already implemented
- Responsive design for mobile and desktop

### Email Templates
- Update email templates in `functions.php`
- Configure SMTP settings in the `.env` file

### Payment Gateway
- Integrate with other payment processors
- Modify `sa-sop/` directory for different gateways
- Update environment variables for different payment providers

## Security Features

- SQL injection protection with prepared statements
- Input sanitization and validation
- CSRF protection on forms
- Secure session management
- Payment data encryption

## Database Schema

- `packages`: Available registration packages
- `users`: User account information
- `registrations`: Registration records
- `registration_participants`: Group participants
- `payments`: Payment transaction records

## API Endpoints

- `index.php` - Main registration form
- `checkout.php?registration_id=X` - Payment checkout
- `checkout.php?token=X` - Payment via email link
- `response.php` - Payment response handler

## Email System

### Email Notifications

The system sends the following email notifications:

1. **Registration Confirmation** (to user):
   - Sent immediately after registration
   - Includes registration details and next steps

2. **Payment Link** (to user):
   - Sent when user chooses to pay later
   - Contains secure payment link

3. **Payment Confirmation** (to user):
   - Sent after successful payment
   - Includes transaction details and conference information

4. **Admin Registration Notification** (to admin):
   - Sent for every new registration
   - Includes all registration details

5. **Admin Payment Notification** (to admin):
   - Sent when payment is completed
   - Includes payment and transaction details

### Email Testing

Use the built-in email testing tool:

1. Visit `http://your-domain/test_email.php`
2. Configure your email settings in `.env`
3. Send test emails to verify functionality
4. Preview email templates

### Email Templates

Email templates are located in `templates/email/`:
- `registration_confirmation.html`
- `payment_link.html`
- `payment_confirmation.html`
- `admin_registration_notification.html`
- `admin_payment_notification.html`

Templates use `{{variable}}` syntax for dynamic content.

## Troubleshooting

### Common Issues

1. **Database Connection Error**:
   - Check database credentials in `.env` file
   - Ensure MySQL service is running
   - Verify database exists

2. **Payment Processing Issues**:
   - Check CyberSource configuration in `.env`
   - Verify SSL certificate
   - Test in sandbox mode first

3. **Email Not Sending**:
   - Configure SMTP settings in `.env`
   - Use `test_email.php` to test email functionality
   - Check Gmail App Passwords for Gmail SMTP
   - Verify firewall settings for SMTP ports

### Debug Mode

Enable debug mode by adding to the top of PHP files:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Support

For technical support or customization requests, please contact the development team.

## License

This project is licensed under the MIT License.

---

**CPHIA 2025** - Moving towards self-reliance to achieve universal health coverage and health security in Africa