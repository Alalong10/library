<?php
require_once '../includes/functions.php';
require_admin_login();
require_once '../includes/dbconnection.php';

$search = sanitize_input($_GET['search'] ?? '');
$sort = sanitize_input($_GET['sort'] ?? 'title');
$order = sanitize_input($_GET['order'] ?? 'ASC');
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Validate sort and order parameters
$allowed_sorts = ['title', 'author', 'category', 'isbn', 'quality', 'date_published'];
$allowed_orders = ['ASC', 'DESC'];

if (!in_array($sort, $allowed_sorts)) {
    $sort = 'title';
}
if (!in_array($order, $allowed_orders)) {
    $order = 'ASC';
}

// Map sort to actual column
$order_by = 'b.title'; // default
switch ($sort) {
    case 'title': $order_by = 'b.title'; break;
    case 'author': $order_by = 'a.name'; break;
    case 'category': $order_by = 'c.name'; break;
    case 'isbn': $order_by = 'b.isbn'; break;
    case 'quality': $order_by = 'b.quality'; break;
    case 'date_published': $order_by = 'b.date_published'; break;
}

// Prepare search condition
$search_sql = "";
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt_total = $conn->prepare("SELECT COUNT(*) FROM books b JOIN authors a ON b.author_id = a.id JOIN categories c ON b.category_id = c.id WHERE b.title LIKE ? OR a.name LIKE ? OR b.isbn LIKE ? OR c.name LIKE ?");
    $stmt_total->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
    $stmt_total->execute();
    $stmt_total->bind_result($total_books);
    $stmt_total->fetch();
    $stmt_total->close();

    $stmt = $conn->prepare("SELECT b.id, b.title, a.name as author, c.name as category, b.isbn, b.quality, b.date_published, b.quantity FROM books b JOIN authors a ON b.author_id = a.id JOIN categories c ON b.category_id = c.id WHERE b.title LIKE ? OR a.name LIKE ? OR b.isbn LIKE ? OR c.name LIKE ? ORDER BY $order_by $order LIMIT ? OFFSET ?");
    $stmt->bind_param("ssssii", $search_param, $search_param, $search_param, $search_param, $limit, $offset);
} else {
    $result_total = $conn->query("SELECT COUNT(*) AS total FROM books");
    $row_total = $result_total->fetch_assoc();
    $total_books = $row_total['total'] ?? 0;

    $stmt = $conn->prepare("SELECT b.id, b.title, a.name as author, c.name as category, b.isbn, b.quality, b.date_published, b.quantity FROM books b JOIN authors a ON b.author_id = a.id JOIN categories c ON b.category_id = c.id ORDER BY $order_by $order LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$total_pages = ceil($total_books / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCC Library Admin Panel - Books List</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="../assets/image/scc1.png" alt="Library Logo">
            </div>
            <div class="sidebar-title">
                <h2>SCC Library</h2>
                <p>Admin Panel</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="students_list.php">
                        <i class="fas fa-users"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li>
                    <a href="books_list.php" class="active">
                        <i class="fas fa-book"></i>
                        <span>Books</span>
                    </a>
                </li>
                <li>
                    <a href="categories.php">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <li>
                    <a href="authors.php">
                        <i class="fas fa-user-edit"></i>
                        <span>Authors</span>
                    </a>
                </li>
                <li>
                    <a href="issue_book.php">
                        <i class="fas fa-hand-holding"></i>
                        <span>Issue Book</span>
                    </a>
                </li>
                <li>
                    <a href="return_book.php">
                        <i class="fas fa-undo"></i>
                        <span>Return Book</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <button class="sidebar-toggle">
            <i class="fas fa-angle-left"></i>
        </button>
    </aside>

    <div class="admin-main">
        <h2 class="students-list-title">Books List</h2>

        <div class="search-container">
            <form method="get" action="books_list.php" class="search-form">
                <input type="text" name="search" placeholder="Search books by title, author, ISBN, or category" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">
                    <i class="fas fa-search"></i>
                    Search
                </button>
                <?php if (!empty($search)): ?>
                    <a href="books_list.php" class="clear-btn">
                        <i class="fas fa-times"></i>
                        Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <table class="students-table-container">
            <thead>
                <tr>
                    <th><a href="?sort=isbn&order=<?php echo ($sort == 'isbn' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&page=1&search=<?php echo urlencode($search); ?>" class="sort-link">ISBN <i class="fas fa-sort<?php echo ($sort == 'isbn') ? ($order == 'ASC' ? '-up' : '-down') : ''; ?>"></i></a></th>
                    <th><a href="?sort=title&order=<?php echo ($sort == 'title' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&page=1&search=<?php echo urlencode($search); ?>" class="sort-link">Title <i class="fas fa-sort<?php echo ($sort == 'title') ? ($order == 'ASC' ? '-up' : '-down') : ''; ?>"></i></a></th>
                    <th><a href="?sort=author&order=<?php echo ($sort == 'author' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&page=1&search=<?php echo urlencode($search); ?>" class="sort-link">Author <i class="fas fa-sort<?php echo ($sort == 'author') ? ($order == 'ASC' ? '-up' : '-down') : ''; ?>"></i></a></th>
                    <th><a href="?sort=category&order=<?php echo ($sort == 'category' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&page=1&search=<?php echo urlencode($search); ?>" class="sort-link">Category <i class="fas fa-sort<?php echo ($sort == 'category') ? ($order == 'ASC' ? '-up' : '-down') : ''; ?>"></i></a></th>
                    <th><a href="?sort=quality&order=<?php echo ($sort == 'quality' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&page=1&search=<?php echo urlencode($search); ?>" class="sort-link">Quality <i class="fas fa-sort<?php echo ($sort == 'quality') ? ($order == 'ASC' ? '-up' : '-down') : ''; ?>"></i></a></th>
                    <th><a href="?sort=date_published&order=<?php echo ($sort == 'date_published' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>&page=1&search=<?php echo urlencode($search); ?>" class="sort-link">Date Published <i class="fas fa-sort<?php echo ($sort == 'date_published') ? ($order == 'ASC' ? '-up' : '-down') : ''; ?>"></i></a></th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><span class="isbn-badge"><i class="fas fa-barcode"></i><?php echo htmlspecialchars($row['isbn']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['author']); ?></td>
                            <td><span class="category-badge"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td><span class="quality-badge quality-<?php echo strtolower($row['quality']); ?>"><?php echo htmlspecialchars($row['quality']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['date_published']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="book_view.php?id=<?php echo $row['id']; ?>" class="action-btn view"><i class="fas fa-eye"></i>View</a>
                                    <a href="books_add.php?id=<?php echo $row['id']; ?>" class="action-btn edit"><i class="fas fa-edit"></i>Edit</a>
                                    <a href="book_delete.php?id=<?php echo $row['id']; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this book?')"><i class="fas fa-trash"></i>Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="no-results"><i class="fas fa-book"></i><br>No books found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <nav class="pagination">
                <?php for ($i=1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <strong><?php echo $i; ?></strong>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>

        <?php $stmt->close(); ?>
    </div>
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date("Y"); ?> SCC Library. develop By: alalong.</p>
    </footer>
</body>
</html>
