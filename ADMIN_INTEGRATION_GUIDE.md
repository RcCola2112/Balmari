# Balmari Admin System - Integration Guide

## How Admin System Connects to Public Website

The Balmari Admin Panel (`/admin/`) is a separate control center that manages the content displayed on the public website (`/`).

## Data Flow

```
Admin Panel              Public Website
─────────────────────────────────────────
Login Page      ────────> (secure session)
Dashboard       ────────> (views DB data)
Projects ───────> projects_in_progress table ───────> progress.php
                  completed_projects table ───────> completed.php
Services ───────> services table ───────> services.php
Media Library ──> media_library table ───────> asset uploads
Testimonials ───> testimonials table ───────> (future display)
Inquiries ──────> contact_messages table ───────> (review only)
Settings ───────> settings table ───────> (future integration)
```

## Integration Points with Existing Public Pages

### Projects Display
**Admin Module**: `admin/projects.php`  
**Public Pages**: `progress.php`, `completed.php`, `project_details.php`  
**Database**: `projects_in_progress`, `completed_projects`, `*_project_images`  
**How It Works**:
1. Admin adds project via `/admin/projects.php`
2. Images stored in `assets/project_images/`
3. Public site queries projects table
4. Displays via progress.php or completed.php

### Services Display  
**Admin Module**: `admin/services.php`  
**Public Page**: `services.php`  
**Database**: `services` table (NEW)  
**How It Works**:
1. Admin creates service via `/admin/services.php`
2. Service added to `services` table
3. Public `services.php` queries active services
4. Displays in order set by admin

### Contact Inquiries
**Admin Module**: `admin/inquiries.php`  
**Public Page**: `contact.php`  
**Database**: `contact_messages` table  
**How It Works**:
1. Visitor fills form on public `contact.php`
2. Form submitted to contact handler (likely `send_contact.php`)
3. Data stored in `contact_messages` table
4. Admin views/manages via `/admin/inquiries.php`

### Media Management
**Admin Module**: `admin/media.php`  
**Public Pages**: All pages using images  
**Database**: `media_library` table (NEW)  
**How It Works**:
1. Admin uploads media via `/admin/media.php`
2. Files stored in `assets/uploads/`
3. Metadata tracked in `media_library` table
4. Public pages reference uploaded files

### Activity Tracking
**Admin Module**: `admin/activity-logs.php`  
**Database**: `activity_logs` table (NEW)  
**How It Works**:
1. Every admin action logged automatically
2. Includes: user, action, entity, timestamp
3. Accessible via activity-logs page
4. Cannot be edited (audit trail)

## Required Public Website Integration

These are files on the public site that interface with admin data:

### 1. Project Display Pages
- `progress.php` - Queries `projects_in_progress` table
- `completed.php` - Queries `completed_projects` table
- `project_details.php` - Shows single project from either table
- `progress_details.php` - Shows single ongoing project

**Ensure these pages query correct tables** ✓

### 2. Settings Integration  
Currently: Hard-coded values in templates  
Future: Query `settings` table for:
- Company name, email, phone, address
- Social media links
- Hero text
- SEO meta tags

**Example future update**:
```php
// Instead of hard-coded:
$company_email = "info@balmari.com";

// Query settings table:
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
$stmt->execute(['company_email']);
$company_email = $stmt->fetch()['setting_value'];
```

### 3. Services Page Integration
Currently: May be hard-coded or from different source  
Future: Query `services` table

**Example**:
```php
$stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY display_order");
$services = $stmt->fetchAll();

foreach ($services as $service) {
    echo $service['title'];
    echo $service['description'];
}
```

## Public Site → Admin Site Links (Optional)

Consider adding links on public site to direct managers to admin panel:
```php
<!-- In footer or header -->
<?php if (isset($_SESSION['admin_user_id'])): ?>
    <a href="/admin/dashboard.php">Admin Dashboard</a>
<?php endif; ?>
```

Or add a simple admin link in footer (not logged in):
```html
<p><a href="/admin/login.php">Admin Login</a></p>
```

## Database Connection

Both admin and public site use same database:

**Connection Details**:
```
Host: localhost (or your host)
Database: balmari
User: (your DB user)
Password: (your DB password)
Port: 3306
```

**Connection File Locations**:
- Public site: `includes/db_config.php` (assumed)
- Admin: `admin/config.php` (created)

**Note**: Update both files to use same credentials since they access same database.

## Image Storage Structure

```
assets/
├── uploads/              (NEW - admin uploads here)
│   ├── media_12345.jpg
│   ├── media_12346.png
│   └── ...
├── projects/            (existing - project images)
│   ├── project_1/
│   └── project_2/
├── images/
│   ├── carousel/
│   └── ...
└── css, js, etc.
```

**Public site should reference**:
- Admin uploads: `/assets/uploads/filename.ext`
- Project images: `/assets/projects/project_id/image.ext`

## Security Considerations

### Access Control
- Admin panel requires login
- Public site does not
- Share same database user or create separate read-only user for public site

### Recommended Setup
```sql
-- Admin user (full access)
CREATE USER 'admin'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON balmari.* TO 'admin'@'localhost';

-- Public site user (read-only)
CREATE USER 'public'@'localhost' IDENTIFIED BY 'public_password';
GRANT SELECT ON balmari.* TO 'public'@'localhost';
```

## Query Examples for Public Site Integration

### Get All Active Services
```php
$pdo = new PDO('mysql:host=localhost;dbname=balmari', 'public', 'public_password');
$stmt = $pdo->query("
    SELECT * FROM services 
    WHERE is_active = 1 
    ORDER BY display_order ASC
");
$services = $stmt->fetchAll();
```

### Get Company Settings
```php
function get_setting($key) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    return $stmt->fetch()['setting_value'];
}

$company_name = get_setting('company_name');
$company_phone = get_setting('company_phone');
```

### Get Featured Testimonials
```php
$stmt = $pdo->query("
    SELECT * FROM testimonials 
    WHERE is_featured = 1 AND is_active = 1 
    ORDER BY display_order ASC
");
$testimonials = $stmt->fetchAll();
```

## Migration Path

### Phase 1: Current State ✓
- Admin system created
- Database ready
- Can manage projects, services, media

### Phase 2: Public Site Integration (Next)
- Update public pages to query tables
- Remove hard-coded values
- Test data display

### Phase 3: Settings Integration (Future)
- Implement dynamic settings
- Update company info from admin
- Update SEO tags from admin

### Phase 4: Advanced Features (Future)
- Blog/news management
- Homepage carousel management
- Multi-language support
- Email notification system

## Testing the Integration

### Test Checklist
- [ ] Admin login works
- [ ] Add test project via admin
- [ ] Project appears on public site
- [ ] Add test service via admin
- [ ] Service appears on public site
- [ ] Upload test image via admin
- [ ] Image accessible from public site
- [ ] Inquiry form submission stored in DB
- [ ] Inquiry visible in admin inquiries page

## Troubleshooting Integration

**Problem**: Data not appearing on public site  
**Solution**: 
- Check if public pages query correct tables
- Verify database connection settings
- Check if data is marked active/enabled

**Problem**: Images showing as broken links  
**Solution**:
- Verify upload directory has correct permissions
- Check file path in database matches actual location
- Ensure relative vs absolute paths are correct

**Problem**: Settings not updating on public site  
**Solution**:
- If hard-coded values, must update PHP files directly
- If using settings table, ensure queries are correct

## Summary

The admin system is **independent but complementary** to the public site:
- Admin creates/manages content
- Public site displays content
- Both use same database
- Clear separation of concerns

**Current Status**: Admin system ready, awaiting public site integration updates.

---

For setup steps, see: **ADMIN_QUICKSTART.md**  
For full documentation, see: **ADMIN_SYSTEM_GUIDE.md**
