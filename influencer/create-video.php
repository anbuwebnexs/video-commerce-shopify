<?php
// Create Video - Influencer can create/add videos for their products
require_once '../config/database.php';
require_once '../auth/session.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'influencer') {
    header('Location: /auth/login.php?mode=login');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Get influencer's products
$products_query = "SELECT id, name FROM products WHERE seller_id = ? ORDER BY name";
$stmt = $db->prepare($products_query);
$stmt->execute([$user_id]);
$influencer_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all platform products
$platform_products_query = "SELECT id, name FROM products ORDER BY name LIMIT 20";
$platform_products = $db->query($platform_products_query)->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? '';
    $video_url = $_POST['video_url'] ?? '';
    $video_type = $_POST['video_type'] ?? 'youtube';
    $status = $_POST['status'] ?? 'draft';
    
    if (empty($product_id) || empty($video_url)) {
        $error_message = 'Product and video URL are required';
    } else {
        // Validate product belongs to user or is platform product
        $product_check = "SELECT id FROM products WHERE id = ?";
        $stmt = $db->prepare($product_check);
        $stmt->execute([$product_id]);
        if ($stmt->rowCount() === 0) {
            $error_message = 'Invalid product selected';
        } else {
            $insert_query = "INSERT INTO videos (product_id, video_url, video_type, creator_id, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $db->prepare($insert_query);
            try {
                $stmt->execute([$product_id, $video_url, $video_type, $user_id, $status]);
                $success_message = 'Video created successfully!';
            } catch (PDOException $e) {
                $error_message = 'Error creating video: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Video</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7c3aed;
            --secondary-color: #ec4899;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 700px;
        }
        
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            padding: 40px;
        }
        
        .form-title {
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-group label {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            font-size: 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(124, 58, 237, 0.25);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            color: white;
            font-weight: bold;
            padding: 12px 30px;
            border-radius: 10px;
            width: 100%;
            font-size: 1rem;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);
            color: white;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }
        
        .info-box {
            background: #f0f7ff;
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-box strong {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="form-title">Create New Video</div>
            
            <div class="info-box">
                <strong>Tip:</strong> You can link videos from YouTube, Instagram, TikTok, Facebook, or Vimeo to showcase your products.
            </div>
            
            <?php if ($success_message): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <hr>
                <a href="/influencer/dashboard.php" class="btn btn-sm btn-success">Back to Dashboard</a>
            </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group mb-4">
                    <label for="video_type">Video Platform *</label>
                    <select class="form-select" id="video_type" name="video_type" required>
                        <option value="youtube">YouTube</option>
                        <option value="instagram">Instagram</option>
                        <option value="tiktok">TikTok</option>
                        <option value="facebook">Facebook</option>
                        <option value="vimeo">Vimeo</option>
                    </select>
                    <small class="text-muted">Select the platform where your video is hosted</small>
                </div>
                
                <div class="form-group mb-4">
                    <label for="video_url">Video URL *</label>
                    <input type="url" class="form-control" id="video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=..." required>
                    <small class="text-muted">Paste the full URL of your video</small>
                </div>
                
                <div class="form-group mb-4">
                    <label for="product_id">Select Product *</label>
                    <select class="form-select" id="product_id" name="product_id" required>
                        <option value="">-- Choose a product --</option>
                        
                        <?php if (!empty($influencer_products)): ?>
                        <optgroup label="Your Products">
                            <?php foreach ($influencer_products as $product): ?>
                            <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?> (Mine)</option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endif; ?>
                        
                        <optgroup label="Platform Products">
                            <?php foreach ($platform_products as $product): ?>
                            <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
                
                <div class="form-group mb-4">
                    <label for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="draft">Draft (Not visible yet)</option>
                        <option value="published">Published (Visible to all)</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-submit">Create Video</button>
            </form>
            
            <div class="back-link">
                <a href="/influencer/dashboard.php">← Back to Dashboard</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
