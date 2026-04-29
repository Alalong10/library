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
    <title>SCC Library Admin Panel</title>
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
                    <a href="categories.php" class="active">
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
<?php

$name = $description = "";
$name_err = $description_err = $success_msg = $error_msg = "";
$edit_id = null;

// Check if editing
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $edit_id = (int)$_GET['id'];

    // Load existing category data
    $stmt = $conn->prepare("SELECT name, description FROM categories WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
        $name = $category['name'];
        $description = $category['description'];
    } else {
        header("Location: categories.php");
        exit();
    }
    $stmt->close();
} else {
    header("Location: categories.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $name = sanitize_input($_POST['name'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');

    // Basic validations
    if (empty($name)) {
        $name_err = "Category name is required.";
    } else {
        // Check if category name is unique (exclude current record)
        $stmt_check = $conn->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
        $stmt_check->bind_param("si", $name, $edit_id);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $name_err = "Category name already exists.";
        }
        $stmt_check->close();
    }

    if (empty($name_err)) {
        // Update existing category
        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $edit_id);

        if ($stmt->execute()) {
            $success_msg = "Category updated successfully.";
            // Redirect to categories list after successful update
            header("Location: categories.php");
            exit();
        } else {
            $error_msg = "Error updating category: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<div class="container">
    <header>
        <h1>Edit Category</h1>
        <p>Update the category information below.</p>
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

    <form action="category_edit.php?id=<?php echo $edit_id; ?>" method="post">
        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter category name">
            <?php if ($name_err): ?>
                <span class="error"><?php echo $name_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Enter category description"><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <button type="submit">
            <i class="fas fa-save"></i>
            Update Category
        </button>
    </form>

    <div style="margin-top: 20px;">
        <a href="categories.php" class="reset-btn" style="text-decoration: none; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Back to Categories
        </a>
    </div>
</div>

    </div>
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date("Y"); ?> SCC Library. develop By: alalong.</p>
    </footer>
</body>
</html>
