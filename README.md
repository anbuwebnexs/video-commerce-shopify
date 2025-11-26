# Video Commerce Platform with Shopify Integration

A complete video commerce solution built with PHP, Bootstrap 5.0.2, WebRTC for live streaming, Socket.io for real-time chat, and Shopify app integration. Supports playing videos from social media platforms.

## Features

- **Shopify Integration**: Connect your Shopify store via OAuth to sync products
- **Live Video Streaming**: WebRTC-based peer-to-peer live streaming
- **Real-time Chat**: Socket.io powered live chat during streams
- **Social Media Videos**: Embed videos from YouTube, Facebook, Instagram, TikTok, Vimeo
- **Responsive Design**: Bootstrap 5.0.2 mobile-first UI
- **Product Display**: Video-enabled product cards with live demos

## Tech Stack

| Component | Technology |
|-----------|------------|
| Frontend | PHP, Bootstrap 5.0.2, JavaScript |
| Backend | PHP 7.4+, Node.js (Socket.io) |
| Video | WebRTC, HTML5 Video |
| Real-time | Socket.io 4.x |
| Database | MySQL/MariaDB |
| Integration | Shopify API |

## Project Structure

```
video-commerce-shopify/
|-- index.php              # Main entry point
|-- live.php               # Live streaming page
|-- config/
|   |-- database.php       # DB & API configuration
|-- shopify/
|   |-- connect.php        # Shopify OAuth initiation
|   |-- callback.php       # OAuth callback handler
|-- assets/
|   |-- js/
|       |-- webrtc.js      # WebRTC video streaming
|       |-- video-player.js # Social media embeds
|-- node/
|   |-- server.js          # Socket.io server
|-- database/
|   |-- schema.sql         # Database schema
```

## Installation

### 1. Clone the repository
```bash
git clone https://github.com/anbuwebnexs/video-commerce-shopify.git
cd video-commerce-shopify
```

### 2. Configure Database
```bash
mysql -u root -p < database/schema.sql
```

### 3. Update Configuration
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'video_commerce');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// Shopify API
define('SHOPIFY_API_KEY', 'your_api_key');
define('SHOPIFY_API_SECRET', 'your_secret');
```

### 4. Install Node.js dependencies
```bash
cd node
npm init -y
npm install express socket.io cors
node server.js
```

### 5. Start PHP server
```bash
php -S localhost:8000
```

## Shopify App Setup

1. Create a Shopify Partner account
2. Create a new app in Partner Dashboard
3. Set redirect URL to: `https://yourdomain.com/shopify/callback.php`
4. Copy API Key and Secret to config file
5. Required scopes: `read_products`, `read_inventory`, `read_orders`

## Social Media Video Support

Supported platforms:
- YouTube (embed/watch URLs)
- Facebook Video
- Instagram Reels/Posts
- TikTok Videos
- Vimeo
- Native MP4/WebM

## WebRTC Live Streaming

Features:
- Start/Join live streams
- Video/Audio toggle
- Screen sharing
- Room-based streaming

## License

MIT License - See LICENSE file

## Author

Built with PHP, Bootstrap, WebRTC & Socket.io
