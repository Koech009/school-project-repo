<?php
// Start the session
session_start();

// Include your database connection file here (make sure the path is correct)
require_once("../db/dbconfic.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the user is logged in
if (isset($_SESSION["user"]["user_id"])) {
    // User is logged in, so retrieve user ID from session
    $userId = $_SESSION["user"]["user_id"];

    // Prepare a statement to fetch user data from the database
    $stmt = $conn->prepare("SELECT user_id, full_name FROM tbl_user WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user data is found
    if ($result->num_rows > 0) {
        // Fetch user data
        $fetchedUserData = $result->fetch_assoc();

        // Set session variables with the fetched data
        $_SESSION["user"]["full_name"] = $fetchedUserData["full_name"];
    } else {
        // For example, redirect to the login page or show an error message
        header("Location: login.php");
        exit;
    }

    $stmt->close();
} else {
    // If user is not logged in, redirect to the login page
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="user.css">
    <title>User Dashboard</title>
    <style>
        .error-message {
            
            background-color: #ffe5e5;
            color: #d9534f;
            border: 1px solid #d9534f;
            border-radius: 4px;
            padding: 10px;
            margin-top:20px;
        }

        .success-message{
           
            background-color: #e6f7e3;
            color: #5cb85c;
            border: 1px solid #5cb85c;
            border-radius: 4px;
            padding: 10px;
            margin-top:20px;
        }
        
    </style>
   
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
    <div class="container">
        <div class="reports-table">
            <h2>Crime Incident Report Form</h2>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                    <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <form action="submit_report.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <h3>Basic Information:</h3>
                <label for="date_time">Date and Time:</label>
                <input type="datetime-local" id="date_time" name="date_time" required><br>
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" required autocomplete="off"><br>
                <label for="reporter_name">Reporter's Name:</label>
                <input type="text" id="reporter_name" name="reporter_name" required><br>
                <label for="contact_info">Contact Information:</label>
                <input type="text" id="contact_info" name="contact_info" required><br>

                <h3>Incident Details:</h3>
                <label for="crime_type">Nature of Crime:</label>
                <select id="crime_type" name="crime_type" required>
                    <option value="Theft">Theft</option>
                    <option value="Arson">Theft</option>

                    <option value="Assault">Assault</option>
                    <option value="Vandalism">Vandalism</option>
                    <option value="Fraud">Fraud</option>
                    <option value="Domestic Violence">Domestic Violence</option>
                    <option value="Rape">Rape</option>
                    <option value="Robbery">Robbery</option>
                    <option value="Drug Trafficking">Drug trafficking</option>
                    <option value="Other">Other</option>
                </select><br>
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea><br>
                <label for="suspects">Suspect(s):</label>
                <input type="text" id="suspects" name="suspects"><br>
                <label for="victims">Victim(s):</label>
                <input type="text" id="victims" name="victims"><br>

                <h3>Evidence and Witnesses:</h3>
                <label for="evidence">Photos or Videos:</label>
                <input type="file" id="evidence" name="evidence[]" accept="image/*, video/*" multiple><br>
                
                
                <label for="witness_name">Witness Name:</label>
                <input type="text" id="witness_name" name="witness_name"><br>
                <label for="witness_contact">Witness Contact:</label>
                <input type="text" id="witness_contact" name="witness_contact"><br>

                <h3>Additional Information:</h3>
                <label for="injuries_damages">Injuries or Damages:</label>
                <input type="text" id="injuries_damages" name="injuries_damages"><br>
                <label for="other_details">Other Relevant Details:</label>
                <textarea id="other_details" name="other_details"></textarea><br>

                <h3>Privacy and Anonymity Options:</h3>
                <label for="anonymous_reporting">Anonymous Reporting:</label>
                <input type="checkbox" id="anonymous_reporting" name="anonymous_reporting" value="1"><br>

                <input type="submit" value="Submit" name="submitCrimeReport" >
            </form>
        </div>
    </div>
</div>


       
    <footer class="footer">
        <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>

    <script>
        function validateForm() {
    var location = document.getElementById("location").value.trim();
    var reporterName = document.getElementById("reporter_name").value.trim();
    var contactInfo = document.getElementById("contact_info").value.trim();
    var crimeType = document.getElementById("crime_type").value;
    var description = document.getElementById("description").value.trim();

    // Validate each field
    if (location === "" || reporterName === "" || contactInfo === "" || crimeType === "" || description === "") {
        alert("Please fill in all required fields.");
        return false;
    }

    // Validate contact information format
    if (!validateContactInfo(contactInfo)) {
        alert("Contact Information should be either a phone number or an email address.");
        return false;
    }

    // If all validations pass, return true
    return true;
}

function validateContactInfo(contactInfo) {
    // Regular expression to match either a phone number or an email address
    var phoneRegex = /^\d{10}$/; // Example phone number format: 10 digits
    var emailRegex = /^\S+@\S+\.\S+$/; // Example email format
    return phoneRegex.test(contactInfo) || emailRegex.test(contactInfo);
}

                        
                           
    </script>
</head>
</body>
</html>


	