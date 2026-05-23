<?php
// admin_sidebar.php

// Ensure this is included only where needed, e.g., by admin_dashboard.php
if (!isset($current_page)) {
    $current_page = basename($_SERVER['PHP_SELF']);
}

if (!isset($page_configs)) {
    $page_configs = [
        'admin_dashboard.php'        => ['title' => 'Dashboard Overview', 'icon' => 'fas fa-chart-line'],
        'manage_user.php'            => ['title' => 'All Users', 'icon' => 'fas fa-users'],
        'admin_all_sellers.php' => ['title' => 'All Sellers', 'icon' => 'fas fa-store'],
        'admin_seller_approval.php'  => ['title' => 'Seller Approvals', 'icon' => 'fas fa-user-check'],
        'admin_products.php'         => ['title' => 'All Products', 'icon' => 'fas fa-boxes'],
        'admin_orders.php'           => ['title' => 'All Orders', 'icon' => 'fas fa-truck'],
        'admin_transactions.php'     => ['title' => 'Transactions History', 'icon' => 'fas fa-exchange-alt'],
        'admin_sales_dashboard.php'  => ['title' => 'Sales & Analytics', 'icon' => 'fas fa-chart-pie'],
        'admin_logs.php'             => ['title' => 'System Logs', 'icon' => 'fas fa-cogs']
    ];
}
?>
<div class="sidebar">
    <div class="logo">
        Green<span>Basket</span>
    </div>
    <ul class="sidebar-menu">
        <?php foreach ($page_configs as $file => $config): 
            $is_active = ($file === $current_page) ? 'active' : '';
        ?>
        <li class="<?= $is_active ?>">
            <a href="<?= htmlspecialchars($file) ?>" class="<?= $is_active ?>">
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
