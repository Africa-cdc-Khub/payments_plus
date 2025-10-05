# CPHIA 2025 Laravel Admin System - Project Plan

## **Existing Database Structure**
- ✅ admins (super_admin, admin roles)
- ✅ users (conference attendees with visa/attendance tracking)
- ✅ registrations (with payment status)
- ✅ payments
- ✅ packages
- ✅ registration_participants (group registrations)

## **Admin Roles to Implement**
1. **Super Admin** - Full system access + user management
2. **Admin** - General admin access (existing)
3. **Finance Team** - Payment management, financial reports
4. **Visa Team** - Visa application management
5. **Ticketing Team** - Attendance tracking, badge management

## **Phase 1: Setup & Configuration** ✅
- [x] Install Laravel
- [x] Install Sanctum for authentication
- [x] Install Spatie Permission for roles
- [x] Install AdminLTE for UI
- [x] Install Laravel Excel for exports
- [x] Configure database connection
- [x] Analyze existing database structure

## **Phase 2: Models & Migrations** (Next)
- [ ] Create Admin model with Sanctum
- [ ] Create User model
- [ ] Create Registration model
- [ ] Create Payment model
- [ ] Create Package model
- [ ] Run Spatie permission migrations
- [ ] Seed roles and permissions
- [ ] Create role-permission matrix

## **Phase 3: Authentication & Authorization**
- [ ] Setup Sanctum authentication routes
- [ ] Create login/logout controllers
- [ ] Create admin dashboard layout (AdminLTE)
- [ ] Implement role-based middleware
- [ ] Create permission-based access control

## **Phase 4: Dashboard & Analytics**
- [ ] Main dashboard with key metrics
- [ ] Registration statistics (total, by type, by status)
- [ ] Payment statistics (collected, pending, failed)
- [ ] Nationality breakdown (African vs Non-African)
- [ ] Interactive charts (Chart.js)
- [ ] Date range filters
- [ ] Real-time updates

## **Phase 5: Registration Management**
- [ ] List all registrations with filters
- [ ] View registration details
- [ ] Search functionality
- [ ] Status management
- [ ] Participant management
- [ ] Export registrations to CSV/Excel
- [ ] Bulk actions

## **Phase 6: Payment Management** (Finance Team)
- [ ] Payment list with filters
- [ ] Update payment status
- [ ] Payment verification workflow
- [ ] Payment reports
- [ ] Revenue analytics
- [ ] Export payment records
- [ ] Payment reconciliation tools

## **Phase 7: Visa Management** (Visa Team)
- [ ] List users requiring visa
- [ ] Visa application status tracking
- [ ] Document management
- [ ] Visa approval workflow
- [ ] Export visa applicants
- [ ] Email notifications

## **Phase 8: Ticketing/Attendance** (Ticketing Team)
- [ ] Attendance tracking interface
- [ ] Check-in/check-out functionality
- [ ] Badge printing integration
- [ ] Attendance reports
- [ ] Export attendance records
- [ ] Real-time attendance dashboard

## **Phase 9: Reports & Exports**
- [ ] Comprehensive report builder
- [ ] Pre-defined report templates
- [ ] Custom date range reports
- [ ] Export to CSV
- [ ] Export to Excel with formatting
- [ ] Export to PDF (optional)
- [ ] Scheduled reports (email delivery)

## **Phase 10: Admin User Management**
- [ ] List all admin users
- [ ] Create/Edit/Delete admins
- [ ] Assign roles and permissions
- [ ] Activity logs
- [ ] Login history
- [ ] Security settings

## **Phase 11: Additional Features**
- [ ] Email notifications
- [ ] Activity logging
- [ ] Audit trail
- [ ] System settings
- [ ] Backup functionality
- [ ] API endpoints for mobile app

## **Permissions Matrix**

### Super Admin
- Full system access
- User management
- System configuration
- All reports and exports

### Admin
- Dashboard access
- View all registrations
- View all payments
- Basic reports

### Finance Team
- Dashboard (financial focus)
- Payment management
- Payment verification
- Financial reports
- Revenue analytics
- Payment exports

### Visa Team
- Dashboard (visa focus)
- Visa applications list
- Update visa status
- Document management
- Visa reports
- Visa applicant exports

### Ticketing Team
- Dashboard (attendance focus)
- Attendance tracking
- Check-in management
- Badge management
- Attendance reports
- Attendance exports

## **Technology Stack**
- Laravel 12.x
- Laravel Sanctum (Authentication)
- Spatie Laravel Permission (Roles & Permissions)
- AdminLTE 3.x (UI)
- Laravel Excel/Maatwebsite (Exports)
- Chart.js (Dashboard charts)
- MySQL (Database)

## **API Endpoints (Sanctum)**
```
POST /api/login
POST /api/logout
GET /api/user
GET /api/dashboard/stats
GET /api/registrations
GET /api/payments
GET /api/users
POST /api/payments/{id}/verify
POST /api/attendance/{id}/checkin
```

## **Next Steps**
1. Create models for existing tables
2. Run permission migrations
3. Seed roles and permissions
4. Create authentication controllers
5. Build AdminLTE dashboard layout
6. Implement role-based access control

