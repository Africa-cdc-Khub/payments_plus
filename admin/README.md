# CPHIA 2025 Admin Portal

A comprehensive Laravel-based admin portal for managing CPHIA 2025 conference registrations, payments, packages, and invitation letters.

## Features

### 📊 Dashboard
- Real-time statistics (total registrations, paid registrations, pending payments, total revenue)
- Recent registrations overview
- Recent payments overview
- Active packages count

### 👥 Registration Management
- View all registrations with filtering and search
- Filter by payment status (pending/completed)
- Search by user name or email
- View detailed registration information
- Bulk selection for invitation sending
- Preview individual registration details

### 💳 Payment Monitoring
- View all completed payments
- Search payments by user
- Track transaction details
- Payment method tracking
- Payment date and amount tracking

### 📦 Package Management (CRUD)
- Create, read, update, and delete packages
- Configure package details (name, description, price, currency)
- Set package types (individual, group, exhibition, side_event)
- Configure max people per package
- Set continent restrictions (all, africa, other)
- Add custom icons and colors
- Enable/disable packages

### 👤 Admin Management (CRUD)
- Create, read, update, and delete admin users
- Role-based access (admin, super_admin)
- Active/inactive status management
- Password management
- Last login tracking
- Self-protection (cannot delete own account)

### 📧 Invitation System
- Generate PDF invitation letters
- Preview invitations before sending
- **Queue-based bulk sending** (background processing)
- Download individual invitation PDFs
- Email invitations with PDF attachments
- **Automatic retries** (3 attempts with backoff)
- **Job-based email delivery** for reliability
- Automatic visa support letter inclusion
- Professional invitation template

## Technology Stack

- **Backend**: Laravel 12
- **Frontend**: Tailwind CSS, jQuery
- **PDF Generation**: DomPDF
- **Database**: MySQL (existing cphia_payments database)
- **Authentication**: Laravel custom admin guard
- **Architecture**: Repository pattern with service layer

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL database (cphia_payments)

### Step 1: Install Dependencies

```bash
# Navigate to admin folder
cd admin

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### Step 2: Environment Configuration

The `.env` file should already be configured with:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cphia_payments
DB_USERNAME=root
DB_PASSWORD=Admin!2025
```

### Step 3: Build Frontend Assets

```bash
# Build assets for production
npm run build

# Or run development server with hot reload
npm run dev
```

### Step 4: Add PDF Images

Place the following image files in `public/images/` directory:
- `banner.png` - CPHIA 2025 header banner
- `co-chair-1.png` - Signature of Professor Olive Shisana
- `co-chair-2.png` - Signature of Professor Placide Mbala Kingebeni
- `bottom-banner.png` - Africa CDC footer logo

These images are required for PDF invitation generation.

### Step 5: Start Development Server

```bash
php artisan serve
```

Access the admin portal at: `http://localhost:8000`

### Step 6: Start Queue Worker (Required for Email Sending)

The invitation emails are sent using Laravel's queue system for better performance and reliability. You **must** run a queue worker to process the emails:

```bash
# Start the queue worker
php artisan queue:work

# Or use queue:listen for development (auto-reloads on code changes)
php artisan queue:listen

# To run in background (production)
php artisan queue:work --daemon
```

**Important Notes:**
- Invitations are **queued** when you click "Send Invitations"
- The queue worker **must be running** for emails to be sent
- Each job retries **3 times** on failure with a 60-second backoff
- Failed jobs are logged in the `failed_jobs` table

**For Production:**
Use a process manager like Supervisor to keep the queue worker running:
```bash
# Example supervisor configuration
[program:cphia-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/admin/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/admin/storage/logs/queue-worker.log
```

## Default Admin Credentials

Based on the existing database:
- **Username**: `admin`
- **Password**: Use existing password from your database

If you need to create a new admin user, run:
```bash
php artisan db:seed --class=AdminSeeder
```

This will create (or check for existing):
- **Username**: `admin`
- **Email**: `admin@cphia2025.com`
- **Password**: `Admin@2025` (only for new users)
- **Role**: Super Admin

## Project Structure

```
admin/
├── app/
│   ├── Contracts/              # Service interfaces
│   │   ├── InvitationServiceInterface.php
│   │   └── RegistrationRepositoryInterface.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── LoginController.php
│   │   │   ├── AdminController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── InvitationController.php
│   │   │   ├── PackageController.php
│   │   │   ├── PaymentController.php
│   │   │   └── RegistrationController.php
│   │   └── Middleware/
│   │       └── AdminAuth.php
│   ├── Jobs/                   # Queue jobs
│   │   └── SendInvitationJob.php
│   ├── Models/
│   │   ├── Admin.php
│   │   ├── Package.php
│   │   ├── Payment.php
│   │   ├── Registration.php
│   │   ├── RegistrationParticipant.php
│   │   └── User.php
│   ├── Repositories/
│   │   └── RegistrationRepository.php
│   ├── Services/
│   │   └── InvitationService.php
│   └── Providers/
│       └── RepositoryServiceProvider.php
├── resources/
│   ├── css/
│   │   └── app.css             # Tailwind CSS
│   ├── js/
│   │   └── app.js              # jQuery & Font Awesome
│   └── views/
│       ├── auth/
│       │   └── login.blade.php
│       ├── dashboard/
│       │   └── index.blade.php
│       ├── registrations/
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── payments/
│       │   └── index.blade.php
│       ├── admins/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── edit.blade.php
│       ├── packages/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── edit.blade.php
│       ├── invitations/
│       │   └── template.blade.php
│       ├── emails/
│       │   └── invitation.blade.php
│       └── layouts/
│           └── app.blade.php
├── routes/
│   └── web.php
└── config/
    └── auth.php                # Admin guard configuration
```

## Key Features Implementation

### Repository Pattern
All data access is abstracted through repository interfaces, promoting testability and maintainability:

```php
RegistrationRepositoryInterface
  - all()
  - paginate()
  - find()
  - getPaidRegistrations()
  - filterByPaymentStatus()
  - searchByUser()
```

### Service Layer
Business logic is encapsulated in services:

```php
InvitationServiceInterface
  - generatePDF()
  - sendInvitation()
  - sendBulkInvitations()
  - previewInvitation()
```

### Authentication
Custom admin guard using the `admins` table:
- Session-based authentication
- Role-based access (admin, super_admin)
- Active/inactive status checking
- Last login tracking

## Routes

### Public Routes
- `GET /login` - Login form
- `POST /login` - Login submission
- `POST /logout` - Logout

### Protected Routes (Requires admin.auth middleware)
- `GET /` - Dashboard
- Resource routes for:
  - `/registrations` - Registration management
  - `/payments` - Payment monitoring
  - `/admins` - Admin management
  - `/packages` - Package management
- Invitation routes:
  - `POST /invitations/preview` - Preview invitation
  - `POST /invitations/send` - Send bulk invitations
  - `GET /invitations/download/{registration}` - Download PDF

## Usage Guide

### Viewing Registrations
1. Navigate to **Registrations** from the sidebar
2. Use search to filter by name/email
3. Use status dropdown to filter by payment status
4. Click "View" to see detailed information

### Sending Invitations
1. Go to **Registrations**
2. Select paid registrations using checkboxes
3. Use "Select All Paid" for convenience
4. Click "Preview Invitation" to preview one
5. Click "Send Invitations" to email selected users

### Managing Packages
1. Navigate to **Packages**
2. Click "Add New Package" to create
3. Fill in package details:
   - Name, description, price
   - Type (individual/group/exhibition/side_event)
   - Max people, continent restrictions
   - Icon (Font Awesome class: e.g., "fas fa-users")
   - Color (Tailwind class: e.g., "text-blue-600")
4. Toggle "Active" status

### Managing Admins
1. Navigate to **Admins**
2. Click "Add New Admin"
3. Fill in username, email, full name
4. Set password (min 8 characters)
5. Choose role (admin/super_admin)
6. Toggle active status

## Security Features

- Password hashing (bcrypt)
- CSRF protection on all forms
- Session-based authentication
- Middleware protection for admin routes
- Active status verification
- Self-deletion prevention

## Email Configuration

Email settings are already configured in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.africacdc.net
MAIL_PORT=465
MAIL_USERNAME=notifications@africacdc.net
MAIL_PASSWORD="FKZN1?1Oa-)v"
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=notifications@africacdc.net
MAIL_FROM_NAME="Africa CDC"
MAIL_SUBJECT_PREFIX="Approval Management system"
```

**Note:** The credentials above are the production SMTP settings for Africa CDC.

## Database Structure

The admin portal connects to the existing `cphia_payments` database with tables:
- `admins` - Admin users
- `users` - Conference attendees
- `registrations` - Event registrations
- `packages` - Registration packages
- `payments` - Payment transactions
- `registration_participants` - Group registration participants

## Deployment

### Production Build
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Web Server Configuration
For Apache, add to `.htaccess` in public folder:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ index.php [L]
</IfModule>
```

For Nginx:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Maintenance

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Rebuild Assets
```bash
npm run build
```

## Support & Documentation

For issues or questions:
- Email: admin@cphia2025.com
- Laravel Documentation: https://laravel.com/docs
- Tailwind CSS: https://tailwindcss.com/docs

## License

Proprietary - CPHIA 2025

---

**Note**: This admin portal is read-only for registrations (populated by the main payment system). It provides management capabilities for admins, packages, and invitation generation only.
