# RBAC Setup & Testing Commands

## Quick Setup

### 1. Run Migrations
```bash
cd admin
php artisan migrate
```

### 2. Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 3. Create Test Users (Tinker)
```bash
php artisan tinker
```

```php
use App\Models\Admin;

// Create Admin User
Admin::create([
    'username' => 'admin',
    'email' => 'admin@cphia.org',
    'password' => bcrypt('admin123'),
    'full_name' => 'System Administrator',
    'role' => 'admin',
    'is_active' => true,
]);

// Create Secretariat User
Admin::create([
    'username' => 'secretariat',
    'email' => 'secretariat@cphia.org',
    'password' => bcrypt('secret123'),
    'full_name' => 'Secretariat Officer',
    'role' => 'secretariat',
    'is_active' => true,
]);

// Create Finance User
Admin::create([
    'username' => 'finance',
    'email' => 'finance@cphia.org',
    'password' => bcrypt('finance123'),
    'full_name' => 'Finance Officer',
    'role' => 'finance',
    'is_active' => true,
]);

// Create Executive User
Admin::create([
    'username' => 'executive',
    'email' => 'executive@cphia.org',
    'password' => bcrypt('exec123'),
    'full_name' => 'Executive Officer',
    'role' => 'executive',
    'is_active' => true,
]);
```

## Test Credentials

| Role | Username | Email | Password |
|------|----------|-------|----------|
| Admin | admin | admin@cphia.org | admin123 |
| Secretariat | secretariat | secretariat@cphia.org | secret123 |
| Finance | finance | finance@cphia.org | finance123 |
| Executive | executive | executive@cphia.org | exec123 |

## Update Existing User Role

```bash
php artisan tinker
```

```php
use App\Models\Admin;

// Find and update user
$admin = Admin::where('email', 'your@email.com')->first();
$admin->update(['role' => 'secretariat']); // or 'finance', 'executive', 'admin'

// Verify
echo "User: {$admin->email} | Role: {$admin->role}";
```

## Verify Permissions (Tinker)

```php
use App\Models\Admin;

// Get user
$admin = Admin::where('email', 'secretariat@cphia.org')->first();

// Check role methods
$admin->isAdmin(); // false
$admin->isSecretariat(); // true
$admin->canManageDelegates(); // true
$admin->canViewInvitations(); // true
$admin->canSendInvitations(); // true

// Check different user
$finance = Admin::where('role', 'finance')->first();
$finance->canManageDelegates(); // false
$finance->canViewInvitations(); // false

$executive = Admin::where('role', 'executive')->first();
$executive->canViewInvitations(); // true
$executive->canSendInvitations(); // false
```

## SQL Queries (Direct Database)

### View All Admins and Roles
```sql
SELECT id, username, email, full_name, role, is_active 
FROM admins;
```

### Update User Role
```sql
UPDATE admins 
SET role = 'secretariat' 
WHERE email = 'user@example.com';
```

### Add Role to Existing Users (if role column exists but is null)
```sql
UPDATE admins 
SET role = 'admin' 
WHERE role IS NULL OR role = '';
```

## Testing Checklist

### Test as Admin ✅
- [ ] Login successful
- [ ] See all menu items (Dashboard, Registrations, Delegates, Payments, Packages, Admins)
- [ ] Can approve/reject delegates
- [ ] Can view/download invitations
- [ ] Can send invitations
- [ ] Can manage packages
- [ ] Can manage admin users

### Test as Secretariat ✅
- [ ] Login successful
- [ ] See: Dashboard, Registrations, Manage Delegates, Payments
- [ ] Don't see: Packages, Admins
- [ ] Can approve/reject delegates
- [ ] Can view/download/send invitations
- [ ] Cannot access packages page
- [ ] Cannot access admins page

### Test as Finance ✅
- [ ] Login successful
- [ ] See: Dashboard, Registrations, Payments
- [ ] Don't see: Manage Delegates, Packages, Admins
- [ ] Can view all payments (including pending)
- [ ] Cannot see delegate management
- [ ] Cannot see invitation buttons
- [ ] Cannot access packages/admins

### Test as Executive ✅
- [ ] Login successful
- [ ] See: Registrations, Payments
- [ ] Don't see: Dashboard, Manage Delegates, Packages, Admins
- [ ] Only see approved delegates in registrations
- [ ] Only see completed payments
- [ ] Can view/download invitations (approved delegates only)
- [ ] Cannot send invitations
- [ ] Cannot approve/reject delegates

## Troubleshooting

### Clear Everything
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear
```

### Check if Policy is Working
```bash
php artisan tinker
```

```php
use App\Models\Admin;
use App\Models\Registration;

$admin = Admin::where('role', 'secretariat')->first();
Gate::forUser($admin)->allows('manageDelegates', Registration::class); // should be true

$finance = Admin::where('role', 'finance')->first();
Gate::forUser($finance)->allows('manageDelegates', Registration::class); // should be false
```

### Verify AuthServiceProvider is Loaded
```bash
php artisan about
```
Look for `App\Providers\AuthServiceProvider` in the list of providers.

## Production Deployment

1. Run migration:
   ```bash
   php artisan migrate --force
   ```

2. Update existing admin users:
   ```bash
   php artisan tinker
   ```
   ```php
   Admin::query()->update(['role' => 'admin']);
   ```

3. Clear caches:
   ```bash
   php artisan optimize
   ```

4. Create role-specific users as needed

## Security Notes

- ⚠️ Change default passwords immediately after setup
- ⚠️ Only create executive users for trusted personnel
- ⚠️ Regularly audit user roles and permissions
- ⚠️ Keep the ACCESS_CONTROL_GUIDE.md updated with any changes

