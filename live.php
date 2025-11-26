<?php
/**
 * Live Streaming Page with WebRTC
 * Supports live video commerce streaming and chat
 */

session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'Live Stream - VideoCommerce';
$roomId = isset($_GET['room']) ? htmlspecialchars($_GET['room']) : 'default';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .video-container { background: #000; border-radius: 10px; overflow: hidden; }
        .local-video { width: 200px; position: absolute; bottom: 20px; right: 20px; border-radius: 8px; border: 2px solid #fff; }
        .remote-video { width: 100%; height: 100%; object-fit: cover; }
        .stream-controls { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); }
        .product-sidebar { max-height: 70vh; overflow-y: auto; }
        .live-badge { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
    </style>
</head>
<body class="bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-play-circle-fill"></i> VideoCommerce
            </a>
            <span class="badge bg-danger live-badge"><i class="bi bi-broadcast"></i> LIVE</span>
        </div>
    </nav>

    <div class="container-fluid py-3">
        <div class="row">
            <!-- Main Video Area -->
            <div class="col-lg-8">
                <div class="video-container position-relative" style="height: 70vh;">
                    <video id="remoteVideo" class="remote-video" autoplay playsinline></video>
                    <video id="localVideo" class="local-video" autoplay playsinline muted></video>
                    
                    <div class="stream-controls">
                        <button id="toggleVideo" class="btn btn-light rounded-circle me-2">
                            <i class="bi bi-camera-video-fill"></i>
                        </button>
                        <button id="toggleAudio" class="btn btn-light rounded-circle me-2">
                            <i class="bi bi-mic-fill"></i>
                        </button>
                        <button id="shareScreen" class="btn btn-info rounded-circle me-2">
                            <i class="bi bi-display"></i>
                        </button>
                        <button id="endCall" class="btn btn-danger rounded-circle">
                            <i class="bi bi-telephone-x-fill"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Start/Join Stream Buttons -->
                <div class="d-flex gap-2 mt-3">
                    <button id="startStream" class="btn btn-success">
                        <i class="bi bi-broadcast"></i> Start Streaming
                    </button>
                    <button id="joinStream" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Join Stream
                    </button>
                    <input type="text" id="roomInput" class="form-control w-25" 
                           placeholder="Room ID" value="<?php echo $roomId; ?>">
                </div>
            </div>

            <!-- Sidebar: Products & Chat -->
            <div class="col-lg-4">
                <!-- Featured Products -->
                <div class="card bg-secondary text-white mb-3">
                    <div class="card-header">
                        <i class="bi bi-bag-fill"></i> Featured Products
                    </div>
                    <div class="card-body product-sidebar">
                        <div id="featured-products">
                            <!-- Products loaded via AJAX -->
                        </div>
                    </div>
                </div>

                <!-- Live Chat -->
                <div class="card bg-secondary text-white">
                    <div class="card-header">
                        <i class="bi bi-chat-dots-fill"></i> Live Chat
                    </div>
                    <div class="card-body p-0">
                        <div id="chat-messages" style="height: 250px; overflow-y: auto; padding: 10px;"></div>
                        <div class="input-group p-2">
                            <input type="text" id="chat-input" class="form-control" placeholder="Say something...">
                            <button id="send-chat" class="btn btn-primary">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script src="assets/js/webrtc.js"></script>
    <script src="assets/js/live-chat.js"></script>
    <script>
        // Initialize WebRTC configuration
        const config = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        };
        
        // Socket.io connection for signaling
        const socket = io('<?php echo SOCKET_SERVER_URL; ?>');
        const roomId = document.getElementById('roomInput').value;
        
        // Join room on page load
        socket.emit('join-room', roomId);
    </script>
</body>
</html>
