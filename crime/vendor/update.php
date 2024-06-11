<?php
// Start the session
session_start();

// Include your database connection file here (make sure the path is correct)
require_once("../db/dbconfic.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the user is logged in
if (isset($_SESSION["user"]["user_id"])) {
    // User is logged in, so retrieve user ID from session
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
        // User data not found, handle the error as needed
        // For example, redirect to the login page or show an error message
    }

    $stmt->close();

    // Query the database to get the total count of crimes reported by the user
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_count FROM crime_reports WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_crimes = $row['total_count'];

    // Query the database to get the counts of different types of crimes reported by the user
    $stmt = $conn->prepare("SELECT 
                                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved_count,
                                SUM(CASE WHEN status = 'unresolved' THEN 1 ELSE 0 END) AS unresolved_count,
                                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_count
                            FROM crime_reports
                            WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $resolved_crimes = $row['resolved_count'];
    $unresolved_crimes = $row['unresolved_count'];
    $in_progress_crimes = $row['in_progress_count'];

    // Close the statement
    $stmt->close();
} else {
    // If user is not logged in, redirect to the login page
    header("Location: login.php");
    exit;
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
        <li><a href="feedback_support.php"><i class="fas fa-comments"></i> Community Engagement and Feedback Support</a></li>
        <li>
            <a href="../crime/logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>
<footer class="footer">
    <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>

</body>
</html>