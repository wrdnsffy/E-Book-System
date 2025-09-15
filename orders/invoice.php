<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id === 0) {
    redirect('index.php');
}

// Fetch order details
$order_query = "SELECT o.*, c.c_name, c.c_email, c.c_address 
                FROM `order` o 
                JOIN customer c ON o.c_id = c.id 
                WHERE o.id = ? AND o.c_id = ?";
$order_stmt = mysqli_prepare(db_connect(), $order_query);
mysqli_stmt_bind_param($order_stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($order_stmt);
$order_result = mysqli_stmt_get_result($order_stmt);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    redirect('index.php');
}

// Fetch order items
$items_query = "SELECT oi.*, b.book_title 
                FROM order_item oi 
                JOIN book b ON oi.b_id = b.id 
                WHERE oi.o_id = ?";
$items_stmt = mysqli_prepare(db_connect(), $items_query);
mysqli_stmt_bind_param($items_stmt, "i", $order_id);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Order #<?php echo $order_id; ?> - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }

            .print-only {
                display: block;
            }
        }

        .print-only {
            display: none;
        }
    </style>
</head>

<body>
    <?php include_once ('../includes/menu.php') ?>

    <div class="container">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Invoice</h1>
                <p><strong>Order #:</strong> <?php echo $order_id; ?></p>
                <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['o_date'])); ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <h2><?php echo SITE_NAME; ?></h2>
                <p>123 Book Street</p>
                <p>Booktown, BT 12345</p>
                <p>Phone: (60+) 101-20093</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h3>Bill To:</h3>
                <p><?php echo htmlspecialchars($order['c_name']); ?></p>
                <p><?php echo htmlspecialchars($order['c_email']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($order['c_address'])); ?></p>
            </div>
            <div class="col-md-6">
                <h3>Order Details:</h3>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($order['o_status']); ?></p>
                <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($order['o_paymentstatus']); ?></p>
            </div>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['book_title']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>RM<?php echo number_format($item['price'], 2); ?></td>
                        <td>RM<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td><strong>RM<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="row mt-4">
            <div class="col-md-6">
                <h3>Thank You for Your Order!</h3>
                <p>We appreciate your business and hope you enjoy your books.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <button class="btn btn-primary no-print" onclick="window.print();">Print Invoice</button>
                <a href="../index.php" class="btn btn-secondary no-print">Back to Home</a>
            </div>
        </div>

        <div class="mt-4 print-only">
            <p>This is a computer-generated invoice. No signature is required.</p>
        </div>
    </div>

    <footer class="mt-5 py-3 bg-light no-print">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>