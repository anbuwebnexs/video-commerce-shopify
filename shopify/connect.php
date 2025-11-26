<?php
/**
 * Shopify OAuth Connection
 * Initiates OAuth flow to connect user's Shopify store
 */

require_once '../config/database.php';

// Validate shop parameter
$shop = isset($_GET['shop']) ? $_GET['shop'] : null;

if (!$shop) {
    // Show connection form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connect Shopify Store</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body p-4">
                            <h3 class="text-center mb-4">
                                <i class="bi bi-shop"></i> Connect Your Shopify Store
                            </h3>
                            <form method="GET" action="">
                                <div class="mb-3">
                                    <label class="form-label">Your Shopify Store URL</label>
                                    <div class="input-group">
                                        <input type="text" name="shop" class="form-control" 
                                               placeholder="your-store" required>
                                        <span class="input-group-text">.myshopify.com</span>
                                    </div>
                                    <small class="text-muted">Enter your store name without .myshopify.com</small>
                                </div>
                                <button type="submit" class="btn btn-success w-100">
                                    Connect Store
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Sanitize shop name
$shop = preg_replace('/[^a-zA-Z0-9\-]/', '', $shop);
if (strpos($shop, '.myshopify.com') === false) {
    $shop .= '.myshopify.com';
}

// Generate state for CSRF protection
$state = bin2hex(random_bytes(16));
$_SESSION['shopify_state'] = $state;
$_SESSION['shopify_shop'] = $shop;

// Build authorization URL
$authUrl = "https://{$shop}/admin/oauth/authorize?" . http_build_query([
    'client_id' => SHOPIFY_API_KEY,
    'scope' => SHOPIFY_SCOPES,
    'redirect_uri' => SHOPIFY_REDIRECT_URI,
    'state' => $state
]);

// Redirect to Shopify authorization
header("Location: {$authUrl}");
exit;
?>
