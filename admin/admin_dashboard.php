<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect(); 

// ✅ Ensure admin-only access
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php"); 
    exit();
}

$admin_full_name ='Admin'; 

// ✅ Page Configs for Sidebar
$page_configs = [
    'admin_content_dashboard.php' => ['title' => 'Dashboard Overview', 'icon' => 'fas fa-chart-line'],
    'manage_user.php' => ['title' => 'All Users', 'icon' => 'fas fa-users'],
    'admin_all_sellers.php' => ['title' => 'All Sellers', 'icon' => 'fas fa-store'],
    'admin_seller_approval.php' => ['title' => 'Seller Approvals', 'icon' => 'fas fa-user-check'],
    'admin_products.php' => ['title' => 'Manage Products', 'icon' => 'fas fa-boxes'],
    'admin_orders.php' => ['title' => 'Manage Orders', 'icon' => 'fas fa-truck'],
    'admin_transactions.php' => ['title' => 'Transactions History', 'icon' => 'fas fa-exchange-alt'],
    'admin_sales_dashboard.php' => ['title' => 'Sales & Analytics', 'icon' => 'fas fa-chart-pie'],
    'admin_logs.php' => ['title' => 'System Logs', 'icon' => 'fas fa-cogs']
];

// ✅ Determine Current Page
$default_content_file = 'admin_content_dashboard.php';
$current_page_file = basename($_SERVER['PHP_SELF']);
$content_to_include = $default_content_file;

if ($current_page_file === 'admin_dashboard.php') {
    $current_page_key = $default_content_file;
} elseif (in_array($current_page_file, array_keys($page_configs))) {
    $current_page_key = $current_page_file;
    $content_to_include = null;
} else {
    $current_page_key = $default_content_file;
}

$current_page_title = $page_configs[$current_page_key]['title'] ?? 'Dashboard Overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Green Basket Admin Dashboard | <?= htmlspecialchars($current_page_title) ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="css/admin_dashboard.css">
</head>

<body class="loading"> 
<div class="background-gradient"></div>

<!-- ✅ Sidebar -->
<div class="sidebar">
    <div class="logo">Green<span>Basket</span></div>
    <ul class="sidebar-menu">
        <?php foreach ($page_configs as $file => $config): 
            $href = ($file === 'admin_content_dashboard.php') ? 'admin_dashboard.php' : $file;
            $isActive = ($file === 'admin_content_dashboard.php' && $current_page_file === 'admin_dashboard.php') || ($current_page_file === $file) ? 'active' : '';
        ?>
            <li>
                <a href="<?= htmlspecialchars($href) ?>" class="<?= $isActive ?>">
                    <i class="<?= htmlspecialchars($config['icon']) ?>"></i>
                    <span><?= htmlspecialchars($config['title']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
         <li>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </li>
    </ul>
</div>

<!-- ✅ Main Content -->
<div class="main-container">
    <div class="header glass-container">
        <h1 id="dashboard-title"><?= htmlspecialchars($current_page_title) ?></h1>
        <div class="user-info">
            <span>Welcome, <?= htmlspecialchars($admin_full_name) ?></span>
        </div>
    </div>
    
    <div class="content-wrapper">
        <div id="dashboard-content-area" class="content-area">
            <?php 
            // ✅ Default Dashboard Section
            if ($content_to_include === $default_content_file):
                // Fetch real values from DB
                $total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
                $total_sellers = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='seller'")->fetch_assoc()['c'];
                $total_orders = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
                $total_pending = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
                $total_sales = $conn->query("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE payment_status='successful'")->fetch_assoc()['total'];
            ?>
            <div class="admin-page-container">
                <h2>Welcome back, <?= htmlspecialchars($admin_full_name) ?>!</h2>
                <div class="dashboard-stats-grid">
                    <div class="stat-card glass-card anim-slide-up">
                        <h3>Total Sales (All Time)</h3>
                        <div class="stat-value">₹<?= number_format($total_sales,2) ?></div>
                    </div>
                    <div class="stat-card glass-card anim-slide-up">
                        <h3>Total Users</h3>
                        <div class="stat-value"><?= $total_users ?></div>
                    </div>
                    <div class="stat-card glass-card anim-slide-up">
                        <h3>Active Sellers</h3>
                        <div class="stat-value"><?= $total_sellers ?></div>
                    </div>
                    <div class="stat-card glass-card anim-slide-up">
                        <h3>Pending Orders</h3>
                        <div class="stat-value"><?= $total_pending ?></div>
                    </div>
                </div>

                <h3>Quick Actions</h3>
                <div class="quick-actions-list anim-slide-up">
                    <a href="admin_products.php" class="quick-action-btn glass-card"><i class="fas fa-box"></i> View All Products</a>
                    <a href="admin_sales_dashboard.php" class="quick-action-btn glass-card"><i class="fas fa-chart-bar"></i> View Sales Report</a>
                    <a href="admin_logs.php" class="quick-action-btn glass-card"><i class="fas fa-cog"></i> System Logs</a>
                </div>

                <h3>Recent Activity (System Logs)</h3>
                <div class="table-responsive anim-slide-up">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Event Type</th>
                                <th>Description</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $logs = $conn->query("SELECT * FROM system_logs ORDER BY event_time DESC LIMIT 6");
                            while ($log = $logs->fetch_assoc()):
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($log['event_type']) ?></td>
                                    <td><?= htmlspecialchars($log['description']) ?></td>
                                    <td><?= htmlspecialchars($log['event_time']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="js/admin_dashboard.js"></script>
</body>
</html>

<?php $conn->close(); ?>
