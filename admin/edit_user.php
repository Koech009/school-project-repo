<?php
session_start();
require_once("../db/dbconfic.php");

// Check if user ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "User ID not provided.";
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// Fetch user details from the database
$stmt = $conn->prepare("SELECT * FROM tbl_user WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "User not found.";
    header("Location: users.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

// Function to get unread crime reports count
function getUnreadCrimeReportsCount($conn) {
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
    return 0;
}

$total_unread = getUnreadCrimeReportsCount($conn);

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate form data
    $full_name = $conn->real_escape_string(trim($_POST['full_name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $address = $conn->real_escape_string(trim($_POST['address']));
    $date_of_birth = $conn->real_escape_string(trim($_POST['date_of_birth']));
    $id_passport = $conn->real_escape_string(trim($_POST['id_passport']));
    $gender = $conn->real_escape_string(trim($_POST['gender']));

    // Validate form data
    $errors = [];
    if (empty($full_name)) {
        $errors['full_name'] = "Full name is required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (empty($phone)) {
        $errors['phone'] = "Phone number is required.";
    }

    if (empty($address)) {
        $errors['address'] = "Address is required.";
    }

    if (empty($date_of_birth)) {
        $errors['date_of_birth'] = "Date of birth is required.";
    }

    if (!preg_match('/^\d{8}$/', $id_passport)) {
        $_SESSION['error']['id_passport'] = "ID or Passport number must be 8 digits.";
    }


    if (count($errors) === 0) {
        // Update user details
        $stmt = $conn->prepare("UPDATE tbl_user SET full_name=?, email=?, phone=?, address=?, date_of_birth=?, id_passport=?, gender=? WHERE user_id=?");
        $stmt->bind_param("sssssssi", $full_name, $email, $phone, $address, $date_of_birth, $id_passport, $gender, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // $_SESSION['success_message'] = "User details updated successfully.";
            $_SESSION['success_message'] = "User details updated successfully.";
            echo "<script>alert('User details updated successfully.');</script>";
            echo "<script>window.location.href = 'users.php';</script>";
            exit();
        } else {
            $_SESSION['error'] = "Failed to update user details.";
            header("Location: edit_user.php");
        exit();
        }

        $stmt->close();
        
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
    <!-- <link rel="stylesheet" href="admin.css"> -->
    <link rel="stylesheet" href="../crime/user.css">

    <title>Update Profile</title>
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
            <h2>Edit User</h2> 
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $user_id; ?>" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            
                    <!-- Display existing user data in form fields -->
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required><br><br>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"><br><br>

                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>"><br><br>

                    <label for="date_of_birth">Date of Birth:</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth']); ?>"><br><br>

                    <label for="id_passport">ID Passport:</label>
                    <input type="text" id="id_passport" name="id_passport" value="<?php echo htmlspecialchars($user['id_passport']); ?>"><br><br>

                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="male" <?php echo ($user['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($user['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                    </select><br><br>

                    <!-- <button type="submit" name="submit">Update</button> -->
                    <input type="submit" value="Update">

                </form>
                
            </div>
        </div>
    </div>


    <footer class="footer">
        <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>
    <script>
        function validateForm() {
            var fullName = document.getElementById("full_name").value;
            var email = document.getElementById("email").value;
            var phone = document.getElementById("phone").value;
            var address = document.getElementById("address").value;
            var dateOfBirth = document.getElementById("date_of_birth").value;
            var idPassport = document.getElementById("id_passport").value;

            if (fullName.trim() === "") {
                alert("Full Name must be filled out");
                return false;
            }
            if (email.trim() === "") {
                alert("Email must be filled out");
                return false;
            }
            if (phone.trim() === "") {
                alert("Phone must be filled out");
                return false;
            }
            if (address.trim() === "") {
                alert("Address must be filled out");
                return false;
            }
            if (dateOfBirth.trim() === "") {
                alert("Date of Birth must be filled out");
                return false;
            }
            if (idPassport.trim() === "") {
                alert("ID or Passport must be filled out");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
