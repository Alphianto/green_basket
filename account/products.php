<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB Connection Failed");

// Check if the user is logged in and is a seller
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'seller') {
    // Redirect to login or home if not authorized
    header("Location: ../../index.php");
    exit();
}

$seller_id = (int)$_SESSION['uid'];

// --- Handle Deletion Request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
    $product_to_delete_id = (int)$_POST['delete_product_id'];

    // Security check: Ensure the product belongs to the seller before deleting
    $delete_query = "DELETE FROM products WHERE product_id = ? AND seller_id = ?";
    $stmt = $conn->prepare($delete_query);
    if ($stmt) {
        $stmt->bind_param("ii", $product_to_delete_id, $seller_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Product ID {$product_to_delete_id} deleted successfully.";
        } else {
            $_SESSION['error'] = "Error deleting product: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Database error preparing delete statement.";
    }
    // Redirect to prevent form resubmission on refresh
    header("Location: products.php"); // Updated redirect path
    exit();
}


// --- 1. Fetch Seller's Products (including p_expiry_date) ---
$products_query = "
    SELECT 
        product_id, 
        pname, 
        price, 
        quantity, 
        total_quantity,
        img, 
        p_status, 
        discount, 
        product_tag,
        p_expiry_date
    FROM products 
    WHERE seller_id = ?
    ORDER BY created_at DESC
";
$stmt = $conn->prepare($products_query);
if (!$stmt) {
    die("SQL Prepare Failed: (" . $conn->errno . ") " . $conn->error); 
}
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// --- 2. Calculate Average Rating for Display ---
$ratings = [];
if (!empty($products)) {
    $product_ids = array_column($products, 'product_id');
    $in_clause = implode(',', array_fill(0, count($product_ids), '?'));
    $types = str_repeat('i', count($product_ids));

    $avg_rating_query = "
        SELECT 
            product_id, 
            COALESCE(AVG(rating), 0) AS avg_rating
        FROM ratings 
        WHERE product_id IN ($in_clause) 
        GROUP BY product_id
    ";
    
    $stmt_rating = $conn->prepare($avg_rating_query);
    if ($stmt_rating) {
        $stmt_rating->bind_param($types, ...$product_ids);
        $stmt_rating->execute();
        $result_rating = $stmt_rating->get_result();
        while ($row = $result_rating->fetch_assoc()) {
            $ratings[$row['product_id']] = $row['avg_rating'];
        }
        $stmt_rating->close();
    }
}
$conn->close();

/**
 * Helper function to generate star icons
 */
function get_stars($rating) {
    $stars = '';
    $rating = round($rating * 2) / 2; // Round to nearest half
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $stars .= '<i class="fas fa-star" style="color: #ffb400;"></i>';
        } elseif ($rating > $i - 1 && $rating < $i) {
            $stars .= '<i class="fas fa-star-half-alt" style="color: #ffb400;"></i>';
        } else {
            $stars .= '<i class="far fa-star" style="color: #ffb400;"></i>';
        }
    }
    return $stars;
}

/**
 * Calculates the current price after discount
 */
function calculate_price($original_price, $discount) {
    if ($discount > 0) {
        return $original_price * (1 - ($discount / 100));
    }
    return $original_price;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit My Products</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <link rel="stylesheet" href="../style/seller_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --color-primary-blue: #007bff;
            --color-danger-red: #dc3545;
            --color-success-green: #28a745;
            --color-secondary-dark: #333;
            --color-text-muted: #6c757d;
            --color-card-bg: #fff;
            --color-border: #e9ecef;
        }
        body { background: #f7f8fa; font-family: 'Inter', sans-serif; }
        .main-content { padding: 20px; }
        h1 { margin-bottom: 20px; color: var(--color-secondary-dark); }
        
        /* --- Alert Messages --- */
        .message-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .message-box.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6fb;
        }

        /* --- Filter Controls --- */
        .filter-controls {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 8px 15px;
            border: 2px solid #1b9938ff;
            background-color: white;
            color:  #20b944ff;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
        }
        .filter-btn:hover {
            background-color: #e0f7ff;
        }
        .filter-btn.active {
            background-color: #20b944ff;
            color: white;
        }

        /* --- Product Grid Layout --- */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
         .product_add-btn{
             padding: 8px 15px;
            border: 2px solid #1d24a7ff;
            background-color: white;
            color:  #0936d7ff;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, color 0.2s;
         }
        /* --- Product Card Styling (Mimicking the image) --- */
        .product-card {
            background: var(--color-card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        /* --- Expired Overlay Styling --- */
        .expired-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(90, 90, 90, 0.6); /* Gray transparency */
            border-radius: 12px;
            z-index: 20;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            /* Allow buttons (delete/edit) to be clickable through the overlay */
            pointer-events: none; 
            text-transform: uppercase;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
            backdrop-filter: blur(2px);
        }

        .product-img-container {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
            background-color: #f0f0f0;
        }
        .product-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .product-content {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        
        .product-tag {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: var(--color-success-green);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            z-index: 10;
        }
        
        .product-discount {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--color-danger-red);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 10;
        }
        
        .product-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-secondary-dark);
            margin-bottom: 5px;
        }
        
        .product-meta {
            font-size: 0.9rem;
            color: var(--color-text-muted);
            margin-bottom: 10px;
        }
        
        .product-meta .stars {
            font-size: 0.9rem;
            margin-right: 5px;
        }
        
        .price-details {
            display: flex;
            align-items: baseline;
            margin-top: auto; /* Push price to the bottom */
            margin-bottom: 15px;
        }
        
        .original-price {
            font-size: 1rem;
            color: var(--color-text-muted);
            text-decoration: line-through;
            margin-right: 10px;
        }
        
        .current-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--color-success-green);
        }
        
        /* --- Action Buttons --- */
        .action-buttons {
            display: flex;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid var(--color-border);
        }

        .action-buttons button, .action-buttons a {
            flex: 1;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-edit {
            background-color: var(--color-primary-blue);
            color: white;
        }
        .btn-edit:hover {
            background-color: #0056b3;
        }

        .btn-delete {
            background-color: var(--color-danger-red);
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        
        /* Out of Stock indicator */
        .stock-info {
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        .out-of-stock {
             color: var(--color-danger-red);
        }
        .in-stock {
             color: var(--color-success-green);
        }
    </style>
    <script>
        function confirmDelete(productId) {
            // Using a custom modal/confirm box logic if available, otherwise native confirm
            if (confirm(`Are you sure you want to delete Product ID ${productId}? This action cannot be undone.`)) {
                // Create a form dynamically to submit POST request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'products.php'; // Updated action

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_product_id';
                input.value = productId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // --- Client-side Filtering Logic ---
        document.addEventListener('DOMContentLoaded', () => {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const productCards = document.querySelectorAll('.product-card');

            function filterProducts(filterType) {
                productCards.forEach(card => {
                    const status = card.getAttribute('data-expiry-status');
                    
                    let shouldShow = false;

                    if (filterType === 'all') {
                        shouldShow = true;
                    } else if (filterType === 'expired') {
                        // Includes both 'expired' and 'expire_today'
                        if (status === 'expired' || status === 'expire_today') {
                            shouldShow = true;
                        }
                    } else if (filterType === 'expire_today') {
                        // Only 'expire_today'
                        if (status === 'expire_today') {
                            shouldShow = true;
                        }
                    }
                    
                    card.style.display = shouldShow ? 'flex' : 'none';
                });
            }

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Update active state
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    const filterType = this.getAttribute('data-filter');
                    filterProducts(filterType);
                });
            });

            // Initial filtering to 'all' on load
            filterProducts('all');


            // Clear message after a few seconds
            const msgBox = document.querySelector('.message-box');
            if (msgBox) {
                setTimeout(() => {
                    msgBox.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</head>
<body>
<?php include 'seller_sidebar.php'; ?>
    <div class="main-content">
        <h1><i class="fas fa-box-open"></i> Manage My Products</h1>

        <?php 
        // Display success or error message
        if (isset($_SESSION['message'])): ?>
            <div class="message-box success">
                <i class="fas fa-check-circle mr-2"></i> <?= $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php elseif (isset($_SESSION['error'])): ?>
            <div class="message-box error">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($products)): ?>
            <!-- Filter Controls -->
            <?php 
                // PHP helper to count statuses for filter labels
                $today_date_str = date('Y-m-d');
                $total_products = count($products);
                $expired_count = 0;
                $expire_today_count = 0;

                foreach ($products as $p) {
                    if ($p['p_expiry_date'] < $today_date_str) {
                        $expired_count++;
                    } elseif ($p['p_expiry_date'] === $today_date_str) {
                        $expire_today_count++;
                    }
                }
                $available_count = $total_products - $expired_count - $expire_today_count;
            ?>

            <div class="filter-controls">
                <button class="filter-btn active" data-filter="all">All (<?= $total_products ?>)</button>
                <button class="filter-btn" data-filter="expire_today">Expire Today (<?= $expire_today_count ?>)</button>
                <button class="filter-btn" data-filter="expired">Expired (<?= $expired_count + $expire_today_count ?>)</button>
                <a href="add_product.php" class="product_add-btn" style="margin-left: auto;">
                <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>

            <!-- Product Grid -->
            <div class="product-grid">
                <?php foreach ($products as $product): 
                    $product_id = $product['product_id'];
                    $current_price = calculate_price($product['price'], $product['discount']);
                    $avg_rating = $ratings[$product_id] ?? 0;
                    $is_in_stock = $product['quantity'] > 0;
                    
                    // --- Expiry Date Logic ---
                    $expiry_date_str = $product['p_expiry_date'];
                    $today_date_str = date('Y-m-d');
                    
                    $is_expired_in_past = $expiry_date_str < $today_date_str;
                    $is_expiring_today = $expiry_date_str === $today_date_str;
                    
                    $is_critical_expiry = $is_expired_in_past || $is_expiring_today;
                    
                    if ($is_expired_in_past) {
                        $expiry_status = 'expired';
                    } elseif ($is_expiring_today) {
                        $expiry_status = 'expire_today';
                    } else {
                        $expiry_status = 'available';
                    }
                ?>
                <div class="product-card" data-expiry-status="<?= $expiry_status ?>">
                    <?php if ($is_critical_expiry): ?>
                    <div class="expired-overlay">
                        <i class="fas fa-exclamation-triangle mr-2"></i> 
                        <?= $is_expired_in_past ? 'Expired' : 'Expires Today' ?>
                    </div>
                    <?php endif; ?>

                    <div class="product-img-container">
                        <img src="../shop/<?= htmlspecialchars($product['img']) ?>" 
                             alt="<?= htmlspecialchars($product['pname']) ?>"
                             onerror="this.onerror=null;this.src='https://placehold.co/600x400/CCCCCC/333333?text=No+Image';">
                    </div>

                    <?php if (!empty($product['product_tag'])): ?>
                        <div class="product-tag"><?= htmlspecialchars($product['product_tag']) ?></div>
                    <?php endif; ?>

                    <?php if ($product['discount'] > 0): ?>
                        <div class="product-discount">-<?= (int)$product['discount'] ?>% OFF</div>
                    <?php endif; ?>

                    <div class="product-content">
                        <div class="product-name"><?= htmlspecialchars($product['pname']) ?></div>
                        
                        <div class="product-meta">
                            <span class="stars"><?= get_stars($avg_rating) ?></span>
                            (<?= number_format($avg_rating, 1) ?>)
                            | Exp. Date: <?= date('M j, Y', strtotime($expiry_date_str)) ?>
                        </div>
                        
                        <div class="price-details">
                            <?php if ($product['discount'] > 0): ?>
                                <span class="original-price">₹<?= number_format($product['price'], 2) ?>/kg</span>
                            <?php endif; ?>
                            <span class="current-price">₹<?= number_format($current_price, 2) ?>/kg</span>
                        </div>
                        
                        <div class="stock-info <?= $is_in_stock ? 'in-stock' : 'out-of-stock' ?>">
                            <?php if (!$is_in_stock): ?>
                                <i class="fas fa-times-circle"></i> OUT OF STOCK
                            <?php else: ?>
                                <i class="fas fa-check-circle"></i> Stock: <?= number_format($product['quantity'], 2) ?> kg
                            <?php endif; ?>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <!-- Edit Button: Redirects to product_edit.php -->
                            <a href="product_edit.php?edit_id=<?= $product_id ?>" class="btn-edit">
                                <i class="fas fa-edit mr-2"></i> Edit
                            </a>

                            <!-- Delete Button: Deletes from database via POST -->
                            <button type="button" class="btn-delete" onclick="confirmDelete(<?= $product_id ?>)">
                                <i class="fas fa-trash-alt mr-2"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="message-box">
                <p>You currently have no products listed. Time to add some inventory!</p>
                <a href="add_product.php" class="btn-edit" style="margin-left: 20px;">
                    <i class="fas fa-plus"></i> Add New Product
                </a>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
