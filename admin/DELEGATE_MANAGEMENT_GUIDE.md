# Delegate Management Feature - Implementation Guide

## Overview
A complete delegate management system has been implemented to review, approve, and reject delegate registrations based on the configured delegate package.

## What Was Created

### 1. **Controller**
- `admin/app/Http/Controllers/DelegateController.php`
  - `index()` - Lists all delegate registrations with filtering
  - `show()` - Shows detailed view of a delegate
  - `approve()` - Approves a pending delegate registration
  - `reject()` - Rejects a pending delegate registration with optional reason

### 2. **Routes**
Added to `admin/routes/web.php`:
```php
Route::get('delegates', [DelegateController::class, 'index'])->name('delegates.index');
Route::get('delegates/{registration}', [DelegateController::class, 'show'])->name('delegates.show');
Route::post('delegates/{registration}/approve', [DelegateController::class, 'approve'])->name('delegates.approve');
Route::post('delegates/{registration}/reject', [DelegateController::class, 'reject'])->name('delegates.reject');
```

### 3. **Views**
- `admin/resources/views/delegates/index.blade.php` - Main listing page with filters and status cards
- `admin/resources/views/delegates/show.blade.php` - Detailed view of individual delegate

### 4. **Database Migration**
- `admin/database/migrations/2025_01_08_000001_add_rejection_reason_to_registrations_table.php`
  - Adds `rejection_reason` text field to `registrations` table

### 5. **Navigation**
- Added "Manage Delegates" menu item in the left sidebar
- Located between "Registrations" and "Payments"

### 6. **Configuration**
Already exists in `admin/config/app.php`:
```php
'delegate_package_id' => env('DELEGATE_PACKAGE_ID', 29),
```

## Features

### Main Features:
1. **Filter by Status** - View pending, approved, or rejected delegates
2. **Search** - Search by name or email
3. **Status Summary Cards** - Quick overview of pending, approved, and rejected counts
4. **Quick Actions** - Approve or reject directly from the list
5. **Detailed View** - Full delegate information with action buttons
6. **Rejection Reason** - Optional reason when rejecting a delegate
7. **Confirmation Dialogs** - Prevent accidental approvals/rejections

### Status Management:
- **Pending** - New delegate registrations awaiting review
- **Approved** - Accepted delegates
- **Rejected** - Declined delegates with optional rejection reason

## Testing Guide

### Step 1: Run the Migration
```bash
cd admin
php artisan migrate
```

### Step 2: Access the Feature
1. Log in to the admin panel
2. Click "Manage Delegates" in the left sidebar
3. You should see a list of all registrations with `package_id = 29` (or configured value)

### Step 3: Test Filtering
```bash
# Test with search parameter
curl -H "Cookie: your_session_cookie" \
  "http://localhost/admin/delegates?search=john"

# Test with status filter
curl -H "Cookie: your_session_cookie" \
  "http://localhost/admin/delegates?status=pending"
```

### Step 4: Test Approve Action
```bash
# Approve a delegate (replace {id} with actual registration ID)
curl -X POST \
  -H "Cookie: your_session_cookie" \
  -H "X-CSRF-TOKEN: your_csrf_token" \
  "http://localhost/admin/delegates/{id}/approve"
```

### Step 5: Test Reject Action
```bash
# Reject a delegate with reason
curl -X POST \
  -H "Cookie: your_session_cookie" \
  -H "X-CSRF-TOKEN: your_csrf_token" \
  -d "reason=Does not meet delegate criteria" \
  "http://localhost/admin/delegates/{id}/reject"
```

### Step 6: Test with Artisan Tinker
```bash
cd admin
php artisan tinker

# Get delegate package ID
config('app.delegate_package_id')

# Count delegates by status
use App\Models\Registration;
Registration::where('package_id', config('app.delegate_package_id'))
    ->groupBy('status')
    ->selectRaw('status, count(*) as count')
    ->pluck('count', 'status');

# Approve a delegate
$registration = Registration::find(1);
$registration->update(['status' => 'approved']);

# Reject a delegate with reason
$registration->update([
    'status' => 'rejected',
    'rejection_reason' => 'Test rejection reason'
]);
```

## Environment Configuration

Add to your `.env` file:
```env
DELEGATE_PACKAGE_ID=29
```

This allows you to change the delegate package ID without modifying code.

## UI Features

### Index Page:
- **Status Cards** - Visual summary of pending, approved, and rejected counts
- **Search Bar** - Filter by name or email
- **Status Dropdown** - Filter by status (all, pending, approved, rejected)
- **Sortable Table** - Shows ID, name, email, category, status, and actions
- **Quick Actions** - Approve/reject buttons for pending delegates
- **Modal Dialog** - Rejection reason input

### Detail Page:
- **Status Badge** - Visual indicator of current status
- **Action Buttons** - Approve/reject for pending delegates
- **Personal Info Card** - Name, email, phone, country, organization, job title
- **Delegate Info Card** - Category, package, registration type, date
- **Rejection Reason** - Displayed if status is rejected
- **Additional Participants** - Table of group registration members

## Database Schema

### New Field Added:
```sql
ALTER TABLE registrations 
ADD COLUMN rejection_reason TEXT NULL 
AFTER status;
```

## Security Features

1. **Package Verification** - All actions verify the registration belongs to delegate package
2. **CSRF Protection** - All POST requests require CSRF token
3. **Authentication Required** - All routes protected by admin.auth middleware
4. **Confirmation Dialogs** - JavaScript confirms before approve/reject

## Notes

- The delegate package ID is configurable via `config('app.delegate_package_id')`
- Default value is 29 if not set in environment
- Only registrations with status 'pending' show action buttons
- Rejection reason is optional but recommended for record-keeping
- All actions show success/error flash messages

## Invitation Letters for Approved Delegates

### NEW FEATURE: Send Invitation Letters

Approved delegates can now receive invitation letters directly from the delegate management page!

#### Features:
1. **From Index Page** (`/delegates`):
   - Select one or more approved delegates using checkboxes
   - Click "Preview" to see invitation letter (single selection only)
   - Click "Send Invitations" to email multiple delegates at once

2. **From Detail Page** (`/delegates/{id}`):
   - Download invitation PDF for approved delegate
   - Send invitation email to approved delegate

#### How It Works:
- Only approved delegates show checkboxes and can receive invitations
- Preview button enabled only when exactly 1 delegate is selected
- Send button enabled when 1 or more delegates are selected
- Confirmation dialog before sending emails

#### Testing:
```bash
# Approve a delegate
cd admin
php artisan tinker

use App\Models\Registration;
$delegate = Registration::where('package_id', config('app.delegate_package_id'))->first();
$delegate->update(['status' => 'approved']);

# Now visit /delegates and you'll see checkboxes next to approved delegates
```

See `DELEGATE_INVITATIONS_FEATURE.md` for detailed documentation.

## Next Steps

1. Run the migration to add the `rejection_reason` field
2. Configure the correct `DELEGATE_PACKAGE_ID` in your `.env` file
3. Test the feature by creating some delegate registrations
4. Approve delegates and test the invitation letter functionality
5. Configure email settings to ensure invitations are sent properly
6. Consider adding email notifications for approvals/rejections
7. Consider adding export functionality for approved delegates

