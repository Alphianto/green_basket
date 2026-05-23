<?php
// account/edit_profile.php
session_start();
require_once __DIR__ . '/../services/user_service.php';

// Ensure logged in
if (!isset($_SESSION['uid'])) {
    $_SESSION['error_message'] = "You must be logged in to edit your profile.";
    header("Location: ../account/login.php");
    exit();
}

$uid = (int)$_SESSION['uid'];
$profile = getUserProfile($uid);
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';

// Clear session messages after displaying
unset($_SESSION['message']);
unset($_SESSION['message_type']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize form data
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email'     => trim($_POST['email'] ?? ''),
        'username'  => trim($_POST['username'] ?? ''),
        'phone'     => trim($_POST['phone'] ?? ''),
        'gender'    => trim($_POST['gender'] ?? ''),
        'address'   => trim($_POST['address'] ?? ''),
        'city'      => trim($_POST['city'] ?? ''),
        'pincode'   => trim($_POST['pincode'] ?? ''),
    ];

    $errors = []; // Initialize an array to collect professional error messages
    
    // --- START: NEW VALIDATION BLOCK ---

    // 1. Basic Required Validation 
    if (empty($data['full_name'])) {
        $errors[] = "Full Name is required and cannot be empty.";
    }
    if (empty($data['username'])) {
        $errors[] = "Username is required.";
    }

    // 2. Email Validation: proper check for '@' and '.'
    if (empty($data['email'])) {
        $errors[] = "Email Address is required.";
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "The **Email Address** format is invalid. Please ensure it contains an '@' and a valid domain.";
    }

    // 3. Username Uniqueness Validation (Checks if username has changed AND is unique)
    if (!empty($data['username']) && strcasecmp($data['username'], $profile['username'] ?? '') !== 0) {
        if (!isUsernameUnique($data['username'], $uid)) {
            $errors[] = "The **Username** '<strong>" . htmlspecialchars($data['username']) . "</strong>' is already taken. Please choose a unique username.";
        }
    }

    // 4. Phone Number Validation: 10 digits, cannot start with 0
    if (!empty($data['phone'])) {
        // Remove all non-digit characters for strict length/zero check
        $sanitized_phone = preg_replace('/\D/', '', $data['phone']); 
        
        if (strlen($sanitized_phone) !== 10) {
            $errors[] = "**Phone Number** must be exactly 10 digits.";
        }
        
        if (str_starts_with($sanitized_phone, '0')) {
            $errors[] = "**Phone Number** cannot start with zero (0).";
        }
    }
    
    // 5. Pincode Validation: 6 digits, must start with 6 (Kerala specific)
    if (!empty($data['pincode'])) {
        // Regex: ^6\d{5}$ -> Starts with 6, followed by 5 digits (total 6 digits)
        if (!preg_match('/^6\d{5}$/', $data['pincode'])) {
            $errors[] = "**Pincode** must be a 6-digit number and must start with '6', adhering to the Kerala postal standard.";
        }
    }

    // --- END: NEW VALIDATION BLOCK ---

    if (!empty($errors)) {
        // If there are validation errors, format them professionally with an HTML list
        $_SESSION['message'] = "<strong>We encountered the following issues:</strong><ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
        $_SESSION['message_type'] = "error";
    } else {
        // All validation passed. Proceed with change detection and update.
        // --- START: Change Detection Logic (Existing) ---
        $has_changed = false;
        $fields_to_check = [
            'full_name', 'email', 'username', 'phone', 'gender', 
            'address', 'city', 'pincode'
        ];
        
        foreach ($fields_to_check as $key) {
            // Normalize values: trim the existing DB value for consistent comparison
            $current_value = trim((string)($profile[$key] ?? ''));
            $new_value = $data[$key]; 
            
            // Compare values case-insensitively
            if (strcasecmp($current_value, $new_value) !== 0) {
                $has_changed = true;
                break; 
            }
        }
        
        // --- END: Change Detection Logic ---
        
        if (!$has_changed) {
            // Case 1: No changes detected
            $_SESSION['message'] = "No changes detected. Your profile data is already up to date.";
            $_SESSION['message_type'] = "info"; 
        } elseif (updateProfile($uid, $data)) {
            // Case 2: Changes detected and update successful
            $_SESSION['message'] = "Profile updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            // Case 3: Changes detected but update failed
            $_SESSION['message'] = "Failed to update profile. Please try again later.";
            $_SESSION['message_type'] = "error";
        }
    }
    
    // Final redirect for Post-Redirect-Get pattern (handles all cases: error, success, no change)
    header("Location: edit_profile.php");
    exit();
}

// Define avatar path for display
$avatarFilename = $profile['avatar'] ?? 'default_avatar.png';
$avatarPath = "img/" . htmlspecialchars($avatarFilename);

// Define avatar path for display
$avatarFilename = $profile['avatar'] ?? 'default_avatar.png';
$avatarPath = "img/" . htmlspecialchars($avatarFilename);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - GreenBasket</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <link rel="stylesheet" href="../style/style.css"> 
    <link rel="stylesheet" href="../style/edit_profile.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&family=Lemon&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../layout/headweb.php';?>
<div id="messageBox" style="display: none;"></div>

<div class="edit-profile-container">
    <h1 class="page-heading">Edit Profile</h1>

    <div class="form-card">
        <a href="/account/profile.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Profile</a>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <form id="profileForm" method="POST" action="edit_profile.php">
            
            <div class="avatar-section">
                <div class="avatar-container">
                    <img id="profileAvatar" class="profile-avatar" 
                        src="<?= $avatarPath ?>" 
                        alt="Profile Avatar"
                        onerror="this.onerror=null; this.src='img/default_avatar.png';">
                    <label for="avatarUpload" class="edit-avatar-btn" title="Change Profile Picture">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
                </div>
                
                <div class="avatar-info-actions">
                    <p class="role-display">Role: <strong><?= ucwords(htmlspecialchars($profile['role'])) ?></strong> (Not editable)</p>
                    
                    <button type="button" id="uploadAvatarBtn" class="btn-primary" disabled><i class="fas fa-upload"></i> Upload Avatar</button>
                </div>
            </div>
            <h2>Personal Details</h2>
            <div class="form-grid">
                
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($profile['full_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($profile['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($profile['username']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($profile['phone']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="Male" <?= strcasecmp($profile['gender'] ?? '', 'Male') === 0 ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= strcasecmp($profile['gender'] ?? '', 'Female') === 0 ? 'selected' : '' ?>>Female</option>
                        <option value="Other" <?= strcasecmp($profile['gender'] ?? '', 'Other') === 0 ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

            </div>

            <h2>Address Details</h2>
            <div class="form-grid">
                
                <div class="form-group full-width">
                    <label for="address">Address Line</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($profile['address']) ?>">
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?= htmlspecialchars($profile['city']) ?>">
                </div>

                <div class="form-group">
                    <label for="pincode">Pincode</label>
                    <input type="text" id="pincode" name="pincode" value="<?= htmlspecialchars($profile['pincode']) ?>">
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                <a href="/account/profile.php" class="btn-secondary"><i class="fas fa-times-circle"></i> Cancel</a>
            </div>
            
        </form>
    </div>
    
</div>
<?php include __DIR__ . '/../layout/footer.php';?>

<script src="../script/edit_profile.js"></script>
</body>
</html>