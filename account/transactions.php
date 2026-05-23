<?php
// transactions.php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB connection failed.");

// Ensure seller is logged in
$seller_id = (int)($_SESSION['uid'] ?? 0);
if ($seller_id === 0) {
    header("Location: ../account/login.php");
    exit();
}

// --- Fetch Transactions ---
$sql = "
SELECT 
    o.order_id, o.order_date, o.status,
    oi.quantity, oi.price AS unit_price, oi.price AS received_amount,
    p.pname, up_buyer.full_name AS buyer_name,
    'Card/UPI' AS payment_method_simulated,
    CASE o.status
        WHEN 'delivered' THEN 'Successful'
        WHEN 'pending' THEN 'Pending'
        WHEN 'cancelled' THEN 'Refunded'
        ELSE 'Processing'
    END AS payment_status
FROM orders o
JOIN orders oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.product_id
JOIN user_profiles up_buyer ON o.buyer_id = up_buyer.uid
WHERE p.seller_id = ?
ORDER BY o.order_date DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) die("SQL Prepare Error: " . $conn->error);

$stmt->bind_param("i", $seller_id);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transaction History - Seller Dashboard</title>
<link rel="icon" type="image/png" href="../style/imgs/gb.png">
<link rel="stylesheet" href="../style/seller_dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
.main-content { padding: 30px; max-width: 1200px; margin: auto; }
h1 { font-size: 1.8rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

/* Filter bar */
.filter-bar { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
.filter-btn { padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; background: #eee; transition: 0.2s; }
.filter-btn.active { background: var(--accent, #2eb535); color: #fff; }
.filter-btn:hover { background: var(--accent, #2eb535); color: #fff; }

/* Table styling */
.table-container { overflow-x: auto; background: var(--color-card, #fff); padding: 20px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
th { background-color: var(--accent-light, #f0f8ff); font-weight: 600; }
tr:hover { background-color: #f9f9f9; }

/* Status badges */
.status { padding: 5px 10px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; color: #fff; display: inline-block; }
.status-successful { background-color: #2eb535; }
.status-pending { background-color: #ffb400; }
.status-refunded { background-color: #ff3b30; }
.amount { font-weight: 600; }

@media(max-width: 768px) { th, td { font-size: 0.85rem; padding: 10px; } }
</style>
</head>
<body>

<?php include 'seller_sidebar.php'; ?>

<div class="main-content">
    <h1><i class="fas fa-money-check-alt"></i> Transaction History</h1>

    <!-- Filter bar -->
    <div class="filter-bar">
        <button class="filter-btn active" data-status="all">All</button>
        <button class="filter-btn" data-status="successful">Successful</button>
        <button class="filter-btn" data-status="pending">Pending</button>
        <button class="filter-btn" data-status="refunded">Refunded</button>
    </div>

    <div class="table-container">
        <table id="transactionsTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Buyer</th>
                    <th>Date</th>
                    <th>Amount Received</th>
                    <th>Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr class="transaction-row <?= strtolower($t['payment_status']) ?>">
                            <td><?= htmlspecialchars($t['pname']) ?></td>
                            <td><?= htmlspecialchars($t['buyer_name']) ?></td>
                            <td><?= date('d M Y', strtotime($t['order_date'])) ?></td>
                            <td class="amount">₹<?= number_format($t['received_amount'], 2) ?></td>
                            <td><?= htmlspecialchars($t['payment_method_simulated']) ?></td>
                            <td><span class="status status-<?= strtolower($t['payment_status']) ?>"><?= htmlspecialchars($t['payment_status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; padding:20px;">No transactions recorded.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const transactionRows = document.querySelectorAll('.transaction-row');

    function filterTransactions(status) {
        transactionRows.forEach(row => {
            if (status === 'all' || row.classList.contains(status)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterTransactions(this.dataset.status);
        });
    });

    // Initial filter
    filterTransactions('all');
});
</script>

</body>
</html>
