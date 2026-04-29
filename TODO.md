<?php
require_once 'includes/dbconnection.php';

$username = 'admin';
$password = 'admin'; // Change this to your desired password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashed_password, $username);

if ($stmt->execute()) {
    echo "Password updated successfully for admin user.";
} else {
    echo "Error updating password: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
