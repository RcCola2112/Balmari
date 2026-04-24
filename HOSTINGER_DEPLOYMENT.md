# 🚀 HOSTINGER DEPLOYMENT GUIDE

## ✅ Your Production Credentials

```
Host:     127.0.0.1
Database: u549992181_Balmari
User:     u549992181_Arciee
Password: Admin_01_Balmari
```

**Status**: ✅ Already configured in `includes/db.php`

---

## ⚠️ SECURITY ACTION REQUIRED

### Step 1: Change Your Database Password ❗

Your production password was exposed publicly. You MUST change it immediately:

1. Log in to **Hostinger Control Panel**
2. Go to **Databases** → **phpMyAdmin**
3. Click on **User accounts** (at the bottom)
4. Find user: `u549992181_Arciee`
5. Click "Change password"
6. Set a **NEW strong password** (use a random generator)
7. Update `includes/db.php` with the new password

### Step 2: Update db.php

After changing the password in Hostinger, update `includes/db.php`:

```php
$pass = "YOUR_NEW_SECURE_PASSWORD_HERE";
```

---

## 📤 DEPLOYMENT STEPS

### Step 1: Create Database Table in Hostinger

1. Log in to **Hostinger Control Panel**
2. Go to **Databases** → **phpMyAdmin**
3. Select database: `u549992181_Balmari`
4. Go to **SQL** tab
5. Paste and execute:

```sql
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(50),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_email ON contact_messages(email);
CREATE INDEX idx_created_at ON contact_messages(created_at);
```

✅ Confirm the table was created

### Step 2: Upload Files to Hostinger

1. Log in to **Hostinger Control Panel**
2. Go to **File Manager** (or FTP)
3. Navigate to **public_html** folder
4. Create a **new folder**: `balmari`
5. Upload these files into `public_html/balmari/`:

**Main files**:
- index.php
- about.php
- services.php
- projects.php
- contact.php

**Folders**:
- includes/ (with all 4 PHP files)
- process/ (with contact_process.php)
- assets/images/

**Do NOT upload**:
- .gitignore
- README.md
- SETUP_GUIDE.md
- *.sql files
- Other documentation

### Step 3: Verify Directory Structure

After upload, your Hostinger structure should be:

```
public_html/
├── balmari/
│   ├── index.php
│   ├── about.php
│   ├── services.php
│   ├── projects.php
│   ├── contact.php
│   ├── includes/
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── db.php (with your credentials)
│   │   └── config.php
│   ├── process/
│   │   └── contact_process.php
│   └── assets/
│       └── images/
```

---

## 🌐 TEST YOUR WEBSITE

### Test 1: Check Website Loads

Visit in your browser:

```
https://yourdomain.com/balmari/index.php
```

or if mapped to root:

```
https://yourdomain.com/index.php
```

✅ Should see: Home page with hero section

### Test 2: Navigate All Pages

- ✅ https://yourdomain.com/balmari/about.php
- ✅ https://yourdomain.com/balmari/services.php
- ✅ https://yourdomain.com/balmari/projects.php
- ✅ https://yourdomain.com/balmari/contact.php

### Test 3: Test Contact Form

1. Go to Contact page
2. Fill in all fields:
   - Full Name: Test User
   - Email: test@email.com
   - Phone: +63 912 345 6789
   - Message: This is a test message

3. Click "Send Message"
4. Should see: ✅ Success message

### Test 4: Verify Database Submission

1. Go to **Hostinger Control Panel**
2. Open **phpMyAdmin**
3. Select: `u549992181_Balmari`
4. Select: `contact_messages` table
5. Should see your test submission with:
   - full_name: Test User
   - email: test@email.com
   - phone: +63 912 345 6789
   - message: This is a test message
   - created_at: Current timestamp

✅ If data appears → Database is working!

---

## ❌ TROUBLESHOOTING

### Error: "Database connection failed"

**Cause**: Wrong credentials or database doesn't exist

**Fix**:
1. Double-check credentials in `includes/db.php`
2. Verify table exists in phpMyAdmin
3. Check if 127.0.0.1 is correct for your Hostinger account

### Error: "Table doesn't exist" or "Unknown table"

**Cause**: contact_messages table not created

**Fix**:
1. Go to Hostinger → phpMyAdmin
2. Select database `u549992181_Balmari`
3. Run the CREATE TABLE SQL (see Step 1 above)

### Error: "500 Internal Server Error"

**Cause**: Usually PHP error

**Fix**:
1. Check Hostinger error logs (File Manager → .htaccess)
2. Verify all files uploaded correctly
3. Check file permissions (should be 644 for files, 755 for folders)

### Form submits but no data appears

**Cause**: Form processing issue

**Fix**:
1. Check file permissions on `process/contact_process.php`
2. Verify database connection is working
3. Check table structure matches code

---

## 🔧 FTP/SFTP UPLOAD INSTRUCTIONS

If using FTP client (FileZilla, WinSCP, etc.):

1. **Connection Details**:
   - Host: Your Hostinger FTP host
   - Username: Your Hostinger FTP username
   - Password: Your Hostinger FTP password
   - Port: 21 (FTP) or 22 (SFTP)

2. **Upload Path**: `/public_html/balmari/`

3. **Set Permissions**:
   - Files: 644
   - Folders: 755
   - process/ folder: 755

---

## 🎯 OPTIONAL: Map to Root Domain

If you want: `https://yourdomain.com/` instead of `/balmari/`

1. In Hostinger, create **Addon Domain** or **Subdomain**
2. Point to `/balmari/` folder
3. Update your website accordingly

---

## 📊 VERIFY EVERYTHING WORKS

**Checklist**:

- [ ] Database table created in phpMyAdmin
- [ ] All files uploaded to Hostinger
- [ ] Website pages load without errors
- [ ] Contact form accepts submissions
- [ ] Data appears in phpMyAdmin after submission
- [ ] All navigation links work
- [ ] Mobile responsive design works
- [ ] No 404 errors

---

## 🔐 POST-DEPLOYMENT SECURITY

1. **✅ Change the database password** (already requested)
2. **✅ Remove documentation files** from public_html
3. **✅ Set proper file permissions** (644 files, 755 folders)
4. **✅ Consider adding SSL certificate** (https://)
5. **✅ Monitor database for spam submissions**
6. **Optional**: Add reCAPTCHA to contact form

---

## 📝 HOSTINGER-SPECIFIC NOTES

### About 127.0.0.1

On Hostinger shared hosting, `127.0.0.1` works because:
- Your website and database are on the **same server**
- It's the localhost address for that server
- Standard for shared hosting environments

### Database Access

Only accessible via:
- ✅ Your PHP code on the same server
- ✅ phpMyAdmin (Hostinger control panel)
- ❌ NOT from external connections

### File Permissions

Hostinger typically sets these automatically, but ensure:
- PHP files: 644 (readable, executable)
- Folders: 755 (readable, executable, writable)
- process/ folder: 755 (must be executable for form processing)

---

## 🆘 IF YOU GET ERRORS

When reporting errors, provide:

1. **Error message** (exact text)
2. **URL** where error occurs
3. **Page/file** causing issue
4. **Screenshot** (if possible)
5. **Last working action**

Example good error report:
```
Error: "Database connection failed"
URL: https://yourdomain.com/balmari/contact.php
File: includes/db.php
After: Submitted contact form
Screenshot: [attached]
```

---

## 🚀 READY TO DEPLOY?

1. ✅ Change database password in Hostinger
2. ✅ Create database table in phpMyAdmin
3. ✅ Upload files to Hostinger
4. ✅ Test all pages and forms
5. ✅ Verify database submissions
6. ✅ Check mobile responsiveness

**You're ready to go live!** 🎉

---

## 📞 HOSTINGER SUPPORT

If you need Hostinger help:
- **Chat Support**: Available 24/7 in Hostinger Control Panel
- **Knowledge Base**: hostinger.com/help
- **Email**: support@hostinger.com

For database-specific questions, mention:
- Your database name: `u549992181_Balmari`
- User: `u549992181_Arciee`
- You're using MySQL

---

**Last Updated**: February 16, 2026  
**Version**: 1.0  
**Status**: Ready for Hostinger Deployment
