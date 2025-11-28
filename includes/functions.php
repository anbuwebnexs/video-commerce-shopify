<?php
/**
 * Helper Functions for Video Commerce Platform
 * PDO Compatible - Works with MySQL/PDO Database Connection
 */

/**
 * Get all products from database
 * @param PDO $conn Database connection
 * @return array Array of products
 */
function getProducts($conn) {
    try {
        $query = "SELECT * FROM products";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching products: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get product by ID
 * @param PDO $conn Database connection
 * @param int $product_id Product ID
 * @return array Product details
 */
function getProductById($conn, $product_id) {
    try {
        $query = "SELECT * FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching product: ' . $e->getMessage());
        return null;
    }
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
 * @param PDO $conn Database connection
 * @param string $category Category name
 * @return array Array of products
 */
function getProductsByCategory($conn, $category) {
    try {
        $query = "SELECT * FROM products WHERE category = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching products by category: ' . $e->getMessage());
        return [];
    }
}

/**
 * Search products
 * @param PDO $conn Database connection
 * @param string $query Search query
 * @return array Array of matching products
 */
function searchProducts($conn, $query) {
    try {
        $search = '%' . sanitizeInput($query) . '%';
        $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$search, $search]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error searching products: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get featured products
 * @param PDO $conn Database connection
 * @param int $limit Number of products to return
 * @return array Array of featured products
 */
function getFeaturedProducts($conn, $limit = 5) {
    try {
        $query = "SELECT * FROM products WHERE featured = 1 LIMIT ?";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching featured products: ' . $e->getMessage());
        return [];
    }
}

/**
 * Log activity
 * @param PDO $conn Database connection
 * @param string $activity Activity description
 * @param int $user_id User ID (optional)
 */
function logActivity($conn, $activity, $user_id = null) {
    try {
        if ($user_id === null) {
            $user_id = getCurrentUserId();
        }
        $query = "INSERT INTO activity_log (user_id, activity, timestamp) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->execute([$user_id, $activity]);
    } catch (PDOException $e) {
        error_log('Error logging activity: ' . $e->getMessage());
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
 * @param PDO $conn Database connection
 * @return float Total sales amount
 */
function getTotalSales($conn) {
    try {
        $query = "SELECT SUM(total) as total FROM orders WHERE status = 'completed'";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    } catch (PDOException $e) {
        error_log('Error fetching total sales: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get total orders
 * @param PDO $conn Database connection
 * @return int Total number of orders
 */
function getTotalOrders($conn) {
    try {
        $query = "SELECT COUNT(*) as count FROM orders";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] ?? 0;
    } catch (PDOException $e) {
        error_log('Error fetching total orders: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get total products
 * @param PDO $conn Database connection
 * @return int Total number of products
 */
function getTotalProducts($conn) {
    try {
        $query = "SELECT COUNT(*) as count FROM products";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'] ?? 0;
    } catch (PDOException $e) {
        error_log('Error fetching total products: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get orders by user ID
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @return array Array of user orders
 */
function getOrdersByUser($conn, $user_id) {
    try {
        $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching user orders: ' . $e->getMessage());
        return [];
    }
}

/**
 * Create a new order
 * @param PDO $conn Database connection
 * @param int $user_id User ID
 * @param float $total Order total
 * @param string $status Order status
 * @return int Order ID or 0 on failure
 */
function createOrder($conn, $user_id, $total, $status = 'pending') {
    try {
        $query = "INSERT INTO orders (user_id, total, status, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->execute([$user_id, $total, $status]);
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log('Error creating order: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Update order status
 * @param PDO $conn Database connection
 * @param int $order_id Order ID
 * @param string $status New status
 * @return bool Success status
 */
function updateOrderStatus($conn, $order_id, $status) {
    try {
        $query = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        return $stmt->execute([$status, $order_id]);
    } catch (PDOException $e) {
        error_log('Error updating order status: ' . $e->getMessage());
        return false;
    }
}
?>
