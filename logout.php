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
                    <a href="issue_book.php" class="active">
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

$message = '';
$selected_student = '';
$student_books = [];

// Handle AJAX request for student books
if (isset($_GET['get_student_books']) && isset($_GET['student_id'])) {
    $student_id = sanitize_input($_GET['student_id']);
    $stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $stmt->bind_result($student_db_id);
    $stmt->fetch();
    $stmt->close();

    if ($student_db_id) {
        $stmt = $conn->prepare("
            SELECT b.title, b.isbn, ib.issue_date
            FROM issued_books ib
            JOIN books b ON ib.book_id = b.id
            WHERE ib.student_id = ? AND ib.return_date IS NULL
            ORDER BY ib.issue_date DESC
        ");
        $stmt->bind_param("i", $student_db_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student_books = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    header('Content-Type: application/json');
    echo json_encode($student_books);
    exit();
}

// Handle AJAX request for book availability
if (isset($_GET['check_book']) && isset($_GET['isbn'])) {
    $isbn = sanitize_input($_GET['isbn']);
    $stmt = $conn->prepare("SELECT id, title, quantity FROM books WHERE isbn = ?");
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $stmt->store_result();

    $response = ['available' => false, 'title' => '', 'quantity' => 0];
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($book_id, $title, $quantity);
        $stmt->fetch();

        // Check how many copies are currently issued
        $stmt2 = $conn->prepare("SELECT COUNT(*) FROM issued_books WHERE book_id = ? AND return_date IS NULL");
        $stmt2->bind_param("i", $book_id);
        $stmt2->execute();
        $stmt2->bind_result($issued_count);
        $stmt2->fetch();
        $stmt2->close();

        $available_copies = $quantity - $issued_count;
        $response = [
            'available' => $available_copies > 0,
            'title' => $title,
            'quantity' => $available_copies
        ];
    }
    $stmt->close();

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = sanitize_input($_POST['student_id'] ?? '');
    $isbn = sanitize_input($_POST['isbn'] ?? '');
    $due_date = sanitize_input($_POST['due_date'] ?? '');

    // Validate inputs
    if (empty($student_id)) {
        $message = '<div class="alert alert-danger">Please select a student.</div>';
    } elseif (empty($isbn)) {
        $message = '<div class="alert alert-danger">Please enter ISBN.</div>';
    } elseif (empty($due_date)) {
        $message = '<div class="alert alert-danger">Please select a due date.</div>';
    } else {
        // Check if student exists
        $stmt = $conn->prepare("SELECT id, name FROM students WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            $message = '<div class="alert alert-danger">Student not found.</div>';
        } else {
            $stmt->bind_result($student_db_id, $student_name);
            $stmt->fetch();
            $stmt->close();

            // Check if book exists and is available with proper locking
            $conn->begin_transaction();

            try {
                // Lock the book row and check quantity
                $stmt = $conn->prepare("SELECT id, title, quantity FROM books WHERE isbn = ? FOR UPDATE");
                $stmt->bind_param("s", $isbn);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows == 0) {
                    throw new Exception("Book not found.");
                }

                $stmt->bind_result($book_id, $book_title, $quantity);
                $stmt->fetch();
                $stmt->close();

                if ($quantity <= 0) {
                    throw new Exception("Book is not available for issue.");
                }

                // Check if student already has this book issued
                $stmt = $conn->prepare("SELECT id FROM issued_books WHERE student_id = ? AND book_id = ? AND return_date IS NULL");
                $stmt->bind_param("ii", $student_db_id, $book_id);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->close();
                    throw new Exception("Student already has this book issued.");
                }
                $stmt->close();

                // Issue the book with due date and decrement quantity
                $issue_date = date('Y-m-d H:i:s');
                $stmt = $conn->prepare("INSERT INTO issued_books (student_id, book_id, issue_date, due_date) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $student_db_id, $book_id, $issue_date, $due_date);
                $stmt->execute();
                $stmt->close();

                // Decrement quantity
                $stmt = $conn->prepare("UPDATE books SET quantity = quantity - 1 WHERE id = ?");
                $stmt->bind_param("i", $book_id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                $message = '<div class="alert alert-success">Book "' . htmlspecialchars($book_title) . '" has been issued to ' . htmlspecialchars($student_name) . ' successfully! Due date: ' . htmlspecialchars($due_date) . '</div>';

            } catch (Exception $e) {
                $conn->rollback();
                $message = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
            }
        }
    }
}

// Get all students for dropdown
$students_result = $conn->query("SELECT student_id, name FROM students ORDER BY name");
$students = $students_result->fetch_all(MYSQLI_ASSOC);

// Set default due date (2 weeks from now)
$default_due_date = date('Y-m-d', strtotime('+14 days'));
?>

<div class="container">
    <h2>Issue Book</h2>

    <?php echo $message; ?>

    <div class="issue-book-container">
        <form method="POST" action="issue_book.php" class="form-container" id="issueForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="student_id">Student ID</label>
                    <select id="student_id" name="student_id" required>
                        <option value="">Select Student</option>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo htmlspecialchars($student['student_id']); ?>" <?php echo (isset($_POST['student_id']) && $_POST['student_id'] == $student['student_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($student['student_id'] . ' - ' . $student['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="isbn">Book ISBN</label>
                    <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($_POST['isbn'] ?? ''); ?>" placeholder="Enter book ISBN" required>
                    <div id="book-info" class="book-info" style="display: none;"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($_POST['due_date'] ?? $default_due_date); ?>" required>
                </div>

                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" id="issueBtn" style="margin-left: 20px;">
                        <i class="fas fa-hand-holding"></i>
                        Issue Book
                    </button>
                </div>
            </div>
        </form>

        <!-- Student Current Books Section -->
        <div id="student-books-section" class="student-books-section" style="display: none;">
            <h3>Student's Current Books</h3>
            <table id="student-books-table" class="students-table-container" style="margin: 0 auto; width: 90%; display: none;">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>ISBN</th>
                        <th>Issue Date</th>
                    </tr>
                </thead>
                <tbody id="student-books-list">
                    <!-- Books will be loaded here via AJAX -->
                </tbody>
            </table>
            <div id="no-books-message" style="text-align: center; color: #6c757d; font-style: italic; display: none;">
                <i class="fas fa-book"></i><br>No books currently issued to this student.
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const studentSelect = document.getElementById('student_id');
        const isbnInput = document.getElementById('isbn');
        const bookInfo = document.getElementById('book-info');
        const studentBooksSection = document.getElementById('student-books-section');
        const studentBooksList = document.getElementById('student-books-list');

        // Load student books when student is selected
        studentSelect.addEventListener('change', function() {
            const studentId = this.value;
            const studentBooksTable = document.getElementById('student-books-table');
            const noBooksMessage = document.getElementById('no-books-message');

            if (studentId) {
                fetch(`issue_book.php?get_student_books=1&student_id=${encodeURIComponent(studentId)}`)
                    .then(response => response.json())
                    .then(data => {
                        studentBooksSection.style.display = 'block';
                        if (data.length > 0) {
                            studentBooksTable.style.display = 'table';
                            noBooksMessage.style.display = 'none';
                            studentBooksList.innerHTML = data.map(book => `
                                <tr>
                                    <td><span class="book-badge"><i class="fas fa-book"></i>${book.title}</span></td>
                                    <td>${book.isbn}</td>
                                    <td>${new Date(book.issue_date).toLocaleDateString()}</td>
                                </tr>
                            `).join('');
                        } else {
                            studentBooksTable.style.display = 'none';
                            noBooksMessage.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading student books:', error);
                        studentBooksSection.style.display = 'none';
                    });
            } else {
                studentBooksSection.style.display = 'none';
            }
        });

        // Check book availability when ISBN is entered
        let checkTimeout;
        isbnInput.addEventListener('input', function() {
            const isbn = this.value.trim();
            clearTimeout(checkTimeout);

            if (isbn.length >= 3) {
                checkTimeout = setTimeout(() => {
                    fetch(`issue_book.php?check_book=1&isbn=${encodeURIComponent(isbn)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.title) {
                                bookInfo.style.display = 'block';
                                bookInfo.className = data.available ? 'book-info book-available' : 'book-info book-unavailable';
                                bookInfo.innerHTML = `
                                    <strong>${data.title}</strong><br>
                                    ${data.available ? '✅ Available' : '❌ Not Available'} (${data.quantity} copies)
                                `;
                            } else {
                                bookInfo.style.display = 'none';
                            }
                        })
                        .catch(error => {
                            console.error('Error checking book:', error);
                            bookInfo.style.display = 'none';
                        });
                }, 500);
            } else {
                bookInfo.style.display = 'none';
            }
        });

        // Form validation (removed past date restriction to allow penalties)
    });
    </script>
</div>

    </div>
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date("Y"); ?> SCC Library. develop By: alalong.</p>
    </footer>
</body>
</html>
