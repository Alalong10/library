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
                    <a href="students_list.php" class="active">
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

// Check if student ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: students_list.php");
    exit();
}

$student_id = (int)$_GET['id'];
$message = '';

// Get student details
$stmt = $conn->prepare("SELECT student_id, name, address, age, gender, course, civil_status, username, email FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: students_list.php");
    exit();
}

$student = $result->fetch_assoc();
$stmt->close();

// Get issued books count
$stmt = $conn->prepare("SELECT COUNT(*) as issued_books FROM issued_books WHERE student_id = ? AND return_date IS NULL");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($issued_books);
$stmt->fetch();
$stmt->close();

// Get total books returned
$stmt = $conn->prepare("SELECT COUNT(*) as returned_books FROM issued_books WHERE student_id = ? AND return_date IS NOT NULL");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($returned_books);
$stmt->fetch();
$stmt->close();
?>

<div class="container">
    <h2>Student Details</h2>

    <?php echo $message; ?>

    <div class="student-profile">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($student['name']); ?></h2>
                <p>Student ID: <?php echo htmlspecialchars($student['student_id']); ?></p>
            </div>
        </div>

        <div class="profile-details">
            <div class="detail-item">
                <h4 style="display: flex; align-items: center;"><i class="fas fa-envelope" style="margin-right: 8px;"></i> Email :</h4>
                <p><?php echo htmlspecialchars($student['email'] ?: 'Not provided'); ?></p>
            </div>
            <div class="detail-item">
                <h4 style="display: flex; align-items: center;"><i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i> Address :</h4>
                <p><?php echo htmlspecialchars($student['address'] ?: 'Not provided'); ?></p>
            </div>
            <div class="detail-item">
                <h4 style="display: flex; align-items: center;"><i class="fas fa-birthday-cake" style="margin-right: 8px;"></i> Age :</h4>
                <p><?php echo htmlspecialchars($student['age'] ?: 'Not specified'); ?></p>
            </div>
            <div class="detail-item">
                <h4 style="display: flex; align-items: center;"><i class="fas fa-venus-mars" style="margin-right: 8px;"></i> Gender :</h4>
                <p><?php echo htmlspecialchars($student['gender'] ?: 'Not specified'); ?></p>
            </div>
            <div class="detail-item">
                <h4 style="display: flex; align-items: center;"><i class="fas fa-graduation-cap" style="margin-right: 8px;"></i> Course :</h4>
                <p><?php echo htmlspecialchars($student['course'] ?: 'Not specified'); ?></p>
            </div>
            <div class="detail-item">
                <h4 style="display: flex; align-items: center;"><i class="fas fa-ring" style="margin-right: 8px;"></i> Civil Status :</h4>
                <p><?php echo htmlspecialchars($student['civil_status'] ?: 'Not specified'); ?></p>
            </div>
            <div class="detail-item">
                <h4 style="display: flex; align-items: center;"><i class="fas fa-user" style="margin-right: 8px;"></i> Username :</h4>
                <p><?php echo htmlspecialchars($student['username']); ?></p>
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="dashboard-stats">
        <div class="stat-box">
            <div class="stat-icon">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stat-info">
                <h3>Books Issued</h3>
                <p><?php echo $issued_books; ?></p>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-icon">
                <i class="fas fa-undo"></i>
            </div>
            <div class="stat-info">
                <h3>Books Returned</h3>
                <p><?php echo $returned_books; ?></p>
            </div>
        </div>
    </div>

    <!-- Currently Issued Books -->
    <div class="quick-links">
        <h3>Currently Issued Books</h3>
        <?php
        $stmt = $conn->prepare("
            SELECT b.title, b.isbn, ib.issue_date, ib.due_date
            FROM issued_books ib
            JOIN books b ON ib.book_id = b.id
            WHERE ib.student_id = ? AND ib.return_date IS NULL
            ORDER BY ib.issue_date DESC
        ");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $issued_result = $stmt->get_result();
        ?>

        <?php if ($issued_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>ISBN</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($book = $issued_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($book['issue_date'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($book['due_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; color: #64748b; font-style: italic;">No books currently issued.</p>
        <?php endif; ?>
        <?php $stmt->close(); ?>
    </div>

    <!-- Action Buttons -->
    <div class="form-actions">
        <a href="students_list.php" class="reset-btn" style="text-decoration: none; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Back to Students List
        </a>
        <a href="students_add.php?id=<?php echo $student_id; ?>" class="action-btn edit" style="text-decoration: none; display: inline-block;">
            <i class="fas fa-edit"></i> Edit Student
        </a>
    </div>
</div>

    </div>
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date("Y"); ?> SCC Library. develop By: alalong.</p>
    </footer>
</body>
</html>
