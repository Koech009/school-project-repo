<?php
// Include database connection file
require_once("../db/dbconfic.php");

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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize form data
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $aliases = mysqli_real_escape_string($conn, trim($_POST['aliases']));
    $age = intval($_POST['age']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $last_location = mysqli_real_escape_string($conn, trim($_POST['last_location']));
    $physical_characteristics = mysqli_real_escape_string($conn, trim($_POST['physical_characteristics']));
    $nationality = mysqli_real_escape_string($conn, trim($_POST['nationality']));
    $languages = mysqli_real_escape_string($conn, trim($_POST['languages']));
    $risk_level = mysqli_real_escape_string($conn, $_POST['risk_level']);
    $crime_description = mysqli_real_escape_string($conn, trim($_POST['crime']));
    

    $image_path = ''; 

    // Check if an image file was uploaded
    if(isset($_FILES['image'])) {
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_type = $_FILES['image']['type'];
        $file_error = $_FILES['image']['error'];

        // Check if file is uploaded without errors
        if($file_error === 0){
            $image_path = '../uploads/' . $file_name; // Set the image path
            // Move uploaded file to specified destination
            move_uploaded_file($file_tmp, $image_path);
        } else {
            // Handle file upload error
            echo "Error uploading file: " . $file_error;
        }
    }
    if (empty($name) || empty($age) || empty($gender) || empty($risk_level) || empty($crime_description) || empty($_FILES['image']['name'])) {
        $error_message = "All fields are required.";
    } elseif ($age < 18 || $age > 100) {
        $error_message = "Age must be between 18 and 100.";
    } 
        

    // Insert data into database
    $sql = "INSERT INTO most_wanted_criminals (name, aliases, age, gender, last_location, physical_characteristics, nationality, languages, risk_level, crime_description, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Check if the prepare statement was successful
    if ($stmt === false) {
        die("Error preparing statement: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("ssissssssss", $name, $aliases, $age, $gender, $last_location, $physical_characteristics, $nationality, $languages, $risk_level, $crime_description, $image_path);
    $stmt->execute();

    // Check if data is inserted successfully
    if ($stmt->affected_rows > 0) {
        // Data inserted successfully, redirect to most wanted page
        echo "<script>alert('Criminal added successfully!'); window.location.href = 'view_criminals.php';</script>";

        exit();
    } else {
        // Error inserting data
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    // Close statement and connection
    $conn->close();
}
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
        Notifications</a></li>

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
                <h2>Add Most Wanted Criminal</h2>
                <?php if(isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
                <form id="addCriminalForm" enctype="multipart/form-data"  method="POST" >
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required><br><br>

                    <label for="aliases">Aliases:</label>
                    <input type="text" id="aliases" name="aliases"><br><br>

                    <label for="age">Age:</label>
                    <input type="number" id="age" name="age" required min="18" max="100"><br><br>

                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select><br><br>

                    <label for="last_location">Last Known Location:</label>
                    <input type="text" id="last_location" name="last_location"><br><br>

                    <label for="physical_characteristics">Physical Characteristics:</label>
                    <textarea id="physical_characteristics" name="physical_characteristics" rows="3" cols="50"></textarea><br><br>

                    <label for="nationality">Nationality:</label>
                    <input type="text" id="nationality" name="nationality"><br><br>

                    <label for="languages">Languages Spoken:</label>
                    <input type="text" id="languages" name="languages"><br><br>

                    <label for="risk_level">Risk Level:</label>
                    <select id="risk_level" name="risk_level" required>
                        <option value="">Select Risk Level</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select><br><br>

                    <label for="crime">Crime Description:</label><br>
                    <textarea id="crime" name="crime" rows="4" cols="50" required></textarea><br><br>

                    <label for="image">Upload Image:</label>
                    <input type="file" id="image" name="image" accept="image/*" required><br><br>

                    <input type="submit" value="Submit">
                </form>
            </div>
        </div>
    </div>

<footer class="footer">
    <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer> 
    <script>
        document.getElementById("addCriminalForm").addEventListener("submit", function(event) {
            var name = document.getElementById("name").value.trim();
            var age = document.getElementById("age").value;
            var gender = document.getElementById("gender").value;
            var riskLevel = document.getElementById("risk_level").value;
            var crimeDescription = document.getElementById("crime").value.trim();
            var image = document.getElementById("image").value;

            if (name === "" || age === "" || gender === "" || riskLevel === "" || crimeDescription === "" || image === "") {
                alert("Please fill in all required fields.");
                event.preventDefault();
            } else if (isNaN(age) || age < 18 || age > 100) {
                alert("Age must be a number between 18 and 100.");
                event.preventDefault();
            }
        });
    </script>
    
</body>
</html>
