<?php
// Debug and environment configuration
// Use environment variables to control behavior in production
$app_env = getenv('APP_ENV') ?: 'development';
$app_debug = getenv('APP_DEBUG'); // if set, use it ("1" or "0")
if ($app_debug !== false) {
	$debug = filter_var($app_debug, FILTER_VALIDATE_BOOLEAN);
} else {
	$debug = ($app_env !== 'production');
}
ini_set('display_errors', $debug ? 1 : 0);
ini_set('display_startup_errors', $debug ? 1 : 0);
error_reporting($debug ? E_ALL : 0);

/**
 * Balmari Configuration File
 * Update values here to customize the website
 */

// ============================================
// COMPANY INFORMATION
// ============================================
define('COMPANY_NAME', 'Balmari');
define('COMPANY_FULL_NAME', 'Balmari: Design and Construction');
define('COMPANY_TAGLINE', 'From Concept to Completion');
define('COMPANY_LOCATION', 'Philippines');
define('SERVICE_AREA', 'Nationwide');

// ============================================
// CONTACT INFORMATION
// ============================================
define('COMPANY_EMAIL', 'balmarihome@gmail.com');
define('COMPANY_PHONE', '0945 463 2111');
define('COMPANY_PHONE_LINK', '+639454632111');

// ============================================
// BUSINESS HOURS
// ============================================
define('BUSINESS_HOURS_WEEKDAY', 'Monday - Friday: 8:00 AM - 8:00 PM');
define('BUSINESS_HOURS_SATURDAY', 'Saturday: 8:00 AM - 5:00 PM');
define('BUSINESS_HOURS_SUNDAY', 'Sunday: Closed');

// ============================================
// COLOR SCHEME (HEX VALUES)
// ============================================
define('COLOR_PRIMARY', '#0f172a');      // Navy Blue
define('COLOR_ACCENT', '#f97316');       // Orange
define('COLOR_BACKGROUND', '#ffffff');   // White

// ============================================
// SOCIAL MEDIA LINKS
// ============================================
define('SOCIAL_FACEBOOK', 'https://web.facebook.com/BalmariHomes');
define('SOCIAL_INSTAGRAM', 'https://www.instagram.com/balmarihomes/');
define('SOCIAL_TIKTOK', 'https://www.tiktok.com/@balmarihome');

// ============================================
// SEO SETTINGS
// ============================================
define('SITE_TITLE', 'Balmari: Design and Construction');
define('SITE_DESCRIPTION', 'Professional design and construction services across the Philippines');
define('SITE_KEYWORDS', 'construction, design, architecture, building, Philippines');
define('SITE_URL', 'https://balmari.com');

// ============================================
// STATISTICS (Update these numbers)
// ============================================
define('STAT_PROJECTS_COMPLETED', '50+');
define('STAT_SATISFACTION_RATE', '100%');
define('STAT_YEARS_EXPERIENCE', '15+');
define('STAT_CUSTOMER_SUPPORT', '24/7');

// ============================================
// FEATURES & SETTINGS
// ============================================
define('ENABLE_CONTACT_FORM', true);
define('ENABLE_BLOG', false);
define('ENABLE_PORTFOLIO', true);
define('MAINTENANCE_MODE', false);

// ============================================
// FORMS & VALIDATION
// ============================================
define('MAX_MESSAGE_LENGTH', 5000);
define('MIN_MESSAGE_LENGTH', 10);
define('PHONE_PATTERN', '/^[0-9+\s\-()]*$/');

// ============================================
// EMAIL SETTINGS (Optional - for future use)
// ============================================
define('MAIL_FROM', 'noreply@balmari.com');
define('MAIL_FROM_NAME', 'Balmari Design and Construction');
define('MAIL_ADMIN', 'admin@balmari.com');

// ============================================
// SECURITY SETTINGS
// ============================================
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes in seconds

// ============================================
// FILE UPLOAD SETTINGS
// ============================================
define('MAX_UPLOAD_SIZE', 5242880); // 5 MB in bytes
define('ALLOWED_UPLOAD_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
define('UPLOAD_DIRECTORY', __DIR__ . '/assets/images/');

// ============================================
// PAGINATION
// ============================================
define('ITEMS_PER_PAGE', 12);

// ============================================
// TIMEZONE
// ============================================
define('TIMEZONE', 'Asia/Manila');
date_default_timezone_set(TIMEZONE);
?>
