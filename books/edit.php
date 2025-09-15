<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();

$errors = [];
$success_message = '';
$book = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $book_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($book_id === false) {
        $errors[] = "Invalid book ID.";
    } else {
        try {
            $query = "SELECT * FROM book WHERE id = ?";
            $stmt = db_prepare($query);
            mysqli_stmt_bind_param($stmt, "i", $book_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $book = mysqli_fetch_assoc($result);

            if (!$book) {
                $errors[] = "Book not found.";
            }

            mysqli_stmt_close($stmt);
        } catch (Exception $e) {
            $errors[] = "Error retrieving book details: " . $e->getMessage();
        }
    }
}
// Fetch publishers and authors for dropdown menus
$publishers = db_query("SELECT id, p_name FROM publisher ORDER BY p_name");
$authors = db_query("SELECT id, a_name FROM author ORDER BY a_name");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Invalid request.";
    } else {
        $book_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $isbn = sanitize_input($_POST['isbn']);
        $title = sanitize_input($_POST['title']);
        $category = sanitize_input($_POST['category']);
        $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
        $copyright_date = sanitize_input($_POST['copyright_date']);
        $year = sanitize_input($_POST['year']);
        $page_count = filter_var($_POST['page_count'], FILTER_VALIDATE_INT);
        $publisher_id = filter_var($_POST['publisher_id'], FILTER_VALIDATE_INT);
        $author_id = filter_var($_POST['author_id'], FILTER_VALIDATE_INT);

        // Validate fields
        if (empty($isbn) || !is_numeric($isbn)) {
            $errors[] = "Please enter an ISBN.";
        }
        if (empty($title)) {
            $errors[] = "Please enter a book title.";
        }
        if (empty($category)) {
            $errors[] = "Please enter a category.";
        }
        if ($price === false || $price <= 0) {
            $errors[] = "Please enter a valid price.";
        }
        if (empty($copyright_date) || !strtotime($copyright_date)) {
            $errors[] = "Please enter a valid copyright date.";
        }
        if (empty($year) || !is_numeric($year) || strlen($year) != 4) {
            $errors[] = "Please enter a valid 4-digit year.";
        }
        if ($page_count === false || $page_count <= 0) {
            $errors[] = "Please enter a valid page count.";
        }
        if ($publisher_id === false || $publisher_id <= 0) {
            $errors[] = "Please select a valid publisher.";
        }
        if ($author_id === false || $author_id <= 0) {
            $errors[] = "Please select a valid author.";
        }

        if (empty($errors)) {
            try {
                $sql = "UPDATE book SET isbn = ?, book_title = ?, category = ?, price = ?, 
                        copyright_date = ?, year = ?, page_count = ?, p_id = ?, a_id = ? 
                        WHERE id = ?";
                $stmt = db_prepare($sql);
                mysqli_stmt_bind_param(
                    $stmt,
                    "sssdssiiii",
                    $isbn,
                    $title,
                    $category,
                    $price,
                    $copyright_date,
                    $year,
                    $page_count,
                    $publisher_id,
                    $author_id,
                    $book_id
                );

                if (mysqli_stmt_execute($stmt)) {
                    log_action($_SESSION['user_id'], 'book_updated', "Updated book: $title (ID: $book_id)");
                    $success_message = "Book updated successfully!";

                    // Refresh book data
                    $book = [
                        'id' => $book_id,
                        'isbn' => $isbn,
                        'book_title' => $title,
                        'category' => $category,
                        'price' => $price,
                        'copyright_date' => $copyright_date,
                        'year' => $year,
                        'page_count' => $page_count,
                        'p_id' => $publisher_id,
                        'a_id' => $author_id
                    ];
                    header('location:list.php');
                    exit;
                } else {
                    throw new Exception("Error updating book.");
                }

                mysqli_stmt_close($stmt);
            } catch (Exception $e) {
                $errors[] = "Error updating book: " . $e->getMessage();
            }
        }
    }
}

?>
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include_once ('../Includes/admin_menu.php') ?>

    <div class="container">
        <h1 class="mb-4">Edit Book</h1>

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

        <?php if ($book): ?>
            <form action="edit.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="id" value="<?php echo $book['id']; ?>">

                <div class="mb-3">
                    <label for="isbn" class="form-label">ISBN:</label>
                    <input type="text" class="form-control" id="isbn" name="isbn"
                        value="<?php echo htmlspecialchars($book['isbn']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Title:</label>
                    <input type="text" class="form-control" id="title" name="title"
                        value="<?php echo htmlspecialchars($book['book_title']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Category:</label>
                    <input type="text" class="form-control" id="category" name="category"
                        value="<?php echo htmlspecialchars($book['category']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Price:</label>
                    <input type="number" class="form-control" id="price" name="price" step="0.01"
                        value="<?php echo htmlspecialchars($book['price']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="copyright_date" class="form-label">Copyright Date:</label>
                    <input type="date" class="form-control" id="copyright_date" name="copyright_date"
                        value="<?php echo htmlspecialchars($book['copyright_date']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="year" class="form-label">Year:</label>
                    <input type="number" class="form-control" id="year" name="year"
                        value="<?php echo htmlspecialchars($book['year']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="page_count" class="form-label">Page Count:</label>
                    <input type="number" class="form-control" id="page_count" name="page_count"
                        value="<?php echo htmlspecialchars($book['page_count']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="publisher_id" class="form-label">Publisher:</label>
                    <select class="form-select" id="publisher_id" name="publisher_id" required>
                        <?php while ($publisher = mysqli_fetch_assoc($publishers)): ?>
                            <option value="<?php echo $publisher['id']; ?>" <?php echo $publisher['id'] == $book['p_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($publisher['p_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="author_id" class="form-label">Author:</label>
                    <select class="form-select" id="author_id" name="author_id" required>
                        <?php while ($author = mysqli_fetch_assoc($authors)): ?>
                            <option value="<?php echo $author['id']; ?>" <?php echo $author['id'] == $book['a_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($author['a_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update Book</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </form>
        <?php else: ?>
            <p>No book found to edit.</p>
            <a href="list.php" class="btn btn-primary">Back to Book List</a>
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