# 🔧 Employee Admin Panel Setup Guide

## 📋 Setup Instructions

### Step 1: Create Database Tables

Run these SQL queries in your Hostinger MySQL database (through phpMyAdmin):

#### Carousel Table
```sql
CREATE TABLE IF NOT EXISTS carousel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255),
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(created_at)
);
```

#### Employees Table
```sql
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Step 2: Create First Employee Account

```sql
INSERT INTO employees (full_name, email, password) 
VALUES (
    'Admin User',
    'admin@balmaribuild.com',
    '$2y$10$YourHashedPasswordHere'
);
```

**To generate a password hash**, use this PHP snippet:
```php
<?php
echo password_hash('your_password', PASSWORD_BCRYPT);
?>
```

Or use online tools like: https://www.browserling.com/tools/bcrypt

### Step 3: Access Admin Panel

1. Navigate to: `https://yourdomain.com/employee/login.php`
2. Login with your credentials
3. Start managing carousel images!

---

## 🔐 Security Best Practices

✅ **Always use HTTPS** - Never share credentials over HTTP  
✅ **Change default passwords** - Create unique passwords for each employee  
✅ **Limit file uploads** - Only JPG, PNG, WebP allowed  
✅ **Regular backups** - Backup database monthly  
✅ **Monitor access** - Check login activity

---

## 📁 Folder Structure

```
balmari/
├── index.php (Updated to load carousel from DB)
├── employee/
│   ├── login.php              ← Employee login page
│   ├── dashboard.php          ← Admin dashboard
│   ├── manage_carousel.php    ← View all carousel items
│   ├── upload_carousel.php    ← Upload new image
│   ├── edit_carousel.php      ← Edit carousel details
│   ├── delete_carousel.php    ← Delete carousel item
│   ├── logout.php             ← Logout
│   └── includes/
│       ├── auth.php           ← Authentication functions
│       └── header.php         ← Admin header template
├── assets/
│   └── uploads/
│       └── carousel/          ← Uploaded carousel images
└── includes/
    ├── db.php                 ← Database connection
    └── ...
```

---

## 🎯 Features Included

✨ **Secure Login System** - Password hashing with bcrypt  
✨ **Upload Images** - Drag & drop interface  
✨ **Edit Details** - Change title and subtitle  
✨ **Delete Items** - Remove carousel items  
✨ **Dashboard** - Overview of all items  
✨ **Session Management** - Automatic logout  
✨ **Professional UI** - Tailwind CSS styling  
✨ **Input Validation** - Prepared statements for security  

---

## 🚀 How It Works

### For Employees:
1. Login at `/employee/login.php`
2. Upload carousel images with titles
3. Edit or delete as needed
4. Changes appear instantly on homepage

### For Homepage:
- `index.php` fetches carousel images from database
- Displays them dynamically
- No manual editing needed!

---

## 🐛 Troubleshooting

### "No carousel items found"
- Make sure table was created correctly
- Check file permissions on `/assets/uploads/carousel/`
- Verify database connection in `includes/db.php`

### "Image upload fails"
- Check folder permissions (should be 755)
- Verify file size is under 5MB
- Ensure format is JPG, PNG, or WebP

### "Can't login"
- Verify employee was added to database
- Double-check email and password
- Make sure password is hashed with bcrypt

### "404 errors on uploaded images"
- Verify image files exist in `/assets/uploads/carousel/`
- Check file permissions (should be 644)
- Verify filename matches database entry

---

## 📞 Support

For issues or questions:
1. Check database tables are created
2. Verify file permissions
3. Review browser console for errors
4. Check server error logs

---

## 🔄 Future Enhancements

Consider adding:
- Role-based access (Admin vs Employee)
- Image compression on upload
- Carousel ordering/positioning
- Bulk operations
- Activity logging
- Email notifications
- Image optimization

---

**Last Updated:** February 17, 2026  
**Version:** 1.0
