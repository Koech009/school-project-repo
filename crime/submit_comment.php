<?php
session_start();

require_once("../db/dbconfic.php");

// Check if the user is logged in
if (!isset($_SESSION["user"]["user_id"])) {
    // If user is not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Fetch comment content, post ID, and user ID from the form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input data
    $postId = (int) $_POST["post_id"];
    $userId = (int) $_SESSION["user"]["user_id"];
    $commentContent = isset($_POST["comment_content"]) ? trim($_POST["comment_content"]) : "";

    // Check if comment content is empty
    if (empty($commentContent)) {
        echo "Comment content is required.";
        exit();
    }

    // Prepare a statement to insert the comment into the database
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $postId, $userId, $commentContent);

    // Execute the statement
    if ($stmt->execute()) {
        // Comment added successfully, redirect back to the forum page
        header("Location: community_forum.php");
        exit();
    } else {
        // Error occurred, log the error
        error_log("Error adding comment: " . $stmt->error);
        // Display a generic error message to the user
        echo "An error occurred while adding the comment. Please try again later.";
    }

    $stmt->close();
} else {
    // If the request method is not POST, redirect to the forum page
    header("Location: community_forum.php");
    exit();
}
?>
