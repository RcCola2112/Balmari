# ✅ HOSTINGER DEPLOYMENT CHECKLIST

## 🔐 SECURITY FIRST

- [ ] **IMMEDIATELY**: Change your database password in Hostinger
  - Old password was exposed: `Admin_01_Balmari`
  - Set new strong password
  - Update `includes/db.php` with new password

---

## 🗄️ DATABASE SETUP

- [ ] Log in to Hostinger Control Panel
- [ ] Open phpMyAdmin
- [ ] Select database: `u549992181_Balmari`
- [ ] Go to SQL tab
- [ ] Copy and run the CREATE TABLE command (see HOSTINGER_DEPLOYMENT.md)
- [ ] Verify table `contact_messages` was created with columns:
  - id (INT, PRIMARY KEY, AUTO_INCREMENT)
  - full_name (VARCHAR 150)
  - email (VARCHAR 150)
  - phone (VARCHAR 50)
  - message (TEXT)
  - created_at (TIMESTAMP)

---

## 📤 FILE UPLOAD

### Files to Upload

- [ ] All 5 main PHP files:
  - index.php
  - about.php
  - services.php
  - projects.php
  - contact.php

- [ ] includes/ folder:
  - header.php
  - footer.php
  - db.php (✅ with your credentials)
  - config.php

- [ ] process/ folder:
  - contact_process.php

- [ ] assets/ folder:
  - images/ (subfolder)

### Upload Method

- [ ] Using File Manager (recommended for beginners)
  - OR
- [ ] Using FTP/SFTP client (more advanced)

### Upload Location

- [ ] Destination: `/public_html/balmari/`
- [ ] OR: `/public_html/` (if mapping to root)

### File Permissions

- [ ] Regular files: 644
- [ ] Folders: 755
- [ ] process/ folder: 755 (executable)

---

## 🧪 TESTING

### Page Loading Tests

- [ ] Home page loads: https://yourdomain.com/balmari/
- [ ] About page loads: https://yourdomain.com/balmari/about.php
- [ ] Services page loads: https://yourdomain.com/balmari/services.php
- [ ] Projects page loads: https://yourdomain.com/balmari/projects.php
- [ ] Contact page loads: https://yourdomain.com/balmari/contact.php

### Navigation Tests

- [ ] All menu links work
- [ ] Home link navigates correctly
- [ ] Mobile menu works (test on phone)
- [ ] All buttons are clickable
- [ ] Footer links work

### Form Testing

- [ ] Contact form appears
- [ ] All form fields are accessible:
  - Full Name field
  - Email field
  - Phone field
  - Message field
- [ ] Submit button is clickable
- [ ] Form accepts valid data

### Form Submission Test

1. [ ] Fill form with test data:
   - Name: Test User
   - Email: test@youremail.com
   - Phone: +63 912 3456789
   - Message: Testing the form submission

2. [ ] Click Send Message button

3. [ ] See success message: "Thank you! Your message has been sent successfully"

4. [ ] Check phpMyAdmin for data:
   - Go to phpMyAdmin
   - Select database `u549992181_Balmari`
   - Check `contact_messages` table
   - Verify test submission appears

### Design & Responsiveness

- [ ] Colors display correctly (Navy Blue, Orange, White)
- [ ] Navigation bar is sticky (stays at top when scrolling)
- [ ] Mobile menu appears on small screens
- [ ] Layout responsive on phone
- [ ] Layout responsive on tablet
- [ ] Layout responsive on desktop
- [ ] All animations work smoothly
- [ ] Hover effects work on service cards

### Performance Tests

- [ ] Page loads quickly (< 3 seconds)
- [ ] No 404 errors in browser console
- [ ] No JavaScript errors in console
- [ ] Images load properly
- [ ] Styles load correctly

---

## 🔧 TROUBLESHOOTING

If issues occur:

### Database Connection Error

- [ ] Verify credentials in `includes/db.php`
- [ ] Check if database `u549992181_Balmari` exists
- [ ] Check if user `u549992181_Arciee` exists
- [ ] Verify password is correct
- [ ] Check if table `contact_messages` exists

### 500 Internal Server Error

- [ ] Check Hostinger error logs
- [ ] Verify all PHP files uploaded
- [ ] Check file permissions
- [ ] Ensure no syntax errors in PHP files
- [ ] Contact Hostinger support

### Form Not Submitting

- [ ] Check file permissions on `process/contact_process.php`
- [ ] Verify database connection works
- [ ] Check form HTML structure
- [ ] Check browser console for JavaScript errors
- [ ] Verify email validation works

### Blank Pages

- [ ] Check that `includes/header.php` is being included
- [ ] Verify Tailwind CSS CDN is loading
- [ ] Check browser console for errors
- [ ] Verify PHP syntax

---

## 🎯 FINAL VERIFICATION

### Before Going Live

- [ ] All 5 pages load without errors
- [ ] Contact form submits successfully
- [ ] Database stores submissions correctly
- [ ] Mobile responsive design works
- [ ] All links navigate correctly
- [ ] No security warnings
- [ ] Website looks professional
- [ ] No console errors or warnings

### Performance Check

- [ ] Page load time < 3 seconds
- [ ] Responsive on all devices
- [ ] Animations are smooth
- [ ] Forms work properly

### Security Check

- [ ] Database password changed ✅ (CRITICAL)
- [ ] No sensitive files exposed
- [ ] SQL injection prevention working
- [ ] Input validation working
- [ ] HTTPS enabled (optional but recommended)

---

## 📊 SUCCESS CRITERIA

✅ Website loads at https://yourdomain.com/balmari/  
✅ All pages accessible and functional  
✅ Contact form submits and stores data  
✅ Mobile responsive on all devices  
✅ Professional appearance maintained  
✅ No errors in console  
✅ Database password changed  

---

## 🚀 DEPLOYMENT COMPLETE

When all checkboxes are checked, your Balmari website is:

✅ Fully deployed  
✅ Fully functional  
✅ Secure  
✅ Ready for visitors  

---

## 📞 NEXT STEPS

1. Monitor form submissions regularly
2. Backup database periodically
3. Keep PHP and MySQL updated
4. Monitor website performance
5. Consider adding reCAPTCHA to form
6. Set up email notifications for form submissions
7. Monitor security logs

---

**Deployment Status**: Ready to Deploy  
**Last Updated**: February 16, 2026  
**Version**: 1.0

Good luck with your deployment! 🎉
