<?php
require_once("../db/dbconfic.php");

// Check if incident ID is provided
if(isset($_GET['id']) && !empty($_GET['id'])) {
    $incident_id = $_GET['id'];

    // Delete incident from the database
    $sql = "DELETE FROM crime_reports WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $incident_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Close prepared statement
        $stmt->close();

        // Close database connection
        $conn->close();

        // Redirect back to reports.php
        echo '<script>alert("Incident deleted successfully.");</script>';
        echo '<script>window.location.href = "reports.php";</script>';
        exit();
    } else {
        echo '<script>alert("Error deleting incident.");</script>';
    }

    $stmt->close();
} else {
    echo '<script>alert("Incident ID not provided.");</script>';
}

$conn->close();
?>
