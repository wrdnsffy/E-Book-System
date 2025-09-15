<?php
require_once '../includes/init.php';

ensure_admin();

$errors = [];
$reports = [];

// Date range for filtering
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

try {
    // Total orders and revenue
    $total_query = "SELECT COUNT(*) as total_orders, SUM(total_amount) as total_revenue 
                    FROM `order` 
                    WHERE DATE(o_date) BETWEEN ? AND ?";
    $total_stmt = db_prepare($total_query);
    $total_stmt->bind_param("ss", $start_date, $end_date);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result()->fetch_assoc();
    $reports['totals'] = $total_result;
    // Top 5 selling books
    $top_books_query = "SELECT b.id, b.book_title, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue
                        FROM order_item oi
                        JOIN book b ON oi.b_id = b.id
                        JOIN `order` o ON oi.o_id = o.id
                        WHERE DATE(o.o_date) BETWEEN ? AND ?
                        GROUP BY b.id
                        ORDER BY total_sold DESC
                        LIMIT 5";
    $top_books_stmt = db_prepare($top_books_query);
    $top_books_stmt->bind_param("ss", $start_date, $end_date);
    $top_books_stmt->execute();
    $top_books_result = $top_books_stmt->get_result();
    $reports['top_books'] = $top_books_result->fetch_all(MYSQLI_ASSOC);

    // Top 5 customers
    $top_customers_query = "SELECT c.id, c.c_name, COUNT(o.id) as total_orders, SUM(o.total_amount) as total_spent
                            FROM customer c
                            JOIN `order` o ON c.id = o.c_id
                            WHERE DATE(o.o_date) BETWEEN ? AND ?
                            GROUP BY c.id
                            ORDER BY total_spent DESC
                            LIMIT 5";
    $top_customers_stmt = db_prepare($top_customers_query);
    $top_customers_stmt->bind_param("ss", $start_date, $end_date);
    $top_customers_stmt->execute();
    $top_customers_result = $top_customers_stmt->get_result();
    $reports['top_customers'] = $top_customers_result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    
    $errors[] = "Error generating reports: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Reports - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/admin_menu.php'; ?>

    <div class="container mt-4">
        <h1>Order Reports</h1>

        <!-- Date Range Form -->
        <form action="order_report.php" method="get" class="mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="start_date" class="col-form-label">Start Date:</label>
                </div>
                <div class="col-auto">
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-auto">
                    <label for="end_date" class="col-form-label">End Date:</label>
                </div>
                <div class="col-auto">
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </div>
        </form>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <!-- Total Orders and Revenue -->
            <h2>Overview</h2>
            <p>Total Orders: <?php echo $reports['totals']['total_orders']; ?></p>
            <p>Total Revenue: RM<?php echo number_format($reports['totals']['total_revenue'], 2); ?></p>

            <!-- Top Selling Books -->
            <h2>Top 5 Selling Books</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Total Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports['top_books'] as $book): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['book_title']); ?></td>
                            <td><?php echo $book['total_sold']; ?></td>
                            <td style="text-align:right;">RM<?php echo number_format($book['revenue'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Top Customers -->
            <h2>Top 5 Customers</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Total Orders</th>
                        <th>Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports['top_customers'] as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['c_name']); ?></td>
                            <td><?php echo $customer['total_orders']; ?></td>
                            <td style="text-align:right;">RM<?php echo number_format($customer['total_spent'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <footer class="mt-5 py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>