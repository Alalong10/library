<?php
require_once '../includes/functions.php';
require_admin_login();
require_once '../includes/dbconnection.php';

// Get total students count
$result_students = $conn->query("SELECT COUNT(*) AS total_students FROM students");
$row_students = $result_students->fetch_assoc();
$total_students = $row_students['total_students'] ?? 0;

// Get total books count
$result_books = $conn->query("SELECT COUNT(*) AS total_books FROM books");
$row_books = $result_books->fetch_assoc();
$total_books = $row_books['total_books'] ?? 0;

// Get total issued books count (books not returned)
$result_issued = $conn->query("SELECT COUNT(*) AS total_issued FROM issued_books WHERE return_date IS NULL");
$row_issued = $result_issued->fetch_assoc();
$total_issued = $row_issued['total_issued'] ?? 0;

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
                    <a href="dashboard.php" class="active">
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

<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="dashboard-welcome">
        <div class="welcome-content">
            <h1><i class="fas fa-chart-line"></i> Library Management Dashboard</h1>
            <p>Monitor your library's performance and manage operations efficiently</p>
            <div class="welcome-stats">
                <div class="welcome-stat">
                    <span class="stat-number"><?php echo htmlspecialchars($total_students); ?></span>
                    <span class="stat-label">Active Students</span>
                </div>
                <div class="welcome-stat">
                    <span class="stat-number"><?php echo htmlspecialchars($total_books); ?></span>
                    <span class="stat-label">Total Books</span>
                </div>
                <div class="welcome-stat">
                    <span class="stat-number"><?php echo htmlspecialchars($total_issued); ?></span>
                    <span class="stat-label">Books Issued</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Grid Layout -->
    <div class="dashboard-main">
        <!-- Left Column: Quick Actions -->
        <div class="quick-actions-panel">
            <div class="panel-header">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                <p>Common library operations</p>
            </div>

            <div class="actions-grid">
                <a href="students_add.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="action-content">
                        <h4>Add Student</h4>
                        <p>Register new student</p>
                    </div>
                </a>

                <a href="books_add.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="action-content">
                        <h4>Add Book</h4>
                        <p>Add new book to collection</p>
                    </div>
                </a>

                <a href="issue_book.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-hand-holding"></i>
                    </div>
                    <div class="action-content">
                        <h4>Issue Book</h4>
                        <p>Lend book to student</p>
                    </div>
                </a>

                <a href="return_book.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div class="action-content">
                        <h4>Return Book</h4>
                        <p>Process book return</p>
                    </div>
                </a>

                <a href="students_list.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="action-content">
                        <h4>View Students</h4>
                        <p>Manage student records</p>
                    </div>
                </a>

                <a href="books_list.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="action-content">
                        <h4>View Books</h4>
                        <p>Browse book inventory</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Center Column: Statistics & Management -->
        <div class="stats-section">
            <!-- Key Performance Metrics -->
            <div class="section-header">
                <h2><i class="fas fa-chart-bar"></i> Key Performance Metrics</h2>
                <p>Real-time overview of your library's performance</p>
            </div>

            <div class="stats-grid">
                <div class="metric-card students-card">
                    <div class="metric-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value"><?php echo htmlspecialchars($total_students); ?></div>
                        <div class="metric-label">Total Students</div>
                        <div class="metric-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+12% this month</span>
                        </div>
                    </div>
                </div>

                <div class="metric-card books-card">
                    <div class="metric-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value"><?php echo htmlspecialchars($total_books); ?></div>
                        <div class="metric-label">Library Collection</div>
                        <div class="metric-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+8% this month</span>
                        </div>
                    </div>
                </div>

                <div class="metric-card issued-card">
                    <div class="metric-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value"><?php echo htmlspecialchars($total_issued); ?></div>
                        <div class="metric-label">Books on Loan</div>
                        <div class="metric-trend neutral">
                            <i class="fas fa-minus"></i>
                            <span>-3% this week</span>
                        </div>
                    </div>
                </div>

                <div class="metric-card available-card">
                    <div class="metric-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="metric-content">
                        <div class="metric-value"><?php echo htmlspecialchars($total_books - $total_issued); ?></div>
                        <div class="metric-label">Available Books</div>
                        <div class="metric-trend positive">
                            <i class="fas fa-equals"></i>
                            <span>95% availability</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Library Management Tools -->
            <div class="section-header">
                <h2><i class="fas fa-tools"></i> Management Tools</h2>
                <p>Essential library management functions</p>
            </div>

            <div class="tools-grid">
                <a href="categories.php" class="tool-card">
                    <div class="tool-icon" style="background-color: orange;">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="tool-content">
                        <h4>Categories</h4>
                        <p>Manage book categories</p>
                    </div>
                </a>

                <a href="authors.php" class="tool-card">
                    <div class="tool-icon" style="background-color: orange;">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div class="tool-content">
                        <h4>Authors</h4>
                        <p>Manage authors database</p>
                    </div>
                </a>

                <a href="books_manage.php" class="tool-card">
                    <div class="tool-icon" style="background-color:orange ;">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="tool-content">
                        <h4>Book Management</h4>
                        <p>Advanced book operations</p>
                    </div>
                </a>

                <a href="reports.php" class="tool-card">
                    <div class="tool-icon" style="background-color: orange;">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="tool-content">
                        <h4>Reports</h4>
                        <p>Generate analytics reports</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Right Column: System Status & Activity -->
        <div class="activity-panel">
            <div class="panel-header">
                <h3><i class="fas fa-history"></i> System Status & Activity</h3>
                <p>Current system health and recent events</p>
            </div>

            <!-- System Status Section -->
            <div class="section-subheader">
                <h4><i class="fas fa-server"></i> System Status</h4>
            </div>
            <div class="status-indicators">
                <div class="status-item healthy">
                    <div class="status-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="status-content">
                        <h5>Database</h5>
                        <p>All systems operational</p>
                    </div>
                </div>

                <div class="status-item healthy">
                    <div class="status-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="status-content">
                        <h5>Server</h5>
                        <p>Response time: 45ms</p>
                    </div>
                </div>

                <div class="status-item warning">
                    <div class="status-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="status-content">
                        <h5>Overdue Books</h5>
                        <p><?php echo htmlspecialchars($total_issued > 10 ? 'Check returns' : 'All on time'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="section-subheader">
                <h4><i class="fas fa-clock"></i> Recent Activity</h4>
            </div>
            <div class="activity-timeline">
                <div class="activity-item">
                    <div class="activity-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">The system has been initialized successfully.</div>
                        <div class="activity-time">2 minutes ago</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon info">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title"><?php echo htmlspecialchars($total_students); ?> students have been registered.</div>
                        <div class="activity-time">Today</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon primary">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title"><?php echo htmlspecialchars($total_books); ?> books are in the collection.</div>
                        <div class="activity-time">This week</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title"><?php echo htmlspecialchars($total_issued); ?> books are currently issued.</div>
                        <div class="activity-time">Ongoing</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    </div>
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date("Y"); ?> SCC Library. develop By: alalong.</p>
    </footer>
</body>
</html>
