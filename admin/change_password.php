<?php
session_start();

// Include your database connection file here (make sure the path is correct)
require_once("../db/dbconfic.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the user is logged in
if (!isset($_SESSION["user"]["user_id"])) {
    // If user is not logged in, redirect to the login page
    header("Location: login.php");
    exit;
}

// Initialize variables
$alertMessage = "";

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


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>alert('CSRF token mismatch.');</script>";
    } else {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Fetch user ID from session
        $userId = $_SESSION["user"]["user_id"];

        // Prepare a statement to fetch user data from the database
        $stmt = $conn->prepare("SELECT password FROM tbl_user WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user data is found
        if ($result->num_rows > 0) {
            // Fetch user data
            $userData = $result->fetch_assoc();

            // Verify old password
            if (password_verify($old_password, $userData['password'])) {
                // Validate new password
                if ($new_password === $confirm_password) {
                    // Check if new password meets password policy criteria
                    $passwordPolicy = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
                    if (preg_match($passwordPolicy, $new_password)) {
                        // Update password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_stmt = $conn->prepare("UPDATE tbl_user SET password = ? WHERE user_id = ?");
                        $update_stmt->bind_param("si", $hashed_password, $userId);
                        if ($update_stmt->execute()) {
                            // Password updated successfully
                            echo "<script>alert('Password updated successfully.');</script>";
                        } else {
                            // Error updating password
                            echo "<script>alert('Error updating password.');</script>";
                        }
                    } else {
                        // New password does not meet criteria
                        echo "<script>alert('Password does not meet the required criteria.');</script>";
                    }
                } else {
                    // New passwords do not match
                    echo "<script>alert('New passwords do not match.');</script>";
                }
            } else {
                // Incorrect old password
                echo "<script>alert('Incorrect old password.');</script>";
            }
        } else {
            // User data not found
            echo "<script>alert('User not found.');</script>";
        }

        $stmt->close();
    }
}

// Generate CSRF token for the form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../crime/user.css">
    <title>Change Password</title>
    <style>
    .error {
        color: red;
        font-size: 14px;
        margin-top: 5px;
    }
</style>

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
            <h2>Change Password</h2>
            <form id="changePasswordForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="form-group">
                    <label for="old_password">Old Password:</label>
                    <input type="password" id="old_password" name="old_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                    <span id="passwordError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <span id="confirmPasswordError" class="error"></span>
                </div>
                <button type="submit">Change Password</button>
            </form>
        </div>
    </div>
</div>

<footer class="footer">
        <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('changePasswordForm');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordError = document.getElementById('passwordError');
        const confirmPasswordError = document.getElementById('confirmPasswordError');

        form.addEventListener('submit', function (event) {
            if (!validatePassword(newPasswordInput.value)) {
                passwordError.textContent = 'Password must be at least 8 characters long.';
                event.preventDefault();
            } else {
                passwordError.textContent = '';
            }

            if (newPasswordInput.value !== confirmPasswordInput.value) {
                confirmPasswordError.textContent = 'Passwords do not match.';
                event.preventDefault();
            } else {
                confirmPasswordError.textContent = '';
            }
        });

        function validatePassword(password) {
            return password.length >= 8;
        }
    });
</script>
</body>
</html>
