<?php
// Upload Product - Influencer can add their own products
require_once '../config/database.php';
require_once '../auth/session.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'influencer') {
    header('Location: /auth/login.php?mode=login');
    exit;
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category = $_POST['category'] ?? '';
    
    if (empty($name) || empty($description) || empty($price)) {
        $error_message = 'All fields are required';
    } else if (!is_numeric($price) || $price < 0) {
        $error_message = 'Price must be a valid number';
    } else {
        $insert_query = "INSERT INTO products (name, description, price, category, seller_id, created_at) 
                        VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $db->prepare($insert_query);
        try {
            $stmt->execute([$name, $description, $price, $category, $_SESSION['user_id']]);
            $success_message = 'Product uploaded successfully!';
        } catch (PDOException $e) {
            $error_message = 'Error uploading product: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Product</title>
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
            max-width: 600px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="form-title">Upload New Product</div>
            
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
                    <label for="name">Product Name *</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter product name" required>
                </div>
                
                <div class="form-group mb-4">
                    <label for="description">Description *</label>
                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe your product in detail" required></textarea>
                </div>
                
                <div class="form-group mb-4">
                    <label for="price">Price (USD) *</label>
                    <input type="number" class="form-control" id="price" name="price" placeholder="0.00" step="0.01" min="0" required>
                </div>
                
                <div class="form-group mb-4">
                    <label for="category">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="electronics">Electronics</option>
                        <option value="fashion">Fashion</option>
                        <option value="beauty">Beauty</option>
                        <option value="home">Home & Garden</option>
                        <option value="sports">Sports & Outdoors</option>
                        <option value="books">Books</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-submit">Upload Product</button>
            </form>
            
            <div class="back-link">
                <a href="/influencer/dashboard.php">← Back to Dashboard</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
