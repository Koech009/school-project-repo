
<?php
// Include your database connection file here
require_once("../db/dbconfic.php");

// Check if the user is logged in
session_start();
if (!isset($_SESSION["user"]["user_id"])) {
    // If user is not logged in, redirect to the login page
    header("Location: login.php");
    exit;
}

// Fetch user ID from session
$userId = $_SESSION["user"]["user_id"];

// Function to create an alert
function createAlert($conn, $eventType, $userId, $details) {
    $sql = "INSERT INTO alerts (user_id, event_type, details) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $userId, $eventType, $details);
    if ($stmt->execute()) {
        echo "Alert created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Trigger an event for changing a password and create an alert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changePassword'])) {
    $userId = $_POST['userId'];
    createAlert($conn, 'Password Change', $userId, 'Your password has been changed.');
}

// Trigger an event for resetting a password and create an alert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resetPassword'])) {
    $userId = $_POST['userId'];
    createAlert($conn, 'Password Reset', $userId, 'Your password has been reset.');
}

// Trigger an event for submitting a crime report and create an alert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitCrimeReport'])) {
    $userId = $_POST['userId'];
    createAlert($conn, 'Crime Report Submission', $userId, 'Thank you for submitting your crime report.');
}

// Fetch alerts for the current user
$sql = "SELECT * FROM alerts WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$alerts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="user.css">
    <title>User Dashboard</title>
</head>
<body>
<header class="header">
    <div class="logo-container">
        <img src="../images/log.jpg" alt="Logo" class="logo">
        <span class="dashboard-title">User Dashboard</span>
    </div>
    <div class="header-right">
        <h3>Welcome: <?php echo htmlspecialchars($_SESSION["user"]["full_name"]); ?></h3>
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
    <div class="reports-table">
        <h2>All Notifications</h2>
        <div id="alerts">
            <?php foreach ($alerts as $alert): ?>
                <div>
                    <strong><?php echo htmlspecialchars($alert['event_type']); ?></strong>: <?php echo htmlspecialchars($alert['details']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script>
// Fetch alerts from the backend
fetch('alerts.php')
.then(response => response.json())
.then(alerts => {
    const alertsContainer = document.getElementById('alerts');
    alerts.forEach(alert => {
        const alertDiv = document.createElement('div');
        alertDiv.innerHTML = `<strong>${alert.eventType}</strong>: ${alert.details}`;
        alertsContainer.appendChild(alertDiv);
    });
})
.catch(error => console.error('Error fetching alerts:', error));
</script>
    
        </div>
    </div>
</div>


<footer class="footer">
    <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>


</body>
</html>