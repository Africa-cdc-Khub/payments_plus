# Approved Delegates Feature

## Overview
A dedicated page for viewing and managing approved delegate registrations with advanced filtering and CSV export capabilities.

## Features

### 1. Menu Item
**Location:** Sidebar navigation  
**Accessible by:** Admin and Executive roles ONLY  
**Icon:** Check circle (✓)  
**Route:** `/approved-delegates`

### 2. List View
Displays all delegates with status = 'approved' and package_id = delegate package.

**Columns:**
- ID
- Name (with title if available)
- Email
- Organization (with position if available)
- Country
- Category (with badge)
- Approved Date
- Actions (View, Invitation)

### 3. Filters

#### Search Filter
- Search by first name, last name, or email
- Real-time text input
- Case-insensitive matching

#### Delegate Category Filter
- Dropdown populated with unique categories from approved delegates
- Examples: Government, NGO, Academia, Private Sector, etc.
- "All Categories" option to clear filter

#### Country Filter
- Dropdown populated with unique countries from approved delegates
- Alphabetically sorted
- "All Countries" option to clear filter

#### Filter UI
- Collapsible filter panel (toggle with "Filters" button)
- Auto-expanded when filters are active
- "Apply" button to submit filters
- "Clear" button to reset all filters
- Filters persist across pagination

### 4. Statistics Dashboard

**Total Approved:**
- Shows count of all approved delegates
- Green badge with check icon
- Updates based on active filters

**Filtered Category** (when active):
- Shows currently filtered category
- Blue badge with filter icon
- Only visible when category filter is applied

**Filtered Country** (when active):
- Shows currently filtered country
- Purple badge with globe icon
- Only visible when country filter is applied

### 5. CSV Export

**Features:**
- Exports all approved delegates matching current filters
- Filename format: `approved_delegates_YYYY-MM-DD_HHmmss.csv`
- Respects active filters (search, category, country)
- No pagination limit (exports all matching records)

**CSV Columns:**
1. ID
2. First Name
3. Last Name
4. Email
5. Phone
6. Title
7. Organization
8. Position
9. Country
10. City
11. Delegate Category
12. Dietary Requirements
13. Special Needs
14. Requires Visa (Yes/No)
15. Registration Date
16. Approval Date

**Export Button:**
- Green button with CSV icon
- Located in header next to Filters button
- Includes hidden inputs to preserve active filters

### 6. Integration

**PDF Preview:**
- Integrated invitation preview modal
- Click "Invitation" to view invitation letter
- Uses existing `invitation-preview-modal` component

**Detail View:**
- "View" link navigates to delegate detail page
- Uses existing `delegates.show` route

## Access Control

**Who Can Access:**
- ✅ Admin (Super Admin)
- ✅ Executive
- ❌ Secretariat
- ❌ Finance

**Authorization:**
- Menu item only visible to Admin and Executive roles
- Controller checks user role on every request
- Returns 403 Forbidden if unauthorized user tries to access directly

**Permissions:**
- View approved delegates list
- Filter delegates
- Export to CSV
- Preview invitations (if `viewInvitation` permission granted)

## Routes

```php
// Index page
GET /approved-delegates
Route name: approved-delegates.index

// CSV Export
GET /approved-delegates/export
Route name: approved-delegates.export
```

## Controller Methods

### `index(Request $request)`
**Purpose:** Display paginated list of approved delegates with filters

**Query Parameters:**
- `search` - Text search
- `delegate_category` - Filter by category
- `country` - Filter by country
- `page` - Pagination

**Returns:** View with delegates, categories, and countries

### `export(Request $request)`
**Purpose:** Generate CSV file of approved delegates

**Query Parameters:**
- Same as index (search, delegate_category, country)

**Returns:** Streaming CSV download response

## Database Queries

### Main Query
```php
Registration::with(['user', 'package'])
    ->where('package_id', config('app.delegate_package_id'))
    ->where('status', 'approved')
```

### With Filters
```php
// Category filter
->whereHas('user', function($q) {
    $q->where('delegate_category', $category);
})

// Country filter
->whereHas('user', function($q) {
    $q->where('country', $country);
})

// Search filter
->whereHas('user', function($q) use ($search) {
    $q->where('first_name', 'like', "%{$search}%")
      ->orWhere('last_name', 'like', "%{$search}%")
      ->orWhere('email', 'like', "%{$search}%");
})
```

## User Interface

### Filter Panel (Collapsed by Default)
```
┌──────────────────────────────────────────────┐
│ [ Filters ▼ ]  [ Export CSV ]                │
└──────────────────────────────────────────────┘
```

### Filter Panel (Expanded)
```
┌──────────────────────────────────────────────┐
│ Search: [____________]  Category: [v]        │
│ Country: [v]  [ Apply ] [ Clear ]            │
└──────────────────────────────────────────────┘
```

### Table View
```
┌────┬──────────┬─────────────┬──────────────┬─────────┬──────────┬────────────┬─────────┐
│ ID │ Name     │ Email       │ Organization │ Country │ Category │ Approved   │ Actions │
├────┼──────────┼─────────────┼──────────────┼─────────┼──────────┼────────────┼─────────┤
│ 57 │ John Doe │ john@...    │ ACME Corp    │ Kenya   │ [NGO]    │ Oct 9, 2025│ View... │
└────┴──────────┴─────────────┴──────────────┴─────────┴──────────┴────────────┴─────────┘
```

## Usage Workflow

### Basic Viewing
1. Click "Approved Delegates" in sidebar
2. View list of all approved delegates
3. See total count in statistics panel
4. Browse paginated results

### Filtering
1. Click "Filters" button
2. Enter search term or select category/country
3. Click "Apply"
4. View filtered results
5. Statistics update to show filtered counts
6. Click "Clear" to reset filters

### Exporting
1. Apply desired filters (optional)
2. Click "Export CSV" button
3. CSV file downloads automatically
4. Filename includes current date/time
5. File contains all matching records (not just current page)

### Viewing Details
1. Click "View" on any delegate row
2. Navigate to delegate detail page
3. See full registration information

### Preview Invitation
1. Click "Invitation" on any delegate row
2. PDF preview modal opens
3. View or download invitation letter

## Example Use Cases

### Use Case 1: Export All Approved Delegates
```
1. Navigate to Approved Delegates
2. Click "Export CSV"
3. Receive CSV with all approved delegates
```

### Use Case 2: Find Government Delegates from Kenya
```
1. Click "Filters"
2. Select Category: "Government"
3. Select Country: "Kenya"
4. Click "Apply"
5. View filtered list
6. Optionally export to CSV
```

### Use Case 3: Search for Specific Delegate
```
1. Click "Filters"
2. Enter name in search box
3. Click "Apply"
4. View matching results
```

## Files Structure

```
admin/
├── app/
│   └── Http/
│       └── Controllers/
│           └── ApprovedDelegateController.php
├── resources/
│   └── views/
│       ├── approved-delegates/
│       │   └── index.blade.php
│       └── layouts/
│           └── app.blade.php (updated sidebar)
└── routes/
    └── web.php (new routes added)
```

## Technical Details

### Pagination
- 20 records per page
- Laravel pagination links at bottom
- Filters preserved across pages

### Performance
- Eager loading of relationships (`user`, `package`)
- Indexed database queries
- Efficient CSV streaming (no memory overhead)

### CSV Generation
- Uses PHP output stream
- No temporary files created
- Memory efficient for large datasets
- UTF-8 encoding
- Proper CSV escaping

## Testing

### Test Filter Functionality
```
1. Apply category filter → Verify only matching delegates shown
2. Apply country filter → Verify only matching delegates shown
3. Apply both filters → Verify AND logic works
4. Use search → Verify name/email matching works
5. Clear filters → Verify all delegates shown again
```

### Test Export
```
1. Export without filters → Verify all delegates in CSV
2. Apply filters and export → Verify only filtered delegates in CSV
3. Check CSV headers → Verify all columns present
4. Check CSV data → Verify data accuracy
5. Check filename → Verify timestamp format
```

### Test Access Control
```
1. Login as Admin → Should see menu item and access page
2. Login as Executive → Should see menu item and access page
3. Login as Secretariat → Should NOT see menu item, 403 if accessing directly
4. Login as Finance → Should NOT see menu item, 403 if accessing directly
```

## Benefits

✅ **Centralized View** - All approved delegates in one place  
✅ **Advanced Filtering** - Find delegates quickly by multiple criteria  
✅ **Data Export** - Easy reporting and external use  
✅ **Role-Based Access** - Executives can view without manage permissions  
✅ **Consistent UI** - Matches existing application design  
✅ **Performance** - Efficient queries and CSV generation  

## Future Enhancements

Potential improvements:
- Bulk invitation sending
- Additional filter options (date range, special needs, etc.)
- Excel export format
- Print-friendly view
- Delegate statistics charts
- Email list copy button
- Badge printing functionality

## Summary

The Approved Delegates feature provides a comprehensive solution for viewing, filtering, and exporting approved delegate registrations. It offers powerful filtering capabilities, efficient CSV export, and integrates seamlessly with existing invitation and detail views.

