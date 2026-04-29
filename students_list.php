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

$student_id = $name = $address = $age = $gender = $course = $civil_status = $username = $email = $password = "";
$name_err = $age_err = $username_err = $password_err = $student_id_err = $email_err = "";
$success_msg = $error_msg = "";

$is_edit = false;
$edit_id = null;

// Check if editing
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_edit = true;
    $edit_id = (int)$_GET['id'];

    // Load existing student data
    $stmt = $conn->prepare("SELECT student_id, name, address, age, gender, course, civil_status, username, email FROM students WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        $student_id = $student['student_id'];
        $name = $student['name'];
        $address = $student['address'];
        $age = $student['age'];
        $gender = $student['gender'];
        $course = $student['course'];
        $civil_status = $student['civil_status'];
        $username = $student['username'];
        $email = $student['email'];
    } else {
        header("Location: students_list.php");
        exit();
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $student_id = sanitize_input($_POST['student_id'] ?? '');
    $name = sanitize_input($_POST['name'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $age = sanitize_input($_POST['age'] ?? '');
    $gender = sanitize_input($_POST['gender'] ?? '');
    $course = sanitize_input($_POST['course'] ?? '');
    $civil_status = sanitize_input($_POST['civil_status'] ?? '');
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Don't sanitize password, but hash it later

    // Basic validations
    if (empty($student_id)) {
        $student_id_err = "Student ID is required.";
    } else {
        // Check if student_id is unique (exclude current record if editing)
        $stmt_check_id = $conn->prepare("SELECT id FROM students WHERE student_id = ?" . ($is_edit ? " AND id != ?" : ""));
        if ($is_edit) {
            $stmt_check_id->bind_param("si", $student_id, $edit_id);
        } else {
            $stmt_check_id->bind_param("s", $student_id);
        }
        $stmt_check_id->execute();
        $stmt_check_id->store_result();
        if ($stmt_check_id->num_rows > 0) {
            $student_id_err = "Student ID already exists.";
        }
        $stmt_check_id->close();
    }
    if (empty($name)) {
        $name_err = "Name is required.";
    }
    if (empty($age) || !is_numeric($age) || $age < 18 || $age > 100) {
        $age_err = "Valid age (18-100) is required.";
    }
    if (empty($username)) {
        $username_err = "Username is required.";
    } else {
        // Check if username is unique (exclude current record if editing)
        $stmt_check = $conn->prepare("SELECT id FROM students WHERE username = ?" . ($is_edit ? " AND id != ?" : ""));
        if ($is_edit) {
            $stmt_check->bind_param("si", $username, $edit_id);
        } else {
            $stmt_check->bind_param("s", $username);
        }
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $username_err = "Username already exists.";
        }
        $stmt_check->close();
    }
    if (empty($email)) {
        $email_err = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        // Check if email is unique (exclude current record if editing)
        $stmt_check_email = $conn->prepare("SELECT id FROM students WHERE email = ?" . ($is_edit ? " AND id != ?" : ""));
        if ($is_edit) {
            $stmt_check_email->bind_param("si", $email, $edit_id);
        } else {
            $stmt_check_email->bind_param("s", $email);
        }
        $stmt_check_email->execute();
        $stmt_check_email->store_result();
        if ($stmt_check_email->num_rows > 0) {
            $email_err = "Email already exists.";
        }
        $stmt_check_email->close();
    }
    if (!$is_edit && (empty($password) || strlen($password) < 6)) {
        $password_err = "Password must be at least 6 characters.";
    }

    if (empty($student_id_err) && empty($name_err) && empty($age_err) && empty($username_err) && empty($email_err) && empty($password_err)) {
        if ($is_edit) {
            // Update existing student
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE students SET name = ?, address = ?, age = ?, gender = ?, course = ?, civil_status = ?, username = ?, email = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssissssssi", $name, $address, $age, $gender, $course, $civil_status, $username, $email, $hashed_password, $edit_id);
            } else {
                $stmt = $conn->prepare("UPDATE students SET name = ?, address = ?, age = ?, gender = ?, course = ?, civil_status = ?, username = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssisssssi", $name, $address, $age, $gender, $course, $civil_status, $username, $email, $edit_id);
            }
            $success_msg = "Student updated successfully.";
        } else {
            // Insert new student
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO students (student_id, name, address, age, gender, course, civil_status, username, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssissssss", $student_id, $name, $address, $age, $gender, $course, $civil_status, $username, $email, $hashed_password);
            $success_msg = "Student registered successfully.";
        }

        if ($stmt->execute()) {
            if (!$is_edit) {
                // Clear inputs for new registration
                $student_id = $name = $address = $age = $gender = $course = $civil_status = $username = $email = $password = "";
            } else {
                // Redirect to students list after successful update
                header("Location: students_list.php");
                exit();
            }
        } else {
            $error_msg = "Error " . ($is_edit ? "updating" : "registering") . " student: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<div class="container">
    <header>
        <h1><?php echo $is_edit ? 'Edit Student' : 'Student Registration'; ?></h1>
        <p><?php echo $is_edit ? 'Update the student information below.' : 'Enter the details below to register a new student in the system.'; ?></p>
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

    <form action="students_add.php<?php echo $is_edit ? '?id=' . $edit_id : ''; ?>" method="post">
        <div class="form-group">
            <label for="student_id">Student ID</label>
            <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>" placeholder="Enter student ID number" <?php echo $is_edit ? 'readonly' : ''; ?>>
            <?php if ($student_id_err): ?>
                <span class="error"><?php echo $student_id_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter student's full name">
            <?php if ($name_err): ?>
                <span class="error"><?php echo $name_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" placeholder="Enter full address"><?php echo htmlspecialchars($address); ?></textarea>
        </div>

        <div class="form-group">
            <label for="age">Age</label>
            <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($age); ?>" placeholder="Enter age" min="18" max="100">
            <?php if ($age_err): ?>
                <span class="error"><?php echo $age_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender">
                <option value="">Select Gender</option>
                <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>

        <div class="form-group">
            <label for="course">Course/Program</label>
            <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($course); ?>" placeholder="Enter course or program">
        </div>

        <div class="form-group">
            <label for="civil_status">Civil Status</label>
            <select id="civil_status" name="civil_status">
                <option value="">Select Civil Status</option>
                <option value="Single" <?php echo ($civil_status == 'Single') ? 'selected' : ''; ?>>Single</option>
                <option value="Married" <?php echo ($civil_status == 'Married') ? 'selected' : ''; ?>>Married</option>
            </select>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" placeholder="Enter username">
            <?php if ($username_err): ?>
                <span class="error"><?php echo $username_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="Enter email">
            <?php if (isset($email_err) && $email_err): ?>
                <span class="error"><?php echo $email_err; ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password">
            <?php if ($password_err): ?>
                <span class="error"><?php echo $password_err; ?></span>
            <?php endif; ?>
        </div>

        <button type="submit">
            <i class="fas fa-<?php echo $is_edit ? 'save' : 'user-plus'; ?>"></i>
            <?php echo $is_edit ? 'Update Student' : 'Register Student'; ?>
        </button>
    </form>

    <div style="margin-top: 20px;">
        <a href="students_list.php" class="reset-btn" style="text-decoration: none; display: inline-block;">
            <i class="fas fa-arrow-left"></i> Back to Students List
        </a>
    </div>
</div>

    </div>
    <footer class="dashboard-footer">
        <p>&copy; <?php echo date("Y"); ?> SCC Library. develop By: alalong.</p>
    </footer>
</body>
</html>
