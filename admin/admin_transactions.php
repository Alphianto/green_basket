<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

// Admin authentication
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
// Summary Cards Data
$total_revenue = $conn->query("SELECT SUM(amount) as total FROM payments WHERE payment_status='successful'")->fetch_assoc()['total'] ?? 0;
$total_pending = $conn->query("SELECT COUNT(*) as total FROM payments WHERE payment_status='pending'")->fetch_assoc()['total'] ?? 0;
$total_refunded = $conn->query("SELECT COUNT(*) as total FROM payments WHERE payment_status='refunded'")->fetch_assoc()['total'] ?? 0;
$total_transactions = $conn->query("SELECT COUNT(*) as total FROM payments")->fetch_assoc()['total'] ?? 0;

// Fetch all payments
$payments_result = $conn->query("SELECT p.payment_id, p.order_id, u.username as buyer_name, s.username as seller_name, p.amount, p.payment_method, p.payment_status, p.payment_date
FROM payments p
LEFT JOIN users u ON p.buyer_id = u.uid
LEFT JOIN users s ON p.seller_id = s.uid
ORDER BY p.payment_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Transactions | Green Basket Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="css/admin_dashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Charts container and canvas */
.charts-container{
    display:flex;
    flex-wrap:wrap;
    gap:20px;
    margin-top:25px;
}
.charts-container canvas{
    flex:1 1 45%;
    min-width:300px;
    height:350px !important;
    background:#fff;
    padding:15px;
    border-radius:8px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

/* Summary cards */
.summary-cards{
    display:flex;
    gap:15px;
    margin-top:25px;
    flex-wrap:wrap;
}
.summary-cards .card{
    flex:1 1 180px;
    background:#fff;
    padding:15px 20px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    display:flex;
    flex-direction:column;
    align-items:flex-start;
}
.summary-cards .card h3{margin:0;font-size:0.9rem;color:#555;}
.summary-cards .card p{margin-top:5px;font-size:1.2rem;font-weight:700;color:#222;}

/* Transactions table adjustments */
.admin-page-container{
    margin-top:40px; /* Increased space between charts and table heading */
}
.data-table{
    width:100%;
    border-collapse:collapse;
    font-size:0.95rem;
    margin-top:15px;
}
.data-table th,
.data-table td{
    padding:12px 15px;
    text-align:left;
    border-bottom:1px solid #e0e0e0;
}
.data-table tbody tr:hover{
    background-color:#f5f5f5;
}
.table-responsive{
    overflow-x:auto;
}
</style>
</head>
<body>

<!-- Sidebar -->
<?php include 'admin_sidebar.php';?>

<div class="main-container">
    <div class="header glass-container">
        <h1>GreenBasket Admin Panel</h1>
        <div class="user-info">
            <span>Welcome, Admin</span>
        </div>
    </div>

    <div class="content-wrapper">

        <!-- Summary Cards below charts -->
        <div class="charts-container">
            <canvas id="salesOverTime"></canvas>
            <canvas id="revenuePerSeller"></canvas>
        </div>

        <div class="summary-cards">
            <div class="card">
                <h3>Total Revenue</h3>
                <p>₹<?= number_format($total_revenue,2) ?></p>
            </div>
            <div class="card">
                <h3>Pending Payments</h3>
                <p><?= $total_pending ?></p>
            </div>
            <div class="card">
                <h3>Refunded Payments</h3>
                <p><?= $total_refunded ?></p>
            </div>
            <div class="card">
                <h3>Total Transactions</h3>
                <p><?= $total_transactions ?></p>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="admin-page-container">
            <h2>All Transactions</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Order ID</th>
                            <th>Buyer</th>
                            <th>Seller</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($payments_result && $payments_result->num_rows > 0) {
                            while ($row = $payments_result->fetch_assoc()) {
                                $action_btn = '';
                                if ($row['payment_status'] === 'pending') {
                                    $action_btn = "<button class='btn-action btn-success' data-id='{$row['payment_id']}'>Mark Complete</button>";
                                }
                                echo "<tr>
                                    <td>{$row['payment_id']}</td>
                                    <td>{$row['order_id']}</td>
                                    <td>".htmlspecialchars($row['buyer_name'])."</td>
                                    <td>".htmlspecialchars($row['seller_name'])."</td>
                                    <td>₹".number_format($row['amount'],2)."</td>
                                    <td>".htmlspecialchars($row['payment_method'])."</td>
                                    <td>".htmlspecialchars($row['payment_status'])."</td>
                                    <td>{$row['payment_date']}</td>
                                    <td>$action_btn</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No transactions found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
$(document).ready(function(){
    $('.btn-action').click(function(){
        var btn = $(this);
        var payment_id = btn.data('id');

        $.ajax({
            url: 'admin_action_payment.php',
            type: 'POST',
            data: { payment_id: payment_id },
            dataType: 'json',
            success: function(response){
                if(response.status === 'success'){
                    btn.closest('tr').find('td:nth-child(7)').text('successful');
                    btn.remove();
                } else {
                    alert(response.message);
                }
            },
            error: function(){ alert('AJAX error'); }
        });
    });

    // Charts data
    var ctx1 = document.getElementById('salesOverTime').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?= json_encode(range(1,12)) ?>,
            datasets: [{
                label: 'Monthly Revenue',
                data: <?= json_encode([500,800,600,1200,900,1500,1300,1700,1600,1800,2000,2200]) ?>,
                backgroundColor: 'rgba(46, 204, 113, 0.2)',
                borderColor: 'rgba(46, 204, 113, 1)',
                borderWidth: 2
            }]
        }
    });

    var ctx2 = document.getElementById('revenuePerSeller').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: ['Seller A','Seller B','Seller C'],
            datasets: [{
                label: 'Revenue',
                data: [1200,900,1500],
                backgroundColor: ['#3498db','#e67e22','#9b59b6']
            }]
        }
    });
});
</script>
</body>
</html>
