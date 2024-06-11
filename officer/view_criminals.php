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

// Fetch most wanted criminals data from the database
$sql = "SELECT * FROM most_wanted_criminals";
$result = $conn->query($sql);
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
            <span class="dashboard-title">Officer Dashboard</span>
        </div>
        <div class="header-right">
            <h3>Welcome: <?php echo htmlspecialchars($_SESSION["user"]["full_name"]); ?></h3>
        </div>
    </header>

   
<nav class="sidebar">
    <ul> 
        <li><a href="officer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="view_crimes.php"><i class="fas fa-eye"></i> View Crimes</a></li>
        <!-- <li><a href="edit_report.php"><i class="fas fa-edit"></i> Update Crimes</a></li> -->
        <li><a href="missing_person.php"><i class="fas fa-tasks"></i> Missing Persons</a></li>
        <li class="has-submenu"><a href="#"><i class="fas fa-user-circle"></i> Profile Management</a>
            <ul class="submenu">
                <li><a href="officer_profile.php"><i class="fas fa-user-edit"></i> Update Profile</a></li>
            </ul>
        </li>
        <li class="has-submenu"><a href="#"><i class="fas fa-cog"></i> Settings</a>
            <ul class="submenu">
                <li><a href="change_password.php"><i class="fas fa-lock"></i> Change Password</a></li>
            </ul>
        </li>
        <!-- <li><a href="alerts_notifications.php"><i class="fas fa-bell"></i> Alerts and Notifications</a></li> -->
        <li><a href="communication.php"><i class="fas fa-comments"></i> Communication</a></li>
        <li>
            <a href="../crime/logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
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
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>
    
</body>
</html>
