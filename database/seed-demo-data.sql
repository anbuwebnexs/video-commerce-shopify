-- Demo Data for Video Commerce Platform
-- Run this after schema.sql to populate test data

USE video_commerce;

-- Insert Demo Admin User
INSERT INTO users (email, password_hash, name, avatar, role, created_at) VALUES
('admin@videocommerce.local', '$2y$10$abcd1234efgh5678ijkl9012mnopqrstuv', 'Admin User', 'https://via.placeholder.com/150?text=Admin', 'admin', NOW());

-- Insert Demo Influencers
INSERT INTO users (email, password_hash, name, avatar, role, created_at) VALUES
('influencer1@demo.local', '$2y$10$demo1234hash5678ijkl9012mnopqrstuv', 'Sarah Johnson', 'https://via.placeholder.com/150?text=Sarah', 'influencer', NOW()),
('influencer2@demo.local', '$2y$10$demo1234hash5678ijkl9012mnopqrstuv', 'Alex Chen', 'https://via.placeholder.com/150?text=Alex', 'influencer', NOW()),
('influencer3@demo.local', '$2y$10$demo1234hash5678ijkl9012mnopqrstuv', 'Emma Williams', 'https://via.placeholder.com/150?text=Emma', 'influencer', NOW());

-- Insert Demo Regular Users
INSERT INTO users (email, password_hash, name, avatar, role, created_at) VALUES
('customer1@demo.local', '$2y$10$demo1234hash5678ijkl9012mnopqrstuv', 'John Doe', 'https://via.placeholder.com/150?text=John', 'buyer', NOW()),
('customer2@demo.local', '$2y$10$demo1234hash5678ijkl9012mnopqrstuv', 'Maria Garcia', 'https://via.placeholder.com/150?text=Maria', 'buyer', NOW());

-- Insert Demo Products (from Platform)
INSERT INTO products (title, description, price, compare_at_price, image, video_url, video_type, inventory_quantity, is_active, user_id) VALUES
('Premium Wireless Headphones', 'High-quality wireless headphones with noise cancellation', 129.99, 149.99, 'https://via.placeholder.com/300?text=Headphones', 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 50, true, NULL),
('Smart Watch Pro', 'Advanced fitness tracking and notifications', 199.99, 249.99, 'https://via.placeholder.com/300?text=SmartWatch', 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 30, true, NULL),
('USB-C Fast Charger', 'Fast charging for all USB-C devices', 39.99, 49.99, 'https://via.placeholder.com/300?text=Charger', NULL, 'native', 100, true, NULL),
('Portable Power Bank', '20000mAh portable battery with fast charging', 44.99, 59.99, 'https://via.placeholder.com/300?text=PowerBank', 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 45, true, NULL),
('Bluetooth Speaker', 'Waterproof portable speaker with 12hr battery', 79.99, 99.99, 'https://via.placeholder.com/300?text=Speaker', 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 35, true, NULL);

-- Insert Influencer Products
INSERT INTO products (title, description, price, image, video_url, video_type, inventory_quantity, is_active, user_id) VALUES
('Organic Coffee Blend', 'Premium organic coffee from Ethiopian farms', 14.99, 'https://via.placeholder.com/300?text=Coffee', 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 200, true, 2),
('Handmade Skincare Set', 'Natural skincare products by Sarah Johnson', 49.99, 'https://via.placeholder.com/300?text=Skincare', 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 20, true, 2),
('Tech Gadget Bundle', 'Collection of trending tech accessories', 89.99, 'https://via.placeholder.com/300?text=Bundle', NULL, 'native', 15, true, 3),
('Eco-Friendly Water Bottle', 'Sustainable and stylish water bottle', 34.99, 'https://via.placeholder.com/300?text=Bottle', 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 50, true, 4);

-- Insert Demo Videos
INSERT INTO videos (product_id, user_id, title, media_url, video_type, duration, views_count, status) VALUES
(1, 2, 'Unboxing Premium Headphones', 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 600, 2500, 'active'),
(2, 3, 'Smart Watch Review', 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 480, 1800, 'active'),
(5, 2, 'Best Budget Speaker', 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 720, 3200, 'active'),
(6, 2, 'Coffee Brewing Tips', 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 300, 500, 'active'),
(8, 4, 'Tech Gadgets Haul', 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 1200, 4500, 'active');

-- Insert Demo Chat Rooms
INSERT INTO chat_rooms (room_token, product_id, is_active) VALUES
('live-room-001', 1, true),
('live-room-002', 2, true),
('live-room-003', 5, true);

-- Passwords are hashed versions of "demo123" for testing
-- Admin: admin@videocommerce.local / demo123
-- Influencers: demo123
-- Customers: demo123
