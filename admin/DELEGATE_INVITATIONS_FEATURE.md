# Delegate Invitation Letters Feature

## Overview
Approved delegates can now receive and preview invitation letters through the delegate management system. This extends the existing invitation functionality to work with delegate status approvals in addition to payment status.

## What Was Updated

### 1. **InvitationController** (`admin/app/Http/Controllers/InvitationController.php`)

#### Updated Methods:
- **`preview()`** - Now accepts both:
  - Paid registrations (existing behavior)
  - Approved delegates (new behavior)
  
- **`download()`** - Now accepts both:
  - Paid registrations (existing behavior)
  - Approved delegates (new behavior)

#### Logic:
```php
// Allow if paid OR if it's an approved delegate
$isDelegate = $registration->package_id == config('app.delegate_package_id');
$canReceiveInvitation = $registration->isPaid() || ($isDelegate && $registration->status === 'approved');
```

### 2. **Delegates Index Page** (`admin/resources/views/delegates/index.blade.php`)

#### New Features:
1. **Invitation Actions Section** - Blue panel with preview and send buttons
2. **Checkboxes** - Only shown for approved delegates
3. **Select All** - Master checkbox to select all approved delegates
4. **Preview Button** - Opens invitation PDF in new tab (single selection only)
5. **Send Invitations Button** - Sends emails to selected delegates

#### UI Elements:
- Checkboxes appear only in the "approved" status rows
- Preview button enabled only when exactly 1 delegate is selected
- Send button enabled when 1 or more delegates are selected
- Confirmation dialog before sending invitations

### 3. **Delegates Detail Page** (`admin/resources/views/delegates/show.blade.php`)

#### New Section:
Added "Invitation Letter" section that appears only for approved delegates with:
- **Download Button** - Downloads PDF invitation letter
- **Send Email Button** - Sends invitation email to the delegate
- Confirmation dialog before sending email

## Features

### For Index Page (`/delegates`):
1. **Bulk Selection** - Select multiple approved delegates at once
2. **Preview** - Preview invitation letter for a single delegate (opens in new tab)
3. **Bulk Send** - Send invitations to multiple delegates simultaneously
4. **Smart UI** - Buttons disable/enable based on selection

### For Detail Page (`/delegates/{id}`):
1. **Download** - Download invitation PDF for the specific delegate
2. **Send Email** - Send invitation email to the delegate's email address
3. **Only for Approved** - Section only appears when status is "approved"

## How It Works

### Workflow:
1. **Admin reviews delegate application** on delegates page
2. **Admin approves delegate** - Status changes to "approved"
3. **Checkbox appears** next to approved delegate in the list
4. **Admin can:**
   - Select delegate(s) and preview invitation letter
   - Select delegate(s) and send invitation emails
   - Open detail page and download/send individual invitation

### Preview Flow:
```
User clicks Preview → 
Selects 1 approved delegate →
JavaScript creates POST form →
Opens in new tab →
InvitationController checks if approved delegate →
Generates PDF →
Returns PDF stream
```

### Send Flow:
```
User clicks Send Invitations →
Selects approved delegate(s) →
Confirms action →
Submits form to invitations.send route →
InvitationService queues emails →
Background job sends emails
```

## Testing Guide

### Test Prerequisites:
1. Have at least one delegate registration (package_id = delegate_package_id)
2. Approve the delegate registration
3. Ensure invitation template exists with all required images

### Test Case 1: Preview from Index Page
```bash
# Manual Test:
1. Go to /delegates
2. Check the checkbox next to an approved delegate
3. Click "Preview" button
4. PDF should open in new tab
```

### Test Case 2: Send from Index Page
```bash
# Manual Test:
1. Go to /delegates
2. Check multiple approved delegates
3. Click "Send Invitations"
4. Confirm dialog
5. Success message should appear
6. Check email queue for queued emails
```

### Test Case 3: Download from Detail Page
```bash
# Manual Test:
1. Go to /delegates/{id} for an approved delegate
2. Click "Download Invitation Letter"
3. PDF should download to your computer
```

### Test Case 4: Send from Detail Page
```bash
# Manual Test:
1. Go to /delegates/{id} for an approved delegate
2. Click "Send Invitation Email"
3. Confirm dialog
4. Success message should appear
5. Check delegate's email inbox
```

### Test with Artisan:
```bash
cd admin
php artisan tinker

# Create/update a delegate registration
use App\Models\Registration;
$delegate = Registration::where('package_id', config('app.delegate_package_id'))->first();
$delegate->update(['status' => 'approved']);

# Test invitation eligibility
$isDelegate = $delegate->package_id == config('app.delegate_package_id');
$canReceive = $delegate->isPaid() || ($isDelegate && $delegate->status === 'approved');
echo "Can receive invitation: " . ($canReceive ? 'YES' : 'NO');
```

## JavaScript Functionality

### Select All Feature:
- Clicking header checkbox selects/deselects all approved delegates
- Individual selections update the header checkbox (indeterminate state supported)

### Button State Management:
```javascript
// Preview button:
- Disabled when 0 or >1 delegates selected
- Enabled when exactly 1 delegate selected

// Send button:
- Disabled when 0 delegates selected
- Enabled when 1+ delegates selected
```

### Validation:
- Prevents form submission if no delegates selected
- Shows confirmation dialog with count before sending
- Alert messages for invalid actions

## Security & Validation

### Controller Level:
1. **CSRF Protection** - All POST requests require CSRF token
2. **Registration Validation** - Checks registration exists
3. **Status Check** - Verifies approved status for delegates
4. **Package Verification** - Confirms delegate package ID

### UI Level:
1. **Checkboxes** - Only appear for approved delegates
2. **Confirmation Dialogs** - Prevent accidental sends
3. **Authentication** - All routes protected by admin.auth middleware

## Error Handling

### Possible Errors:
1. **Not approved** - "Only paid registrations or approved delegates can receive invitations"
2. **Missing images** - "Failed to generate invitation PDF. Please check if all required images are present"
3. **No selection** - "Please select at least one approved delegate to send invitations"

## Email Queue

Invitations are queued for background processing:
- Multiple invitations don't block the UI
- Failed emails are tracked and reported
- Success message shows queued count

## Integration Points

### Routes Used:
- `POST /invitations/preview` - Preview invitation
- `POST /invitations/send` - Send bulk invitations
- `GET /invitations/download/{registration}` - Download single invitation

### Models Used:
- `Registration` - Main delegate registration model
- `User` - Delegate user information
- `Package` - Package information for template

### Services Used:
- `InvitationService` - Handles bulk invitation queuing
- `Pdf` (Barryvdh\DomPDF) - Generates PDF documents

## Configuration

Required in `.env`:
```env
DELEGATE_PACKAGE_ID=29
```

Required in `config/app.php`:
```php
'delegate_package_id' => env('DELEGATE_PACKAGE_ID', 29),
'fully_sponsored_message' => env('FULLY_SPONSORED_MESSAGE', '...'),
```

## Files Modified

1. `admin/app/Http/Controllers/InvitationController.php` - Added delegate support
2. `admin/resources/views/delegates/index.blade.php` - Added bulk invitation features
3. `admin/resources/views/delegates/show.blade.php` - Added individual invitation section

## UI Screenshots Description

### Index Page:
- Blue invitation panel at top with icon
- Preview and Send buttons (disabled by default)
- Table with checkboxes in first column (only for approved)
- Select all checkbox in table header

### Detail Page:
- "Invitation Letter" section with envelope icon
- Download button (blue)
- Send Email button (green)
- Both buttons side-by-side

## Notes

1. **Dual Criteria** - Invitations work for BOTH paid registrations AND approved delegates
2. **Non-Breaking** - Existing registration invitation functionality unchanged
3. **Consistent UX** - Uses same design patterns as registration page
4. **Performance** - Checkbox state management uses efficient event delegation
5. **Accessibility** - Indeterminate state properly handled for select-all

## Future Enhancements

Potential improvements:
1. Add invitation sent timestamp to database
2. Track invitation email open/click rates
3. Allow custom message when sending invitations
4. Bulk download as ZIP file
5. Resend capability with tracking
6. Email preview before sending

