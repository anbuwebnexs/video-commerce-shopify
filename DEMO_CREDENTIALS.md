# Video Commerce Platform - Demo Credentials

This document contains demo credentials for testing the Video Commerce Platform locally.

## Base URL
```
http://localhost/videocom/video-commerce-shopify/
```

## Database Information

**Database Name:** video_commerce  
**Database Host:** localhost  
**Database User:** root  
**Database Password:** (empty by default)  

> Note: Update database credentials in `config/database.php` according to your local MySQL setup.

## Admin Account

**Role:** Administrator  
**Email:** admin@videocommerce.local  
**Password:** demo123  

### Admin Permissions:
- Manage all users
- View analytics
- Manage products and videos
- Moderate content
- System configuration

### Admin Dashboard:
Access at: `http://localhost/videocom/video-commerce-shopify/admin/dashboard.php`

---

## Influencer Account

**Role:** Content Creator / Influencer  
**Email:** influencer1@demo.local  
**Password:** demo123  

### Influencer Permissions:
- Create and upload videos
- Add products
- View analytics for their content
- Interact with customers
- Manage live streams

### Influencer Dashboard:
Access at: `http://localhost/videocom/video-commerce-shopify/influencer/dashboard.php`

---

## Customer Account

**Role:** Buyer / Customer  
**Email:** customer1@demo.local  
**Password:** demo123  

### Customer Permissions:
- Browse products
- Watch videos
- Add items to cart
- Complete purchases
- Participate in live shopping
- Chat with sellers/influencers

### Customer Dashboard:
Access at: `http://localhost/videocom/video-commerce-shopify/dashboard.php`

---

## Creating Additional Test Accounts

You can create additional test accounts by:

1. Navigate to: `http://localhost/videocom/video-commerce-shopify/auth/login.php?mode=signup`
2. Fill in the registration form
3. Select your account type (Customer or Influencer)
4. Click "Create Account"
5. Login with the new credentials

---

## Main Pages

| Page | URL | Description |
|------|-----|-------------|
| Home | `http://localhost/videocom/video-commerce-shopify/` | Main landing page |
| Products | `http://localhost/videocom/video-commerce-shopify/products.php` | Browse all products |
| Live Stream | `http://localhost/videocom/video-commerce-shopify/live.php` | Live streaming page |
| Dashboard | `http://localhost/videocom/video-commerce-shopify/dashboard.php` | User dashboard |
| Login | `http://localhost/videocom/video-commerce-shopify/auth/login.php` | Login/Signup page |
| Cart | `http://localhost/videocom/video-commerce-shopify/cart.php` | Shopping cart |

---

## Features to Test

### Video Commerce Features
- [x] User Authentication (Login/Signup)
- [x] Product Browsing
- [x] Video Display
- [x] Shopping Cart
- [x] Live Streaming
- [x] Live Chat
- [x] Shopify Integration Ready
- [x] Bootstrap 5.0.2 Responsive Design
- [x] WebRTC Video Streaming
- [x] Socket.io Chat System

### Test Workflows

#### Workflow 1: Customer Shopping
1. Login as customer1@demo.local
2. Browse products on Products page
3. Add items to cart
4. View cart
5. Proceed to checkout

#### Workflow 2: Live Shopping
1. Login as customer1@demo.local  
2. Go to Live Stream page
3. Join an active stream (if available)
4. Chat with the streamer
5. Watch product demos
6. Add featured products to cart

#### Workflow 3: Influencer Broadcasting
1. Login as influencer1@demo.local
2. Go to Dashboard
3. Start a live stream
4. Add products to stream
5. Interact with viewers via chat
6. End stream

#### Workflow 4: Admin Management
1. Login as admin@videocommerce.local
2. Access Admin Panel
3. View all users
4. Manage products
5. View analytics
6. Moderate content

---

## Troubleshooting

### Session Errors
If you encounter session errors:
- Clear browser cookies
- Check that `session_status() === PHP_SESSION_NONE` check is in place
- Verify database connection

### Database Connection Errors
If database connection fails:
- Check `config/database.php` settings
- Verify MySQL is running
- Confirm database and user exist
- Check database credentials

### Link/Navigation Issues
If links are broken:
- Verify base URL is set to `http://localhost/videocom/video-commerce-shopify/`
- Clear browser cache
- Check file paths in includes

---

## Development Notes

- All PHP files have been updated with proper session handling
- Base URL is centrally defined in each file
- Database connection is validated before use
- Error handling has been improved
- Session variables are properly named

---

## Support

For issues or questions, please:
1. Check the README.md
2. Review error messages in the browser console
3. Check PHP error logs
4. Verify database connection

---

**Last Updated:** 2025  
**Version:** 1.0.0
