<?php
require_once 'includes/dbconnection.php';

$sql = "UPDATE issued_books SET due_date = DATE_ADD(issue_date, INTERVAL 14 DAY) WHERE due_date IS NULL OR due_date = '0000-00-00 00:00:00' OR due_date = '0000-00-00'";

if ($conn->query($sql)) {
    echo 'Updated ' . $conn->affected_rows . ' records with default due dates.';
} else {
    echo 'Error: ' . $conn->error;
}

$conn->close();
?>
