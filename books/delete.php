<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $book_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($book_id === false) {
        $errors[] = "Invalid book ID.";
    } else {
        try {
            // First, check if the book exists
            $check_query = "SELECT book_title FROM book WHERE id = ?";
            $check_stmt = db_prepare($check_query);
            $check_stmt->bind_param("i", $book_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $book = $result->fetch_assoc();
            $check_stmt->close();

            if (!$book) {
                $errors[] = "Book not found.";
            } else {
                // Check if the book is referenced in any order
                $order_check_query = "SELECT COUNT(*) as count FROM order_item WHERE b_id = ?";
                $order_check_stmt = db_prepare($order_check_query);
                $order_check_stmt->bind_param("i", $book_id);
                $order_check_stmt->execute();
                $order_check_result = $order_check_stmt->get_result();
                $order_count = $order_check_result->fetch_assoc()['count'];
                $order_check_stmt->close();

                if ($order_count > 0) {
                    // Book is referenced in orders, can't delete
                    $errors[] = "This book cannot be deleted because it is associated with one or more orders. Consider marking it as inactive instead.";
                } else {
                    // Book exists and is not referenced, proceed with deletion
                    $delete_query = "DELETE FROM book WHERE id = ?";
                    $delete_stmt = db_prepare($delete_query);
                    $delete_stmt->bind_param("i", $book_id);

                    if ($delete_stmt->execute()) {
                        log_action($_SESSION['user_id'], 'book_deleted', "Deleted book: {$book['book_title']} (ID: $book_id)");
                        $success_message = "Book deleted successfully.";
                    } else {
                        throw new Exception("Error deleting book.");
                    }

                    $delete_stmt->close();
                }
            }
        } catch (Exception $e) {
            $errors[] = "Error deleting book: " . $e->getMessage();
        }
    }
} else {
    $errors[] = "Invalid request.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Book - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="../index.php"><?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="list.php">Books</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h3 class="mb-4">Delete Book</h3>

        <?php
        if (!empty($errors)) {
            echo '<div class="alert alert-danger" role="alert">';
            foreach ($errors as $error) {
                echo '<p class="mb-0">' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
        }
        if ($success_message) {
            echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($success_message) . '</div>';
        }
        ?>

        <p>
            <?php
            if ($success_message) {
                echo "The book has been deleted. ";
            } elseif (empty($errors)) {
                echo "An error occurred while trying to delete the book. ";
            }
            ?>
            <a href="list.php" class="btn btn-info">Return to Book List</a>
        </p>
    </div>

    <footer class="mt-5 py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>