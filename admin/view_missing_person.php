<?php
session_start();

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


// Fetch missing person data from the database
$sql = "SELECT * FROM missing_persons";
$result = $conn->query($sql);
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
        <div class="containr">
            <div class="reports-table">
                <h1>Missing Person Reports</h1>
                <table>
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Date of Birth</th>
                            <th>Gender</th>
                            <th>Height(cm)</th>
                            <th>Weight(kg)</th>
                            <th>Hair color</th>
                            <th>Last seen Date</th>
                            <th>Last Seen Location</th>
                            <th>Description</th>
                            <th>Contact Email</th>
                            <th>Contact Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                <td><img src="<?php echo htmlspecialchars($row['PhotoPath']); ?>" alt="Missing Person Photo" style="width: 100px;"></td>

                                    <td><?php echo htmlspecialchars($row['FirstName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['LastName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['DateOfBirth']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Gender']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Height']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Weight']); ?></td>
                                    <td><?php echo htmlspecialchars($row['HairColor']); ?></td>
                                    <td><?php echo htmlspecialchars($row['DateLastSeen']); ?></td>
                                    <td><?php echo htmlspecialchars($row['LastSeenLocation']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Description']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ContactEmail']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ContactPhone']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9">No missing person reports found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<footer class="footer">
    <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>

</body>
</html>
