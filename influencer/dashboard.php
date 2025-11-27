<?php
// Influencer Dashboard - Content creator management
require_once '../config/database.php';
require_once '../auth/session.php';

// Ensure user is logged in and is an influencer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'influencer') {
    header('Location: /auth/login.php?mode=login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get influencer's products
$products_query = "SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($products_query);
$stmt->execute([$user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get influencer's videos
$videos_query = "SELECT v.*, p.name as product_name FROM videos v 
                 JOIN products p ON v.product_id = p.id 
                 WHERE v.creator_id = ? 
                 ORDER BY v.created_at DESC";
$stmt = $db->prepare($videos_query);
$stmt->execute([$user_id]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats_query = "SELECT 
                 COUNT(DISTINCT p.id) as product_count,
                 COUNT(DISTINCT v.id) as video_count,
                 COALESCE(SUM(p.price), 0) as total_product_value
               FROM products p
               LEFT JOIN videos v ON p.id = v.product_id
               WHERE p.seller_id = ?";
$stmt = $db->prepare($stats_query);
$stmt->execute([$user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Influencer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7c3aed;
            --secondary-color: #ec4899;
        }
        
        body {
            background: #f8f9fa;
            padding: 20px 0;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
        }
        
        .nav-link:hover {
            color: white !important;
        }
        
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
        }
        
        .section-header {
            color: var(--primary-color);
            font-weight: bold;
            margin: 30px 0 20px 0;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }
        
        .product-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .product-icon {
            font-size: 2rem;
            margin-right: 15px;
        }
        
        .product-details {
            flex: 1;
        }
        
        .product-name {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .product-price {
            color: var(--secondary-color);
            font-weight: bold;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-buttons button {
            padding: 5px 15px;
            font-size: 0.9rem;
        }
        
        .video-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .video-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .status-published {
            background: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background: #fff3cd;
            color: #856404;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-lg">
            <a class="navbar-brand" href="/">🎥 VideoCommerce Creator</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span style="color: white; margin-right: 20px;"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard.php">Public Store</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-lg mt-4">
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stat-number"><?php echo $stats['product_count']; ?></div>
                    <div class="stat-label">Your Products</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stat-number"><?php echo $stats['video_count']; ?></div>
                    <div class="stat-label">Created Videos</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stat-number">$<?php echo number_format($stats['total_product_value'], 2); ?></div>
                    <div class="stat-label">Product Value</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div style="margin-bottom: 30px;">
            <button class="btn btn-primary btn-lg me-2">📷 Upload Product</button>
            <button class="btn btn-secondary btn-lg">🎥 Create Video</button>
        </div>

        <!-- Products Section -->
        <div class="section-header">Your Products</div>
        <?php if (!empty($products)): ?>
            <div class="row mb-4">
                <?php foreach ($products as $product): ?>
                <div class="col-md-6 mb-3">
                    <div class="product-item">
                        <div style="display: flex; align-items: center; flex: 1;">
                            <div class="product-icon">📋</div>
                            <div class="product-details">
                                <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div style="font-size: 0.85rem; color: #666;"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></div>
                                <div class="product-price mt-2">$<?php echo number_format($product['price'], 2); ?></div>
                            </div>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-outline-primary">Edit</button>
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <p>No products yet. Upload your first product to get started!</p>
                <button class="btn btn-primary mt-3">Upload Product</button>
            </div>
        <?php endif; ?>

        <!-- Videos Section -->
        <div class="section-header mt-5">Your Videos</div>
        <?php if (!empty($videos)): ?>
            <?php foreach ($videos as $video): ?>
            <div class="video-item">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <div style="font-weight: bold; color: var(--primary-color);">Product: <?php echo htmlspecialchars($video['product_name']); ?></div>
                        <div style="color: #666; font-size: 0.9rem; margin-top: 5px;"><?php echo htmlspecialchars($video['video_url']); ?></div>
                        <div class="video-status status-<?php echo $video['status']; ?>">
                            <?php echo ucfirst($video['status']); ?>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-primary">Edit</button>
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🎬</div>
                <p>No videos created yet. Create a video to showcase your products!</p>
                <button class="btn btn-primary mt-3">Create Video</button>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add event listeners for buttons
        document.querySelectorAll('button').forEach(btn => {
            if (btn.textContent.includes('Upload Product')) {
                btn.addEventListener('click', () => window.location.href = '/influencer/upload-product.php');
            }
            if (btn.textContent.includes('Create Video')) {
                btn.addEventListener('click', () => window.location.href = '/influencer/create-video.php');
            }
        });
    </script>
</body>
</html>
