<?php
// Include database connection file
require_once("../db/dbconfic.php");

// Check if the criminal ID is provided and is a valid integer
if(isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    // Retrieve criminal ID from the URL
    $criminal_id = $_GET['id'];

    // Prepare and execute the SQL query to delete the criminal record
    $stmt = $conn->prepare("DELETE FROM most_wanted_criminals WHERE id = ?");
    $stmt->bind_param("i", $criminal_id);
    $stmt->execute();

    // Check if the deletion was successful
    if($stmt->affected_rows > 0) {
        // Redirect back to the page displaying criminals with a success message
        header("Location: view_criminals.php?message=success");
        exit();
    } else {
        // Redirect back to the page displaying criminals with an error message
        header("Location: view_criminals.php?message=error");
        exit();
    }

    // Close the prepared statement
    $stmt->close();
} else {
    // Redirect back to the page displaying criminals with an error message if criminal ID is not provided or is invalid
    header("Location: view_criminals.php?message=error");
    exit();
}

// Close the database connection
$conn->close();
?>
