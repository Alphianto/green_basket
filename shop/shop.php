<?php
session_start();

// --- SESSION CHECK ---
if (!isset($_SESSION['uid'])) {
    header("Location: ../account/login.php");
    exit();
}

require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

$user_id = $_SESSION['uid'];
$product_id = $_GET['id'] ?? null;
if (!$product_id) die("Error: Product ID not specified.");

// --- FETCH PRODUCT DETAILS ---
$product_query = "
    SELECT 
        p.product_id, p.pname, p.p_description, p.price, p.quantity AS stock_quantity, 
        p.img, p.category, p.seller_id, p.p_expiry_date, p.discount, p.product_tag, p.city,
        IFNULL((SELECT AVG(rating) FROM ratings WHERE product_id = p.product_id), 0) AS product_rating,
        IFNULL((SELECT AVG(r.rating) 
                FROM ratings r 
                JOIN products pr ON r.product_id = pr.product_id 
                WHERE pr.seller_id = p.seller_id), 0) AS seller_rating,
        u.full_name AS seller_name
    FROM products p
    JOIN user_profiles u ON p.seller_id = u.uid
    WHERE p.product_id = ?
";
$stmt = $conn->prepare($product_query);
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$product) die("Product not found.");

$product['current_price'] = $product['price'] * (1 - ($product['discount'] ?? 0)/100);

// --- FETCH REVIEWS FOR THIS PRODUCT (WITH BUYER FULL NAME) ---
$product_reviews = [];
$product_reviews_query = "
    SELECT r.rating, r.review, u.full_name
    FROM ratings r
    JOIN user_profiles u ON r.buyer_id = u.uid
    WHERE r.product_id = ?
    ORDER BY r.rating_id DESC
";
$stmt = $conn->prepare($product_reviews_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- HELPER FUNCTION FOR STARS ---
function display_stars($rating) {
    $full = floor($rating);
    $half = ceil($rating - $full);
    $empty = 5 - $full - $half;
    return str_repeat('★', $full) . str_repeat('⯪', $half) . str_repeat('☆', $empty);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($product['pname']) ?> - Details</title>
<link rel="icon" type="image/png" href="../style/imgs/gb.png">
<link rel="stylesheet" href="../style/style.css">
<link rel="stylesheet" href="../style/checkout.css">
<link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Lemon&display=swap" rel="stylesheet">
</head>
<body 
    data-product-id="<?= $product['product_id'] ?>" 
    data-user-id="<?= $user_id ?>" 
    data-seller-id="<?= $product['seller_id'] ?>" 
    data-current-price="<?= number_format($product['current_price'],2,'.','') ?>"
    data-expiry="<?= $product['p_expiry_date'] ?>"
>


<?php include __DIR__ . '/../layout/headweb.php';?> 

<div class="container">
    <section class="product-info-section">
        <div class="product-image-container">
            <img src="<?= htmlspecialchars($product['img']) ?>" alt="<?= htmlspecialchars($product['pname']) ?>">
        </div>

        <div class="product-details-content">
            <h1 class="product-title"><?= htmlspecialchars($product['pname']) ?></h1>
            <p class="product-category-tag">
                <span class="category-badge"><?= htmlspecialchars($product['category']) ?></span> 
                <span class="tag-badge"><?= htmlspecialchars($product['product_tag']) ?></span>
            </p>

            <div class="rating-group">
                <p class="product-rating">
                    Product Rating: <span class="stars"><?= display_stars($product['product_rating']) ?></span> (<?= number_format($product['product_rating'],1) ?>)
                </p>
                <p class="seller-rating">
                    Seller Rating: <span class="stars"><?= display_stars($product['seller_rating']) ?></span> (<?= number_format($product['seller_rating'],1) ?>)
                </p>
                <p class="seller-name">
                    Seller: <a href="../account/public_profile.php?uid=<?= $product['seller_id'] ?>" class="seller-link">
                    <?= htmlspecialchars($product['seller_name']) ?>
                    </a>
                </p>
                <p class="location-expiry">
                     Available in: <strong><?= htmlspecialchars($product['city']) ?></strong> | 📅 Expires: <strong id="expiryDate"><?= date('M d, Y', strtotime($product['p_expiry_date'])) ?></strong>
                </p>
            </div>

            <div class="price-box">
                <?php if ($product['discount'] > 0): ?>
                    <div class="discount-pill"><?= $product['discount'] ?>% OFF</div>
                    <span class="original-price-strike">₹<?= number_format($product['price'],2) ?>/kg</span>
                <?php endif; ?>
                <span class="final-price-large">₹<span id="unitPriceValue"><?= number_format($product['current_price'],2) ?></span>/kg</span>
                <p class="stock-info">In Stock: <strong><?= htmlspecialchars($product['stock_quantity']) ?> kg </strong></p>
            </div>

            <p class="description-text"><strong>Description:</strong><br><?= nl2br(htmlspecialchars($product['p_description'])) ?></p>

            <div class="checkout-actions">
                <div class="quantity-control">
                    <label for="quantityInput">Quantity (kg):</label>
                    <input type="number" id="quantityInput" value="1" min="0.1" max="<?= htmlspecialchars($product['stock_quantity']) ?>" step="0.1">
                </div>

                <div class="total-price-display">
                    Total Price: <span class="price-value">₹<span id="totalPriceValue"><?= number_format($product['current_price'],2) ?></span></span>
                </div>

                <div class="action-buttons">
                    <button class="btn-add-to-cart" id="addToCartBtn">🛒 Add to Basket</button>
                    <button class="btn-buy-now" id="buyNowBtn">💰 Buy Now</button>
                </div>
            </div>

            <div id="messageBox" class="message-box" style="display: none;"></div>
        </div>
    </section>

    <section class="seller-reviews-section">
        <h2>Reviews for <?= htmlspecialchars($product['pname']) ?></h2>
        <div class="reviews-list">
            <?php if (!empty($product_reviews)): ?>
                <?php foreach ($product_reviews as $review): ?>
                    <div class="review-card">
                        <h3 class="review-user-name"><?= htmlspecialchars($review['full_name']) ?></h3>
                        <div class="review-rating"><?= display_stars($review['rating']) ?> (<?= number_format($review['rating'],1) ?>)</div>
                        <p class="review-text"><?= nl2br(htmlspecialchars($review['review'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-reviews">No reviews for this product yet.</p>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../layout/footer.php';?>

<script src="../script/checkout.js"></script>
</body>
</html>