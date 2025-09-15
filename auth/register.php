<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

session_start();

// Redirect if user is already logged in
if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$name = $email = $phone = $address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = "Invalid request.";
    } else {
        // Sanitize and validate input
        $name = sanitize_input($_POST['name']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $address = sanitize_input($_POST['address']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate name
        if (empty($name)) {
            $errors[] = "Please enter your name.";
        }

        // Validate email
        if (empty($email) || !is_valid_email($email)) {
            $errors[] = "Please enter a valid email address.";
        } else {
            // Check if email already exists
            $conn = db_connect();
            $sql = "SELECT id FROM customer WHERE c_email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $errors[] = "This email address is already registered.";
            }
            mysqli_stmt_close($stmt);
        }

        // Validate phone
        if (empty($phone)) {
            $errors[] = "Please enter your phone number.";
        }

        // Validate address
        if (empty($address)) {
            $errors[] = "Please enter your address.";
        }

        // Validate password
        if (empty($password)) {
            $errors[] = "Please enter a password.";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }

        // Validate password confirmation
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }

        // If no errors, create the account
        if (empty($errors)) {
            $hashed_password = hash_password($password);

            $sql = "INSERT INTO customer (c_name, c_email, c_password, c_phone, c_address) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashed_password, $phone, $address);

            if (mysqli_stmt_execute($stmt)) {
                $user_id = mysqli_insert_id($conn);
                log_action($user_id, 'register', 'New user registered');
                $_SESSION['user_id'] = $user_id;
                redirect('index.php');
            } else {
                $errors[] = "Registration failed. Please try again later.";
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
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="../css/style.css">
		<script src="../js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</head>
<body>
    <header>
        <h1><?php echo SITE_NAME; ?></h1>
        <nav class="navbar navbar-expand-sm bg-dark navbar-dark"
        style="background-image: linear-gradient(#eeaeca, #94bbe9);">
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h2>Register</h2>
        <?php display_errors($errors); ?>
        <form action="register.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div>
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div>
                <label for="address">Address:</label>
                <textarea id="address" name="address" required><?php echo htmlspecialchars($address); ?></textarea>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div>
                <input type="submit" value="Register">
            </div>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
    </footer>
</body>
</html>