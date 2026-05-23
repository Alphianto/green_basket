<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB connection failed.");

$user_id = $_SESSION['uid'] ?? null;
if (!$user_id) die("User not logged in.");

// Fetch cart with joined product + seller info
$sql = "SELECT c.cart_id, c.product_id, c.quantity AS cart_qty,
               p.pname AS name, p.price AS price, p.img AS image,
               p.quantity AS stock, p.discount, p.p_expiry_date,
               u.full_name AS seller, u.uid AS seller_id
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        JOIN user_profiles u ON p.seller_id = u.uid
        WHERE c.user_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) die("SQL Prepare Failed: " . $conn->error);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
while ($row = $result->fetch_assoc()) {
    // Remove expired products
    if (!empty($row['p_expiry_date']) && strtotime($row['p_expiry_date']) < strtotime('today')) {
        $del = $conn->prepare("DELETE FROM cart WHERE user_id=? AND product_id=?");
        $del->bind_param("ii", $user_id, $row['product_id']);
        $del->execute();
        $del->close();
        continue;
    }
    $cart_items[] = $row;
}
$stmt->close();

// Store items for payment page use
$_SESSION['cart_items'] = [];
foreach ($cart_items as $item) {
    $_SESSION['cart_items'][] = [
        'product_id' => $item['product_id'],
        'seller_id'  => $item['seller_id'],
        'quantity'   => $item['cart_qty'],
        'price'      => $item['discount'] ? ($item['price'] * (1 - $item['discount'] / 100)) : $item['price']
    ];
}

// Calculate totals
function calculateCartTotals(array $cart): array {
    $subtotal = 0;
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $shipping = $subtotal >= 100 ? 0 : 5;
    $tax = $subtotal * 0.05; // 5% tax
    return [$subtotal, $shipping, $tax, $subtotal + $shipping + $tax];
}

list($subtotal, $shipping, $tax, $total) = calculateCartTotals($_SESSION['cart_items']);
$cart_empty = empty($_SESSION['cart_items']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shopping Cart | GreenBasket</title>
<link rel="icon" type="image/png" href="../style/imgs/gb.png">
<link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&family=Lemon&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../style/cart.css">
<link rel="stylesheet" href="../style/style.css">
</head>
<body>
<?php include __DIR__ . '/../layout/headweb.php'; ?>

<main class="cart-page">
  <h1 class="cart-title">Your Shopping Basket</h1>
  <p class="cart-subtitle">Review and manage your farm-fresh selections.</p>

  <?php if ($cart_empty): ?>
  <div class="empty-cart-message">
    <h2>Your basket is empty!</h2>
    <a href="../index.php" class="shop-now-btn">Go Shopping →</a>
  </div>
  <?php else: ?>
  <div class="cart-container">
    <div class="cart-items">
      <?php foreach($cart_items as $item):
        $price = $item['discount'] ? ($item['price'] * (1 - $item['discount'] / 100)) : $item['price'];
        $total_price = $price * $item['cart_qty'];
      ?>
      <div class="cart-item-card" 
           data-product-id="<?= $item['product_id'] ?>" 
           data-stock="<?= $item['stock'] ?>" 
           data-price="<?= $price ?>">
        <button class="remove-item-btn">&times;</button>
        <div class="item-image">
          <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
        </div>
        <div class="item-details">
          <h3 class="item-name"><?= htmlspecialchars($item['name']) ?></h3>
          <p class="item-seller">Seller: 
            <a href="../seller/profile.php?id=<?= $item['seller_id'] ?>" class="seller-link">
              <?= htmlspecialchars($item['seller']) ?>
            </a>
          </p>
          <p class="item-price">
            ₹<?= number_format($item['price'],2) ?>/kg
            <?php if(!empty($item['discount'])): ?>
              <span class="discount">(-<?= $item['discount'] ?>%)</span>
            <?php endif; ?>
          </p>
          <p class="stock-info">Available: <?= number_format($item['stock'],3) ?> kg</p>
          <div class="quantity-control">
            <button class="qty-btn qty-decrease">-</button>
            <input type="number" class="qty-input" value="<?= $item['cart_qty'] ?>" min="0.1" step="0.1">
            <button class="qty-btn qty-increase">+</button>
          </div>
        </div>
        <div class="item-total">₹<span><?= number_format($total_price,2) ?></span></div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="cart-summary">
      <h2>Order Summary</h2>
      <div class="summary-line">Subtotal: ₹<span id="subtotalValue"><?= number_format($subtotal,2) ?></span></div>
      <div class="summary-line">Shipping: ₹<span id="shippingValue"><?= number_format($shipping,2) ?></span></div>
      <div class="summary-line">Tax (5%): ₹<span id="taxValue"><?= number_format($tax,2) ?></span></div>
      <div class="summary-total">Total: ₹<span id="totalValue"><?= number_format($total,2) ?></span></div>
      <button class="btn-gradient checkout-btn" id="proceedToPayment">Proceed to Checkout</button>
      <a href="../index.php" class="continue-shopping-link">← Continue Shopping</a>
    </div>
  </div>
  <?php endif; ?>
</main>
<?php include __DIR__ . '/../layout/footer.php';?>
<script>
document.getElementById('proceedToPayment')?.addEventListener('click', () => {
  window.location.href = 'payment_cart.php';
});
</script>
</body>
</html>
