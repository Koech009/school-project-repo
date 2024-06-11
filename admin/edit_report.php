<?php
session_start(); // Start the session 
require_once("../db/dbconfic.php");

// Initialize variables
$caseNumber = $comments = "";
$errors = [];
$crimeReportId = $_GET['id'] ?? ''; 

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

// Fetch officers always needed for the form dropdown
$stmt = $conn->prepare("SELECT user_id, full_name FROM tbl_user WHERE user_type = 'officer'");
$stmt->execute();
$officersResult = $stmt->get_result();
$officers = $officersResult->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_status'])) {
        // Update Status
        $status = $_POST['status'];

        // Handle the case where crime_report_id is not set or empty
        if (empty($_POST['crime_report_id'])) {
            $_SESSION['error'] = "Crime report ID is missing.";
            header("Location: reports.php");
            exit();
        }

        // Prepare and execute the update query
        $stmt = $conn->prepare("UPDATE crime_reports SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $_POST['crime_report_id']);
        $stmt->execute();

        // Check for errors and affected rows
        if ($stmt->error) {
            $_SESSION['error'] = "Database error: " . $stmt->error;
            echo "<script>alert('Failed to update the status due to a database error.');</script>";
        } elseif ($stmt->affected_rows > 0) {
            echo "<script>alert('Status updated successfully!');</script>";
        } else {
            echo "<script>alert('No changes were made to the status.');</script>";
        }
        $stmt->close();
    } else {
        // Process form submission
        $caseNumber = $_POST['case_number'];
        $assignedOfficer = $_POST['assigned_officer'];
        $deadline = $_POST['deadline'];
        $comments = $_POST['comments'];

        // Example of validation
        if (empty($caseNumber)) {
            $errors['case_number'] = "Case number is required.";
        } elseif (!preg_match("/^[A-Z]{2}-\d{4}$/", $caseNumber)) {
            $errors['case_number'] = "Case number must follow the format XX-1234.";
        } else {
            // Check for uniqueness
            $stmt = $conn->prepare("SELECT id FROM crime_reports WHERE case_number = ? AND id != ?");
            $stmt->bind_param("si", $caseNumber, $_POST['crime_report_id']);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors['case_number'] = "Case number must be unique.";
            }
            $stmt->close();
        }

        if (empty($assignedOfficer)) {
            $errors['assigned_officer'] = "Assigned officer is required.";
        }

        if (empty($deadline)) {
            $errors['deadline'] = "Deadline is required.";
        } elseif (strtotime($deadline) <= time()) {
            $errors['deadline'] = "Deadline must be in the future.";
        }

        // Handle the case where crime_report_id is not set or empty
        if (empty($_POST['crime_report_id'])) {
            $_SESSION['error'] = "Crime report ID is missing.";
            header("Location: reports.php");
            exit();
        }

        if (count($errors) === 0) {
            // Prepare and execute the update query
            $stmt = $conn->prepare("UPDATE crime_reports SET assigned_officer=?, deadline=?, case_number=?, comments=? WHERE id=?");
            $stmt->bind_param("ssssi", $assignedOfficer, $deadline, $caseNumber, $comments, $_POST['crime_report_id']);
            $stmt->execute();

            // Check for errors and affected rows
            if ($stmt->error) {
                $_SESSION['error'] = "Database error: " . $stmt->error;
                echo "<script>alert('Failed to update the report due to a database error.');</script>";
            } elseif ($stmt->affected_rows > 0) {
                echo "<script>alert('Report updated successfully!');</script>";
            } else {
                echo "<script>alert('No changes were made to the report.');</script>";
            }
            $stmt->close();
        }
    }
} elseif (!empty($crimeReportId)) {
    // Fetching existing data for editing
    $stmt = $conn->prepare("SELECT * FROM crime_reports WHERE id = ?");
    $stmt->bind_param("i", $crimeReportId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $report = $result->fetch_assoc();
        $caseNumber = $report['case_number'];
        $comments = $report['comments'];
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
    <title>Update Crime Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../crime/user.css">

    <script>
    function validateForm() {
        var caseNumber = document.getElementById("case_number").value;
        var assignedOfficer = document.getElementById("assigned_officer").value;
        var deadline = document.getElementById("deadline").value;

        if (!/^[A-Z]{2}-\d{4}$/.test(caseNumber)) {
            alert("Case number must follow the format XX-1234.");
            return false;
        }

        if (assignedOfficer.trim() === "") {
            alert("Assigned Officer must be filled out");
            return false;
        }

        var currentDateTime = new Date();
        var selectedDateTime = new Date(deadline);

        if (selectedDateTime <= currentDateTime) {
            alert("Deadline must be in the future");
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
        <li><a href="contact_us_messages.php" class="sidebar-link"><i class="fas fa-comments"></i> Messages</a></li>
        <li><a href="../crime/logout.php" class="sidebar-link logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a></li>
    </ul>
</nav>

<div class="main">
    <div class="container">
        <div class="reports-table">
            <form method="post" onsubmit="return validateForm()">
                <h2>Assign case</h2>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <label for="case_number">Case Number:</label><br>
                <input type="text" id="case_number" name="case_number" value="<?php echo htmlspecialchars($caseNumber); ?>" required>
                <span class="error"><?php echo $errors['case_number'] ?? ''; ?></span><br>

                <label for="assigned_officer">Assigned Officer:</label><br>
                <select id="assigned_officer" name="assigned_officer" required>
                    <option value="">Select Officer</option>
                    <?php foreach ($officers as $officer) : ?>
                        <option value="<?php echo $officer['full_name']; ?>" <?php if (isset($report) && $report['assigned_officer'] == $officer['user_id']) echo "selected"; ?>>
                            <?php echo htmlspecialchars($officer['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <label for="deadline">Deadline:</label><br>
                <input type="datetime-local" id="deadline" name="deadline" value="<?php echo isset($report['deadline']) ? date('Y-m-d\TH:i', strtotime($report['deadline'])) : ''; ?>" required><br>
                <span class="error"><?php echo $errors['deadline'] ?? ''; ?></span><br>

                <label for="comments">Comments:</label><br>
                <textarea id="comments" name="comments"><?php echo htmlspecialchars($comments); ?></textarea><br>

                <input type="hidden" name="crime_report_id" value="<?php echo $crimeReportId; ?>">
                <input type="submit" value="Assign case">
            </form>
        </div>
    </div>
</div>

<footer class="footer">
    <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>
</body>
</html>
