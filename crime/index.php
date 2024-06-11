<?php
session_start(); // Start the session if not already started

// Check if the user is logged in
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'user') {
    // User is logged in, redirect to the report crime form
    $link = "report_crime.php";
} else {
    // User is not logged in, redirect to the login page
    $link = "login.php";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Ian Koech">
    <title>CO-CRS</title>
    <link rel="stylesheet" href="../CSS/index.css">
</head>
<body>

    <header>
        <nav>
            <div class="logo-container">
                <img src="../images/log.jpg" alt="Logo" class="logo">
            </div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about-us.php">About Us</a></li>
                <li><a href="<?php echo $link; ?>">Report a Crime</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
            <div>
                <a href="Login.php" class="login-btn">Login</a>
                <a href="register.php?register=true" class="btn">Get Started</a>
            </div>
        </nav>
    </header>

    
    <main>
        <section>
            <div class="hero">
                <div class="content">
                    <h1><strong>Welcome to Our Community-Based<br> Crime Reporting System</strong></h1>
                    <p style="color: blue;"><strong>Report crimes and help make your community safer.<br>We believe in the power of community collaboration to ensure<br> safety and security for all.Our platform is designed to empower<br> individuals to report crimes swiftly and efficiently, fostering a<br> safer environment for everyone.</strong></p>
                    <a href="register.php?register=true" class="btn btn-primary">Get Started</a>
                    <a href="contact.php" class="btn">Contact Us</a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="social-media">
                <h3>Connect With Us</h3>
                <a href="https://www.facebook.com/" class="social-icon">
                    <img src="../images/facebook-icon.png" alt="Facebook">
                </a>
                <a href="https://twitter.com/" class="social-icon">
                    <img src="../images/x-icon.png" alt="Twitter">
                </a>
                <a href="https://www.instagram.com/" class="social-icon">
                    <img src="../images/intergram-icon.png" alt="Instagram">
                </a>
            </div>
            <div class="contact">
                <h3>Contact Us</h3>
                <p>Email: contact@co-crs.co.ke<br>Phone: +254712345678<br>Address: 123 Main Street, Nairobi, Kenya</p>
            </div>
        </div>
        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">Copyright Information</a>
        </div>
        <div class="copywrite">
            <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
        </div>
    </footer>
</body>
</html>
