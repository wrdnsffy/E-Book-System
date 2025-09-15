<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();



$errors = [];
$success_message = '';
$orders_result = null;
$total_pages = 0;

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : (isset($_POST['page']) ? (int) $_POST['page'] : 1);
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : (isset($_POST['sort']) ? $_POST['sort'] : 'id');
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : (isset($_POST['sort_order']) ? $_POST['sort_order'] : 'ASC');

// Validate sort column to prevent SQL injection
$allowed_sort_columns = ['id', 'c_name', 'order_date', 'total_amount', 'status'];
if (!in_array($sort, $allowed_sort_columns)) {
    $sort = 'id';
}

// Ensure $sort_order is either 'ASC' or 'DESC'
$sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';

// Create the ORDER BY clause
$order_by = "ORDER BY $sort $sort_order";

try {
    // Get total number of orders
    $total_result = db_query("SELECT COUNT(*) as total FROM `order`");
    $total_orders = mysqli_fetch_assoc($total_result)['total'];
    $total_pages = ceil($total_orders / $per_page);

    // Fetch orders with sorting
    $orders_query = "SELECT o.*, c.c_name 
                     FROM `order` o 
                     JOIN customer c ON o.c_id = c.id 
                     $order_by 
                     LIMIT ? OFFSET ?";
    $stmt = db_prepare($orders_query);
    $stmt->bind_param("ii", $per_page, $offset);
    $stmt->execute();
    $orders_result = $stmt->get_result();

    // Handle status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        if (!verify_csrf_token($_POST['csrf_token'])) {
            throw new Exception("Invalid request.");
        }

        $order_id = $_POST['order_id'];
        $new_status = $_POST['new_status'];
        $update_query = "UPDATE `order` SET o_status = ? WHERE id = ?";
        $update_stmt = db_prepare($update_query);
        $update_stmt->bind_param("si", $new_status, $order_id);

        if ($update_stmt->execute()) {
            $success_message = "Order status updated successfully.";
            log_action($_SESSION['user_id'], 'order_status_updated', "Updated order status: Order ID $order_id, New status: $new_status");
        } else {
            throw new Exception("Error updating order status.");
        }
    }
} catch (Exception $e) {
    $errors[] = "Error: " . $e->getMessage();
}

// Function to generate sorting links
function sort_link($column, $title)
{
    global $sort, $sort_order, $page;
    $new_sort_order = ($sort === $column && $sort_order === 'ASC') ? 'DESC' : 'ASC';
    $class = ($sort === $column) ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : '';
    return "<a style='text-decoration:none;color:#000;' href='?sort=$column&sort_order=$new_sort_order&page=$page' class='$class'>$title</a>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order List - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sort-asc::after {
            content: " ▲";
        }

        .sort-desc::after {
            content: " ▼";
        }
    </style>
</head>

<body>
    <?php
    if (!is_admin()) { ?>
        <?php include_once ('../Includes/menu.php') ?>
    <?php } else { ?>
        <?php include_once ('../Includes/admin_menu.php') ?>
    <?php } ?>


    <div class="container">
        <h3 class="mb-4">Order List</h3>

        <?php
        if (!empty($errors)) {
            echo '<div class="alert alert-danger" role="alert">';
            foreach ($errors as $error) {
                echo '<p class="mb-0">' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
        }
        if (isset($success_message)) {
            echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($success_message) . '</div>';
        }
        ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th><?php echo sort_link('id', 'Order ID'); ?></th>
                        <th><?php echo sort_link('c_name', 'Customer'); ?></th>
                        <th><?php echo sort_link('o_date', 'Date'); ?></th>
                        <th><?php echo sort_link('total_amount', 'Amount'); ?></th>
                        <?php if (is_admin()) { ?>
                            <th><?php echo sort_link('o_status', 'Status'); ?></th>
                        <?php } ?>

                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['id']); ?></td>
                            <td><?php echo htmlspecialchars($order['c_name']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($order['o_date'])); ?></td>
                            <td style="text-align:right;">RM<?php echo number_format($order['total_amount'], 2); ?></td>
                            <?php if (is_admin()) { ?>
                                <td>
                                    <form action="list.php" method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="page" value="<?php echo $page; ?>">
                                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                                        <input type="hidden" name="sort_order"
                                            value="<?php echo htmlspecialchars($sort_order); ?>">
                                        <select name="new_status" class="form-select form-select-sm"
                                            onchange="this.form.submit()">
                                            <option value="Pending" <?php echo $order['o_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Processing" <?php echo $order['o_status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Shipped" <?php echo $order['o_status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="Delivered" <?php echo $order['o_status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="Cancelled" <?php echo $order['o_status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                            <?php } ?>

                            <td style="text-align:right;">
                                <a href="invoice.php?order_id=<?php echo $order['id']; ?>"
                                    class="btn btn-sm btn-info">View</a>
                                <a href="delete.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this order?')">Cancel</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Order list pagination">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link"
                            href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&sort_order=<?php echo $sort_order; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <footer class="mt-5 py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>