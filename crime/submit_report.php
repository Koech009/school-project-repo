
<?php
// Start the session
session_start();

// Include your database connection file here (make sure the path is correct)
require_once("../db/dbconfic.php");

$error_message = "";
$success_message="";

// Enable error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Function to sanitize input data
function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}

// Function to validate email format
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate phone number format
function validatePhoneNumber($phone) {
    return preg_match('/^\d{10}$/', $phone);
}

// Function to validate file types
function validateFileType($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'video/mp4']; // Add more allowed file types if needed
    return in_array($file['type'], $allowedTypes);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check for empty fields
    if (empty($_POST['date_time']) || empty($_POST['location']) || empty($_POST['reporter_name']) || empty($_POST['contact_info']) || empty($_POST['crime_type']) || empty($_POST['description'])) {
        echo "<script>alert('Please fill in all required fields.');</script>";
        exit();
    }

    // Validate date and time
    $currentDateTime = date('Y-m-d H:i:s');
    $date_time = sanitizeInput($_POST['date_time']);
    if ($date_time > $currentDateTime) {
        echo "<script>alert('Date and time must be either the present time or in the past.');</script>";
        exit();
    }

    // Validate maximum character limits for text input fields
    $maxCharacters = 1000;
    $fields = ['location', 'reporter_name', 'contact_info', 'crime_type', 'description', 'suspects', 'victims', 'witness_name', 'witness_contact', 'injuries_damages', 'other_details'];
    foreach ($fields as $field) {
        if (strlen($_POST[$field]) > $maxCharacters) {
            echo "<script>alert('Maximum $maxCharacters characters allowed for $field.');</script>";
            exit();
        }
    }

    // Validate contact information
    $contact_info = sanitizeInput($_POST['contact_info']);
    if (!validateEmail($contact_info) && !validatePhoneNumber($contact_info)) {
        echo "<script>alert('Contact Information should be either a valid email or a 10-digit phone number.');</script>";
        exit();
    }

    // Handle file upload for evidence
    $uploadDir = '../uploads/'; // Directory where you want to store uploaded files
    $filePaths = [];
    if (isset($_FILES['evidence']) && !empty($_FILES['evidence']['name'])) {
        foreach ($_FILES['evidence']['tmp_name'] as $key => $tmpName) {
            $filename = basename($_FILES['evidence']['name'][$key]);
            $destination = $uploadDir . $filename;
            // Move the uploaded file to the designated upload directory
            if (move_uploaded_file($tmpName, $destination) && validateFileType($_FILES['evidence'])) {
                $filePaths[] = $destination; // Store file path in an array
            }
        }
    }

    // Store file paths in the database
    $evidencePath = implode(',', $filePaths); // Convert array of file paths to comma-separated string

    // Retrieve user_id from the session
    $userId = isset($_SESSION["user"]["user_id"]) ? $_SESSION["user"]["user_id"] : null;
    if ($userId === null) {
        echo "<script>alert('User ID not found. Please log in.');</script>";
        exit();
    }

    // Disable foreign key checks temporarily
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

    // Validate and sanitize the input data (server-side validation)
    $date_time = $conn->real_escape_string($_POST['date_time']);
    $location = $conn->real_escape_string($_POST['location']);
    // Check if the anonymous_reporting checkbox is checked, set its value accordingly
    $anonymous_reporting = isset($_POST['anonymous_reporting']) ? 1 : 0;
    $reporter_name = $anonymous_reporting ? 'Anonymous' : $conn->real_escape_string($_POST['reporter_name']);
    $contact_info = $anonymous_reporting ? 'Anonymous' : $conn->real_escape_string($_POST['contact_info']);
    $crime_type = $conn->real_escape_string($_POST['crime_type']);
    $description = $conn->real_escape_string($_POST['description']);
    $suspects = $conn->real_escape_string($_POST['suspects']);
    $victims = $conn->real_escape_string($_POST['victims']);
    $witness_name = $conn->real_escape_string($_POST['witness_name']);
    $witness_contact = $conn->real_escape_string($_POST['witness_contact']);
    $injuries_damages = $conn->real_escape_string($_POST['injuries_damages']);
    $other_details = $conn->real_escape_string($_POST['other_details']);

    // Prepare an SQL statement to insert the data into the crime_reports table
    $stmt = $conn->prepare("INSERT INTO crime_reports (user_id, date_time, location, reporter_name, contact_info, crime_type, description, suspects, victims, witness_name, witness_contact, injuries_damages, other_details, anonymous_reporting, evidence_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Bind the input data to the prepared statement
    $stmt->bind_param("issssssssssssis", $userId, $date_time, $location, $reporter_name, $contact_info, $crime_type, $description, $suspects, $victims, $witness_name, $witness_contact, $injuries_damages, $other_details, $anonymous_reporting, $evidencePath);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Display an alert if the report is submitted successfully
        echo "<script>alert('Report submitted successfully.');</script>";
    } else {
        // Display an alert if there's an error submitting the report
        echo "<script>alert('Error submitting report: " . $conn->error . "');</script>";
    }

    // Close the prepared statement
    $stmt->close();

    // Enable foreign key checks again
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

    // Redirect back to the form page after submission
    header("Location: report_crime.php");
    exit();
}

// Close the database connection
$conn->close();
?>

	