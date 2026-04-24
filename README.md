# Balmari: Design and Construction Website

A professional construction company website built with PHP, Tailwind CSS, and MySQL.

## Features

- ✅ Responsive design using Tailwind CSS
- ✅ Sticky navigation bar with mobile menu
- ✅ Hero section with background image
- ✅ Services cards with hover animations
- ✅ Featured projects grid
- ✅ Contact form with database integration
- ✅ MySQL database for form submissions
- ✅ Prepared statements for security
- ✅ Success/error messages after form submission
- ✅ Clean professional layout

## Project Structure

```
balmari/
├── index.php              # Home page
├── about.php              # About page
├── services.php           # Services page
├── projects.php           # Projects page
├── contact.php            # Contact page
├── includes/
│   ├── header.php         # Header component
│   ├── footer.php         # Footer component
│   └── db.php             # Database configuration
├── process/
│   └── contact_process.php # Contact form handler
├── assets/
│   └── images/            # Images folder
└── README.md              # This file
```

## Installation & Setup

### 1. Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)

### 2. Database Setup

Execute the following SQL commands to create the database and table:

```sql
-- Create Database
CREATE DATABASE balmari CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE balmari;

-- Create contact_messages table
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create index for faster queries
CREATE INDEX idx_email ON contact_messages(email);
CREATE INDEX idx_created_at ON contact_messages(created_at);
```

### 3. Database Configuration

Edit `includes/db.php` with your database credentials:

```php
define('DB_HOST', 'localhost');      // Database host
define('DB_USER', 'root');           // Database username
define('DB_PASS', '');               // Database password
define('DB_NAME', 'balmari');        // Database name
```

### 4. File Permissions

Ensure proper permissions for the following directories:
- `process/` - Must be executable
- `assets/images/` - Must be writable for uploads

```bash
chmod 755 process/
chmod 755 assets/images/
```

### 5. Start Your Server

Using PHP's built-in server (for development):
```bash
php -S localhost:8000
```

Then access the website at: `http://localhost:8000`

Or configure your web server (Apache/Nginx) to point to the project directory.

## Color Scheme

- **Primary**: Navy Blue (#0f172a)
- **Accent**: Orange (#f97316)
- **Background**: White (#ffffff)

## Company Information

- **Company Name**: Balmari: Design and Construction
- **Tagline**: From Concept to Completion
- **Location**: Batangas, Philippines
- **Service Area**: Batangas and Nearby Areas

## Services Offered

1. Architectural Design & Visualization
2. Technical Drawings
3. Blueprint Printing Services
4. Construction Services
5. Build & Sell Real Estate

## Contact Form Security

The contact form implements the following security measures:

- ✅ **Prepared Statements**: Prevents SQL injection attacks
- ✅ **Input Validation**: Validates email format and required fields
- ✅ **Data Sanitization**: Uses trim() and htmlspecialchars()
- ✅ **POST Method**: Only accepts POST requests
- ✅ **Error Handling**: Graceful error handling with try-catch

## Pages Overview

### Home (index.php)
- Hero section with call-to-action
- Statistics section
- Featured services
- Why choose us section

### About (about.php)
- Company story and background
- Mission and vision statements
- Core values
- Team information

### Services (services.php)
- Detailed service descriptions
- Benefits of each service
- Service-specific features
- Call-to-action

### Projects (projects.php)
- Project showcase grid
- Project categories
- Statistics and achievements
- Project details

### Contact (contact.php)
- Contact information
- Contact form with validation
- Success/error messages
- Map placeholder
- Social media links

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Responsive Breakpoints

- **Mobile**: 320px - 640px
- **Tablet**: 641px - 1024px
- **Desktop**: 1025px+

## API & CDN

- **Tailwind CSS**: CDN version (no build process needed)

## Future Enhancements

- [ ] Google Maps integration
- [ ] Image upload for projects
- [ ] Admin dashboard
- [ ] Project filtering and search
- [ ] Newsletter subscription
- [ ] Blog section
- [ ] Multi-language support
- [ ] Email notifications

## Troubleshooting

### Database Connection Error
- Check database credentials in `includes/db.php`
- Ensure MySQL server is running
- Verify database and table exist

### Form Not Submitting
- Check file permissions on `process/` directory
- Verify database connection
- Check browser console for JavaScript errors

### Styles Not Loading
- Clear browser cache
- Check CDN connectivity
- Verify Tailwind CSS CDN link

## Support & Contact

For support or inquiries, contact:
- Email: info@balmari.com
- Phone: +63 912 345 6789
- Location: Batangas, Philippines

## License

© 2024 Balmari Design and Construction. All rights reserved.

## Notes

- This is a fully functional website ready for production
- Ensure proper backups of database
- Monitor contact form submissions regularly
- Keep PHP and MySQL updated for security
- Implement SSL certificate for HTTPS in production
