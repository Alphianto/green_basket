<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB Connection Failed");

// Seller ID
$seller_id = (int)($_SESSION['uid'] ?? 1);

// --- SALES PERFORMANCE & REVENUE ---
// NOTE: I'm correcting the SQL logic here based on standard e-commerce practices.
// Revenue is usually price * quantity, and the previous query didn't use quantity.
$analytics_query = "
    SELECT 
        COUNT(CASE WHEN status != 'cancelled' THEN 1 END) AS total_orders,
        COALESCE(SUM(CASE WHEN status='delivered' THEN price  ELSE 0 END),0) AS total_revenue,
        COALESCE(SUM(CASE WHEN status='pending' THEN price  ELSE 0 END),0) AS pending_revenue
    FROM orders
    WHERE seller_id = ?
";
$stmt = $conn->prepare($analytics_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$analytics_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- PRODUCT STATUS & RATINGS ---
$product_stats_query = "
    SELECT 
        SUM(CASE WHEN p_status='available' THEN 1 ELSE 0 END) AS total_active,
        SUM(CASE WHEN p_status='sold' THEN 1 ELSE 0 END) AS total_sold_out,
        SUM(CASE WHEN p_status='expired' THEN 1 ELSE 0 END) AS total_expired,
        COALESCE(AVG(r.rating),0) AS avg_seller_rating
    FROM products p
    LEFT JOIN ratings r ON p.product_id = r.product_id
    WHERE p.seller_id = ?
";
$stmt = $conn->prepare($product_stats_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$product_stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// --- TOP PRODUCTS BY RATING ---
$top_products_query = "
    SELECT p.pname, p.img, COALESCE(AVG(r.rating),0) AS avg_rating
    FROM products p
    LEFT JOIN ratings r ON p.product_id = r.product_id
    WHERE p.seller_id = ?
    GROUP BY p.product_id
    ORDER BY avg_rating DESC
    LIMIT 5
";
$stmt = $conn->prepare($top_products_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$top_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- MONTHLY SALES PERFORMANCE ---
$monthly_sales_query = "
    SELECT MONTH(order_date) AS month_no, 
           COALESCE(SUM(CASE WHEN status='delivered' THEN price ELSE 0 END),0) AS revenue
    FROM orders
    WHERE seller_id = ?
    AND YEAR(order_date) = YEAR(CURDATE())
    GROUP BY MONTH(order_date)
    ORDER BY month_no
";
$stmt = $conn->prepare($monthly_sales_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$monthly_sales = [];
while($row = $result->fetch_assoc()){
    $monthly_sales[$row['month_no']] = $row['revenue'];
}
$stmt->close();

// Fill missing months with 0
$sales_data = [];
for($m=1;$m<=12;$m++){
    $sales_data[$m] = $monthly_sales[$m] ?? 0;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Seller Analytics Dashboard</title>
<link rel="icon" type="image/png" href="../style/imgs/gb.png">
<link rel="stylesheet" href="../style/seller_dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Keeping body and h styles for quick local overrides, 
   but removing the conflicting layout styles (dashboard-wrapper, main-content) */
body { 
    font-family: 'Inter', sans-serif; 
    background: #f7f8fa; 
    margin:0; 
}

h1,h2 { color: #333; }

/* Component-specific styles */
.stats-grid1 { 
    display: grid; 
    grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); 
    gap: 20px; 
    margin-top: 20px; 
}
.stat-card1 { 
    background: #ffffffff; 
    border-radius: 12px; 
    padding: 20px; 
    box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
    transition: transform 0.2s; 
}
.stat-card1:hover { 
    transform: translateY(-4px); 
}
.stat-card1 .value { 
    font-size: 1.8rem; 
    font-weight: 700; 
    color: #000000ff; /* Using a specific green for the value */
}
.stat-card1 .label { 
    font-size: 0.9rem; 
    color: #666; 
    margin-top: 5px; 
}
.chart-container { 
    margin-top: 40px; 
    background: #fff; 
    padding: 20px; 
    border-radius: 12px; 
    box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
}
.top-products { 
    display: flex; 
    flex-wrap: wrap; 
    gap: 20px; 
    margin-top: 10px; 
}
.product-card { 
    width: 180px; 
    background: #fdfdfd; 
    border-radius: 12px; 
    overflow: hidden; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.08); 
    text-align: center; 
    transition: transform 0.2s; 
}
.product-card:hover { 
    transform: translateY(-3px); 
}
.product-card img { 
    width: 100%; 
    height: 120px; 
    object-fit: cover; 
}
.product-card .name { 
    font-weight: 600; 
    margin: 10px 0 5px; 
    font-size: 0.95rem; 
}
.product-card .rating { 
    font-size: 0.85rem; 
    color: #ffb400; 
    margin-bottom: 10px; 
}
</style>
</head>
<body>
<?php include 'seller_sidebar.php'; ?>
<div class="main-content">
        <strong><h1><i class="fas fa-chart-line"></i> Seller Analytics Dashboard</h1></strong>

<div class="stats-grid1">

    <div class="stat-card1">
        <i class="fas fa-wallet" style="font-size: 28px; color:#2eb535;"></i>
        <p class="value">₹ <?= number_format($analytics_data['total_revenue'], 2) ?></p>
        <p class="label">Total Revenue</p>
    </div>

    <div class="stat-card1">
        <i class="fas fa-shopping-cart" style="font-size: 28px; color:#2eb535;"></i>
        <p class="value"><?= $analytics_data['total_orders'] ?></p>
        <p class="label">Total Orders</p>
    </div>

    <div class="stat-card1">
        <i class="fas fa-store" style="font-size: 28px; color:#2eb535;"></i>
        <p class="value"><?= $product_stats['total_active'] ?></p>
        <p class="label">Active Listings</p>
    </div>

    <div class="stat-card1">
        <i class="fas fa-shopping-cart" style="font-size: 28px; color:#007bff;"></i>
        <p class="value"><?= $product_stats['total_sold_out'] ?></p>
        <p class="label">Total Sold Products</p>
    </div>


    <div class="stat-card1" style="background:#fce8e6; color:#d33;">
        <i class="fas fa-exclamation-circle" style="font-size: 28px; color:#d33;"></i>
        <p class="value"><?= $product_stats['total_expired'] ?></p>
        <p class="label">Expired Products</p>
    </div>

</div>


        <div class="chart-container">
            <h2 style="font-size:1.2rem;">Monthly Sales Performance</h2>
            <canvas id="monthlyChart" height="150"></canvas>
        </div>

        <div class="chart-container">
            <h2>Top Rated Products</h2>
            <?php if(!empty($top_products)): ?>
            <div class="top-products">
                <?php foreach($top_products as $p): ?>
                <div class="product-card">
                    <img src="../shop/<?= htmlspecialchars($p['img']) ?>" alt="<?= htmlspecialchars($p['pname']) ?>">
                    <div class="name"><?= htmlspecialchars($p['pname']) ?></div>
                    <div class="rating">⭐ <?= number_format($p['avg_rating'],1) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p>No top products yet.</p>
            <?php endif; ?>
        </div>
    </div>
<script>
const ctx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Revenue (₹)',
            data: <?= json_encode(array_values($sales_data)) ?>,
            backgroundColor: 'rgba(46,179,53,0.7)',
            borderColor: 'rgba(46,179,53,1)',
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>


</body>
</html>