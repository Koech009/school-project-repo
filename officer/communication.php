<?php
session_start();

// Include the database connection file
require_once("../db/dbconfic.php");

// Set up error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Check if the user is logged in
if (!isset($_SESSION["user"]["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user"]["user_id"];

// Fetch user data from the database
$stmt = $conn->prepare("SELECT user_id, full_name FROM tbl_user WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $fetchedUserData = $result->fetch_assoc();
    $_SESSION["user"]["full_name"] = $fetchedUserData["full_name"];
} else {
   
}

$stmt->close();

// Function to fetch messages from the database
function fetchMessages($conn) {
    $messages = array();
    $stmt = $conn->prepare("SELECT sender_id, content, timestamp FROM tbl_messages ORDER BY timestamp ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $senderStmt = $conn->prepare("SELECT full_name FROM tbl_user WHERE user_id = ?");
        $senderStmt->bind_param("i", $row['sender_id']);
        $senderStmt->execute();
        $senderResult = $senderStmt->get_result();
        if ($senderData = $senderResult->fetch_assoc()) {
            $row['sender'] = $senderData['full_name'];
        }
        $messages[] = $row;
    }
    return $messages;
}

$chatMessages = fetchMessages($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../crime/user.css">
    <title>Communication Page</title>
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
            <h2>Communication Page</h2>
        
            <div class="chat-container">
                <div id="chat-box"></div>
                <div class="input-container">
                    <input type="text" id="user-input" placeholder="Type your message...">
                    <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <p class="text-center">© <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.3.2/socket.io.js"></script>
<script>
    const socket = io();

    function appendMessage(sender, content) {
        const chatBox = document.getElementById("chat-box");
        const messageElement = document.createElement('div');
        messageElement.classList.add(sender === "<?php echo $_SESSION['user']['full_name']; ?>" ? 'sender-message' : 'receiver-message');
        messageElement.textContent = `${sender}: ${content}`;
        chatBox.appendChild(messageElement);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    socket.on("message", (message) => {
        appendMessage(message.sender, message.content);
    });

    function sendMessage() {
        const userInput = document.getElementById('user-input');
        const message = userInput.value;
        userInput.value = '';
        appendMessage("<?php echo $_SESSION['user']['full_name']; ?>", message);
        socket.emit("sendMessage", { sender: "<?php echo $_SESSION['user']['full_name']; ?>", content: message });
    }

    const initialMessages = <?php echo json_encode($chatMessages); ?>;
    initialMessages.forEach(message => {
        appendMessage(message.sender, message.content);
    });
</script>

</body>
</html>
