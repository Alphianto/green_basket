<?php
// api/upload_avatar.php
session_start();
require_once __DIR__ . '/../services/user_service.php';

header('Content-Type: application/json');

// Check if the user is authenticated
if (!isset($_SESSION['uid'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit();
}

// Check if a file was actually uploaded
if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error occurred.']);
    exit();
}

$uid = (int)$_SESSION['uid'];
$file = $_FILES['avatar'];

// Call the service function to handle the file move and database update
$newFileName = updateAvatar($uid, $file);

if ($newFileName) {
    // FIX: Construct the full new URL path for the client to use, pointing to the new folder
    $newAvatarUrl = "../account/img/" . $newFileName;
    http_response_code(200);
    echo json_encode([
        'success' => true, 
        'message' => 'Avatar updated successfully.', 
        'new_avatar_url' => $newAvatarUrl
    ]);
} else {
    // Failure in file move, database update, or validation (handled inside updateAvatar)
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to process file upload or update database.']);
}
?>