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
<?php

$isbn = $title = $description = $author = $category = $quality = $date_published = $date_registered = "";
$quantity = 1;
$isbn_err = $title_err = $description_err = $author_err = $category_err = $quality_err = $date_published_err = $date_registered_err = $quantity_err = "";
$success_msg = $error_msg = "";
$editing = false;
$book_id = 0;

if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_msg = "Book registered successfully.";
} elseif (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $success_msg = "Book updated successfully.";
}

// Check if editing
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $book_id = (int)$_GET['id'];
    $editing = true;

    // Load book data
    $stmt_load = $conn->prepare("SELECT b.isbn, b.title, b.description, a.name as author, c.name as category, b.quality, b.date_published, b.date_registered, b.quantity FROM books b JOIN authors a ON b.author_id = a.id JOIN categories c ON b.category_id = c.id WHERE b.id = ?");
    $stmt_load->bind_param("i", $book_id);
    $stmt_load->execute();
    $result_load = $stmt_load->get_result();

    if ($result_load->num_rows > 0) {
        $book_data = $result_load->fetch_assoc();
        $isbn = $book_data['isbn'];
        $title = $book_data['title'];
        $description = $book_data['description'];
        $author = $book_data['author'];
        $category = $book_data['category'];
        $quality = $book_data['quality'];
        $date_published = $book_data['date_published'];
        $date_registered = $book_data['date_registered'];
        $quantity = $book_data['quantity'];
    } else {
        $error_msg = "Book not found.";
        $editing = false;
    }
    $stmt_load->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $isbn = sanitize_input($_POST['isbn'] ?? '');
    $title = sanitize_input($_POST['title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $author = sanitize_input($_POST['author'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $quality = sanitize_input($_POST['quality'] ?? '');
    $date_published = sanitize_input($_POST['date_published'] ?? '');
    $date_registered = sanitize_input($_POST['date_registered'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);

    // Basic validations
    if (empty($isbn)) {
        $isbn_err = "ISBN is required.";
    } else {
        // Check if ISBN is unique
        $stmt_check_isbn = $conn->prepare("SELECT id FROM books WHERE isbn = ? AND id != ?");
        $stmt_check_isbn->bind_param("si", $isbn, $book_id);
        $stmt_check_isbn->execute();
        $stmt_check_isbn->store_result();
        if ($stmt_check_isbn->num_rows > 0) {
            $isbn_err = "ISBN already exists.";
        }
        $stmt_check_isbn->close();
    }
    if (empty($title)) {
        $title_err = "Book title is required.";
    }
    if (empty($description)) {
        $description_err = "Description is required.";
    }
    if (empty($author)) {
        $author_err = "Author is required.";
    }
    if (empty($category)) {
        $category_err = "Category is required.";
    }
    if (empty($quality)) {
        $quality_err = "Quality is required.";
    }
    if (empty($date_published)) {
        $date_published_err = "Date published is required.";
    }
    if ($quantity < 1) {
        $quantity_err = "Quantity must be at least 1.";
    }

    if (empty($isbn_err) && empty($title_err) && empty($description_err) && empty($author_err) && empty($category_err) && empty($quality_err) && empty($date_published_err) && empty($quantity_err)) {
        // Check if author exists, if not create it
        $stmt_check_author = $conn->prepare("SELECT id FROM authors WHERE name = ?");
        $stmt_check_author->bind_param("s", $author);
        $stmt_check_author->execute();
        $stmt_check_author->store_result();
        if ($stmt_check_author->num_rows > 0) {
            $stmt_check_author->bind_result($author_id);
            $stmt_check_author->fetch();
        } else {
            // Insert new author
            $stmt_insert_author = $conn->prepare("INSERT INTO authors (name) VALUES (?)");
            $stmt_insert_author->bind_param("s", $author);
            $stmt_insert_author->execute();
            $author_id = $conn->insert_id;
            $stmt_insert_author->close();
        }
        $stmt_check_author->close();

        // Check if category exists, if not create it
        $stmt_check_category = $conn->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt_check_category->bind_param("s", $category);
        $stmt_check_category->execute();
        $stmt_check_category->store_result();
        if ($stmt_check_category->num_rows > 0) {
            $stmt_check_category->bind_result($category_id);
            $stmt_check_category->fetch();
        } else {
            // Insert new category
            $stmt_insert_category = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt_insert_category->bind_param("s", $category);
            $stmt_insert_category->execute();
            $category_id = $conn->insert_id;
            $stmt_insert_category->close();
        }
        $stmt_check_category->close();

        if ($editing) {
            // Update existing book
            $stmt = $conn->prepare("UPDATE books SET isbn = ?, title = ?, description = ?, author_id = ?, category_id = ?, quality = ?, date_published = ?, date_registered = ?, quantity = ? WHERE id = ?");
            $stmt->bind_param("sssiisssii", $isbn, $title, $description, $author_id, $category_id, $quality, $date_published, $date_registered, $quantity, $book_id);

            if ($stmt->execute()) {
                // Redirect to avoid resubmission on refresh
                header("Location: books_add.php?updated=1");
                exit();
            } else {
                $error_msg = "Error updating book: " . $stmt->error;
            }
        } else {
            // Insert new book
            $stmt = $conn->prepare("INSERT INTO books (isbn, title, description, author_id, category_id, quality, date_published, date_registered, quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiisssi", $isbn, $title, $description, $author_id, $category_id, $quality, $date_published, $date_registered, $quantity);

            if ($stmt->execute()) {
                // Redirect to avoid resubmission on refresh
                header("Location: books_add.php?success=1");
                exit();
            } else {
                $error_msg = "Error registering book: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>
<div class="container">
    <header>
        <h1><?php echo $editing ? 'Edit Book' : 'Book Registration'; ?></h1>
        <p><?php echo $editing ? 'Update the book information below.' : 'Enter the details below to register a new book in the library system.'; ?></p>
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

    <form action="books_add.php<?php echo $editing ? '?id=' . $book_id : ''; ?>" method="post">
        <div class="form-group">
            <label for="isbn">ISBN</label>
            <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($isbn); ?>" placeholder="Enter ISBN number">
            <?php if ($isbn_err): ?>
                <span class="error"><?php echo $isbn_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="title">Book Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" placeholder="Enter book title">
            <?php if ($title_err): ?>
                <span class="error"><?php echo $title_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Enter book description"><?php echo htmlspecialchars($description); ?></textarea>
            <?php if ($description_err): ?>
                <span class="error"><?php echo $description_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="author">Author</label>
            <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($author); ?>" placeholder="Enter author name">
            <?php if ($author_err): ?>
                <span class="error"><?php echo $author_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category">
                <option value="">Select Category</option>
                <?php
                $result_categories = $conn->query("SELECT id, name FROM categories ORDER BY name");
                while ($row = $result_categories->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($row['name']) . '" ' . ($category == $row['name'] ? 'selected' : '') . '>' . htmlspecialchars($row['name']) . '</option>';
                }
                ?>
            </select>
            <?php if ($category_err): ?>
                <span class="error"><?php echo $category_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="quality">Quality</label>
            <select id="quality" name="quality">
                <option value="">Select Quality</option>
                <option value="New" <?php echo ($quality == 'New') ? 'selected' : ''; ?>>New</option>
                <option value="Good" <?php echo ($quality == 'Good') ? 'selected' : ''; ?>>Good</option>
                <option value="Fair" <?php echo ($quality == 'Fair') ? 'selected' : ''; ?>>Fair</option>
                <option value="Poor" <?php echo ($quality == 'Poor') ? 'selected' : ''; ?>>Poor</option>
            </select>
            <?php if ($quality_err): ?>
                <span class="error"><?php echo $quality_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="date_published">Date Published</label>
            <input type="date" id="date_published" name="date_published" value="<?php echo htmlspecialchars($date_published); ?>">
            <?php if ($date_published_err): ?>
                <span class="error"><?php echo $date_published_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="date_registered">Date Registered</label>
            <input type="date" id="date_registered" name="date_registered" value="<?php echo htmlspecialchars($date_registered ?: date('Y-m-d')); ?>">
            <?php if ($date_registered_err): ?>
                <span class="error"><?php echo $date_registered_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($quantity); ?>" min="1" placeholder="Enter quantity">
            <?php if ($quantity_err): ?>
                <span class="error"><?php echo $quantity_err; ?></span>
            <?php endif; ?>
        </div>

        <button type="submit">
            <i class="fas fa-<?php echo $editing ? 'edit' : 'plus-circle'; ?>"></i>
            <?php echo $editing ? 'Update Book' : 'Register Book'; ?>
        </button>
    </form>

    <div style="margin-top: 20px;">
        <a href="books_list.php" class="reset-btn" style="text-decoration: none; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Back to Books List
        </a>
    </div>
</div>

    </div>
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date("Y"); ?> SCC Library. develop By: alalong.</p>
    </footer>
</body>
</html>
