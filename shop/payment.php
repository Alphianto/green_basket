<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB connection failed.");

// simple auth guard
if (!isset($_SESSION['uid'])) {
    header('Location: ../account/login.php');
    exit();
}
$buyer_id = (int)$_SESSION['uid'];

// --- NEW: Check if the user has a profile ---
$profile_check_query = "SELECT profile_id FROM user_profiles WHERE uid = ? LIMIT 1";
$stmt = $conn->prepare($profile_check_query);
if (!$stmt) die("SQL Error (Profile Check): " . $conn->error);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    // No profile found, redirect to profile creation page
    header('Location: ../account/edit_profile.php');
    exit();
}
// GET params
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
$quantity = isset($_GET['quantity']) ? (float)$_GET['quantity'] : null;
$price = isset($_GET['price']) ? (float)$_GET['price'] : null; // this is TOTAL (frontend sends total)

if (!$product_id || !$quantity || !$price || $quantity <= 0 || $price <= 0) {
    die("<h1>Error: Missing or invalid product information.</h1><p>Please return to the product page and try again.</p>");
}

// Set variables: user sends total price, so we use it as total_amount.
// For order row we still keep per-unit price derived from total.
$total_amount = round($price, 2);
$price_per_unit = ($quantity > 0) ? round($total_amount / $quantity, 2) : $total_amount;

// Fetch product info for display and minimal validation
$fetch_sql = "SELECT seller_id, pname FROM products WHERE product_id = ?";
$stmt = $conn->prepare($fetch_sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("<h1>Error: Product not found.</h1><p>The product ID is invalid.</p>");
}
$product_data = $result->fetch_assoc();
$seller_id = (int)$product_data['seller_id'];
$product_name = htmlspecialchars($product_data['pname']);
$stmt->close();

?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Complete Purchase - Payment</title>
        <link rel="icon" type="image/png" href="../style/imgs/gb.png">
        <link rel="stylesheet" href="../style/payment.css"> 
        <link rel="stylesheet" href="../style/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Lemon&display=swap" rel="stylesheet">
    </head>
    <body data-product-id="<?= $product_id ?>" 
        data-seller-id="<?= $seller_id ?>"
        data-buyer-id="<?= $buyer_id ?>"
        data-quantity="<?= $quantity ?>"
        data-price-per-unit="<?= $price_per_unit ?>"
        data-initial-total="<?= $total_amount ?>">

        <?php include __DIR__ . '/../layout/headpay.php';?> 
        <div class="paymet" >
        <div class="payment-container">
            <header class="payment-header">
                <h2>Complete Your Order</h2>
                <p class="product-summary">Paying for: <strong><?= $product_name ?></strong> (<?= number_format($quantity, 2) ?> units)</p>
            </header>

            <main class="payment-main">
                <!-- Total Amount Display -->
                <div class="total-summary-card">
                    <h3>Order Total: </h3>
                    <span id="finalTotalAmount" data-base-amount="<?= $total_amount ?>">₹<?= number_format($total_amount, 2) ?></span>
                    <p id="codFeeInfo" class="cod-fee-info" style="display:none;">+ ₹10.00 COD Fee</p>
                </div>
                
                <form id="paymentForm" class="payment-form">
                    <!-- Payment Method Selector -->
                    <div class="method-selector-group">
                        <label class="method-option">
                            <input type="radio" name="payment_method" value="card" checked>
                            <i class="fas fa-credit-card"></i> Card Payment
                        </label>
                        <label class="method-option">
                            <input type="radio" name="payment_method" value="upi">
                            <i class="fas fa-qrcode"></i> UPI
                        </label>
                        <label class="method-option">
                            <input type="radio" name="payment_method" value="cash">
                            <i class="fas fa-money-bill-wave"></i> Cash on Delivery (+₹10)
                        </label>
                    </div>
                    
                    <!-- Payment Detail Sections (Dynamically displayed) -->
                    <div id="cardDetails" class="payment-details-section active">
                        <h4 class="section-title">Card Details</h4>
                        <div class="form-group">
                            <label for="cardNumber">Card Number (16 Digits)</label>
                            <input type="text" id="cardNumber" name="cardNumber" placeholder="XXXX XXXX XXXX XXXX" maxlength="19" >
                            <span class="validation-error" id="cardNumberError"></span>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cardExpiry">Expiry Date (MM/YY)</label>
                                <input type="text" id="cardExpiry" name="cardExpiry" placeholder="MM/YY" maxlength="5">
                                <span class="validation-error" id="cardExpiryError"></span>
                            </div>
                            <div class="form-group">
                                <label for="cardCVV">CVV (3 Digits)</label>
                                <input type="password" id="cardCVV" name="cardCVV" placeholder="***" maxlength="3" >
                                <span class="validation-error" id="cardCVVError"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cardName">Name on Card</label>
                            <input type="text" id="cardName" name="cardName" >
                        </div>
                    </div>

                    <div id="upiDetails" class="payment-details-section hidden">
                        <h4 class="section-title">UPI Details</h4>
                        <div class="form-group">
                            <label for="upiId">Enter UPI ID</label>
                            <input type="text" id="upiId" name="upiId" placeholder="user@bankname" >
                            <span class="validation-error" id="upiIdError"></span>
                        </div>
                        <p class="upi-note">You will receive a collection request on your UPI app to complete the payment.</p>
                    </div>

                    <div id="cashDetails" class="payment-details-section hidden">
                        <h4 class="section-title">Cash on Delivery</h4>
                        <p class="cod-note">Please keep the exact amount ready to pay the delivery agent.</p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="payButton" class="pay-button"><i class="fas fa-lock"></i> Process Payment</button>
                </form>
                
                <div id="messageBox" class="message-box hidden"></div>
            </main>
        </div>

        <!-- Success Pop-up Modal (Hidden by default) -->
        <div id="successModal" class="success-modal hidden">
            <div class="modal-content">
                <div class="success-icon-container">
                    <i class="fas fa-check-circle success-icon"></i>
                </div>
                <h2>Payment Successful!</h2>
                <p id="modalMessage">Your order has been placed.</p>
                <div class="redirect-timer">Redirecting in <span id="timer">5</span> seconds...</div>
            </div>
        </div>
    </div>
    
        <!-- Link the external JavaScript -->
        <script src="../script/payment.js"></script>
        
    </body>
    </html>
