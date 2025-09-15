<?php
// Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Check if user is logged in
$logged_in = isset($_SESSION['user_id']);
// Include necessary files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Define the is_logged_in function if it's not already defined
if (!function_exists('is_logged_in')) {
    function is_logged_in()
    {
        return isset($_SESSION['user_id']);
    }
}

// Check if user is logged in
function ensure_user_is_logged_in()
{
    if (!is_logged_in()) {
        redirect('auth/login.php');
    }
}

?>