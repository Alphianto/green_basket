<?php
// api/check_username.php
session_start();
require_once __DIR__ . '/../services/user_service.php';

header('Content-Type: application/json');

// Ensure the user is logged in
if (!isset($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['unique' => false, 'message' => 'Authentication required.']);
    exit();
}

$uid = (int)$_SESSION['uid'];
$newUsername = trim($_POST['username'] ?? '');

// Fetch the current username from the database/session to see if it actually changed
$profile = getUserProfile($uid);
$currentUsername = $profile['username'] ?? '';

if (empty($newUsername)) {
    http_response_code(400);
    echo json_encode(['unique' => false, 'message' => 'Username cannot be empty.']);
    exit();
}

// 1. Check if the username is the same as the current one (if not changed, it's valid)
if (strcasecmp($newUsername, $currentUsername) === 0) {
    echo json_encode(['unique' => true, 'message' => 'Username is your current one.']);
    exit();
}

// 2. Perform uniqueness check using the DB function
if (isUsernameUnique($newUsername, $uid)) {
    echo json_encode(['unique' => true, 'message' => 'Username is available!']);
} else {
    echo json_encode(['unique' => false, 'message' => 'This username is already taken.']);
}

?>