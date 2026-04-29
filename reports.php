<?php
// Start session and include DB connection
session_start();
require_once "../includes/dbconnection.php";
require_once "../includes/functions.php";

// CSRF token generation function
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
generateCsrfToken();

// CSRF token validation function
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        // Sanitize inputs
        $username = sanitize_input($_POST['username'] ?? '');
        $password = sanitize_input($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = "Please enter username and password.";
        } else {
            $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }

            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($id, $hash);

            // Check if fetch returns data
            if ($stmt->fetch()) {
                if (password_verify($password, $hash)) {
                    $_SESSION['admin_id'] = $id;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Invalid username or password.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Login - SCC Library</title>
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-page-wrapper">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <div class="logo-section">
                        <img src="../assets/image/scc1.png" alt="SCC Logo" class="login-logo">
                        <h1 class="login-title">SCC LIBRARY</h1>
                        <p class="login-subtitle">Admin Portal</p>
                    </div>
                </div>

                <div class="login-body">
                    <?php if ($error): ?>
                        <div class="alert alert-error" role="alert" aria-live="polite" aria-atomic="true">
                            <i class="fas fa-exclamation-triangle alert-icon"></i>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" class="login-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>" />

                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="fas fa-user label-icon"></i>
                                Username
                            </label>
                            <div class="input-wrapper">
                                <input type="text" name="username" id="username" class="form-input" placeholder="Enter your username" required autofocus autocomplete="username" />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock label-icon"></i>
                                Password
                            </label>
                            <div class="input-wrapper">
                                <input type="password" name="password" id="password" class="form-input" placeholder="Enter your password" required autocomplete="current-password" />
                            </div>
                        </div>

                        <button type="submit" class="login-btn">
                            <i class="fas fa-sign-in-alt btn-icon"></i>
                            Login to Admin Panel
                        </button>
                    </form>
                </div>

                <div class="login-footer">
                    <a href="../index.php" class="back-home-link">
                        <i class="fas fa-arrow-left"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
</body>
</html>
