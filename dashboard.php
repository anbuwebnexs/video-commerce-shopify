<?php
// Dashboard - Buyer/Customer view showing all products and videos
require_once 'config/database.php';
require_once 'auth/session.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php?mode=login');
    exit;
}

// Get all products with videos
$query = "SELECT p.*, u.name as seller_name, COUNT(v.id) as video_count 
          FROM products p 
          LEFT JOIN users u ON p.seller_id = u.id 
          LEFT JOIN videos v ON p.id = v.product_id 
          GROUP BY p.id 
          ORDER BY p.created_at DESC";

$products = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Get recommended videos
$video_query = "SELECT v.*, p.name as product_name, u.name as creator_name 
               FROM videos v 
               JOIN products p ON v.product_id = p.id 
               JOIN users u ON v.creator_id = u.id 
               WHERE v.status = 'published' 
               ORDER BY v.created_at DESC 
               LIMIT 12";

$videos = $db->query($video_query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Commerce - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7c3aed;
            --secondary-color: #ec4899;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .search-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-name {
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .product-price {
            font-size: 1.5rem;
            color: var(--secondary-color);
            font-weight: bold;
            margin: 10px 0;
        }
        
        .seller-info {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 10px;
        }
        
        .video-badge {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 10px;
        }
        
        .video-section {
            margin-top: 50px;
        }
        
        .section-title {
            color: white;
            font-size: 2rem;
            font-weight: bold;
            margin: 40px 0 20px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .video-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .video-card:hover {
            transform: scale(1.02);
        }
        
        .video-thumbnail {
            width: 100%;
            height: 200px;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
        
        .video-title {
            padding: 15px;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .header {
            color: white;
            padding: 40px 0;
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header h1 {
            font-size: 3rem;
            font-weight: bold;
        }
        
        .header p {
            font-size: 1.2rem;
            margin-top: 10px;
        }
        
        .user-greeting {
            color: white;
            margin-right: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">🎥 VideoCommerce</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="user-greeting">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                    </li>
                    <?php if ($_SESSION['user_role'] === 'influencer'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/influencer/dashboard.php">My Dashboard</a>
                    </li>
                    <?php elseif ($_SESSION['user_role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/dashboard.php">Admin Panel</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="header">
        <div class="container-fluid">
            <h1>Discover Amazing Products & Videos</h1>
            <p>Shop products featured in exclusive video content from our creators</p>
        </div>
    </div>

    <!-- Search Section -->
    <div class="container-fluid">
        <div class="search-section">
            <div class="row">
                <div class="col-md-8">
                    <input type="text" class="form-control form-control-lg" id="searchInput" placeholder="Search products or videos..." style="border-radius: 10px;">
                </div>
                <div class="col-md-4">
                    <select class="form-select form-select-lg" style="border-radius: 10px;">
                        <option>All Categories</option>
                        <option>Electronics</option>
                        <option>Fashion</option>
                        <option>Beauty</option>
                        <option>Home</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="section-title">Featured Products</div>
        <div class="row g-4 mb-5">
            <?php foreach (array_slice($products, 0, 6) as $product): ?>
            <div class="col-md-6 col-lg-4">
                <div class="product-card">
                    <div class="product-image">
                        <?php 
                        $icons = ['🎧', '⌚', '🔌', '🔋', '📢', '☕', '💅', '💻', '🚰'];
                        echo $icons[array_rand($icons)];
                        ?>
                    </div>
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="seller-info">By <?php echo htmlspecialchars($product['seller_name'] ?: 'Unknown'); ?></div>
                        <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                        <p style="color: #666; font-size: 0.9rem;"><?php echo htmlspecialchars(substr($product['description'], 0, 80) . '...'); ?></p>
                        <?php if ($product['video_count'] > 0): ?>
                        <span class="video-badge">🎬 <?php echo $product['video_count']; ?> Video<?php echo $product['video_count'] > 1 ? 's' : ''; ?></span>
                        <?php endif; ?>
                        <button class="btn btn-primary mt-3 w-100" style="background: var(--primary-color); border: none; border-radius: 10px;">View Product</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Videos Section -->
        <div class="video-section">
            <div class="section-title">Trending Videos</div>
            <div class="row g-4">
                <?php foreach (array_slice($videos, 0, 6) as $video): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="video-card" style="cursor: pointer;">
                        <div class="video-thumbnail">
                            🎬
                        </div>
                        <div class="video-title">
                            <div style="font-size: 0.9rem;"><?php echo htmlspecialchars($video['product_name']); ?></div>
                            <small style="color: #999;">By <?php echo htmlspecialchars($video['creator_name']); ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background: rgba(0,0,0,0.2); color: white; padding: 30px; margin-top: 50px; text-align: center;">
        <p>&copy; 2024 VideoCommerce Platform. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple search functionality
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            console.log('Search:', e.target.value);
            // Add search logic here
        });
    </script>
</body>
</html>
