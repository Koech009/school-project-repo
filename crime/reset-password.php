<?php
require_once("../db/dbconfic.php");

$errors = []; // Array to store error messages

// Check if the token is provided in the URL
if (!isset($_GET["token"]) || empty($_GET["token"])) {
    $errors[] = "Token not provided";
}

// Retrieve the token from the URL and URL-decode it
$token = urldecode($_GET["token"]);
$token_hash = hash("sha256", $token);

// Check if the user was found
$sql = "SELECT * FROM tbl_user WHERE reset_token_hash = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $errors[] = "Prepare failed: " . $conn->error;
} else {
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user === null) {
        $errors[] = "Token not found";
    } elseif ($user['reset_token_hash'] !== $token_hash) {
        $errors[] = "Token hash mismatch";
    } elseif (strtotime($user["reset_token_expires_at"]) <= time()) {
        $errors[] = "Token has expired";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST["password"];
    $password_confirmation = $_POST["password_confirmation"];

    if ($new_password !== $password_confirmation) {
        $errors[] = "Passwords do not match";
    }

    if (strlen($new_password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    // Client-side password validation
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $new_password)) {
        $errors[] = "Password must contain at least one uppercase letter, one lowercase letter, one digit, one special character, and be at least 8 characters long";
    }

    if (empty($errors)) {
        // Hash the new password
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password
        $update_sql = "UPDATE tbl_user SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);

        if (!$update_stmt) {
            $errors[] = "Prepare failed: " . $conn->error;
        } else {
            $update_stmt->bind_param("si", $new_password_hash, $user['user_id']);
            $update_success = $update_stmt->execute();

            if ($update_success) {
                echo "<p style='color: green;'>Password updated successfully</p>";
                
            } else {
                $errors[] = "Failed to update password: " . $update_stmt->error;
            }
            $update_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../CSS/login.css">
</head>
<body>
    
<div class="container">
        
            <h3>Reset Password</h3>
            <form id="reset-password-form" class="form" method="post" action="" onsubmit="return validateForm()">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="form-group">
                    <label for="password" class="form-label">New password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirm password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>

                <div id="error-messages">
                    <?php
                    foreach ($errors as $error) {
                        echo "<p style='color: red;'>$error</p>";
                    }
                    ?>
                </div>

                <div class="form-btn">
                    <input type="submit" name="resetPassword" value="Reset password" class="btn btn-primary">
                </div>
            </form>
        
    </div>

    <script>
    function validateForm() {
        var password = document.getElementById("password").value;
        var confirmPassword = document.getElementById("password_confirmation").value;
        var errorDiv = document.getElementById("error-messages");
        errorDiv.innerHTML = "";

        // Check if passwords match
        if (password !== confirmPassword) {
            errorDiv.innerHTML += "<p style='color: red;'>Passwords do not match</p>";
            return false;
        }

        // Check if password is at least 8 characters long
        if (password.length < 8) {
            errorDiv.innerHTML += "<p style='color: red;'>Password must be at least 8 characters long</p>";
            return false;
        }

        // Check password format: at least one uppercase letter, one lowercase letter, one digit, one special character
        var formatRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!formatRegex.test(password)) {
            errorDiv.innerHTML += "<p style='color: red;'>Password must contain at least one uppercase letter, one lowercase letter, one digit, one special character, and be at least 8 characters long</p>";
            return false;
        }

        return true;
    }
</script>

</body>
</html>
