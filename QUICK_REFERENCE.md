# Balmari Website - Quick Reference Card

## 🎯 Essential Files at a Glance

### Main Pages
- `index.php` - Home page
- `about.php` - About company
- `services.php` - Services offered
- `projects.php` - Project portfolio
- `contact.php` - Contact page & form

### Reusable Components
- `includes/header.php` - Navigation & header
- `includes/footer.php` - Footer with links
- `includes/db.php` - Database connection
- `includes/config.php` - Site configuration

### Backend
- `process/contact_process.php` - Form handler
- `balmari_database.sql` - Database setup

---

## ⚡ Quick Setup (5 minutes)

```bash
# 1. Create database
mysql -u root -p < balmari_database.sql

# 2. Edit database credentials
# Open: includes/db.php
# Update: DB_HOST, DB_USER, DB_PASS, DB_NAME

# 3. Start server
php -S localhost:8000

# 4. Open in browser
# http://localhost:8000
```

---

## 🗄️ Database Connection

**File**: `includes/db.php`

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'balmari');
```

---

## 📨 Contact Form Flow

```
contact.php (Form) 
    ↓
process/contact_process.php (Handler)
    ↓
Database: contact_messages
    ↓
Redirect to contact.php?success=1
```

---

## 🎨 Site Colors

- Primary: `#0f172a` (Navy Blue)
- Accent: `#f97316` (Orange)
- Background: `#ffffff` (White)

---

## 📱 Tailwind CSS Classes Used

Common classes throughout the site:
- `max-w-7xl` - Content container
- `grid` - Layout grids
- `hover:` - Hover effects
- `transition` - Smooth animations
- `rounded-lg` - Rounded corners
- `shadow-lg` - Drop shadows
- `text-orange-500` - Orange text

---

## ✅ Form Validation

**Required Fields:**
- Full Name (text)
- Email (must be valid format)
- Phone (numeric)
- Message (min 10 chars)

**Security:**
- Prepared statements (SQL injection prevention)
- Input sanitization
- Email validation
- Trim whitespace

---

## 🔍 View Contact Submissions

```sql
-- All submissions
SELECT * FROM contact_messages ORDER BY created_at DESC;

-- By email
SELECT * FROM contact_messages WHERE email = 'user@email.com';

-- Recent submissions
SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 10;

-- Count total
SELECT COUNT(*) as total FROM contact_messages;
```

---

## 🚀 Deployment Checklist

- [ ] Database credentials updated
- [ ] SSL/HTTPS configured
- [ ] File permissions set (755 for dirs, 644 for files)
- [ ] Error logging configured
- [ ] Backups scheduled
- [ ] Email notifications set up
- [ ] reCAPTCHA added to form
- [ ] All pages tested
- [ ] Mobile responsiveness verified
- [ ] Forms tested end-to-end

---

## 📁 Important Paths

```
Site Root:     c:\Users\denve\Documents\Balmari\
Database:      includes/db.php
Config:        includes/config.php
Contact Form:  process/contact_process.php
Images:        assets/images/
Database SQL:  balmari_database.sql
```

---

## 🌐 URL Structure

```
Home:        localhost:8000/index.php (or just /)
About:       localhost:8000/about.php
Services:    localhost:8000/services.php
Projects:    localhost:8000/projects.php
Contact:     localhost:8000/contact.php
```

---

## 💡 Customization Quick Links

Edit these files to customize:

| What | File |
|------|------|
| Company Info | includes/config.php |
| Navigation | includes/header.php |
| Footer | includes/footer.php |
| Colors | includes/header.php (CSS) |
| Home Content | index.php |
| Services | services.php |
| Projects | projects.php |
| Contact Info | contact.php |

---

## 🔐 Security Notes

✅ Already implemented:
- Prepared statements
- Input validation
- Email format checking
- SQL injection prevention

⚠️ To add:
- HTTPS/SSL certificate
- reCAPTCHA
- Rate limiting
- Email notifications
- Admin login

---

## 🆘 Common Commands

```bash
# Start PHP server
php -S localhost:8000

# Clear browser cache
# Ctrl+Shift+Delete (Windows/Linux)
# Cmd+Shift+Delete (Mac)

# Test database connection
mysql -u root -p -e "SELECT * FROM balmari.contact_messages;"

# MySQL backup
mysqldump -u root -p balmari > backup.sql

# Restore backup
mysql -u root -p balmari < backup.sql
```

---

## 📞 Contact Details

- **Company**: Balmari: Design and Construction
- **Tagline**: From Concept to Completion
- **Location**: Batangas, Philippines
- **Area**: Batangas and Nearby Areas

---

## 📚 Documentation

- `README.md` - Full documentation
- `SETUP_GUIDE.md` - Setup instructions
- `INSTALLATION_COMPLETE.md` - Installation summary
- `balmari_database.sql` - Database schema

---

## ✨ Features Summary

✅ Responsive design
✅ Contact form with database
✅ Reusable components
✅ Professional layout
✅ Mobile menu
✅ Service cards with animations
✅ Project showcase
✅ Success/error messages
✅ SQL injection prevention
✅ Input validation

---

**Ready to go!** 🚀

Start with SETUP_GUIDE.md for complete instructions.
