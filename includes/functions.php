<?php
/**
 * Utility functions for the Book Store application
 */

/**
 * Sanitize user input
 *
 * @param string $input The input to sanitize
 * @return string The sanitized input
 */
function sanitize_input($input)
{
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Validate email address
 *
 * @param string $email The email address to validate
 * @return bool True if the email is valid, false otherwise
 */
function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate a secure password hash
 *
 * @param string $password The password to hash
 * @return string The hashed password
 */
function hash_password($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password against a hash
 *
 * @param string $password The password to verify
 * @param string $hash The hash to verify against
 * @return bool True if the password is correct, false otherwise
 */
function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Check if a user is logged in
 *
 * @return bool True if the user is logged in, false otherwise
 */
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to a specific page
 *
 * @param string $page The page to redirect to
 */
function redirect($page)
{
    header("Location: " . BASE_URL . $page);
    exit();
}

/**
 * Generate a CSRF token
 *
 * @return string The generated CSRF token
 */
function generate_csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 *
 * @param string $token The token to verify
 * @return bool True if the token is valid, false otherwise
 */
function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Display error messages
 *
 * @param array $errors Array of error messages
 */
function display_errors($errors)
{
    if (!empty($errors)) {
        echo '<div class="error-messages">';
        foreach ($errors as $error) {
            echo '<p>' . $error . '</p>';
        }
        echo '</div>';
    }
}

/**
 * Display success message
 *
 * @param string $message The success message to display
 */
function display_success($message)
{
    if (!empty($message)) {
        echo '<div class="success-message">';
        echo '<p>' . $message . '</p>';
        echo '</div>';
    }
}

/**
 * Format price as currency
 *
 * @param float $price The price to format
 * @return string The formatted price
 */
function format_price($price)
{
    return '$' . number_format($price, 2);
}

/**
 * Get user role name
 *
 * @param int $role_id The role ID
 * @return string The role name
 */
function get_user_role_name($role_id)
{
    $roles = [
        1 => 'Admin',
        2 => 'Staff',
        3 => 'Customer'
    ];
    return isset($roles[$role_id]) ? $roles[$role_id] : 'Unknown';
}

/**
 * Log an action in the system
 *
 * @param int $user_id The ID of the user performing the action
 * @param string $action The action being performed
 * @param string $details Additional details about the action
 */
function log_action($user_id, $action, $details = '')
{
    $sql = "INSERT INTO activity_log (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())";
    $conn = db_connect();
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $action, $details);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Ensure the current user is an admin, redirect if not
 */
function ensure_admin() {
    if (!is_admin()) {
        redirect('auth/admin_login.php');
    }
}
function get_cart_total()
{
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}
?>