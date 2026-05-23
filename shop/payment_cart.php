<?php
// shop/payment_cart.php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

// --- Checkout-specific timeout: 3 minutes (180 sec) ---
// Only clear checkout-related session keys, never destroy user login session.
if (isset($_SESSION['checkout_start']) && (time() - $_SESSION['checkout_start'] > 180)) {
    unset($_SESSION['checkout_start'], $_SESSION['checkout_products'], $_SESSION['checkout_total']);
}
$_SESSION['checkout_start'] = time();

// --- Authentication Guard ---
if (!isset($_SESSION['uid'])) {
    header("Location: ../account/login.php");
    exit();
}
$buyer_id = (int)$_SESSION['uid'];

// --- Fetch Cart Items (with product data) ---
$sql = "SELECT c.cart_id, c.quantity, p.product_id, p.pname, p.price, p.img AS image, p.seller_id
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL prepare error: " . htmlspecialchars($conn->error));
}
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$base_total = 0.0;
$total_qty = 0.0;
while ($row = $result->fetch_assoc()) {
    $row['quantity'] = (float)$row['quantity'];
    $row['price'] = (float)$row['price'];
    $cart_items[] = $row;
    $base_total += $row['price'] * $row['quantity'];
    $total_qty += $row['quantity'];
}
$stmt->close();

if (empty($cart_items)) {
    echo "<h2>Your cart is empty.</h2><p><a href='../shop/'>Return to shop</a></p>";
    exit();
}

$_SESSION['checkout_products'] = array_column($cart_items, 'product_id');
$_SESSION['checkout_total'] = $base_total;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Checkout — Payment</title>
  <link rel="icon" type="image/png" href="../style/imgs/gb.png">

  <link rel="stylesheet" href="../style/payment_cart.css">
  <link rel="stylesheet" href="../style/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Lemon&display=swap" rel="stylesheet">
</head>
<body
  data-base-total="<?= number_format($base_total, 2, '.', '') ?>"
  data-total-qty="<?= number_format($total_qty, 2, '.', '') ?>"
>
<?php include __DIR__ . '/../layout/headpay.php'; ?>

<main class="payment-page">
  <div class="checkout-container">
    <!-- LEFT: Order Summary -->
    <section class="order-summary">
      <div class="card">
        <h2>Your Order</h2>

        <?php foreach ($cart_items as $item): 
          $img = htmlspecialchars($item['image'] ?: 'imgs/placeholder.png');
          $pname = htmlspecialchars($item['pname']);
          $qty = number_format($item['quantity'], 2);
          $price = number_format($item['price'], 2);
          $subtotal = number_format($item['price'] * $item['quantity'], 2);
        ?>
        <div class="cart-item" data-cart-id="<?= (int)$item['cart_id'] ?>" data-product-id="<?= (int)$item['product_id'] ?>" data-qty="<?= $item['quantity'] ?>">
          <div class="thumb">
            <img src="<?= $img ?>" alt="<?= $pname ?>">
            <script>
              console.log("Product image for <?= addslashes($pname) ?>:", "../<?= addslashes($item['image']) ?>");
            </script>
          </div>
          <div class="info">
            <div class="name"><?= $pname ?></div>
            <div class="meta">Qty: <?= $qty ?> × ₹<?= $price ?></div>
            <div class="subtotal">₹<?= $subtotal ?></div>
          </div>
        </div>
        <?php endforeach; ?>

        <div class="summary-footer">
          <span class="total-label">Base Total:</span>
          <span class="total-value" id="baseTotal">₹<?= number_format($base_total, 2) ?></span>
        </div>

        <div class="summary-footer" style="margin-top:8px;">
          <span class="total-label">Payable Now :</span>
          <span class="total-value" id="payableNow">₹<?= number_format($base_total, 2) ?></span>
        </div>
      </div>
    </section>

    <!-- RIGHT: Payment Panel -->
    <section class="payment-panel">
      <div class="card">
        <h2>Payment Method</h2>

        <div class="methods" role="radiogroup" aria-label="Payment methods">
          <label class="method"><input type="radio" name="paymentMethod" value="card" checked> <i class="fas fa-credit-card"></i> Card</label>
          <label class="method"><input type="radio" name="paymentMethod" value="upi"> <i class="fas fa-qrcode"></i> UPI</label>
          <label class="method"><input type="radio" name="paymentMethod" value="cod"> <i class="fas fa-money-bill-wave"></i> Cash on Delivery (₹10 per unit)</label>
        </div>

        <!-- Card -->
        <div class="payment-details" id="cardDetails">
          <input type="text" id="cardNumber" maxlength="19" placeholder="Card Number — 16 digits" autocomplete="cc-number" />
          <div class="row">
            <input type="text" id="expiry" maxlength="5" placeholder="MM/YY" autocomplete="cc-exp" />
            <input type="password" id="cvv" maxlength="3" placeholder="CVV" autocomplete="cc-csc" />
          </div>
          <input type="text" id="cardName" maxlength="80" placeholder="Name on card" autocomplete="cc-name" />
        </div>

        <!-- UPI -->
        <div class="payment-details hidden" id="upiDetails">
          <input type="text" id="upiId" maxlength="64" placeholder="yourid@bank" />
        </div>

        <!-- COD Note -->
        <div class="note hidden" id="codNote">
          Cash on Delivery: ₹10 will be added <strong>per unit</strong>
        </div>

        <button class="btn" id="payNowBtn"><i class="fas fa-lock"></i> Pay Now</button>

        <div class="message-box hidden" id="messageBox" role="status" aria-live="polite"></div>
      </div>
    </section>
  </div>
</main>

<!-- Success Modal -->
<div id="successModal" class="success-modal hidden" aria-hidden="true">
  <div class="modal-content">
    <div class="success-icon-container"><i class="fas fa-check-circle success-icon"></i></div>
    <h2 id="modalTitle">Payment Successful!</h2>
    <p id="modalMessage">Your order(s) have been placed.</p>
    <div class="redirect-timer">Redirecting in <span id="timer">5</span> seconds...</div>
  </div>
</div>

<script src="../script/payment_cart.js"></script>
</body>
</html>
