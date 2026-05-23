<?php
// product_status.php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB connection failed.");

// Assuming the seller_id is stored in the session
$seller_id = (int)($_SESSION['uid'] ?? 1); 

// --- SQL QUERY FOR SALES DATA ---
$sql = "
    SELECT 
        oi.product_id, p.pname, p.img AS product_img, p.quantity AS product_quantity,
        o.order_id, o.order_date, o.status AS order_status,
        up_buyer.uid AS buyer_id,
        up_buyer.full_name AS buyer_name, 
        up_buyer.avatar AS buyer_avatar,
        oi.quantity, oi.price, oi.price AS total_amount,
        oi.price AS profit_estimate,
        p.seller_id
    FROM orders oi
    JOIN orders o ON oi.order_id = o.order_id
    JOIN products p ON oi.product_id = p.product_id
    JOIN user_profiles up_buyer ON o.buyer_id = up_buyer.uid
    WHERE p.seller_id = ?
    ORDER BY o.order_date DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) die("SQL Prepare Error: " . $conn->error);

$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$sales = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Status - Seller Dashboard</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <link rel="stylesheet" href="../style/seller_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Make entire card clickable */
        .sale-card { position: relative; cursor: pointer; }
        .sale-card .card-link-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 1;
            text-decoration: none;
        }
    </style>
</head>
<body>

<?php include 'seller_sidebar.php'; ?>

<div class="main-content">
    <header class="dashboard-header goodboard-header" style="margin-bottom: 20px;">
        <h1><i class="fas fa-tasks"></i> Product Sales Status</h1>
    </header>

    <div class="filter-bar">
        <span class="filter-label">Filter by Status:</span>
        <button class="filter-btn active" data-status="all">All</button>
        <button class="filter-btn" data-status="pending">Pending</button>
        <button class="filter-btn" data-status="delivered">Delivered</button>
        <button class="filter-btn" data-status="cancelled">Cancelled</button>
    </div>
    
    <section class="orders-list sales-grid" id="salesList">
        <?php if (!empty($sales)): ?>
            <?php foreach ($sales as $sale): 
                $status_class = strtolower($sale['order_status']);
                $buyer_avatar_url = !empty($sale['buyer_avatar']) ? 'img/' . htmlspecialchars($sale['buyer_avatar']) : 'img/default_avatar.png';
            ?>
            <article class="sale-card <?= $status_class ?>" data-product="<?= htmlspecialchars($sale['pname']) ?>" data-seller="<?= $sale['seller_id'] ?>">
                
                <!-- Overlay link to buyer's public profile -->
                <a href="../account/public_profile.php?uid=<?= $sale['buyer_id'] ?>" class="card-link-overlay"></a>

                <div class="sale-left">
                    <img src="<?= $buyer_avatar_url ?>" alt="Buyer Avatar" class="avatar-img"> 
                    <div class="sale-details">
                        <p class="product-name">Product: <strong><?= htmlspecialchars($sale['pname']) ?></strong></p>
                        <p class="seller-name">Buyer: <strong><?= htmlspecialchars($sale['buyer_name'] ?? 'N/A') ?></strong></p>
                        <p class="quantity-date">
                            Quantity: <strong><?= number_format($sale['quantity'], 2) ?> kg</strong> | 
                            Date: <strong><?= date('d M Y', strtotime($sale['order_date'])) ?></strong>
                        </p>
                    </div>
                </div>

                <div class="sale-right">
                    <?php $amountClass = (strtolower($sale['order_status']) === 'cancelled') ? 'amount cancelled-amount' : 'amount'; ?>
                    <p class="<?= $amountClass ?>">₹<?= number_format($sale['total_amount'], 2) ?></p>

                    <?php if (strtolower($sale['order_status']) !== 'cancelled'): ?>
                    <p class="profit">Profit: ₹<?= number_format($sale['profit_estimate'], 2) ?></p>
                    <?php endif; ?>
                    <div class="status status-<?= $status_class ?>">
                        <?= htmlspecialchars(ucfirst($sale['order_status'])) ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="chart-container" style="grid-column: 1 / -1; text-align: center; padding: 50px;">
                <p style="font-size: 1.1rem; color: var(--color-text-muted);">No sales found yet. Keep listing great products!</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Filter functionality
    const filterButtons = document.querySelectorAll('.filter-btn');
    const salesCards = document.querySelectorAll('.sale-card');

    function filterSales(status) {
        salesCards.forEach(card => {
            card.style.display = (status === 'all' || card.classList.contains(status)) ? 'flex' : 'none';
        });
    }

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const statusToFilter = this.getAttribute('data-status');
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            filterSales(statusToFilter);
        });
    });

    filterSales('all'); // initial
});
</script>
</body>
</html>
