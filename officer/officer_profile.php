<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user"]["user_id"]) || !isset($_SESSION["user"]["role"])) {
    // If user is not logged in, redirect to the login page
    header("Location: login.php");
    exit;
}

require_once("../db/dbconfic.php");

// Fetch user details
$user_id = $_SESSION['user']['user_id'] ?? null;
if ($user_id) {
    $stmt = $conn->prepare("SELECT * FROM tbl_user WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "<script>alert('User not found.');</script>";
        exit;
    }
    $stmt->close();
} else {
    echo "<script>alert('No user ID in session.');</script>";
    exit;
}

// Handle profile update
if (isset($_POST['update'])) {
    // Sanitize and validate the input data
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $date_of_birth = $_POST['date_of_birth']; // No need for sanitization as it's a date
    $id_passport = $conn->real_escape_string($_POST['id_passport']);
    $gender = $conn->real_escape_string($_POST['gender']);
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.');</script>";
    } else {
        $update_stmt = $conn->prepare("UPDATE tbl_user SET full_name = ?, email = ?, phone = ?, address = ?, date_of_birth = ?, id_passport = ?, gender = ? WHERE user_id = ?");
        if ($update_stmt === false) {
            die('Error preparing update statement: ' . $conn->error);
        }

        // Bind parameters and execute the statement
        $update_success = $update_stmt->bind_param("sssssssi", $full_name, $email, $phone, $address, $date_of_birth, $id_passport, $gender, $user_id);
        if ($update_success === false) {
            die('Error binding parameters: ' . $update_stmt->error);
        }

        // Execute the statement
        if ($update_stmt->execute()) {
            echo "<script>alert('Profile updated successfully.');</script>";
            header("Location: user_profile.php");
            exit();
        } else {
            echo "<script>alert('Error updating profile: " . $conn->error . "');</script>";
        }

        $update_stmt->close();
    }
}

$delete_stmt = null; // Initialize $delete_stmt

// Handle account deletion
if (isset($_POST['delete'])) {
    // Password for account deletion
    $password = $_POST['password'];
    $hashed_password = $user['password'];
    // Verify the entered password against the hashed password
    if (password_verify($password, $hashed_password)) {
        // Passwords match, proceed with account deletion
        $delete_stmt = $conn->prepare("DELETE FROM tbl_user WHERE user_id = ?");
        $delete_stmt->bind_param("i", $user_id);
        if ($delete_stmt->execute()) {
            // Account deleted successfully
            echo "<script>alert('Account deleted successfully.');</script>";
            // Redirect to login page or display a message
            header("Location: login.php");
            exit();
        } else {
            echo "<script>alert('Error deleting account: " . $conn->error . "');</script>";
        }
    } else {
        // Passwords don't match, show error message
        echo "<script>alert('Incorrect password. Account not deleted.');</script>";
    }
}

// Close $delete_stmt if it's set
if ($delete_stmt instanceof mysqli_stmt) {
    $delete_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

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
        <h3>Welcome: <?php echo htmlspecialchars($_SESSION["user"]["full_name"]); ?></h3>
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
            

<?php if ($user): ?>   
    <div class="user-details">
    <h2>User Details</h2>
    <table>
        <tr>
            <td><strong>Full Name:</strong></td>
            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
        </tr>
        <tr>
            <td><strong>Email:</strong></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
        </tr>
        <tr>
            <td><strong>Phone:</strong></td>
            <td><?php echo htmlspecialchars($user['phone']); ?></td>
        </tr>
        <tr>
            <td><strong>Address:</strong></td>
            <td><?php echo htmlspecialchars($user['address']); ?></td>
        </tr>
        <tr>
            <td><strong>Date of Birth:</strong></td>
            <td><?php echo htmlspecialchars($user['date_of_birth']); ?></td>
        </tr>
        <tr>
            <td><strong>ID/Passport:</strong></td>
            <td><?php echo htmlspecialchars($user['id_passport']); ?></td>
        </tr>
        <tr>
            <td><strong>Gender:</strong></td>
            <td><?php echo htmlspecialchars($user['gender']); ?></td>
        </tr>
    </table>
</div>

<?php endif; ?>


<h2>User Profile</h2>

<form method="post" action="" enctype="multipart/form-data">
 
      
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo isset($user['full_name']) ? htmlspecialchars($user['full_name']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" value="<?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="address">Address:</label>
            <textarea id="address" name="address" required><?php echo isset($user['address']) ? htmlspecialchars($user['address']) : ''; ?></textarea>
        </div>
        <div class="form-group">
            <label for="date_of_birth">Date of Birth:</label>
            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo isset($user['date_of_birth']) ? htmlspecialchars($user['date_of_birth']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="id_passport">ID/Passport:</label>
            <input type="text" id="id_passport" name="id_passport" value="<?php echo isset($user['id_passport']) ? htmlspecialchars($user['id_passport']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="male" <?php echo (isset($user['gender']) && $user['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo (isset($user['gender']) && $user['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo (isset($user['gender']) && $user['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div class="form-group">
            <input type="submit" name="update" value="Update Profile">
        </div>
   
</form>

<h2>Delete Account</h2>
<form action="" method="post">
    <label for="password">Confirm Password to Delete Account:</label><br>
    <input type="password" id="password" name="password" required><br><br>
    <input type="submit" name="delete" value="Delete Account">
</form>
</div>   
</div>
</div>

    <footer class="footer">
        <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>
    
</body>
</html>


