<?php
require_once 'includes/init.php';

// Fetch featured books from the database
$sql = "SELECT id, book_title, isbn FROM book ORDER BY RAND() LIMIT 3";
$featured_books = db_query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Home</title>
    <!-- Bootstrap 5 CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/front_style.css">
</head>

<body>
    <div class="panel">
        <div class="panel-body">
            <?php echo SITE_NAME; ?>
        </div>
        <a class="abt-button" href="#about">About</a>
        <a class="book-button" href="#books">Books</a>
        <a class="review-button" href="#reviews">Review</a>
        <a class="cart-button" href="#cart"><i class="fas fa-shopping-cart"></i> Cart
            (<?php echo count($_SESSION['cart']); ?>)
        </a>
        <!-- <a class="login-button" href="auth/login.php">Login</a>
        <a class="register-button" href="auth/register.php">Register</a> -->
    </div>
    <h1 class="header">Welcome To <?php echo SITE_NAME; ?></h1>
    <p class="header-statement">Buy your book by only one click</p>
    <button class="shopping-button" href="#books">Start Shopping</button>
    <div class="container">
        <div class="content">
            <h1 id="books">Our Books</h1>
            <p>Discover a vast selection of books on our website, where you'll find everything from bestsellers and
                timeless
                classics to niche genres and hidden gems. Explore our extensive collection and find your next great read
                today!</p>
            <div id="featuredBooks" class="carousel slide mb-5" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <?php for ($i = 0; $i < mysqli_num_rows($featured_books); $i++): ?>
                        <button type="button" data-bs-target="#featuredBooks" data-bs-slide-to="<?php echo $i; ?>" <?php echo $i === 0 ? 'class="active" aria-current="true"' : ''; ?>
                            aria-label="Slide <?php echo $i + 1; ?>"></button>
                    <?php endfor; ?>
                </div>
                <div class="carousel-inner">
                    <?php
                    $first = true;
                    while ($book = mysqli_fetch_assoc($featured_books)):
                        ?>
                        <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                            <img src="img/covers/<?php echo $book['isbn']; ?>.jpg" class="d-inline w-10"
                                alt="<?php echo htmlspecialchars($book['book_title']); ?>">
                            <div class="carousel-caption d-none d-md-block">
                                <h5><?php echo htmlspecialchars($book['book_title']); ?></h5>
                                <p><a href="books/details.php?id=<?php echo $book['id']; ?>" class="btn btn-light">View
                                        Details</a></p>
                            </div>
                        </div>
                        <?php
                        $first = false;
                    endwhile;
                    ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#featuredBooks"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#featuredBooks"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
        <div class="content">
            <h2 id="about">Our Story</h2>
            <p>Our goal is to make it easy for customers to browse, search, and buy books online, without needing to
                visit a physical bookstore. We've created a simple and user-friendly interface to achieve this.
                For administrators, we offer a robust book management system with an easy-to-use interface for
                adding, editing, and organizing books. This ensures our catalog is always current and accessible.
            </p>
            <p>We also provide tools to manage users, orders, and inventory efficiently. With advanced analytics and
                reporting, administrators can monitor sales and user activity, while user account management and
                support tools ensure a smooth experience for everyone.
                We are committed to innovation and customer satisfaction, constantly improving our services to make
                buying and managing books as easy as possible.
            </p>
        </div>
        <div class="content">
            <h2 class="mb-4" id="cart">Shopping Cart</h2>
            <?php include_once ('orders/cart.php'); ?>
        </div>
        <div class="content">
            <h2 class="mb-4" id="reviews">Reviews</h2>
            <?php include_once ('books/reviews.php'); ?>
        </div>


        <footer class="mt-5 py-3 bg-secondary">
            <div class="row">
                <div class="col-md-6">
                    <h2>About Us</h2>
                    <p>Welcome to our online bookstore! We offer a wide selection of books across various genres.
                        Whether
                        you're looking for bestsellers, classics, or hidden gems, we've got you covered.</p>
                </div>
                <div class="col-md-6">
                    <h2>Featured Categories</h2>
                    <ul>
                        <li><a href="books/list.php?category=fiction">Fiction</a></li>
                        <li><a href="books/list.php?category=non-fiction">Non-Fiction</a></li>
                        <li><a href="books/list.php?category=mystery">Mystery</a></li>
                        <li><a href="books/list.php?category=sci-fi">Science Fiction</a></li>
                    </ul>
                </div>
            </div>
            <div class="container text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>