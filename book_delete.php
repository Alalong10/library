<?php
require_once '../includes/dbconnection.php';
require_once '../includes/functions.php';
require_admin_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCC Library Admin Panel - Edit Author</title>
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
<?php

$name = "";
$name_err = $success_msg = $error_msg = "";
$edit_id = null;

// Check if editing
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $edit_id = (int)$_GET['id'];

    // Load existing author data
    $stmt = $conn->prepare("SELECT name FROM authors WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $author = $result->fetch_assoc();
        $name = $author['name'];
    } else {
        header("Location: authors.php");
        exit();
    }
    $stmt->close();
} else {
    header("Location: authors.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $name = sanitize_input($_POST['name'] ?? '');

    // Basic validations
    if (empty($name)) {
        $name_err = "Author name is required.";
    } else {
        // Check if author name is unique (exclude current record)
        $stmt_check = $conn->prepare("SELECT id FROM authors WHERE name = ? AND id != ?");
        $stmt_check->bind_param("si", $name, $edit_id);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $name_err = "Author name already exists.";
        }
        $stmt_check->close();
    }

    if (empty($name_err)) {
        // Update existing author
        $stmt = $conn->prepare("UPDATE authors SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $name, $edit_id);

        if ($stmt->execute()) {
            $success_msg = "Author updated successfully.";
            // Redirect to authors list after successful update
            header("Location: authors.php");
            exit();
        } else {
            $error_msg = "Error updating author: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<div class="container">
    <header>
        <h1>Edit Author</h1>
        <p>Update the author information below.</p>
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

    <form action="author_edit.php?id=<?php echo $edit_id; ?>" method="post">
        <div class="form-group">
            <label for="name">Author Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter author name">
            <i class="fas fa-user-edit input-icon"></i>
            <?php if ($name_err): ?>
                <span class="error"><?php echo $name_err; ?></span>
            <?php endif; ?>
        </div>

        <button type="submit">
            <i class="fas fa-save"></i>
            Update Author
        </button>
    </form>

    <div style="margin-top: 20px;">
        <a href="authors.php" class="reset-btn" style="text-decoration: none; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Back to Authors
        </a>
    </div>
</div>

    </div>
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date("Y"); ?> SCC Library. develop By: alalong.</p>
    </footer>
</body>
</html>
