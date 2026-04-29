<?php
require_once '../includes/functions.php';
require_admin_login();
require_once '../includes/dbconnection.php';

$name = "";
$name_err = $success_msg = $error_msg = "";

// Check for success/error messages from redirects
if (isset($_GET['success'])) {
    $success_msg = sanitize_input($_GET['success']);
}
if (isset($_GET['error'])) {
    $error_msg = sanitize_input($_GET['error']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $name = sanitize_input($_POST['name'] ?? '');

    // Basic validations
    if (empty($name)) {
        $name_err = "Author name is required.";
    } else {
        // Check if author already exists
        $stmt_check = $conn->prepare("SELECT id FROM authors WHERE name = ?");
        $stmt_check->bind_param("s", $name);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $name_err = "Author already exists.";
        }
        $stmt_check->close();
    }

    if (empty($name_err)) {
        // Insert new author
        $stmt = $conn->prepare("INSERT INTO authors (name) VALUES (?)");
        $stmt->bind_param("s", $name);

        if ($stmt->execute()) {
            $success_msg = "Author added successfully.";
            $name = ""; // Clear input
        } else {
            $error_msg = "Error adding author: " . $stmt->error;
        }
        $stmt->close();
    }
}

$search = sanitize_input($_GET['search'] ?? '');
$sort = sanitize_input($_GET['sort'] ?? 'name');
$order = sanitize_input($_GET['order'] ?? 'ASC');
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Validate sort and order parameters
$allowed_sorts = ['name'];
$allowed_orders = ['ASC', 'DESC'];

if (!in_array($sort, $allowed_sorts)) {
    $sort = 'name';
}
if (!in_array($order, $allowed_orders)) {
    $order = 'ASC';
}

// Prepare search condition
if (!empty($search)) {
    $search_param = "%$search%";
    $stmt_total = $conn->prepare("SELECT COUNT(*) FROM authors WHERE name LIKE ?");
    $stmt_total->bind_param("s", $search_param);
    $stmt_total->execute();
    $stmt_total->bind_result($total_authors);
    $stmt_total->fetch();
    $stmt_total->close();

    $stmt = $conn->prepare("SELECT id, name FROM authors WHERE name LIKE ? ORDER BY name LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $search_param, $limit, $offset);
} else {
    $result_total = $conn->query("SELECT COUNT(*) AS total FROM authors");
    $row_total = $result_total->fetch_assoc();
    $total_authors = $row_total['total'] ?? 0;

    $stmt = $conn->prepare("SELECT id, name FROM authors ORDER BY name LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

$total_pages = ceil($total_authors / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCC Library Admin Panel - Authors</title>
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
                    <a href="books_list.php">
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
                    <a href="authors.php" class="active">
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
        <div class="categories-layout">
            <div class="left-column">
                <header>
                    <h1>Author Management</h1>
                    <p>Add new authors and manage existing ones.</p>
                </header>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>

                <!-- Add Author Form -->
                <div class="form-section">
                    <h2>Add New Author</h2>
                    <form action="authors.php" method="post">
                        <div class="form-group">
                            <label for="name">Author Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter author name">
                            <i class="fas fa-user-edit input-icon"></i>
                            <?php if ($name_err): ?>
                                <span class="error"><?php echo $name_err; ?></span>
                            <?php endif; ?>
                        </div>

                        <button type="submit">
                            <i class="fas fa-plus-circle"></i>
                            Add Author
                        </button>
                    </form>
                </div>
            </div>

            <div class="right-column">
                <!-- Existing Authors -->
                <h2>Existing Authors</h2>

                <div class="search-container">
                    <form method="get" action="authors.php" class="search-form">
                        <input type="text" name="search" placeholder="Search authors by name" value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="authors.php" class="clear-btn">
                                <i class="fas fa-times"></i>
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <table class="students-table-container">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><a href="?page=1&search=<?php echo urlencode($search); ?>&sort=name&order=<?php echo ($sort == 'name' && $order == 'ASC') ? 'DESC' : 'ASC'; ?>" class="sort-link">Author Name<?php if ($sort == 'name') echo '<i class="fas fa-sort-' . ($order == 'ASC' ? 'up' : 'down') . '"></i>'; else echo '<i class="fas fa-sort"></i>'; ?></a></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><span class="username-badge"><i class="fas fa-user-edit"></i><?php echo htmlspecialchars($row['name']); ?></span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="author_edit.php?id=<?php echo $row['id']; ?>" class="action-btn edit"><i class="fas fa-edit"></i>Edit</a>
                                            <a href="author_delete.php?id=<?php echo $row['id']; ?>" class="action-btn delete" onclick="return confirm('Are you sure you want to delete this author?')"><i class="fas fa-trash"></i>Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="no-results"><i class="fas fa-user-edit"></i><br>No authors found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <nav class="pagination">
                        <?php for ($i=1; $i <= $total_pages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <strong><?php echo $i; ?></strong>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </nav>
                <?php endif; ?>
            </div>
        </div>

        <?php $stmt->close(); ?>
    </div>
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date("Y"); ?> SCC Library. develop By: alalong.</p>
    </footer>
</body>
</html>
