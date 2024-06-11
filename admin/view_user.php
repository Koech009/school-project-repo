<?php
require_once("../db/dbconfic.php");

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    function getUnreadCrimeReportsCount($conn) {
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
        return 0;
    }
    
    $total_unread = getUnreadCrimeReportsCount($conn);

    $stmt_user = $conn->prepare("SELECT * FROM tbl_user WHERE user_id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows > 0) {
        $user_row = $result_user->fetch_assoc();
        $user_type = $user_row['user_type'];
        $full_name = $user_row['full_name'];

        if ($user_type == 'user') {
            $stmt_crimes = $conn->prepare("SELECT * FROM crime_reports WHERE user_id = ?");
            $stmt_crimes->bind_param("i", $user_id);
        } elseif ($user_type == 'officer') {
            $stmt_crimes = $conn->prepare("SELECT * FROM crime_reports WHERE assigned_officer = ? ORDER BY date_time DESC");
            $stmt_crimes->bind_param("s", $full_name);
        } else {
            echo "Invalid user type.";
            exit();
        }

        $stmt_crimes->execute();
        $result_crimes = $stmt_crimes->get_result();

        if (!$result_crimes) {
            echo "Error fetching crime reports: " . $conn->error;
        }
    } else {
        echo "User not found.";
        exit();
    }

    $stmt_user->close();
    if (isset($stmt_crimes)) {
        $stmt_crimes->close();
    }
} else {
    header("Location: users.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../crime/user.css">
    <title>View Users</title>
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
        <h2>User Details</h2>
        <table>
            <tr>
                <td>User ID:</td>
                <td><?= $user_row['user_id'] ?></td>
            </tr>
            <tr>
                <td>Full Name:</td>
                <td><?= $user_row['full_name'] ?></td>
            </tr>
            <tr>
                <td>User Type:</td>
                <td><?= $user_row['user_type'] ?></td>
            </tr>
        </table>

        <h2><?= $user_row['full_name'] ?>'s Crime Reports</h2>
        <?php if (isset($result_crimes) && $result_crimes->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Crime ID</th>
                        <th>Crime Type</th>
                        <th>Description</th>
                        <th>Date Reported</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($crime_row = $result_crimes->fetch_assoc()): ?>
                        <tr>
                            <td><?= $crime_row['id'] ?></td>
                            <td><?= $crime_row['crime_type'] ?></td>
                            <td><?= $crime_row['description'] ?></td>
                            <td><?= $crime_row['date_time'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No crime reports found for this user.</p>
        <?php endif; ?>
    </div>
</div>
<footer class="footer">
    <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>
</body>
</html>
