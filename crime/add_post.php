<?php
session_start();
require_once("../db/dbconfic.php");

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function handleDatabaseError($stmt) {
    echo "Database Error: " . $stmt->error;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the user is logged in
if (!isset($_SESSION["user"]["user_id"]) || !isset($_SESSION["user"]["role"])) {
    // If user is not logged in, redirect to the login page
    header("Location: login.php");
    exit;
}



// Fetch user details
$user_id = $_SESSION['user']['user_id'] ?? null;
if ($user_id) {
    $stmt = $conn->prepare("SELECT * FROM tbl_user WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "<script>alert('User not found.');</script>";
        exit;
    }
    $stmt->close();
} else {
    echo "<script>alert('No user ID in session.');</script>";
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_SESSION["user"]["user_id"];
    $postTitle = sanitizeInput($_POST["post_title"]);
    $postContent = sanitizeInput($_POST["post_content"]);
    
    // Handle file uploads for photo
    $photoName = $_FILES['photo']['name'];
    $photoTmpName = $_FILES['photo']['tmp_name'];
    $photoError = $_FILES['photo']['error'];

    $photoDestination = '';
    if ($photoError === 0) {
        
        $photoDestination = '../uploads/' . $photoName;
        move_uploaded_file($photoTmpName, $photoDestination);
    }

    // Handle file uploads for video
    $videoName = $_FILES['video']['name'];
    $videoTmpName = $_FILES['video']['tmp_name'];
    $videoError = $_FILES['video']['error'];

    $videoDestination = '';
    if ($videoError === 0) {
        $videoDestination = '../uploads/' . $videoName;
        move_uploaded_file($videoTmpName, $videoDestination);
    }

    // Perform database insertion
    $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, photo_path, video_path) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $postTitle, $postContent, $photoDestination, $videoDestination);
    if (!$stmt->execute()) {
        handleDatabaseError($stmt);
    }

    // Redirect to community forum page
    header("Location: community_forum.php");
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
        <h2>Add a Post</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <label for="post_title">Title:</label><br>
        <input type="text" id="post_title" name="post_title" required><br>
        <label for="post_content">Content:</label><br>
        <textarea id="post_content" name="post_content" rows="4" cols="50" required></textarea><br>
        <!-- File upload fields -->
        <div class="file-input">
            <label for="photo">
                <span class="file-icon">&#128247;</span> <!-- Photo icon -->
                Upload Photo
            </label>
            <input type="file" id="photo" name="photo" accept="image/*"> <!-- File upload input for photo -->
        </div>
        <div class="file-input">
            <label for="video">
                <span class="file-icon">&#127916;</span> <!-- Video icon -->
                Upload Video
            </label>
            <input type="file" id="video" name="video" accept="video/*"> <!-- File upload input for video -->
        </div>
        <input type="submit" value="Submit Post">
    </form>
        </div>
    </div>
</div>
<footer class="footer">
    <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>

</body>
</html>
