<?php
// Session Management - Initialize and manage user sessions

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session configuration for security
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

if (!isset($_SESSION['user_id']) && isset($_COOKIE[session_name()])) {
    // Invalid session, destroy it
    session_destroy();
    header('Location: /auth/login.php?mode=login');
    exit;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Function to get current user role
function getUserRole() {
    return $_SESSION['user_role'] ?? 'guest';
}

// Function to get current user name
function getUserName() {
    return $_SESSION['user_name'] ?? 'Guest';
}

// Function to check if user has specific role
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /auth/login.php?mode=login');
        exit;
    }
}

// Function to require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('HTTP/1.0 403 Forbidden');
        echo 'Access Denied';
        exit;
    }
}

// Function to set user session
function setUserSession($userId, $userName, $userEmail, $userRole) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $userName;
    $_SESSION['user_email'] = $userEmail;
    $_SESSION['user_role'] = $userRole;
    $_SESSION['last_activity'] = time();
}

// Session timeout check (30 minutes)
$timeout = 30 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_destroy();
    header('Location: /auth/login.php?mode=login&message=session_expired');
    exit;
}

if (isLoggedIn()) {
    $_SESSION['last_activity'] = time();
}
