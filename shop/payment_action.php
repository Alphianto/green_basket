<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

$response = ['success' => false, 'message' => 'Unknown error occurred.'];

// Toggle this to true to force DB price * quantity validation (safer)
define('STRICT_VALIDATE_DB_TOTAL', false);

try {
    if (!isset($_SESSION['uid'])) {
        throw new Exception('Please log in to proceed.');
    }
    $buyer_id = (int)$_SESSION['uid'];

    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_FLOAT);
    $price_per_unit = filter_input(INPUT_POST, 'price_per_unit', FILTER_VALIDATE_FLOAT);
    $final_amount = filter_input(INPUT_POST, 'final_amount', FILTER_VALIDATE_FLOAT);
    $payment_method = filter_input(INPUT_POST, 'payment_method', FILTER_SANITIZE_STRING);
    $payment_detail = filter_input(INPUT_POST, 'payment_detail', FILTER_SANITIZE_STRING);

    if (!$product_id || !$quantity || $quantity <= 0 || !is_numeric($final_amount) || !$payment_method) {
        throw new Exception('Invalid order data.');
    }

    // Fetch product info
    $stmt = $conn->prepare("SELECT seller_id, price, quantity AS stock FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) throw new Exception('Product not found.');
    $seller_id = (int)$product['seller_id'];
    $stock = (float)$product['stock'];
    if ($quantity > $stock) throw new Exception('Insufficient stock.');

    // Validate total: default behavior trusts frontend-provided final_amount
    if (STRICT_VALIDATE_DB_TOTAL) {
        $expected_total = round($product['price'] * $quantity, 2);
        if ($payment_method === 'cash') $expected_total += 10.00;
        if (abs($expected_total - $final_amount) > 0.01) {
            throw new Exception('Price mismatch detected. Transaction aborted.');
        }
    } else {
        // Optionally, we can sanity-check small differences (e.g., rounding)
        if ($final_amount <= 0) throw new Exception('Invalid final amount.');
    }

    $payment_status = ($payment_method === 'cash') ? 'pending' : 'successful';
    $order_status = 'pending';

    $conn->begin_transaction();

    // Insert order (price column = per-unit price)
    // We'll use the posted price_per_unit (derived on payment.php) — fallback to final_amount/quantity if missing.
    if (!$price_per_unit || $price_per_unit <= 0) {
        $price_per_unit = ($quantity > 0) ? round($final_amount / $quantity, 2) : $final_amount;
    }
    $stmt = $conn->prepare("
        INSERT INTO orders (buyer_id, seller_id, product_id, quantity, price, status)
        VALUES (?,?,?,?,?,?)
    ");
    $stmt->bind_param("iiidds", $buyer_id, $seller_id, $product_id, $quantity, $final_amount, $order_status);
    $stmt->execute();
    $order_id = $conn->insert_id;
    $stmt->close();

    if (!$order_id) throw new Exception("Order insertion failed.");

    // Insert payment using final_amount (total)
    $stmt = $conn->prepare("
        INSERT INTO payments (order_id, buyer_id, seller_id, amount, payment_method, payment_status, payment_date)
        VALUES (?,?,?,?,?,?,NOW())
    ");
    $stmt->bind_param("iiidss", $order_id, $buyer_id, $seller_id, $final_amount, $payment_method, $payment_status);
    $stmt->execute();
    $stmt->close();

    // Update stock
    $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE product_id = ?");
    $stmt->bind_param("di", $quantity, $product_id);
    $stmt->execute();
    $stmt->close();

    // Update user_profiles if payment successful
    if ($payment_status === 'successful') {
        $stmt1 = $conn->prepare("UPDATE user_profiles SET total_spent = total_spent + ? WHERE uid = ?");
        $stmt1->bind_param("di", $final_amount, $buyer_id);
        $stmt1->execute();
        $stmt1->close();

        $stmt2 = $conn->prepare("UPDATE user_profiles SET total_earned = total_earned + ? WHERE uid = ?");
        $stmt2->bind_param("di", $final_amount, $seller_id);
        $stmt2->execute();
        $stmt2->close();
    }

    $conn->commit();

    $response['success'] = true;
    $response['message'] = ($payment_method === 'cash')
        ? 'Cash on Delivery order placed successfully. Payment pending.'
        : 'Payment successful. Thank you for your purchase!';
    $response['order_id'] = $order_id;
} catch (Exception $e) {
    if ($conn && $conn->errno === 0) $conn->rollback();
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

$conn->close();
echo json_encode($response);
exit;
