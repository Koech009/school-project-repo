<?php
session_start();

require_once("../db/dbconfic.php");

// Check if the user is logged in
if (!isset($_SESSION["user"]["user_id"])) {
    // If user is not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Check if the post ID is set and not empty
if (isset($_POST["post_id"]) && !empty($_POST["post_id"])) {
    $postId = $_POST["post_id"];

    // Prepare a statement to delete comments associated with the post
    $stmt_delete_comments = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
    $stmt_delete_comments->bind_param("i", $postId);

    // Execute the statement to delete comments
    if (!$stmt_delete_comments->execute()) {
        // Error occurred while deleting comments, handle it accordingly
        echo "Error deleting comments: " . $stmt_delete_comments->error;
        exit(); 
    }

    // Prepare a statement to delete the post
    $stmt_delete_post = $conn->prepare("DELETE FROM posts WHERE post_id = ?");
    $stmt_delete_post->bind_param("i", $postId);

    // Execute the statement to delete the post
    if ($stmt_delete_post->execute()) {
        // Post deleted successfully, redirect back to the forum page
        header("Location: community_forum.php");
        exit();
    } else {
        // Error occurred while deleting the post, handle it accordingly
        echo "Error deleting post: " . $stmt_delete_post->error;
    }

    $stmt_delete_comments->close();
    $stmt_delete_post->close();
} else {
    // If post ID is not set or empty, redirect to the forum page
    header("Location: community_forum.php");
    exit();
}
?>
