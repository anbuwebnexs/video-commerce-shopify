<?php
/**
 * Video Commerce Platform - Main Entry Point
 * Connects to Shopify, WebRTC Video, Socket.io Chat
 */

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/ShopifyAPI.php';

// Check if database connection exists
if (!isset($conn) || $conn === null) {
    die('Error: Database connection failed. Please check config/database.php');
}

// Define base URL for all links
$base_url = 'http://localhost/videocom/video-commerce-shopify/';

$pageTitle = 'Video Commerce - Shop Live';
$products = getProducts($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Bootstrap 5.0.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">
                <i class="bi bi-play-circle-fill"></i> VideoCommerce
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="<?php echo $base_url; ?>index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>live.php">Live Stream</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $base_url; ?>products.php">Products</a></li>
                </ul>
                <div class="d-flex">
                    <?php if(isset($_SESSION['shopify_connected'])): ?>
                        <span class="badge bg-success me-2 align-self-center">Shopify Connected</span>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>shopify/connect.php" class="btn btn-outline-success me-2">Connect Shopify</a>
                    <?php endif; ?>
                    <a href="<?php echo $base_url; ?>cart.php" class="btn btn-outline-light">
                        <i class="bi bi-cart3"></i> Cart <span class="badge bg-danger" id="cart-count">0</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Live Video -->
    <section class="hero-section bg-gradient">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold">Shop Live with Video</h1>
                    <p class="lead">Watch product demos, join live shopping events, and chat with sellers in real-time.</p>
                    <a href="<?php echo $base_url; ?>live.php" class="btn btn-primary btn-lg me-2">
                        <i class="bi bi-broadcast"></i> Watch Live
                    </a>
                    <a href="<?php echo $base_url; ?>products.php" class="btn btn-outline-secondary btn-lg">Browse Products</a>
                </div>
                <div class="col-lg-6">
                    <div class="video-preview-card">
                        <div id="featured-video" class="ratio ratio-16x9">
                            <div class="placeholder-video d-flex align-items-center justify-content-center bg-dark text-white">
                                <i class="bi bi-play-circle display-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="products-section py-5">
        <div class="container">
            <h2 class="text-center mb-4">Featured Products</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php foreach($products as $product): ?>
                <div class="col">
                    <div class="card product-card h-100">
                        <div class="position-relative">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($product['title']); ?>">
                            <?php if($product['video_url']): ?>
                                <button class="btn btn-play-video" data-video="<?php echo htmlspecialchars($product['video_url']); ?>" 
                                        data-type="<?php echo htmlspecialchars($product['video_type']); ?>">
                                    <i class="bi bi-play-circle-fill"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                            <p class="card-text text-muted small"><?php echo substr(htmlspecialchars($product['description']), 0, 80); ?>...</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0">$<?php echo number_format($product['price'], 2); ?></span>
                                <button class="btn btn-primary btn-sm add-to-cart" data-id="<?php echo $product['id']; ?>">
                                    <i class="bi bi-cart-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Video Modal -->
    <div class="modal fade" id="videoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="video-container" class="ratio ratio-16x9"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Widget -->
    <div id="chat-widget" class="chat-widget">
        <button id="chat-toggle" class="btn btn-primary rounded-circle">
            <i class="bi bi-chat-dots-fill"></i>
        </button>
        <div id="chat-box" class="chat-box d-none">
            <div class="chat-header bg-primary text-white p-2">
                <span>Live Chat</span>
                <button class="btn btn-sm text-white" id="chat-close"><i class="bi bi-x-lg"></i></button>
            </div>
            <div id="chat-messages" class="chat-messages"></div>
            <div class="chat-input">
                <input type="text" id="message-input" class="form-control" placeholder="Type a message...">
                <button id="send-message" class="btn btn-primary"><i class="bi bi-send"></i></button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">© 2025 VideoCommerce. Powered by Shopify Integration.</p>
        </div>
    </footer>

    <!-- Bootstrap 5.0.2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Socket.io Client -->
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <!-- Custom Scripts -->
    <script src="<?php echo $base_url; ?>assets/js/video-player.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/chat.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/cart.js"></script>
</body>
</html>
