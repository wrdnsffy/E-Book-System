<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();

$errors = [];
$success_message = '';
$publisher = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $publisher_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($publisher_id === false) {
        $errors[] = "Invalid publisher ID.";
    } else {
        try {
            $query = "SELECT * FROM publisher WHERE id = ?";
            $stmt = db_prepare($query);
            $stmt->bind_param("i", $publisher_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $publisher = $result->fetch_assoc();

            if (!$publisher) {
                $errors[] = "Publisher not found.";
            }

            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Error retrieving publisher details: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Invalid request.";
    } else {
        $publisher_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
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

        if (empty($errors)) {
            try {
                $sql = "UPDATE publisher SET p_name = ?, p_address = ?, p_state = ?, p_phone = ?, p_email = ? WHERE id = ?";
                $stmt = db_prepare($sql);
                $stmt->bind_param("sssssi", $name, $address, $state, $phone, $email, $publisher_id);

                if ($stmt->execute()) {
                    log_action($_SESSION['user_id'], 'publisher_updated', "Updated publisher: $name (ID: $publisher_id)");
                    $success_message = "Publisher updated successfully!";

                    // Refresh publisher data
                    $publisher = [
                        'id' => $publisher_id,
                        'p_name' => $name,
                        'p_address' => $address,
                        'p_state' => $state,
                        'p_phone' => $phone,
                        'p_email' => $email
                    ];
                } else {
                    throw new Exception("Error updating publisher.");
                }

                $stmt->close();
            } catch (Exception $e) {
                $errors[] = "Error updating publisher: " . $e->getMessage();
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
    <title>Edit Publisher - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <?php include_once ('../includes/admin_menu.php') ?>
    <div class="container">
        <h3 class="mb-4">Edit Publisher</h3>

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

        <?php if ($publisher): ?>
            <form action="edit.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="id" value="<?php echo $publisher['id']; ?>">

                <div class="mb-3">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="<?php echo htmlspecialchars($publisher['p_name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">Address:</label>
                    <input type="text" class="form-control" id="address" name="address"
                        value="<?php echo htmlspecialchars($publisher['p_address']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="state" class="form-label">State:</label>
                    <input type="text" class="form-control" id="state" name="state"
                        value="<?php echo htmlspecialchars($publisher['p_state']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">Phone:</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                        value="<?php echo htmlspecialchars($publisher['p_phone']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo htmlspecialchars($publisher['p_email']); ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Update Publisher</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </form>
        <?php else: ?>
            <p>No publisher found to edit.</p>
            <a href="list.php" class="btn btn-primary">Back to Publisher List</a>
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