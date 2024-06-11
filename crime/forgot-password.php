<?php
// Database connection
require_once("../db/dbconfic.php");

// Initialize error and success variables
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the email from the POST request
    $email = $_POST["email"];

    // Generate a new token for password reset
    $token = bin2hex(random_bytes(16));

    // Hash the token for secure storage
    $token_hash = hash("sha256", $token);

    // Set the expiry time for the token (30 minutes from now)
    $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

    // Prepare the SQL query to update the user's password reset token and expiry
    $sql = "UPDATE tbl_user
            SET reset_token_hash = ?,
                reset_token_expires_at = ?
            WHERE email = ?";

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);

    // Bind the parameters to the SQL statement
    $stmt->bind_param("sss", $token_hash, $expiry, $email);

    // Execute the SQL statement
    $stmt->execute();

    // Check if any rows were affected by the update
    if ($conn->affected_rows) {
        $mail = require __DIR__ . "/mailer.php";
        $mail->setFrom("noreply@example.com");
        $mail->addAddress($email);
        $mail->Subject = "Password Reset";
        


        $mail->Body = "Click the following link to reset your password: " .
                      "http://localhost/project/crime/reset-password.php?token=$token";


        // Attempt to send the email
        if ($mail->send()) {
            
            // $success = "Message sent, please check your inbox.";
            echo '<script>alert("Message sent, please check your inbox."); window.location.href = "login.php";</script>';

        } else {
            // Error sending email
            $error = "Message could not be sent. Mailer error: " . $mail->ErrorInfo;
        }
    } else {
        // No user found with that email address
        $error = "No user found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/login.css">
    <title>Forgot Password</title>
</head>
<body>
    
<div class="container">
    <form action="" method="POST" id="form" onsubmit="return validateForm()">
        <h3>Forgot Password</h3>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        <div class="form-group">
            <label for="email">Email address:</label>
            <input type="email" placeholder="Enter your Email address:" name="email" id="email" class="form-control" required>
            <div id="emailError" class="error-message"></div>
        </div>
        <div class="form-btn">
            <input type="submit" value="Send Password Reset Link" name="password-reset" class="btn btn-primary">
        </div>
    </form>
</div>

<script>
    function validateForm() {
        var emailInput = document.getElementById("email");
        var emailError = document.getElementById("emailError");
        var email = emailInput.value.trim();

        // Regular expression for email validation
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email)) {
            emailError.innerHTML = "Please enter a valid email address.";
            emailInput.focus();
            return false;
        } else {
            emailError.innerHTML = "";
            return true;
        }
    }
</script>
</body>
</html>
