<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();

$errors = [];
$success_message = '';

// Fetch publishers and authors for dropdown menus
$publishers = db_query("SELECT id, p_name FROM publisher ORDER BY p_name");
$authors = db_query("SELECT id, a_name FROM author ORDER BY a_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Invalid request.";
    } else {
        // Sanitize and validate input
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
        if (empty($isbn)) {
            $errors[] = "Please enter an ISBN.";
        }
        // ... (other validations remain the same)

        // Handle file upload
        $cover_image = $_FILES['cover_image'];
        $upload_dir = '../img/covers/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if ($cover_image['error'] === UPLOAD_ERR_OK) {
            if (!in_array($cover_image['type'], $allowed_types)) {
                $errors[] = "Invalid file type. Please upload a JPEG, PNG, or GIF image.";
            } elseif ($cover_image['size'] > $max_size) {
                $errors[] = "File is too large. Maximum size is 5MB.";
            } else {
                $file_extension = pathinfo($cover_image['name'], PATHINFO_EXTENSION);
                $new_filename = $isbn . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;

                if (!move_uploaded_file($cover_image['tmp_name'], $upload_path)) {
                    $errors[] = "Failed to upload the cover image.";
                }
            }
        } elseif ($cover_image['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors[] = "Error uploading file. Please try again.";
        }

        // If no errors, insert the book
        if (empty($errors)) {
            try {
                $conn = db_connect();
                $sql = "INSERT INTO book (isbn, book_title, category, price, copyright_date, year, page_count, p_id, a_id, s_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                $staff_id = $_SESSION['user_id']; // Assuming the logged-in user is a staff member
                mysqli_stmt_bind_param($stmt, "sssdssiiii", $isbn, $title, $category, $price, $copyright_date, $year, $page_count, $publisher_id, $author_id, $staff_id);

                if (mysqli_stmt_execute($stmt)) {
                    $book_id = mysqli_insert_id($conn);
                    log_action($_SESSION['user_id'], 'book_added', "Added book: $title (ID: $book_id)");
                    $success_message = "Book added successfully!";
                    // Clear form fields after successful submission
                    $_POST = array();
                } else {
                    throw new Exception("Error adding book.");
                }

                $stmt->close();
            } catch (Exception $e) {
                $errors[] = "Error adding book: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Book - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include_once ('../Includes/admin_menu.php') ?>

    <div class="container">
        <h3 class="mb-4">Add New Book</h3>
        
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
        
        <form action="add.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <div class="mb-3">
                <label for="isbn" class="form-label">ISBN:</label>
                <input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="title" class="form-label">Title:</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="category" class="form-label">Category:</label>
                <input type="text" class="form-control" id="category" name="category" value="<?php echo isset($_POST['category']) ? htmlspecialchars($_POST['category']) : ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="price" class="form-label">Price:</label>
                <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="copyright_date" class="form-label">Copyright Date:</label>
                <input type="date" class="form-control" id="copyright_date" name="copyright_date" value="<?php echo isset($_POST['copyright_date']) ? htmlspecialchars($_POST['copyright_date']) : ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="year" class="form-label">Year:</label>
                <input type="number" class="form-control" id="year" name="year" value="<?php echo isset($_POST['year']) ? htmlspecialchars($_POST['year']) : ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="page_count" class="form-label">Page Count:</label>
                <input type="number" class="form-control" id="page_count" name="page_count" value="<?php echo isset($_POST['page_count']) ? htmlspecialchars($_POST['page_count']) : ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="publisher_id" class="form-label">Publisher:</label>
                <select class="form-select" id="publisher_id" name="publisher_id" required>
                    <option value="">Select a publisher</option>
                    <?php while ($publisher = mysqli_fetch_assoc($publishers)): ?>
                        <option value="<?php echo $publisher['id']; ?>" <?php echo (isset($_POST['publisher_id']) && $_POST['publisher_id'] == $publisher['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($publisher['p_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="author_id" class="form-label">Author:</label>
                <select class="form-select" id="author_id" name="author_id" required>
                    <option value="">Select an author</option>
                    <?php while ($author = mysqli_fetch_assoc($authors)): ?>
                        <option value="<?php echo $author['id']; ?>" <?php echo (isset($_POST['author_id']) && $_POST['author_id'] == $author['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($author['a_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="cover_image" class="form-label">Cover Image:</label>
                <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/gif">
            </div>
            
            <button type="submit" class="btn btn-primary">Add Book</button>
        </form>
    </div>

    <footer class="mt-5 py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
