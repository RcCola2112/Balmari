# Balmari Admin System - Quick Start Guide

## What's New

A complete, production-ready admin control panel has been created with the following structure:

```
admin/
├── config.php                 # Database config & core setup
├── login.php                  # Secure admin login
├── logout.php                 # Session termination
├── dashboard.php              # Analytics & quick stats
├── projects.php               # Manage ongoing & completed projects
├── services.php               # Add/edit/manage services
├── media.php                  # Central media library with upload
├── inquiries.php              # View & manage contact form submissions
├── testimonials.php           # Client testimonials management
├── users.php                  # User & role management (Super Admin)
├── activity-logs.php          # Audit trail of all admin actions
├── settings.php               # System-wide configuration
└── includes/
    ├── config.php             # Database initialization
    ├── auth.php               # Auth & permission functions
    ├── header.php             # Admin layout & navigation
    └── footer.php             # Layout footer & scripts
```

## Immediate Setup Steps

### 1. Database Migration (REQUIRED)
```
1. Go to https://your-hosting.com/phpmyadmin
2. Select the "balmari" database
3. Click the "SQL" tab
4. Open ADMIN_SCHEMA_UPDATES.sql
5. Copy all content
6. Paste into the SQL editor
7. Click "Execute"
```

This creates:
- services table
- media_library table
- settings table (with defaults)
- activity_logs table
- testimonials table
- Updates to employees table (adds roles)
- Updates to contact_messages table

### 2. Update Config (If Needed)
Edit `admin/config.php` and verify:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'balmari');
```

### 3. Create Uploads Directory
Ensure this directory exists and is writable:
```
assets/uploads/
```

If it doesn't exist, create it through FTP or file manager.

### 4. Test Login
Visit: `https://your-domain.com/admin/login.php`

Use credentials from the database (check EMPLOYEE_LOGIN_CREDENTIALS.md or the email you set up).

Default test accounts (change password after first login):
- admin@balmari.com / admin123
- Admin01@gmail.com / Admin_01

## Features Overview

### Dashboard
- Real-time project counts
- New inquiry alerts
- Active services count
- Quick action buttons

### Projects Management
- List all projects (ongoing & completed)
- Add new projects with multiple images
- Cover image always appears first
- Edit project details
- Delete projects with cascading image deletion

### Services
- Create service offerings
- Set display order
- Active/inactive toggle
- Descriptions and icons

### Media Library
- Upload images (JPG, PNG, GIF, WEBP)
- View all uploaded files
- File info: size, type, date
- Delete unused media
- Central repository for all assets

### Contact Inquiries
- View form submissions
- Mark as read/unread
- Filter: All/Unread/Spam
- Add internal notes
- Delete spam

### Testimonials
- Add client testimonials
- 5-star rating system
- Feature testimonials
- Set display order

### User Management (Super Admin Only)
- Create team accounts
- Assign roles: Super Admin, Content Manager, Staff
- Activate/deactivate users
- View last login times

### Activity Logs (Super Admin Only)
- Complete audit trail
- Track all admin actions
- User, action, timestamp
- Search and filter

### System Settings (Super Admin Only)
- Company info (name, email, phone, address)
- Social media links
- Hero section content
- SEO meta tags
- Upload limits and preferences

## Color Scheme (Updated)
All admin pages use the new Balmari palette:
- Dark backgrounds: #37353E
- Container backgrounds: #44444E
- Accent color: #715A5A
- Text color: #D3DAD9

## User Roles & Permissions

| Feature | Super Admin | Content Manager | Staff |
|---------|------------|-----------------|-------|
| Dashboard | ✓ | ✓ | ✓ |
| Projects | ✓ | ✓ | Read |
| Services | ✓ | ✓ | Read |
| Media Upload | ✓ | ✓ | ✓ |
| Inquiries | ✓ | ✓ | ✓ |
| Testimonials | ✓ | ✓ | Read |
| Users | ✓ | ✗ | ✗ |
| Activity Logs | ✓ | ✗ | ✗ |
| Settings | ✓ | ✗ | ✗ |

## Security Features

✅ **BCrypt Password Hashing** - All passwords encrypted  
✅ **Session Timeout** - Auto logout after 30 minutes  
✅ **Activity Logging** - All changes tracked  
✅ **Role-Based Access** - Different permissions per role  
✅ **File Upload Validation** - Type & size checking  
✅ **CSRF Protection Ready** - Structure supports tokens  
✅ **SQL Injection Prevention** - Prepared statements  

## Common Tasks

### Add a New Project
1. Go to Dashboard or Projects
2. Click "New Ongoing Project" or "New Completed Project"
3. Enter title, location, type, description
4. Upload cover image first
5. Upload additional project images
6. Save

### Create a Service
1. Go to Services
2. Fill in title, description
3. Set display order
4. Check "Active"
5. Click "Create Service"

### Manage Inquiries
1. Go to Inquiries
2. Click on any inquiry to view full details
3. Mark as read when reviewed
4. Delete if spam
5. Add notes for follow-up

### Upload Media
1. Go to Media Library
2. Click upload area
3. Select image (max 50MB)
4. View all uploads below
5. Delete if no longer needed

## Accessing Admin Panel

From anywhere on the site:
```
https://your-domain.com/admin/login.php
```

Menu navigation (once logged in):
- Dashboard (home)
- Projects
- Services
- Media Library
- Inquiries
- Testimonials
- Users (super admin only)
- Activity Logs (super admin only)
- Settings (super admin only)
- Your Profile + Logout

## Troubleshooting

**Q: "Database connection failed"**  
A: Check admin/config.php - verify host, user, password, database name

**Q: "Access denied" error**  
A: Ensure you're logged in and your account status is "active"

**Q: File uploads not working**  
A: Check uploads directory exists and is writable (chmod 755)

**Q: Can't see admin menu items**  
A: Run ADMIN_SCHEMA_UPDATES.sql - tables might not exist yet

**Q: Forgot admin password**  
A: You'll need database access to reset. Contact hosting support.

## Next Steps

1. ✅ Run the database migration (ADMIN_SCHEMA_UPDATES.sql)
2. ✅ Test login at /admin/login.php
3. ✅ Change default admin password
4. ✅ Create team member accounts
5. ✅ Add your company information in Settings
6. ✅ Start adding or updating projects
7. ✅ Manage inquiries and testimonials

## Support

For complete documentation, see: **ADMIN_SYSTEM_GUIDE.md**

---
Created: February 21, 2026  
Admin System Version: 1.0  
Status: Production Ready
