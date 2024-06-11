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


// Function to insert a notification when a crime report is submitted
function insertNotification($conn, $crimeReportId, $userId) {
    $status = 'unread';
    $sql = "INSERT INTO notifications (crime_report_id, user_id, status) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $crimeReportId, $userId, $status);
    $stmt->execute();
    $stmt->close();
}

// Function to retrieve unread notifications for admin
function getUnreadNotificationsForAdmin($conn) {
    $notifications = []; // Initialize an empty array to store notifications

    // Query to retrieve unread notifications for admin
    $sql = "SELECT n.id AS notification_id, c.location, c.date_time,c.crime_type, c.description 
            FROM notifications AS n
            JOIN crime_reports AS c ON n.crime_report_id = c.id
            WHERE n.user_id IN (SELECT user_id FROM tbl_user WHERE user_type = 'admin') 
            AND n.status = 'unread'";

    // Execute the query
    $result = $conn->query($sql);

    // Check if query was successful
    if ($result && $result->num_rows > 0) {
        // Fetch each row of the result set and add it to the notifications array
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }
    }

    // Return the array of notifications
    return $notifications;
}

// Function to mark notification as read
function markNotificationAsRead($conn, $notificationId) {
    // Query to update notification status
    $sql = "UPDATE notifications SET status = 'read' WHERE id = ?";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $notificationId);

    // Execute the statement
    $stmt->execute();

    // Close the statement
    $stmt->close();
}

// Check if the mark as read button is clicked
if (isset($_POST['mark_as_read'])) {
    $notificationId = $_POST['notification_id'];
    markNotificationAsRead($conn, $notificationId);
    // Refresh the page to reflect the changes
    header("Refresh:0");
    exit();
}

// Call the function to get unread notifications for admin
$unreadNotifications = getUnreadNotificationsForAdmin($conn);

// Close the database connection
$conn->close();
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
        <!-- <li><a href="assignment.php" class="sidebar-link"><i class="fas fa-search"></i> Assign Cases</a></li> -->
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
    <div class="container">
    <div class="reports-table">
        <h1>Unread Notifications</h1>
        <?php if (!empty($unreadNotifications)): ?>
            <table>
                <thead>
                    <tr>
                       
                        <th>Date & Time</th>
                        <th>Location</th>
                        <th>Crime type</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unreadNotifications as $notification): ?>
                        <tr>
                            <td><?= $notification['date_time'] ?></td>
                            <td><?= $notification['location'] ?></td>
                            <td><?= $notification['crime_type'] ?></td>
                            <td><?= $notification['description'] ?></td>
                            
                            <td>
                                <form method="post">
                                    <input type="hidden" name="notification_id" value="<?= $notification['notification_id'] ?>">
                                    <button type="submit" name="mark_as_read">Mark as Read</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No unread notifications found.</p>
        <?php endif; ?>
    </div>
</div>
</div>

<footer class="footer">
    <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>
</body>
</html>
