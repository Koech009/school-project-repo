<?php
// Start the session
session_start();

// Include your database connection file here (make sure the path is correct)
require_once("../db/dbconfic.php");

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to handle database errors
function handleDatabaseError($stmt) {
    // Handle database errors gracefully
    echo "Database Error: " . $stmt->error;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the user is logged in
if (isset($_SESSION["user"]["user_id"])) {
    // User is logged in, so retrieve user ID from session
    $userId = $_SESSION["user"]["user_id"];

    // Prepare a statement to fetch user data from the database
    $stmt = $conn->prepare("SELECT user_id, full_name FROM tbl_user WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        handleDatabaseError($stmt);
    }
    $result = $stmt->get_result();

    // Check if user data is found
    if ($result->num_rows > 0) {
        // Fetch user data
        $fetchedUserData = $result->fetch_assoc();

        // Set session variables with the fetched data
        $_SESSION["user"]["full_name"] = $fetchedUserData["full_name"];
    } else {
        // Handle the case where user data is not found
        // Redirect the user to the login page
        header("Location: login.php");
        exit();
    }

    $stmt->close();
} else {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    
    <link rel="stylesheet" href="user.css">
    <title>User Dashboard</title>
</head>
<body>
<header class="header">
    <div class="logo-container">
        <img src="../images/log.jpg" alt="Logo" class="logo">
        <span class="dashboard-title">User Dashboard</span>
    </div>
    <div class="header-right">
        <h3>Welcome: <?php echo htmlspecialchars($_SESSION["user"]["full_name"]); ?></h3>
    </div>
</header>

<nav class="sidebar">
    <ul> 
        <li><a href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="report_crime.php"><i class="fas fa-bullhorn"></i> Log an Incident</a></li>
        <li><a href="view_crime.php"><i class="fas fa-eye"></i> View Crime Reports</a></li>
        <li><a href="alerts.php"><i class="fas fa-bell"></i> Alerts and Notifications</a></li>
        <li class="has-submenu"><a href="#"><i class="fas fa-user-circle"></i> Profile Management</a>
            <ul class="submenu">
                <li><a href="user_profile.php"><i class="fas fa-user-edit"></i> Update Profile</a></li>
            </ul>
        </li>
        <li class="has-submenu"><a href="#"><i class="fas fa-cog"></i> Settings</a>
            <ul class="submenu">
                <li><a href="change_password.php"><i class="fas fa-lock"></i> Change Password</a></li>
            </ul>
        </li>
        <li><a href="commmunity_forum.php"><i class="fas fa-comments"></i> Community Engagement forum</a></li>
        <li>
            <a href="../crime/logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav> 

<div class="main">
    <div class="container">
        <div class="reports-table">
            <h1>Forum Page</h1>
            <?php
            // Post Submission Form
            echo "<h3>Add a Post</h3>";
            echo "<form action='add_post.php' method='post' enctype='multipart/form-data'>";
            echo "<input type='text' name='post_title' placeholder='Title' required><br>";
            echo "<textarea name='post_content' rows='4' cols='50' placeholder='Write your post here...' required></textarea><br>";
            echo "<input type='file' name='post_photo' accept='image/*'><br>"; // Input for photo
            echo "<input type='file' name='post_video' accept='video/*'><br>"; // Input for video
            echo "<input type='submit' value='Submit Post'>";
            echo "</form>";

            // Fetch and display all posts with pagination
            $postsPerPage = 10; // Number of posts per page
            $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $offset = ($currentPage - 1) * $postsPerPage;

            $query_posts = "SELECT posts.post_id, posts.content, posts.photo_path, posts.video_path, posts.created_at, tbl_user.full_name
            FROM posts
            INNER JOIN tbl_user ON posts.user_id = tbl_user.user_id
            ORDER BY posts.created_at DESC
            LIMIT ?, ?";
            $stmt_posts = $conn->prepare($query_posts);
            $stmt_posts->bind_param("ii", $offset, $postsPerPage);
            if (!$stmt_posts->execute()) {
                handleDatabaseError($stmt_posts);
            }
            $posts_result = $stmt_posts->get_result();

            if ($posts_result->num_rows > 0) {
                while ($post_row = $posts_result->fetch_assoc()) {
                    // Display post
                    echo "<div>";
                    echo "<h2>".$post_row['content']."</h2>";
                    if (!empty($post_row['photo_path'])) {
                        echo "<img src='".$post_row['photo_path']."' alt='Photo'>";
                    }
                    if (!empty($post_row['video_path'])) {
                        echo "<video controls><source src='".$post_row['video_path']."' type='video/mp4'></video>";
                    }
                    echo "<p>Posted by: ".$post_row['full_name']." | ".$post_row['created_at']."</p>";
                    echo "<form action='delete_post.php' method='post'>";
                    echo "<input type='hidden' name='post_id' value='".$post_row['post_id']."'>";
                    echo "<input type='submit' value='Delete Post'>";
                    echo "</form>";

                    // Fetch comments related to the post
                    $comments_query = "SELECT comments.comment_id, comments.content, comments.created_at, tbl_user.full_name
                                       FROM comments
                                       INNER JOIN tbl_user ON comments.user_id = tbl_user.user_id
                                       WHERE comments.post_id = ?
                                       ORDER BY comments.created_at ASC";
                    $stmt_comments = $conn->prepare($comments_query);
                    $stmt_comments->bind_param("i", $post_row['post_id']);
                    if (!$stmt_comments->execute()) {
                        handleDatabaseError($stmt_comments);
                    }
                    $comments_result = $stmt_comments->get_result();

                    if ($comments_result->num_rows > 0) {
                        // Display comments
                        echo "<h3>Comments</h3>";
                        while ($comment_row = $comments_result->fetch_assoc()) {
                            echo "<div>";
                            echo "<p>Comment by: ".$comment_row['full_name']." | ".$comment_row['created_at']."</p>";
                            echo "<p>".htmlspecialchars($comment_row['content'])."</p>";
                            echo "</div>";
                        }
                    } else {
                        echo "No comments found.";
                    }

                    echo "</div>";
                }
            } else {
                echo "No posts found.";
            }

            // Pagination links
            $totalPostsQuery = "SELECT COUNT(*) AS totalPosts FROM posts";
            $totalPostsResult = $conn->query($totalPostsQuery);
            $totalPosts = $totalPostsResult->fetch_assoc()['totalPosts'];
            $totalPages = ceil($totalPosts / $postsPerPage);

            echo "<div class='pagination'>";
            for ($i = 1; $i <= $totalPages; $i++) {
                echo "<a href='commmunity_forum.php?page=$i'>$i</a>";
            }
            echo "</div>";
            ?>
        </div>
    </div>
</div>

<footer class="footer">
    <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>

</body>
</html>
