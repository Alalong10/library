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

// Get book statistics
$result_total = $conn->query("SELECT COUNT(*) AS total FROM books");
$row_total = $result_total->fetch_assoc();
$total_books = $row_total['total'] ?? 0;

$result_issued = $conn->query("SELECT COUNT(*) AS issued FROM issued_books WHERE return_date IS NULL");
$row_issued = $result_issued->fetch_assoc();
$issued_books = $row_issued['issued'] ?? 0;

$result_available = $conn->query("SELECT COUNT(*) AS available FROM books WHERE id NOT IN (SELECT book_id FROM issued_books WHERE return_date IS NULL)");
$row_available = $result_available->fetch_assoc();
$available_books = $row_available['available'] ?? 0;

// Get recent book additions
$result_recent = $conn->query("
    SELECT b.title, a.name as author, b.date_registered
    FROM books b
    JOIN authors a ON b.author_id = a.id
    ORDER BY b.date_registered DESC
    LIMIT 5
");

// Get books due soon (next 7 days)
$result_due_soon = $conn->query("
    SELECT b.title, s.name as student_name, s.student_id, ib.due_date
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN students s ON ib.student_id = s.id
    WHERE ib.return_date IS NULL
    AND ib.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY ib.due_date ASC
    LIMIT 10
");

// Get overdue books
$result_overdue = $conn->query("
    SELECT b.title, s.name as student_name, s.student_id, ib.due_date,
           DATEDIFF(CURDATE(), ib.due_date) as days_overdue
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN students s ON ib.student_id = s.id
    WHERE ib.return_date IS NULL
    AND ib.due_date < CURDATE()
    ORDER BY ib.due_date ASC
    LIMIT 10
");
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-cogs"></i> Book Management</h1>
        <p>Comprehensive book inventory and circulation management</p>
    </div>

    <!-- Quick Stats -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo htmlspecialchars($total_books); ?></div>
                <div class="stat-label">Total Books</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon available">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo htmlspecialchars($available_books); ?></div>
                <div class="stat-label">Available</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon issued">
                <i class="fas fa-hand-holding"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo htmlspecialchars($issued_books); ?></div>
                <div class="stat-label">Currently Issued</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon overdue">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?php echo htmlspecialchars($result_overdue->num_rows); ?></div>
                <div class="stat-label">Overdue</div>
            </div>
        </div>
    </div>

    <!-- Management Actions -->
    <div class="management-actions">
        <h2>Quick Actions</h2>
        <div class="actions-grid">
            <a href="books_add.php" class="action-btn primary">
                <i class="fas fa-plus-circle"></i>
                <span>Add New Book</span>
            </a>

            <a href="books_list.php" class="action-btn secondary">
                <i class="fas fa-list"></i>
                <span>View All Books</span>
            </a>

            <a href="issue_book.php" class="action-btn success">
                <i class="fas fa-hand-holding"></i>
                <span>Issue Book</span>
            </a>

            <a href="return_book.php" class="action-btn warning">
                <i class="fas fa-undo"></i>
                <span>Return Book</span>
            </a>

            <a href="categories.php" class="action-btn info">
                <i class="fas fa-tags"></i>
                <span>Manage Categories</span>
            </a>

            <a href="authors.php" class="action-btn info">
                <i class="fas fa-user-edit"></i>
                <span>Manage Authors</span>
            </a>
        </div>
    </div>

    <div class="management-grid">
        <!-- Recent Additions -->
        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-clock"></i> Recently Added Books</h3>
                <a href="books_list.php?sort=recent" class="view-all">View All</a>
            </div>
            <div class="section-content">
                <?php if ($result_recent->num_rows > 0): ?>
                    <div class="recent-books-list">
                        <?php while ($book = $result_recent->fetch_assoc()): ?>
                            <div class="recent-book-item">
                                <div class="book-info">
                                    <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                                    <p>by <?php echo htmlspecialchars($book['author']); ?></p>
                                </div>
                                <div class="book-date">
                                    <small>Added: <?php echo htmlspecialchars(date('M d, Y', strtotime($book['date_registered']))); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">No books added recently.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Books Due Soon -->
        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-calendar-alt"></i> Due Soon (Next 7 Days)</h3>
                <a href="books_list.php?filter=due_soon" class="view-all">View All</a>
            </div>
            <div class="section-content">
                <?php if ($result_due_soon->num_rows > 0): ?>
                    <div class="due-books-list">
                        <?php while ($book = $result_due_soon->fetch_assoc()): ?>
                            <div class="due-book-item">
                                <div class="book-info">
                                    <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($book['student_id'] . ' - ' . $book['student_name']); ?></p>
                                </div>
                                <div class="due-date <?php echo (strtotime($book['due_date']) < time()) ? 'overdue' : 'due-soon'; ?>">
                                    <small>Due: <?php echo htmlspecialchars(date('M d, Y', strtotime($book['due_date']))); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">No books due in the next 7 days.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Overdue Books -->
        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Overdue Books</h3>
                <a href="books_list.php?filter=overdue" class="view-all">View All</a>
            </div>
            <div class="section-content">
                <?php if ($result_overdue->num_rows > 0): ?>
                    <div class="overdue-books-list">
                        <?php while ($book = $result_overdue->fetch_assoc()): ?>
                            <div class="overdue-book-item">
                                <div class="book-info">
                                    <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($book['student_id'] . ' - ' . $book['student_name']); ?></p>
                                </div>
                                <div class="overdue-info">
                                    <small>Overdue by <?php echo htmlspecialchars($book['days_overdue']); ?> days</small>
                                    <small>Due: <?php echo htmlspecialchars(date('M d, Y', strtotime($book['due_date']))); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">No overdue books. Great job!</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Book Categories Overview -->
        <div class="management-section">
            <div class="section-header">
                <h3><i class="fas fa-tags"></i> Categories Overview</h3>
                <a href="categories.php" class="view-all">Manage</a>
            </div>
            <div class="section-content">
                <?php
                $result_categories = $conn->query("
                    SELECT c.name, COUNT(b.id) as book_count
                    FROM categories c
                    LEFT JOIN books b ON c.id = b.category_id
                    GROUP BY c.id, c.name
                    ORDER BY book_count DESC
                    LIMIT 8
                ");
                ?>
                <?php if ($result_categories->num_rows > 0): ?>
                    <div class="categories-grid">
                        <?php while ($category = $result_categories->fetch_assoc()): ?>
                            <div class="category-item">
                                <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                                <div class="category-count"><?php echo htmlspecialchars($category['book_count']); ?> books</div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data">No categories found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
require_once 'footer.php';
?>
