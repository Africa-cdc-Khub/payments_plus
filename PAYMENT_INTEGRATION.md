# 💳 Payment Integration Guide

## Overview

This document explains how the CyberSource payment integration works with the CPHIA 2025 registration system. The integration automatically populates payment forms with registration data and processes payments securely.

## 🔄 Payment Flow

```
Registration → Payment Link → Checkout Form → CyberSource → Response Handler → Confirmation
```

### 1. **Registration Process**
- User fills registration form
- System creates user and registration records
- Payment token is generated for security
- Payment link email is sent

### 2. **Payment Checkout**
- User clicks payment link in email
- System validates token and retrieves registration data
- Payment form is auto-populated with user details
- User enters card information

### 3. **Payment Processing**
- Form data is signed for security (HMAC-SHA256)
- Data is sent to CyberSource for processing
- CyberSource processes payment and returns response

### 4. **Response Handling**
- System receives payment response
- Registration status is updated
- Confirmation emails are sent
- User sees success/failure page

## 📁 Key Files

### Core Payment Files
- `checkout_payment.php` - Main payment form with auto-populated data
- `payment_response.php` - Handles payment results and updates database
- `sa-sop/response.php` - CyberSource response redirect

### CyberSource Integration
- `sa-sop/config.php` - CyberSource configuration
- `sa-sop/security.php` - HMAC-SHA256 signing functions
- `sa-sop/payment_form.php` - Original CyberSource form (for reference)
- `sa-sop/payment_confirm.php` - CyberSource confirmation page

### Supporting Files
- `functions.php` - Contains `sendPaymentLinkEmail()` function
- `test_payment_flow.php` - Test script for payment integration

## 🔧 Configuration

### Environment Variables Required
```bash
# CyberSource Configuration
CYBERSOURCE_MERCHANT_ID=your_merchant_id
CYBERSOURCE_PROFILE_ID=your_profile_id
CYBERSOURCE_ACCESS_KEY=your_access_key
CYBERSOURCE_SECRET_KEY=your_secret_key
CYBERSOURCE_DF_ORG_ID=your_df_org_id
CYBERSOURCE_BASE_URL=https://testsecureacceptance.cybersource.com
```

### Database Tables Used
- `users` - User information
- `registrations` - Registration details
- `packages` - Package information
- `payment_tokens` - Security tokens for payment links

## 🛡️ Security Features

### 1. **Payment Token Security**
- Each payment link has a unique token
- Tokens are time-limited and single-use
- Prevents unauthorized access to payment forms

### 2. **Data Signing**
- All payment data is signed with HMAC-SHA256
- Prevents tampering with payment information
- CyberSource validates signatures

### 3. **Field Validation**
- Client-side validation for card details
- Server-side validation for all data
- Card type auto-detection

## 📋 Payment Form Fields

### Signed Fields (Security Critical)
- `profile_id` - CyberSource profile ID
- `access_key` - CyberSource access key
- `transaction_uuid` - Unique transaction ID
- `signed_date_time` - Timestamp
- `amount` - Payment amount
- `currency` - Payment currency
- `reference_number` - Registration reference

### Unsigned Fields (User Data)
- `bill_to_forename` - First name
- `bill_to_surname` - Last name
- `bill_to_email` - Email address
- `bill_to_phone` - Phone number
- `bill_to_address_*` - Billing address
- `card_type` - Card type
- `card_number` - Card number
- `card_expiry_date` - Expiry date
- `card_cvn` - CVV code

## 🧪 Testing

### Test Card Numbers (CyberSource Test Environment)
- **Visa**: 4111111111111111
- **Mastercard**: 5555555555554444
- **American Express**: 378282246310005
- **Discover**: 6011111111111117

### Test Script
```bash
php test_payment_flow.php
```

This script:
1. Creates a test user and registration
2. Generates a payment token
3. Creates a payment link
4. Sends a test email
5. Displays the payment form URL

## 🔄 Integration Points

### 1. **Registration Form Integration**
```php
// After successful registration
$paymentToken = generatePaymentToken($registrationId);
$paymentLink = APP_URL . "/checkout_payment.php?registration_id=" . $registrationId . "&token=" . $paymentToken;
sendPaymentLinkEmail($user, $registrationId, $amount);
```

### 2. **Email Template Integration**
The payment link email uses the `payment_link` template with these variables:
- `user_name` - User's full name
- `registration_id` - Registration ID
- `amount` - Payment amount
- `payment_link` - Secure payment URL
- `conference_name` - Conference name

### 3. **Database Updates**
After successful payment:
```sql
UPDATE registrations SET 
    payment_status = 'completed',
    payment_transaction_id = ?,
    payment_amount = ?,
    payment_currency = ?,
    payment_method = 'card',
    payment_completed_at = NOW()
WHERE id = ?
```

## 🚀 Usage Examples

### 1. **Manual Payment Link Generation**
```php
$registrationId = 123;
$paymentToken = generatePaymentToken($registrationId);
$paymentLink = APP_URL . "/checkout_payment.php?registration_id=" . $registrationId . "&token=" . $paymentToken;
```

### 2. **Sending Payment Email**
```php
$user = ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john@example.com'];
$registrationId = 123;
$amount = 200.00;
sendPaymentLinkEmail($user, $registrationId, $amount);
```

### 3. **Checking Payment Status**
```php
$stmt = $pdo->prepare("SELECT payment_status FROM registrations WHERE id = ?");
$stmt->execute([$registrationId]);
$status = $stmt->fetchColumn();
```

## 🔍 Troubleshooting

### Common Issues

1. **"Invalid payment link"**
   - Check if token exists in database
   - Verify token hasn't expired
   - Ensure registration exists

2. **"Payment failed"**
   - Check CyberSource configuration
   - Verify test card numbers
   - Check error logs

3. **"Signature verification failed"**
   - Verify CyberSource secret key
   - Check signed field names
   - Ensure data hasn't been tampered with

### Debug Mode
Set `APP_DEBUG=true` in `.env` to see:
- Full payment response data
- Signature verification status
- Detailed error messages

## 📞 Support

For payment integration issues:
1. Check the error logs
2. Verify CyberSource configuration
3. Test with provided test cards
4. Contact CyberSource support if needed

## 🔐 Security Best Practices

1. **Never log sensitive data** (card numbers, CVV)
2. **Use HTTPS** for all payment pages
3. **Validate all input** on both client and server
4. **Keep CyberSource credentials secure**
5. **Monitor payment logs** for suspicious activity
6. **Regular security updates** for all components

---

*This integration provides a seamless, secure payment experience for conference registrations while maintaining PCI compliance through CyberSource's secure payment processing.*
