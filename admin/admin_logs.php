<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

// Admin authentication
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$admin_full_name = $_SESSION['full_name'] ?? 'Admin User';



// Fetch system logs
$logs_result = $conn->query("SELECT * FROM system_logs ORDER BY event_time DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>System Logs | Green Basket Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="css/admin_dashboard.css">
<style>
.table-container{margin-top:20px; padding:15px; background:#fff; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05);}
.data-table{width:100%; border-collapse:collapse;}
.data-table th, .data-table td{padding:10px; border-bottom:1px solid #ddd; text-align:left;}
.data-table th{background:#f5f5f5;}
</style>
</head>
<body>
<?php include 'admin_sidebar.php';?>


<div class="main-container">
    <div class="header glass-container">
        <h1>GreenBasket Admin Panel</h1>
        <div class="user-info"><span>Welcome, <?= htmlspecialchars($admin_full_name) ?></span></div>
    </div>

    <div class="content-wrapper">
        <div class="table-container">
            <h2>System Logs</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Event Type</th>
                        <th>Description</th>
                        <th>Event Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if($logs_result && $logs_result->num_rows > 0){
                        while($row = $logs_result->fetch_assoc()){
                            echo "<tr>
                                <td>{$row['log_id']}</td>
                                <td>".htmlspecialchars($row['event_type'])."</td>
                                <td>".htmlspecialchars($row['description'])."</td>
                                <td>{$row['event_time']}</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No logs found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
