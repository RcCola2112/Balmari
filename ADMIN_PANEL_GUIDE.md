# 🎛️ Employee Admin Panel - Quick Reference

## 🔑 Login Credentials

**Admin Panel URL:** `https://yourdomain.com/employee/login.php`

**Default Account:**
- Email: `admin@balmaribuild.com`
- Password: `admin123` (Change this immediately!)

> ⚠️ **SECURITY:** Change your password after first login!

---

## 📋 What You Can Do

### 1️⃣ Upload New Carousel Images
- Click "Upload New Image" on dashboard
- Drag & drop or select JPG, PNG, or WebP
- Add title (required) and subtitle (optional)
- Max file size: 5MB
- Recommended size: 1920x600px

### 2️⃣ View All Images
- Go to "Manage Carousel"
- See all uploaded images in a table
- Check upload date and details

### 3️⃣ Edit Image Details
- Click "Edit" button next to any image
- Change title and subtitle
- Image cannot be changed (delete and re-upload if needed)

### 4️⃣ Delete Images
- Click "Delete" button to remove from carousel
- Or use "Delete Item" in Danger Zone when editing
- Confirmation required
- Image removed from live site instantly

---

## 🚀 Getting Started (First Time Setup)

### Step 1: Database Setup
1. Go to Hostinger > MySQL Databases
2. Open phpMyAdmin
3. Copy contents of `SETUP_DATABASE.sql`
4. Paste in SQL tab and execute

### Step 2: Create Upload Folder
- Folder should exist: `/assets/uploads/carousel/`
- Verify folder permissions are set to 755

### Step 3: Try Login
- Navigate to `/employee/login.php`
- Use test credentials provided
- Change password immediately

### Step 4: Upload First Image
- Click "Upload Image"
- Add carousel image
- Should appear on homepage carousel!

---

## ❓ FAQ

### Q: How do I change my password?
**A:** Currently passwords are set in database. Contact your admin for password reset.

### Q: Why doesn't my image appear?
**A:** 
- Check file size (max 5MB)
- Verify format (JPG, PNG, WebP only)
- Check `/assets/uploads/carousel/` folder exists
- Verify permissions are 755

### Q: Can I upload images for other employees?
**A:** No, each employee can manage their own uploads. Contact admin for new accounts.

### Q: What if I deleted an image by mistake?
**A:** The database record is deleted immediately. The image file is also removed. Consider using version control or recent backups.

### Q: How often do carousel images rotate?
**A:** Every 5 seconds automatically.

### Q: Can I schedule image uploads?
**A:** Not in current version. Consider for future enhancement.

---

## 🔒 Security Tips

✅ **DO:**
- Change default password immediately
- Use strong, unique passwords
- Log out when done
- Never share login credentials
- Report suspicious activity

❌ **DON'T:**
- Leave admin panel open unattended
- Share passwords via email/chat
- Upload images from untrusted sources
- Store credentials in browser

---

## 📁 File Locations

| File | Location |
|------|----------|
| Admin Login | `/employee/login.php` |
| Dashboard | `/employee/dashboard.php` |
| Manage Images | `/employee/manage_carousel.php` |
| Upload Image | `/employee/upload_carousel.php` |
| Edit Image | `/employee/edit_carousel.php` |
| Upload Folder | `/assets/uploads/carousel/` |

---

## 🐛 Troubleshooting

### "Login Failed"
- Verify email spelling
- Check password is correct (case-sensitive)
- Make sure CAPS LOCK is off
- Try copying/pasting credentials

### "Upload Failed"
- File must be JPG, PNG, or WebP
- Size must be under 5MB
- Check folder permissions
- Try different browser

### "Images Not Showing"
- Clear browser cache (CTRL+SHIFT+DEL)
- Check database connection
- Verify images in `/assets/uploads/carousel/`
- Check filename matches database

---

## 📞 Support Checklist

Before contacting support, check:
- ✓ Am I connected to internet?
- ✓ Is login URL correct?
- ✓ Are credentials spelled correctly?
- ✓ Have I cleared browser cache?
- ✓ Is file size under 5MB?
- ✓ Is file format correct (JPG/PNG/WebP)?
- ✓ Do folder permissions exist (755)?

---

## 🎯 Best Practices

1. **Update Carousel Regularly**
   - Keep images fresh and current
   - Remove outdated designs

2. **Use Descriptive Titles**
   - Clear project descriptions
   - Help visitors understand your work

3. **Optimize Images**
   - Resize to ~1920x600px
   - Compress for fast loading
   - Use PNG for small file size

4. **Monitor Activity**
   - Check upload dates
   - Keep track of changes
   - Document different versions

---

## 🔄 Common Workflows

### Promote a New Project (New Carousel)
1. Take photo/screenshot of new project
2. Edit image: resize to 1920x600px
3. Login to `/employee/login.php`
4. Click "Upload Image"
5. Fill in project title & description
6. Upload image
7. Done! Visible on homepage

### Update Carousel Descriptions
1. Go to Manage Carousel
2. Find image to update
3. Click "Edit"
4. Update title/subtitle
5. Save changes
6. Done!

### Seasonal Update
1. Review current carousel
2. Remove outdated images (Delete)
3. Upload new seasonal images (Upload)
4. Update descriptions as needed
5. Done!

---

## 📊 Stats & Monitoring

**Dashboard Shows:**
- Total carousel items
- Quick action buttons
- Your account info
- Login status

**Carousel Table Shows:**
- All uploaded images
- Image timestamps
- Edit/Delete options

---

**Last Updated:** February 17, 2026  
**Version:** 1.0.0  
**Support Email:** admin@balmaribuild.com
