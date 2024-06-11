<?php
// Database connection
require_once("../db/dbconfic.php");

// Fetch users from the database
$sql = "SELECT * FROM tbl_user";
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

// Usage example (assuming you have a valid database connection $conn)
$total_unread = getUnreadCrimeReportsCount($conn);


// Close database connection
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
    <script src="../js/table_search.js"></script>

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
            
            <?php if ($result->num_rows > 0): ?>
                <h2>Admin Panel: Manage User Details</h2>
            
                <table>
                    <thead>
                        <tr>
                        <th>user id</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Date of Birth</th>
                            <th>ID Passport</th>
                            <th>Gender</th>
                            <th>User Type</th>
                            <th>Created At</th>
                            <th> Administrator user Action</th>
                            <th>Change Password</th>

                        </tr>
                    </thead>
                    <tbody>
                   
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                            <td><?= htmlspecialchars($row['user_id']) ?></td>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td><?= htmlspecialchars($row['address']) ?></td>
                                <td><?= htmlspecialchars($row['date_of_birth']) ?></td>
                                <td><?= htmlspecialchars($row['id_passport']) ?></td>
                                <td><?= htmlspecialchars($row['gender']) ?></td>
                                <td><?= htmlspecialchars($row['user_type']) ?></td>
                                <td><?= htmlspecialchars($row['created_at']) ?></td>
                                <td>
                                    <a href='view_user.php?id=<?= htmlspecialchars($row['user_id']) ?>'><i class='fas fa-eye'>view</i></a> |
                                    <a href='edit_user.php?id=<?= htmlspecialchars($row['user_id']) ?>'><i class='fas fa-edit'>edit</i></a> |
                                    <a href='delete_user.php?id=<?= htmlspecialchars($row['user_id']) ?>' onclick="return confirm('Are you sure you want to delete this user?');"><i class='fas fa-trash'>delete</i></a>
                                </td>
                                <td>
                                    <!-- Reset Password link -->
                                    <a href='reset_password.php?user_id=<?= htmlspecialchars($row['user_id']) ?>'><i class='fas fa-key'>reset password</i></a>

                                    
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tr>
            <td colspan="11" style="text-align: center;">
                <button id="btn" onclick="location.href='add_user_form.php';">Add User</button>
            </td>
        </tr>
                </table>
            <?php else: ?>
                <p>No users found.</p>
            <?php endif; ?>
        </div>
    </div>
    
 


    <footer class="footer">
        <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>

</body>
</html>
