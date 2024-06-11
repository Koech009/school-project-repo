<?php
require_once("../db/dbconfic.php");

$successMessage = "";
$error = "";
$crimeReport = [];

// Check if the crime ID is provided in the URL
if (isset($_GET['crimeid'])) {
    $crimeId = $_GET['crimeid'];

    // Fetch crime report data based on crime ID
    if ($stmt = $conn->prepare("SELECT id, case_number, assigned_officer, status, deadline, close_case FROM crime_reports WHERE id = ?")) {
        $stmt->bind_param("i", $crimeId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a crime report is found
        if ($result->num_rows > 0) {
            $crimeReport = $result->fetch_assoc();
        } else {
            $error = "Crime report not found.";
        }

        // Close the statement
        $stmt->close();
    } else {
        $error = "Error preparing statement: " . $conn->error;
    }
}


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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $caseNumber = $_POST['caseNumber'] ?? '';
    $officer = $_POST['officer'] ?? '';
    $status = $_POST['status'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $caseClosed = isset($_POST['caseClosed']) ? 1 : 0;

    // Prepare and execute SQL query to update crime report
    if ($stmt = $conn->prepare("UPDATE crime_reports SET case_number = ?, assigned_officer = ?, status = ?, deadline = ?, close_case = ? WHERE id = ?")) {
        $stmt->bind_param("ssssii", $caseNumber, $officer, $status, $deadline, $caseClosed, $crimeId);

        if ($stmt->execute()) {
            $successMessage = "Crime report updated successfully.";
        } else {
            $error = "Error updating crime report: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        $error = "Error preparing statement: " . $conn->error;
    }
}

// Redirect after POST to prevent form resubmission
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) {
    header("Location: manage_reports.php?crimeid=" . urlencode($crimeId));
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Crime Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- <link rel="stylesheet" href="admin.css"> -->
    <link rel="stylesheet" href="../crime/user.css">

    <script>
    function validateForm() {
        var caseNumber = document.getElementById('caseNumber').value;
        var officer = document.getElementById('officer').value;
        var status = document.getElementById('status').value;
        var deadline = document.getElementById('deadline').value;
        
        // Get today's date
        var today = new Date();
        // Convert the deadline to a Date object
        var deadlineDate = new Date(deadline);

        if(caseNumber === '' || officer === '' || status === '' || deadline === '') {
            alert('Please fill out all required fields.');
            return false;
        }
        
        if (deadlineDate <= today) {
            alert('Deadline must be set to a future date.');
            return false;
        }
        
        return true;
    }
</script>

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
            <h2>Manage Crime Reports</h2>
            <?php if (!empty($successMessage)) : ?>
                <div class="success-message"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            <?php if (!empty($error)) : ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="manage_reports.php?crimeid=<?php echo $crimeReport['id'] ?? ''; ?>" method="post" id="crimeReportForm" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="caseNumber">Case Number:</label>
                    <input type="text" id="caseNumber" name="caseNumber" value="<?php echo $crimeReport['case_number'] ?? ''; ?>" required pattern="[A-Za-z0-9]+" title="Please enter a valid case number.">
                </div>
                <div class="form-group">
                    <label for="officer">Assign to Officer:</label>
                    <select id="officer" name="officer" required>
                        <option value="">Select an officer</option>
                        <?php foreach ($officers as $officerId => $officerName) : ?>
                            <option value="<?php echo $officerId; ?>" <?php echo ($crimeReport['assigned_officer'] == $officerId) ? 'selected' : ''; ?>>
                                <?php echo $officerName; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Update Status:</label>
                    <select id="status" name="status" required>
                        <option value="">Select a status</option>
                        <option value="in_progress" <?php echo ($crimeReport['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo ($crimeReport['status'] == 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                        <option value="unresolved" <?php echo ($crimeReport['status'] == 'unresolved') ? 'selected' : ''; ?>>Unresolved</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="deadline">Set Deadline:</label>
                    <input type="date" id="deadline" name="deadline" value="<?php echo $crimeReport['deadline'] ?? ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="caseClosed">Close Case:</label>
                    <input type="checkbox" id="caseClosed" name="caseClosed" <?php echo ($crimeReport['close_case'] == 1) ? 'checked' : ''; ?>>
                </div>
                <div class="form-group">
                    <input type="submit" value="Submit">
                </div>
            </form>
        </div>
    </div>
</div>


    <footer class="footer">
        <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>

    
</body>
</html>
<!-- not used anywhere -->