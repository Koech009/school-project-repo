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
    $crime_description = $_POST['crime']; 
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
        header("Location: most_wanted.php");
        exit();
    } else {
        // Error inserting data
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
