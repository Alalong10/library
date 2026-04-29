<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sanitize input to prevent XSS and SQL injection
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if admin is logged in
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

// Check if student is logged in
function is_student_logged_in() {
    return isset($_SESSION['student_id']);
}

// Redirect to login page if admin not logged in
function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: ../admin/login.php');
        exit;
    }
}

// Redirect to login page if student not logged in
function require_student_login() {
    if (!is_student_logged_in()) {
        header('Location: ../student/login.php');
        exit;
    }
}
?>
