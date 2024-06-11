<?php
// Include the database connection file
require_once("../db/dbconfic.php");

// Function to force download a file
function forceDownload($file) {
    if (file_exists($file)) {
        // Set headers for downloading the file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($file));
        // Output the file contents
        readfile($file);
        // Exit after file download
        exit;
    } else {
        // Display an error message if the file is not found
        echo "File not found.";
    }
}

// Start output buffering
ob_start();

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


// Check if an incident ID is provided in the URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // Get the incident ID from the URL
    $incident_id = $_GET['id'];

    // Prepare SQL query to select the crime report with the provided ID
    $sql = "SELECT * FROM crime_reports WHERE id = ?";
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $incident_id);
    $stmt->execute();
    // Get the result of the query
    $result = $stmt->get_result();

    // Check if a single row is returned
    if ($result->num_rows == 1) {
        // Fetch the row as an associative array
        $row = $result->fetch_assoc();
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
    <div class="container">
    <div class="reports-table">
    <h2>Incident Details</h2>
            <table>
                <tr><th>Date and Time</th><td><?= htmlspecialchars($row['date_time']) ?></td></tr>
                <tr><th>Location</th><td><?= htmlspecialchars($row['location']) ?></td></tr>
                <tr><th>Reporter's Name</th><td><?= htmlspecialchars($row['reporter_name']) ?></td></tr>
                <tr><th>Contact Information</th><td><?= htmlspecialchars($row['contact_info']) ?></td></tr>
                <tr><th>Affiliation</th><td><?= htmlspecialchars($row['affiliation']) ?></td></tr>
                <tr><th>Crime Type</th><td><?= htmlspecialchars($row['crime_type']) ?></td></tr>
                <tr><th>Description</th><td><?= htmlspecialchars($row['description']) ?></td></tr>
                <tr><th>Suspects</th><td><?= htmlspecialchars($row['suspects']) ?></td></tr>
                <tr><th>Victims</th><td><?= htmlspecialchars($row['victims']) ?></td></tr>
                <tr><th>Additional Information</th><td><?= htmlspecialchars($row['injuries_damages']) ?></td></tr>
                <tr><th>Police Involvement</th><td><?= $row['police_involved'] ? 'Yes' : 'No' ?></td></tr>
                <tr><th>Other Relevant Details</th><td><?= htmlspecialchars($row['other_details']) ?></td></tr>
                <tr><th>Anonymous Reporting</th><td><?= $row['anonymous_reporting'] ? 'Yes' : 'No' ?></td></tr>
            </table>
            <h3>Evidence and Witnesses</h3>
            <table>
            <tr>
    <th>Photos/Videos</th>
    <th>Witness Name</th>
    <th>Witness Contact</th>
</tr>
<tr>
    <td>
<?php
// Check if evidence files exist
if (!empty($row['evidence_path'])) {
    // Get the evidence paths as an array
    $evidenceFiles = explode(",", $row['evidence_path']);
    // Iterate through each file path
    foreach ($evidenceFiles as $evidenceFile) {
        // Display links to view and download each file
        echo '<a href="../uploads/' . $evidenceFile . '" target="_blank">View</a> | ';
        echo '<a href="../uploads/' . $evidenceFile . '" download="' . basename($evidenceFile) . '">Download</a><br>';
    }
} else {
    // Display message if no evidence files are found
    echo "No evidence files";
}
?>
    </td>
    <!-- Display witness name and contact -->
    <td><?= htmlspecialchars($row['witness_name']) ?></td>
    <td><?= htmlspecialchars($row['witness_contact']) ?></td>
</tr>

               
                    </table>
                    
                    <!-- Form to download the report -->
                    <form action="" method="post">
                        <input type="hidden" name="incident_id" value="<?= $incident_id ?>">
                        <button type="submit" name="download_report">Download Report</button>

                    </form>
                    
                       
    </div>
</div>
</div>
    <footer class="footer">
        <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>
</body>
</html>
<?php
    } else {
        // Display message if incident not found
        echo "Incident not found.";
    }

    // Close the prepared statement
    $stmt->close();
} else {
    // Display message if incident ID is not provided
    echo "Incident ID not provided.";
}

// Check if the download report button is clicked
if (isset($_POST['download_report'])) {
    // Get the incident ID from the form
    $incident_id = $_POST['incident_id'];
    // Create a filename for the report
    $file_name = "crime_report_" . $incident_id . ".txt";
    // Initialize file content variable
    $file_content = "";

    // Prepare SQL query to select the crime report with the provided ID
    $sql = "SELECT * FROM crime_reports WHERE id = ?";
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $incident_id);
    $stmt->execute();
    // Get the result of the query
    $result = $stmt->get_result();

    // Check if a single row is returned
    if ($result->num_rows == 1) {
        // Fetch the row as an associative array
        $row = $result->fetch_assoc();

        // Construct file content from incident details
        $file_content .= "Date and Time: " . $row['date_time'] . "\n";
        $file_content .= "Location: " . $row['location'] . "\n";
        $file_content .= "Reporter's Name: " . $row['reporter_name'] . "\n";
        $file_content .= "Contact Information: " . $row['contact_info'] . "\n";

        // Save file content to a temporary file
        $tmp_file = tempnam(sys_get_temp_dir(), 'crime_report_');
        file_put_contents($tmp_file, $file_content);

        // Force download the temporary file
        forceDownload($tmp_file);

        // Remove the temporary file after download
        unlink($tmp_file);
    } else {
        // Display message if incident not found
        echo "Incident not found.";
    }

    // Close the prepared statement
    $stmt->close();
}

// Flush the output buffer and turn off output buffering
ob_end_flush();

// Close the database connection
$conn->close();
?>