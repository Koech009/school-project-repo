<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user"]["user_id"]) || !isset($_SESSION["user"]["role"])) {
    // If the user is not logged in, redirect to the login page
    header("Location: ../crime/login.php");
    exit;
}

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

$total_unread = getUnreadCrimeReportsCount($conn);



// Retrieve total crimes reported in the year
$year = $_GET['year'] ?? date('Y'); // Default to current year if not provided
$sqlTotalCrimes = "SELECT COUNT(*) AS total_crimes FROM crime_reports WHERE YEAR(date_time) = $year";
$resultTotalCrimes = $conn->query($sqlTotalCrimes);
$rowTotalCrimes = $resultTotalCrimes->fetch_assoc();
$totalCrimes = $rowTotalCrimes['total_crimes'];

// Retrieve month with highest crime rate
$sqlHighestMonth = "SELECT MONTHNAME(date_time) AS month, COUNT(*) AS crime_count FROM crime_reports WHERE YEAR(date_time) = $year GROUP BY MONTH(date_time) ORDER BY crime_count DESC LIMIT 1";
$resultHighestMonth = $conn->query($sqlHighestMonth);
$rowHighestMonth = $resultHighestMonth->fetch_assoc();
$highestMonth = $rowHighestMonth['month'];

// Retrieve month with lowest crime rate
$sqlLowestMonth = "SELECT MONTHNAME(date_time) AS month, COUNT(*) AS crime_count FROM crime_reports WHERE YEAR(date_time) = $year GROUP BY MONTH(date_time) ORDER BY crime_count ASC LIMIT 1";
$resultLowestMonth = $conn->query($sqlLowestMonth);
$rowLowestMonth = $resultLowestMonth->fetch_assoc();
$lowestMonth = $rowLowestMonth['month'];
// Calculate percentage change compared to the previous year
$prevYear = $year - 1;
$sqlPrevYearTotalCrimes = "SELECT COUNT(*) AS total_crimes FROM crime_reports WHERE YEAR(date_time) = $prevYear";
$resultPrevYearTotalCrimes = $conn->query($sqlPrevYearTotalCrimes);
$rowPrevYearTotalCrimes = $resultPrevYearTotalCrimes->fetch_assoc();
$prevYearTotalCrimes = $rowPrevYearTotalCrimes['total_crimes'];

// Calculate percentage change if previous year's total crimes are not zero
$percentageChange = 0; // Default value
if ($prevYearTotalCrimes != 0) {
    $percentageChange = ($totalCrimes - $prevYearTotalCrimes) / $prevYearTotalCrimes * 100;
}


// Retrieve data for the bar graph
$sqlGraphData = "SELECT MONTH(date_time) AS month, COUNT(*) AS crime_count FROM crime_reports GROUP BY MONTH(date_time)";
$resultGraphData = $conn->query($sqlGraphData);

// Initialize arrays to store labels and data for the graph
$labels = [];
$data = [];

// Process query result
if ($resultGraphData->num_rows > 0) {
    while ($row = $resultGraphData->fetch_assoc()) {
        $labels[] = date("F", mktime(0, 0, 0, $row['month'], 1));
        $data[] = $row['crime_count'];
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../crime/user.css">
    <title>Admin Dashboard</title>
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
            <h1>Crime Statistic</h1>
           

            <div class="container">
                <h2>Crime Rate Graph</h2>
                <canvas id="crime-bar-chart" width="800" height="400"></canvas>
            </div>
    
            <div class="statistics">
            <p>Total crimes reported in <span id="selected-year"><?php echo $year; ?></span>: <span id="total-crimes"><?php echo $totalCrimes; ?></span></p>
            <p>Month with highest crime rate: <span id="highest-month"><?php echo $highestMonth; ?></span></p>
            <p>Month with lowest crime rate: <span id="lowest-month"><?php echo $lowestMonth; ?></span></p>
            <p>Percentage change compared to previous year: <span id="percentage-change"><?php echo $percentageChange; ?>%</span></p>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Chart data
        const labels = <?php echo json_encode($labels); ?>;
        const data = <?php echo json_encode($data); ?>;

        // Get canvas element
        const ctx = document.getElementById('crime-bar-chart').getContext('2d');

        // Initialize the bar chart
        const crimeBarChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Crimes Reported',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)', // Bar color
                    borderColor: 'rgba(54, 162, 235, 1)', // Border color
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true // Start Y-axis at 0
                    }
                }
            }
        });
    });

    
</script>

    </div>
</div>

<footer class="footer">
    <p class="text-center">&copy; <?php echo date('Y'); ?> Community Crime Reporting System</p>
</footer>
</body>
</html>

