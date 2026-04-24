# Balmari Admin Panel - Complete Documentation

## Overview
The Balmari Admin Panel is a comprehensive content management system designed for managing design and construction projects, services, media, inquiries, and team members. Built with PHP, Tailwind CSS, and a modern UI following the updated Balmari color palette.

## Color Palette
- **Background**: #37353E
- **Container BG**: #44444E
- **Accent**: #715A5A
- **Text**: #D3DAD9

## Installation

### Step 1: Database Setup
1. Open phpMyAdmin in your Hostinger control panel
2. Select your Balmari database
3. Go to the SQL tab
4. Copy and paste the entire contents of `ADMIN_SCHEMA_UPDATES.sql`
5. Click Execute

This will:
- Add new columns to the `employees` table (role, status, last_login, updated_at)
- Create `services` table
- Create `media_library` table
- Create `settings` table
- Create `activity_logs` table
- Create `testimonials` table
- Update `contact_messages` with: is_read, is_spam, responded_at, notes
- Insert default settings for the system

### Step 2: Configuration
Update `admin/config.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'balmari');
```

### Step 3: File Permissions
Ensure the `assets/uploads/` directory is writable:
```bash
chmod 755 assets/uploads/
```

## System Features

### 1. Authentication & Security
- **Login System**: Email and password-based authentication
- **Password Hashing**: BCrypt hashing for all passwords
- **Session Management**: 30-minute timeout
- **Activity Logging**: All admin actions are logged
- **Role-Based Access Control**: Super Admin, Content Manager, Staff roles

### 2. Dashboard
Located at: `admin/dashboard.php`
Shows real-time statistics:
- Total projects (ongoing + completed)
- New contact inquiries (24h)
- Active services
- Team members
- Quick action buttons
- Recent activity

### 3. Projects Management
Located at: `admin/projects.php`
Manage both ongoing and completed projects:
- View all projects
- Add new ongoing projects
- Add completed projects
- Edit project details
- Upload project images
- Delete projects
- Activity logging for all project changes

### 4. Services Management
Located at: `admin/services.php`
Feature-rich service management:
- Add new services
- Edit existing services
- Set display order
- Active/inactive status
- Upload service icons
- Delete services

### 5. Media Library
Located at: `admin/media.php`
Centralized media management:
- Upload images (JPG, PNG, GIF, WEBP)
- Max file size: 50MB
- View all uploaded files
- File information (size, type, upload date)
- Delete unused media
- Track media usage

### 6. Contact Inquiries
Located at: `admin/inquiries.php`
Manage customer inquiries:
- View all contact form submissions
- Filter: All, Unread, Spam
- Mark as read
- Add notes to inquiries
- Delete spam/invalid inquiries
- Email, phone, and message details

### 7. Testimonials
Located at: `admin/testimonials.php`
Manage client testimonials:
- Add new testimonials
- Set star rating (1-5)
- Mark as featured
- Set display order
- Manage active/inactive testimonials

### 8. User Management (Super Admin Only)
Located at: `admin/users.php`
Create and manage team:
- Add new users with specific roles
- Assign roles: Super Admin, Content Manager, Staff
- Toggle user status (active/inactive)
- View user activity history
- Edit user information

### 9. Activity Logs (Super Admin Only)
Located at: `admin/activity-logs.php`
Complete audit trail:
- View all admin actions
- Filter by user
- Filter by action type
- Filter by timestamp
- Track content changes

### 10. System Settings (Super Admin Only)
Located at: `admin/settings.php`
Configure system-wide settings:
- Company information (name, email, phone, address)
- Social media links (Facebook, Instagram, LinkedIn)
- Hero section content
- SEO meta tags
- System preferences

## User Roles

### Super Admin
- Access to all features
- User management
- Activity logs
- System settings
- Can create/edit/delete other users

### Content Manager
- Project management
- Service management
- Media management
- Contact inquiries
- Testimonials
- Cannot manage users or view system settings

### Staff
- Read-only access to most features
- Can upload media
- Cannot delete content
- Limited editing capabilities

## File Structure

```
admin/
├── config.php                 # Database config and initialization
├── login.php                  # Login page
├── logout.php                 # Logout handler
├── dashboard.php              # Main dashboard
├── projects.php               # Projects management
├── services.php               # Services management
├── media.php                  # Media library
├── inquiries.php              # Contact inquiries
├── testimonials.php           # Testimonials management
├── users.php                  # User management (super admin)
├── activity-logs.php          # Activity logs (super admin)
├── settings.php               # System settings (super admin)
├── includes/
│   ├── header.php             # Admin layout header with navigation
│   ├── footer.php             # Admin layout footer
│   └── auth.php               # Authentication functions
└── style.css                  # Admin-specific styling (inline in header.php)
```

## Database Schema

### employees (Updated)
```sql
- id (INT, PRIMARY KEY)
- full_name (VARCHAR)
- email (VARCHAR, UNIQUE)
- password (VARCHAR)
- role (VARCHAR) - super_admin, content_manager, staff
- status (ENUM) - active, inactive
- last_login (TIMESTAMP)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### services (New)
```sql
- id (INT, PRIMARY KEY)
- title (VARCHAR)
- description (TEXT)
- icon_path (VARCHAR)
- image_path (VARCHAR)
- display_order (INT)
- is_active (BOOLEAN)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### media_library (New)
```sql
- id (INT, PRIMARY KEY)
- filename (VARCHAR, UNIQUE)
- original_name (VARCHAR)
- file_path (VARCHAR)
- file_size (INT)
- file_type (VARCHAR)
- width (INT)
- height (INT)
- uploaded_by (INT, FOREIGN KEY)
- is_used (BOOLEAN)
- created_at (TIMESTAMP)
```

### settings (New)
```sql
- id (INT, PRIMARY KEY)
- setting_key (VARCHAR, UNIQUE)
- setting_value (LONGTEXT)
- setting_type (VARCHAR)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### activity_logs (New)
```sql
- id (INT, PRIMARY KEY)
- employee_id (INT, FOREIGN KEY)
- action (VARCHAR)
- entity_type (VARCHAR)
- entity_id (INT)
- description (TEXT)
- old_value (LONGTEXT)
- new_value (LONGTEXT)
- created_at (TIMESTAMP)
```

### testimonials (New)
```sql
- id (INT, PRIMARY KEY)
- client_name (VARCHAR)
- client_title (VARCHAR)
- client_image (VARCHAR)
- testimonial_text (TEXT)
- rating (INT)
- project_id (INT, FOREIGN KEY)
- is_featured (BOOLEAN)
- display_order (INT)
- is_active (BOOLEAN)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

### contact_messages (Updated)
```sql
- id (INT, PRIMARY KEY)
- full_name (VARCHAR)
- email (VARCHAR)
- phone (VARCHAR)
- message (TEXT)
- is_read (BOOLEAN) - NEW
- is_spam (BOOLEAN) - NEW
- responded_at (TIMESTAMP) - NEW
- notes (TEXT) - NEW
- created_at (TIMESTAMP)
```

## Authentication & Security

### Password Requirements
- Minimum 6 characters
- All passwords are hashed using BCrypt
- Never stored in plain text

### Session Security
- Session timeout after 30 minutes of inactivity
- Automatic logout on timeout
- Session data includes: user_id, name, email, role
- HTTPS recommended for production

### File Upload Security
- Whitelist of allowed file types: jpg, jpeg, png, gif, webp
- Maximum file size: 50MB
- Files stored outside web root when possible
- Unique filename generation to prevent overwrites

## Default Credentials

After running `ADMIN_SCHEMA_UPDATES.sql`, default accounts are available. **Change these immediately**:

Default admin accounts from `balmari_database.sql`:
- Email: `Admin01@gmail.com`
- Email: `admin@balmari.com`

To create a new Super Admin account:
```php
<?php
$password = 'your_secure_password';
$hashed = password_hash($password, PASSWORD_BCRYPT);
echo $hashed; // Use this hash in database
?>
```

## Best Practices

1. **Regular Backups**: Backup database and media files weekly
2. **Activity Monitoring**: Regularly review activity logs for suspicious behavior
3. **User Management**: Remove inactive users and revoke unnecessary permissions
4. **Media Cleanup**: Delete unused media files to save storage
5. **Settings Updates**: Keep SEO and company information current
6. **Content Review**: Regularly review and update project descriptions

## Troubleshooting

### "Access Denied" on admin pages
- Ensure you're logged in
- Check if your account status is "active"
- Verify your role has permission for that page

### File upload errors
- Check `assets/uploads/` directory permissions
- Ensure file size is under 50MB
- Verify file type is allowed (jpg, png, gif, webp)
- Check disk space availability

### Database connection errors
- Verify `admin/config.php` credentials
- Ensure MySQL server is running
- Check if database exists: `balmari`
- Run `ADMIN_SCHEMA_UPDATES.sql` if tables don't exist

### Slow dashboard loading
- Database indexes may be missing
- Check MySQL query performance
- Review activity_logs table size (may need archiving)

## Support & Maintenance

For questions or issues:
1. Check this documentation
2. Review admin/config.php for database issues
3. Check activity logs for error patterns
4. Contact your hosting provider for server issues

## Future Enhancements

Potential additions:
- Project featured/highlight system
- Email notifications for new inquiries
- Bulk import/export of projects
- Advanced analytics and reports
- CDN integration for media
- Two-factor authentication
- API endpoints for mobile apps
- Email newsletter management
- Blog/news section management

---
Last Updated: February 21, 2026
Admin System Version: 1.0
