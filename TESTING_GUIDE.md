# Video Commerce Platform - Testing Guide

## Phase 3 Completion: Demo Testing Setup

This guide explains how to test the video commerce platform with demo data and role-based access.

## Demo Credentials

### Admin Account
- **Email:** admin@videocommerce.local
- **Password:** demo123
- **Role:** Admin (Full platform access)

### Influencer Accounts
- **Email:** influencer1@demo.local
- **Password:** demo123
- **Email:** influencer2@demo.local
- **Password:** demo123
- **Email:** influencer3@demo.local
- **Password:** demo123

### Customer Accounts
- **Email:** customer1@demo.local
- **Password:** demo123
- **Email:** customer2@demo.local
- **Password:** demo123

## Files Created

### Core Files
- `auth/login.php` - Login/signup page with role selection
- `auth/logout.php` - Session destruction
- `auth/session.php` - Session management utilities
- `dashboard.php` - Public buyer dashboard

### Influencer Features
- `influencer/dashboard.php` - Creator management interface
- `influencer/upload-product.php` - Product upload form
- `influencer/create-video.php` - Video creation form

### Admin Features
- `admin/dashboard.php` - Platform statistics and management

## Features Implemented

### Authentication System
✅ Login with email/password
✅ Signup with role selection (Customer/Influencer)
✅ Password hashing with bcrypt
✅ Session-based authentication
✅ Role-based redirects
✅ Logout functionality
✅ Session timeout (30 minutes)

### Influencer Features
✅ Dashboard with statistics
✅ Product upload form
✅ Video creation form
✅ View own products and videos
✅ Link videos from YouTube, Instagram, TikTok, Facebook, Vimeo
✅ Use own products or platform products for videos
✅ Draft/Published status for videos

### Admin Features
✅ Full platform statistics
✅ View all users
✅ View all products
✅ View all videos
✅ View chat statistics
✅ Admin navigation panel

### Customer Features
✅ View all products
✅ View all videos
✅ Browse featured products
✅ Search products
✅ Filter by category

## Testing Steps

### Step 1: Test Login/Signup
1. Open `/auth/login.php?mode=signup`
2. Fill in form with:
   - Name: Your Name
   - Email: test@example.local
   - Password: testpass123
   - Role: Select 'Influencer'
3. Submit and verify redirect to influencer dashboard

### Step 2: Test Login as Demo User
1. Open `/auth/login.php?mode=login`
2. Enter demo credentials:
   - Email: influencer1@demo.local
   - Password: demo123
3. Verify redirect to `/influencer/dashboard.php`
4. View statistics: Should show products and videos

### Step 3: Test Product Upload
1. As influencer, click 'Upload Product' button
2. Redirects to `/influencer/upload-product.php`
3. Fill form:
   - Name: Test Product
   - Description: Test product description
   - Price: 29.99
   - Category: Electronics
4. Submit and verify success message

### Step 4: Test Video Creation
1. As influencer, click 'Create Video' button
2. Redirects to `/influencer/create-video.php`
3. Fill form:
   - Platform: YouTube
   - URL: https://www.youtube.com/watch?v=example
   - Product: Select from your products
   - Status: Published
4. Submit and verify success message

### Step 5: Test Admin Dashboard
1. Logout (click Logout button)
2. Login with admin credentials:
   - Email: admin@videocommerce.local
   - Password: demo123
3. Redirects to `/admin/dashboard.php`
4. View statistics:
   - Customer count
   - Influencer count
   - Product count
   - Video count
5. Browse tables:
   - Recent Users
   - Recent Products
   - Recent Videos

### Step 6: Test Customer Dashboard
1. Logout
2. Login with customer credentials:
   - Email: customer1@demo.local
   - Password: demo123
3. Redirects to `/dashboard.php`
4. Browse:
   - Featured Products (max 6)
   - Trending Videos (max 6)
   - Search functionality
   - Category filters

### Step 7: Test Role-Based Access
1. As customer, try to access `/influencer/dashboard.php`
2. Should redirect to login page
3. As influencer, try to access `/admin/dashboard.php`
4. Should show 'Access Denied'

## Demo Data Included

### Pre-loaded Products (9 total)
- Headphones ($79.99)
- Smartwatch ($199.99)
- USB-C Charger ($19.99)
- Power Bank ($39.99)
- Bluetooth Speaker ($59.99)
- Coffee Machine ($89.99)
- Skincare Set ($49.99)
- Tech Bundle ($299.99)
- Water Bottle ($24.99)

### Pre-loaded Videos (5 total)
- Videos linked to demo products
- Mix of platform and influencer videos
- Published and draft status

### Pre-loaded Users (8 total)
- 1 Admin
- 3 Influencers
- 4 Customers

## Security Features

✅ Password hashing with bcrypt
✅ SQL injection prevention (prepared statements)
✅ Session-based authentication
✅ Role-based access control
✅ Session timeout (30 minutes)
✅ CSRF protection ready (headers available)
✅ XSS prevention (htmlspecialchars)
✅ Secure session cookies

## Next Steps After Testing

1. **Payment Integration** - Add Stripe or similar
2. **Shopping Cart** - Add product cart functionality
3. **Order Management** - Track orders
4. **Video Processing** - Upload custom videos
5. **Live Streaming** - Enable WebRTC live streams
6. **Chat System** - Real-time messaging
7. **Notifications** - User notifications
8. **Analytics** - Advanced statistics

## Troubleshooting

### Issue: Redirect loop on login
- Check database connection in `/config/database.php`
- Ensure demo data is loaded in database
- Clear browser cookies/cache

### Issue: "Access Denied" error
- Verify user role in database
- Check session.php for role validation

### Issue: Products/videos not showing
- Verify database has demo data
- Check SQL queries in dashboard files
- Ensure database credentials are correct

## Database Requirements

Make sure to load the seed data:
```bash
mysql -u user -p database < database/seed-demo-data.sql
```

Required tables:
- `users` - User accounts with roles
- `products` - Product catalog
- `videos` - Video library
- `chat_messages` - Chat messages (optional)
- `chat_rooms` - Chat rooms (optional)
