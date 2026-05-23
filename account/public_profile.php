<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB connection failed.");

// GET METHOD INCLUDED: Get the user ID from the URL (e.g., public_profile.php?uid=101)
// It defaults to user ID 101 for safe testing based on your sample data.
$profile_uid = (int)($_GET['uid'] ?? ''); 

// --- 1. Fetch User Profile Details ---
// Role is fetched from the 'users' table (u.role)
$profile_query = "
    SELECT 
        up.full_name, up.address, up.city, up.pincode, up.gender, u.phone, up.avatar, u.role, up.email
    FROM users u
    JOIN user_profiles up ON u.uid = up.uid
    WHERE u.uid = ?
";
$stmt = $conn->prepare($profile_query);
if (!$stmt) { die("SQL Error fetching profile: " . $conn->error); }
$stmt->bind_param("i", $profile_uid);
$stmt->execute();
$profile_result = $stmt->get_result();
$user_profile = $profile_result->fetch_assoc();
$stmt->close();

if (!$user_profile) {
    die("User profile not found.");
}

// **CORRECTED LOGIC: Check u.role for 'seller' or 'admin'**
$is_seller = (strtolower($user_profile['role']) === 'seller' || strtolower($user_profile['role']) === 'admin');
$seller_rating = 0.0; // Default rating
$recent_products = [];
$review_count = 0;

// --- 2. Fetch Seller-Specific Data (if applicable) ---
if ($is_seller) {
    // 2a. Average Seller Rating and Review Count
    $rating_query = "
        SELECT COALESCE(AVG(r.rating), 0) AS avg_rating, COUNT(r.rating) AS review_count
        FROM ratings r 
        JOIN products p ON r.product_id = p.product_id
        WHERE p.seller_id = ?
    ";
    $stmt = $conn->prepare($rating_query);
    if (!$stmt) { die("SQL Error fetching rating: " . $conn->error); }
    $stmt->bind_param("i", $profile_uid);
    $stmt->execute();
    $rating_result = $stmt->get_result();
    $rating_data = $rating_result->fetch_assoc();
    $seller_rating = number_format($rating_data['avg_rating'], 1);
    $review_count = $rating_data['review_count'];
    $stmt->close();

    // 2b. Recent Products Sold by the Seller (Limit 6 for display)
    // Using 'quantity' instead of 'unit'
    $products_query = "
        SELECT product_id, pname, price, img, quantity
        FROM products
        WHERE seller_id = ?
        ORDER BY created_at DESC
        LIMIT 6
    ";
    $stmt = $conn->prepare($products_query);
    if (!$stmt) { die("SQL Error fetching products: " . $conn->error); }
    $stmt->bind_param("i", $profile_uid);
    $stmt->execute();
    $products_result = $stmt->get_result();
    $recent_products = $products_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
// --- 3. Fetch Seller Reviews (if applicable) ---
$recent_reviews = [];
if ($is_seller && $review_count > 0) {
    // Join 'ratings' table with 'user_profiles' to get the buyer's name.
    $reviews_query = "
        SELECT 
            r.rating, r.review, r.created_at, 
            up.full_name AS buyer_name, 
            p.pname AS product_name 
        FROM ratings r
        JOIN user_profiles up ON r.buyer_id = up.uid
        JOIN products p ON r.product_id = p.product_id
        WHERE r.seller_id = ?
        ORDER BY r.created_at DESC
        LIMIT 5; -- Display the 5 most recent reviews
    ";
    $stmt = $conn->prepare($reviews_query);
    if (!$stmt) { die("SQL Error fetching reviews: " . $conn->error); }
    $stmt->bind_param("i", $profile_uid);
    $stmt->execute();
    $reviews_result = $stmt->get_result();
    $recent_reviews = $reviews_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

/**
 * Generates the star rating HTML based on the average rating.
 */
function display_rating_stars($rating) {
    $rating = (float)$rating; 
    $html = '';
    $full_stars = floor($rating);
    $has_half_star = ($rating - $full_stars >= 0.25 && $rating - $full_stars < 0.75); 
    $empty_stars = 5 - $full_stars - ($has_half_star ? 1 : 0);
    $star_icon = '<i class="fas fa-star full"></i>';
    $half_icon = '<i class="fas fa-star-half-alt half"></i>';
    $empty_icon = '<i class="far fa-star empty"></i>';

    for ($i = 0; $i < $full_stars; $i++) {
        $html .= $star_icon;
    }
    if ($has_half_star) {
        $html .= $half_icon;
    }
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= $empty_icon;
    }

    return $html;
}

// Define avatar path (assuming avatars are stored in /style/imgs/avatars/)
$avatarPath = !empty($user_profile['avatar']) ? 'img/' . htmlspecialchars($user_profile['avatar']) : 'img/default_avatar.png';

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user_profile['full_name'] ?? 'User') ?>'s Public Profile</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <link rel="stylesheet" href="../style/style.css"> 
    <link rel="stylesheet" href="../style/public_profile.css"> <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&family=Lemon&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<?php  include __DIR__ . '/../layout/headweb.php';?>

<div class="profile-page-container">
    <h1 class="page-heading"><?= htmlspecialchars($user_profile['full_name'] ?? 'User') ?>'s Public Profile</h1>
    <p class="heading-tag"><?= $is_seller ? '⭐ Verified Seller Profile' : '🛒 Registered Buyer' ?></p>

    <div class="profile-card-layout">
        
        <aside class="profile-sidebar">
            <div class="avatar-container">
                <img src="<?= $avatarPath ?>" alt="User Avatar" class="profile-avatar">
            </div>

            <h2 class="user-display-name"><?= htmlspecialchars($user_profile['full_name'] ?? 'User') ?></h2>
            <p class="user-role-tag role-<?= $is_seller ? 'seller' : 'buyer' ?>">
                <i class="fas <?= $is_seller ? 'fa-store' : 'fa-shopping-basket' ?>"></i> 
                <?= $is_seller ? 'Seller' : 'Buyer' ?>
            </p>
            <?php if ($is_seller): ?>
            <div class="quick-stats-card rating-card">
                <h3>Average Seller Rating</h3>
                <div class="rating-display">
                    <div class="rating-stars">
                        <?= display_rating_stars($seller_rating) ?>
                    </div>
                    <span class="rating-value"><?= $seller_rating ?></span>
                </div>
                <p class="review-count-detail">(Based on **<?= $review_count ?>** reviews)</p>
                <a href="#reviews" class="theme-link">View All Reviews &rarr;</a>
            </div>
            <?php endif; ?>
        </aside>

        <section class="profile-details">
            <h2>Contact & Location Information</h2>
            
            <div class="detail-grid">
                <div class="detail-item"><strong>Email:</strong> <span><?= htmlspecialchars($user_profile['email'] ?? 'N/A') ?></span></div>
                <div class="detail-item"><strong>Gender:</strong> <span><?= htmlspecialchars(ucfirst($user_profile['gender'] ?? 'N/A')) ?></span></div>
                
                <?php if ($is_seller): ?>
                <div class="detail-item"><strong>Phone:</strong> <span><?= htmlspecialchars($user_profile['phone'] ?? 'N/A') ?></span></div>
                <div class="detail-item"><strong>Pincode:</strong> <span><?= htmlspecialchars($user_profile['pincode'] ?? 'N/A') ?></span></div>
                <?php endif; ?>

                <div class="detail-item detail-full-width"><strong>Address:</strong> <span><?= htmlspecialchars($user_profile['address'] ?? 'N/A') ?></span></div>
                <div class="detail-item"><strong>City:</strong> <span><?= htmlspecialchars($user_profile['city'] ?? 'N/A') ?></span></div>
            </div>

            <?php if ($is_seller): ?>
            <div class="products-card">
                <h2 style="margin-top: 30px;">Recent Listings</h2>
                
                <?php if (!empty($recent_products)): ?>
                <div class="products-grid">
                    <?php foreach ($recent_products as $product): ?>
                    <a href="../product_detail.php?id=<?= $product['product_id'] ?>" class="product-card">
                        <?php 
                        // Assuming product images are in '../shop/'
                        $product_img_path = !empty($product['img']) ? '../shop/' . htmlspecialchars($product['img']) : 'https://placehold.co/180x140/2A8C42/ffffff?text=ITEM';
                        ?>
                        <img src="<?= $product_img_path ?>" alt="<?= htmlspecialchars($product['pname']) ?>">
                        <div class="product-info">
                            <p title="<?= htmlspecialchars($product['pname']) ?>">
                                <strong><?= htmlspecialchars(strlen($product['pname']) > 20 ? substr($product['pname'], 0, 17) . '...' : $product['pname']) ?></strong>
                            </p>
                            <p class="price">₹<?= number_format($product['price'], 2) ?> / **<?= htmlspecialchars($product['quantity'] ?? 'Qty') ?>**</p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="empty-message">This seller has no public listings yet.</p>
                <?php endif; ?>
            </div>
            <div class="reviews-card" id="reviews">
                <h2 style="margin-top: 30px;">Customer Reviews (<?= $review_count ?>)</h2>
                
                <?php if (!empty($recent_reviews)): ?>
                <div class="reviews-grid">
                    <?php foreach ($recent_reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <h4 class="buyer-name"><?= htmlspecialchars($review['buyer_name']) ?></h4>
                            <span class="review-rating">
                                <span class="stars"><?= display_rating_stars($review['rating']) ?></span>
                                <span class="value"><?= number_format($review['rating'], 1) ?></span>
                            </span>
                        </div>
                        
                        <p class="review-text">
                            <i class="fas fa-quote-left quote-icon"></i>
                            <?= htmlspecialchars($review['review']) ?>
                            <i class="fas fa-quote-right quote-icon"></i>
                        </p>
                        <p class="review-meta">
                            **Product:** <?= htmlspecialchars($review['product_name']) ?> | 
                            **Date:** <?= date('M d, Y', strtotime($review['created_at'])) ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="empty-message">No customer reviews have been submitted for this seller yet.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </section>
    </div>

</div>

<?php // include __DIR__ . '/../layout/footer.php';?>

</body>
</html>