# Balmari Website - Quick Start Guide

## Step-by-Step Setup Instructions

### Step 1: Database Setup

1. Open your MySQL client (phpMyAdmin, MySQL Workbench, or command line)
2. Execute the SQL commands from `balmari_database.sql`

Using phpMyAdmin:
- Create a new database named: `balmari`
- Import the `balmari_database.sql` file

Using MySQL Command Line:
```bash
mysql -u root -p < balmari_database.sql
```

Or copy and paste the SQL commands directly into your MySQL client.

### Step 2: Configure Database Connection

Edit `includes/db.php` with your database credentials:

```php
define('DB_HOST', 'localhost');      // Usually 'localhost'
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password (empty if none)
define('DB_NAME', 'balmari');        // Database name
```

### Step 3: Set File Permissions (Linux/Mac)

```bash
# Navigate to project directory
cd /path/to/balmari

# Make process directory executable
chmod 755 process/

# Make assets directory writable
chmod 755 assets/
chmod 755 assets/images/
```

### Step 4: Start Your Development Server

Option A: Using PHP Built-in Server (Recommended for Development)
```bash
# Navigate to project directory
cd c:\path\to\balmari

# Start server
php -S localhost:8000
```

Option B: Using XAMPP
1. Copy the `balmari` folder to `xampp/htdocs/`
2. Start Apache and MySQL from XAMPP Control Panel
3. Access at: `http://localhost/balmari`

Option C: Using WAMP
1. Copy the `balmari` folder to `wamp/www/`
2. Start WAMP
3. Access at: `http://localhost/balmari`

### Step 5: Access the Website

Open your browser and navigate to:
- **Development**: `http://localhost:8000`
- **With XAMPP**: `http://localhost/balmari`
- **With WAMP**: `http://localhost/balmari`

## Testing the Contact Form

1. Navigate to the Contact page
2. Fill in all required fields:
   - Full Name
   - Email Address
   - Phone Number
   - Message
3. Click "Send Message"
4. You should see a success message
5. Check the database to verify the submission was saved

To view submissions in the database:

```sql
-- View all contact messages
SELECT * FROM contact_messages ORDER BY created_at DESC;

-- View messages by email
SELECT * FROM contact_messages WHERE email = 'user@email.com';

-- Delete a message
DELETE FROM contact_messages WHERE id = 1;
```

## Website Navigation

- **Home**: `http://localhost:8000` or `index.php`
- **About**: `http://localhost:8000/about.php`
- **Services**: `http://localhost:8000/services.php`
- **Projects**: `http://localhost:8000/projects.php`
- **Contact**: `http://localhost:8000/contact.php`

## File Descriptions

| File | Purpose |
|------|---------|
| `index.php` | Home page with hero section |
| `about.php` | Company information and team |
| `services.php` | Detailed service descriptions |
| `projects.php` | Project showcase gallery |
| `contact.php` | Contact form and information |
| `includes/header.php` | Navigation bar and page header |
| `includes/footer.php` | Footer component |
| `includes/db.php` | Database connection configuration |
| `process/contact_process.php` | Contact form handler |
| `balmari_database.sql` | Database schema and setup |

## Customization Tips

### Change Company Information

Edit the following files to update company info:

1. **Header/Footer Navigation**
   - Edit `includes/header.php` and `includes/footer.php`
   - Update company name, phone, email, location

2. **Homepage Content**
   - Edit `index.php` to change hero text, statistics, etc.

3. **Contact Information**
   - Edit `contact.php` with actual contact details

### Update Colors

The color scheme is defined in CSS within `includes/header.php`:
- Primary Navy: `#0f172a`
- Orange Accent: `#f97316`
- White Background: `#ffffff`

Use Tailwind CSS classes to change colors throughout the site.

### Add Images

1. Place images in `assets/images/` folder
2. Reference them in HTML:
   ```html
   <img src="assets/images/your-image.jpg" alt="Description">
   ```

## Security Best Practices

✅ **Already Implemented:**
- Prepared statements for SQL queries
- Input validation and sanitization
- Email format validation
- POST method enforcement
- Error logging

**Additional Recommendations:**
1. Use HTTPS in production (SSL certificate)
2. Implement CSRF token validation
3. Add rate limiting to contact form
4. Regular database backups
5. Keep PHP and MySQL updated
6. Use strong database passwords
7. Implement reCAPTCHA for spam prevention

## Common Issues & Solutions

### Issue: "Connection failed: Access denied"
**Solution**: Check database credentials in `includes/db.php`

### Issue: Form not submitting
**Solution**: 
- Check if database exists and tables are created
- Verify file permissions on `process/` directory
- Check PHP error logs

### Issue: Styles not loading
**Solution**:
- Clear browser cache (Ctrl+Shift+Delete)
- Check internet connection (Tailwind CSS uses CDN)
- Verify CDN URL in header.php is correct

### Issue: Page not found (404)
**Solution**:
- Verify all .php files exist in the root directory
- Check file paths in links
- Ensure web server is configured correctly

## Database Backup

To backup the database:

```bash
# Using mysqldump
mysqldump -u root -p balmari > balmari_backup.sql

# To restore
mysql -u root -p balmari < balmari_backup.sql
```

## Production Deployment Checklist

- [ ] Update database credentials with production values
- [ ] Install SSL certificate (HTTPS)
- [ ] Set proper file permissions (644 for files, 755 for directories)
- [ ] Configure error logging
- [ ] Set up automated backups
- [ ] Implement email notifications for form submissions
- [ ] Add reCAPTCHA to contact form
- [ ] Test all pages and forms
- [ ] Monitor server logs
- [ ] Set up regular maintenance schedule

## Support & Help

For issues or questions:
1. Check the main README.md file
2. Review the PHP error logs
3. Check MySQL error logs
4. Verify database credentials
5. Test individual pages

## Next Steps

1. Customize company information
2. Add real project images
3. Update contact details
4. Configure email notifications
5. Add team member information
6. Deploy to production server

Enjoy your new Balmari website!
