<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB connection failed.");

$seller_id = (int)$_SESSION['uid']; 
$seller_name = $_SESSION['user'] ?? '';

// --- NEW: Check if the user has a profile ---
$profile_check_query = "SELECT profile_id FROM user_profiles WHERE uid = ? LIMIT 1";
$stmt = $conn->prepare($profile_check_query);
if (!$stmt) die("SQL Error (Profile Check): " . $conn->error);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    // No profile found, redirect to profile creation page
    header('Location: edit_profile.php');
    exit();
}
$stmt->close();
// 1. Total Revenue (Last 30 Days)
$revenue_query = "
    SELECT COALESCE(SUM(o.price), 0) AS total_revenue
    FROM orders o
    WHERE o.seller_id = ? 
      AND o.status = 'delivered'
      AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
";
$stmt = $conn->prepare($revenue_query);
if (!$stmt) { die("SQL Error for Revenue Query: " . $conn->error); }
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$total_revenue = number_format($data['total_revenue'], 2);
$stmt->close();

// 2. Total Products Listed
$product_count_query = "SELECT COUNT(*) AS total_products FROM products WHERE seller_id = ?";
$stmt = $conn->prepare($product_count_query);
if (!$stmt) { die("SQL Error for Product Count Query: " . $conn->error); }
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$total_products = $data['total_products'];
$stmt->close();

// 3. Average Seller Rating
$rating_query = "
    SELECT COALESCE(AVG(r.rating), 0) AS avg_rating
    FROM ratings r 
    JOIN products p ON r.product_id = p.product_id
    WHERE p.seller_id = ?
";
$stmt = $conn->prepare($rating_query);
if (!$stmt) { die("SQL Error for Rating Query: " . $conn->error); }
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$avg_rating = number_format($data['avg_rating'], 1);
$stmt->close();

// 4. Monthly Revenue Data for Chart (Last 6 months)
$chart_data = [];
$chart_query = "
    SELECT 
        DATE_FORMAT(o.order_date, '%Y-%m') AS month,
        SUM(o.price) AS monthly_revenue
    FROM orders o
    WHERE o.seller_id = ? AND o.status = 'delivered'
      AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month ASC
";
$stmt = $conn->prepare($chart_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $chart_data[$row['month']] = $row['monthly_revenue'];
}
$stmt->close();

$labels = [];
$revenues = [];
for ($i = 5; $i >= 0; $i--) {
    $date = new DateTime("-$i months");
    $month_key = $date->format('Y-m');
    $month_label = $date->format('M');
    $revenue = isset($chart_data[$month_key]) ? round($chart_data[$month_key]) : 0;
    
    $labels[] = $month_label;
    $revenues[] = $revenue;
}

$chart_labels_json = json_encode($labels);
$chart_revenues_json = json_encode($revenues);


// --- NEW FEATURES (Total Earned & Buyer Satisfaction) ---

// 5. Total Amount Earned (from user_profiles table)
$earned_query = "
    SELECT COALESCE(total_earned, 0) AS total_earned
    FROM user_profiles
    WHERE uid = ?
";
$stmt = $conn->prepare($earned_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$total_earned = number_format($data['total_earned'], 2); // Format for display
$stmt->close();

// 6. Buyer Satisfaction Data (Ratings Count: 5/4 vs 3/2/1)
$satisfaction_query = "
    SELECT 
        SUM(CASE WHEN r.rating IN ('4', '5') THEN 1 ELSE 0 END) AS satisfied_count,
        SUM(CASE WHEN r.rating IN ('1', '2', '3') THEN 1 ELSE 0 END) AS unsatisfied_count
    FROM ratings r 
    JOIN products p ON r.product_id = p.product_id
    WHERE p.seller_id = ?
";
$stmt = $conn->prepare($satisfaction_query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$satisfied_count = $data['satisfied_count'] ?? 0;
$unsatisfied_count = $data['unsatisfied_count'] ?? 0;
$stmt->close();

$satisfaction_data_json = json_encode([$satisfied_count, $unsatisfied_count]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - GreenBasket</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png"> 
    <link rel="stylesheet" href="../style/seller_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Poetsen+One&display=swap');
    </style>
</head>
<body>

<?php 
// Include the updated sidebar with the new Back to Home link and dark theme
require 'seller_sidebar.php'; 
?>

<div class="main-content goodboard-main">
    <header class="dashboard-header goodboard-header">
        <h1>Dashboard seller</h1>
        <div class="user-profile">
            <span><?=$seller_name?></span>
            <i class="fas fa-chevron-down"></i>
        </div>
    </header>

    <div class="stats-grid goodboard-stats">
        <div class="stat-card total-sales revenue">
            <div class="stat-header">
                <i class="fas fa-rocket stat-icon"></i>
                <i class="fas fa-arrow-up trend-icon"></i>
            </div>
            <div class="stat-info">
                <p class="value">₹<?= $total_revenue ?></p>
                <p class="label">Total Sales (Last 30 Days)</p>
            </div>
            <p class="trend-text">+10% from last month</p> </div>
        
        <div class="stat-card total-earned products">
            <div class="stat-header">
                <i class="fas fa-wallet stat-icon"></i>
                <i class="fas fa-arrow-up trend-icon"></i>
            </div>
            <div class="stat-info">
                <p class="value">₹<?= $total_earned ?></p>
                <p class="label">Total Earned (Lifetime)</p>
            </div>
            <p class="trend-text">+5% from last month</p> </div>
        
        <div class="stat-card total-products rating">
            <div class="stat-header">
                <i class="fas fa-boxes stat-icon"></i>
                <i class="fas fa-arrow-up trend-icon"></i>
            </div>
            <div class="stat-info">
                <p class="value"><?= $total_products ?></p>
                <p class="label">Active Products</p>
            </div>
            <p class="trend-text">+2 from last month</p> </div>
        
        <div class="stat-card avg-rating other">
            <div class="stat-header">
                <i class="fas fa-award stat-icon"></i>
                <i class="fas fa-arrow-down trend-icon down"></i> 
            </div>
            <div class="stat-info">
                <p class="value"><?= $avg_rating ?> / 5.0</p>
                <p class="label">Average Seller Rating</p>
            </div>
            <p class="trend-text down">-0.1 from last month</p> </div>
    </div>

    <div class="charts-and-tables-grid">
        <div class="chart-section satisfaction-chart-container chart-container">
            <h2>Buyer Satisfaction (Ratings)</h2>
            <div class="chart-wrapper">
                <canvas id="satisfactionChart"></canvas>
            </div>
            <div class="chart-legend-custom">
                <span class="satisfied-legend"><i class="fas fa-circle"></i> Satisfied (4 & 5 stars)</span>
                <span class="unsatisfied-legend"><i class="fas fa-circle"></i> Unsatisfied (1, 2 & 3 stars)</span>
            </div>
        </div>
        
        <div class="chart-section monthly-revenue-container chart-container">
            <h2>Monthly Revenue (Last 6 Months)</h2>
            <div class="chart-wrapper">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="details-section top-products-container chart-container">
        <h2>Top Products by Sales</h2>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Product Name</th><th>Popularity</th><th>Sales Qty</th></tr>
            </thead>
            <tbody>
                <tr><td>01</td><td>Banana</td><td><div class="progress-bar-bg"><div class="progress-bar" style="width: 70%; background-color: #79f75b;"></div></div></td><td>70%</td></tr>
                <tr><td>02</td><td>Tomato</td><td><div class="progress-bar-bg"><div class="progress-bar" style="width: 55%; background-color: #49c4ff;"></div></div></td><td>55%</td></tr>
                <tr><td>03</td><td>Carrot</td><td><div class="progress-bar-bg"><div class="progress-bar" style="width: 30%; background-color: #ffde40;"></div></div></td><td>30%</td></tr>
                <tr><td>04</td><td>Spinach</td><td><div class="progress-bar-bg"><div class="progress-bar" style="width: 15%; background-color: #f06a6a;"></div></div></td><td>15%</td></tr>
            </tbody>
        </table>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Monthly Revenue Chart (Line Chart) ---
    const revenueLabels = <?= $chart_labels_json ?>;
    const revenueData = <?= $chart_revenues_json ?>;

    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChartBgColor = revenueCtx.createLinearGradient(0, 0, 0, 300);
    revenueChartBgColor.addColorStop(0, 'rgba(46, 181, 53, 0.2)'); // Lighter green
    revenueChartBgColor.addColorStop(1, 'rgba(46, 181, 53, 0)');

    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Revenue (₹)',
                data: revenueData,
                backgroundColor: revenueChartBgColor,
                borderColor: 'var(--color-primary-dark)',
                borderWidth: 2, 
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'var(--color-accent)', 
                pointBorderColor: '#fff',
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allows chart to fill the container height
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(0,0,0,0.05)' } 
                },
                x: { 
                    grid: { display: false } 
                }
            },
            plugins: { 
                legend: { display: false }, 
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                } 
            } 
        }
    });

    // --- Buyer Satisfaction Chart (Bar Graph) ---
    const satisfactionData = <?= $satisfaction_data_json ?>; 
    const satisfactionCtx = document.getElementById('satisfactionChart').getContext('2d');

    new Chart(satisfactionCtx, {
        type: 'bar',
        data: {
            labels: ['Satisfied', 'Unsatisfied'],
            datasets: [{
                label: 'Total Ratings',
                data: satisfactionData,
                // Green for Satisfied, Red for Unsatisfied
                backgroundColor: ['#52c41a', '#ff4d4f'], 
                borderRadius: 6, 
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // Allows chart to fill the container height
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: 'rgba(0,0,0,0.05)' } 
                },
                x: { 
                    grid: { display: false } 
                }
            },
            plugins: { legend: { display: false } }
        }
    });

    // --- Dynamic Animation Placeholder ---
    const stats = document.querySelectorAll('.goodboard-stats .stat-card');
    stats.forEach((card, index) => {
        card.style.transition = 'all 0.4s ease-out';
        setTimeout(() => {
            card.style.transform = 'scale(1)';
            card.style.opacity = '1';
        }, 50 * index);

        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = '0 8px 15px rgba(0, 0, 0, 0.1)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.05)';
        });
    });
});
</script>
</body>
</html>