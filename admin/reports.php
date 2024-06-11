<?php
require_once("../db/dbconfic.php");

// Fetch incidents from the database
$sql = "SELECT * FROM crime_reports";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

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
        
        <div class="reports-table">

        <div class="search-container">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search... ">
				
            </div>
            <h2>Admin Panel - Crime Incidents</h2>
            <table>
    <tr>
        <th>Case Number</th>
        <th>Date and Time</th>
        <th>Location</th>
        <th>Reporter's Name</th>
        <th>Contact Information</th>
        <th>Crime Type</th>
        <th>Description</th>
        <th>Assigned Officer</th>
        <th>Status</th>
        <th>Deadline</th>
        <th>Supervisor comments</th>
        <th>View</th>
        <th>Assign Case</th>
        <th>Delete</th>
    </tr>
    
    <?php
    // Display incidents in table rows
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['case_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['date_time']) . "</td>";
            echo "<td>" . htmlspecialchars($row['location']) . "</td>";
            echo "<td>" . htmlspecialchars($row['reporter_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['contact_info']) . "</td>";
            echo "<td>" . htmlspecialchars($row['crime_type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
            echo "<td>" . htmlspecialchars($row['assigned_officer']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['deadline']) . "</td>";
            echo "<td>" . htmlspecialchars($row['comments']) . "</td>";
            echo "<td><button onclick=\"location.href='view_reports.php?id=" . $row['id'] . "';\">View Details</button></td>";
            echo "<td><button onclick=\"location.href='edit_report.php?id=" . $row['id'] . "';\">Edit</button></td>";
            echo "<td><button onclick=\"if(confirm('Are you sure you want to delete this incident?')){location.href='delete_reports.php?id=" . $row['id'] . "';}\">Delete</button></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='14'>No incidents found</td></tr>";
    }
    ?>
</table>

        </div>
    </div>

    <footer class="footer">
        <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>

    <script src="../js/table_search.js"></script>


</body>
</html>
