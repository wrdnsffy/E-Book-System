<?php
require_once 'includes/init.php';

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
?>
<div class="container">
    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
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
                        <td style="text-align:right">RM<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td style="text-align:right">RM<?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th style="text-align:right">RM<?php echo number_format($total, 2); ?></th>
                </tr>
            </tfoot>
        </table>

        <a href="orders/checkout.php" class="btn btn-secondary btn-sm">Proceed to Checkout</a>
    <?php endif; ?>
</div>
