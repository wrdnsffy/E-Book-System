<?php
require_once '../includes/init.php';

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Invalid request.";
    } else {
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];

        // Validate email
        if (empty($email) || !is_valid_email($email)) {
            $errors[] = "Please enter a valid email address.";
        }

        // Validate password
        if (empty($password)) {
            $errors[] = "Please enter your password.";
        }

        // If no errors, attempt to log in
        if (empty($errors)) {
            try {
                $sql = "SELECT id, s_email, s_password FROM staff WHERE s_email = ?";
                $stmt = db_prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($staff = $result->fetch_assoc()) {
                    if (password_verify($password, $staff['s_password'])) {
                        // Login successful
                        $_SESSION['user_id'] = $staff['id'];
                        $_SESSION['user_email'] = $staff['s_email'];
                        $_SESSION['user_role'] = 'admin'; // Set admin role
                        log_action($staff['id'], 'admin_login', 'Admin logged in');
                        redirect('admin/dashboard.php');
                    } else {
                        $errors[] = "Invalid email or password.";
                    }
                } else {
                    $errors[] = "Invalid email or password.";
                }

                $stmt->close();
            } catch (Exception $e) {
                $errors[] = "Login error: " . $e->getMessage();
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
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <h2 class="mb-4">Admin Login</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="admin_login.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>
