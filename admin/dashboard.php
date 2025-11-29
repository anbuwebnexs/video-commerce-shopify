<?php
// Admin Dashboard - Full platform management

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

// Check if database connection exists
if (!isset($conn) || $conn === null) {
    die('Error: Database connection failed. Please check config/database.php');
}

// Define base URL
$base_url = SITE_URL ?? 'http://localhost/videocom/video-commerce-shopify/';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ' . $base_url . 'auth/login.php?mode=login');
    exit;
}

// Get platform statistics
$stats_query = "SELECT 
 (SELECT COUNT(*) FROM users WHERE role = 'customer') as customer_count,
 (SELECT COUNT(*) FROM users WHERE role = 'influencer') as influencer_count,
 (SELECT COUNT(*) FROM products) as product_count,
 (SELECT COUNT(*) FROM videos) as video_count,
 (SELECT COUNT(*) FROM chat_messages) as chat_count";

try {
    $stats = $conn->query($stats_query)->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $stats = ['customer_count' => 0, 'influencer_count' => 0, 'product_count' => 0, 'video_count' => 0, 'chat_count' => 0];
}

// Get recent users
$users_query = "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 10";
try {
    $recent_users = $conn->query($users_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recent_users = [];
}

// Get all products for admin
$products_query = "SELECT p.*, u.name as seller_name FROM products p 
 LEFT JOIN users u ON p.seller_id = u.id 
 ORDER BY p.created_at DESC LIMIT 15";
try {
    $products = $conn->query($products_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
}

// Get all videos
$videos_query = "SELECT v.*, p.name as product_name, u.name as creator_name 
 FROM videos v 
 JOIN products p ON v.product_id = p.id 
 JOIN users u ON v.creator_id = u.id 
 ORDER BY v.created_at DESC LIMIT 10";
try {
    $videos = $conn->query($videos_query)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $videos = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7c3aed;
            --secondary-color: #ec4899;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }
        
        body { background: #f3f4f6; }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand { color: white !important; font-weight: bold; font-size: 1.5rem; }
        .nav-link { color: rgba(255,255,255,0.8) !important; }
        .nav-link:hover { color: white !important; }
        
        .sidebar {
            background: white;
            min-height: calc(100vh - 60px);
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .sidebar .nav-link {
            color: #666 !important;
            border-radius: 10px;
            margin-bottom: 10px;
            padding: 10px 15px;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: var(--primary-color);
            color: white !important;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 5px solid var(--primary-color);
        }
        
        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.danger { border-left-color: var(--danger-color); }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stat-card.success .stat-number { color: var(--success-color); }
        .stat-label { color: #999; font-size: 0.95rem; margin-top: 10px; }
        
        .content { padding: 20px; }
        .section-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: bold;
            margin: 30px 0 20px 0;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .table { margin-bottom: 0; }
        .table thead { background: #f9fafb; }
        .table th {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-influencer { background: #dbeafe; color: #1e40af; }
        .badge-customer { background: #dcfce7; color: #166534; }
        .badge-published { background: #d1fae5; color: #065f46; }
        .badge-draft { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo $base_url; ?>">📊 Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span style="color: white; margin-right: 20px;">Admin: <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>dashboard.php">Public View</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar">
                <nav class="nav flex-column">
                    <a class="nav-link active" href="#">📊 Dashboard</a>
                    <a class="nav-link" href="#">👥 Users Management</a>
                    <a class="nav-link" href="#">📦 Products</a>
                    <a class="nav-link" href="#">🎬 Videos</a>
                    <a class="nav-link" href="#">📊 Reports</a>
                    <a class="nav-link" href="#">⚙️ Settings</a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 content">
                <h1 style="color: var(--primary-color); margin-bottom: 30px;">Admin Dashboard</h1>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['customer_count'] ?? 0; ?></div>
                            <div class="stat-label">Total Customers</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card success">
                            <div class="stat-number"><?php echo $stats['influencer_count'] ?? 0; ?></div>
                            <div class="stat-label">Active Influencers</div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="stat-card warning">
                            <div class="stat-number"><?php echo $stats['product_count'] ?? 0; ?></div>
                            <div class="stat-label">Total Products</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card danger">
                            <div class="stat-number"><?php echo $stats['video_count'] ?? 0; ?></div>
                            <div class="stat-label">Total Videos</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="section-title">👥 Recent Users</div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Recent Products -->
                <div class="section-title">📦 Recent Products</div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Seller</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['seller_name'] ?: 'Platform'); ?></td>
                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                <td><span style="color: #10b981;">✅ Active</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Recent Videos -->
                <div class="section-title">🎬 Recent Videos</div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Creator</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($videos as $video): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($video['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($video['creator_name']); ?></td>
                                <td><?php echo ucfirst($video['video_type'] ?? 'Unknown'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $video['status'] ?? 'draft'; ?>">
                                        <?php echo ucfirst($video['status'] ?? 'draft'); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
