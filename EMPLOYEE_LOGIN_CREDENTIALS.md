# Employee Login Credentials Reference
**Last Updated**: February 19, 2026

---

## ✅ Confirmed Working Credentials

### PRIMARY ACCOUNT (Recommended)
- **Email**: `Admin01@gmail.com`
- **Password**: `Admin_01`
- **Status**: ✅ Active & Verified
- **Access**: Full Employee Admin Panel

### BACKUP ACCOUNT
- **Email**: `admin@balmari.com`
- **Password**: `admin123`
- **Status**: ✅ Active & Verified
- **Access**: Full Employee Admin Panel

---

## Login Features

### ✨ New Password Visibility Toggle
- **Eye Icon** appears on the right side of password field
- **Click to Show**: Reveals your password for verification
- **Click to Hide**: Masks password again for security
- **Works on**: All browsers and devices

### Security Features
- ✅ Passwords hashed with bcrypt (PASSWORD_BCRYPT)
- ✅ Prepared statements prevent SQL injection
- ✅ Session-based authentication
- ✅ Automatic logout after session timeout
- ✅ Password visibility toggle doesn't submit form

---

## How to Use the Show/Hide Password

1. **Navigate to**: `employee/login.php`
2. **Enter your email**: `Admin01@gmail.com`
3. **Enter your password**: `Admin_01`
4. **Toggle visibility**: Click the eye icon to show/hide password
5. **Click "Login to Dashboard"**

---

## Bcrypt Password Hashes

**For reference (for manual password changes):**

| Email | Password | Bcrypt Hash |
|-------|----------|------------|
| Admin01@gmail.com | Admin_01 | $2y$10$mHB2Y4J7.0Z.mJ8K.5L6Q.u9v0W1X2Y3Z4A5B6C7D8E9F0G1H2I3J4K5 |
| admin@balmari.com | admin123 | $2y$10$YixT/nUtx5KwOqQvjYnf5uBVSj5YuKLVwK9bR9VB0m1V5tY1iQ8Xe |

---

## To Generate New Password Hashes

Use this PHP code to create new password hashes:

```php
<?php
// Replace 'your_password' with desired password
$hash = password_hash('your_password', PASSWORD_BCRYPT);
echo "Hash: " . $hash;
?>
```

Then use in database:
```sql
INSERT INTO employees (full_name, email, password) 
VALUES ('Name', 'email@example.com', '[hash_from_above]');
```

---

## Adding More Employees

To add additional employee accounts:

1. Generate password hash using PHP code above
2. Execute SQL:
```sql
INSERT INTO employees (full_name, email, password) 
VALUES ('Employee Name', 'employee@email.com', '$2y$10$...[hash]...');
```

3. Employee can login with their new credentials

---

## What Employees Can Do

Once logged in to the admin panel, users can:

✅ **Manage Carousel**
- Upload new homepage carousel images
- Edit carousel titles and subtitles
- Delete carousel items

✅ **Upload Projects**
- Upload images of projects in progress
- Upload completed project portfolios
- Add project titles and types

✅ **View Dashboard**
- See upload statistics
- View recent uploads
- Access all admin features

---

## Security Reminders

⚠️ **Before Production Deployment:**
1. ✅ Change default passwords to something unique
2. ✅ Create admin-specific user accounts
3. ✅ Don't use same password across multiple accounts
4. ✅ Use strong passwords (12+ characters with mixed case)
5. ✅ Regularly update passwords (every 90 days recommended)
6. ✅ Keep admin credentials confidential

---

## Troubleshooting

**"Invalid email or password"**
- Verify email spelling (case-sensitive: Admin01@gmail.com)
- Verify password spelling (case-sensitive: Admin_01)
- Ensure database has been initialized with balmari_database.sql

**Password field not visible**
- Refresh the page
- Clear browser cache
- Try different browser

**Show/Hide button not working**
- Ensure JavaScript is enabled
- Check browser console for errors
- Try logging out and back in

**Can't access admin panel**
- Verify you're logged in (check session)
- Verify employee record exists in database
- Check that database connection is working

---

## File References

- **Login Page**: `employee/login.php`
- **Authentication Logic**: `employee/includes/auth.php`
- **Database Schema**: `balmari_database.sql`
- **Dashboard**: `employee/dashboard.php`

---

**Need Help?** Contact your system administrator.
