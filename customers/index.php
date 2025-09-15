<?php
require_once '../includes/init.php';

ensure_admin();

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

// Validate sort column to prevent SQL injection
$allowed_sort_columns = ['id', 'c_name', 'c_email', 'c_phone'];
if (!in_array($sort, $allowed_sort_columns)) {
    $sort = 'id';
}

// Create the ORDER BY clause
$order_by = "ORDER BY $sort $order";

try {
    // Fetch customers
    $query = "SELECT * FROM customer $order_by";
    $result = db_query($query);
    $customers = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error_message = "Error fetching customers: " . $e->getMessage();
}

// Function to generate sorting links
function sort_link($column, $title)
{
    global $sort, $order;
    $new_order = ($sort === $column && $order === 'ASC') ? 'DESC' : 'ASC';
    $class = ($sort === $column) ? ($order === 'ASC' ? 'sort-asc' : 'sort-desc') : '';
    return "<a style='text-decoration:none;color:#000;' href='?sort=$column&order=$new_order' class='$class'>$title</a>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer List - <?php echo SITE_NAME; ?></title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sort-asc::after {
            content: " ▲";
        }

        .sort-desc::after {
            content: " ▼";
        }
    </style>
</head>

<body>
    <?php include '../includes/admin_menu.php'; ?>

    <div class="container mt-4">
        <h3>Customer List</h3>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><?php echo sort_link('id', 'ID'); ?></th>
                        <th><?php echo sort_link('c_name', 'Name'); ?></th>
                        <th><?php echo sort_link('c_email', 'Email'); ?></th>
                        <th><?php echo sort_link('c_phone', 'Phone'); ?></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['id']); ?></td>
                            <td><?php echo htmlspecialchars($customer['c_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['c_email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['c_phone']); ?></td>
                            <td style="text-align: right;">
                                <a href="customer_books.php?id=<?php echo $customer['id']; ?>"
                                    class="btn btn-sm btn-primary">View Purchases</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>

</html>