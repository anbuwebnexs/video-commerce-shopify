<?php
/**
 * Shopify OAuth Callback
 * Handles the OAuth callback and exchanges code for access token
 */

require_once '../config/database.php';

// Verify state parameter
$state = isset($_GET['state']) ? $_GET['state'] : null;
$code = isset($_GET['code']) ? $_GET['code'] : null;
$shop = isset($_GET['shop']) ? $_GET['shop'] : null;

if (!$state || !$code || !$shop) {
    die('Invalid request: Missing required parameters');
}

if (!isset($_SESSION['shopify_state']) || $state !== $_SESSION['shopify_state']) {
    die('Invalid state parameter - potential CSRF attack');
}

// Exchange code for access token
$tokenUrl = "https://{$shop}/admin/oauth/access_token";
$tokenData = [
    'client_id' => SHOPIFY_API_KEY,
    'client_secret' => SHOPIFY_API_SECRET,
    'code' => $code
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die('Failed to obtain access token');
}

$tokenResponse = json_decode($response, true);
$accessToken = $tokenResponse['access_token'];

// Store shop connection in database
try {
    $stmt = $pdo->prepare("
        INSERT INTO shopify_stores (shop_domain, access_token, user_id, created_at)
        VALUES (:shop, :token, :user_id, NOW())
        ON DUPLICATE KEY UPDATE access_token = :token, updated_at = NOW()
    ");
    $stmt->execute([
        'shop' => $shop,
        'token' => $accessToken,
        'user_id' => $_SESSION['user_id'] ?? 0
    ]);
    
    // Set session variables
    $_SESSION['shopify_connected'] = true;
    $_SESSION['shopify_shop'] = $shop;
    $_SESSION['shopify_token'] = $accessToken;
    
    // Sync products from Shopify
    syncShopifyProducts($pdo, $shop, $accessToken);
    
    // Redirect to success page
    header('Location: ../index.php?shopify=connected');
    exit;
    
} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

/**
 * Sync products from Shopify store
 */
function syncShopifyProducts($pdo, $shop, $token) {
    $apiUrl = "https://{$shop}/admin/api/2024-01/products.json";
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "X-Shopify-Access-Token: {$token}",
        "Content-Type: application/json"
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    
    if (isset($data['products'])) {
        foreach ($data['products'] as $product) {
            $stmt = $pdo->prepare("
                INSERT INTO products (shopify_product_id, title, description, price, image, shop_domain)
                VALUES (:pid, :title, :desc, :price, :image, :shop)
                ON DUPLICATE KEY UPDATE title = :title, description = :desc, price = :price, image = :image
            ");
            $stmt->execute([
                'pid' => $product['id'],
                'title' => $product['title'],
                'desc' => $product['body_html'],
                'price' => $product['variants'][0]['price'] ?? 0,
                'image' => $product['images'][0]['src'] ?? '',
                'shop' => $shop
            ]);
        }
    }
}
?>
