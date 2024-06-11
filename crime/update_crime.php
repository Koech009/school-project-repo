<!--not in use-->
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

// Check if crime ID is provided in the URL
if (!isset($_GET['crime_id']) || empty($_GET['crime_id'])) {
    // Redirect to view_crime.php if crime ID is not provided
    header("Location: view_crime.php");
    exit;
}

// Fetch user ID from session
$userId = $_SESSION["user"]["user_id"];

// Fetch crime ID from URL
$crimeId = $_GET['crime_id'];

// Prepare a statement to fetch the crime report data along with creation date from the database
$stmt = $conn->prepare("SELECT *, DATEDIFF(CURRENT_DATE, date_time) AS days_since_creation FROM crime_reports WHERE user_id = ? AND id = ?");
$stmt->bind_param("ii", $userId, $crimeId);
$stmt->execute();
$result = $stmt->get_result();

// Check if the crime report exists and belongs to the user
if ($result->num_rows !== 1) {
    // If crime report does not exist or does not belong to the user, redirect to view_crime.php
    header("Location: view_crime.php");
    exit;
}

// Fetch crime report data
$crimeData = $result->fetch_assoc();

// Check if the update period has expired
$updateDisabled = ($crimeData['days_since_creation'] > 14);

// Define variables and initialize with empty values
$locationErr = $crimeTypeErr = $descriptionErr = "";
$location = $crimeType = $description = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && !$updateDisabled) {
    // Validate form fields
    $location = validate_input($_POST["location"]);
    $crimeType = validate_input($_POST["crime_type"]);
    $description = validate_input($_POST["description"]);

    // Validate location
    if (empty($location)) {
        $locationErr = "Location is required";
    }

    // Validate crime type
    if (empty($crimeType)) {
        $crimeTypeErr = "Crime type is required";
    }

    // Validate description
    if (empty($description)) {
        $descriptionErr = "Description is required";
    }

    // If all fields are validated, update the crime report in the database
    if (empty($locationErr) && empty($crimeTypeErr) && empty($descriptionErr)) {
        // Retrieve other form data
        $suspects = validate_input($_POST['suspects']);
        $victims = validate_input($_POST['victims']);
        $witnessName = validate_input($_POST['witness_name']);
        $witnessContact = validate_input($_POST['witness_contact']);
        $injuriesDamages = validate_input($_POST['injuries_damages']);
        $otherDetails = validate_input($_POST['other_details']);

        // Update the crime report in the database
        $stmt = $conn->prepare("UPDATE crime_reports SET location = ?, crime_type = ?, description = ?, suspects = ?, victims = ?, witness_name = ?, witness_contact = ?, injuries_damages = ?, other_details = ? WHERE id = ?");
        $stmt->bind_param("sssssssssi", $location, $crimeType, $description, $suspects, $victims, $witnessName, $witnessContact, $injuriesDamages, $otherDetails, $crimeId);
        // After successful update
        if ($stmt->execute()) {
            echo "<script>alert('Crime report updated successfully!');</script>";
            // Redirect to view_crime.php after successful update
            header("Location: view_crime.php");
            exit;
        } else {
            // Handle update failure (you may display an error message)
            echo "Error updating record: " . $conn->error;
        }
    }
}

// Function to validate input data
function validate_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
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
    <div class="container">
        <div class="reports-table">
<form method="POST">
        <label for="location">Location:</label><br>
        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($crimeData['location']); ?>">
        <span class="error"><?php echo $locationErr; ?></span><br><br>

        <label for="crime_type">Crime Type:</label><br>
        <input type="text" id="crime_type" name="crime_type" value="<?php echo htmlspecialchars($crimeData['crime_type']); ?>">
        <span class="error"><?php echo $crimeTypeErr; ?></span><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description"><?php echo htmlspecialchars($crimeData['description']); ?></textarea>
        <span class="error"><?php echo $descriptionErr; ?></span><br><br>

        <label for="suspects">Suspects:</label><br>
        <input type="text" id="suspects" name="suspects" value="<?php echo htmlspecialchars($crimeData['suspects']); ?>"><br><br>

        <label for="victims">Victims:</label><br>
        <input type="text" id="victims" name="victims" value="<?php echo htmlspecialchars($crimeData['victims']); ?>"><br><br>

        <label for="witness_name">Witness Name:</label><br>
        <input type="text" id="witness_name" name="witness_name" value="<?php echo htmlspecialchars($crimeData['witness_name']); ?>"><br><br>

        <label for="witness_contact">Witness Contact:</label><br>
        <input type="text" id="witness_contact" name="witness_contact" value="<?php echo htmlspecialchars($crimeData['witness_contact']); ?>"><br><br>

        <label for="injuries_damages">Injuries/Damages:</label><br>
        <input type="text" id="injuries_damages" name="injuries_damages" value="<?php echo htmlspecialchars($crimeData['injuries_damages']); ?>"><br><br>

        <label for="other_details">Other Details:</label><br>
        <textarea id="other_details" name="other_details"><?php echo htmlspecialchars($crimeData['other_details']); ?></textarea><br><br>

        <input type="submit" value="Submit" <?php if ($updateDisabled) echo "disabled"; ?>>
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
</body>
</html>

<!--not in use-->