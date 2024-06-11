<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user"]["user_id"]) || !isset($_SESSION["user"]["role"])) {
    // If the user is not logged in, redirect to the login page
    header("Location: ../crime/login.php");
    exit;
}

// Include database connection file
require_once("../db/dbconfic.php");

function getUnreadCrimeReportsCount($conn) {
    // Query to count the number of unread notifications for admin
    $sql = "SELECT COUNT(*) AS total_unread 
            FROM notifications AS n
            JOIN crime_reports AS c ON n.crime_report_id = c.id
            WHERE n.user_id IN (SELECT user_id FROM tbl_user WHERE user_type = 'admin') 
            AND n.status = 'unread'";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total_unread'];
    }
    return 0; // Default value if no unread reports found
}


$total_unread = getUnreadCrimeReportsCount($conn);



// Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Initialize variables
    $total_crimes = $new_crimes = $resolved_crimes = $unresolved_crimes = $in_progress_crimes = 0;

    // Calculate 16 hours ago
    $date_of_interest = date('Y-m-d H:i:s', strtotime('-16 hours'));

    // Query to get crime statistics, including new crimes based on date
    $sql = "SELECT 
                COUNT(*) AS total_crimes,
                SUM(CASE WHEN status = 'new' AND DATE(date_time) = ? THEN 1 ELSE 0 END) AS new_crimes,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved_crimes,
                SUM(CASE WHEN status = 'unresolved' THEN 1 ELSE 0 END) AS unresolved_crimes,
                SUM(CASE WHEN status = 'in progress' THEN 1 ELSE 0 END) AS in_progress_crimes,
                SUM(CASE WHEN status = 'new' AND date_time >= ? THEN 1 ELSE 0 END) AS recent_new_crimes
            FROM crime_reports";

    // Prepare the statement to prevent SQL injection
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("ss", $date_of_interest, $date_of_interest);
    
    // Execute the statement
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();

    // Check if query was successful
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total_crimes = $row['total_crimes'];
        $new_crimes = $row['new_crimes'];
        $resolved_crimes = $row['resolved_crimes'];
        $unresolved_crimes = $row['unresolved_crimes'];
        $in_progress_crimes = $row['in_progress_crimes'];
        $recent_new_crimes = $row['recent_new_crimes'];
    } else {
        // Handle the case where no records are found
        echo "No records found.";
    }

    // Close the statement
    $stmt->close();
    
    // Close the database connection
    $conn->close();

    // Output "New Crime" if there are recent new crimes
    if ($recent_new_crimes > 0) {
        echo "New Crime";
    }

    // Output the count of crimes in progress
    echo "Crimes in Progress: " . $in_progress_crimes;
} catch (Exception $e) {
    // Handle any exceptions
    echo "Error: " . $e->getMessage();
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../crime/user.css">
    <title>Admin Dashboard</title>
</head>
<body>
<header class="header">
    <div class="logo-container">
        <img src="../images/log.jpg" alt="Logo" class="logo">
        <h1 class="dashboard-title">Admin Dashboard</h1>
    </div>
    
    <div class="header-right">
        <a href="alerts.php" class="notification-link">
            <span class="notification-count"><?php echo $total_unread; ?></span>
            <i class="fas fa-bell"></i>
            <span class="notification-text">Notifications</span>
        </a>
    </div>
</header>

    
    <!-- Sidebar Navigation -->
<nav class="sidebar">
    <ul class="sidebar-menu">
        <li><a href="admin_dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="users.php" class="sidebar-link"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-alt"></i> Crime Reports</a></li>
        <li class="has-submenu">
            <a href="#" class="sidebar-link submenu-toggle"><i class="fas fa-user-shield"></i> Manage Criminals</a>
            <ul class="submenu">
                <li><a href="view_criminals.php" class="sidebar-link"><i class="fas fa-list"></i> Most Wanted Criminals</a></li>
                <li><a href="most_wanted.php" class="sidebar-link"><i class="fas fa-user-plus"></i> Add Criminal</a></li>
            </ul>
        </li>
        <li class="notification-item"><a href="alerts.php" class="sidebar-link"><i class="fas fa-bell"></i>
        <span class="num"><?php echo $total_unread; ?></span> Notifications</a></li>

        <li class="has-submenu">
            <a href="#" class="sidebar-link submenu-toggle"><i class="fas fa-cogs"></i> Settings</a>
            <ul class="submenu">
                <li><a href="change_password.php" class="sidebar-link"><i class="fas fa-lock"></i> Change Password</a></li>
                <li><a href="profile.php" class="sidebar-link"><i class="fas fa-id-badge"></i> Profile</a></li>
            </ul>
        </li>
        <li><a href="analytics.php" class="sidebar-link"><i class="fas fa-chart-bar"></i> Crime Statistics</a></li>
        <li><a href="user_approval.php" class="sidebar-link"><i class="fas fa-user-plus"></i> Approve new users</a></li>
        <li><a href="contact_us_messages.php" class="sidebar-link"><i class="fas fa-comments"></i> messages</a></li>




        <li><a href="../crime/logout.php" class="sidebar-link logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a></li>
    </ul>
</nav>


<div class="main">
    <div class="reports-table">
        <h2>Dashboard</h2>
        <div class="promo_card">
            <h1>Welcome to Admin Dashboard </h1>
            <button><a href="reports.php">View reports</a></button>

            <button><a href="view_missing_person.php">View missing person</a></button>
            <button><a href="view_criminals.php">View most wanted criminals</a></button>
        </div>
      <div class="crime-stats-container">
        <div class="info-box">
            <h2>Total Crimes reported</h2>
            <p><?php echo $total_crimes; ?></p>
            <button>view details</button>
        </div>
       
        <div class="info-box">
            <h2>Resolved Crimes</h2>
            <p><?php echo $resolved_crimes; ?></p>
            <button>view details</button>

        </div>
        <div class="info-box">
            <h2>Unresolved Crimes</h2>
            <p><?php echo $unresolved_crimes; ?></p>
            <button>view details</button>

        </div>
        <div class="info-box">
            <h2>Crimes In Progress</h2>
            <p><?php echo $in_progress_crimes; ?></p>
            <button>view details</button>

        </div>
    </div>
    </div>
</div>
   

    <footer class="footer">
        <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>
</body>
</html>
