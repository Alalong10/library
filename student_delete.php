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
                    <a href="return_book.php" class="active">
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

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $isbn = sanitize_input($_POST['isbn'] ?? '');

    if (empty($isbn)) {
        $message = '<div class="alert alert-danger">Please enter ISBN.</div>';
    } else {
        // Get book_id from ISBN
        $stmt = $conn->prepare("SELECT id, title FROM books WHERE isbn = ?");
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            $message = '<div class="alert alert-danger">Book not found.</div>';
        } else {
            $stmt->bind_result($book_id, $book_title);
            $stmt->fetch();
            $stmt->close();

            // Check if book is currently issued
            $stmt = $conn->prepare("SELECT ib.id, s.name, s.student_id, ib.due_date FROM issued_books ib JOIN students s ON ib.student_id = s.id WHERE ib.book_id = ? AND ib.return_date IS NULL");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 0) {
                $message = '<div class="alert alert-danger">This book is not currently issued to any student.</div>';
            } else {
                $stmt->bind_result($issue_id, $student_name, $student_id, $due_date);
                $stmt->fetch();
                $stmt->close();

                // Calculate penalty
                $return_date = date('Y-m-d H:i:s');
                $penalty = 0;

                // Handle invalid due dates by setting default (14 days from issue date)
                if (!$due_date || $due_date == '0000-00-00 00:00:00' || $due_date == '0000-00-00') {
                    // Get issue date to calculate default due date
                    $stmt_due = $conn->prepare("SELECT issue_date FROM issued_books WHERE id = ?");
                    $stmt_due->bind_param("i", $issue_id);
                    $stmt_due->execute();
                    $stmt_due->bind_result($issue_date);
                    $stmt_due->fetch();
                    $stmt_due->close();

                    if ($issue_date) {
                        $due_date = date('Y-m-d H:i:s', strtotime($issue_date . ' +14 days'));
                    } else {
                        $due_date = date('Y-m-d H:i:s', strtotime('+14 days'));
                    }
                }

                $due_date_obj = new DateTime($due_date);
                $return_date_obj = new DateTime($return_date);
                $days_late = $return_date_obj->diff($due_date_obj)->days;

                if ($return_date_obj > $due_date_obj) {
                    $penalty = $days_late * 10; // 10 pesos per day late
                }

                // Return the book and update penalty
                $stmt = $conn->prepare("UPDATE issued_books SET return_date = ?, penalty = ? WHERE id = ?");
                $stmt->bind_param("sdi", $return_date, $penalty, $issue_id);

                if ($stmt->execute()) {
                    $penalty_message = $penalty > 0 ? ' Penalty: ₱' . number_format($penalty, 2) . ' (' . $days_late . ' days late).' : '';
                    $message = '<div class="alert alert-success">Book "' . htmlspecialchars($book_title) . '" has been returned by ' . htmlspecialchars($student_name) . ' (ID: ' . htmlspecialchars($student_id) . ') successfully!' . $penalty_message . '</div>';
                } else {
                    $message = '<div class="alert alert-danger">Error returning book. Please try again.</div>';
                }
                $stmt->close();
            }
        }
    }
}
?>

<div class="container">
    <h2>Return Book</h2>

    <?php echo $message; ?>

    <form method="POST" action="return_book.php" class="form-container">
        <div class="form-group">
            <label for="isbn">Book ISBN</label>
            <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($_POST['isbn'] ?? ''); ?>" placeholder="Enter book ISBN to return" required>
            <i class="fas fa-barcode input-icon"></i>
        </div>

        <button type="submit">
            <i class="fas fa-undo"></i>
            Return Book
        </button>
    </form>

    <div class="info-section" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3><i class="fas fa-info-circle"></i> How to Return a Book</h3>
        <ol style="margin-top: 10px;">
            <li>Enter the ISBN of the book being returned</li>
            <li>The system will automatically identify which student has the book</li>
            <li>The return will be processed and the book will become available again</li>
        </ol>
    </div>
</div>

    </div>
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date("Y"); ?> SCC Library. develop By: alalong.</p>
    </footer>
</body>
</html>
