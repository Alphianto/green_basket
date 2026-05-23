<?php
session_start();
require_once __DIR__ . '/../services/user_service.php';

// Ensure logged in
if (!isset($_SESSION['uid'])) {
    $_SESSION['error_message'] = "You must be logged in to view your profile.";
    header("Location: ../account/login.php");
    exit();
}

$uid = (int)$_SESSION['uid'];
$profile = getUserProfile($uid);

$is_seller = (strtolower($profile['role']) === 'seller');

// Fetch financial data (simulated)
$totalSpend = getTotalSpend($uid);
$totalEarned = getTotalEarned($uid);
$totalItemsBought = getTotalItemsBought($uid);

// Define avatar path (assuming avatars are stored in /style/imgs/avatars/)
$avatarPath = "img/" . htmlspecialchars($profile['avatar']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - GreenBasket</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <link rel="stylesheet" href="../style/style.css"> 
    <link rel="stylesheet" href="../style/profile.css">
    <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&family=Lemon&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../layout/headweb.php';?>

<div class="profile-page-container">
    <h1 class="page-heading">My Account & Profile</h1>

    <div class="profile-card-layout">
        
        <!-- Profile Summary and Avatar (Left Column/Top Section) -->
        <aside class="profile-sidebar">
            <div class="avatar-container">
                <img id="profileAvatar" src="<?= $avatarPath ?>" alt="User Avatar" onerror="this.onerror=null; this.src='img/default_avatar.png';" class="profile-avatar">
                <button class="edit-avatar-btn" title="Change Profile Picture"><i class="fas fa-camera"></i></button>
                <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
            </div>

            <h2 class="user-display-name"><?= htmlspecialchars($profile['username']) ?></h2>
            <p class="user-role-tag role-<?= htmlspecialchars(strtolower($profile['role'])) ?>">
                <i class="fas <?= $is_seller ? 'fa-store' : 'fa-shopping-basket' ?>"></i> 
                <?= ucwords(htmlspecialchars($profile['role'])) ?>
            </p>
            <p class="user-joined">Joined: <span><?= htmlspecialchars($profile['created_at']) ?></span></p>

            <div class="quick-stats-card">
                <h3>Financial Summary</h3>
                
                <?php if ($is_seller): ?>
                    <!-- Seller Stats -->
                    <div class="stat-item">
                        <span class="stat-label">Total Earned:</span>
                        <span class="stat-value earned"><?= $totalEarned ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Spent:</span>
                        <span class="stat-value spent"><?= $totalSpend ?></span>
                    </div>
                <?php else: ?>
                    <!-- Buyer Stats -->
                    <div class="stat-item">
                        <span class="stat-label">Total Spent:</span>
                        <span class="stat-value spent"><?= $totalSpend ?></span>
                    </div>
                    <!-- New Item: Total Items Bought -->
                    <div class="stat-item">
                        <span class="stat-label">Items Bought:</span>
                        <span class="stat-value count"><?= $totalItemsBought ?></span>
                    </div>
                <?php endif; ?>

                <div class="stat-action-link">
                    <!-- Conditionally remove the "View Full Transaction History" link for buyers. -->
                    <?php if ($is_seller): ?>
                        <a href="/account/transactions.php" class="theme-link">View Full Transaction History &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
        </aside>

        <!-- Main Profile Details (Right Column/Bottom Section) -->
        <section class="profile-details">
            <h2>Personal Information</h2>
            
            <div class="detail-grid">
                <!-- User Profile Data -->
                <div class="detail-item"><strong>Full Name:</strong> <span><?= htmlspecialchars($profile['full_name']) ?></span></div>
                <div class="detail-item"><strong>Email:</strong> <span><?= htmlspecialchars($profile['email']) ?></span></div>
                <div class="detail-item"><strong>Phone:</strong> <span><?= htmlspecialchars($profile['phone']) ?></span></div>
                <div class="detail-item"><strong>Gender:</strong> <span><?= htmlspecialchars($profile['gender']) ?></span></div>
                
                <!-- User Table Data -->
                <div class="detail-item"><strong>Account Status:</strong> <span class="status-<?= htmlspecialchars(strtolower($profile['status'])) ?>"><?= ucwords(htmlspecialchars($profile['status'])) ?></span></div>
                <div class="detail-item"><strong>Last Updated:</strong> <span><?= htmlspecialchars($profile['last_updated']) ?></span></div>

                <!-- Address Details -->
                <div class="detail-item detail-full-width"><strong>Address:</strong> <span><?= htmlspecialchars($profile['address']) ?></span></div>
                <div class="detail-item"><strong>City:</strong> <span><?= htmlspecialchars($profile['city']) ?></span></div>
                <div class="detail-item"><strong>Pincode:</strong> <span><?= htmlspecialchars($profile['pincode']) ?></span></div>

            </div>
            
            <div class="profile-actions">
                <a href="/account/edit_profile.php" class="btn-primary"><i class="fas fa-user-edit"></i> Edit Profile</a>
                <a href="/account/change_password.php" class="btn-secondary"><i class="fas fa-lock"></i> Change Password</a>
            </div>
            
        </section>

    </div>

</div>

<?php include __DIR__ . '/../layout/footer.php';?>
<script src="../script/profile.js"></script>
</body>
</html>
