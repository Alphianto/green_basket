<?php
// services/user_service.php
require_once __DIR__ . '/../session/connection.php';

/**
 * Fetches combined user and profile data for a given UID.
 * @param int $uid The user ID.
 * @return array The merged user and profile data.
 */
function getUserProfile(int $uid): array {
    $conn = Connect();

    $sql = "
        SELECT 
            u.username, u.phone, u.role, u.status, u.created_at,
            up.full_name, up.email, up.gender, up.address, up.city, 
            up.pincode, up.last_updated, up.avatar
        FROM users u 
        LEFT JOIN user_profiles up ON u.uid = up.uid 
        WHERE u.uid = ?
    ";

    $defaultProfile = [
        "username" => "Guest",
        "phone" => "Not set",
        "role" => "buyer",
        "status" => "active",
        "created_at" => "N/A",
        "full_name" => "Not set",
        "email" => "Not set",
        "gender" => "Not set",
        "address" => "Not set",
        "city" => "Not set",
        "pincode" => "Not set",
        "last_updated" => "N/A",
        "avatar" => "default_avatar.png"
    ];

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        $conn->close();
        return array_merge($defaultProfile, $row);
    }

    $stmt->close();
    $conn->close();
    return $defaultProfile;
}

/**
 * Updates a user's profile details.
 * @param int $uid The user ID.
 * @param array $data The profile data array.
 * @return bool True on success, false on failure.
 */
function updateProfile(int $uid, array $data): bool {
    $conn = Connect();
    
    // Default data structure for user_profiles (handles partial data gracefully)
    $full_name = $data['full_name'] ?? null;
    $email = $data['email'] ?? null;
    $gender = $data['gender'] ?? null;
    $address = $data['address'] ?? null;
    $city = $data['city'] ?? null;
    $pincode = $data['pincode'] ?? null;
    $last_updated = date('Y-m-d H:i:s');
    
    // Update user table for username and phone
    $sql_user = "UPDATE users SET username = ?, phone = ? WHERE uid = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("ssi", $data['username'], $data['phone'], $uid);
    $stmt_user->execute();
    $stmt_user->close();

    // Insert or update user_profiles table
    $sql_profile = "
        INSERT INTO user_profiles (uid, full_name, email, gender, address, city, pincode, last_updated)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            full_name = VALUES(full_name),
            email = VALUES(email),
            gender = VALUES(gender),
            address = VALUES(address),
            city = VALUES(city),
            pincode = VALUES(pincode),
            last_updated = VALUES(last_updated)
    ";

    $stmt_profile = $conn->prepare($sql_profile);
    if (!$stmt_profile) {
        error_log("Profile DB Prepare Error for UID {$uid}: " . $conn->error);
        $conn->close();
        return false;
    }
    
    $stmt_profile->bind_param("isssssss", $uid, $full_name, $email, $gender, $address, $city, $pincode, $last_updated);
    $success = $stmt_profile->execute();
    
    $stmt_profile->close();
    $conn->close();
    
    return $success;
}

/**
 * Handles the upload of the new avatar file and updates the database record.
 * @param int $uid The user ID.
 * @param array $file The $_FILES array entry for the uploaded file.
 * @return string|false The new file name on success, or false on failure.
 */
function updateAvatar(int $uid, array $file): string|false {
    // Basic file validation
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if ($file['size'] > $maxFileSize) {
        error_log("Avatar Upload Error for UID {$uid}: File size too large.");
        return false;
    }
    if (!in_array($file['type'], $allowedMimes)) {
        error_log("Avatar Upload Error for UID {$uid}: Invalid file type: {$file['type']}");
        return false;
    }
    
    // --- FIX: Correcting the upload directory path to /account/img/ ---
    // This goes one directory up from /services/ to the project root, 
    // then into /account/img/.
    $uploadDir = dirname(__DIR__) . '/account/img/';

    // CRITICAL: Check if directory exists and is writable.
    if (!is_dir($uploadDir)) {
        error_log("Avatar Upload Error for UID {$uid}: Destination directory does not exist at " . $uploadDir);
        return false;
    }
    if (!is_writable($uploadDir)) {
        error_log("Avatar Upload Error for UID {$uid}: Destination directory is NOT writable. Check folder permissions (e.g., chmod 777). Path: " . $uploadDir);
        return false; 
    }
    // ---------------------------------------------------------------------

    // Set destination directory and generate a unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'avatar_' . $uid . '_' . time() . '.' . $fileExtension;
    $targetPath = $uploadDir . $newFileName;

    // Move the uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        error_log("Avatar Upload Error for UID {$uid}: Failed to move file. Potential permission issue even after writability check. Details: {$file['error']}");
        return false;
    }

    // Update the database (similar INSERT...ON DUPLICATE KEY UPDATE logic)
    $conn = Connect();
    $last_updated = date('Y-m-d H:i:s');
    $sql = "
        INSERT INTO user_profiles (uid, avatar, last_updated)
        VALUES (?, ?, ?)    
        ON DUPLICATE KEY UPDATE 
            avatar = VALUES(avatar),
            last_updated = VALUES(last_updated)
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Avatar DB Prepare Error for UID {$uid}: " . $conn->error);
        @unlink($targetPath); // Clean up file just uploaded
        $conn->close();
        return false;
    }

    $stmt->bind_param("iss", $uid, $newFileName, $last_updated);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();

    if (!$success) {
        error_log("Avatar DB Execute Error for UID {$uid}: " . $stmt->error);
        @unlink($targetPath); // Clean up file if DB update fails
        return false;
    }

    return $newFileName;
}

/**
 * Fetches the total amount spent by the user.
 */
function getTotalSpend(int $uid): string {
    $conn = Connect();
    if (!$conn) {
        error_log("❌ DB connection failed in getTotalSpend()");
        return "₹1.00";
    }

    $sql = "SELECT total_spent FROM user_profiles WHERE uid = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("❌ SQL prepare error in getTotalSpend(): " . $conn->error);
        $conn->close();
        return "₹2.00";
    }

    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->bind_result($totalSpend);
    $stmt->fetch();

    $stmt->close();
    $conn->close();

    $totalSpend = $totalSpend ?? 0.00;
    return "₹" . number_format((float)$totalSpend, 2);
}

/**
 * Fetches the total amount earned by the user.
 */
function getTotalEarned(int $uid): string {
    $conn = Connect();
    if (!$conn) {
        error_log("❌ DB connection failed in getTotalEarned()");
        return "₹1.00";
    }

    $sql = "SELECT total_earned FROM user_profiles WHERE uid = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("❌ SQL prepare error in getTotalEarned(): " . $conn->error);
        $conn->close();
        return "₹0.00";
    }

    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->bind_result($totalEarned);
    $stmt->fetch();

    $stmt->close();
    $conn->close();

    $totalEarned = $totalEarned ?? 0.00;
    return "₹" . number_format((float)$totalEarned, 2);
}
/**
 * Fetches the total number of items bought by a user.
 * @param int $uid The user ID.
 * @return int Total quantity of items purchased.
 */
function getTotalItemsBought(int $uid): int {
    $conn = Connect();
    if (!$conn) {
        error_log("❌ DB connection failed in getTotalItemsBought()");
        return 0;
    }

    $sql = "SELECT COALESCE(SUM(quantity), 0) AS total_items 
            FROM orders 
            WHERE buyer_id = ? AND status = 'completed'";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("❌ SQL prepare error in getTotalItemsBought(): " . $conn->error);
        $conn->close();
        return 0;
    }

    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->bind_result($totalItems);
    $stmt->fetch();

    $stmt->close();
    $conn->close();

    return (int)($totalItems ?? 0);
}

/**
 * Updates the user's password.
 * @param int $uid The user ID.
 * @param string $newPassword The new password (must be hashed).
 * @return bool True on success, false otherwise.
 */
function updatePassword(int $uid, string $newPassword): bool {
    $conn = Connect();
    
    // Hash the password for security
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); 
    
    $sql = "UPDATE users SET password = ? WHERE uid = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        // Log error in a real app
        $conn->close();
        return false;
    }
    
    $stmt->bind_param("si", $hashedPassword, $uid);
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

/**
 * Checks if a username is unique, excluding the current user's ID.
 * @param string $username The username to check.
 * @param int $current_uid The user ID to exclude from the check (the current user).
 * @return bool True if the username is unique, false otherwise.
 */
function isUsernameUnique(string $username, int $current_uid): bool {
    $conn = Connect();
    if (!$conn) {
        error_log("❌ DB connection failed in isUsernameUnique()");
        return true; // Assume unique if DB fails to prevent blocking user
    }

    // Check for the username, excluding the current user's UID.
    $sql = "SELECT COUNT(*) FROM users WHERE username = ? AND uid != ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("❌ SQL prepare error in isUsernameUnique(): " . $conn->error);
        $conn->close();
        return true; 
    }

    $stmt->bind_param("si", $username, $current_uid);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    
    $stmt->close();
    $conn->close();

    return $count === 0; // If count is 0, the username is unique.
}


?>