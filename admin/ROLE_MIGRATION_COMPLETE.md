# Admin Role Migration - Complete ✅

## Migration Summary

Successfully updated the `admins` table role column from ENUM to VARCHAR(50) to support the new 4-role RBAC system.

## What Was Done

### 1. Created Migration
**File:** `2025_10_08_100928_update_admin_roles_to_new_system.php`

### 2. Migration Actions
- ✅ Converted `role` column from ENUM to VARCHAR(50)
- ✅ Migrated `super_admin` → `admin`
- ✅ Set default role to `admin` for any null/empty values
- ✅ Preserved existing `admin` roles

### 3. Verified Results
```
Current Admin Roles:
====================
ID: 1 | Username: admin | Role: admin | Active: Yes
ID: 4 | Username: adminstrator | Role: admin | Active: Yes

Role Distribution:
  admin: 2
```

## New Role System

The database now supports 4 roles (VARCHAR, not ENUM):

| Role | Access Level | Capabilities |
|------|-------------|--------------|
| **admin** | Full Access | Everything - user management, delegates, payments, packages |
| **secretariat** | Delegates & Invitations | Manage delegates, approve/reject, send invitations |
| **finance** | Payments Only | View all payments and registration dashboard |
| **executive** | View Only | View approved delegates and completed payments only |

## Database Schema

### Before:
```sql
role ENUM('super_admin', 'admin') DEFAULT 'admin'
```

### After:
```sql
role VARCHAR(50) DEFAULT 'admin'
```

## Valid Role Values

The application now validates these role values in:
- `AdminController::store()` - validation rules
- `AdminController::update()` - validation rules

```php
'role' => ['required', 'in:admin,secretariat,finance,executive']
```

## Creating New Admin Users

You can now create admins with any of the 4 roles:

### Via Admin UI:
1. Go to `/admins`
2. Click "Add New Admin"
3. Select role from dropdown
4. Submit

### Via Tinker:
```php
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

Admin::create([
    'username' => 'finance.user',
    'email' => 'finance@example.com',
    'password' => Hash::make('password'),
    'full_name' => 'Finance User',
    'role' => 'finance',
    'is_active' => true,
]);
```

## Updating Existing Users

### Via Admin UI:
1. Go to `/admins`
2. Click "Edit" on user
3. Change role dropdown
4. Submit

### Via Tinker:
```php
use App\Models\Admin;

$user = Admin::where('email', 'admin@cphia2025.com')->first();
$user->update(['role' => 'secretariat']);
```

## Migration Details

### Migration File Location:
```
admin/database/migrations/2025_10_08_100928_update_admin_roles_to_new_system.php
```

### Migration Status:
```bash
php artisan migrate:status
```

Output:
```
2025_10_08_100928_update_admin_roles_to_new_system ................ [3] Ran
```

### Rollback (if needed):
```bash
php artisan migrate:rollback --step=1
```

**⚠️ Warning:** Rolling back will revert to ENUM('super_admin', 'admin') and lose secretariat/finance/executive users.

## Testing

### Test Role Creation:
```bash
# Create a secretariat user
php artisan tinker

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

Admin::create([
    'username' => 'secretariat.test',
    'email' => 'secretariat@test.com',
    'password' => Hash::make('Test123!'),
    'full_name' => 'Secretariat Test User',
    'role' => 'secretariat',
    'is_active' => true,
]);
```

### Test Permissions:
1. Login as different role types
2. Verify menu items show/hide correctly
3. Test access to restricted pages
4. Verify authorization works

## Related Files

### Updated:
- `app/Http/Controllers/AdminController.php` - Validation rules
- `resources/views/admins/create.blade.php` - Role dropdown
- `resources/views/admins/edit.blade.php` - Role dropdown
- `resources/views/admins/index.blade.php` - Role badges
- `database/migrations/2025_10_08_100928_update_admin_roles_to_new_system.php` - New migration

### Authorization:
- `app/Policies/RegistrationPolicy.php` - Registration & delegate permissions
- `app/Policies/PaymentPolicy.php` - Payment permissions
- `app/Providers/AuthServiceProvider.php` - Policy registration
- `app/Models/Admin.php` - Role helper methods

## Current Admin Users

After migration, all existing users have `role = 'admin'`:
- **admin@cphia2025.com** - Full admin access
- **adminstrator@cphia2025.com** - Full admin access

To assign different roles:
1. Use the admin UI at `/admins`
2. Edit each user and select appropriate role
3. Save changes

## Troubleshooting

### Check Current Roles:
```bash
php artisan tinker
DB::table('admins')->select('id', 'username', 'role')->get();
```

### Fix Invalid Roles:
```php
// Set all invalid roles to 'admin'
DB::table('admins')
    ->whereNotIn('role', ['admin', 'secretariat', 'finance', 'executive'])
    ->update(['role' => 'admin']);
```

### Verify Column Type:
```bash
php artisan db:show --table=admins
```

Look for:
```
role | varchar(50) | YES | admin
```

## Complete! ✅

The database role column has been successfully updated to support the new 4-role RBAC system. You can now:
- ✅ Create users with any of the 4 roles
- ✅ Update existing users to new roles
- ✅ No more ENUM limitations
- ✅ Full flexibility for role-based access control

## Next Steps

1. **Assign Roles** - Edit existing admin users to assign appropriate roles
2. **Create New Users** - Add secretariat, finance, or executive users as needed
3. **Test Permissions** - Login as different roles to verify access control
4. **Document Users** - Keep track of who has which role

---

Migration completed on: **{{ date('Y-m-d H:i:s') }}**

