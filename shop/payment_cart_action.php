<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../session/connection.php';

$conn = Connect();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

// --- Verify user login ---
if (!isset($_SESSION['uid'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to continue.']);
    exit();
}
$buyer_id = (int)$_SESSION['uid'];

// --- Get payment data ---
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['method'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}
$method = $data['method'];

// --- Fetch cart items for this user ---
$sql = "SELECT c.cart_id, c.product_id, c.quantity, p.price, p.seller_id 
        FROM cart c
        JOIN products p ON c.product_id = p.product_id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'SQL Error: ' . $conn->error]);
    exit();
}
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
    exit();
}

$orders_success = true;
$order_count = 0;

// --- Start transaction ---
$conn->begin_transaction();

try {
    while ($row = $result->fetch_assoc()) {
        $product_id = $row['product_id'];
        $seller_id = $row['seller_id'];
        $quantity = $row['quantity'];
        $base_price = $row['price'] * $quantity;

        // Add ₹10 for COD
        $extra_charge = ($method === 'cod') ? (10 * (float)$quantity) : 0;
        $total_price = $base_price + $extra_charge;

        // === Insert into orders ===
        $order_sql = "INSERT INTO orders (buyer_id, seller_id, product_id, quantity, total_amount, status, order_date)
                      VALUES (?, ?, ?, ?, ?, 'placed', NOW())";
        $stmt_order = $conn->prepare($order_sql);
        $stmt_order->bind_param("iiiid", $buyer_id, $seller_id, $product_id, $quantity, $total_price);
        $stmt_order->execute();
        $order_id = $stmt_order->insert_id;
        $stmt_order->close();

        // === Insert into payments ===
        $payment_sql = "INSERT INTO payments (user_id, order_id, amount, payment_method, payment_status, payment_date)
                        VALUES (?, ?, ?, ?, 'successful', NOW())";
        $stmt_payment = $conn->prepare($payment_sql);
        $stmt_payment->bind_param("iids", $buyer_id, $order_id, $total_price, $method);
        $stmt_payment->execute();
        $stmt_payment->close();

        // === Update user_profiles: total_spend for buyer ===
        $update_buyer = $conn->prepare("UPDATE user_profiles SET total_spend = total_spend + ? WHERE uid = ?");
        $update_buyer->bind_param("di", $total_price, $buyer_id);
        $update_buyer->execute();
        $update_buyer->close();

        // === Update user_profiles: total_earned for seller ===
        $update_seller = $conn->prepare("UPDATE user_profiles SET total_earned = total_earned + ? WHERE uid = ?");
        $update_seller->bind_param("di", $base_price, $seller_id);
        $update_seller->execute();
        $update_seller->close();

        $order_count++;
    }

    // === Delete cart items for this user ===
    $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $delete_cart->bind_param("i", $buyer_id);
    $delete_cart->execute();
    $delete_cart->close();

    // === Clear only checkout sessions ===
    unset($_SESSION['checkout_start'], $_SESSION['checkout_products'], $_SESSION['checkout_total']);

    // === Commit transaction ===
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Payment successful! {$order_count} orders placed.",
        'redirect' => '../shop/orders.php'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Transaction failed: ' . $e->getMessage()
    ]);
}
?>
