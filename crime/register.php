<?php
session_start();
require_once("../db/dbconfic.php");

if (isset($_POST["submit"])) {
    // Sanitize and retrieve form data
    $full_name = $conn->real_escape_string(trim($_POST["full_name"]));
    $email = $conn->real_escape_string(trim($_POST["email"]));
    $password = $_POST["password"];
    $repeatPassword = $_POST["repeat-password"];
    $user_type = $conn->real_escape_string($_POST["user_type"]);
    $phone = $conn->real_escape_string($_POST["phone"]);
    $address = $conn->real_escape_string($_POST["address"]);
    $date_of_birth = $conn->real_escape_string($_POST["date_of_birth"]);
    $id_passport = $conn->real_escape_string($_POST["id_passport"]);
    $gender = $conn->real_escape_string($_POST["gender"]);

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Array to store validation errors
    $errors = array();

    // Validation checks
    if (!preg_match("/^[a-zA-Z\s]+$/", $full_name)) {
        array_push($errors, "Full name can only contain letters and spaces.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        array_push($errors, "Email is not valid.");
    }

    if (strlen($password) < 8 || !preg_match("/[A-Z]/", $password) || !preg_match("/[a-z]/", $password) || !preg_match("/\d/", $password) || !preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $password)) {
        array_push($errors, "Password does not meet the requirements.");
    }

    if ($password !== $repeatPassword) {
        array_push($errors, "Passwords do not match.");
    }

    $dob = new DateTime($date_of_birth);
    $now = new DateTime();
    $age = $now->diff($dob)->y;
    if ($age < 18) {
        array_push($errors, "You must be at least 18 years old.");
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM tbl_user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        array_push($errors, "Email already exists!");
    }

    // If no validation errors, insert data into the database
    if (count($errors) === 0) {
        // Check if the user is new
        $is_new_user = true;
        $stmt_check_existing = $conn->prepare("SELECT user_id FROM tbl_user WHERE email = ?");
        $stmt_check_existing->bind_param("s", $email);
        $stmt_check_existing->execute();
        $stmt_check_existing->store_result();
        if ($stmt_check_existing->num_rows > 0) {
            $is_new_user = false;
        }
        $stmt_check_existing->close();

        // Set is_approved based on user_type and whether it's a new user
        if ($is_new_user && ($user_type === 'admin' || $user_type === 'officer')) {
            $is_approved = 0; // Admins and officers need approval
        } else {
            $is_approved = 1; // Normal users don't need approval
        }

        // Prepare and bind the SQL statement
        $stmt = $conn->prepare("INSERT INTO tbl_user (full_name, email, password, user_type, phone, address, date_of_birth, id_passport, gender, is_approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssi", $full_name, $email, $passwordHash, $user_type, $phone, $address, $date_of_birth, $id_passport, $gender, $is_approved);

        
        // Execute the SQL statement
        if ($stmt->execute()) {
            $_SESSION['message'] = "<div class='alert alert-success'>Registration successful. Please wait for approval.</div>";
            header("location: register.php");
            exit;
        } else {
            $_SESSION['errors'] = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        // Store errors in session
        $_SESSION['errors'] = $errors;
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="../CSS/create.css">
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
<div class="container">
    <form action="register.php" method="POST" id="form" name="loginForm" onsubmit="return validateForm()">
        <h3>User Registration</h3>
        <div id="error-container">
            <?php
            if (isset($_SESSION['message'])) {
                echo $_SESSION['message'];
                unset($_SESSION['message']);
            }
            if (isset($_SESSION['errors'])) {
                foreach ($_SESSION['errors'] as $error) {
                    echo "<div class='alert alert-danger'>$error</div>";
                }
                unset($_SESSION['errors']);
            }
            ?>
        </div>
        <div class="form-group">
            <label for="full_name" class="form-label">Full Name:</label>
            <input type="text" class="form-control" name="full_name" id="full_name" placeholder="Enter your full name" required>
        </div>
        <div class="form-group">
            <label for="email" class="form-label">Email address:</label>
            <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email" required>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">Password:</label>
            <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
        </div>
        <div class="form-group">
            <label for="repeat-password" class="form-label">Confirm Password:</label>
            <input type="password" class="form-control" name="repeat-password" id="repeat-password" placeholder="Repeat your password" required>
        </div>
        <div class="form-group">
            <label for="phone" class="form-label">Phone:</label>
            <input type="tel" name="phone" id="phone" class="form-control" placeholder="+254 712 345 678" pattern="\+254\d{9}" required>
        </div>
        <div class="form-group">
            <label for="address" class="form-label">Address:</label>
            <input type="text" class="form-control" name="address" id="address" placeholder="Enter your address">
        </div>
        <div class="form-group">
            <label for="date_of_birth" class="form-label">Date of Birth:</label>
            <input type="date" class="form-control" name="date_of_birth" id="date_of_birth" required>
        </div>
        <div class="form-group">
            <label for="id_passport" class="form-label">ID/Passport:</label>
            <input type="text" class="form-control" name="id_passport" id="id_passport" placeholder="Enter your ID or passport number">
        </div>
        <div class="form-group">
            <label for="gender" class="form-label">Gender:</label>
            <select name="gender" id="gender" class="form-control" required>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="form-group">
            <label for="user_type" class="form-label">Register as:</label>
            <select name="user_type" id="user_type" class="form-control">
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="officer">Police Officer</option>
            </select>
        </div>
        <div class="form-btn">
            <button type="submit" class="btn btn-primary" name="submit">Create Account</button>
        </div>
        <div class="create-account">
            <p>If you already have an account <a href="login.php">Login</a></p>
        </div>
    </form>
</div>
<script>
    function validateForm() {
        const fullName = document.getElementById("full_name").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();
        const repeatPassword = document.getElementById("repeat-password").value.trim();
        const phone = document.getElementById("phone").value.trim();
        const dob = document.getElementById("date_of_birth").value;

        const namePattern = /^[a-zA-Z\s]+$/;
        if (!namePattern.test(fullName)) {
            alert("Full name can only contain letters and spaces.");
            return false;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            alert("Email is not valid.");
            return false;
        }

        if (password.length < 8 || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/\d/.test(password) || !/[!@#$%^&*()\-_=+{};:,<.>]/.test(password)) {
            alert("Password does not meet the requirements.");
            return false;
        }

        if (password !== repeatPassword) {
            alert("Passwords do not match.");
            return false;
        }
        const phonePattern = /^(?:\+?254)?(?:0)?\d{9}$/;
        if (!phonePattern.test(phone)) {
             alert("Phone number is not valid. Please enter a valid phone number.");
            return false;
        }

        

        const dateOfBirth = new Date(dob);
        const today = new Date();
        const age = today.getFullYear() - dateOfBirth.getFullYear();
        const month = today.getMonth() - dateOfBirth.getMonth();
        if (month < 0 || (month === 0 && today.getDate() < dateOfBirth.getDate())) {
            age--;
        }

        if (age < 18) {
            alert("You must be at least 18 years old.");
            return false;
        }

        return true;
    }
</script>
</body>
</html>
