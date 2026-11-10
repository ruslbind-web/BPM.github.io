<?php
/**
 * BPM - Database & Core Configuration
 */

// Enable global output buffering so redirects (header Location) always succeed
if (!ob_get_level()) {
    ob_start();
}

// Ensure session cookie is accessible across all directories (including /admin/)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    session_start();
}

// Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'bpm');
define('DB_USER', 'root');
define('DB_PASS', '');

// Telegram Bot Configuration
define('TG_BOT_USERNAME', 'BpmGarmentsBot');
define('TG_BOT_TOKEN', 'YOUR_TELEGRAM_BOT_TOKEN');

// Admin Credentials
define('ADMIN_USER', 'bpm_admin');
define('ADMIN_PASS', 'bpm2026!');

// Site Info - NO EXPANSION OF BPM
define('SITE_NAME', 'BPM.');
define('SITE_TAGLINE', 'UNDERGROUND SOUND SYSTEM GARMENTS');
define('SITE_URL', 'http://bpm');

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Database Connection Error: " . htmlspecialchars($e->getMessage()) . 
        "<br>Make sure MySQL is running in OpenServer and database 'bpm' exists.");
}

/**
 * Helper to get a database setting with fallback
 */
function getSetting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM shop_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}
