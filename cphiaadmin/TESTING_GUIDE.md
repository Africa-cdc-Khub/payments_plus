# 🧪 CPHIA 2025 Laravel Admin - Testing Guide

**Ready to Test:** October 4, 2025  
**System Status:** Phase 4 Complete - 85% Done

---

## **🚀 Quick Start Testing**

### **1. Access the System**
```
URL: http://localhost/payments_plus/cphiaadmin/public/admin/login
```

### **2. Login Credentials**

| Username | Password | Test This |
|----------|----------|-----------|
| **admin** | `Admin@2025` | Full system access |
| **finance** | `Finance@2025` | Payment management |
| **visa** | `Visa@2025` | Visa applications |
| **ticketing** | `Ticketing@2025` | Attendance tracking |

---

## **✅ Testing Checklist**

### **Phase 3: Authentication & Dashboard** ✅

**Login Page:**
- [ ] Page loads with purple gradient
- [ ] Can login with valid credentials
- [ ] Error shown for invalid credentials
- [ ] Remember me checkbox works
- [ ] Redirects to dashboard after login

**Dashboard:**
- [ ] Statistics cards display correctly
- [ ] Charts render (line & doughnut)
- [ ] Recent registrations table shows data
- [ ] Recent payments table shows data (Finance role)
- [ ] Sidebar navigation displays
- [ ] User dropdown menu works
- [ ] Logout works

---

### **Phase 4: CRUD Modules** ✅

#### **Registrations Module:**
- [ ] Click "Registrations" in sidebar
- [ ] DataTables loads successfully
- [ ] Table shows registration data
- [ ] Filter by registration type works
- [ ] Filter by status works
- [ ] Filter by payment status works
- [ ] Date range filter works
- [ ] Search box filters results
- [ ] "Apply Filters" button works
- [ ] "Reset" button clears filters
- [ ] "Export" button downloads Excel file
- [ ] "View" button opens details page
- [ ] Pagination works
- [ ] Table is sortable

**Expected Data Columns:**
- ID
- Name
- Email
- Type (Individual/Side Event/Exhibition)
- Package
- Amount (with currency)
- Status (badge)
- Payment Status (badge)
- Participants count
- Date
- Actions (View button)

#### **Payments Module:**
- [ ] Click "Payments" in sidebar
- [ ] DataTables loads successfully
- [ ] Filter by payment status works
- [ ] Date range filter works
- [ ] Search functionality works
- [ ] "Export" button downloads file
- [ ] Click "Update Status" button
- [ ] Modal opens with form
- [ ] Can change status
- [ ] "Save Changes" updates status
- [ ] Table refreshes after update
- [ ] Success message shown

**Expected Data Columns:**
- ID
- Name
- Transaction UUID
- Reference
- Amount (with currency)
- Status (badge: pending/completed/failed)
- Method
- Date
- Actions (View & Update buttons)

#### **Users/Participants Module:**
- [ ] Click "Participants" in sidebar
- [ ] DataTables loads successfully
- [ ] Filter by attendance status works
- [ ] Filter by visa requirement works
- [ ] Search functionality works
- [ ] "Export" button downloads file
- [ ] "View" button opens details

**Expected Data Columns:**
- ID
- Name
- Email
- Country
- Organization
- Visa (Yes/No badge)
- Attendance Status (badge)
- Registrations count
- Actions (View button)

#### **Admin Users Module (Super Admin Only):**
- [ ] Login as `admin` (Super Admin)
- [ ] Click "Admin Users" in sidebar
- [ ] DataTables loads successfully
- [ ] Shows all admin users
- [ ] Click "Add Admin" button
- [ ] Modal opens with form
- [ ] Role dropdown populated
- [ ] Fill all fields
- [ ] Click "Save" button
- [ ] New admin created
- [ ] Table refreshes
- [ ] Click "Activate/Deactivate" button
- [ ] Status toggles successfully
- [ ] Click "Delete" button
- [ ] Confirmation shown
- [ ] Admin deleted (if not self)
- [ ] Can't delete own account
- [ ] Can't deactivate own account

**Expected Data Columns:**
- ID
- Username
- Full Name
- Email
- Role (badge)
- Status (Active/Inactive badge)
- Last Login
- Actions (Edit/Toggle/Delete buttons)

#### **Reports Module:**
- [ ] Click "Reports" in sidebar
- [ ] Placeholder page loads
- [ ] Shows Phase 5 message

#### **Settings Module:**
- [ ] Click "Settings" in sidebar
- [ ] Placeholder page loads
- [ ] Shows Phase 6 message

---

## **🔐 Role-Based Access Testing**

### **Test as Super Admin (admin):**
Login: `admin` / `Admin@2025`

**Should See:**
- [ ] Dashboard
- [ ] Registrations
- [ ] Payments
- [ ] Participants
- [ ] Reports
- [ ] Admin Users
- [ ] Settings

**Should Be Able To:**
- [ ] View all data
- [ ] Update payment status
- [ ] Update attendance
- [ ] Manage admin users
- [ ] Export all data
- [ ] Access all settings

---

### **Test as Finance Team (finance):**
Login: `finance` / `Finance@2025`

**Should See:**
- [ ] Dashboard (with payment stats)
- [ ] Registrations
- [ ] Payments
- [ ] Reports

**Should NOT See:**
- [ ] Participants menu
- [ ] Admin Users menu
- [ ] Settings menu

**Should Be Able To:**
- [ ] View registrations
- [ ] View payments
- [ ] Update payment status
- [ ] Export payments
- [ ] Generate finance reports

**Should NOT Be Able To:**
- [ ] Manage admin users
- [ ] Update attendance
- [ ] Access settings

---

### **Test as Visa Team (visa):**
Login: `visa` / `Visa@2025`

**Should See:**
- [ ] Dashboard (with visa stats)
- [ ] Registrations
- [ ] Participants
- [ ] Reports

**Should NOT See:**
- [ ] Payments menu
- [ ] Admin Users menu
- [ ] Settings menu

**Should Be Able To:**
- [ ] View registrations
- [ ] View participants
- [ ] Filter by visa requirement
- [ ] Export participant data
- [ ] Generate visa reports

**Should NOT Be Able To:**
- [ ] View payments
- [ ] Update payment status
- [ ] Manage admin users

---

### **Test as Ticketing Team (ticketing):**
Login: `ticketing` / `Ticketing@2025`

**Should See:**
- [ ] Dashboard (with attendance stats)
- [ ] Registrations
- [ ] Participants
- [ ] Reports

**Should NOT See:**
- [ ] Payments menu
- [ ] Admin Users menu
- [ ] Settings menu

**Should Be Able To:**
- [ ] View registrations
- [ ] View participants
- [ ] Update attendance status
- [ ] Filter by attendance
- [ ] Export participant data
- [ ] Generate attendance reports

**Should NOT Be Able To:**
- [ ] View payments
- [ ] Update payment status
- [ ] Manage admin users

---

## **📊 Data Validation Testing**

### **Registration Filters:**
1. Select "Individual" type → Should show only individual registrations
2. Select "Pending" status → Should show only pending
3. Select "Completed" payment → Should show only completed payments
4. Set date range → Should show only registrations in that range
5. Type in search box → Should filter as you type
6. Apply multiple filters → Should show intersection of filters
7. Reset filters → Should show all data again

### **Payment Filters:**
1. Select "Pending" status → Should show only pending payments
2. Set date range → Should filter by date
3. Search by transaction ID → Should find specific payment
4. Export with filters → Should export only filtered data

### **User Filters:**
1. Select "Present" attendance → Should show only present users
2. Select "Yes" for visa → Should show only users requiring visa
3. Search by name → Should filter results
4. Export with filters → Should export only filtered data

---

## **💾 Export Testing**

### **Test Export Functionality:**
1. Go to Registrations
2. Apply some filters
3. Click "Export" button
4. **Expected:** Excel file downloads
5. **Filename format:** `registrations_YYYY-MM-DD_HHMMSS.xlsx`
6. Open file in Excel
7. **Expected columns:** ID, First Name, Last Name, Email, Type, Package, Amount, Currency, Status, Payment Status, Participants, Country, Organization, Created At

**Repeat for:**
- [ ] Payments export
- [ ] Users export
- [ ] Verify all exports respect filters
- [ ] Verify all data is correct
- [ ] Verify headers are included

---

## **🔄 Update Operations Testing**

### **Payment Status Update:**
1. Login as Finance Team
2. Go to Payments
3. Find a "pending" payment
4. Click "Update Status" button
5. Modal opens
6. Change status to "Completed"
7. Click "Save Changes"
8. **Expected:** Modal closes, table refreshes, status updated
9. **Expected:** Success alert shown
10. Verify status changed in table
11. Try changing to "Failed" status
12. Verify update works

### **Attendance Update (Future):**
1. Login as Ticketing Team
2. Go to Participants
3. Click on a user
4. Update attendance status
5. Verify update works

---

## **🎨 UI/UX Testing**

### **Responsive Design:**
- [ ] Open on desktop (full view)
- [ ] Open on tablet (sidebar collapses)
- [ ] Open on mobile (all features accessible)
- [ ] Test menu on mobile
- [ ] Test tables on mobile (horizontal scroll)
- [ ] Test modals on mobile

### **Browser Compatibility:**
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)

### **Visual Elements:**
- [ ] Green sidebar (success theme)
- [ ] Statistics cards render correctly
- [ ] Badges show correct colors
- [ ] Charts display properly
- [ ] Tables are styled correctly
- [ ] Modals are centered
- [ ] Buttons have hover effects
- [ ] Alerts auto-dismiss after 5 seconds

---

## **🐛 Error Handling Testing**

### **Test Error Scenarios:**
1. **Invalid Login:**
   - Try wrong password → Should show error
   - Try non-existent user → Should show error

2. **Unauthorized Access:**
   - Login as Finance
   - Try to access `/admin/admins` directly
   - **Expected:** Redirected or 403 error

3. **Invalid Data:**
   - Try creating admin with existing username
   - **Expected:** Validation error shown
   - Try creating admin with weak password
   - **Expected:** Validation error

4. **Network Errors:**
   - Disable network
   - Try to load DataTables
   - **Expected:** Loading spinner or error message

5. **Missing Data:**
   - Try to export with no data
   - **Expected:** Empty Excel file or message

---

## **⚡ Performance Testing**

### **Load Times:**
- [ ] Dashboard loads in < 2 seconds
- [ ] DataTables loads in < 3 seconds
- [ ] Filters apply in < 1 second
- [ ] Export completes in < 5 seconds (100 records)
- [ ] Charts render in < 1 second

### **Large Dataset:**
- [ ] Test with 100+ records
- [ ] Test pagination
- [ ] Test search with many results
- [ ] Test export with many records

---

## **✅ Checklist Summary**

### **Core Features:**
- [ ] ✅ Authentication works
- [ ] ✅ Dashboard displays data
- [ ] ✅ Registrations module functional
- [ ] ✅ Payments module functional
- [ ] ✅ Users module functional
- [ ] ✅ Admin users module functional
- [ ] ✅ Export works for all modules
- [ ] ✅ Filters work correctly
- [ ] ✅ Search functionality works
- [ ] ✅ Role-based access enforced
- [ ] ✅ Permissions working
- [ ] ✅ UI is responsive
- [ ] ✅ No console errors

---

## **📝 Bugs to Report**

If you find any issues, please note:
1. **What you were doing**
2. **Expected behavior**
3. **Actual behavior**
4. **Browser/Device**
5. **User role**
6. **Error messages (if any)**

---

## **🎯 Success Criteria**

The system passes testing if:
1. ✅ All users can login
2. ✅ Dashboard loads for all roles
3. ✅ All DataTables load successfully
4. ✅ All filters work
5. ✅ All exports work
6. ✅ Role-based access enforced
7. ✅ No JavaScript errors in console
8. ✅ No PHP errors
9. ✅ Updates save successfully
10. ✅ UI is responsive and looks good

---

## **📞 Support**

If you encounter any issues:
1. Check browser console for errors
2. Check Laravel logs: `cphiaadmin/storage/logs/laravel.log`
3. Verify database connection
4. Clear Laravel cache: `php artisan cache:clear`
5. Clear browser cache

---

**Happy Testing!** 🎉

**The system is fully functional and ready for your test!** 🚀

