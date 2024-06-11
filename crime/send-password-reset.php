<?php
// Database connection
require_once("../db/dbconfic.php");

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
        // Email sent successfully
        echo '<script>alert("Message sent, please check your inbox."); window.location.href = "login.php";</script>';
    } else {
        // Error sending email
        echo '<script>alert("Message could not be sent. Mailer error: ' . $mail->ErrorInfo . '"); window.location.href = "send-password-reset.php";</script>';
    }
} else {
    // No user found with that email address
  
     echo '<script>alert("No user found with that email address."); window.location.href = "login.php";</script>';
}

?>
