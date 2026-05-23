<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

$category = 'vegetables';

// 1. Fetch Current User City
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

// 2. Fetch Maximum Price
$max_price_val = 1000;
$max_price_query = "SELECT CEIL(MAX(price)) AS max_price FROM products WHERE category = ?";
$stmt = $conn->prepare($max_price_query);
$stmt->bind_param("s", $category);
$stmt->execute();
$stmt->bind_result($db_max_price);
$stmt->fetch();
$stmt->close();
if (!empty($db_max_price)) $max_price_val = (float)$db_max_price;

// 3. Product Query
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
    WHERE p.category = ? AND p.quantity > 0
      AND p.p_status = 'available' AND p.p_expiry_date > CURDATE()
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
<title>GreenBasket — Vegetables</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Lemon&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* ════════════════════════════════════════
   GreenBasket — Vegetables Page
   Includes full headweb.php header styles
   ════════════════════════════════════════ */
:root {
  --forest:       #1A3020;
  --moss:         #2C4A2E;
  --sage:         #7A9E7E;
  --mint:         #B8D4BB;
  --cream:        #F5F0E8;
  --parchment:    #EDE7D9;
  --gb-green:     #6EC97A;
  --amber:        #C8955A;
  --white:        #FFFFFF;
  --charcoal:     #1C1C1C;
  --muted:        #6B7B6D;
  --nav-h:        68px;
  --sidebar-w:    272px;
  --radius-card:  16px;
  --ff-display:   'Playfair Display', Georgia, serif;
  --ff-body:      'DM Sans', sans-serif;
  --ff-mono:      'DM Mono', monospace;
  --ease-smooth:  cubic-bezier(0.4,0,0.2,1);
  --ease-bounce:  cubic-bezier(0.34,1.56,0.64,1);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
  font-family: var(--ff-body);
  background: var(--parchment);
  color: var(--charcoal);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  padding-top: var(--nav-h);
}
a { text-decoration: none; color: inherit; }
ul { list-style: none; }
img { display: block; max-width: 100%; }

/* ── headweb.php header styles ── */
.site-header1 {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 1000;
  height: var(--nav-h);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 48px;
  background: rgba(26,48,32,0.97);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  box-shadow: 0 2px 32px rgba(0,0,0,0.22);
  border-bottom: 1px solid rgba(110,201,122,0.12);
}

.site-header1 .site-logo a {
  display: flex;
  align-items: baseline;
  gap: 1px;
  text-decoration: none;
}

.logo-green1 {
  background: linear-gradient(135deg, #5DF56A 0%, #22C55E 45%, #16A34A 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-family: 'Lemon', cursive;
  font-size: 1.6rem;
  font-weight: 900;
}

.logo-basket1 {
  color: var(--cream);
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.55rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.nav-menu1 {
  display: flex;
  align-items: center;
  gap: 2px;
  list-style: none;
  padding: 0; margin: 0;
}

.nav-menu1 > li > a {
  font-family: var(--ff-body);
  font-size: 0.82rem;
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(245,240,232,0.82);
  padding: 8px 15px;
  border-radius: 100px;
  text-decoration: none;
  transition: color 0.2s, background 0.2s;
}
.nav-menu1 > li > a:hover { color: #fff; background: rgba(255,255,255,0.1); }

.btn-gradient1 {
  display: inline-flex;
  align-items: center;
  font-family: var(--ff-body);
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--forest) !important;
  -webkit-text-fill-color: var(--forest) !important;
  background: linear-gradient(135deg, #5DF56A, #22C55E);
  padding: 9px 22px;
  border-radius: 100px;
  text-decoration: none;
  box-shadow: 0 3px 16px rgba(78,197,88,0.32);
  transition: transform 0.2s var(--ease-bounce), box-shadow 0.2s;
}
.btn-gradient1:hover { transform: translateY(-2px) scale(1.03); box-shadow: 0 7px 22px rgba(110,201,122,0.48); }

/* Profile dropdown */
.profile-dropdown1 { position: relative; }

.btn-profile-style1 {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 100px;
  color: var(--cream);
  font-family: var(--ff-body);
  font-size: 0.84rem;
  font-weight: 500;
  padding: 8px 18px;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
}
.btn-profile-style1:hover,
.btn-profile-style1.open {
  background: rgba(255,255,255,0.18);
  border-color: rgba(110,201,122,0.45);
}

.dropdown-icon1 {
  font-size: 0.66rem;
  transition: transform 0.22s var(--ease-smooth);
  display: inline-block;
}
.btn-profile-style1.open .dropdown-icon1 { transform: rotate(180deg); }

.dropdown-link-group1 {
  position: absolute;
  top: calc(100% + 14px);
  right: 0;
  min-width: 224px;
  background: var(--forest);
  border: 1px solid rgba(110,201,122,0.16);
  border-radius: 18px;
  overflow: hidden;
  list-style: none;
  padding: 8px 0;
  box-shadow: 0 20px 52px rgba(0,0,0,0.38);
  opacity: 0;
  pointer-events: none;
  transform: translateY(-10px) scale(0.96);
  transform-origin: top right;
  transition: opacity 0.2s var(--ease-smooth), transform 0.2s var(--ease-smooth);
}
.dropdown-link-group1.open {
  opacity: 1;
  pointer-events: all;
  transform: translateY(0) scale(1);
}
.dropdown-link-group1 li a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 18px;
  font-family: var(--ff-body);
  font-size: 0.87rem;
  color: var(--mint);
  text-decoration: none;
  transition: background 0.15s, color 0.15s;
}
.dropdown-link-group1 li a:hover { background: rgba(110,201,122,0.1); color: #fff; }

.list-icon1 { font-size: 1rem; width: 20px; text-align: center; flex-shrink: 0; }

.divider1 {
  height: 1px;
  background: rgba(255,255,255,0.08);
  margin: 4px 14px;
}

/* ── Shop hero ── */
.shop-hero {
  background: linear-gradient(135deg, var(--forest) 0%, var(--moss) 60%, #3a6640 100%);
  padding: 48px 72px 40px;
  position: relative;
  overflow: hidden;
}
.shop-hero::before {
  content: '';
  position: absolute;
  left: -80px; bottom: -80px;
  width: 320px; height: 320px;
  background: radial-gradient(circle, rgba(93,245,106,0.1) 0%, transparent 65%);
  border-radius: 50%;
  pointer-events: none;
}
.shop-hero::after {
  content: '';
  position: absolute;
  right: -60px; top: -60px;
  width: 380px; height: 380px;
  background: radial-gradient(circle, rgba(110,201,122,0.12) 0%, transparent 65%);
  border-radius: 50%;
  pointer-events: none;
}

.shop-hero-eyebrow {
  font-family: var(--ff-mono);
  font-size: 0.68rem;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--gb-green);
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.shop-hero-eyebrow::before {
  content: '';
  display: block;
  width: 20px; height: 1px;
  background: var(--gb-green);
}

.shop-hero-title {
  font-family: var(--ff-display);
  font-size: clamp(1.9rem, 4vw, 2.9rem);
  font-weight: 900;
  letter-spacing: -0.025em;
  color: var(--white);
  margin-bottom: 22px;
  line-height: 1.1;
}
.shop-hero-title em { font-style: italic; color: var(--gb-green); }

.shop-search-wrap {
  display: flex;
  align-items: center;
  background: rgba(255,255,255,0.09);
  border: 1.5px solid rgba(255,255,255,0.18);
  border-radius: 12px;
  max-width: 460px;
  overflow: hidden;
  transition: background 0.2s, border-color 0.2s;
}
.shop-search-wrap:focus-within {
  background: rgba(255,255,255,0.14);
  border-color: rgba(110,201,122,0.5);
}
.shop-search-icon { padding: 0 14px; color: rgba(245,240,232,0.5); font-size: 0.88rem; flex-shrink: 0; }
#productSearch {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  padding: 13px 14px 13px 0;
  font-family: var(--ff-body);
  font-size: 0.9rem;
  color: var(--white);
}
#productSearch::placeholder { color: rgba(245,240,232,0.4); }

/* ── Layout ── */
.shop-layout {
  display: flex;
  flex: 1;
  align-items: flex-start;
}

/* ── Filter sidebar ── */
.filter-sidebar {
  width: var(--sidebar-w);
  flex-shrink: 0;
  background: var(--white);
  border-right: 1px solid rgba(44,74,46,0.1);
  padding: 32px 24px;
  min-height: calc(100vh - var(--nav-h));
  position: sticky;
  top: var(--nav-h);
  overflow-y: auto;
}
.filter-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 26px;
}
.filter-title {
  font-family: var(--ff-display);
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--forest);
}
.clear-filters-link {
  font-family: var(--ff-mono);
  font-size: 0.68rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--amber);
  border-bottom: 1px solid transparent;
  transition: border-color 0.2s;
}
.clear-filters-link:hover { border-color: var(--amber); }

.filter-group {
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid rgba(44,74,46,0.07);
}
.filter-group:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

.filter-group h4 {
  font-family: var(--ff-body);
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 12px;
}
.filter-select {
  width: 100%;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1.5px solid rgba(44,74,46,0.16);
  background: var(--parchment);
  font-family: var(--ff-body);
  font-size: 0.87rem;
  color: var(--charcoal);
  outline: none;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%236B7B6D'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  transition: border-color 0.2s;
}
.filter-select:focus { border-color: var(--gb-green); }

/* Price slider */
.price-slider {
  width: 100%;
  accent-color: var(--gb-green);
  cursor: pointer;
  margin-bottom: 8px;
}
.price-display {
  font-size: 0.82rem;
  color: var(--muted);
  font-family: var(--ff-mono);
}
.price-display span { color: var(--forest); font-weight: 600; }

/* Number input */
.filter-group input[type="number"] {
  width: 100%;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1.5px solid rgba(44,74,46,0.16);
  background: var(--parchment);
  font-family: var(--ff-mono);
  font-size: 0.88rem;
  color: var(--charcoal);
  outline: none;
  transition: border-color 0.2s;
}
.filter-group input[type="number"]:focus { border-color: var(--gb-green); }

/* Checkbox / radio labels */
.filter-check-label {
  display: flex;
  align-items: center;
  gap: 9px;
  font-size: 0.86rem;
  color: var(--charcoal);
  cursor: pointer;
  padding: 5px 0;
  transition: color 0.15s;
}
.filter-check-label:hover { color: var(--forest); }
.filter-check-label input { accent-color: var(--gb-green); cursor: pointer; }

.rating-filter-container .filter-check-label { font-size: 0.83rem; }

/* ── Product grid ── */
.product-listing {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(224px, 1fr));
  gap: 22px;
  padding: 32px 32px;
  align-content: start;
}

/* Empty state */
.empty-state {
  grid-column: 1 / -1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
  padding: 80px 20px;
  color: var(--muted);
  font-size: 1rem;
}
.empty-state i { font-size: 2.4rem; color: var(--sage); }

/* ── Product card ── */
.product-card {
  background: var(--white);
  border-radius: var(--radius-card);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  position: relative;
  border: 1px solid rgba(44,74,46,0.07);
  box-shadow: 0 2px 12px rgba(26,48,32,0.07);
  transition: transform 0.25s var(--ease-smooth), box-shadow 0.25s var(--ease-smooth);
}
.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 36px rgba(26,48,32,0.14);
}

/* Badges */
.product-tag {
  position: absolute;
  top: 12px; left: 12px;
  z-index: 2;
  background: var(--forest);
  color: var(--mint);
  font-family: var(--ff-mono);
  font-size: 0.63rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: 100px;
}
.sale-badge {
  position: absolute;
  top: 12px; right: 12px;
  z-index: 2;
  background: linear-gradient(135deg, #F97316, #EF4444);
  color: #fff;
  font-family: var(--ff-mono);
  font-size: 0.63rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  padding: 4px 10px;
  border-radius: 100px;
}

.product-image-container {
  width: 100%;
  aspect-ratio: 4 / 3;
  overflow: hidden;
  background: var(--parchment);
}
.product-image {
  width: 100%; height: 100%;
  object-fit: cover;
  transition: transform 0.5s var(--ease-smooth);
}
.product-card:hover .product-image { transform: scale(1.06); }

/* Product details */
.product-details {
  padding: 18px 18px 20px;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 5px;
}
.product-name {
  font-family: var(--ff-display);
  font-size: 1rem;
  font-weight: 700;
  color: var(--forest);
  letter-spacing: -0.01em;
  margin-bottom: 3px;
  line-height: 1.25;
}
.seller-name { font-size: 0.77rem; color: var(--muted); }
.seller-name strong { color: var(--moss); }

.product-rating,
.seller-rating {
  font-size: 0.74rem;
  color: var(--muted);
  display: flex;
  align-items: center;
  gap: 4px;
}
.stars { color: #F59E0B; font-size: 0.8rem; letter-spacing: -1px; }

.price-group {
  display: flex;
  align-items: center;
  gap: 9px;
  margin-top: 8px;
}
.original-price {
  font-size: 0.8rem;
  color: var(--muted);
  text-decoration: line-through;
}
.current-price {
  font-family: var(--ff-display);
  font-size: 1.12rem;
  font-weight: 800;
  color: var(--forest);
}

/* Add to cart */
.add-to-cart-btn {
  width: 100%;
  padding: 12px 14px;
  margin-top: 12px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #5DF56A, #22C55E);
  color: var(--forest);
  font-family: var(--ff-body);
  font-size: 0.83rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  cursor: pointer;
  box-shadow: 0 2px 10px rgba(34,197,94,0.22);
  transition: transform 0.2s var(--ease-bounce), box-shadow 0.2s;
}
.add-to-cart-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(34,197,94,0.4);
}

/* ── Responsive ── */
@media (max-width: 1024px) {
  .shop-hero { padding: 40px 36px 36px; }
  .product-listing { padding: 26px 24px; }
}
@media (max-width: 768px) {
  .site-header1 { padding: 0 20px; }
  .nav-menu1 > li:not(.profile-dropdown1):not(.login-li1) { display: none; }
  .shop-hero { padding: 32px 20px 28px; }
  .shop-layout { flex-direction: column; }
  .filter-sidebar {
    width: 100%;
    position: static;
    min-height: auto;
    padding: 22px 20px;
    border-right: none;
    border-bottom: 1px solid rgba(44,74,46,0.1);
  }
  .product-listing { padding: 22px 18px; grid-template-columns: repeat(2, 1fr); gap: 14px; }
}
@media (max-width: 480px) {
  .product-listing { grid-template-columns: 1fr; }
}
</style>
</head>

<body data-max-price="<?= $max_price_val ?>" data-user-city="<?= htmlspecialchars($currentUserCity) ?>">

<?php include __DIR__ . '/../layout/headweb.php'; ?>

<!-- Shop hero -->
<div class="shop-hero">
  <div class="shop-hero-eyebrow">Fresh Today</div>
  <h1 class="shop-hero-title">Today's <em>Vegetables</em></h1>
  <div class="shop-search-wrap">
    <span class="shop-search-icon"><i class="fas fa-search"></i></span>
    <input type="text" id="productSearch" placeholder="Search vegetables by name…" class="search-input">
  </div>
</div>

<!-- Shop layout -->
<div class="shop-layout">

  <!-- Filter sidebar -->
  <aside class="filter-sidebar">
    <div class="filter-header">
      <h3 class="filter-title">Filters</h3>
      <a href="#" id="clearFilters" class="clear-filters-link">Clear All</a>
    </div>

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
      <p class="price-display">Up to: <span id="priceValue">₹<?= $max_price_val ?></span></p>
    </div>

    <div class="filter-group">
      <h4>Exact Max Price (₹)</h4>
      <input type="number" id="maxPriceInput" min="0" max="<?= $max_price_val ?>" value="<?= $max_price_val ?>" step="1">
    </div>

    <div class="filter-group">
      <h4>Deals</h4>
      <label class="filter-check-label">
        <input type="checkbox" id="filterDiscountOnly"> Show Discounted Items Only
      </label>
    </div>

    <div class="filter-group">
      <h4>Location</h4>
      <label class="filter-check-label">
        <input type="checkbox" id="filterNearMe">
        Near Me <?= $currentUserCity ? '(' . htmlspecialchars($currentUserCity) . ')' : '' ?>
      </label>
    </div>

    <div class="filter-group rating-filter-container">
      <h4>Min Vegetable Rating</h4>
      <?php for ($i = 4; $i >= 0; $i--): ?>
        <label class="filter-check-label">
          <input type="radio" name="minProductRating" value="<?= $i ?>" id="p<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?>>
          <?= str_repeat('★', $i ?: 1) ?> (<?= $i ?: 'Any' ?><?= $i ? '+' : '' ?>)
        </label>
      <?php endfor; ?>
    </div>

    <div class="filter-group rating-filter-container">
      <h4>Min Seller Rating</h4>
      <?php for ($i = 4; $i >= 0; $i--): ?>
        <label class="filter-check-label">
          <input type="radio" name="minSellerRating" value="<?= $i ?>" id="s<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?>>
          <?= str_repeat('★', $i ?: 1) ?> (<?= $i ?: 'Any' ?><?= $i ? '+' : '' ?>)
        </label>
      <?php endfor; ?>
    </div>
  </aside>

  <!-- Product grid -->
  <main class="product-listing">
    <?php if ($result->num_rows === 0): ?>
      <div class="empty-state">
        <i class="fas fa-seedling"></i>
        <p>No vegetable products available right now.</p>
      </div>
    <?php else: ?>
      <?php while ($product = $result->fetch_assoc()):
        $has_discount   = (int)$product['discount'] > 0;
        $original_price = (float)$product['price'];
        $current_price  = $has_discount ? $original_price * (1 - ($product['discount'] / 100)) : $original_price;
        $product_rating = (float)$product['avg_product_rating'];
        $seller_rating  = (float)$product['avg_seller_rating'];
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
          <span class="sale-badge">-<?= $product['discount'] ?>% OFF</span>
        <?php endif; ?>

        <div class="product-image-container">
          <img src="<?= htmlspecialchars($product['img']) ?>"
               alt="<?= htmlspecialchars($product['pname']) ?>"
               class="product-image">
        </div>

        <div class="product-details">
          <h4 class="product-name"><?= htmlspecialchars($product['pname']) ?></h4>
          <p class="seller-name">by <strong><?= htmlspecialchars($product['seller_name']) ?></strong></p>
          <p class="product-rating">
            Product: <span class="stars"><?= str_repeat('★', round($product_rating)) ?><?= str_repeat('☆', 5 - round($product_rating)) ?></span>
            (<?= number_format($product_rating, 1) ?>)
          </p>
          <p class="seller-rating">
            Seller: <span class="stars"><?= str_repeat('★', round($seller_rating)) ?><?= str_repeat('☆', 5 - round($seller_rating)) ?></span>
            (<?= number_format($seller_rating, 1) ?>)
          </p>
          <div class="price-group">
            <?php if ($has_discount): ?>
              <span class="original-price">₹<?= number_format($original_price, 2) ?>/kg</span>
            <?php endif; ?>
            <span class="current-price">₹<?= number_format($current_price, 2) ?>/kg</span>
          </div>
          <button class="add-to-cart-btn" data-product-id="<?= $product['product_id'] ?>">
            <i class="fas fa-basket-shopping" style="margin-right:6px"></i> Add to Basket
          </button>
        </div>
      </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </main>

</div><!-- /shop-layout -->

<script>
const MAX_PRICE_DB = <?= $max_price_val ?>;
const USER_CITY = <?= json_encode($currentUserCity) ?>;
</script>

<!-- Dropdown JS for headweb.php (profileBtn1 / dropdownMenu1) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const btn  = document.getElementById('profileBtn1');
  const menu = document.getElementById('dropdownMenu1');
  if (!btn || !menu) return;

  const open  = () => { menu.classList.add('open'); btn.classList.add('open'); btn.setAttribute('aria-expanded','true'); menu.setAttribute('aria-hidden','false'); };
  const close = () => { menu.classList.remove('open'); btn.classList.remove('open'); btn.setAttribute('aria-expanded','false'); menu.setAttribute('aria-hidden','true'); };

  btn.addEventListener('click',  e => { e.stopPropagation(); menu.classList.contains('open') ? close() : open(); });
  menu.addEventListener('click', e => e.stopPropagation());
  document.addEventListener('click', close);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
<script src="../script/filter.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
