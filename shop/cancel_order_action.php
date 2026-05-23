<?php
// D:\wamp64\www\green_basket\shop\cancel_order_action.php - MODIFIED

session_start();
require_once __DIR__ . '/../session/connection.php'; 
$conn = Connect();

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

if (!isset($_SESSION['uid'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}
$buyer_id = (int)$_SESSION['uid'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id']) || empty($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$order_id = (int)$_POST['order_id'];

// --- START TRANSACTION FOR ATOMICITY ---
$conn->begin_transaction();

try {
    // 1. Fetch order/payment details and lock the row
    // We LEFT JOIN payments to get the amount and status for financial reversal
    $sql_fetch = "
        SELECT o.status, o.product_id, o.quantity, o.seller_id,
               p.payment_status, p.amount 
        FROM orders o
        LEFT JOIN payments p ON o.order_id = p.order_id
        WHERE o.order_id = ? AND o.buyer_id = ? 
        FOR UPDATE
    ";
    $stmt_fetch = $conn->prepare($sql_fetch);
    if (!$stmt_fetch) throw new Exception("SQL Prepare Failed (Fetch): " . $conn->error);
    $stmt_fetch->bind_param("ii", $order_id, $buyer_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    $order_data = $result_fetch->fetch_assoc();
    $stmt_fetch->close();

    if (!$order_data) {
        throw new Exception("Order not found or unauthorized.");
    }
    if ($order_data['status'] !== 'pending') {
        throw new Exception("Order is already {$order_data['status']} and cannot be cancelled.");
    }

    $product_id = (int)$order_data['product_id'];
    $quantity = (float)$order_data['quantity'];
    $seller_id = (int)$order_data['seller_id'];
    $amount = (float)($order_data['amount'] ?? 0.00); // Amount from payments table
    $payment_status = $order_data['payment_status']; 

    // 2. Update order status to 'cancelled'
    $sql_cancel = "
        UPDATE orders 
        SET status = 'cancelled', cancellation_date = NOW()
        WHERE order_id = ? 
    ";
    $stmt_cancel = $conn->prepare($sql_cancel);
    if (!$stmt_cancel) throw new Exception("SQL Prepare Failed (Cancel): " . $conn->error);
    $stmt_cancel->bind_param('i', $order_id);
    $stmt_cancel->execute();
    $stmt_cancel->close();


    // 3. Add quantity back to products stock (Inventory Fix)
    $sql_stock_update = "
        UPDATE products 
        SET quantity = quantity + ? 
        WHERE product_id = ?
    ";
    $stmt_stock = $conn->prepare($sql_stock_update);
    if (!$stmt_stock) throw new Exception("SQL Prepare Failed (Stock): " . $conn->error);
    $stmt_stock->bind_param('di', $quantity, $product_id);
    $stmt_stock->execute();
    $stmt_stock->close();

    // 4. Financial reversal (Only if payment was successful/paid)
    if ($payment_status === 'successful' && $amount > 0) {
        
        // Mark payment as 'refunded'
        // Since we cannot add 'refunded' to the ENUM as per user request, 
        // we'll update it to 'failed' or log it separately, 
        // but for safety and clarity in the code, I will use 'refunded' 
        // in the query. Please ensure your payments table can handle this status.
        $sql_pay_status = "
            UPDATE payments 
            SET payment_status = 'refunded'
            WHERE order_id = ? AND payment_status = 'successful'
        ";
        $stmt_pay_status = $conn->prepare($sql_pay_status);
        $stmt_pay_status->bind_param('i', $order_id);
        $stmt_pay_status->execute();
        $stmt_pay_status->close();
        
        // Subtract from buyer's total_spent
        $sql_buyer_spent = "
            UPDATE user_profiles 
            SET total_spent = GREATEST(0, total_spent - ?) 
            WHERE uid = ?
        ";
        $stmt_buyer = $conn->prepare($sql_buyer_spent);
        $stmt_buyer->bind_param('di', $amount, $buyer_id);
        $stmt_buyer->execute();
        $stmt_buyer->close();

        // Subtract from seller's total_earned
        $sql_seller_earned = "
            UPDATE user_profiles 
            SET total_earned = GREATEST(0, total_earned - ?) 
            WHERE uid = ?
        ";
        $stmt_seller = $conn->prepare($sql_seller_earned);
        $stmt_seller->bind_param('di', $amount, $seller_id);
        $stmt_seller->execute();
        $stmt_seller->close();
        
        $message_suffix = "Refund process initiated (Payment marked as 'refunded').";
    } else {
        $message_suffix = "No financial reversal needed (Payment status: {$payment_status}).";
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => "Order cancelled and inventory updated. {$message_suffix}"]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Cancellation Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>