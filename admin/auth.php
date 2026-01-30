<?php
/**
 * Authentication Middleware
 * SMPN 3 Satu Atap Cipari - Admin Panel
 *
 * Include this file in admin pages that require authentication
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Check if user is logged in
 */
function requireAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }

    // Check session timeout (30 minutes)
    if (isset($_SESSION['admin_login_time'])) {
        $timeout = 30 * 60; // 30 minutes
        if (time() - $_SESSION['admin_login_time'] > $timeout) {
            // Session expired
            session_unset();
            session_destroy();
            header('Location: ' . BASE_URL . 'admin/login.php?expired=1');
            exit;
        }
        // Refresh login time
        $_SESSION['admin_login_time'] = time();
    }
}

/**
 * Check if user has specific role
 *
 * @param array $allowedRoles Roles that can access the page
 * @return bool True if user has access
 */
function hasRole(array $allowedRoles) {
    if (!isset($_SESSION['admin_role'])) {
        return false;
    }
    return in_array($_SESSION['admin_role'], $allowedRoles);
}

/**
 * Require specific role to access page
 *
 * @param array $allowedRoles Roles that can access the page
 */
function requireRole(array $allowedRoles) {
    requireAuth();

    if (!hasRole($allowedRoles)) {
        $_SESSION['error_message'] = 'Anda tidak memiliki akses ke halaman ini.';
        header('Location: ' . BASE_URL . 'admin/index.php');
        exit;
    }
}

/**
 * Logout function
 */
function logout() {
    // Unset session variables
    $_SESSION = [];

    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy session
    session_destroy();

    // Redirect to login
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit;
}

// Process logout request
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

// Base URL constant
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname(dirname($_SERVER['SCRIPT_NAME']));
    define('BASE_URL', rtrim($protocol . $host . $path, '/') . '/');
}
