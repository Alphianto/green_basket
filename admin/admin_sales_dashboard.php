<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

// Admin authentication
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$current_page_file = basename($_SERVER['PHP_SELF']);  
$admin_full_name = $_SESSION['full_name'] ?? 'Admin User';

// Sidebar pages
$page_configs = [
    'admin_content_dashboard.php' => ['title' => 'Dashboard Overview', 'icon' => 'fas fa-chart-line'],
    'manage_user.php' => ['title' => 'Manage Users', 'icon' => 'fas fa-users'],
    'admin_seller_approval.php' => ['title' => 'Seller Approvals', 'icon' => 'fas fa-user-check'],
    'admin_products.php' => ['title' => 'Manage Products', 'icon' => 'fas fa-boxes'],
    'admin_orders.php' => ['title' => 'Manage Orders', 'icon' => 'fas fa-truck'],
    'admin_transactions.php' => ['title' => 'Transactions History', 'icon' => 'fas fa-exchange-alt'],
    'admin_sales_dashboard.php' => ['title' => 'Sales & Analytics', 'icon' => 'fas fa-chart-pie'],
    'admin_logs.php' => ['title' => 'System Logs', 'icon' => 'fas fa-cogs']
];

// Fetch Top Sellers
$top_sellers_labels = [];
$top_sellers_revenue = [];
$top_sellers_result = $conn->query("SELECT seller_name, revenue FROM top_sellers LIMIT 10");
if ($top_sellers_result && $top_sellers_result->num_rows > 0) {
    while ($row = $top_sellers_result->fetch_assoc()) {
        $top_sellers_labels[] = $row['seller_name'];
        $top_sellers_revenue[] = $row['revenue'];
    }
}

// Fetch Top Products by quantity sold
$top_products_labels = [];
$top_products_quantity = [];
$top_products_result = $conn->query("SELECT product_id, SUM(quantity) AS total_sold 
    FROM orders WHERE status='delivered' 
    GROUP BY product_id ORDER BY total_sold DESC LIMIT 5");
if ($top_products_result && $top_products_result->num_rows > 0) {
    while ($row = $top_products_result->fetch_assoc()) {
        // Fetch product name
        $prod_name = $conn->query("SELECT pname FROM products WHERE product_id={$row['product_id']}")->fetch_assoc()['pname'] ?? 'Unknown';
        $top_products_labels[] = $prod_name;
        $top_products_quantity[] = $row['total_sold'];
    }
}

// Monthly Sales Summary
$current_month = date('Y-m');
$prev_month = date('Y-m', strtotime("-1 month"));
$sales_this_month = $conn->query("SELECT SUM(amount) as total FROM payments WHERE payment_status='successful' AND DATE_FORMAT(payment_date,'%Y-%m')='$current_month'")->fetch_assoc()['total'] ?? 0;
$sales_prev_month = $conn->query("SELECT SUM(amount) as total FROM payments WHERE payment_status='successful' AND DATE_FORMAT(payment_date,'%Y-%m')='$prev_month'")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sales Dashboard | Green Basket Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="css/admin_dashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Charts */
.charts-container{
    display:flex;
    flex-wrap:wrap;
    gap:20px;
    margin-top:20px;
}
.charts-container canvas{
    flex:1 1 45%;
    min-width:250px;
    height:300px !important;
    background:#fff;
    padding:10px;
    border-radius:8px;
    box-shadow:0 2px 8px rgba(0,0,0,0.05);
}

/* Side by side tables */
.table-boxes{
    display:flex;
    flex-wrap:wrap;
    gap:20px;
    margin-top:20px;
}
.table-box{
    flex:1 1 45%;
}
.data-table{
    width:100%;
    border-collapse:collapse;
}
.data-table th, .data-table td{
    padding:10px;
    border-bottom:1px solid #e0e0e0;
    text-align:left;
}
.data-table tbody tr:hover{
    background:#f5f5f5;
}

/* Monthly Sales Summary */
.summary-boxes{
    display:flex;
    gap:20px;
    margin-top:20px;
}
.summary-box{
    flex:1 1 200px;
    background:#fff;
    padding:15px;
    border-radius:8px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}
.summary-box h3{margin:0;font-size:0.9rem;color:#555;}
.summary-box p{margin-top:5px;font-size:1.2rem;font-weight:700;color:#222;}
</style>
</head>
<body>

<!-- Sidebar -->
<?php include 'admin_sidebar.php';?>

<div class="main-container">
    <div class="header glass-container">
        <h1>GreenBasket Admin Panel</h1>
        <div class="user-info"><span>Welcome, Admin</span></div>
    </div>

    <div class="content-wrapper">
        <!-- Monthly Sales Summary -->
        <div class="summary-boxes">
            <div class="summary-box">
                <h3>Sales This Month</h3>
                <p>₹<?= number_format($sales_this_month,2) ?></p>
            </div>
            <div class="summary-box">
                <h3>Sales Previous Month</h3>
                <p>₹<?= number_format($sales_prev_month,2) ?></p>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-container">
            <canvas id="salesOverTime"></canvas>
            <canvas id="revenuePerSeller"></canvas>
        </div>

        <!-- Tables side by side -->
        <div class="table-boxes">
            <div class="table-box">
                <h2>Top-Selling Sellers</h2>
                <table class="data-table">
                    <thead><tr><th>Seller</th><th>Revenue</th></tr></thead>
                    <tbody>
                        <?php 
                        if(!empty($top_sellers_labels)){
                            foreach($top_sellers_labels as $i => $seller){
                                echo "<tr><td>".htmlspecialchars($seller)."</td><td>₹".number_format($top_sellers_revenue[$i],2)."</td></tr>";
                            }
                        } else { echo "<tr><td colspan='2'>No top sellers found</td></tr>"; }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="table-box">
                <h2>Top Products by Quantity Sold</h2>
                <table class="data-table">
                    <thead><tr><th>Product</th><th>Quantity Sold</th></tr></thead>
                    <tbody>
                        <?php 
                        if(!empty($top_products_labels)){
                            foreach($top_products_labels as $i => $product){
                                echo "<tr><td>".htmlspecialchars($product)."</td><td>".number_format($top_products_quantity[$i],2)."</td></tr>";
                            }
                        } else { echo "<tr><td colspan='2'>No top products found</td></tr>"; }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // Charts
    var ctx1 = document.getElementById('salesOverTime').getContext('2d');
    new Chart(ctx1,{
        type:'line',
        data:{
            labels:<?= json_encode(range(1,12)) ?>,
            datasets:[{
                label:'Monthly Revenue',
                data:[500,800,600,1200,900,1500,1300,1700,1600,1800,2000,2200],
                backgroundColor:'rgba(46, 204, 113, 0.2)',
                borderColor:'rgba(46, 204, 113, 1)',
                borderWidth:2
            }]
        },
        options:{
            responsive:true,
            plugins:{ legend:{display:true}, tooltip:{enabled:true} },
            scales:{
                x:{ ticks:{ font:{size:10} } },
                y:{ ticks:{ font:{size:10} } }
            }
        }
    });

    var ctx2 = document.getElementById('revenuePerSeller').getContext('2d');
    new Chart(ctx2,{
        type:'bar',
        data:{
            labels: <?= json_encode($top_sellers_labels) ?>,
            datasets:[{
                label:'Revenue',
                data: <?= json_encode($top_sellers_revenue) ?>,
                backgroundColor:'#3498db'
            }]
        },
        options:{
            responsive:true,
            plugins:{ legend:{display:false}, tooltip:{enabled:true} },
            scales:{
                x:{ ticks:{ font:{size:10} } },
                y:{ ticks:{ font:{size:10} } }
            }
        }
    });
});
</script>
</body>
</html>
