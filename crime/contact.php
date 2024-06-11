<?php
require_once("../db/dbconfic.php");

// Initialize error message variable
$error_message = "";
$success_message="";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // Validate form data
    if (empty($name) || empty($email) || empty($message)) {
        $error_message = "Please fill in all required fields.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $error_message = "Name should contain only letters and spaces.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($message) > 500) {
        $error_message = "Message should not exceed 500 characters.";
    } else {
        // Prepare and execute SQL statement to insert data into messages table
        $sql = "INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $subject, $message);

        if ($stmt->execute()) {
            // Message successfully inserted
            $success_message = "Message sent successfully!";
            $name = $email = $subject = $message = ""; 
        } else {
            // Error occurred during insertion
            $error_message = "Error: " . $stmt->error;
        }

        // Close prepared statement
        $stmt->close();
    }
}

// Close database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Ian Koech">
    <title>CO-CRS</title>

    
    <link rel="stylesheet" href="../CSS/index.css">
    <style>
        .error-message {
            
            background-color: #ffe5e5;
            color: #d9534f;
            border: 1px solid #d9534f;
            border-radius: 4px;
            padding: 10px;
            margin-top:20px;
        }

        .success-message{
           
            background-color: #e6f7e3;
            color: #5cb85c;
            border: 1px solid #5cb85c;
            border-radius: 4px;
            padding: 10px;
            margin-top:20px;
        }
        
    </style>
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
                <a href="Login.php" class="login-btn">Log in</a>
                <a href="register.php?register=true" class="btn">Get Started</a>
            </div>
        </nav>
    </header>
    <div class="container">
    
    <div class="contact-content">
        <div class="contact-info">
        <h2>Contact Information</h2>
        <p>Address: 987 Main Street, Nairobi, Kenya</p>
        <p>Phone: +254 (123) 456-7890</p>
        <p>Email: co-crs@co.ke</p>
        </div>
        <div class="contact-form">
    <h2>Send Us a Message</h2>
    <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <form action="contact.php" method="POST">
        <label for="name">Your Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required><br>
        <label for="email">Your Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required><br>
        <label for="subject">Subject:</label>
        <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>"><br>
        <label for="message">Message:</label><br>
        <textarea id="message" name="message" rows="4" cols="50" required><?php echo htmlspecialchars($message ?? ''); ?></textarea><br>
        <input type="submit" value="Send Message">
    </form>
</div>


       </div>
           
       </div>

    <footer>
        <div class="footer-content">
            <div class="social-media">
                <h3>Connect With Us</h3>
                
                <a href="https://www.facebook.com/example" class="social-icon"> 
                    <img src="../images/facebook-icon.png" alt="Facebook">
                </a>
                <a href="https://twitter.com/example" class="social-icon"> 
                    <img src="../images/x-icon.png" alt="Twitter">
                </a>
                <a href="https://www.instagram.com/example" class="social-icon"> 
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
            <p class="text-center"><p>&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>

        </div>
    </footer>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('.contact-form form');
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const messageInput = document.getElementById('message');

        form.addEventListener('submit', function (event) {
            let isValid = true;
            const errorMessage = [];

            // Validate name
            if (!/^[a-zA-Z\s]+$/.test(nameInput.value.trim())) {
                errorMessage.push("Name should contain only letters and spaces.");
                isValid = false;
            }

            // Validate email
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim())) {
                errorMessage.push("Invalid email format.");
                isValid = false;
            }

            // Validate message length
            if (messageInput.value.trim().length > 500) {
                errorMessage.push("Message should not exceed 500 characters.");
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault(); // Prevent form submission
                alert(errorMessage.join("\n")); // Display error messages
            }
        });
    });
</script>

</body>
</html>