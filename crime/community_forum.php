<?php
session_start();

// Include your database connection file here (make sure the path is correct)
require_once("../db/dbconfic.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the user is logged in
if (!isset($_SESSION["user"]["user_id"])) {
    // If user is not logged in, redirect to the login page
    header("Location: login.php");
    exit;
}

// Fetch user ID from session
$userId = $_SESSION["user"]["user_id"];

// Prepare a statement to fetch user data from the database
$stmt = $conn->prepare("SELECT user_id, full_name FROM tbl_user WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Check if user data is found
if ($result->num_rows > 0) {
    // Fetch user data
    $fetchedUserData = $result->fetch_assoc();

    // Set session variables with the fetched data
    $_SESSION["user"]["full_name"] = $fetchedUserData["full_name"];
} else {
   
}

$stmt->close();
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
        <li><a href="community_forum.php"><i class="fas fa-comments"></i> Community Engagement Forumn </a></li>
        <li>
            <a href="../crime/logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>
<div class="main">
    <div class="reports-table">



            <h1>Community Forum</h1>
            <div class="promo_card">
                <h1>Welcome to community forum <?php echo htmlspecialchars($_SESSION["user"]["full_name"]); ?>.</h1>
                <button><a href="add_post.php">Start a Discussion</a></button>
                <button><a href="view_missing_people.php">View Missing Persons</a></button>
                <button><a href="view_criminals.php">View Most Wanted Criminals</a></button>
            </div>

            <div class="community-forum">
                <?php
                // Fetch all posts from the database
                $posts_query = "SELECT * FROM posts ORDER BY created_at DESC";
                $posts_result = $conn->query($posts_query);

                if ($posts_result->num_rows > 0) {
                    while ($post_row = $posts_result->fetch_assoc()) {
                        // Display each post
                        echo "<div class='post' onclick='toggleForms(this)'>";
                        echo "<h3>" . htmlspecialchars($post_row['title']) . "</h3>";
                        echo "<p>" . htmlspecialchars($post_row['content']) . "</p>";

                        // Display the image if available
                        if (!empty($post_row['photo_path'])) {
                            $image_path = "../uploads/" . $post_row['photo_path'];
                            if (file_exists($image_path)) {
                                echo "<div class='post-image'>";
                                echo "<img src='" . htmlspecialchars($image_path) . "' alt='Photo'>";
                                echo "</div>";
                            } else {
                                echo "<p>Image not found</p>";
                            }
                        }

                        // Display any attached video
                        if (!empty($post_row['video_path'])) {
                            if (file_exists($post_row['video_path'])) {
                                echo "<div class='post-video'>";
                                echo "<video controls><source src='" . htmlspecialchars($post_row['video_path']) . "' type='video/mp4'></video>";
                                echo "</div>";
                            } else {
                                echo "<p>Video not found</p>";
                            }
                        }

                        // Display comments for this post
                        $post_id = $post_row['post_id'];
                        $comments_query = "SELECT comments.content, tbl_user.full_name 
                                            FROM comments 
                                            INNER JOIN tbl_user ON comments.user_id = tbl_user.user_id 
                                            WHERE comments.post_id = $post_id 
                                            ORDER BY comments.created_at DESC";
                        $comments_result = $conn->query($comments_query);

                        echo "<div class='comments'>";
                        if ($comments_result->num_rows > 0) {
                            while ($comment_row = $comments_result->fetch_assoc()) {
                                echo "<div class='comment'>";
                                echo "<p>" . htmlspecialchars($comment_row['content']) . "</p>";
                                echo "<p>Comment by: " . htmlspecialchars($comment_row['full_name']) . "</p>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p>No comments yet.</p>";
                        }
                        echo "</div>";

                        // Comment submission form
                        echo "<form action='submit_comment.php' method='post' class='comment-form hidden'>";
                        echo "<input type='hidden' name='post_id' value='$post_id'>";
                        echo "<textarea name='comment_content' placeholder='Add your comment...' required></textarea>";
                        echo "<button type='submit'>Send</button>";
                        echo "</form>";

                        // Display delete button if the post belongs to the current user
                        if ($post_row['user_id'] == $_SESSION["user"]["user_id"]) {
                            echo "<form action='delete_post.php' method='post' class='delete-form hidden'>";
                            echo "<input type='hidden' name='post_id' value='" . $post_row['post_id'] . "'>";
                            echo "<button type='submit'>Delete Post</button>";
                            echo "</form>";
                        }

                        echo "</div>"; 
                    }
                } else {
                    echo "<p>No posts found.</p>";
                }
                ?>
            </div>
        </div>
    
</div>

    <script>
        function toggleForms(postElement) {
            const forms = postElement.querySelectorAll('.comment-form, .delete-form');
            forms.forEach(form => {
                form.classList.toggle('hidden');
            });
        }
    </script>

<footer class="footer">
    <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>

</body>
</html>
