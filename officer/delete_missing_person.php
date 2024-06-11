<?php
// Database connection
require_once("../db/dbconfic.php");

// Check if missing person ID is provided
if(isset($_GET['id'])) {
    $missing_person_id = $_GET['id'];
    
    // Prepare SQL statement
    $sql = "DELETE FROM missing_persons WHERE ID = ?";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $missing_person_id);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Redirect back to view_missing_person.php after successful deletion
        header("Location: view_missing_persons.php?message=success");
        exit();
    } else {
        // Handle deletion failure with JavaScript alert
        echo "<script>alert('Error deleting missing person: " . $stmt->error . "');</script>";
    }
} else {
    // Echo error message using JavaScript alert
    echo "<script>alert('Missing person ID not provided');</script>";
}

// Close statement and database connection
$stmt->close();
$conn->close();
?>
