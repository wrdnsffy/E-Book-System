<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();

// Check if the user is an admin (you may need to implement this function)
if (!is_admin()) {
    redirect('../index.php');
}

if (isset($_GET['id'])) {
    $order_id = (int) $_GET['id'];

    $conn = db_connect();

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Delete order items
        $delete_items_query = "DELETE FROM order_item WHERE o_id = ?";
        $delete_items_stmt = mysqli_prepare($conn, $delete_items_query);
        mysqli_stmt_bind_param($delete_items_stmt, "i", $order_id);
        mysqli_stmt_execute($delete_items_stmt);

        // Delete the order
        $delete_order_query = "DELETE FROM `order` WHERE id = ?";
        $delete_order_stmt = mysqli_prepare($conn, $delete_order_query);
        mysqli_stmt_bind_param($delete_order_stmt, "i", $order_id);
        mysqli_stmt_execute($delete_order_stmt);

        // Commit transaction
        mysqli_commit($conn);

        $_SESSION['success_message'] = "Order #$order_id has been deleted successfully.";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Error deleting order. Please try again.";
    }

    mysqli_close($conn);
}

redirect('list.php');
?>