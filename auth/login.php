<?php
/**
 * Login & Signup Page
 * Support for Admin, Influencer, and Regular Users
 */

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';

// Check if database connection exists
if (!isset($conn) || $conn === null) {
    die('Error: Database connection failed. Please check config/database.php');
}

// Define base URL for all links
$base_url = 'http://localhost/videocom/video-commerce-shopify/';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . 'dashboard.php');
    exit;
}

$error = '';
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'login'; // login or signup

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($mode === 'signup') {
        handleSignup($conn);
    } else {
        handleLogin($conn);
    }
}

function handleSignup($conn) {
    global $error, $base_url;
    
    $email = $_POST['email'] ?? '';
    $name = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'buyer';
    
    if (empty($email) || empty($name) || empty($password)) {
        $error = 'All fields are required';
        return;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
        return;
    }
    
    // Check if email exists
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = 'Email already registered';
        return;
    }
    
    // Create new user
    try {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare(
            'INSERT INTO users (email, name, password_hash, role, avatar, created_at) VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            $email,
            $name,
            $passwordHash,
            $role,
            'https://via.placeholder.com/150?text=' . urlencode(substr($name, 0, 1))
        ]);
        
        $_SESSION['success'] = 'Account created! Please login.';
        header('Location: ?mode=login');
        exit;
    } catch (Exception $e) {
        $error = 'Error creating account';
    }
}

function handleLogin($conn) {
    global $error, $base_url;
    
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password required';
        return;
    }
    
    try {
        $stmt = $conn->prepare('SELECT id, password_hash, role, name FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: ' . $base_url . 'admin/dashboard.php');
            } elseif ($user['role'] === 'influencer') {
                header('Location: ' . $base_url . 'influencer/dashboard.php');
            } else {
                header('Location: ' . $base_url . 'dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    } catch (Exception $e) {
        $error = 'Login error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $mode === 'signup' ? 'Sign Up' : 'Login'; ?> - VideoCommerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .auth-container { min-height: 100vh; display: flex; align-items: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .auth-card { background: white; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="auth-card p-4">
                        <h1 class="text-center mb-4"><i class="bi bi-play-circle-fill"></i> VideoCommerce</h1>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($mode === 'signup'): ?>
                        <!-- SIGNUP FORM -->
                        <h3 class="text-center mb-4">Create Account</h3>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Account Type</label>
                                <select name="role" class="form-control">
                                    <option value="buyer">Customer</option>
                                    <option value="influencer">Influencer (Create & Sell Products)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Create Account</button>
                        </form>
                        <p class="text-center">Already have an account? <a href="?mode=login">Login</a></p>
                        <?php else: ?>
                        <!-- LOGIN FORM -->
                        <h3 class="text-center mb-4">Login</h3>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required placeholder="admin@videocommerce.local">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required placeholder="demo123">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                        </form>
                        <p class="text-center">New user? <a href="?mode=signup">Create Account</a></p>
                        
                        <!-- DEMO CREDENTIALS -->
                        <hr>
                        <div class="alert alert-info small">
                            <strong>Demo Credentials:</strong><br>
                            👨‍💼 Admin: admin@videocommerce.local / demo123<br>
                            👥 Influencer: influencer1@demo.local / demo123<br>
                            🛍️ Customer: customer1@demo.local / demo123
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
