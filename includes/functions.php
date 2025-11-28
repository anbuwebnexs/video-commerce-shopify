<?php
/**
 * Helper Functions for Video Commerce Platform
 * Contains utility functions for the application
 */

/**
 * Get all products from database
 * @param mysqli $conn Database connection
 * @return array Array of products
 */
function getProducts($conn) {
    $query = "SELECT * FROM products";
    $result = $conn->query($query);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Get product by ID
 * @param mysqli $conn Database connection
 * @param int $product_id Product ID
 * @return array Product details
 */
function getProductById($conn, $product_id) {
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Format price for display
 * @param float $price Price value
 * @return string Formatted price
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Check if user is authenticated
 * @return bool True if authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 * @return int|null User ID or null
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get current user role
 * @return string User role
 */
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : 'guest';
}

/**
 * Sanitize user input
 * @param string $input User input
 * @return string Sanitized input
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool True if valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Redirect to URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Get products by category
 * @param mysqli $conn Database connection
 * @param string $category Category name
 * @return array Array of products
 */
function getProductsByCategory($conn, $category) {
    $query = "SELECT * FROM products WHERE category = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Search products
 * @param mysqli $conn Database connection
 * @param string $query Search query
 * @return array Array of matching products
 */
function searchProducts($conn, $query) {
    $query = '%' . sanitizeInput($query) . '%';
    $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $query, $query);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get featured products
 * @param mysqli $conn Database connection
 * @param int $limit Number of products to return
 * @return array Array of featured products
 */
function getFeaturedProducts($conn, $limit = 5) {
    $query = "SELECT * FROM products WHERE featured = 1 LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Log activity
 * @param mysqli $conn Database connection
 * @param string $activity Activity description
 * @param int $user_id User ID
 */
function logActivity($conn, $activity, $user_id = null) {
    if ($user_id === null) {
        $user_id = getCurrentUserId();
    }
    $query = "INSERT INTO activity_log (user_id, activity, timestamp) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param('is', $user_id, $activity);
        $stmt->execute();
    }
}

/**
 * Format date for display
 * @param string $date Date string
 * @return string Formatted date
 */
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

/**
 * Get total sales
 * @param mysqli $conn Database connection
 * @return float Total sales amount
 */
function getTotalSales($conn) {
    $query = "SELECT SUM(total) as total FROM orders WHERE status = 'completed'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

/**
 * Get total orders
 * @param mysqli $conn Database connection
 * @return int Total number of orders
 */
function getTotalOrders($conn) {
    $query = "SELECT COUNT(*) as count FROM orders";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

/**
 * Get total products
 * @param mysqli $conn Database connection
 * @return int Total number of products
 */
function getTotalProducts($conn) {
    $query = "SELECT COUNT(*) as count FROM products";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}
?>
