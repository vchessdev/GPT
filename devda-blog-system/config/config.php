<?php
/**
 * devDA Blog System - Configuration File
 * Tệp cấu hình chính của hệ thống
 */

// ============================================
// DATABASE & FILE PATHS
// ============================================

define('BASE_DIR', __DIR__ . '/../');
define('DATA_DIR', BASE_DIR . 'data/');
define('UPLOADS_DIR', BASE_DIR . 'uploads/');
define('API_DIR', BASE_DIR . 'api/');

// Create data directory if not exists
if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
if (!is_dir(UPLOADS_DIR)) mkdir(UPLOADS_DIR, 0755, true);

// ============================================
// SECURITY SETTINGS
// ============================================

// Hash algorithm for passwords
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_COST', 10);

// Session configuration
define('SESSION_NAME', 'DEVDA_SESSION');
define('SESSION_LIFETIME', 2592000); // 30 days in seconds

// Site settings
define('SITE_NAME', 'devDA Blog System');
define('SITE_DOMAIN', 'devda.undo.it');
define('SITE_URL', 'https://' . SITE_DOMAIN . '/blog/');
define('ADMIN_EMAIL', 'admin@devda.undo.it');

// ============================================
// PAGINATION & LIMITS
// ============================================

define('POSTS_PER_PAGE', 10);
define('COMMENTS_PER_PAGE', 20);
define('USERS_PER_PAGE', 25);

// File upload settings
define('MAX_FILE_SIZE', 10485760);           // 10MB in bytes
define('MAX_IMAGE_SIZE', 5242880);           // 5MB for images
define('MAX_PDF_SIZE', 20971520);            // 20MB for PDFs

define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOC_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt']);
define('ALLOWED_MIME_TYPES', [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'text/plain' => 'txt'
]);

// ============================================
// ERROR HANDLING
// ============================================

// Display errors (set to false in production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 0);
}

// ============================================
// CATEGORY & TAG DEFAULTS
// ============================================

define('DEFAULT_CATEGORIES', [
    'Kiến Thức Cơ Bản',
    'Đề Thi & Ôn Luyện',
    'Kỹ Năng & Mẹo Vặt',
    'Tài Liệu & E-book',
    'Trao Đổi Học Tập',
    'Tin Tức & Sự Kiện'
]);

// ============================================
// INITIALIZE SESSION
// ============================================

session_name(SESSION_NAME);
session_start();

// Auto-logout based on session lifetime
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
        session_unset();
        session_destroy();
        header('Location: ' . SITE_URL . 'login.php?expired=1');
        exit;
    }
}
$_SESSION['last_activity'] = time();

?>
