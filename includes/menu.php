<?php
// Ensure this file is included in init.php or at the beginning of each page
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate the total number of items in the cart
$cart_count = array_sum($_SESSION['cart']);

// You might want to define BASE_URL in your config.php file
if (!defined('BASE_URL')) {
    define('BASE_URL', '/bookstore/'); // Adjust this path as needed
}
?>
<header>
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark no-print"
        style="background-image: linear-gradient(#eeaeca, #94bbe9);">
        <div class="container">
            <a class="navbar-brand" href="javascript:void(0)"><?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="../books/list.php">Books</a>
                    </li>
                    <?php if ($logged_in): ?>
                        <li class="nav-item"><a class="nav-link" aria-current="page" href="../orders/list.php">Orders</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" aria-current="page" href="../auth/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" aria-current="page" href="../auth/login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" aria-current="page"
                                href="../auth/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
                <span class="navbar-text">
                    <a href="../orders/cart.php" class="btn btn-outline-light">
                        <i class="fas fa-shopping-cart"></i> Cart (<?php echo count($_SESSION['cart']); ?>)
                        - RM<?php echo number_format(get_cart_total(), 2); ?>
                    </a>
                </span>
            </div>
        </div>
    </nav>
</header>