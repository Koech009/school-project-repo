<?php
session_start();
require_once("../db/dbconfic.php");

$errors = []; // Array to store error messages

// Function to handle redirection based on user role
function redirectToDashboard($role) {
    switch ($role) {
        case "user":
            header("Location: user_dashboard.php");
            exit;
        case "officer":
            header("Location: ../officer/officer_dashboard.php");
            exit;
        case "admin":
            header("Location: ../admin/admin_dashboard.php");
            exit;
        default:
            // Handle invalid roles
            session_destroy();
            header("Location: login.php?error=invalid_role");
            exit;
    }
}

// Check if the user is already logged in
if (isset($_SESSION["user"]["user_id"])) {
    // Redirect them to their dashboard
    redirectToDashboard($_SESSION["user"]["role"]);
}

// Initialize session variables 
if (!isset($_SESSION["user"])) {
    $_SESSION["user"] = array("user_id" => null, "role" => null);
}

// Check if the login form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];


    // Server-side validation for empty email
    if (empty($email)) {
        $errors[] = "Email address is required.";
    }

    // Server-side validation for empty password
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // Server-side validation for email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Server-side validation for password length
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }


    // Server-side validation for password complexity
    if (strlen($password) < 8 || !preg_match("/[a-zA-Z]/", $password) || !preg_match("/\d/", $password) || !preg_match("/[@$!%*#?&]/", $password)) {
       
        $error[] = "Password must be at least 8 characters long and contain at least one letter, one digit, and one special character (@$!%*#?&)";

    }

   
    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT user_id, user_type, password, is_approved FROM tbl_user WHERE email = ?");
    if (!$stmt) {
        // Handle error when the statement couldn't be prepared
        $errors[] = "Statement preparation failed.";
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Check if the user account is approved or if it's an admin or officer account
if ($row['user_type'] === 'user' || ($row['is_approved'] == 1 && ($row['user_type'] === 'admin' || $row['user_type'] === 'officer'))) {
    // Set session variables
    $_SESSION['user']['user_id'] = $row['user_id'];
    $_SESSION['user']['role'] = $row['user_type'];
    // Redirect based on user role
    redirectToDashboard($row['user_type']);
} else {
    $errors[] = "Your account is not approved yet.";
}

            } else {
                $errors[] = "Invalid password.";
            }
        } else {
            $errors[] = "No user found with this email.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login page</title>
    <link rel="stylesheet" href="../CSS/login.css">
   
</head>

<body>
    <div class="container">
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>

        <form action="login.php" method="POST" id="form" name="loginForm" onsubmit="return validateForm()">
            <h3>Login Form</h3>
            
                <div class="form-group">
                    <label for="email" class="form-label">Email address:</label>
                    <input type="email" placeholder="Enter your Email address:" name="email" class="form-control>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" placeholder="Enter your password:" name="password" class="form-control <?php if (isset($errors) && count($errors) > 0) echo "error"; ?>"
                        required>
                    <?php if (isset($errors) && count($errors) > 0): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors[0]); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-btn">
                    <input type="submit" value="Login" name="login" class="btn btn-primary">
                </div>
                <div class="forgot-password">
                    <a href="forgot-password.php">Forgot Password?</a>
                </div>
                <div class="create-account">
                    <p>Don't have an account? <a href="register.php?register=true">Create Account</a></p>
                </div>
            
        </form>
    </div>

    <script>
    function validateForm() {
    var email = document.getElementById('email').value;
    var password = document.getElementById('password').value;
    var emailError = document.getElementById('emailError');
    var passwordError = document.getElementById('passwordError');

    // Reset errors
    emailError.innerHTML = '';
    passwordError.innerHTML = '';

    // Check if email is empty
    if (email.trim() === '') {
        emailError.innerHTML = 'Email address is required.';
        return false;
    }

    // Check if password is empty
    if (password.trim() === '') {
        passwordError.innerHTML = 'Password is required.';
        return false;
    }

    // Email format validation
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        emailError.innerHTML = 'Please enter a valid email address.';
        return false;
    }

    // Password format validation
    var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
    if (!passwordRegex.test(password)) {
        passwordError.innerHTML = 'Password must contain at least one lowercase letter, one uppercase letter, one digit, and be at least 8 characters long.';
        return false;
    }

    return true;
}

</script>

</body>
</html>
