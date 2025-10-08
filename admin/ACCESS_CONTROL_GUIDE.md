# Role-Based Access Control (RBAC) Guide

## Overview
The system implements a clean, policy-based access control system with four distinct roles.

## Roles & Permissions

### 1. **Admin** (Full Access)
- ✅ View & Manage Registrations
- ✅ View & Manage Delegates (Approve/Reject)
- ✅ View All Payments (Including Pending)
- ✅ View, Send & Download Invitations
- ✅ Manage Packages
- ✅ Manage Admin Users
- ✅ Access Dashboard

### 2. **Secretariat**
- ✅ View Registrations & Payment Dashboard
- ✅ View & Manage Delegates (Approve/Reject)
- ✅ View, Send & Download Invitations
- ✅ Access Dashboard
- ❌ Cannot Manage Packages
- ❌ Cannot Manage Admin Users

### 3. **Finance**
- ✅ View Registrations & Payment Dashboard
- ✅ View All Payments (Including Pending)
- ✅ Access Dashboard
- ❌ Cannot Manage Delegates
- ❌ Cannot View/Send Invitations
- ❌ Cannot Manage Packages
- ❌ Cannot Manage Admin Users

### 4. **Executive**
- ✅ View Only Completed Payments
- ✅ View Only Approved Delegates
- ✅ View & Download Invitations (Approved Only)
- ❌ Cannot Approve/Reject Delegates
- ❌ Cannot Send Invitations
- ❌ Cannot View Pending Payments
- ❌ Cannot Access Standard Dashboard
- ❌ Cannot Manage Packages
- ❌ Cannot Manage Admin Users

## Implementation

### Files Created

1. **Policies**
   - `app/Policies/RegistrationPolicy.php` - Registration & Delegate permissions
   - `app/Policies/PaymentPolicy.php` - Payment permissions

2. **Service Provider**
   - `app/Providers/AuthServiceProvider.php` - Policy registration & Gates

3. **Migration**
   - `database/migrations/2025_01_08_000003_add_role_to_admins_table.php`

4. **Model Methods**
   - Added helper methods to `app/Models/Admin.php`

### Controllers Updated
- `DelegateController.php` - Authorization for delegate management
- `InvitationController.php` - Authorization for invitation actions

### Views Updated
- `layouts/app.blade.php` - Navigation based on permissions
- `delegates/index.blade.php` - Hide actions based on roles
- `delegates/show.blade.php` - Hide actions based on roles
- `registrations/index.blade.php` - Hide actions based on roles
- `registrations/show.blade.php` - Hide actions based on roles

## Setup Instructions

### 1. Run Migration
```bash
cd admin
php artisan migrate
```

### 2. Update config/app.php
Add AuthServiceProvider to the providers array:

```php
'providers' => [
    // ... other providers
    App\Providers\AuthServiceProvider::class,
],
```

### 3. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 4. Create or Update Admin Users

```bash
php artisan tinker
```

```php
use App\Models\Admin;

// Update existing admin
$admin = Admin::where('email', 'admin@example.com')->first();
$admin->update(['role' => 'admin']);

// Create new secretariat user
Admin::create([
    'username' => 'secretariat',
    'email' => 'secretariat@example.com',
    'password' => bcrypt('password'),
    'full_name' => 'Secretariat User',
    'role' => 'secretariat',
    'is_active' => true,
]);

// Create new finance user
Admin::create([
    'username' => 'finance',
    'email' => 'finance@example.com',
    'password' => bcrypt('password'),
    'full_name' => 'Finance User',
    'role' => 'finance',
    'is_active' => true,
]);

// Create new executive user
Admin::create([
    'username' => 'executive',
    'email' => 'executive@example.com',
    'password' => bcrypt('password'),
    'full_name' => 'Executive User',
    'role' => 'executive',
    'is_active' => true,
]);
```

## Policy Usage

### In Controllers
```php
// Check if user can manage delegates
$this->authorize('manageDelegates', Registration::class);

// Check if user can view invitations
$this->authorize('viewInvitation', Registration::class);

// Check if user can send invitations
$this->authorize('sendInvitation', Registration::class);
```

### In Blade Views
```blade
@can('manageDelegates', App\Models\Registration::class)
    <!-- Show approve/reject buttons -->
@endcan

@can('viewInvitation', App\Models\Registration::class)
    <!-- Show view/download invitation buttons -->
@endcan

@can('sendInvitation', App\Models\Registration::class)
    <!-- Show send invitation button -->
@endcan

@can('manage-admins')
    <!-- Show admin management link -->
@endcan

@can('manage-packages')
    <!-- Show package management link -->
@endcan
```

## Gates Defined

- `manage-admins` - Only admin role
- `manage-packages` - Only admin role
- `view-dashboard` - Admin, Secretariat, Finance
- `view-executive-dashboard` - Executive only

## Testing Roles

### Test as Admin
```bash
# Login as admin user
# Should see all menu items and actions
```

### Test as Secretariat
```bash
# Login as secretariat user
# Should see: Dashboard, Registrations, Manage Delegates, Payments
# Should NOT see: Packages, Admins
# Can: Approve/Reject delegates, Send invitations
```

### Test as Finance
```bash
# Login as finance user
# Should see: Dashboard, Registrations, Payments
# Should NOT see: Manage Delegates, Packages, Admins
# Cannot: Approve/Reject delegates, View/Send invitations
```

### Test as Executive
```bash
# Login as executive user
# Should see: Registrations (approved delegates only), Payments (completed only)
# Should NOT see: Dashboard, Manage Delegates, Packages, Admins
# Can: View and download invitations (approved delegates only)
# Cannot: Send invitations, Approve/Reject delegates
```

## Security Features

1. **Controller Authorization** - All sensitive actions check permissions
2. **View-Level Hiding** - Users don't see actions they can't perform
3. **Policy-Based** - Clean separation of authorization logic
4. **Gate System** - Flexible permission checking
5. **No Direct PHP in Views** - Uses Blade directives only

## Troubleshooting

### Issue: "This action is unauthorized"
**Solution**: Check user role and policy definitions

### Issue: Menu items not showing
**Solution**: Clear view cache: `php artisan view:clear`

### Issue: Policies not working
**Solution**: 
1. Ensure AuthServiceProvider is registered in config/app.php
2. Clear config cache: `php artisan config:clear`
3. Check policy class namespace

### Issue: Executive sees everything
**Solution**: Verify role is exactly 'executive' (lowercase) in database

## Best Practices

1. **Never use raw PHP conditions in views** - Always use `@can/@cannot`
2. **Always authorize in controllers** - Don't rely on view-level security alone
3. **Use specific policy methods** - Don't use generic `update()` or `delete()`
4. **Test each role thoroughly** - Verify permissions work as expected
5. **Document role changes** - Update this guide when adding new permissions

## Future Enhancements

Potential improvements:
1. **Role Management UI** - Admin panel to assign roles
2. **Audit Logging** - Track who approved/rejected delegates
3. **Permission Groups** - More granular permissions
4. **Department-Based Access** - Restrict by department
5. **Multi-Role Support** - Users with multiple roles

## Summary

✅ **Clean Code** - No PHP in views, uses Blade directives
✅ **Precise Control** - Users only see what they can access
✅ **Secure** - Authorization checked at controller and view levels
✅ **Maintainable** - Policy-based architecture
✅ **Scalable** - Easy to add new roles and permissions

