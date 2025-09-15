<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();

$errors = [];
$book = null;
$reviews = [];
$related_books = [];

if (isset($_GET['id'])) {
    $book_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($book_id === false) {
        $errors[] = "Invalid book ID.";
    } else {
        try {
            // Fetch book details
            $query = "SELECT b.*, a.a_name AS author_name, p.p_name AS publisher_name
                      FROM book b
                      JOIN author a ON b.a_id = a.id
                      JOIN publisher p ON b.p_id = p.id
                      WHERE b.id = ?";

            $stmt = db_prepare($query);
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $book = $result->fetch_assoc();

            if (!$book) {
                $errors[] = "Book not found.";
            } else {
                // Fetch reviews
                $review_query = "SELECT r.*, c.c_name 
                                 FROM review r
                                 JOIN customer c ON r.c_id = c.id
                                 WHERE r.b_id = ?
                                 ORDER BY r.created_at DESC";
                $review_stmt = db_prepare($review_query);
                $review_stmt->bind_param("i", $book_id);
                $review_stmt->execute();
                $review_result = $review_stmt->get_result();
                while ($review = $review_result->fetch_assoc()) {
                    $reviews[] = $review;
                }

                // Fetch related books (same author or category)
                $related_query = "SELECT b.id, b.book_title, b.category 
                                  FROM book b
                                  WHERE (b.a_id = ? OR b.category = ?) AND b.id != ?
                                  LIMIT 5";
                $related_stmt = db_prepare($related_query);
                $related_stmt->bind_param("isi", $book['a_id'], $book['category'], $book_id);
                $related_stmt->execute();
                $related_result = $related_stmt->get_result();
                while ($related_book = $related_result->fetch_assoc()) {
                    $related_books[] = $related_book;
                }
            }

            $stmt->close();
            $review_stmt->close();
            $related_stmt->close();
        } catch (Exception $e) {
            $errors[] = "Error retrieving book details: " . $e->getMessage();
        }
    }
} else {
    $errors[] = "No book ID provided.";
}

// Handle adding review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]);
    $review_text = trim($_POST['review_text']);

    if ($rating === false) {
        $errors[] = "Invalid rating.";
    }
    if (empty($review_text)) {
        $errors[] = "Review text cannot be empty.";
    }

    if (empty($errors)) {
        try {
            $add_review_query = "INSERT INTO review (c_id, b_id, r_rating, r_text) VALUES (?, ?, ?, ?)";
            $add_review_stmt = db_prepare($add_review_query);
            $add_review_stmt->bind_param("iiis", $_SESSION['user_id'], $book_id, $rating, $review_text);
            $add_review_stmt->execute();
            $add_review_stmt->close();

            // Refresh the page to show the new review
            header("Location: details.php?id=$book_id");
            exit();
        } catch (Exception $e) {
            $errors[] = "Error adding review: " . $e->getMessage();
        }
    }
}

// Handle adding to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

    if ($quantity === false) {
        $errors[] = "Invalid quantity.";
    } else {
        // Initialize the cart if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if the book is already in the cart
        if (isset($_SESSION['cart'][$book_id])) {
            $_SESSION['cart'][$book_id] += $quantity;
        } else {
            $_SESSION['cart'][$book_id] = $quantity;
        }

        $success_message = "Added $quantity copy/copies of '{$book['book_title']}' to your cart.";
    }
}

// Get the current cart quantity for this book
$cart_quantity = isset($_SESSION['cart'][$book_id]) ? $_SESSION['cart'][$book_id] : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $book ? htmlspecialchars($book['book_title']) : 'Book Details'; ?> - <?php echo SITE_NAME; ?>
    </title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include_once ('../Includes/admin_menu.php') ?>
    <div class="content">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php elseif ($book): ?>
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            <h3 class="mb-4"><?php echo htmlspecialchars($book['book_title']); ?></h3>
            <div class="row">
                <div class="col-md-4">
                    <img src="../img/covers/<?php echo htmlspecialchars($book['isbn']); ?>.jpg"
                        alt="<?php echo htmlspecialchars($book['book_title']); ?> cover" class="img-fluid mb-3">

                    <!-- Add to Cart Form -->
                    <form action="details.php?id=<?php echo $book['id']; ?>" method="post" class="mb-3">
                        <div class="input-group">
                            <input type="number" name="quantity" value="1" min="1" class="form-control">
                            <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                        </div>
                    </form>

                    <!-- Social Sharing Buttons -->
                    <div class="mb-3">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                            class="btn btn-primary" target="_blank"><i class="fab fa-facebook-f"></i> Share</a>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($book['book_title']); ?>&url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                            class="btn btn-info" target="_blank"><i class="fab fa-twitter"></i> Tweet</a>
                    </div>
                </div>
                <div class="col-md-8">
                    <table class="table">
                        <tr>
                            <th>ISBN:</th>
                            <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        </tr>
                        <tr>
                            <th>Author:</th>
                            <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Publisher:</th>
                            <td><?php echo htmlspecialchars($book['publisher_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td><?php echo htmlspecialchars($book['category']); ?></td>
                        </tr>
                        <tr>
                            <th>Price:</th>
                            <td>RM<?php echo number_format($book['price'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Copyright Date:</th>
                            <td><?php echo date('F j, Y', strtotime($book['copyright_date'])); ?></td>
                        </tr>
                        <tr>
                            <th>Year:</th>
                            <td><?php echo htmlspecialchars($book['year']); ?></td>
                        </tr>
                        <tr>
                            <th>Page Count:</th>
                            <td><?php echo htmlspecialchars($book['page_count']); ?></td>
                        </tr>
                    </table>
                    <div class="mt-3">
                        <a href="edit.php?id=<?php echo $book['id']; ?>" class="btn btn-primary">Edit Book</a>
                        <a href="delete.php?id=<?php echo $book['id']; ?>" class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to delete this book?')">Delete Book</a>
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            <h2 class="mt-5">Reviews</h2>
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($review['c_name']); ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted">Rating:
                                <?php echo str_repeat('★', $review['r_rating']) . str_repeat('☆', 5 - $review['r_rating']); ?>
                            </h6>
                            <p class="card-text"><?php echo htmlspecialchars($review['r_text']); ?></p>
                            <footer class="blockquote-footer"><?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                            </footer>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No reviews yet. Be the first to review this book!</p>
            <?php endif; ?>

            <!-- Add Review Form -->
            <h3 class="mt-4">Add a Review</h3>
            <form action="details.php?id=<?php echo $book['id']; ?>" method="post">
                <div class="mb-3">
                    <label for="rating" class="form-label">Rating</label>
                    <select name="rating" id="rating" class="form-select" required>
                        <option value="1">1 Star</option>
                        <option value="2">2 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="5">5 Stars</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="review_text" class="form-label">Your Review</label>
                    <textarea name="review_text" id="review_text" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" name="add_review" class="btn btn-primary">Submit Review</button>
            </form>

            <!-- Related Books Section -->
            <?php if (!empty($related_books)): ?>
                <h2 class="mt-5">Related Books</h2>
                <div class="row">
                    <?php foreach ($related_books as $related_book): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($related_book['book_title']); ?></h5>
                                    <p class="card-text">Category: <?php echo htmlspecialchars($related_book['category']); ?></p>
                                    <a href="details.php?id=<?php echo $related_book['id']; ?>" class="btn btn-primary">View
                                        Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <footer class="mt-5 py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>