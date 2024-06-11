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
        <div class="contaier">
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
                            <th>Delete</th>

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
                                     <!-- <td><button type="submit">edit</button></td> -->
                                     <td><a href='delete_missing_person.php?id=<?= htmlspecialchars($row['ID']) ?>' onclick="return confirm('Are you sure you want to delete this missing person record?');"><i class='fas fa-trash'></i></a></td>
                                    

                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11">No missing person reports found.</td>
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
