<?php 
// seller_sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="logo-container">
        <h2 class="goodboard-logo">GreenBasket Seller</h2> 
    </div>
    <ul class="nav-menu">
        <strong><li><a href="seller_dashboard.php" class="<?= $current_page === 'seller_dashboard.php' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li></strong>
        <strong><li><a href="add_product.php" class="<?= $current_page === 'add_product.php' ? 'active' : '' ?>"><i class="fas fa-plus-circle"></i> <span>Add Product</span></a></li></strong>
        <strong><li><a href="products.php" class="<?= $current_page === 'products.php' ? 'active' : '' ?>"><i class="fas fa-edit"></i> <span>Products</span></a></li></strong>
        <strong><li><a href="product_status.php" class="<?= $current_page === 'product_status.php' ? 'active' : '' ?>"><i class="fas fa-tasks"></i> <span>Product Status</span></a></li></strong>
        <strong><li><a href="transactions.php" class="<?= $current_page === 'transactions.php' ? 'active' : '' ?>"><i class="fas fa-money-check-alt"></i> <span>Transaction History</span></a></li></strong>
        <strong><li><a href="analytics.php" class="<?= $current_page === 'analytics.php' ? 'active' : '' ?>"><i class="fas fa-chart-line"></i> <span>Analytics / Reports</span></a></li></strong>
        <strong><li><a href="reviews.php" class="<?= $current_page === 'reviews.php' ? 'active' : '' ?>"><i class="fas fa-star"></i> <span>Reviews / Ratings</span></a></li></strong>
        <strong><li><a href="../index.php" class=""><i class="fas fa-arrow-left"></i> <span>Back to Home</span></a></li>
    </ul>
</div>