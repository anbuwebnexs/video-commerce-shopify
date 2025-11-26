<?php
/**
 * Database Configuration
 * Video Commerce Platform
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'video_commerce');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Shopify API Configuration
define('SHOPIFY_API_KEY', 'your_shopify_api_key');
define('SHOPIFY_API_SECRET', 'your_shopify_api_secret');
define('SHOPIFY_REDIRECT_URI', 'https://yourdomain.com/shopify/callback.php');
define('SHOPIFY_SCOPES', 'read_products,read_inventory,read_orders');

// Socket.io Server Configuration
define('SOCKET_SERVER_URL', 'http://localhost:3000');

// WebRTC Configuration
define('WEBRTC_STUN_SERVER', 'stun:stun.l.google.com:19302');
define('WEBRTC_TURN_SERVER', ''); // Optional TURN server

// Site Configuration
define('SITE_URL', 'http://localhost/video-commerce');
define('SITE_NAME', 'VideoCommerce');

// Create PDO connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
