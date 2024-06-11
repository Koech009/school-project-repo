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
                <li><a href="Login.php">Report a Crime</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
            <div>
                <a href="Login.php" class="login-btn">Log in</a>
                <a href="register.php?register=true" class="btn">Get Started</a>
            </div>
        </nav>
    </header>

    <main>
        <section id="introduction">
            <h1>Welcome to community based online crime reporting system</h1>
            <p>Your dedicated online crime reporting system. We empower community members to contribute to the safety and security of our neighborhood by providing a simple, anonymous, and effective way to report crimes.</p>
        </section>

        <section id="mission">
            <h2>Our Mission</h2>
            <p>To foster a safer community by enabling a seamless and secure channel for reporting criminal activities. By harnessing the power of community vigilance, we aim to facilitate faster and more efficient crime reporting and response.</p>
        </section>

        <section id="how-it-works">
            <h2>How It Works</h2>
            <ul>
                <li><strong>Create account:</strong>By clicking get started button and login and you will find the report incident form in the user dashboard </li>
                <li><strong>Report Anonymously:</strong> Your identity remains anonymous as you report a crime through our secure platform.</li>
                <li><strong>Immediate Alerts:</strong> Once a report is submitted, real-time alerts are sent to local authorities and community watch groups.</li>
                <li><strong>Follow Up:</strong> Track the status of your report through our system and receive updates as they happen.</li>
            </ul>
        </section>

        <section id="features">
            <h2>Features and Benefits</h2>
            <p>User-Friendly Interface: Our platform is designed for ease of use. Anyone with internet access can report a crime without hassle.</p>
            <p>Mobile Compatibility: Report crimes on-the-go with our mobile-optimized design.</p>
            <p>Community Engagement: Engage with community safety initiatives and receive safety tips directly through the platform.</p>
        </section>

        <section id="team">
            <h2>Meet the Team</h2>
            <p>The system was developed by Ian kipchirchir and thanks to him for adocating to make our communities safe.</p>
        </section>

        <section id="impact">
            <h2>Community Impact</h2>
            <p>Since our launch, we've seen a 30% increase in crime reporting in our community, leading to quicker responses and more arrests. </p>
            
        </section>

        <section id="call-to-action">
            <h2>Get Involved</h2>
            <p>Join us in making our community safer. Start by reporting any suspicious activity safely and anonymously today or become a community volunteer to help spread the word.</p>
        </section>

        <section id="contact">
            <h2>Contact Us</h2>
            <p>For any questions or further information, please contact us using the contact form available when you click contact us in the header section or use our email. We are here to assist you.</p>
        </section>
    </main>
   
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
