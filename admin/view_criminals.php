<?php
// Include database connection file
require_once("../db/dbconfic.php");

// Fetch most wanted criminals data from the database
$sql = "SELECT * FROM most_wanted_criminals";
$result = $conn->query($sql);

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
        <h1>Most Wanted Criminals</h1>
    <table>
        <thead>
            <tr>
                <th>Image</th>

                <th>Name</th>
                <th>Aliases</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Last Location</th>
                <th>Physical Characteristics</th>
                <th>Nationality</th>
                <th>Languages</th>
                <th>Risk Level</th>
                <th>Crime Description</th>
                <th>Edit criminal details</th>
                <th>Delete criminals</th>
                
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                    <td><img src="<?= htmlspecialchars($row['image_path']) ?>" alt="Criminal Image" /></td>

                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['aliases']) ?></td>
                        <td><?= htmlspecialchars($row['age']) ?></td>
                        <td><?= htmlspecialchars($row['gender']) ?></td>
                        <td><?= htmlspecialchars($row['last_location']) ?></td>
                        <td><?= htmlspecialchars($row['physical_characteristics']) ?></td>
                        <td><?= htmlspecialchars($row['nationality']) ?></td>
                        <td><?= htmlspecialchars($row['languages']) ?></td>
                        <td><?= htmlspecialchars($row['risk_level']) ?></td>
                        <td><?= htmlspecialchars($row['crime_description']) ?></td>
                        <td>
                            <a href="edit_criminals.php?id=<?= $row['id'] ?>"><i class="fas fa-edit"></i></a>
                            
                        </td>
                        <td><a href="delete_criminals.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this record?');"><i class="fas fa-trash-alt"></i></a></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="12">No records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

        </div>

    </div>

    <footer class="footer">
        <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>
</body>
</html>
