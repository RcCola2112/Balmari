<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Contact";
include 'includes/header.php';

$success_message = '';
$error_message = '';

// Check for success message from redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = "Thank you! Your message has been sent successfully. We'll get back to you soon.";
}

if (isset($_GET['error']) && $_GET['error'] == '1') {
    $error_message = "There was an error sending your message. Please try again.";
}
?>

<?php
// Load editable contact information from DB (titles used as keys: Location, Email, Phone, Hours)
$contact_items = [];

// Ensure a DB connection exists: prefer PDO ($pdo), else try mysqli ($conn)
if (!isset($pdo) && file_exists(__DIR__ . '/includes/db_pdo.php')) {
    require_once __DIR__ . '/includes/db_pdo.php';
}
if (!isset($pdo) && !isset($conn) && file_exists(__DIR__ . '/includes/db.php')) {
    require_once __DIR__ . '/includes/db.php';
}

if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT title, description FROM contact_information WHERE is_active = 1");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $key = strtolower(trim($r['title']));
            $contact_items[$key] = $r['description'];
        }
    } catch (Exception $e) {
        // If query fails, leave $contact_items empty to fall back to defaults
    }
} elseif (isset($conn)) {
    try {
        $res = $conn->query("SELECT title, description FROM contact_information WHERE is_active = 1");
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $key = strtolower(trim($r['title']));
                $contact_items[$key] = $r['description'];
            }
        }
    } catch (Exception $e) {
        // ignore and fall back to defaults
    }
} else {
    // No DB available; front-end will use static defaults defined below
}

function get_contact($items, $key, $fallback = '') {
    if (isset($items[$key]) && strlen(trim($items[$key])) > 0) {
        return $items[$key];
    }
    return $fallback;
}

function extract_email($text) {
    if (!$text) return null;
    // find first email-like substring
    if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $text, $m)) {
        return $m[0];
    }
    return null;
}

function extract_phone_for_tel($text) {
    if (!$text) return null;
    // keep plus and digits
    if (preg_match('/(\+?[0-9][0-9()\-\s+.]{4,})/', $text, $m)) {
        // normalize: remove spaces, parentheses and dots
        $tel = preg_replace('/[^+0-9]/', '', $m[0]);
        return $tel;
    }
    return null;
}

// Determine display values with sensible defaults
$location_html = nl2br(htmlspecialchars(get_contact($contact_items, 'location', "Balmari Design and Construction Ereville subdivision, Paz St, Balayan, Batangas\nServing the Philippines")));
$email_text = get_contact($contact_items, 'email', 'balmarihome@gmail.com');
$email_addr = extract_email($email_text) ?: $email_text;
$phone_text = get_contact($contact_items, 'phone', '0945 463 2111');
$phone_tel = extract_phone_for_tel($phone_text) ?: preg_replace('/[^0-9+]/', '', $phone_text);
$hours_html = nl2br(htmlspecialchars(get_contact($contact_items, 'hours', "Monday - Friday: 8:00 AM - 8:00 PM\nSaturday: 8:00 AM - 5:00 PM\nSunday: Closed")));
?>

<!-- Page Header -->
<section class="bg-[#183D3D] text-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-['Playfair_Display'] font-semibold mb-4">Contact Us</h1>
        <p class="text-xl">Get in touch with our team</p>
    </div>
</section>
<hr class="border-t border-[#44444E]/30 my-0">

<!-- Success/Error Messages -->
<?php if ($success_message): ?>
    <div class="bg-[#183D3D] border-l-4 border-[#5C8374]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-[#715A5A]" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-white"><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="bg-[#183D3D] border-l-4 border-[#5C8374]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-[#715A5A]" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-white"><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content -->

<section class="py-20 bg-[#040D12] opacity-0 translate-y-6 transition duration-700 ease-out" data-animate>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 mb-20">
            <!-- Contact Information -->
            <div class="md:col-span-1 bg-[#183D3D] rounded-2xl shadow-lg p-10 border border-[#5C8374]/25">
                <h2 class="text-2xl font-['Playfair_Display'] font-semibold text-white mb-8">Contact Information</h2>

                <!-- CONTACT_INFO_START -->
                <div class="mb-8">
                    <h3 class="font-bold text-[#5C8374] mb-2 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-[#715A5A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Location
                    </h3>
                    <p class="text-white/85">
                        <?php echo $location_html; ?>
                    </p>
                </div>

                <div class="mb-8">
                    <h3 class="font-bold text-[#5C8374] mb-2 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-[#715A5A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Email
                    </h3>
                    <p class="text-white/85">
                        <?php if ($email_addr): ?>
                            <a href="mailto:<?php echo htmlspecialchars($email_addr); ?>" class="text-white hover:text-[#5C8374]"><?php echo htmlspecialchars($email_text); ?></a>
                        <?php else: ?>
                            <?php echo nl2br(htmlspecialchars($email_text)); ?>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="mb-8">
                    <h3 class="font-bold text-[#5C8374] mb-2 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-[#715A5A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Phone
                    </h3>
                        <p class="text-white/85">
                            <?php
                            // Render each phone entry on its own line. Admin can enter multiple numbers using Shift+Enter.
                            $phone_lines = preg_split('/\r\n|\r|\n/', $phone_text);
                            foreach ($phone_lines as $line) {
                                $line = trim($line);
                                if ($line === '') continue;
                                $tel = extract_phone_for_tel($line);
                                if ($tel) {
                                    echo '<div><a href="tel:' . htmlspecialchars($tel) . '" class="text-white hover:text-[#5C8374]">' . htmlspecialchars($line) . '</a></div>';
                                } else {
                                    echo '<div>' . htmlspecialchars($line) . '</div>';
                                }
                            }
                            ?>
                        </p>
                </div>

                <div class="mb-8">
                    <h3 class="font-bold text-[#5C8374] mb-2 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-[#715A5A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Hours
                    </h3>
                    <p class="text-[#5C8374]/85">
                        <?php echo $hours_html; ?>
                    </p>
                </div>

                <!-- CONTACT_INFO_END -->

                <!-- Social Links -->
                <div class="mt-12">
                <h3 class="font-bold text-white mb-4">Follow Us</h3>
                <div class="flex space-x-3">
                        <a href="https://web.facebook.com/BalmariHomes" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="w-10 h-10 bg-[#5C8374] text-[#040D12] rounded-full flex items-center justify-center hover:bg-[#44444E] hover:text-white transition">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M22 12.06C22 6.477 17.523 2 11.94 2S1.88 6.477 1.88 12.06c0 5.012 3.657 9.164 8.438 9.94v-7.03H7.898V12.06h2.42V9.845c0-2.385 1.42-3.7 3.594-3.7 1.04 0 2.13.186 2.13.186v2.35h-1.2c-1.182 0-1.55.734-1.55 1.487v1.79h2.64l-.422 2.91h-2.218v7.03C18.343 21.224 22 17.072 22 12.06z"/>
                            </svg>
                        </a>
                        <a href="https://www.instagram.com/balmarihomes/" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="w-10 h-10 bg-[#5C8374] text-[#040D12] rounded-full flex items-center justify-center hover:bg-[#44444E] hover:text-white transition">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 7.1a4.9 4.9 0 1 0 0 9.8 4.9 4.9 0 0 0 0-9.8zm0 8.1a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4z"/>
                                <path d="M17.5 2h-11A4.5 4.5 0 0 0 2 6.5v11A4.5 4.5 0 0 0 6.5 22h11a4.5 4.5 0 0 0 4.5-4.5v-11A4.5 4.5 0 0 0 17.5 2zm3 15.5a3 3 0 0 1-3 3h-11a3 3 0 0 1-3-3v-11a3 3 0 0 1 3-3h11a3 3 0 0 1 3 3v11z"/>
                                <path d="M18.2 6.2a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0z"/>
                            </svg>
                        </a>
                        <a href="https://www.tiktok.com/@balmarihome" target="_blank" rel="noopener noreferrer" aria-label="TikTok" class="w-10 h-10 bg-[#5C8374] text-[#040D12] rounded-full flex items-center justify-center hover:bg-[#44444E] hover:text-white transition">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M16.6 5.82a4.33 4.33 0 0 1-2.97-1.2A4.33 4.33 0 0 1 12.5 1.6h-2.4v13.1a2.36 2.36 0 1 1-1.6-2.24V9.98a5 5 0 1 0 4.88 5v-7.1a6.8 6.8 0 0 0 3.22.8v-2.86z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <!-- CONTACT_INFO_END -->
            
            <!-- Contact Form -->
            <div class="md:col-span-2">
                <div class="bg-[#183D3D] rounded-2xl shadow-lg p-10 border border-[#5C8374]/25">
                    <h2 class="text-2xl font-['Playfair_Display'] font-semibold text-white mb-6">Send us a Message</h2>
                    
                    <form action="process/contact_process.php" method="POST" id="contactForm">
                        <!-- Full Name -->
                        <div class="mb-6">
                            <label for="fullName" class="block text-sm font-medium text-white mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="fullName" 
                                name="full_name" 
                                required
                                class="w-full px-4 py-3 border border-[#5C8374]/30 rounded-xl bg-[#183D3D] text-white focus:ring-2 focus:ring-[#5C8374] focus:border-[#5C8374] outline-none transition"
                                placeholder="John Doe"
                            >
                        </div>
                        
                        <!-- Email -->
                        <div class="mb-6">
                            <label for="email" class="block text-sm font-medium text-white mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required
                                class="w-full px-4 py-3 border border-[#5C8374]/30 rounded-xl bg-[#183D3D] text-white focus:ring-2 focus:ring-[#5C8374] focus:border-[#5C8374] outline-none transition"
                                placeholder="john@example.com"
                            >
                        </div>
                        
                        <!-- Phone -->
                        <div class="mb-6">
                            <label for="phone" class="block text-sm font-medium text-white mb-2">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                required
                                class="w-full px-4 py-3 border border-[#5C8374]/30 rounded-xl bg-[#183D3D] text-white focus:ring-2 focus:ring-[#5C8374] focus:border-[#5C8374] outline-none transition"
                                placeholder="+63 912 345 6789"
                            >
                        </div>
                        
                        <!-- Message -->
                        <div class="mb-6">
                            <label for="message" class="block text-sm font-medium text-white mb-2">
                                Message <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="message" 
                                name="message" 
                                rows="6" 
                                required
                                class="w-full px-4 py-3 border border-[#5C8374]/30 rounded-xl bg-[#183D3D] text-white focus:ring-2 focus:ring-[#5C8374] focus:border-[#5C8374] outline-none transition"
                                placeholder="Tell us about your project..."
                            ></textarea>
                        </div>
                        
                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            class="w-full bg-[#5C8374] text-[#040D12] py-3 rounded-full font-semibold text-lg hover:bg-[#44444E] hover:text-white transition"
                        >
                            Send Message
                        </button>
                        <p class="text-xs text-white/80 mt-4">
                            All fields marked with <span class="text-red-500">*</span> are required.
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<hr class="border-t border-[#44444E]/30 my-8">
<section class="bg-[#040D12] py-16 opacity-0 translate-y-6 transition duration-700 ease-out" data-animate>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-['Playfair_Display'] font-semibold text-white mb-8 text-center">Visit Us</h2>
        <div class="rounded-2xl overflow-hidden shadow-lg border-4 border-[#5C8374]/30">
            <iframe
                title="Balmari Location"
                class="w-full h-96"
                src="https://www.google.com/maps?q=13.9472127,120.729546&output=embed"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
