<?php
require_once '../includes/init.php';

ensure_admin();

$customer_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

if (!$customer_id) {
    redirect('customers.php');
}

try {
    // Fetch customer details
    $customer_query = "SELECT * FROM customer WHERE id = ?";
    $customer_stmt = db_prepare($customer_query);
    $customer_stmt->bind_param("i", $customer_id);
    $customer_stmt->execute();
    $customer_result = $customer_stmt->get_result();
    $customer = $customer_result->fetch_assoc();

    if (!$customer) {
        throw new Exception("Customer not found");
    }

    // Fetch customer's purchased books
    $books_query = "SELECT b.id, b.book_title, b.isbn, o.o_date, oi.quantity, oi.price
                    FROM `order` o
                    JOIN order_item oi ON o.id = oi.o_id
                    JOIN book b ON oi.b_id = b.id
                    WHERE o.c_id = ?
                    ORDER BY o.o_date DESC";
    $books_stmt = db_prepare($books_query);
    $books_stmt->bind_param("i", $customer_id);
    $books_stmt->execute();
    $books_result = $books_stmt->get_result();
    $purchased_books = $books_result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Purchases - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include '../includes/admin_menu.php'; ?>

    <div class="container mt-4">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php else: ?>
            <h3>Purchases for <?php echo htmlspecialchars($customer['c_name']); ?></h3>
            <p>Email: <?php echo htmlspecialchars($customer['c_email']); ?></p>
            <p>Phone: <?php echo htmlspecialchars($customer['c_phone']); ?></p>

            <?php if (empty($purchased_books)): ?>
                <p>This customer has not made any purchases yet.</p>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>ISBN</th>
                            <th>Order Date</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; ?>
                        <?php foreach ($purchased_books as $book): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['book_title']); ?></td>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($book['o_date'])); ?></td>
                                <td><?php echo $book['quantity']; ?></td>
                                <?php $total += $book['price']; ?>
                                <td style="text-align:right;">RM<?php echo number_format($book['price'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <th colspan="4" style="text-align:right;">Total:</th>
                            <th style="text-align:right;"><?php echo $total; ?></th>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <a href="index.php" class="btn btn-secondary btn-sm">Back to Customer List</a>
        <?php endif; ?>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>
