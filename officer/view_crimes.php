<?php
session_start();

// Correct the path to your database configuration file
require_once("../db/dbconfic.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the user is logged in
if (!isset($_SESSION["user"]["user_id"])) {
    // If user is not logged in, redirect to the login page
    header("Location: login.php");
    exit;
}

// Fetch user ID and full name from session
$userId = $_SESSION["user"]["user_id"];
$officerFullName = $_SESSION["user"]["full_name"]; // Assuming the full name is stored in the session

// Prepare a statement to fetch assigned crime reports from the database
$stmt = $conn->prepare("SELECT * FROM crime_reports WHERE assigned_officer = ? ORDER BY date_time DESC");
$stmt->bind_param("s", $officerFullName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "No crime reports assigned to you.";
}

$stmt->close();
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
    <div class="search-container">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search... ">
				
            </div>
         <h1>Assigned Crimes</h1>

         <table>
    <thead>
        <tr>
            <th>Date and Time</th>
            <th>Location</th>
            <th>Reporter's Name</th>
            <th>Contact Information</th>
            <th>Crime Type</th>
            <th>Description</th>
            <th>Assigned Officer</th>
            <th>Status</th>
            <th>View</th>
            <th>Update status</th>
          



            
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <tr>
                <td><?php echo htmlspecialchars($row['date_time']); ?></td>
                <td><?php echo htmlspecialchars($row['location']); ?></td>
                <td><?php echo htmlspecialchars($row['reporter_name']); ?></td>
                <td><?php echo htmlspecialchars($row['contact_info']); ?></td>
                <td><?php echo htmlspecialchars($row['crime_type']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td><?php echo htmlspecialchars($row['assigned_officer']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><a href="view_reports.php?id=<?php echo $row['id']; ?>"><i class="fas fa-eye"></i></a></td>
                <td><a href="edit_report.php?id=<?php echo $row['id']; ?>"><i class="fas fa-edit"></i></a></td>

        <?php endwhile; ?>
    </tbody>
</table>


    </div>
    </div>

    <footer class="footer">
        <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>
    <script src="../js/table_search.js"></script>

    
</body>
</html>
