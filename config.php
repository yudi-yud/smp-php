<?php
/**
 * Konfigurasi SMPN 3 Satu Atap Cipari
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load database connection
require_once __DIR__ . '/config/database.php';

/**
 * Get site setting from database or fallback to default
 *
 * @param string $key Setting key
 * @param mixed $default Default value if not found
 * @return mixed Setting value
 */
function getSetting($key, $default = '') {
    static $settings = null;

    if ($settings === null) {
        try {
            $pdo = getDB();
            $stmt = $pdo->query("SELECT * FROM site_settings");
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException $e) {
            // Return defaults if database connection fails
            $settings = [];
        }
    }

    return $settings[$key] ?? $default;
}

// Site Configuration (with database fallback)
define('SITE_NAME', getSetting('site_name', 'SMPN 3 Satu Atap Cipari'));
define('SITE_TITLE', getSetting('site_title', 'SMPN 3 Satu Atap Cipari - Membangun Generasi Cerdas dan Berkarakter'));

// Detect base URL automatically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
define('BASE_URL', $protocol . $host . $path);

// School Information
define('SCHOOL_ADDRESS', getSetting('school_address', 'Jl. Depok No.24, Karangreja, Kec. Cipari, Kabupaten Cilacap, Jawa Tengah 53262'));
define('SCHOOL_PHONE', getSetting('school_phone', '(0232) 123-4567'));
define('SCHOOL_WHATSAPP', '0812-3456-7890');
define('SCHOOL_EMAIL', getSetting('school_email', 'info@smpn3cipari.sch.id'));

// Principal Information
define('PRINCIPAL_NAME', getSetting('principal_name', 'Drs. H. Ahmad Fauzi, M.Pd.'));
define('PRINCIPAL_TITLE', 'Kepala Sekolah SMPN 3 Satu Atap Cipari');

// Accreditation
define('ACCREDITATION', getSetting('accreditation', 'A (Unggul)'));
define('NPSN', '2021xxxxx');
define('NISN', '1234xxxx');

// Social Media (from database)
$social_links = [
    'facebook' => getSetting('facebook_url', 'https://www.facebook.com/smpn3cipari'),
    'instagram' => getSetting('instagram_url', 'https://www.instagram.com/smpn3cipari'),
    'youtube' => getSetting('youtube_url', 'https://www.youtube.com/@smpn3cipari'),
    'twitter' => getSetting('twitter_url', 'https://www.twitter.com/smpn3cipari')
];

/**
 * Get published news items
 *
 * @param int $limit Number of items to return
 * @return array News items
 */
function getNews($limit = 3) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT * FROM news
            WHERE status = 'published'
            ORDER BY published_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get published achievements
 *
 * @param int $limit Number of items to return
 * @return array Achievement items
 */
function getAchievements($limit = 6) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT * FROM achievements
            WHERE status = 'published'
            ORDER BY year DESC, sort_order
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get published programs
 *
 * @param int $limit Number of items to return
 * @return array Program items
 */
function getPrograms($limit = 4) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT * FROM programs
            WHERE status = 'published'
            ORDER BY sort_order
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get published gallery items
 *
 * @param int $limit Number of items to return
 * @return array Gallery items
 */
function getGallery($limit = 8) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT * FROM gallery
            WHERE status = 'published'
            ORDER BY sort_order
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get published testimonials
 *
 * @param int $limit Number of items to return
 * @return array Testimonial items
 */
function getTestimonials($limit = 3) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT * FROM testimonials
            WHERE status = 'published'
            ORDER BY sort_order
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get published features
 *
 * @param int $limit Number of items to return
 * @return array Feature items
 */
function getFeatures($limit = 4) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT * FROM features
            WHERE status = 'published'
            ORDER BY sort_order
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get published statistics
 *
 * @param int $limit Number of items to return
 * @return array Statistics items
 */
function getStatistics($limit = 4) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT * FROM statistics
            WHERE status = 'published'
            ORDER BY sort_order
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Submit contact form
 *
 * @param array $data Contact form data
 * @return bool Success status
 */
function submitContactForm($data) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            INSERT INTO contacts (name, email, subject, message, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['subject'] ?? '',
            $data['message'],
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Contact Form Error: " . $e->getMessage());
        return false;
    }
}
