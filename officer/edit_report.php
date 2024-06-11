<?php
session_start(); // Start session 
require_once("../db/dbconfic.php");

// Initialize variables
$errors = [];
$crimeReportId = $_GET['id'] ?? ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['update_status'])) {
        // Update Status
        $status = $_POST['status'];

        // Handle the case where crime_report_id is not set or empty
        if (empty($_POST['crime_report_id'])) {
            $_SESSION['error'] = "Crime report ID is missing.";
            header("Location: reports.php");
            exit();
        }

        // Check if the case is marked as closed
        $isClosed = isset($_POST['closed']) ? true : false;

        // Officer comments
        $officerComments = $_POST['officer_comments'];

        // Prepare and execute the update query
        $stmt = $conn->prepare("UPDATE crime_reports SET status=?, close_case=?, officer_comments=? WHERE id=?");
        if (!$stmt) {
            die("Error: " . $conn->error); // Print error message and exit if prepare fails
        }
        $stmt->bind_param("sisi", $status, $isClosed, $officerComments, $_POST['crime_report_id']);
        $stmt->execute();

        // Check for errors and affected rows
if ($stmt->error) {
    $_SESSION['error'] = "Database error: " . $stmt->error;
    echo "<script>alert('Failed to update the status due to a database error.'); window.location.href = 'view_crimes.php';</script>";
} elseif ($stmt->affected_rows > 0) {
    echo "<script>alert('Status updated successfully!'); window.location.href = 'view_crimes.php';</script>";
} else {
    echo "<script>alert('No changes were made to the status.'); window.location.href = 'view_crimes.php';</script>";
}
$stmt->close();



    }
} elseif (!empty($crimeReportId)) {
    // Fetching existing data for editing
    $stmt = $conn->prepare("SELECT * FROM crime_reports WHERE id = ?");
    $stmt->bind_param("i", $crimeReportId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $report = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = "Crime report not found.";
        header("Location: reports.php");
        exit();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../crime/user.css">
    <title>Officer Dashboard</title>
    <style>
        .closed-case {
            color: red; 
            font-weight: bold; 
            text-decoration: line-through;
        }
    </style>
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
        <div class="container">
            <div class="reports-table">
                <h2>Update Status</h2>
                <form method="post" onsubmit="return validateForm()">
                      <!-- Input field for case number -->
                    <label for="case_number">Case Number:</label><br>
                    <input type="text" id="case_number" name="case_number" value="<?php echo isset($report['case_number']) ? htmlspecialchars($report['case_number']) : ''; ?>" readonly><br>
                    
                    <label for="status">Update Case Status:</label><br>
                    <select id="status" name="status" required>
                        <option value="">Select Status</option>
                        <option value="Resolved" <?php if (isset($report) && $report['status'] == 'Resolved') echo "selected"; ?>>Resolved</option>
                        <option value="Unresolved" <?php if (isset($report) && $report['status'] == 'Unresolved') echo "selected"; ?>>Unresolved</option>
                        <option value="In Progress" <?php if (isset($report) && $report['status'] == 'In Progress') echo "selected"; ?>>In Progress</option>
                    </select><br>

                        <!-- Textarea for officer comments -->
                        <label for="officer_comments">Officer Comments:</label><br>
                        <textarea id="officer_comments" name="officer_comments" rows="4" cols="50"><?php echo isset($report['officer_comments']) ? htmlspecialchars($report['officer_comments']) : ''; ?></textarea><br>

                        <!-- Checkbox to mark the case as closed -->
                        <label for="case_number" <?php if (isset($report['close_case']) && $report['close_case'] == true) echo 'class="closed-case"'; ?>>Case Number:</label><br>
                         <input type="checkbox" id="closed" name="closed" value="1" <?php if (isset($report['close_case']) && $report['close_case'] == true) echo "checked"; ?>>

                        <input type="hidden" name="crime_report_id" value="<?php echo $crimeReportId; ?>">
                        <input type="submit" name="update_status" value="Update Status">
                    </form>

             
            </div>
        </div>
    </div>

    <footer class="footer">
        <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>


    <script>
        function validateForm() {
            var errorMessage = '';
            var status = document.getElementById('status').value;
            var officerComments = document.getElementById('officer_comments').value;

            if (status === '') {
                errorMessage += 'Please select a status.\n';
            }
            if (officerComments.trim() === '') {
                errorMessage += 'Officer comments cannot be empty.\n';
            }

            if (errorMessage !== '') {
                document.getElementById('error-messages').innerHTML = '<p>' + errorMessage + '</p>';
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
