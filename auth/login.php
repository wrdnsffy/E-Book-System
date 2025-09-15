<?php
require_once '../includes/init.php';

// Redirect if user is already logged in
if (is_logged_in()) {
    redirect('index.php');
}

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
            $conn = db_connect();
            $sql = "SELECT id, c_password FROM customer WHERE c_email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($user = mysqli_fetch_assoc($result)) {
                if (verify_password($password, $user['c_password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    log_action($user['id'], 'login', 'User logged in');
                    redirect('index.php');
                } else {
                    $errors[] = "Invalid email or password.";
                }
            } else {
                $errors[] = "Invalid email or password.";
            }

            mysqli_stmt_close($stmt);
            mysqli_close($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <header>
        <nav class="navbar navbar-expand-sm bg-dark navbar-dark"
            style="background-image: linear-gradient(#eeaeca, #94bbe9);padding-left:10px;">
            <h1 style="color:brown"><?php echo SITE_NAME; ?></h1>
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">

                    <ul>
                        <li class="nav-item active"><a class="nav-link" href="../index.php">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container">
        <h2>Login</h2>
        <?php display_errors($errors); ?>
        <form action="login.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <input type="submit" value="Login">
            </div>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
    </footer>
</body>

</html>