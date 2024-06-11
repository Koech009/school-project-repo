<?php
// Database connection
require_once("../db/dbconfic.php");

// Check if user ID is provided
if(isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    // Prepare SQL statement
    $sql = "DELETE FROM tbl_user WHERE user_id = ?";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Redirect back to users.php after successful deletion
        header("Location: users.php");
        exit();
    } else {
        // Handle deletion failure with JavaScript alert
        echo "<script>alert('Error deleting user: " . $conn->error . "');</script>";
    }
} else {
    // Echo error message using JavaScript alert
    echo "<script>alert('User ID not provided');</script>";
}
// Close statement and database connection
$stmt->close();
$conn->close();
?>
