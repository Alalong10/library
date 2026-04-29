<?php
// Simple landing page with options for admin and student login
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Library Management System</title>
    <link rel="stylesheet" href="css/index.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="landing-container">
        <!-- Header Section -->
        <header class="landing-header">
            <div class="header-content">
                <div class="logo-section">
                    <img src="assets/image/scc1.png" alt="SCC Logo" class="logo-image">
                    <div class="brand-text">
                        <h1 class="brand-title">
                            <span class="brand-main">SCC Library</span>
                            <span class="brand-sub">Management System</span>
                        </h1>
                        <p class="brand-tagline">
                            <span class="tagline-text">Your gateway to</span>
                            <span class="tagline-accent">knowledge and resources</span>
                        </p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="landing-main">
            <!-- Hero Section -->
            <section class="hero-section">
                <div class="hero-content">
                    <h2 class="hero-title">Welcome to SCC Library</h2>
                    <p class="hero-description">
                        Discover a world of knowledge with our comprehensive library management system.
                        Access books, manage resources, and explore endless possibilities.
                    </p>
                </div>
            </section>

            <!-- Login Options -->
            <section class="login-section">
                <div class="login-grid">
                    <div class="login-card admin-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h3 class="card-title">Administrator</h3>
                        </div>
                        <p class="card-description">Manage library resources and settings with full administrative access.</p>
                        <a href="admin/login.php" class="login-btn admin-btn">
                            <span>Login as Admin</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="login-card student-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h3 class="card-title">Student</h3>
                        </div>
                        <p class="card-description">Access your account, browse books, and manage your library activities.</p>
                        <a href="student/login.php" class="login-btn student-btn">
                            <span>Login as Student</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="landing-footer">
            <div class="footer-content">
                <div class="footer-text">
                    <p class="copyright">&copy; 2024 SCC Library Management System. All rights reserved.</p>
                    <p class="footer-tagline">BY: ALALONG JERYL.</p>
                </div>
                <div class="footer-decoration">
                    <div class="decoration-line"></div>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
