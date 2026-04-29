<?php
require_once 'includes/dbconnection.php';

echo "Checking admins table...<br>";

$result = $conn->query("SELECT * FROM admins");
if ($result) {
    echo "Admins table exists. Number of rows: " . $result->num_rows . "<br>";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Username: " . $row['username'] . "<br>";
    }
} else {
    echo "Admins table does not exist or query failed: " . $conn->error . "<br>";
}

$conn->close();
?>
