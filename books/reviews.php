<?php
require_once 'includes/init.php';

$errors = [];
$success_message = '';

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

// Validate sort column to prevent SQL injection
$allowed_sort_columns = ['created_at', 'r_rating', 'book_title', 'c_name'];
if (!in_array($sort, $allowed_sort_columns)) {
    $sort = 'created_at';
}

// Create the ORDER BY clause
$order_by = "ORDER BY $sort $order";

try {
    // Get total number of reviews
    $total_result = db_query("SELECT COUNT(*) as total FROM review");
    $total_reviews = mysqli_fetch_assoc($total_result)['total'];
    $total_pages = ceil($total_reviews / $per_page);

    // Fetch reviews
    $reviews_query = "SELECT r.*, b.book_title, c.c_name 
                      FROM review r
                      JOIN book b ON r.b_id = b.id
                      JOIN customer c ON r.c_id = c.id
                      $order_by 
                      LIMIT ? OFFSET ?";
    $stmt = db_prepare($reviews_query);
    $stmt->bind_param("ii", $per_page, $offset);
    $stmt->execute();
    $reviews_result = $stmt->get_result();

    // Handle review actions (approve or delete)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf_token($_POST['csrf_token'])) {
            throw new Exception("Invalid request.");
        }

        $review_id = filter_var($_POST['review_id'], FILTER_VALIDATE_INT);
        $action = $_POST['action'];

        // if ($review_id && in_array($action, ['approve', 'delete'])) {
        //     if ($action === 'approve') {
        //         $update_query = "UPDATE review SET is_approved = 1 WHERE id = ?";
        //     } else {
        //         $update_query = "DELETE FROM review WHERE id = ?";
        //     }

        //     $update_stmt = db_prepare($update_query);
        //     $update_stmt->bind_param("i", $review_id);

        //     if ($update_stmt->execute()) {
        //         $success_message = "Review " . ($action === 'approve' ? "approved" : "deleted") . " successfully.";
        //     } else {
        //         throw new Exception("Error processing review action.");
        //     }
        // }
    }
} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

// Function to generate sorting links
function sort_link($column, $title)
{
    global $sort, $order, $page;
    $new_order = ($sort === $column && $order === 'ASC') ? 'DESC' : 'ASC';
    $class = ($sort === $column) ? ($order === 'ASC' ? 'sort-asc' : 'sort-desc') : '';
    return "<a href='?sort=$column&order=$new_order&page=$page' class='$class'>$title</a>";
}
?>

<table class="table table-striped">
    <thead>
        <tr>
            <th><?php echo sort_link('book_title', 'Book Title'); ?></th>
            <th><?php echo sort_link('c_name', 'Customer'); ?></th>
            <th><?php echo sort_link('r_rating', 'Rating'); ?></th>
            <th>Review Text</th>
            <th><?php echo sort_link('created_at', 'Date'); ?></th>
            <!-- <th>Actions</th> -->
        </tr>
    </thead>
    <tbody>
        <?php while ($review = $reviews_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($review['book_title']); ?></td>
                <td><?php echo htmlspecialchars($review['c_name']); ?></td>
                <td><?php echo $review['r_rating']; ?></td>
                <td><?php echo htmlspecialchars(substr($review['r_text'], 0, 100)) . '...'; ?></td>
                <td><?php echo date('Y-m-d', strtotime($review['created_at'])); ?></td>
                <!-- <td>
                    <form action="review_list.php" method="post" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                        <?php if (!$review['is_approved']): ?>
                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                        <?php endif; ?>
                        <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger"
                            onclick="return confirm('Are you sure you want to delete this review?')">Delete</button>
                    </form>
                    <a href="../books/details.php?id=<?php echo $review['b_id']; ?>" class="btn btn-sm btn-info">View
                        Book</a>
                </td> -->
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>