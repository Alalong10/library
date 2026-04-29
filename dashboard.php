<?php
require_once '../includes/dbconnection.php';
require_once '../includes/functions.php';
require_admin_login();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $category_id = (int)$_GET['id'];

    // Check if category exists
    $stmt_check = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt_check->bind_param("i", $category_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->bind_result($category_name);
        $stmt_check->fetch();
        $stmt_check->close();

        // Check if category is being used by any books
        $stmt_books = $conn->prepare("SELECT COUNT(*) FROM books WHERE category_id = ?");
        $stmt_books->bind_param("i", $category_id);
        $stmt_books->execute();
        $stmt_books->bind_result($book_count);
        $stmt_books->fetch();
        $stmt_books->close();

        if ($book_count > 0) {
            // Category is in use, redirect with error
            header("Location: categories.php?error=Category '" . urlencode($category_name) . "' cannot be deleted because it is associated with $book_count book(s).");
            exit();
        } else {
            // Safe to delete
            $stmt_delete = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt_delete->bind_param("i", $category_id);

            if ($stmt_delete->execute()) {
                header("Location: categories.php?success=Category '" . urlencode($category_name) . "' deleted successfully.");
                exit();
            } else {
                header("Location: categories.php?error=Error deleting category: " . urlencode($stmt_delete->error));
                exit();
            }
            $stmt_delete->close();
        }
    } else {
        header("Location: categories.php?error=Category not found.");
        exit();
    }
} else {
    header("Location: categories.php");
    exit();
}
?>
