<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();

$errors = [];
$success_message = '';
$author = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $author_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($author_id === false) {
        $errors[] = "Invalid author ID.";
    } else {
        try {
            $query = "SELECT * FROM author WHERE id = ?";
            $stmt = db_prepare($query);
            $stmt->bind_param("i", $author_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $author = $result->fetch_assoc();

            if (!$author) {
                $errors[] = "Author not found.";
            }

            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Error retrieving author details: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Invalid request.";
    } else {
        $author_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
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

        if (empty($errors)) {
            try {
                $sql = "UPDATE author SET a_name = ?, a_email = ?, address = ?, phone = ? WHERE id = ?";
                $stmt = db_prepare($sql);
                $stmt->bind_param("ssssi", $name, $email, $address, $phone, $author_id);

                if ($stmt->execute()) {
                    log_action($_SESSION['user_id'], 'author_updated', "Updated author: $name (ID: $author_id)");
                    $success_message = "Author updated successfully!";

                    // Refresh author data
                    $author = [
                        'id' => $author_id,
                        'a_name' => $name,
                        'a_email' => $email,
                        'address' => $address,
                        'phone' => $phone
                    ];
                } else {
                    throw new Exception("Error updating author.");
                }

                $stmt->close();
            } catch (Exception $e) {
                $errors[] = "Error updating author: " . $e->getMessage();
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
    <title>Edit Author - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include_once ('../Includes/admin_menu.php') ?>
    <div class="container">
        <h3 class="mb-4">Edit Author</h3>
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

        <?php if ($author): ?>
            <form action="edit.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="id" value="<?php echo $author['id']; ?>">

                <div class="mb-3">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="<?php echo htmlspecialchars($author['a_name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo htmlspecialchars($author['a_email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address:</label>
                    <textarea class="form-control" id="address"
                        name="address"><?php echo htmlspecialchars($author['address']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone:</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                        value="<?php echo htmlspecialchars($author['phone']); ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Update Author</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </form>
        <?php else: ?>
            <p>No author found to edit.</p>
            <a href="list.php" class="btn btn-primary">Back to Author List</a>
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