<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();

$user_id = $_SESSION['user_id'];

// Fetch available books
$books_query = "SELECT id, book_title, price FROM book ORDER BY book_title";
$books_result = db_query($books_query);

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Invalid request.";
    } else {
        $book_ids = $_POST['book_ids'] ?? [];
        $quantities = $_POST['quantities'] ?? [];

        if (empty($book_ids)) {
            $errors[] = "Please select at least one book.";
        } else {
            $total_amount = 0;
            $order_items = [];

            $conn = db_connect();

            // Start transaction
            mysqli_begin_transaction($conn);

            try {
                // Create order
                $order_date = date('Y-m-d H:i:s');
                $order_query = "INSERT INTO `order` (c_id, total_amount, o_date, o_paymentstatus, o_status) VALUES (?, 0, ?, 'Unpaid', 'Processing')";
                $order_stmt = mysqli_prepare($conn, $order_query);
                mysqli_stmt_bind_param($order_stmt, "is", $user_id, $order_date);
                mysqli_stmt_execute($order_stmt);
                $order_id = mysqli_insert_id($conn);

                // Process each book in the order
                foreach ($book_ids as $index => $book_id) {
                    $quantity = intval($quantities[$index]);
                    if ($quantity > 0) {
                        $book_query = "SELECT price FROM book WHERE id = ?";
                        $book_stmt = mysqli_prepare($conn, $book_query);
                        mysqli_stmt_bind_param($book_stmt, "i", $book_id);
                        mysqli_stmt_execute($book_stmt);
                        $book_result = mysqli_stmt_get_result($book_stmt);
                        $book = mysqli_fetch_assoc($book_result);

                        $item_total = $book['price'] * $quantity;
                        $total_amount += $item_total;

                        $order_items[] = [
                            'book_id' => $book_id,
                            'quantity' => $quantity,
                            'price' => $book['price'],
                            'total' => $item_total
                        ];

                        // Update order items
                        $item_query = "INSERT INTO order_item (o_id, b_id, quantity, price) VALUES (?, ?, ?, ?)";
                        $item_stmt = mysqli_prepare($conn, $item_query);
                        mysqli_stmt_bind_param($item_stmt, "iiid", $order_id, $book_id, $quantity, $book['price']);
                        mysqli_stmt_execute($item_stmt);
                    }
                }

                // Update order total
                $update_total_query = "UPDATE `order` SET total_amount = ? WHERE id = ?";
                $update_total_stmt = mysqli_prepare($conn, $update_total_query);
                mysqli_stmt_bind_param($update_total_stmt, "di", $total_amount, $order_id);
                mysqli_stmt_execute($update_total_stmt);

                // Commit transaction
                mysqli_commit($conn);

                $success_message = "Order placed successfully!";
                $_SESSION['last_order_id'] = $order_id;
                header("Location: invoice.php?order_id=$order_id");
                exit();
            } catch (Exception $e) {
                mysqli_rollback($conn);
                echo $e->getMessage();exit;
                $errors[] = "Error processing order. Please try again.";
            }

            mysqli_close($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include_once ('../includes/menu.php') ?>

    <div class="container">
        <h1 class="mb-4">Place Your Order</h1>
        <?php
        if (!empty($errors)) {
            echo '<div class="alert alert-danger" role="alert">';
            foreach ($errors as $error) {
                echo '<p class="mb-0">' . $error . '</p>';
            }
            echo '</div>';
        }
        if ($success_message) {
            echo '<div class="alert alert-success" role="alert">' . $success_message . '</div>';
        }
        ?>
        <form action="form.php" method="post" id="orderForm">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Price</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = mysqli_fetch_assoc($books_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['book_title']); ?></td>
                                <td>$<?php echo number_format($book['price'], 2); ?></td>
                                <td>
                                    <input type="number" name="quantities[]" class="form-control quantity-input" min="0"
                                        value="0" data-price="<?php echo $book['price']; ?>">
                                    <input type="hidden" name="book_ids[]" value="<?php echo $book['id']; ?>">
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-end mb-3">
                <strong>Total: $<span id="orderTotal">0.00</span></strong>
            </div>
            <button type="submit" class="btn btn-primary">Place Order</button>
        </form>
    </div>

    <footer class="mt-5 py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('orderForm');
            const quantityInputs = document.querySelectorAll('.quantity-input');
            const totalSpan = document.getElementById('orderTotal');

            function updateTotal() {
                let total = 0;
                quantityInputs.forEach(input => {
                    const quantity = parseInt(input.value);
                    const price = parseFloat(input.dataset.price);
                    total += quantity * price;
                });
                totalSpan.textContent = total.toFixed(2);
            }

            quantityInputs.forEach(input => {
                input.addEventListener('change', updateTotal);
            });

            form.addEventListener('submit', function (e) {
                const total = parseFloat(totalSpan.textContent);
                if (total === 0) {
                    e.preventDefault();
                    alert('Please select at least one book to order.');
                }
            });
        });
    </script>
</body>

</html>