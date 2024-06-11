<?php
session_start();
require_once("../db/dbconfic.php");

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION["user"]["user_id"])) {
        header("Location: ../crime/login.php");
        exit;
    }

    $userId = $_SESSION["user"]["user_id"];

    function sanitizeData($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    function validateDate($date) {
        $currentDate = date('Y-m-d');
        return ($date <= $currentDate);
    }

    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoTmpName = $_FILES['photo']['tmp_name'];
        $photoName = $_FILES['photo']['name'];
        $photoPath = '../uploads/' . $photoName; 

        if (move_uploaded_file($photoTmpName, $photoPath)) {
            $firstName = sanitizeData($_POST["first_name"]);
            $lastName = sanitizeData($_POST["last_name"]);
            $dateOfBirth = sanitizeData($_POST["date_of_birth"]);
            $gender = sanitizeData($_POST["gender"]);
            $height = sanitizeData($_POST["height"]);
            $weight = sanitizeData($_POST["weight"]);
            $hairColor = sanitizeData($_POST["hair_color"]);
            $eyeColor = sanitizeData($_POST["eye_color"]);
            $dateLastSeen = sanitizeData($_POST["date_last_seen"]);
            $lastSeenLocation = sanitizeData($_POST["last_seen_location"]);
            $description = sanitizeData($_POST["description"]);
            $yourEmail = sanitizeData($_POST["your_email"]);
            $yourPhone = sanitizeData($_POST["your_phone"]);

            if (!preg_match("/^[a-zA-Z]+$/", $firstName)) {
                $errors[] = "First name should contain only letters.";
            }

            if (!preg_match("/^[a-zA-Z]+$/", $lastName)) {
                $errors[] = "Last name should contain only letters.";
            }

            if (!is_numeric($height) || $height <= 0) {
                $errors[] = "Height must be a valid number greater than 0.";
            }

            if (!is_numeric($weight) || $weight <= 0) {
                $errors[] = "Weight must be a valid number greater than 0.";
            }

            if (!validateDate($dateLastSeen)) {
                $errors[] = "Date last seen should be a valid date in the past.";
            }

            if (!validateEmail($yourEmail)) {
                $errors[] = "Please enter a valid email address.";
            }

            if (!preg_match("/^\d{10}$/", $yourPhone)) {
                $errors[] = "Phone number should be exactly 10 digits.";
            }

            if (empty($errors)) {
                $sql = "INSERT INTO missing_persons (FirstName, LastName, DateOfBirth, Gender, Height, Weight, HairColor, EyeColor, DateLastSeen, LastSeenLocation, Description, ContactEmail, ContactPhone, PhotoPath) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssssssssssssss", $firstName, $lastName, $dateOfBirth, $gender, $height, $weight, $hairColor, $eyeColor, $dateLastSeen, $lastSeenLocation, $description, $yourEmail, $yourPhone, $photoPath);

                    if ($stmt->execute()) {
                        echo "<script>alert('Missing person report submitted successfully.');</script>";
                    } else {
                        echo "<script>alert('Error: " . $stmt->error . "');</script>";
                    }

                    $stmt->close();
                } else {
                    echo "<script>alert('Error preparing statement.');</script>";
                }
            }
        } else {
            $errors[] = "Error uploading photo.";
        }
    } else {
        $errors[] = "Please upload a photo of the missing person.";
    }

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
            <h1>Missing Person Information</h1>
             
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
               
                <form action="missing_person.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <h2>Missing Person info</h2>
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required><br>

                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required><br>

                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" required><br>

                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select><br>

                <label for="height">Height (in cm):</label>
                <input type="number" id="height" name="height" required><br>

                <label for="weight">Weight (in kg):</label>
                <input type="number" id="weight" name="weight" required><br>

                <label for="hair_color">Hair Color:</label>
                <input type="text" id="hair_color" name="hair_color" required><br>

                <label for="eye_color">Eye Color:</label>
                <input type="text" id="eye_color" name="eye_color" required><br>

                <label for="date_last_seen">Date Last Seen:</label>
                <input type="date" id="date_last_seen" name="date_last_seen" required><br>

                <label for="last_seen_location">Last Seen Location:</label>
                <input type="text" id="last_seen_location" name="last_seen_location" required><br>

                <label for="description">Description:</label><br>
                <textarea id="description" name="description" rows="4" cols="50" required></textarea><br>

                <label for="photo">Photo of Missing Person:</label>
                <input type="file" id="photo" name="photo" accept="image/*" required><br>
                
                <h2>Contact Information</h2>
                <label for="your_email">Email:</label>
                <input type="email" id="your_email" name="your_email" required><br>

                <label for="your_phone">Phone Number:</label>
                <input type="tel" id="your_phone" name="your_phone" required><br>

                <input type="submit" value="Submit Report">
            </form>
        </div>
    </div>
</div>

    <footer class="footer">
        <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
    </footer>
    
<script>
function validateForm() {
    var firstName = document.getElementById("first_name").value.trim();
    var lastName = document.getElementById("last_name").value.trim();
    var dateOfBirth = document.getElementById("date_of_birth").value;
    var gender = document.getElementById("gender").value;
    var height = document.getElementById("height").value.trim();
    var weight = document.getElementById("weight").value.trim();
    var hairColor = document.getElementById("hair_color").value.trim();
    var eyeColor = document.getElementById("eye_color").value.trim();
    var dateLastSeen = document.getElementById("date_last_seen").value;
    var lastSeenLocation = document.getElementById("last_seen_location").value.trim();
    var description = document.getElementById("description").value.trim();
    var photo = document.getElementById("photo").value.trim();
    var yourEmail = document.getElementById("your_email").value.trim();
    var yourPhone = document.getElementById("your_phone").value.trim();

    var isValid = true;
    var errorMessage = "";

    if (!/^[a-zA-Z]+$/.test(firstName)) {
        errorMessage += "First name should contain only letters.\n";
        isValid = false;
    }

    if (!/^[a-zA-Z]+$/.test(lastName)) {
        errorMessage += "Last name should contain only letters.\n";
        isValid = false;
    }

    if (dateOfBirth === "") {
        errorMessage += "Date of birth is required.\n";
        isValid = false;
    }

    if (gender === "") {
        errorMessage += "Gender is required.\n";
        isValid = false;
    }

    if (height === "" || isNaN(height) || parseFloat(height) <= 0) {
        errorMessage += "Height must be a valid number greater than 0.\n";
        isValid = false;
    }

    if (weight === "" || isNaN(weight) || parseFloat(weight) <= 0) {
        errorMessage += "Weight must be a valid number greater than 0.\n";
        isValid = false;
    }

    if (hairColor === "") {
        errorMessage += "Hair color is required.\n";
        isValid = false;
    }

    if (eyeColor === "") {
        errorMessage += "Eye color is required.\n";
        isValid = false;
    }

    if (dateLastSeen === "") {
        errorMessage += "Date last seen is required.\n";
        isValid = false;
    } else {
        var currentDate = new Date().toISOString().split('T')[0];
        if (dateLastSeen > currentDate) {
            errorMessage += "Date last seen should be in the past.\n";
            isValid = false;
        }
    }

    if (lastSeenLocation === "") {
        errorMessage += "Last seen location is required.\n";
        isValid = false;
    }

    if (description === "") {
        errorMessage += "Description is required.\n";
        isValid = false;
    }

    if (photo === "") {
        errorMessage += "Photo of missing person is required.\n";
        isValid = false;
    }

    if (yourEmail === "") {
        errorMessage += "Your email is required.\n";
        isValid = false;
    } else {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(yourEmail)) {
            errorMessage += "Please enter a valid email address.\n";
            isValid = false;
        }
    }

    if (yourPhone === "") {
        errorMessage += "Your phone number is required.\n";
        isValid = false;
    } else if (!/^\d{10}$/.test(yourPhone)) {
        errorMessage += "Phone number should be exactly 10 digits.\n";
        isValid = false;
    }

    if (!isValid) {
        alert(errorMessage);
    }

    return isValid;
}
</script>
</body>
</html>
