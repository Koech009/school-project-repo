<?php
// Start the session
session_start();

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
       
    }

    // Close the statement
    $stmt->close();
} else {
    
}

// Initialize officer's full name
$officerFullName = "";

// Check if the full name is set in the session
if (isset($_SESSION["user"]["full_name"])) {
    $officerFullName = $_SESSION["user"]["full_name"];
}

// Function to get the count of unread assignments for the officer
function getUnreadAssignmentsCount($conn, $userId) {
    $sql = "SELECT COUNT(*) AS total_unread 
            FROM notifications 
            JOIN crime_reports ON notifications.crime_report_id = crime_reports.id
            WHERE crime_reports.assigned_officer = ? AND notifications.status = 'unread'";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['total_unread'];
    }
    return 0; // Default value if no unread assignments found
}

// Function to retrieve unread assignments for the officer
function getUnreadAssignmentsForOfficer($conn, $userId) {
    $assignments = []; // Initialize an empty array to store assignments

    $sql = "SELECT n.id AS notification_id, c.location, c.date_time, c.crime_type, c.description 
            FROM notifications AS n
            JOIN crime_reports AS c ON n.crime_report_id = c.id
            WHERE c.assigned_officer = ? AND n.status = 'unread'";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if query was successful
    if ($result && $result->num_rows > 0) {
        // Fetch each row of the result set and add it to the assignments array
        while ($row = $result->fetch_assoc()) {
            $assignments[] = $row;
        }
    }

    // Return the array of assignments
    return $assignments;
}

// Function to mark an assignment as read
function markAssignmentAsRead($conn, $notificationId) {
    $sql = "UPDATE notifications SET status = 'read' WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("i", $notificationId);
    $stmt->execute();
    $stmt->close();
}

// Check if the mark as read button is clicked
if (isset($_POST['mark_as_read'])) {
    $notificationId = $_POST['notification_id'];
    markAssignmentAsRead($conn, $notificationId);
    // Refresh the page to reflect the changes
    header("Refresh:0");
    exit();
}

// Get the count of unread assignments
$total_unread = getUnreadAssignmentsCount($conn, $userId);

// Get the unread assignments for the officer
$unreadAssignments = getUnreadAssignmentsForOfficer($conn, $userId);

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
    <title>Officer Dashboard</title>
</head>
<body>
<header class="header">
    <div class="logo-container">
        <img src="../images/log.jpg" alt="Logo" class="logo">
        <h1 class="dashboard-title">Officer Dashboard</h1>
    </div>
    
    <div class="header-right">
        <a href="alerts_notifications.php" class="notification-link">
            <span class="notification-count"><?php echo $total_unread; ?></span>
            <i class="fas fa-bell"></i>
            <span class="notification-text">Notifications</span>
        </a>
    </div>
</header>

<!-- Sidebar Navigation -->
<nav class="sidebar">
    <ul class="sidebar-menu">
        <li><a href="officer_dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="assigned_cases.php" class="sidebar-link"><i class="fas fa-briefcase"></i> Assigned Cases</a></li>
        <li class="notification-item"><a href="alerts_notifications.php" class="sidebar-link"><i class="fas fa-bell"></i>
        <span class="num"><?php echo $total_unread; ?></span> Notifications</a></li>
        <li class="has-submenu">
            <a href="#" class="sidebar-link submenu-toggle"><i class="fas fa-cogs"></i> Settings</a>
            <ul class="submenu">
                <li><a href="change_password.php" class="sidebar-link"><i class="fas fa-lock"></i> Change Password</a></li>
                <li><a href="profile.php" class="sidebar-link"><i class="fas fa-id-badge"></i> Profile</a></li>
            </ul>
        </li>
        <li><a href="../crime/logout.php" class="sidebar-link logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a></li>
    </ul>
</nav>

<div class="main">
    <div class="container">
        <div class="reports-table">
            <h1>Unread Assignments</h1>
            <?php if (!empty($unreadAssignments)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Location</th>
                            <th>Crime Type</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unreadAssignments as $assignment): ?>
                            <tr>
                                <td><?= htmlspecialchars($assignment['date_time']) ?></td>
                                <td><?= htmlspecialchars($assignment['location']) ?></td>
                                <td><?= htmlspecialchars($assignment['crime_type']) ?></td>
                                <td><?= htmlspecialchars($assignment['description']) ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="notification_id" value="<?= $assignment['notification_id'] ?>">
                                        <button type="submit" name="mark_as_read" class="btn btn-primary">Mark as Read</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No unread assignments found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="footer">
    <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>
</body>
</html>