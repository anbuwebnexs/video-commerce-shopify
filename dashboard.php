<?php
// Dashboard - Buyer/Customer view showing all products and videos

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';

// Check if database connection exists
if (!isset($conn) || $conn === null) {
    die('Error: Database connection failed. Please check config/database.php');
}

// Define base URL
$base_url = SITE_URL ?? 'http://localhost/videocom/video-commerce-shopify/';

// Get all products with videos (Fixed: using user_id instead of seller_id)
$query = "SELECT p.*, u.name as seller_name, COUNT(v.id) as video_count 
          FROM products p 
          LEFT JOIN users u ON p.user_id = u.id 
          LEFT JOIN videos v ON p.id = v.product_id 
          GROUP BY p.id 
          ORDER BY p.created_at DESC";
          
try {
    $products = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
    error_log('Products query error: ' . $e->getMessage());
}

// Get recommended videos (Fixed: using user_id instead of creator_id)
$video_query = "SELECT v.*, p.title as product_name, u.name as creator_name 
                FROM videos v 
                LEFT JOIN products p ON v.product_id = p.id 
                LEFT JOIN users u ON v.user_id = u.id 
                WHERE v.status = 'active' 
                ORDER BY v.created_at DESC 
                LIMIT 12";
                
try {
    $videos = $conn->query($video_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $videos = [];
    error_log('Videos query error: ' . $e->getMessage());
}
?>
