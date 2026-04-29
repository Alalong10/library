<?php
require_once '../includes/dbconnection.php';
require_once '../includes/functions.php';
require_admin_login();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $delete_id = (int)$_GET['id'];

    // Check if author exists
    $stmt_check = $conn->prepare("SELECT id FROM authors WHERE id = ?");
    $stmt_check->bind_param("i", $delete_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Check if author is referenced in books
        $stmt_books = $conn->prepare("SELECT COUNT(*) FROM books WHERE author_id = ?");
        $stmt_books->bind_param("i", $delete_id);
        $stmt_books->execute();
        $stmt_books->bind_result($book_count);
        $stmt_books->fetch();
        $stmt_books->close();

        if ($book_count > 0) {
            // Author is referenced, cannot delete
            header("Location: authors.php?error=Cannot delete author because they have associated books.");
            exit();
        } else {
            // Delete author
            $stmt_delete = $conn->prepare("DELETE FROM authors WHERE id = ?");
            $stmt_delete->bind_param("i", $delete_id);

            if ($stmt_delete->execute()) {
                header("Location: authors.php?success=Author deleted successfully.");
                exit();
            } else {
                header("Location: authors.php?error=Error deleting author.");
                exit();
            }
            $stmt_delete->close();
        }
    } else {
        header("Location: authors.php?error=Author not found.");
        exit();
    }
    $stmt_check->close();
} else {
    header("Location: authors.php");
    exit();
}
?>
