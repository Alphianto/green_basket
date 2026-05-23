<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB connection failed.");

// require login
if (!isset($_SESSION['uid'])) {
    header('Location: ../account/login.php');
    exit();
}
$buyer_id = (int)$_SESSION['uid'];

// Fetch orders for this user (most recent first)
// The correlated subquery is updated to check for a rating using the correct table schema: r.product_id and r.buyer_id
$sql = "
    SELECT o.order_id, o.product_id, o.seller_id, o.quantity, o.price, o.status, o.order_date,
           p.pname, p.img AS product_img,
           u.full_name AS seller_name,
           -- Check if the buyer has rated this specific product.
           (SELECT COUNT(*) 
            FROM ratings r 
            WHERE r.product_id = o.product_id 
              AND r.buyer_id = ?) AS is_reviewed
    FROM orders o
    JOIN products p ON o.product_id = p.product_id
    LEFT JOIN user_profiles u ON o.seller_id = u.uid
    WHERE o.buyer_id = ?
    ORDER BY o.order_date DESC
";
$stmt = $conn->prepare($sql);
if (!$stmt) die("SQL Prepare Failed: " . $conn->error);
// Bind the buyer_id twice: once for the subquery, once for the main WHERE clause
$stmt->bind_param("ii", $buyer_id, $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your Orders — GreenBasket</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="../style/orders.css">
    <link rel="stylesheet" href="../style/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&family=Lemon&display=swap" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../layout/headweb.php'; ?>

<main class="orders-page">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="../index.php">Home</a> &gt; 
        <a href="../account/profile.php">Profile</a> &gt; 
        <span>My Orders</span>
    </div>

    <header class="orders-header">
        <h1>Your Orders</h1>
        <div class="orders-controls">
            <!-- Only one search bar, focusing on product name -->
            <input id="ordersSearch" type="search" placeholder="Search by product name..." aria-label="Search orders by product name">
            <!-- Removed the global search control and button -->
        </div>
    </header>

    <section id="ordersList" class="orders-list">
        <?php if (empty($orders)): ?>
            <div class="empty">You have no orders yet.</div>
        <?php else: foreach ($orders as $o): 
            // compute display values:
            $status = $o['status'];
            $orderDate = strtotime($o['order_date']);
            $is_reviewed = (int)$o['is_reviewed'] > 0;

            // If delivered -> show order_date as delivered date. If pending -> expected before tomorrow
            if ($status === 'delivered') {
                $dateLabel = "Delivered on: " . date('M d, Y', $orderDate);
            } elseif ($status === 'cancelled') {
                $dateLabel = "Cancelled";
            } else {
                $tomorrow = strtotime('+1 day', $orderDate);
                $dateLabel = "Expected before: " . date('M d, Y', $tomorrow);
            }
            // total price (price column assumed to be final price for that order)
            $totalPrice = number_format((float)$o['price'], 2);
            $qty = (float)$o['quantity'];
        ?>
        <article class="order-card" data-product="<?= htmlspecialchars(strtolower($o['pname'])) ?>" data-seller="<?= htmlspecialchars(strtolower($o['seller_name'] ?? '')) ?>">
            <div class="order-left">
                <div class="thumb">
                    <img src="<?= htmlspecialchars($o['product_img']) ?>" alt="<?= htmlspecialchars($o['pname']) ?>" onerror="this.src='/assets/img/placeholder.png'">
                </div>
                <div class="meta">
                    <h3 class="product-name"><?= htmlspecialchars($o['pname']) ?></h3>
                    <div class="seller">Sold by: <a href="../account/public_profile.php?id=<?= (int)$o['seller_id'] ?>" class="seller-link"><?= htmlspecialchars($o['seller_name'] ?? 'Seller') ?></a></div>
                    <div class="quantity">Quantity: <?= rtrim(rtrim(number_format($qty,3, '.', ''),'0'),'.') ?> kg</div>
                </div>
            </div>

            <div class="order-right">
                <div class="price">₹<?= $totalPrice ?></div>
                
                <div class="status-action-row">
                    <?php if ($status === 'pending'): ?>
                        <a href="#" class="cancel-order-link" data-order-id="<?= (int)$o['order_id'] ?>">Cancel Order</a>
                    <?php endif; ?>
                    
                    <div class="status <?php echo $status === 'cancelled' ? 'status-cancelled' : ($status === 'delivered' ? 'status-delivered' : 'status-pending'); ?>">
                        <?= htmlspecialchars(ucfirst($status)) ?>
                    </div>
                </div>
                <div class="date"><?= $dateLabel ?></div>
                <?php if ($status !== 'cancelled'): ?>
                    <?php if ($is_reviewed): ?>
                        <div class="rate-link reviewed">Reviewed</div>
                    <?php else: ?>
                        <a class="rate-link not-reviewed" href="rating.php?order_id=<?= (int)$o['order_id'] ?>&product_id=<?= (int)$o['product_id'] ?>&seller_id=<?= (int)$o['seller_id'] ?>">Rate & Review</a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="rate-link disabled">Rate & Review</div>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; endif; ?>
    </section>
</main>

<script src="../script/orders.js"></script>
</body>
</html>
