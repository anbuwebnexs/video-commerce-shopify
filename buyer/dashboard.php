<?php
require_once '../auth/session.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - Video Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">Video Commerce</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="./dashboard.php">My Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="./orders.php">My Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h2>Buyer Dashboard</h2>
                <p>Welcome back, User #<?php echo $user_id; ?></p>
                
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <p>Your recent orders will appear here.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="../index.php">Browse Products</a></li>
                            <li><a href="../live.php">Live Streams</a></li>
                            <li><a href="./orders.php">My Orders</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
