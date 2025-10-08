# Delegate Categories Management Guide

## Overview
Delegate categories are currently set during the **user registration process** on the public-facing registration form. There is no admin UI to manage or edit delegate categories after registration.

## How Categories Work

### 1. **Categories are Set During Registration**
- Users select their delegate category when they register
- The category is stored in the `users` table in the `delegate_category` field
- This happens on the public registration form (not in the admin panel)

### 2. **Fully Sponsored Categories**
Certain delegate categories receive full sponsorship (Visa, Accommodation, Registration fees covered):
- Oral abstract presenter
- Invited speaker/Moderator
- Scientific Program Committee Member
- Secretariat
- Media Partner
- Youth Program Participant

### 3. **All Available Categories**
The complete list is defined in `config/delegates.php`:
- Oral abstract presenter
- Poster presenter
- Invited speaker/Moderator
- Scientific Program Committee Member
- Secretariat
- Media Partner
- Youth Program Participant
- Government Official
- NGO Representative
- Academic/Researcher
- Healthcare Professional
- Private Sector
- Student
- Other

## Where Categories Are Used

### Admin Panel
1. **Delegates Index** (`/delegates`)
   - Shows delegate category in the table

2. **Delegates Detail** (`/delegates/{id}`)
   - Displays delegate category in delegate information section

3. **Invitation Template**
   - Uses category to determine if fully sponsored message should appear

## Configuration

### File: `config/delegates.php`

```php
'categories' => [
    'Oral abstract presenter',
    'Poster presenter',
    // ... etc
],

'fully_sponsored_categories' => [
    'Oral abstract presenter',
    'Invited speaker/Moderator',
    // ... etc
],
```

## Modifying Categories

### To Add a New Category:

1. **Update the config file:**
```php
// config/delegates.php
'categories' => [
    // ... existing categories
    'New Category Name',
],
```

2. **If it should be fully sponsored, add it here too:**
```php
'fully_sponsored_categories' => [
    // ... existing categories
    'New Category Name',
],
```

3. **Update the public registration form** (outside admin panel):
   - Add the new category to the registration form dropdown
   - File location may vary based on your setup

### To Change Fully Sponsored Categories:

Edit `config/delegates.php`:
```php
'fully_sponsored_categories' => [
    'Category 1',
    'Category 2',
    // Add or remove as needed
],
```

### To Change a User's Category (Manual):

Since there's no UI for this, use Artisan Tinker:

```bash
php artisan tinker
```

```php
use App\Models\User;

// Find the user
$user = User::where('email', 'user@example.com')->first();

// Update their category
$user->update(['delegate_category' => 'New Category Name']);

// Verify
echo "User: {$user->full_name} | Category: {$user->delegate_category}";
```

## Future Enhancement: Category Management UI

To add an admin UI for managing categories:

### Option 1: Edit User Categories Directly

Create a user management section where admins can:
1. Search for users
2. Edit their delegate_category field
3. See category change history

### Option 2: Category Master List

Create a database table for categories:
1. Create `delegate_categories` table
2. Manage categories through admin UI
3. Reference categories in dropdown menus
4. Track which categories are fully sponsored

### Implementation Steps (if needed):

1. **Create migration:**
```bash
php artisan make:migration create_delegate_categories_table
```

2. **Add table structure:**
```php
Schema::create('delegate_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->boolean('is_fully_sponsored')->default(false);
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```

3. **Create admin CRUD:**
   - Controller: `DelegateCategoryController`
   - Views: index, create, edit
   - Routes: resource routes

## Current Limitations

❌ **No admin UI to:**
- Add/remove categories
- Edit user's category assignment
- Manage fully sponsored categories
- View category statistics

✅ **What EXISTS:**
- Categories displayed in admin panel
- Fully sponsored logic in invitation template
- Configuration file for easy updates
- Category shown in delegate views

## Workarounds

### To Change Multiple Users' Categories:

```bash
php artisan tinker
```

```php
use App\Models\User;

// Update all users from one category to another
User::where('delegate_category', 'Old Category')
    ->update(['delegate_category' => 'New Category']);

// Or update specific users
User::whereIn('email', [
    'user1@example.com',
    'user2@example.com',
])->update(['delegate_category' => 'New Category']);
```

### To View Category Distribution:

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Get count by category
User::select('delegate_category', DB::raw('count(*) as count'))
    ->groupBy('delegate_category')
    ->orderBy('count', 'desc')
    ->get();
```

## Recommendation

For now, delegate categories are managed through:
1. **Configuration file** (`config/delegates.php`) - for available categories
2. **Registration form** - where users select their category
3. **Manual updates** (via Tinker) - for corrections

If category changes become frequent, consider implementing a full admin UI for category management.

## Related Files

- Configuration: `config/delegates.php`
- Model: `app/Models/User.php` (delegate_category field)
- Views: `resources/views/delegates/index.blade.php`, `resources/views/delegates/show.blade.php`
- Template: `resources/views/invitations/template.blade.php` (uses fully_sponsored logic)

