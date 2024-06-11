<?php
session_start();

// Include your database connection file here (make sure the path is correct)
require_once("../db/dbconfic.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the user is logged in
if (!isset($_SESSION["user"]["user_id"]) || !isset($_SESSION["user"]["full_name"])) {
    // If user is not logged in or session data is missing, redirect to the login page
    header("Location: login.php");
    exit;
}

// Fetch user ID and full name from session (sanitize session data)
$userId = filter_var($_SESSION["user"]["user_id"], FILTER_SANITIZE_NUMBER_INT);
$fullName = filter_var($_SESSION["user"]["full_name"], FILTER_SANITIZE_STRING);

// Prepare a statement to fetch user data from the database
$stmt = $conn->prepare("SELECT full_name FROM tbl_user WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Check if user data is found
if ($result->num_rows > 0) {
    // Fetch user data
    $fetchedFullName = $result->fetch_assoc()["full_name"];

    // Validate fetched full name against session full name
    if ($fetchedFullName !== $fullName) {
        // If the fetched full name does not match the session full name, redirect to the login page
        header("Location: login.php");
        exit;
    }
} else {
    // If user data is not found, redirect to the login page
    header("Location: login.php");
    exit;
}

$stmt->close();

// Prepare an SQL statement to select crimes reported by the user
$stmt = $conn->prepare("SELECT * FROM crime_reports WHERE user_id = ?");
$stmt->bind_param("i", $userId);

// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

    <link rel="stylesheet" href="user.css">
    <title>User Dashboard</title>
</head>
<body>
<header class="header">
    <div class="logo-container">
        <img src="../images/log.jpg" alt="Logo" class="logo">
        <span class="dashboard-title">View all my Crimes</span>
    </div>

    <div class="header-right">
        <h3>Welcome: <?php echo htmlspecialchars($fullName); ?></h3>
    </div>
</header>



<nav class="sidebar">
    <ul> 
        
        <li><a href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="report_crime.php"><i class="fas fa-bullhorn"></i> Log an Incident</a></li>
        <li><a href="view_crime.php"><i class="fas fa-eye"></i> View Crime Reports</a></li>
        <li><a href="alerts.php"><i class="fas fa-bell"></i> Alerts and Notifications</a></li>
        <li class="has-submenu"><a href="#"><i class="fas fa-user-circle"></i> Profile Management</a>
            <ul class="submenu">
                <li><a href="user_profile.php"><i class="fas fa-user-edit"></i> Update Profile</a></li>
            </ul>
        </li>
        <li class="has-submenu"><a href="#"><i class="fas fa-cog"></i> Settings</a>
            <ul class="submenu">
                <li><a href="change_password.php"><i class="fas fa-lock"></i> Change Password</a></li>
            </ul>
        </li>
        <li><a href="community_forum.php"><i class="fas fa-comments"></i> Community Engagement forum</a></li>
        <li>
            <a href="../crime/logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</nav>
<div class="main">
    <div class="containr">

    <div class="reports-table">
    <div class="search-container">
                <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search... ">
        <h1>Crimes Reported by You</h1>
        <table>
            <thead>
                <tr>
                    <!-- <th>Crime ID</th> -->
                    <th>Date and Time</th>
                    <th>Location</th>
                    <th>Crime Type</th>
                    <th>Description</th>
                    <th>Assigned officer</th>
                    <th>Status</th>
                    <th>Officer comments</th>
                    <!-- <th>Action</th> -->
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if there are any results
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["date_time"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["location"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["crime_type"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["description"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row['assigned_officer']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                         echo "<td>" . htmlspecialchars($row["officer_comments"]) . "</td>";


                    //     echo "<td>";
                    //     // Check if the crime report is older than two weeks
                    //     $twoWeeksAgo = date('Y-m-d H:i:s', strtotime('-2 weeks'));
                    //     if ($row["date_time"] > $twoWeeksAgo) {
                    //         // Crime report is less than two weeks old, display update button
                    //         echo "<a href='update_crime.php?crime_id=" . $row["id"] . "'>Update</a>";
                    //     } else {
                    //         // Crime report is older than two weeks, disable update button
                    //         echo "Update Disabled";
                    //     }
                    //     echo "</td>";
                    //     echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No crimes reported by you.</td></tr>";
                }

                // Close statement
                $stmt->close();
                ?>
            </tbody>
        </table>
    </div>
    </div>
    </div>
    


<footer class="footer">
    <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>
<script src="../js/table_search.js"></script>



</body>
</html>
