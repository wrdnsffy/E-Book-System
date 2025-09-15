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
        $address = sanitize_input($_POST['address']);
        $state = sanitize_input($_POST['state']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);

        // Validate fields
        if (empty($name)) {
            $errors[] = "Please enter a publisher name.";
        }
        if (empty($address)) {
            $errors[] = "Please enter an address.";
        }
        if (empty($state)) {
            $errors[] = "Please enter a state.";
        }
        if (empty($phone)) {
            $errors[] = "Please enter a phone number.";
        }
        if (empty($email) || !is_valid_email($email)) {
            $errors[] = "Please enter a valid email address.";
        }

        // If no errors, insert the publisher
        if (empty($errors)) {
            try {
                $sql = "INSERT INTO publisher (p_name, p_address, p_state, p_phone, p_email) VALUES (?, ?, ?, ?, ?)";
                $stmt = db_prepare($sql);
                mysqli_stmt_bind_param($stmt, "sssss", $name, $address, $state, $phone, $email);

                if (mysqli_stmt_execute($stmt)) {
                    $publisher_id = mysqli_insert_id(db_connect());
                    log_action($_SESSION['user_id'], 'publisher_added', "Added publisher: $name (ID: $publisher_id)");
                    $success_message = "Publisher added successfully!";
                    // Clear form fields after successful submission
                    $_POST = array();
                } else {
                    throw new Exception("Error adding publisher.");
                }

                mysqli_stmt_close($stmt);
            } catch (Exception $e) {
                $errors[] = "Error adding publisher: " . $e->getMessage();
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
    <title>Add New Publisher - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include_once ('../includes/admin_menu.php') ?>

    <div class="container">
        <h3 class="mb-4">Add New Publisher</h3>
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
                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address:</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="state" class="form-label">State:</label>
                <input type="text" class="form-control" id="state" name="state" value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone:</label>
                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Publisher</button>
        </form>
    </div>

    <footer class="mt-5 py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src=../js/bootstrap.bundle.min.js"></script>
</body>
</html>
