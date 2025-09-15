<?php
require_once '../includes/init.php';

ensure_user_is_logged_in();

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'book_title';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

// Validate sort column to prevent SQL injection
$allowed_sort_columns = ['book_title', 'author_name', 'publisher_name', 'price'];
if (!in_array($sort, $allowed_sort_columns)) {
    $sort = 'book_title';
}

// Create the ORDER BY clause
$order_by = "ORDER BY $sort $order";

try {
    // Get total number of books
    $total_result = db_query("SELECT COUNT(*) as total FROM book");
    $total_books = mysqli_fetch_assoc($total_result)['total'];
    $total_pages = ceil($total_books / $per_page);

    // Fetch books with sorting
    $books_query = "SELECT b.*, a.a_name AS author_name, p.p_name AS publisher_name 
                    FROM book b
                    JOIN author a ON b.a_id = a.id
                    JOIN publisher p ON b.p_id = p.id
                    $order_by 
                    LIMIT ? OFFSET ?";
    $stmt = db_prepare($books_query);
    mysqli_stmt_bind_param($stmt, "ii", $per_page, $offset);
    mysqli_stmt_execute($stmt);
    $books_result = mysqli_stmt_get_result($stmt);
} catch (Exception $e) {
    $error_message = "Error fetching books: " . $e->getMessage();
}

// Function to generate sorting links
function sort_link($column, $title)
{
    global $sort, $order, $page;
    $new_order = ($sort === $column && $order === 'ASC') ? 'DESC' : 'ASC';
    $class = ($sort === $column) ? ($order === 'ASC' ? 'sort-asc' : 'sort-desc') : '';
    return "<a style='text-decoration:none;color:#000' href='?sort=$column&order=$new_order&page=$page' class='$class'>$title</a>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book List - <?php echo SITE_NAME; ?></title>
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

    <?php include_once ('../Includes/admin_menu.php') ?>
    <div class="container">
        <h3 class="mb-4">Book List</h3>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php else: ?>
            <a href="add.php" class="btn btn-secondary btn-sm mb-3">Add New Book</a>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><?php echo sort_link('book_title', 'Title'); ?></th>
                            <th><?php echo sort_link('author_name', 'Author'); ?></th>
                            <th><?php echo sort_link('publisher_name', 'Publisher'); ?></th>
                            <th><?php echo sort_link('isbn', 'ISBN'); ?></th>
                            <th><?php echo sort_link('price', 'Price'); ?></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = mysqli_fetch_assoc($books_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['book_title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                                <td><?php echo htmlspecialchars($book['publisher_name']); ?></td>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td>RM<?php echo number_format($book['price'], 2); ?></td>
                                <td style="text-align: right;">
                                    <a href="details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-info">View</a>
                                    <a href="edit.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="delete.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav aria-label="Book list pagination">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link"
                                href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
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