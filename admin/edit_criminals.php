<?php
// Start the session
session_start();

// Include database connection file
require_once("../db/dbconfic.php");

// Check if criminal ID is provided
if(isset($_GET['id']) && !empty($_GET['id'])) {
    // Retrieve criminal ID from the URL
    $criminal_id = $_GET['id'];

    // Function to get the count of unread crime reports for admin
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
    
    // Get the count of unread crime reports
    $total_unread = getUnreadCrimeReportsCount($conn);

    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve form data
        $name = $_POST['name'];
        $aliases = $_POST['aliases'];
        $age = $_POST['age'];
        $gender = $_POST['gender'];
        $last_location = $_POST['last_location'];
        $physical_characteristics = $_POST['physical_characteristics'];
        $nationality = $_POST['nationality'];
        $languages = $_POST['languages'];
        $risk_level = $_POST['risk_level'];
        $crime_description = $_POST['crime_description'];

        // Check if an image file is uploaded
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Process the uploaded image
            $image_path = "uploads/" . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        } else {
            // If no new image is uploaded, keep the existing image path
            $image_path = $_POST['current_image'];
        }

        // Validate form data
        $errors = array();

        // Validate name
        if(empty($name)) {
            $errors[] = "Name is required.";
        }

        // Validate age
        if(empty($age) || !is_numeric($age) || $age < 18 || $age > 100) {
            $errors[] = "Age must be a number between 18 and 100.";
        }
        if (empty($gender)) {
            $errors[] = "Gender is required.";
        }
        if (empty($crime_description)) {
            $errors[] = "Crime description is required.";
        }

        // If there are no validation errors, proceed with the update
        if(empty($errors)) {
            // Prepare and execute SQL query to update the criminal record
            $stmt = $conn->prepare("UPDATE most_wanted_criminals SET name=?, aliases=?, age=?, gender=?, last_location=?, physical_characteristics=?, nationality=?, languages=?, risk_level=?, crime_description=?, image_path=? WHERE id=?");
            $stmt->bind_param("ssissssssssi", $name, $aliases, $age, $gender, $last_location, $physical_characteristics, $nationality, $languages, $risk_level, $crime_description, $image_path, $criminal_id);
            $stmt->execute();

            // Check if the update was successful
            if($stmt->affected_rows > 0) {
                // Set success message
                echo "<script>alert('Criminal information updated successfully.'); window.location.href='view_criminals.php';</script>";
            exit(); 
            } else {
                // Set error message
                $_SESSION['error_message'] = "Failed to update criminal information. Please try again.";
                
            }

            // Close the prepared statement
            $stmt->close();
        } else {
            // Set error messages
            $_SESSION['error_messages'] = $errors;
        }
    }

    // Retrieve the criminal record from the database based on the provided ID
    $stmt = $conn->prepare("SELECT * FROM most_wanted_criminals WHERE id = ?");
    $stmt->bind_param("i", $criminal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $criminal = $result->fetch_assoc();

    // Close the prepared statement
    $stmt->close();
} else {
    // Redirect back to the page displaying criminals with an error message if criminal ID is not provided
    $_SESSION['error_message'] = "Criminal ID is missing.";
    header("Location: view_criminals.php");
    exit();
}

// Close the database connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../crime/user.css">
    <title>Update Criminal Info</title>
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
        <h2>Edit Criminal</h2>
            
            <!-- Display error messages -->
            <?php if(isset($_SESSION['error_messages']) && !empty($_SESSION['error_messages'])): ?>
                <div class="alert alert-danger">
                    <?php foreach($_SESSION['error_messages'] as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['error_messages']); ?>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $criminal_id; ?>" method="POST" enctype="multipart/form-data">
                <!-- Display existing criminal data in form fields -->
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($criminal['name']); ?>" required><br><br>

                <label for="aliases">Aliases:</label>
                <input type="text" id="aliases" name="aliases" value="<?php echo htmlspecialchars($criminal['aliases']); ?>"><br><br>

                <label for="age">Age:</label>
                <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($criminal['age']); ?>" required><br><br>

                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="male" <?php echo ($criminal['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo ($criminal['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                    <option value="other" <?php echo ($criminal['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                </select><br><br>

                <label for="last_location">Last Known Location:</label>
                <input type="text" id="last_location" name="last_location" value="<?php echo htmlspecialchars($criminal['last_location']); ?>"><br><br>

                <label for="physical_characteristics">Physical Characteristics:</label>
                <textarea id="physical_characteristics" name="physical_characteristics" rows="3" cols="50"><?php echo htmlspecialchars($criminal['physical_characteristics']); ?></textarea><br><br>

                <label for="nationality">Nationality:</label>
                <input type="text" id="nationality" name="nationality" value="<?php echo htmlspecialchars($criminal['nationality']); ?>"><br><br>

                <label for="languages">Languages Spoken:</label>
                <input type="text" id="languages" name="languages" value="<?php echo htmlspecialchars($criminal['languages']); ?>"><br><br>

                <label for="risk_level">Risk Level:</label>
                <select id="risk_level" name="risk_level">
                    <option value="low" <?php echo ($criminal['risk_level'] == 'low') ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo ($criminal['risk_level'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo ($criminal['risk_level'] == 'high') ? 'selected' : ''; ?>>High</option>
                    <option value="critical" <?php echo ($criminal['risk_level'] == 'critical') ? 'selected' : ''; ?>>Critical</option>
                </select><br><br>

                <label for="crime_description">Crime Description:</label><br>
                <textarea id="crime_description" name="crime_description" rows="4" cols="50" required><?php echo htmlspecialchars($criminal['crime_description']); ?></textarea><br><br>

               
                <img src="<?php echo htmlspecialchars($criminal['image_path']); ?>" alt="Criminal Image" width="200"><br><br>

              
                <label for="image">Upload New Image:</label>
                <input type="file" id="image" name="image" accept="image/*"><br><br>

            
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($criminal['image_path']); ?>">

                
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
        let errors = [];
        const name = document.getElementById('name').value.trim();
        const age = document.getElementById('age').value;
        const gender = document.getElementById('gender').value;
        const crimeDescription = document.getElementById('crime_description').value.trim();

        if (name === "") {
            errors.push("Name is required.");
        }
        if (isNaN(age) || age < 18 || age > 100) {
            errors.push("Age must be a number between 18 and 100.");
        }
        if (gender === "") {
            errors.push("Gender is required.");
        }
        if (crimeDescription === "") {
            errors.push("Crime description is required.");
        }

        if (errors.length > 0) {
            alert(errors.join("\n"));
            return false;
        }
        return true;
    }
</script>

</body>
</html>
