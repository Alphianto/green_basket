<?php
require_once __DIR__ . '/connection.php';
session_start();

if (!isset($_SESSION['uid'])) {
    die("not_logged_in");
}

$user_id = $_SESSION['uid'];
$product_id = $_POST['product_id'] ?? null;

if (!$product_id) {
    die("missing_data");
}

$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
echo $stmt->execute() ? "success" : "error";
$stmt->close();
$conn->close();
