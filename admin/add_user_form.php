<?php
// Database connection
require_once("../db/dbconfic.php");

$successMessage = ""; // Initialize variable to hold success message
$errorMessage = ""; // Initialize variable to hold error message

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
    return 0; // Default value if no unread reports found
}

$total_unread = getUnreadCrimeReportsCount($conn);


function addUser($conn, $full_name, $email, $phone, $address, $date_of_birth, $id_passport, $gender, $user_type) {
    $stmt = $conn->prepare("INSERT INTO tbl_user (full_name, email, phone, address, date_of_birth, id_passport, gender, user_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $full_name, $email, $phone, $address, $date_of_birth, $id_passport, $gender, $user_type);

    if ($stmt->execute()) {
        return true;
    } else {
        return $conn->error;
    }
}

function calculateAge($date_of_birth) {
    $birthDate = new DateTime($date_of_birth);
    $currentDate = new DateTime();
    $age = $currentDate->diff($birthDate)->y;
    return $age;
}

function isEmailUnique($conn, $email) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM tbl_user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] == 0;
}

$total_unread = getUnreadCrimeReportsCount($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    $date_of_birth = trim($_POST["date_of_birth"]);
    $id_passport = trim($_POST["id_passport"]);
    $gender = trim($_POST["gender"]);
    $user_type = trim($_POST["user_type"]);
    $errors = [];

    // Server-side validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($date_of_birth) || empty($id_passport) || empty($gender) || empty($user_type)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif (!preg_match("/^\d{10}$/", $phone)) {
        $errors[] = "Invalid phone number. It should be 10 digits.";
    } elseif (calculateAge($date_of_birth) < 18) {
        $errors[] = "User must be at least 18 years old.";
    } elseif (!isEmailUnique($conn, $email)) {
        $errors[] = "Email already exists. Please use a different email address.";
    }

    if (empty($errors)) {
        $full_name = $conn->real_escape_string($full_name);
        $email = $conn->real_escape_string($email);
        $phone = $conn->real_escape_string($phone);
        $address = $conn->real_escape_string($address);
        $date_of_birth = $conn->real_escape_string($date_of_birth);
        $id_passport = $conn->real_escape_string($id_passport);
        $gender = $conn->real_escape_string($gender);
        $user_type = $conn->real_escape_string($user_type);

        $result = addUser($conn, $full_name, $email, $phone, $address, $date_of_birth, $id_passport, $gender, $user_type);
        if ($result === true) {
            $successMessage = "User added successfully";
            echo "<script>alert('$successMessage'); window.location.href = 'users.php';</script>";
            exit();
        } else {
            $errors[] = "Error adding user: " . $result;
        }
    }

    // Display errors
    if (!empty($errors)) {
        $errorMessage = implode("<br>", $errors);
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
    <title>Add User</title>
    <style>
        .alert {
            margin-top: 20px;
        }
        .alert-danger {
            background-color: #ffe5e5;
            color: #d9534f;
            border: 1px solid #d9534f;
            border-radius: 4px;
            padding: 10px;
        }
        .alert-success {
            background-color: #e6f7e3;
            color: #5cb85c;
            border: 1px solid #5cb85c;
            border-radius: 4px;
            padding: 10px;
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
        <li><a href="../crime/logout.php" class="sidebar-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<div class="main">
    <div class="container">
        <div class="reports-table">
            <h2>Admin Panel - Add User Form</h2>
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
            <form id="addUserForm" method="POST">
                <h3 class="mb-3">Add User</h3>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name:</label>
                    <input type="text" class="form-control" name="full_name" id="full_name" placeholder="Enter full name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address:</label>
                    <input type="email" class="form-control" name="email" id="email" placeholder="Enter email" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone:</label>
                    <input type="tel" name="phone" id="phone" class="form-control" placeholder="Enter phone number" pattern="[0-9]{10}" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address:</label>
                    <input type="text" class="form-control" name="address" id="address" placeholder="Enter address" required>
                </div>
                <div class="mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth:</label>
                    <input type="date" class="form-control" name="date_of_birth" id="date_of_birth" required>
                </div>
                <div class="mb-3">
                    <label for="id_passport" class="form-label">ID/Passport:</label>
                    <input type="text" class="form-control" name="id_passport" id="id_passport" placeholder="Enter ID or passport number" required>
                </div>
                <div class="mb-3">
                    <label for="gender" class="form-label">Gender:</label>
                    <select name="gender" id="gender" class="form-control" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="user_type" class="form-label">User Type:</label>
                    <select name="user_type" id="user_type" class="form-control" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                        <option value="officer">Police Officer</option>
                    </select>
                </div>
                <div class="mb-3">
                    <input type="submit" value="Add User" class="btn btn-primary">
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="footer">
    <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('addUserForm');
    
    form.addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent form submission
        
        // Validate form fields
        if (!validateForm()) {
            return false; // Stop form submission if validation fails
        }

        // If validation passes, submit the form
        this.submit();
    });

    function validateForm() {
        const fullName = document.getElementById('full_name').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;
        const address = document.getElementById('address').value;
        const dateOfBirth = document.getElementById('date_of_birth').value;
        const idPassport = document.getElementById('id_passport').value;
        const gender = document.getElementById('gender').value;
        const userType = document.getElementById('user_type').value;

        if (fullName.trim() === '') {
            alert('Please enter full name');
            return false;
        }
        if (email.trim() === '' || !isValidEmail(email)) {
            alert('Please enter a valid email address');
            return false;
        }
        if (phone.trim() === '' || !isValidPhone(phone)) {
            alert('Please enter a valid phone number');
            return false;
        }
        if (address.trim() === '') {
            alert('Please enter address');
            return false;
        }
        if (dateOfBirth.trim() === '') {
            alert('Please enter date of birth');
            return false;
        }
        if (idPassport.trim() === '') {
            alert('Please enter ID or passport number');
            return false;
        }

        return true; 
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function isValidPhone(phone) {
        const phoneRegex = /^\d{10}$/;
        return phoneRegex.test(phone);
    }
});
</script>

</body>
</html>
