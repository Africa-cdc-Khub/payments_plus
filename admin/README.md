# CPHIA 2025 Admin Panel

A comprehensive admin panel for managing registrations, payments, and system settings for the CPHIA 2025 conference registration system.

## 🎯 Features

### Dashboard
- Real-time statistics (Total registrations, revenue, pending/failed payments)
- Interactive charts (Registration trends, package distribution, nationality breakdown)
- Recent registrations and pending payments
- Quick access to key sections

### Registrations Management
- View all registrations with advanced filtering
- Filter by: Search, Registration type, Package type, Status, Date range
- Export registrations to CSV
- Export all participants to CSV
- View detailed registration information
- View group participants

### Payments Management
- View all payments with filtering
- Update payment status (Pending → Completed/Failed/Cancelled)
- Filter by: Search, Payment status, Date range
- Statistics cards showing payment breakdown
- View detailed payment information
- Auto-update registration status when payment is completed

### Admin User Management
- Create new admin users (Super Admin only)
- Set admin roles (Admin, Super Admin)
- Activate/Deactivate admin accounts
- Delete admin users
- View admin activity (last login)

### Email Management
- Configure MS Exchange OAuth for email sending
- Check email configuration status
- Test email functionality
- Integrated with existing email system

### Settings
- Update admin profile (name, email)
- Change password
- View system information
- Quick links to email configuration

## 🔐 Access & Login

### Access URL
```
http://localhost/payments_plus/admin/
```

### Default Credentials
```
Username: admin
Password: admin123
```

**⚠️ IMPORTANT:** Change the default password immediately after first login!

## 📊 Admin Panel Pages

| Page | URL | Description |
|------|-----|-------------|
| Dashboard | `/admin/` | Main dashboard with statistics and charts |
| Registrations | `/admin/registrations.php` | View and export registrations |
| Registration Details | `/admin/registration-details.php?id=X` | View single registration |
| Payments | `/admin/payments.php` | Manage and update payment statuses |
| Payment Details | `/admin/payment-details.php?id=X` | View single payment |
| Admin Users | `/admin/admins.php` | Manage admin users (Super Admin only) |
| Email OAuth | `/admin/email-oauth.php` | Configure email OAuth |
| Email Status | `/admin/email-status.php` | Check email configuration |
| Settings | `/admin/settings.php` | Update profile and view system info |
| Logout | `/admin/logout.php` | Log out from admin panel |

## 🚀 Getting Started

### First Time Setup

1. **Access the admin panel:**
   ```
   http://localhost/payments_plus/admin/
   ```

2. **Login with default credentials:**
   - Username: `admin`
   - Password: `admin123`

3. **Change your password:**
   - Go to Settings → Change Password
   - Enter current password and new password

4. **Create additional admin users (optional):**
   - Go to Admin Users
   - Fill in the form and click "Create Admin"

5. **Configure email (optional):**
   - Go to Email OAuth Setup
   - Follow Azure AD setup instructions
   - Authenticate with Microsoft

## 📈 Export Features

### Export Registrations
1. Go to Registrations page
2. Apply filters if needed (optional)
3. Click "Export" → "Export Registrations"
4. CSV file will download with all filtered registrations

### Export Participants
1. Go to Registrations page
2. Click "Export" → "Export All Participants"
3. CSV file will download with all group registration participants

**Export Fields:**

**Registrations CSV includes:**
- Registration ID, Date, Name, Email, Phone
- Country, Nationality, Organization
- Package details, Registration type
- Amount, Currency, Status, Payment reference

**Participants CSV includes:**
- Registration details, Package name
- Primary contact information
- Participant details (Title, Name, Email, Nationality, Passport, Organization)

## 💳 Payment Status Management

### How to Verify and Update Payment Status

1. **Go to Payments page**
2. **Filter for pending payments** (optional)
3. **Click the edit icon** (pencil) on the payment you want to update
4. **Select new status:**
   - **Pending**: Payment not yet received
   - **Completed**: Payment verified and received
   - **Failed**: Payment attempt failed
   - **Cancelled**: Payment cancelled

5. **Click "Update Status"**

**Note:** When you set a payment to "Completed", the system automatically updates the related registration status to "Paid".

## 🔍 Filtering Options

### Registrations Filters
- **Search**: Name, email, payment reference
- **Registration Type**: Individual, Group
- **Package Type**: Individual, Group, Exhibition
- **Status**: Pending, Paid, Cancelled
- **Date Range**: From/To dates

### Payments Filters
- **Search**: Name, email, reference, transaction ID
- **Payment Status**: Pending, Completed, Failed, Cancelled
- **Date Range**: From/To dates

## 👥 Admin Roles

### Super Admin
- Full access to all features
- Can create and manage admin users
- Can delete other admins
- Cannot delete or deactivate own account

### Admin
- Access to Dashboard, Registrations, Payments
- Can update payment statuses
- Can export data
- Access to email management and settings
- Cannot manage other admin users

## 🎨 Dashboard Statistics

### Key Metrics
1. **Total Registrations**: Count of all registrations
2. **Total Revenue**: Sum of all completed payments
3. **Pending Payments**: Count of pending payments
4. **Failed Payments**: Count of failed payments

### Charts
1. **Registration Trend**: Line chart showing last 7 days
2. **Registration Type Distribution**: Doughnut chart (Individual vs Group)
3. **Package Type Distribution**: Bar chart by package types
4. **Nationality Distribution**: Pie chart (African vs Non-African)

## 🔧 Troubleshooting

### Cannot Login
- Check that the admins table exists in the database
- Run migration: `php admin/migrations/create_admins_table.php`
- Try default credentials: admin / admin123

### Database Connection Error
- Check database is running
- Verify `.env` file settings
- Check `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`

### Export Not Working
- Check file permissions
- Ensure PHP has write access to output
- Check browser download settings

### Charts Not Displaying
- Check JavaScript console for errors
- Ensure Chart.js CDN is accessible
- Clear browser cache

## 📱 Browser Support

- Chrome/Edge (Recommended)
- Firefox
- Safari
- Opera

Mobile responsive design included.

## 🛡️ Security Features

- Password hashing (bcrypt)
- Session-based authentication
- CSRF protection
- SQL injection prevention (prepared statements)
- XSS protection
- Role-based access control
- Secure password requirements (minimum 6 characters)

## 📝 Notes

- All actions are logged with timestamps
- Payment status updates also update registration status
- Email configuration is optional but recommended
- Regular backups of database recommended
- Change default admin password immediately
- Admin panel is not linked from public site for security

## 🆘 Support

For issues or questions:
1. Check this README
2. Review error messages
3. Check browser console for JavaScript errors
4. Check PHP error logs
5. Contact system administrator

---

**CPHIA 2025 Admin Panel v1.0.0**  
Built with AdminLTE 3 & PHP


