<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user"]["user_id"]) || !isset($_SESSION["user"]["role"])) {
    // If the user is not logged in, redirect to the login page
    header("Location: ../crime/login.php");
    exit;
}

// Database connection
require_once("../db/dbconfic.php");

$errorMessage = ""; // Initialize error message
$successMessage = ""; // Initialize success message

// Use 'user_id' from the session array, not 'user_id' directly
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';
//$userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';

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


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if the form fields are set
    if (isset($_POST["user_id"]) && isset($_POST["new_password"]) && isset($_POST["confirm_password"])) {
        // Retrieve form data
        $id = $_POST["user_id"];
        $newPassword = $_POST["new_password"];
        $confirmPassword = $_POST["confirm_password"];

        // Password validation
        if ($newPassword !== $confirmPassword) {
            $errorMessage = "Passwords do not match";
        } else {
            // Validate password strength
            if (strlen($newPassword) < 8 || !preg_match("/[a-zA-Z]/", $newPassword) || !preg_match("/\d/", $newPassword) || !preg_match("/[@$!%*#?&]/", $newPassword)) {
                $errorMessage = "Password must be at least 8 characters long and contain at least one letter, one digit, and one special character (@$!%*#?&)";
            } else {
                // Check if the user exists in the database
                $stmt = $conn->prepare("SELECT * FROM tbl_user WHERE user_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    // Hash the new password
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    // Update user password in the database
                    $updateStmt = $conn->prepare("UPDATE tbl_user SET password = ? WHERE user_id = ?");
                    $updateStmt->bind_param("si", $hashedPassword, $id);

                    if ($updateStmt->execute()) {
                        $successMessage = "Password reset successfully";
                    } else {
                        $errorMessage = "Error resetting password: " . $updateStmt->error;
                    }

                    $updateStmt->close();
                } else {
                    $errorMessage = "No user found with the provided ID";
                }

                $stmt->close();
            }
        }
    } else {
        $errorMessage = "Invalid form data";
    }
}

// Close database connection
$conn->close();
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
            
            <p>User ID: <?php echo $userId; ?></p>
                <h2>Password Reset</h2>
                <?php if ($errorMessage): ?>
                    <div class="error-message"><?php echo $errorMessage; ?></div>
                <?php endif; ?>
                <?php if ($successMessage): ?>
                    <div class="success-message"><?php echo $successMessage; ?></div>
                <?php endif; ?>
                <form action="reset_password.php?user_id=<?php echo htmlspecialchars($userId); ?>" method="POST" onsubmit="return reset_password()">

                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">


                    <div class="form-group">
                        <label for="new_password">New Password:</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit">Reset Password</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>
    <script>
      function reset_password() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');

            // Event listener for new password
            newPassword.addEventListener('input', function () {
                if (newPassword.value.length < 8 || !/[a-zA-Z]/.test(newPassword.value) || !/\d/.test(newPassword.value) || !/[@$!%*#?&]/.test(newPassword.value)) {
                    newPassword.setCustomValidity('Password must be at least 8 characters long and contain at least one letter, one digit, and one special character (@$!%*#?&)');
                } else {
                    newPassword.setCustomValidity('');
                }
                confirmPassword.setCustomValidity('');
            });

            // Event listener for confirm password
            confirmPassword.addEventListener('input', function () {
                if (confirmPassword.value !== newPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            });

            // Check if success message is present and alert success
            const successMessage = "<?php echo $successMessage; ?>";
            if (successMessage) {
                alert(successMessage);
            }
        });
    </script>
    
</body>
</html>
