<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Invalid request.";
    } else {
        // Sanitize and validate input
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $address = sanitize_input($_POST['address']);
        $phone = sanitize_input($_POST['phone']);

        // Validate fields
        if (empty($name)) {
            $errors[] = "Please enter an author name.";
        }
        if (empty($email) || !is_valid_email($email)) {
            $errors[] = "Please enter a valid email address.";
        }
        if (empty($phone)) {
            $errors[] = "Please enter a phone number.";
        }
        // Address can be optional, so we don't validate it

        // If no errors, insert the author
        if (empty($errors)) {
            try {
                $sql = "INSERT INTO author (a_name, a_email, address, phone) VALUES (?, ?, ?, ?)";
                $stmt = db_prepare($sql);
                mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $address, $phone);

                if (mysqli_stmt_execute($stmt)) {
                    $author_id = mysqli_insert_id(db_connect());
                    log_action($_SESSION['user_id'], 'author_added', "Added author: $name (ID: $author_id)");
                    $success_message = "Author added successfully!";
                    // Clear form fields after successful submission
                    $_POST = array();
                } else {
                    throw new Exception("Error adding author.");
                }

                mysqli_stmt_close($stmt);
            } catch (Exception $e) {
                $errors[] = "Error adding author: " . $e->getMessage();
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
    <title>Add New Author - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include_once ('../Includes/admin_menu.php') ?>

    <div class="container">
        <h3 class="mb-4">Add New Author</h3>
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
        <form action="add.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" class="form-control" id="name" name="name"
                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address:</label>
                <textarea class="form-control" id="address" name="address"
                    rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone:</label>
                <input type="tel" class="form-control" id="phone" name="phone"
                    value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Author</button>
        </form>
    </div>

    <footer class="mt-5 py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>
