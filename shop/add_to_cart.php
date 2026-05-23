<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../session/connection.php'; 
$conn = Connect();

// --- CHECK USER SESSION & DB CONNECTION ---
if (!isset($_SESSION['uid']) || !$conn) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in or database connection failed.']);
    exit;
}

$user_id = $_SESSION['uid'];

// --- READ POST DATA ---
$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? null;
$quantity = $data['quantity'] ?? null;

// --- VALIDATE INPUT ---
if (!is_numeric($product_id) || !is_numeric($quantity) || $quantity <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity.']);
    exit;
}

// --- CHECK IF PRODUCT ALREADY IN CART ---
$existing_item = null;
$check_query = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
if ($stmt = $conn->prepare($check_query)) {
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $existing_item = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else {
    error_log("Cart check prepare error: " . $conn->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal database error.']);
    exit;
}

// --- INSERT OR UPDATE CART ---
try {
    if ($existing_item) {
        // Update quantity
        $new_quantity = $existing_item['quantity'] + $quantity;
        $update_query = "UPDATE cart SET quantity = ?, added_at = NOW() WHERE cart_id = ?";
        if ($stmt = $conn->prepare($update_query)) {
            $stmt->bind_param("di", $new_quantity, $existing_item['cart_id']);
            $stmt->execute();
            $stmt->close();
            
            echo json_encode([
                'success' => true,
                'message' => 'Product quantity updated in your basket!',
                'action' => 'updated',
                'quantity' => $new_quantity
            ]);
            exit;
        }
    } else {
        // Insert new cart item
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity, added_at) VALUES (?, ?, ?, NOW())";
        if ($stmt = $conn->prepare($insert_query)) {
            $stmt->bind_param("iid", $user_id, $product_id, $quantity);
            $stmt->execute();
            $stmt->close();

            echo json_encode([
                'success' => true,
                'message' => 'Product successfully added to your basket!',
                'action' => 'inserted',
                'quantity' => $quantity
            ]);
            exit;
        }
    }

    // If neither insert nor update executed
    throw new Exception("Failed to insert/update cart item.");
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Cart DB Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
    exit;
}
