<?php
session_start();
require_once '../includes/dbconnection.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get student details
if ($student_id > 0) {
    $stmt = $conn->prepare("SELECT name, username, student_id FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->bind_result($student_name, $student_username, $student_number);
    $stmt->fetch();
    $stmt->close();
} else {
    header("Location: students_list.php");
    exit();
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = sanitize_input($_POST['new_password'] ?? '');
    $confirm_password = sanitize_input($_POST['confirm_password'] ?? '');

    // Validation
    if (empty($new_password)) {
        $message = '<div class="alert alert-danger">Please enter a new password.</div>';
    } elseif (strlen($new_password) < 8) {
        $message = '<div class="alert alert-danger">New password must be at least 8 characters long.</div>';
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $message = '<div class="alert alert-danger">New password must contain at least one uppercase letter.</div>';
    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $message = '<div class="alert alert-danger">New password must contain at least one lowercase letter.</div>';
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $message = '<div class="alert alert-danger">New password must contain at least one number.</div>';
    } elseif ($new_password !== $confirm_password) {
        $message = '<div class="alert alert-danger">New passwords do not match.</div>';
    } else {
        // Update password
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_hashed_password, $student_id);

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Password reset successfully for student: ' . htmlspecialchars($student_name) . '.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error resetting password. Please try again.</div>';
        }
        $stmt->close();
    }
}

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

<div class="container">
    <h2>Reset Student Password</h2>

    <div class="student-info">
        <h3>Student Details</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($student_name); ?></p>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($student_username); ?></p>
        <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student_number); ?></p>
    </div>

    <?php echo $message; ?>

    <form method="POST" action="reset_student_password.php?id=<?php echo $student_id; ?>" class="form-container">
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <div class="password-requirements">
            <h4>Password Requirements:</h4>
            <ul>
                <li id="req-length"><i class="fas fa-times"></i> At least 8 characters long</li>
                <li id="req-uppercase"><i class="fas fa-times"></i> At least one uppercase letter</li>
                <li id="req-lowercase"><i class="fas fa-times"></i> At least one lowercase letter</li>
                <li id="req-number"><i class="fas fa-times"></i> At least one number</li>
            </ul>
        </div>

        <button type="submit" class="btn-reset-password">
            <i class="fas fa-save"></i>
            Reset Password
        </button>
    </form>

    <div style="text-align: center; margin-top: 20px;">
        <a href="students_list.php" class="btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Back to Students List
        </a>
    </div>
</div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');

    newPassword.addEventListener('input', function() {
        updateRequirements(this.value);
    });

    function updateRequirements(password) {
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password)
        };

        Object.keys(requirements).forEach(req => {
            const element = document.getElementById('req-' + req);
            if (requirements[req]) {
                element.classList.add('valid');
            } else {
                element.classList.remove('valid');
            }
        });
    }
});
</script>

<style>
.student-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.student-info h3 {
    margin-top: 0;
    color: #2c3e50;
}

.student-info p {
    margin: 5px 0;
}

.password-requirements {
    margin: 20px 0;
}

.password-requirements ul {
    list-style: none;
    padding: 0;
}

.password-requirements li {
    margin: 5px 0;
    color: #e74c3c;
}

.password-requirements li.valid {
    color: #27ae60;
}

.password-requirements li.valid i:before {
    content: "\f00c"; /* check icon */
}

.btn-secondary {
    display: inline-block;
    padding: 10px 20px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-left: 10px;
}

.btn-secondary:hover {
    background: #5a6268;
}
</style>

<?php
require_once 'footer.php';
?>
