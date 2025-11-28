# PDO Database Setup Guide

## Problem Fixed

**Error Encountered:**
```
Fatal error: Uncaught Error: Call to undefined method PDOStatement::fetch_all()
in D:\xampp\htdocs\vc\includes\functions.php:15
```

**Root Cause:**
The original code was written for **MySQLi** (a specific MySQL driver), but your project uses **PDO** (PHP Data Objects - a database abstraction layer).

Key differences:
- **MySQLi**: `$result->fetch_all(MYSQLI_ASSOC)` and `$stmt->bind_param()`
- **PDO**: `$stmt->fetchAll(PDO::FETCH_ASSOC)` and `$stmt->execute([$params])`

---

## Solution Implemented

The `includes/functions.php` file has been completely refactored to use PDO instead of MySQLi.

### Key Changes

#### 1. **Fetching Multiple Rows**
**Before (MySQLi):**
```php
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
return $result->fetch_all(MYSQLI_ASSOC);
```

**After (PDO):**
```php
$stmt = $conn->prepare($query);
$stmt->execute([$product_id]);
return $stmt->fetchAll(PDO::FETCH_ASSOC);
```

#### 2. **Fetching Single Row**
**Before (MySQLi):**
```php
$result = $stmt->get_result();
$row = $result->fetch_assoc();
```

**After (PDO):**
```php
$row = $stmt->fetch(PDO::FETCH_ASSOC);
```

#### 3. **Parameter Binding**
**Before (MySQLi):**
```php
$stmt->bind_param('i', $id);
$stmt->execute();
```

**After (PDO):**
```php
$stmt->execute([$id]);
```

#### 4. **Error Handling**
**Added PDOException Handling:**
```php
try {
    // Database operation
    $stmt->execute([$param]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Error message: ' . $e->getMessage());
    return [];
}
```

#### 5. **Getting Last Insert ID**
**Before (MySQLi):**
```php
$id = $conn->insert_id;
```

**After (PDO):**
```php
$id = $conn->lastInsertId();
```

---

## Updated Functions in functions.php

### Product Management
- `getProducts($conn)` - Fetch all products
- `getProductById($conn, $product_id)` - Get single product
- `getProductsByCategory($conn, $category)` - Filter by category
- `getFeaturedProducts($conn, $limit)` - Get featured items
- `searchProducts($conn, $query)` - Search functionality

### Order Management
- `getTotalOrders($conn)` - Count total orders
- `getTotalSales($conn)` - Calculate sales revenue
- `getOrdersByUser($conn, $user_id)` - User order history
- `createOrder($conn, $user_id, $total, $status)` - Create new order
- `updateOrderStatus($conn, $order_id, $status)` - Update order status

### Product Statistics
- `getTotalProducts($conn)` - Count products
- `logActivity($conn, $activity, $user_id)` - Activity tracking

### Utility Functions
- `formatPrice($price)` - Format as currency
- `formatDate($date)` - Format dates
- `sanitizeInput($input)` - Security sanitization
- `validateEmail($email)` - Email validation
- `isAuthenticated()` - Check user session
- `getCurrentUserId()` - Get logged-in user
- `getUserRole()` - Get user role
- `redirect($url)` - HTTP redirect

---

## Setting Up PDO Connection

Your `config/database.php` should have PDO connection like this:

```php
<?php
$host = 'localhost';
$db_name = 'video_commerce';
$user = 'root';
$password = '';

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Connection error: " . $e->getMessage());
}
?>
```

---

## Usage Examples

### Example 1: Get All Products
```php
<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$products = getProducts($conn);
foreach ($products as $product) {
    echo $product['name'] . ' - ' . formatPrice($product['price']);
}
?>
```

### Example 2: Search Products
```php
<?php
$search_term = $_GET['q'] ?? '';
if (!empty($search_term)) {
    $results = searchProducts($conn, $search_term);
    echo 'Found ' . count($results) . ' products';
}
?>
```

### Example 3: Create Order
```php
<?php
if (isAuthenticated()) {
    $user_id = getCurrentUserId();
    $total = 99.99;
    $order_id = createOrder($conn, $user_id, $total, 'pending');
    
    if ($order_id > 0) {
        echo "Order #$order_id created successfully";
    }
}
?>
```

### Example 4: Update Order Status
```php
<?php
$success = updateOrderStatus($conn, $order_id, 'completed');
if ($success) {
    echo "Order status updated";
} else {
    echo "Failed to update order";
}
?>
```

---

## PDO vs MySQLi Comparison

| Feature | PDO | MySQLi |
|---------|-----|--------|
| **Database Support** | Multiple (MySQL, PostgreSQL, SQLite, etc.) | MySQL only |
| **Parameter Binding** | `execute([$param])` | `bind_param()` |
| **Fetch Methods** | `fetch()`, `fetchAll()` | `fetch_assoc()`, `fetch_all()` |
| **Error Handling** | PDOException | Error checking required |
| **Last Insert ID** | `lastInsertId()` | `insert_id` |
| **Learning Curve** | Slightly higher | Lower |
| **Flexibility** | More flexible | Less flexible |
| **Performance** | Similar | Similar |

---

## Benefits of PDO Implementation

✅ **Portability** - Easy to switch databases
✅ **Security** - Built-in prepared statements prevent SQL injection
✅ **Error Handling** - Better exception handling with PDOException
✅ **Consistency** - Same syntax for all supported databases
✅ **Modern Standard** - PDO is the modern PHP standard
✅ **Better Error Messages** - More detailed error information

---

## Testing Your Setup

1. **Verify PDO is enabled** in PHP:
   ```bash
   php -m | grep -i pdo
   ```

2. **Test database connection**:
   ```php
   <?php
   try {
       $conn = new PDO('mysql:host=localhost;dbname=video_commerce', 'root', '');
       echo "Connection successful";
   } catch (PDOException $e) {
       echo "Connection failed: " . $e->getMessage();
   }
   ?>
   ```

3. **Test a function**:
   ```php
   <?php
   $products = getProducts($conn);
   var_dump($products);
   ?>
   ```

---

## Troubleshooting

### Error: "SQLSTATE[HY000]: General error: 1030"
- Usually a database connection issue
- Check database credentials in `config/database.php`
- Ensure MySQL server is running

### Error: "Call to undefined method"
- Verify you're using PDO and not MySQLi
- Check that `$conn` is a PDO object: `var_dump($conn);`

### Error: "No such table"
- Run database migrations/schema setup
- Verify table names are correct
- Check database name in connection string

---

## Summary

All functions in `includes/functions.php` now use PDO for database operations. This ensures compatibility with your PDO-based database connection and provides better security and error handling.

**Status:** ✅ Fixed and tested
**Date:** November 28, 2025
**Version:** 1.1.0 (PDO Compatible)
