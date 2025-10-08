# Admin Management & Delegate Categories Update

## Summary

Updated the admin management system to support the new 4-role RBAC system and documented delegate category management.

## ✅ What Was Fixed

### 1. Admin Management System

**Problem:** Admin management only supported old roles (admin, super_admin)

**Solution:** Updated to support new 4-role system

#### Files Updated:

1. **`app/Http/Controllers/AdminController.php`**
   - Added authorization checks (`$this->authorize('manage-admins')`) to all methods
   - Updated validation rules to accept: `admin`, `secretariat`, `finance`, `executive`
   - Removed old `super_admin` role

2. **`resources/views/admins/create.blade.php`**
   - Updated role dropdown with 4 new roles
   - Added descriptive labels: "Admin (Full Access)", etc.
   - Added help text explaining each role's permissions

3. **`resources/views/admins/index.blade.php`**
   - Updated role badges to show all 4 roles with color coding:
     - 🟣 Admin - Purple with crown icon
     - 🔵 Secretariat - Blue with users icon
     - 🟢 Finance - Green with dollar icon
     - 🟡 Executive - Yellow with eye icon

4. **`resources/views/admins/edit.blade.php`**
   - Same updates as create view
   - Properly shows current role when editing

### 2. Delegate Categories

**Problem:** No documentation or UI for managing delegate categories

**Solution:** Created configuration system and comprehensive documentation

#### Files Created:

1. **`config/delegates.php`**
   - Centralized configuration for delegate categories
   - Lists all available categories
   - Defines fully sponsored categories
   - Configurable status options

2. **`DELEGATE_CATEGORIES_GUIDE.md`**
   - Complete guide on how categories work
   - Where they're used in the system
   - How to add/modify categories
   - Workarounds for manual updates
   - Future enhancement recommendations

#### Files Updated:

1. **`resources/views/invitations/template.blade.php`**
   - Now uses `config('delegates.fully_sponsored_categories')`
   - Falls back to hardcoded list if config missing
   - Clean Blade syntax instead of raw PHP

## New Features

### Admin Management (Admin Role Only)

Admins can now:
- ✅ Create new users with specific roles
- ✅ Edit user roles and details
- ✅ Deactivate/activate users
- ✅ Delete users (except themselves)
- ✅ View role badges with clear visual distinction

### Access Control

- Only users with `admin` role can access admin management
- Protected by `manage-admins` gate
- Controller-level authorization on all actions

## Configuration

### Delegate Categories

Edit `config/delegates.php` to:
- Add new delegate categories
- Change fully sponsored categories
- Modify status options

### Admin Roles

Available roles (defined in `AdminController`):
1. **admin** - Full system access
2. **secretariat** - Delegates & invitations
3. **finance** - Payments only
4. **executive** - View only

## Testing

### Test Admin Management:

```bash
# Login as admin user
# Visit /admins
# Should see all admins with proper role badges
# Can create/edit/delete users
```

### Test Role Permissions:

```bash
# Login as secretariat
# Try to access /admins
# Should get 403 Forbidden
```

### Test Categories:

```bash
php artisan tinker

# View all categories
config('delegates.categories');

# View fully sponsored
config('delegates.fully_sponsored_categories');

# Check if category is fully sponsored
in_array('Oral abstract presenter', config('delegates.fully_sponsored_categories'));
```

## Role Descriptions in UI

### Create/Edit Admin Forms:

- **Admin (Full Access)** - Can do everything
- **Secretariat (Delegates & Invitations)** - Manage delegates, send invitations
- **Finance (Payments Only)** - View all payments
- **Executive (View Only)** - View approved delegates & completed payments

### Admin List View:

| Role | Badge Color | Icon |
|------|-------------|------|
| Admin | Purple | 👑 Crown |
| Secretariat | Blue | 👥 Users |
| Finance | Green | 💲 Dollar |
| Executive | Yellow | 👁️ Eye |

## Migration Notes

### Updating Existing Admins:

If you have existing admin users with old roles:

```bash
php artisan tinker

use App\Models\Admin;

// Update all super_admin to admin
Admin::where('role', 'super_admin')->update(['role' => 'admin']);

// Or update specific users
$user = Admin::where('email', 'user@example.com')->first();
$user->update(['role' => 'secretariat']);
```

## Limitations

### Delegate Categories:

- ❌ No admin UI to edit user's category
- ❌ No category statistics dashboard
- ❌ No bulk category updates through UI

### Workarounds:

1. **Manual Updates** - Use Tinker (see `DELEGATE_CATEGORIES_GUIDE.md`)
2. **Configuration** - Edit `config/delegates.php`
3. **Registration Form** - Users select during registration

## Future Enhancements

### Admin Management:
1. Activity log for admin actions
2. Role change history
3. Bulk user import/export
4. Password reset functionality
5. Two-factor authentication

### Delegate Categories:
1. Admin UI for category management
2. Category assignment/editing interface
3. Category statistics dashboard
4. Bulk category updates
5. Category-based reporting

## Files Summary

### Created:
- `config/delegates.php` - Delegate configuration
- `DELEGATE_CATEGORIES_GUIDE.md` - Category documentation
- `ADMIN_AND_CATEGORIES_UPDATE.md` - This file

### Modified:
- `app/Http/Controllers/AdminController.php` - Authorization & validation
- `resources/views/admins/index.blade.php` - Role badges
- `resources/views/admins/create.blade.php` - New roles
- `resources/views/admins/edit.blade.php` - New roles
- `resources/views/invitations/template.blade.php` - Use config

## Quick Reference

### Create New Admin:
1. Go to `/admins`
2. Click "Add New Admin"
3. Fill form and select role
4. Submit

### Change User Role:
1. Go to `/admins`
2. Click "Edit" on user
3. Change role dropdown
4. Submit

### Add Delegate Category:
1. Edit `config/delegates.php`
2. Add to `categories` array
3. If fully sponsored, add to `fully_sponsored_categories`
4. Clear config cache: `php artisan config:clear`

### Check Permissions:
See `ACCESS_CONTROL_GUIDE.md` for complete permission matrix

## Complete!

✅ Admin management updated for 4-role system
✅ Authorization properly enforced
✅ UI shows role badges clearly
✅ Delegate categories documented
✅ Configuration centralized
✅ Ready for production use

