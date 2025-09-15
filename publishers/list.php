<?php
require_once '../includes/init.php';

// Fetch publishers from the database
$sql = "SELECT * FROM publisher ORDER BY p_name";
$result = db_query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publishers - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    
</head>

<body>
    <?php include_once ('../Includes/admin_menu.php') ?>

    <div class="container">
        <h3>Publishers</h3>
        <a href="add.php" class="btn btn-sm btn-secondary mb-3">Add New Publisher</a>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>State</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['p_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['p_address']); ?></td>
                        <td><?php echo htmlspecialchars($row['p_state']); ?></td>
                        <td><?php echo htmlspecialchars($row['p_phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['p_email']); ?></td>
                        <td style="text-align:right;">
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure you want to delete this publisher?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <footer class="mt-5 py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>