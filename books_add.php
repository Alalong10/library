<?php
require_once '../includes/dbconnection.php';
require_once '../includes/functions.php';
require_admin_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_msg = $error_msg = "";

if ($id > 0) {
    // Check if book exists
    $stmt_check = $conn->prepare("SELECT title FROM books WHERE id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->bind_result($book_title);
        $stmt_check->fetch();
        $stmt_check->close();

        // Delete book
        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $success_msg = "Book '" . htmlspecialchars($book_title) . "' deleted successfully.";
        } else {
            $error_msg = "Error deleting book: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_msg = "Book not found.";
    }
} else {
    $error_msg = "Invalid book ID.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Book - SCC Library Admin Panel</title>
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
        <div class="container">
            <header>
                <h1>Delete Book</h1>
                <p>Book deletion result.</p>
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

            <div class="form-section">
                <a href="books_list.php" class="action-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Books List
                </a>
            </div>
        </div>
    </div>

    <footer class="dashboard-footer">
        <p>&copy; <?php echo date("Y"); ?> SCC Library. develop By: alalong.</p>
    </footer>
</body>
</html>
