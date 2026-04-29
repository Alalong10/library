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

// Get report data
$report_type = $_GET['type'] ?? 'overview';

// Overview Statistics
$total_students = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$total_issued = $conn->query("SELECT COUNT(*) as count FROM issued_books WHERE return_date IS NULL")->fetch_assoc()['count'];
$total_returns = $conn->query("SELECT COUNT(*) as count FROM issued_books WHERE return_date IS NOT NULL")->fetch_assoc()['count'];

// Monthly statistics for the last 6 months
$monthly_stats = $conn->query("
    SELECT
        DATE_FORMAT(date_registered, '%Y-%m') as month,
        COUNT(*) as books_added
    FROM books
    WHERE date_registered >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(date_registered, '%Y-%m')
    ORDER BY month DESC
")->fetch_all(MYSQLI_ASSOC);

// Popular categories
$popular_categories = $conn->query("
    SELECT c.name, COUNT(b.id) as book_count
    FROM categories c
    LEFT JOIN books b ON c.id = b.category_id
    GROUP BY c.id, c.name
    ORDER BY book_count DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Books by quality
$quality_stats = $conn->query("
    SELECT quality, COUNT(*) as count
    FROM books
    GROUP BY quality
    ORDER BY count DESC
")->fetch_all(MYSQLI_ASSOC);

// Overdue books
$overdue_books = $conn->query("
    SELECT b.title, s.name as student_name, s.student_id, ib.due_date,
           DATEDIFF(CURDATE(), ib.due_date) as days_overdue
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN students s ON ib.student_id = s.id
    WHERE ib.return_date IS NULL AND ib.due_date < CURDATE()
    ORDER BY days_overdue DESC
    LIMIT 20
")->fetch_all(MYSQLI_ASSOC);

// Penalties incurred
$penalties = $conn->query("
    SELECT b.title, s.name as student_name, s.student_id, ib.return_date, ib.penalty,
           DATEDIFF(ib.return_date, ib.due_date) as days_late
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN students s ON ib.student_id = s.id
    WHERE ib.return_date IS NOT NULL AND ib.penalty > 0
    ORDER BY ib.return_date DESC
    LIMIT 20
")->fetch_all(MYSQLI_ASSOC);

// Most active students
$active_students = $conn->query("
    SELECT s.name, s.student_id, COUNT(ib.id) as books_issued
    FROM students s
    LEFT JOIN issued_books ib ON s.id = ib.student_id
    GROUP BY s.id, s.name, s.student_id
    ORDER BY books_issued DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Books never issued
$unused_books = $conn->query("
    SELECT b.title, a.name as author, c.name as category, b.date_registered
    FROM books b
    JOIN authors a ON b.author_id = a.id
    JOIN categories c ON b.category_id = c.id
    WHERE b.id NOT IN (SELECT DISTINCT book_id FROM issued_books)
    ORDER BY b.date_registered DESC
    LIMIT 20
")->fetch_all(MYSQLI_ASSOC);

// Recent activity (last 30 days)
$recent_activity = $conn->query("
    SELECT
        'issue' as type,
        CONCAT(s.name, ' (', s.student_id, ') issued ', b.title) as description,
        ib.issue_date as date
    FROM issued_books ib
    JOIN students s ON ib.student_id = s.id
    JOIN books b ON ib.book_id = b.id
    WHERE ib.issue_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)

    UNION ALL

    SELECT
        'return' as type,
        CONCAT(s.name, ' (', s.student_id, ') returned ', b.title) as description,
        ib.return_date as date
    FROM issued_books ib
    JOIN students s ON ib.student_id = s.id
    JOIN books b ON ib.book_id = b.id
    WHERE ib.return_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)

    ORDER BY date DESC
    LIMIT 50
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
    <div class="page-header">
        <h1><i class="fas fa-chart-pie"></i> Library Reports & Analytics</h1>
        <p>Comprehensive insights into library operations and performance</p>
    </div>

    <!-- Report Navigation -->
    <div class="report-nav">
        <a href="?type=overview" class="nav-btn <?php echo ($report_type == 'overview') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Overview
        </a>
        <a href="?type=books" class="nav-btn <?php echo ($report_type == 'books') ? 'active' : ''; ?>">
            <i class="fas fa-book"></i> Book Reports
        </a>
        <a href="?type=students" class="nav-btn <?php echo ($report_type == 'students') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Student Reports
        </a>
        <a href="?type=circulation" class="nav-btn <?php echo ($report_type == 'circulation') ? 'active' : ''; ?>">
            <i class="fas fa-exchange-alt"></i> Circulation
        </a>
        <a href="?type=activity" class="nav-btn <?php echo ($report_type == 'activity') ? 'active' : ''; ?>">
            <i class="fas fa-history"></i> Recent Activity
        </a>
    </div>

    <?php if ($report_type == 'overview'): ?>
        <!-- Overview Dashboard -->
        <div class="overview-grid">
            <div class="metric-card">
                <div class="metric-icon students">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value"><?php echo number_format($total_students); ?></div>
                    <div class="metric-label">Total Students</div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon books">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value"><?php echo number_format($total_books); ?></div>
                    <div class="metric-label">Total Books</div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon issued">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value"><?php echo number_format($total_issued); ?></div>
                    <div class="metric-label">Books Issued</div>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon returns">
                    <i class="fas fa-undo"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-value"><?php echo number_format($total_returns); ?></div>
                    <div class="metric-label">Total Returns</div>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <!-- Monthly Book Additions -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Books Added (Last 6 Months)</h3>
                </div>
                <div class="chart-content">
                    <canvas id="monthlyChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Popular Categories -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Books by Category</h3>
                </div>
                <div class="chart-content">
                    <canvas id="categoriesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

    <?php elseif ($report_type == 'books'): ?>
        <!-- Book Reports -->
        <div class="report-section">
            <h2>Book Collection Analysis</h2>

            <!-- Quality Distribution -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Books by Quality</h3>
                </div>
                <div class="chart-content">
                    <canvas id="qualityChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Unused Books -->
            <div class="data-table-card">
                <div class="table-header">
                    <h3>Books Never Issued (<?php echo count($unused_books); ?>)</h3>
                    <div class="table-actions">
                        <button onclick="exportTable('unused-books-table')" class="export-btn">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="table-content">
                    <table id="unused-books-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unused_books as $book): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td><?php echo htmlspecialchars($book['category']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($book['date_registered']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php elseif ($report_type == 'students'): ?>
        <!-- Student Reports -->
        <div class="report-section">
            <h2>Student Activity Analysis</h2>

            <!-- Most Active Students -->
            <div class="data-table-card">
                <div class="table-header">
                    <h3>Most Active Students</h3>
                    <div class="table-actions">
                        <button onclick="exportTable('active-students-table')" class="export-btn">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="table-content">
                    <table id="active-students-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Books Issued</th>
                                <th>Activity Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($active_students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['books_issued']); ?></td>
                                    <td>
                                        <span class="activity-badge <?php
                                            echo $student['books_issued'] >= 10 ? 'high' :
                                                 ($student['books_issued'] >= 5 ? 'medium' : 'low');
                                        ?>">
                                            <?php
                                            echo $student['books_issued'] >= 10 ? 'High' :
                                                 ($student['books_issued'] >= 5 ? 'Medium' : 'Low');
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php elseif ($report_type == 'circulation'): ?>
        <!-- Circulation Reports -->
        <div class="report-section">
            <h2>Circulation Management</h2>

            <!-- Overdue Books -->
            <div class="data-table-card">
                <div class="table-header">
                    <h3>Overdue Books (<?php echo count($overdue_books); ?>)</h3>
                    <div class="table-actions">
                        <button onclick="exportTable('overdue-books-table')" class="export-btn">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="table-content">
                    <table id="overdue-books-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Student</th>
                                <th>Due Date</th>
                                <th>Days Overdue</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($overdue_books as $book): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['student_id'] . ' - ' . $book['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($book['due_date']))); ?></td>
                                    <td><?php echo htmlspecialchars($book['days_overdue']); ?></td>
                                    <td>
                                        <span class="status-badge overdue">
                                            <?php echo $book['days_overdue'] > 30 ? 'Severely Overdue' : 'Overdue'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Penalties Incurred -->
            <div class="data-table-card">
                <div class="table-header">
                    <h3>Penalties Incurred (<?php echo count($penalties); ?>)</h3>
                    <div class="table-actions">
                        <button onclick="exportTable('penalties-table')" class="export-btn">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div class="table-content">
                    <table id="penalties-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Student</th>
                                <th>Return Date</th>
                                <th>Days Late</th>
                                <th>Penalty Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($penalties as $penalty): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($penalty['title']); ?></td>
                                    <td><?php echo htmlspecialchars($penalty['student_id'] . ' - ' . $penalty['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($penalty['return_date']))); ?></td>
                                    <td><?php echo htmlspecialchars($penalty['days_late']); ?></td>
                                    <td>₱<?php echo number_format($penalty['penalty'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php elseif ($report_type == 'activity'): ?>
        <!-- Recent Activity -->
        <div class="report-section">
            <h2>Recent Library Activity (Last 30 Days)</h2>

            <div class="activity-timeline">
                <?php foreach ($recent_activity as $activity): ?>
                    <div class="activity-item <?php echo $activity['type']; ?>">
                        <div class="activity-icon">
                            <i class="fas fa-<?php echo $activity['type'] == 'issue' ? 'hand-holding' : 'undo'; ?>"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></div>
                            <div class="activity-date"><?php echo htmlspecialchars(date('M d, Y H:i', strtotime($activity['date']))); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Books Chart
const monthlyCtx = document.getElementById('monthlyChart');
if (monthlyCtx) {
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column(array_reverse($monthly_stats), 'month')); ?>,
            datasets: [{
                label: 'Books Added',
                data: <?php echo json_encode(array_column(array_reverse($monthly_stats), 'books_added')); ?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Categories Chart
const categoriesCtx = document.getElementById('categoriesChart');
if (categoriesCtx) {
    new Chart(categoriesCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($popular_categories, 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($popular_categories, 'book_count')); ?>,
                backgroundColor: [
                    '#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1',
                    '#e83e8c', '#fd7e14', '#20c997', '#6c757d', '#17a2b8'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Quality Chart
const qualityCtx = document.getElementById('qualityChart');
if (qualityCtx) {
    new Chart(qualityCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($quality_stats, 'quality')); ?>,
            datasets: [{
                label: 'Number of Books',
                data: <?php echo json_encode(array_column($quality_stats, 'count')); ?>,
                backgroundColor: '#28a745',
                borderColor: '#1e7e34',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Export table to CSV
function exportTable(tableId) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    let csv = [];

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cols = row.querySelectorAll('td, th');
        const rowData = [];

        for (let j = 0; j < cols.length; j++) {
            // Skip status/activity columns for export
            if (!cols[j].querySelector('.status-badge, .activity-badge')) {
                rowData.push('"' + cols[j].textContent.trim() + '"');
            }
        }

        csv.push(rowData.join(','));
    }

    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = tableId + '_export.csv';
    a.click();

    window.URL.revokeObjectURL(url);
}
</script>


<?php
require_once 'footer.php';
?>
