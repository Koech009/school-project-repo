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
    <title>Officer Dashboard</title>
</head>
<body>
    <header class="header">
        <div class="logo-container">
            <img src="../images/log.jpg" alt="Logo" class="logo">
            <span class="dashboard-title">Officer Dashboard</span>
        </div>
        <div class="header-right">
            <a href="../crime/logout.php" class="logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
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
    <h2>Incident Details</h2>
<table>
    <tr>
        <th>Date and Time</th>
        <td><?= htmlspecialchars($row['date_time']) ?></td>
    </tr>
    <tr>
        <th>Location</th>
        <td><?= htmlspecialchars($row['location']) ?></td>
    </tr>
    <tr>
        <th>Reporter's Name</th>
        <td><?= htmlspecialchars($row['reporter_name']) ?></td>
    </tr>
    <tr>
        <th>Contact Information</th>
        <td><?= htmlspecialchars($row['contact_info']) ?></td>
    </tr>
    
    <tr>
        <th>Crime Type</th>
        <td><?= htmlspecialchars($row['crime_type']) ?></td>
    </tr>
    <tr>
        <th>Description</th>
        <td><?= htmlspecialchars($row['description']) ?></td>
    </tr>
    <tr>
        <th>Suspects</th>
        <td><?= htmlspecialchars($row['suspects']) ?></td>
    </tr>
    <tr>
        <th>Victims</th>
        <td><?= htmlspecialchars($row['victims']) ?></td>
    </tr>
    <tr>
        <th>Additional Information</th>
        <td><?= htmlspecialchars($row['injuries_damages']) ?></td>
    </tr>
    
    <tr>
        <th>Other Relevant Details</th>
        <td><?= htmlspecialchars($row['other_details']) ?></td>
    </tr>
    <tr>
        <th>Anonymous Reporting</th>
        <td><?= $row['anonymous_reporting'] ? 'Yes' : 'No' ?></td>
    </tr>
    <tr>
        <th>Case Number</th>
        <td><?= htmlspecialchars($row['case_number']) ?></td>
    </tr>
    <tr>
        <th>Supervisor Comments</th>
        <td><?= htmlspecialchars($row['comments']) ?></td>
    </tr>
    <tr>
        <th>Deadline</th>
        <td><?= htmlspecialchars($row['deadline']) ?></td>
    </tr>
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
                        if (!empty($row['evidence_path'])) {
                            $evidenceFiles = json_decode($row['evidence_path'], true);
                            foreach ($evidenceFiles as $evidenceFile) {
                                $evidenceFileName = basename($evidenceFile);
                                echo '<a href="../uploads/' . $evidenceFileName . '" target="_blank">View</a> | ';
                                echo '<a href="../uploads/' . $evidenceFileName . '" download="' . $evidenceFileName . '">Download</a><br>';
                            }
                        } else {
                            echo "No evidence files";
                        }
                        ?>
                    </td>
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

    <footer class="footer">
        <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>
</body>
</html>

<?php
    } else {
        echo "Incident not found.";
    }

    // Close the prepared statement
    $stmt->close();
} else {
    echo "Incident ID not provided.";
}

// Check if the download report button is clicked
if (isset($_POST['download_report'])) {
    $incident_id = $_POST['incident_id'];
    $file_name = "crime_report_" . $incident_id . ".txt";
    $file_content = "";

    $sql = "SELECT * FROM crime_reports WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $incident_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        $file_content .= "Date and Time: " . $row['date_time'] . "\n";
        $file_content .= "Location: " . $row['location'] . "\n";
        // Continue adding other details to the file content

        $tmp_file = tempnam(sys_get_temp_dir(), 'crime_report_');
        file_put_contents($tmp_file, $file_content);

        forceDownload($tmp_file);

        unlink($tmp_file);
    } else {
        echo "Incident not found.";
    }

    $stmt->close();
}

// Flush the output buffer and turn off output buffering
ob_end_flush();

// Close the database connection
$conn->close();
?>