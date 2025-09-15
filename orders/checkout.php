<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();

$errors = [];
$success_message = '';

// Retrieve cart items and calculate total
$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$total = 0;

if (!empty($cart)) {
    $book_ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($book_ids), '?'));

    $query = "SELECT id, book_title, price FROM book WHERE id IN ($placeholders)";
    $stmt = db_prepare($query);
    $stmt->bind_param(str_repeat('i', count($book_ids)), ...$book_ids);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($book = $result->fetch_assoc()) {
        $quantity = $cart[$book['id']];
        $subtotal = $book['price'] * $quantity;
        $cart_items[] = [
            'id' => $book['id'],
            'title' => $book['book_title'],
            'price' => $book['price'],
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
        $total += $subtotal;
    }
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $shipping_address = trim($_POST['shipping_address']);
    $payment_method = trim($_POST['payment_method']);

    if (empty($shipping_address)) {
        $errors[] = "Shipping address is required.";
    }
    if (empty($payment_method)) {
        $errors[] = "Payment method is required.";
    }

    // If no errors, process the order
    if (empty($errors)) {
        try {
            $conn = db_connect();
            $conn->begin_transaction();

            // Insert into 'order' table
            $order_query = "INSERT INTO `order` (customer_id, order_date, total_amount, status, shipping_address) VALUES (?, NOW(), ?, 'Pending', ?)";
            $order_stmt = $conn->prepare($order_query);
            $order_stmt->bind_param("ids", $_SESSION['user_id'], $total, $shipping_address);
            $order_stmt->execute();
            $order_id = $conn->insert_id;

            // Insert into 'order_item' table
            $item_query = "INSERT INTO order_item (o_id, b_id, quantity, price) VALUES (?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_query);
            foreach ($cart_items as $item) {
                $item_stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
                $item_stmt->execute();
            }

            // Insert into 'payment' table
            $payment_query = "INSERT INTO payment (o_id, p_date, p_amount, p_method) VALUES (?, NOW(), ?, ?)";
            $payment_stmt = $conn->prepare($payment_query);
            $payment_stmt->bind_param("ids", $order_id, $total, $payment_method);
            $payment_stmt->execute();

            $conn->commit();

            // Clear the cart
            $_SESSION['cart'] = [];

            $success_message = "Your order has been placed successfully! Order ID: " . $order_id;
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Error processing your order: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include '../includes/menu.php'; ?>

    <div class="container">
        <h1 class="mb-4">Checkout</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php else: ?>
            <?php if (empty($cart_items)): ?>
                <p>Your cart is empty. Please add some items before checking out.</p>
            <?php else: ?>
                <h3>Order Summary</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <td style="text-align: right;">RM<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td style="text-align: right;">RM<?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3">Total</th>
                            <th style="text-align: right;">RM<?php echo number_format($total, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>

                <h3>Shipping and Payment</h3>
                <form action="checkout.php" method="post">
                    <div class="mb-3">
                        <label for="shipping_address" class="form-label">Shipping Address</label>
                        <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3"
                            required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Select a payment method</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="PayPal">PayPal</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Place Order</button>
                </form>
            <?php endif; ?>
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