# Domain & URL Configuration

## Overview
This document explains how to configure URLs in the CPHIA 2025 system to ensure emails and links work correctly across different domains and environments.

## Configuration File

**Location:** `config/domains.php`

This file centralizes all URL configurations used across the application, particularly in email templates.

## Environment Variables

Add these to your `.env` file:

```env
# Admin Application URL (where admins login)
APP_URL=http://localhost:8000
# or
APP_URL=https://admin.cphia2025.com

# Parent Application URL (main registration website)
PARENT_APP_URL=http://localhost
# or  
PARENT_APP_URL=https://cphia2025.com

# Contact Email (used in email templates)
CONTACT_EMAIL=info@cphia2025.com
```

## Configuration Details

### 1. Admin URL (`domains.admin_url`)

**Purpose:** Used for admin-related links in emails  
**Used in:**
- Admin login links (credentials emails)
- Admin password reset links
- Any admin panel references

**Example Values:**
- Development: `http://localhost:8000`
- Staging: `https://staging-admin.cphia2025.com`
- Production: `https://admin.cphia2025.com`

**Usage in code:**
```php
config('domains.admin_url')
```

**Usage in templates:**
```blade
{{ rtrim(config('domains.admin_url'), '/') }}/login
```

### 2. Parent URL (`domains.parent_url`)

**Purpose:** Points to the main registration website  
**Used in:**
- Registration links in rejection emails
- Website references in emails
- Any public-facing links

**Example Values:**
- Development: `http://localhost`
- Staging: `https://staging.cphia2025.com`
- Production: `https://cphia2025.com`

**Usage in code:**
```php
config('domains.parent_url')
```

**Usage in templates:**
```blade
{{ rtrim(config('domains.parent_url'), '/') }}/register
```

### 3. Contact Email (`domains.contact.email`)

**Purpose:** Default contact email shown in email templates  
**Used in:**
- Footer of all emails
- Support contact information
- Help links

**Usage in templates:**
```blade
{{ config('domains.contact.email') }}
```

## Deployment Scenarios

### Local Development

```env
APP_URL=http://localhost:8000
PARENT_APP_URL=http://localhost
CONTACT_EMAIL=dev@localhost
```

**Result:**
- Admin links: `http://localhost:8000/login`
- Registration links: `http://localhost/register`

### Staging Environment

```env
APP_URL=https://staging-admin.cphia2025.com
PARENT_APP_URL=https://staging.cphia2025.com
CONTACT_EMAIL=staging@cphia2025.com
```

**Result:**
- Admin links: `https://staging-admin.cphia2025.com/login`
- Registration links: `https://staging.cphia2025.com/register`

### Production Environment

```env
APP_URL=https://admin.cphia2025.com
PARENT_APP_URL=https://cphia2025.com
CONTACT_EMAIL=info@cphia2025.com
```

**Result:**
- Admin links: `https://admin.cphia2025.com/login`
- Registration links: `https://cphia2025.com/register`

## Email Templates Using Domain Config

### Admin Credentials Email
- Login URL: `config('domains.admin_url')/login`

### Admin Password Reset Email  
- Login URL: `config('domains.admin_url')/login`

### Delegate Rejection Email
- Registration URL: `config('domains.parent_url')/register`
- Website URL: `config('domains.parent_url')`
- Contact Email: `config('domains.contact.email')`

### Invitation Emails
- Can reference both domains as needed

## Best Practices

### 1. Always Use Config
❌ **Don't hardcode URLs:**
```blade
<a href="http://cphia2025.com/register">Register</a>
```

✅ **Use config:**
```blade
<a href="{{ config('domains.parent_url') }}/register">Register</a>
```

### 2. Trim Trailing Slashes
Always trim URLs to avoid double slashes:
```blade
{{ rtrim(config('domains.admin_url'), '/') }}/login
```

### 3. Use Correct Domain
- Admin features → `domains.admin_url`
- Public registration → `domains.parent_url`
- Contact info → `domains.contact.email`

## Testing URLs

### Test Admin Emails
```bash
php artisan tinker
```
```php
// Check admin URL
config('domains.admin_url')

// Test email
dispatch(new \App\Jobs\SendAdminCredentials([
    'username' => 'test',
    'email' => 'test@example.com',
    'full_name' => 'Test User',
    'role' => 'admin'
], 'TestPassword123'));
```

### Test Delegate Emails
```php
// Check parent URL
config('domains.parent_url')

// Test rejection email
dispatch(new \App\Jobs\SendDelegateRejectionEmail(1));
```

## Troubleshooting

### Wrong Domain in Emails

**Problem:** Emails show `localhost` in production

**Solution:** 
1. Check `.env` has correct `APP_URL` and `PARENT_APP_URL`
2. Clear config cache: `php artisan config:clear`
3. Restart queue workers: `php artisan queue:restart`

### Links Not Working

**Problem:** Links in emails return 404

**Solution:**
1. Verify URL format (no double slashes)
2. Check route exists on target domain
3. Test URL manually in browser

### Email Showing Wrong Contact

**Problem:** Emails show wrong contact email

**Solution:**
1. Update `CONTACT_EMAIL` in `.env`
2. Clear config cache
3. Resend test email

## Summary

✅ **Configure once** in `.env`  
✅ **Use everywhere** via `config('domains.*)`  
✅ **Works correctly** across all environments  
✅ **Easy to update** - change `.env` and restart workers  

This ensures all emails and links point to the correct domains regardless of where the application is deployed.

