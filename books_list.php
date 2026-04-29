<?php
require_once '../includes/dbconnection.php';
require_once '../includes/functions.php';
require_admin_login();

$message = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: books_list.php");
    exit();
}

$book_id = (int)$_GET['id'];

// Get book details
$stmt = $conn->prepare("
    SELECT b.*, a.name as author_name, c.name as category_name
    FROM books b
    JOIN authors a ON b.author_id = a.id
    JOIN categories c ON b.category_id = c.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $message = '<div class="alert alert-danger">Book not found.</div>';
} else {
    $book = $result->fetch_assoc();
}
$stmt->close();

// Get current issue status and available copies
$issued_count = 0;
$available_copies = $book['quantity'];
$issue_status = 'Available';
$current_borrowers = [];

$stmt = $conn->prepare("SELECT COUNT(*) FROM issued_books WHERE book_id = ? AND return_date IS NULL");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$stmt->bind_result($issued_count);
$stmt->fetch();
$stmt->close();

$available_copies = $book['quantity'] - $issued_count;

if ($issued_count > 0) {
    $issue_status = 'Issued';
    $stmt = $conn->prepare("
        SELECT s.student_id, s.name, ib.issue_date, ib.due_date
        FROM issued_books ib
        JOIN students s ON ib.student_id = s.id
        WHERE ib.book_id = ? AND ib.return_date IS NULL
        ORDER BY ib.issue_date DESC
    ");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_borrowers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Get issue history
$stmt = $conn->prepare("
    SELECT s.student_id, s.name, ib.issue_date, ib.return_date, ib.due_date
    FROM issued_books ib
    JOIN students s ON ib.student_id = s.id
    WHERE ib.book_id = ?
    ORDER BY ib.issue_date DESC
");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$history_result = $stmt->get_result();
$issue_history = $history_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SCC Library Admin Panel - Book Details</title>
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
    <h2>Book Details</h2>

    <?php echo $message; ?>

    <?php if (isset($book)): ?>
        <div class="book-details-container">
            <div class="book-header">
                <div class="book-title-section">
                    <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                    <div class="book-meta">
                        <span class="isbn-display">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></span>
                        <span class="quality-badge quality-<?php echo strtolower($book['quality']); ?>">
                            <?php echo htmlspecialchars($book['quality']); ?>
                        </span>
                    </div>
                </div>
                <div class="book-actions">
                    <a href="books_add.php?id=<?php echo $book['id']; ?>" class="action-btn edit">
                        <i class="fas fa-edit"></i> Edit Book
                    </a>
                    <a href="book_delete.php?id=<?php echo $book['id']; ?>" class="action-btn delete"
                       onclick="return confirm('Are you sure you want to delete this book?')">
                        <i class="fas fa-trash"></i> Delete Book
                    </a>
                </div>
            </div>

            <div class="book-info-grid">
                <div class="info-section">
                    <h4>Book Information</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Author:</label>
                            <span><?php echo htmlspecialchars($book['author_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Category:</label>
                            <span><?php echo htmlspecialchars($book['category_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Date Published:</label>
                            <span><?php echo htmlspecialchars($book['date_published']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Date Registered:</label>
                            <span><?php echo htmlspecialchars($book['date_registered']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="info-section">
                    <h4>Current Status</h4>
                    <div class="status-summary">
                        <div class="status-item">
                            <label>Overall Status:</label>
                            <span class="status-badge status-<?php echo strtolower($issue_status); ?>">
                                <?php echo $issue_status; ?> (<?php echo $available_copies; ?> of <?php echo $book['quantity']; ?> available)
                            </span>
                        </div>
                    </div>
                    <?php if (empty($current_borrowers)): ?>
                        <p class="no-history">No books are currently issued.</p>
                    <?php else: ?>
                        <div class="history-table-container">
                            <table class="history-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($current_borrowers as $borrower): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($borrower['student_id'] . ' - ' . $borrower['name']); ?></td>
                                            <td><?php echo htmlspecialchars($borrower['issue_date']); ?></td>
                                            <td><?php echo htmlspecialchars($borrower['due_date']); ?></td>
                                            <td>
                                                <span class="status-badge status-issued">
                                                    Issued
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($book['description'])): ?>
                <div class="info-section">
                    <h4>Description</h4>
                    <p class="book-description"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                </div>
            <?php endif; ?>

            <div class="info-section">
                <h4>Issue History</h4>
                <?php if (empty($issue_history)): ?>
                    <p class="no-history">This book has never been issued.</p>
                <?php else: ?>
                    <div class="history-table-container">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($issue_history as $history): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($history['student_id'] . ' - ' . $history['name']); ?></td>
                                        <td><?php echo htmlspecialchars($history['issue_date']); ?></td>
                                        <td><?php echo htmlspecialchars($history['due_date']); ?></td>
                                        <td><?php echo $history['return_date'] ? htmlspecialchars($history['return_date']) : '-'; ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $history['return_date'] ? 'returned' : 'issued'; ?>">
                                                <?php echo $history['return_date'] ? 'Returned' : 'Issued'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="action-buttons">
            <a href="books_list.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Books List
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'footer.php';
?>
