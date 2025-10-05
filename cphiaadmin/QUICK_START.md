# 🚀 Quick Start Guide - CPHIA 2025 Laravel Admin

## **Access the Admin Panel**

### **Login URL:**
```
http://localhost/payments_plus/cphiaadmin/public/admin/login
```

---

## **Login Credentials**

| Username | Password | Role | Access Level |
|----------|----------|------|--------------|
| `admin` | `Admin@2025` | Super Admin | ⭐⭐⭐⭐⭐ Full Access |
| `finance` | `Finance@2025` | Finance Team | 💰 Payments & Finance |
| `visa` | `Visa@2025` | Visa Team | 🛂 Visa Applications |
| `ticketing` | `Ticketing@2025` | Ticketing Team | 🎫 Attendance Tracking |

---

## **What Each Role Can Do**

### **🔐 Super Admin (admin):**
- ✅ Full system access
- ✅ Manage all admin users
- ✅ View all registrations
- ✅ Manage payments
- ✅ Access all reports
- ✅ System settings
- ✅ Assign roles & permissions

### **💰 Finance Team (finance):**
- ✅ View dashboard
- ✅ View registrations (related to payments)
- ✅ Manage payments
- ✅ Update payment status
- ✅ Generate finance reports
- ✅ Export payment data

### **🛂 Visa Team (visa):**
- ✅ View dashboard
- ✅ View registrations (for visa info)
- ✅ View user details
- ✅ Track visa applications
- ✅ Generate visa reports
- ✅ Export visa data

### **🎫 Ticketing Team (ticketing):**
- ✅ View dashboard
- ✅ View registrations (for attendance)
- ✅ Track attendance
- ✅ Generate attendance reports
- ✅ Export attendance data

---

## **Dashboard Features**

### **Statistics You'll See:**

#### **All Roles:**
- 📊 Total registrations count
- 👤 Individual registrations
- 📅 Side event registrations
- 🏢 Exhibition registrations
- 🌍 Total participants
- 🌍 African nationals count
- 🌎 Non-African nationals count
- 📈 Registration trend chart (last 7 days)

#### **Finance Team, Admin, Super Admin:**
- 💰 Total revenue
- ⏱️ Pending payments
- ✅ Completed payments
- ❌ Failed payments
- 🥧 Payment status distribution chart

#### **Visa Team, Admin, Super Admin:**
- 🛂 Total visa applications
- ⏳ Pending visa applications

#### **Ticketing Team, Admin, Super Admin:**
- ✅ Present attendees
- ❌ Absent attendees
- ⏳ Pending attendance

---

## **Navigation Menu**

### **For Everyone:**
- 🏠 **Dashboard** - Overview & statistics
- 📝 **Registrations** - View all registrations
- 📊 **Reports** - Generate reports

### **Finance Team Additional:**
- 💳 **Payments** - Manage payment statuses

### **Super Admin Additional:**
- 👥 **Admin Users** - Manage admin accounts
- ⚙️ **Settings** - System configuration

---

## **Quick Actions**

### **To Login:**
1. Go to: `http://localhost/payments_plus/cphiaadmin/public/admin/login`
2. Enter username and password
3. Click "Sign In"
4. You'll be redirected to the dashboard

### **To Logout:**
1. Click your name in the top-right corner
2. Click "Logout"

### **To View Statistics:**
- The dashboard shows all statistics automatically
- Charts are interactive - hover for details

---

## **Browser Support**

✅ Chrome (Recommended)  
✅ Firefox  
✅ Safari  
✅ Edge  

---

## **Mobile Access**

The admin panel is fully responsive and works on:
- 📱 Smartphones
- 📱 Tablets
- 💻 Laptops
- 🖥️ Desktops

---

## **Troubleshooting**

### **Can't Login?**
1. Make sure XAMPP is running
2. Check MySQL is running
3. Verify URL is correct
4. Check username/password (case-sensitive)

### **Dashboard Not Loading?**
1. Clear browser cache
2. Check database connection
3. Run: `cd /Applications/XAMPP/xamppfiles/htdocs/payments_plus/cphiaadmin && php artisan cache:clear`

### **404 Error?**
1. Make sure Apache is running
2. Check the URL is correct
3. Ensure `.htaccess` is present in `public/` folder

---

## **Important Notes**

⚠️ **Security:**
- Change default passwords after first login
- Don't share credentials
- Always logout when done

📱 **Browser:**
- Use latest browser version
- Enable JavaScript
- Enable cookies

🔐 **Passwords:**
- Minimum 8 characters
- Use strong passwords
- Change regularly

---

## **Need Help?**

Contact the system administrator for:
- Password resets
- Role changes
- Technical issues
- Feature requests

---

## **System Status**

✅ Authentication - Working  
✅ Dashboard - Working  
✅ Statistics - Working  
✅ Charts - Working  
✅ Navigation - Working  
✅ Role-based Access - Working  

---

**Everything is ready to use!** 🎉

**Start by logging in as `admin` to see full system access!** 🚀

