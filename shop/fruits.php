<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

// Define category (static for now)
$category = 'fruits';

// ✅ 1. Fetch Current User City (safe, minimal queries)
$currentUserCity = '';
if (!empty($_SESSION['uid'])) {
    $uid = $_SESSION['uid'];
    $cityQuery = "SELECT city FROM user_profiles WHERE uid = ?";
    $stmt = $conn->prepare($cityQuery);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->bind_result($currentUserCity);
    $stmt->fetch();
    $stmt->close();
}

// ✅ 2. Fetch Maximum Price (fallback handled)
$max_price_val = 1000;
$max_price_query = "SELECT CEIL(MAX(price)) AS max_price FROM products WHERE category = ?";
$stmt = $conn->prepare($max_price_query);
$stmt->bind_param("s", $category);
$stmt->execute();
$stmt->bind_result($db_max_price);
$stmt->fetch();
$stmt->close();

if (!empty($db_max_price)) {
    $max_price_val = (float)$db_max_price;
}

// ✅ 3. Optimized Product Query
// Uses indexes, COALESCE for null safety, and filters only available items
$sql = "
    SELECT 
        p.product_id, p.pname, p.price, p.quantity, p.img, p.discount, 
        p.product_tag, p.city AS product_city, 
        u.full_name AS seller_name,
        COALESCE(sr.avg_rating, 0.00) AS avg_seller_rating, 
        COALESCE(apr.avg_rating, 0.00) AS avg_product_rating 
    FROM products p
    INNER JOIN user_profiles u ON p.seller_id = u.uid
    LEFT JOIN seller_ratings sr ON p.seller_id = sr.seller_id
    LEFT JOIN (
        SELECT product_id, AVG(CAST(rating AS DECIMAL(10,2))) AS avg_rating 
        FROM ratings GROUP BY product_id
    ) apr ON p.product_id = apr.product_id
    WHERE 
        p.category = ? 
        AND p.quantity > 0 
        AND p.p_status = 'available'
        AND p.p_expiry_date > CURDATE()
    ORDER BY p.product_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="../style/imgs/gb.png">
<link rel="shortcut icon" type="image/x-icon" href="../style/imgs/gb.png">
<title>GreenBasket - <?= htmlspecialchars(ucfirst($category)) ?></title>
<link rel="stylesheet" href="../style/style.css">
<link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&family=Lemon&display=swap" rel="stylesheet">
</head>

<body data-max-price="<?= $max_price_val ?>" data-user-city="<?= htmlspecialchars($currentUserCity) ?>">
<?php include __DIR__ . '/../layout/headweb.php'; ?>

<section class="shop-sections">
    <h2 class="shop-title">Today's Fresh Finds <?= htmlspecialchars(ucfirst($category)) ?></h2>
    <div class="search-container">
        <input type="text" id="productSearch" placeholder="Search products by name..." class="search-input">
    </div>
</section>

<section class="shop-section">
    <aside class="filter-sidebar">
        <h3 class="filter-title">Filter Options</h3>
        <a href="#" id="clearFilters" class="clear-filters-link">Clear All</a>

        <div class="filter-group">
            <h4>Price Sorting</h4>
            <select id="priceOrder" class="filter-select">
                <option value="">Default Order</option>
                <option value="desc">Highest Price First</option>
                <option value="asc">Lowest Price First</option>
            </select>
        </div>

        <div class="filter-group">
            <h4>Max Price Range</h4>
            <input type="range" id="priceRange" min="0" max="<?= $max_price_val ?>" value="<?= $max_price_val ?>" class="price-slider">
            <p>Max Price: <span id="priceValue">₹<?= $max_price_val ?></span></p>
        </div>

        <div class="filter-group">
            <h4>Max Price Box</h4>
            <input type="number" id="maxPriceInput" min="0" max="<?= $max_price_val ?>" value="<?= $max_price_val ?>" step="1">
        </div>

        <div class="filter-group">
            <label><input type="checkbox" id="filterDiscountOnly"> Show Discounted Items Only</label>
        </div>

        <div class="filter-group">
            <h4>Location</h4>
            <label><input type="checkbox" id="filterNearMe"> Near Me <?= $currentUserCity ? '(' . htmlspecialchars($currentUserCity) . ')' : '' ?></label>
        </div>

        <div class="filter-group rating-filter-container">
            <h4>Minimum <?= htmlspecialchars(ucfirst($category)) ?> Rating</h4>
            <?php for ($i = 4; $i >= 0; $i--): ?>
                <input type="radio" name="minProductRating" value="<?= $i ?>" id="p<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?>>
                <label for="p<?= $i ?>"><?= str_repeat('★', $i ?: 1) ?> (<?= $i ?: 'Any' ?><?= $i ? '+' : '' ?>)</label>
            <?php endfor; ?>
        </div>

        <div class="filter-group rating-filter-container">
            <h4>Minimum Seller Rating</h4>
            <?php for ($i = 4; $i >= 0; $i--): ?>
                <input type="radio" name="minSellerRating" value="<?= $i ?>" id="s<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?>>
                <label for="s<?= $i ?>"><?= str_repeat('★', $i ?: 1) ?> (<?= $i ?: 'Any' ?><?= $i ? '+' : '' ?>)</label>
            <?php endfor; ?>
        </div>
    </aside>

    <main class="product-listing">
        <?php if ($result->num_rows === 0): ?>
            <p style="text-align:center;width:100%;padding:50px;">No <?= htmlspecialchars($category) ?> products available.</p>
        <?php else: ?>
            <?php while ($product = $result->fetch_assoc()): 
                $has_discount = (int)$product['discount'] > 0;
                $original_price = (float)$product['price'];
                $current_price = $has_discount ? $original_price * (1 - ($product['discount'] / 100)) : $original_price;
                $product_rating = (float)$product['avg_product_rating'];
                $seller_rating = (float)$product['avg_seller_rating'];
            ?>
            <div 
                class="product-card"
                data-name="<?= strtolower(htmlspecialchars($product['pname'])) ?>"
                data-price="<?= $original_price ?>"
                data-current-price="<?= $current_price ?>"
                data-has-discount="<?= $has_discount ?>"
                data-product-rating="<?= $product_rating ?>"
                data-seller-rating="<?= $seller_rating ?>"
                data-city="<?= htmlspecialchars($product['product_city']) ?>"
                onclick="window.location.href='shop.php?id=<?= $product['product_id'] ?>'">
                
                <?php if ($product['product_tag']): ?>
                    <span class="product-tag"><?= htmlspecialchars($product['product_tag']) ?></span>
                <?php endif; ?>

                <?php if ($has_discount): ?>
                    <span class="sale-badge">- <?= $product['discount'] ?>% OFF</span>
                <?php endif; ?>

                <div class="product-image-container">
                    <img src="<?= htmlspecialchars($product['img']) ?>" alt="<?= htmlspecialchars($product['pname']) ?>" class="product-image">
                </div>

                <div class="product-details">
                    <h4 class="product-name"><?= htmlspecialchars($product['pname']) ?></h4>
                    <p class="seller-name">Sold by: <strong><?= htmlspecialchars($product['seller_name']) ?></strong></p>
                    <p class="product-rating">Product: <span class="stars"><?= str_repeat('★', round($product_rating)) ?><?= str_repeat('☆', 5 - round($product_rating)) ?></span> (<?= number_format($product_rating, 1) ?>)</p>
                    <p class="seller-rating">Seller: <span class="stars"><?= str_repeat('★', round($seller_rating)) ?><?= str_repeat('☆', 5 - round($seller_rating)) ?></span> (<?= number_format($seller_rating, 1) ?>)</p>

                    <div class="price-group">
                        <?php if ($has_discount): ?>
                            <span class="original-price">₹<?= number_format($original_price, 2) ?>/kg</span>
                        <?php endif; ?>
                        <span class="current-price">₹<?= number_format($current_price, 2) ?>/kg</span>
                    </div>

                    <button class="add-to-cart-btn" data-product-id="<?= $product['product_id'] ?>">Add to Basket</button>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </main>
</section>

<script>
const MAX_PRICE_DB = <?= $max_price_val ?>;
const USER_CITY = <?= json_encode($currentUserCity) ?>;
</script>
<?php include __DIR__ . '/../layout/footer.php';?>

<script src="../script/filter.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
