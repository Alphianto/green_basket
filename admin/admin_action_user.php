<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

// Check if admin is logged in
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Get AJAX data
$user_id = $_POST['user_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$user_id || !$action) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}

// Determine new status
$new_status = '';
if ($action === 'ban') {
    $new_status = 'banned';
} elseif ($action === 'unban') {
    $new_status = 'active';
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit();
}

// Update the user
$stmt = $conn->prepare("UPDATE users SET status=? WHERE uid=?");
$stmt->bind_param("si", $new_status, $user_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'new_status' => $new_status]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

$stmt->close();
$conn->close();
?>
