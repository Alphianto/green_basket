<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB Connection Failed");

// Seller ID - Use the session ID, default to 101 for testing based on your sample data
$seller_id = (int)($_SESSION['uid'] ?? 101);

// --- 1. OVERALL SELLER RATING ---
$seller_rating_query = "
    SELECT COALESCE(AVG(rating), 0) AS avg_seller_rating
    FROM ratings 
    WHERE seller_id = ?
";
$stmt = $conn->prepare($seller_rating_query);
if (!$stmt) {
    die("SQL Prepare Failed for Overall Rating: (" . $conn->errno . ") " . $conn->error); 
}
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$seller_rating_data = $stmt->get_result()->fetch_assoc();
$avg_seller_rating = $seller_rating_data['avg_seller_rating'];
$stmt->close();


// --- 2. FETCH ALL PRODUCT REVIEWS AND RATINGS ---
$reviews_query = "
    SELECT 
        r.rating_id, 
        r.rating, 
        r.review,           
        r.created_at AS review_date, 
        p.pname,
        p.img AS product_img,
        u.full_name       
    FROM ratings r
    JOIN products p ON r.product_id = p.product_id
    JOIN user_profiles u ON r.buyer_id = u.uid 
    WHERE r.seller_id = ? 
    ORDER BY r.created_at DESC
";
$stmt = $conn->prepare($reviews_query);

if (!$stmt) {
    die("SQL Prepare Failed for Reviews List: (" . $conn->errno . ") " . $conn->error); 
}

$stmt->bind_param("i", $seller_id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();


// --- 3. FETCH AVERAGE RATING PER PRODUCT ---
$product_avg_query = "
    SELECT 
        p.pname,
        p.img AS product_img,
        COALESCE(AVG(r.rating), 0) AS avg_rating,
        COUNT(r.rating_id) AS total_reviews
    FROM products p
    JOIN ratings r ON p.product_id = r.product_id
    WHERE r.seller_id = ? 
    GROUP BY p.product_id, p.pname, p.img
    HAVING COUNT(r.rating_id) > 0 
    ORDER BY avg_rating DESC
";
$stmt = $conn->prepare($product_avg_query);
if (!$stmt) {
    die("SQL Prepare Failed for Product Averages: (" . $conn->errno . ") " . $conn->error); 
}
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$product_ratings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

/**
 * Helper function to generate star icons
 */
function get_stars($rating) {
    $stars = '';
    $rating = round($rating * 2) / 2; // Round to nearest half
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) {
            $stars .= '<i class="fas fa-star" style="color: #ffb400;"></i>';
        } elseif ($rating > $i - 1 && $rating < $i) {
            $stars .= '<i class="fas fa-star-half-alt" style="color: #ffb400;"></i>';
        } else {
            $stars .= '<i class="far fa-star" style="color: #ffb400;"></i>';
        }
    }
    return $stars;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews Management</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <link rel="stylesheet" href="../style/seller_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General page layout matching previous theme */
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f7f8fa; 
            margin: 0; 
        }

        h1,h2 { color: #333; }
        
        /* --- General Layout and Stats Card (Retained) --- */
        .rating-card-grid { 
            display: grid; 
            grid-template-columns: 1fr 3fr; 
            gap: 20px; 
            margin-top: 20px; 
            align-items: flex-start;
        }

        .overall-rating-card {
            background: var(--color-card);
            border-radius: 12px;
            padding: 30px 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            text-align: center;
            border-left: 5px solid var(--color-primary);
        }
        /* ... other stats card styles ... */
        .product-ratings-summary {
            background: var(--color-card);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .product-ratings-summary table td img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
            vertical-align: middle;
        }

        /* ------------------------------------------------ */
        /* --- CRITICAL UI CHANGES FOR REVIEW GRID --- */
        /* ------------------------------------------------ */

        .reviews-list-container {
            margin-top: 40px;
            /* Remove background/padding here to let the cards own their styling */
            padding: 0;
            background: none;
            box-shadow: none;
        }
        
        .review-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); /* 2-3 columns on wide screens */
            gap: 20px;
        }

        .review-card {
            background: var(--color-card); /* White card background */
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 5px solid var(--color-success); /* Green accent bar on the left */
            transition: transform 0.2s;
            display: flex; /* Flex container for content */
            flex-direction: column;
            justify-content: space-between;
        }
        
        .review-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .review-info .buyer-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: #000000ff;
            margin-top: 0;
        }
        
        .review-rating-date .stars {
            font-size: 1.1rem;
        }

        .review-comment {
            font-size: 1rem;
            color: var(--color-text-dark);
            padding: 10px 0;
            position: relative;
            font-style: italic;
        }

        /* Quote styling */
        .review-comment::before {
            content: "\201C"; /* Left double quote */
            font-size: 2.5rem;
            line-height: 0.1;
            color: var(--color-success);
            margin-right: 5px;
            vertical-align: -0.3em;
            font-family: serif;
        }
        .review-comment::after {
            content: "\201D"; /* Right double quote */
            font-size: 2.5rem;
            line-height: 0.1;
            color: var(--color-success);
            margin-left: 5px;
            vertical-align: -0.3em;
            font-family: serif;
        }

        .review-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #eee;
            font-size: 0.85rem;
            color: #666;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .review-footer strong {
            font-weight: 600;
            color: #333;
        }

        /* Reply Button Styling (Less prominent in the grid view) */

        /* Mobile Adjustments */
        @media (max-width: 900px) {
            .rating-card-grid {
                grid-template-columns: 1fr; 
            }
            .review-grid {
                grid-template-columns: 1fr; /* Single column on phones */
            }
        }
    </style>
</head>
<body>
<?php include 'seller_sidebar.php'; ?>
    <div class="main-content">
        <h1><i class="fas fa-star"></i> Reviews Management</h1>

        <div class="rating-card-grid">
            <div class="overall-rating-card">
                <p class="value"><?= number_format($avg_seller_rating, 1) ?></p>
                <div class="stars"><?= get_stars($avg_seller_rating) ?></div>
                <p class="label">Overall Seller Rating</p>
            </div>
            
            <div class="product-ratings-summary">
                <h2>Product Ratings Summary</h2>
                <?php if (!empty($product_ratings)): ?>
                    <table class="product-ratings-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Avg. Rating</th>
                                <th>Total Reviews</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($product_ratings as $pr): ?>
                            <tr>
                                <td>
                                    <img src="../shop/<?= htmlspecialchars($pr['product_img']) ?>" alt="<?= htmlspecialchars($pr['pname']) ?>">
                                    <?= htmlspecialchars($pr['pname']) ?>
                                </td>
                                <td>
                                    <span class="stars"><?= get_stars($pr['avg_rating']) ?></span> 
                                    (<?= number_format($pr['avg_rating'], 1) ?>)
                                </td>
                                <td><?= $pr['total_reviews'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No products have received reviews yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="reviews-list-container">
            <h2>Customer Reviews (<?= count($reviews) ?>)</h2>
            <?php if (!empty($reviews)): ?>
                <div class="review-grid">
                    <?php foreach($reviews as $r): ?>
                    <div class="review-card">
                        <div>
                            <div class="review-header">
                                <div class="review-info">
                                    <span class="buyer-name"><?= htmlspecialchars($r['full_name']) ?></span>
                                </div>
                                <div class="review-rating-date">
                                    <span class="stars"><?= get_stars($r['rating']) ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($r['review'])): ?>
                                <p class="review-comment"><?= nl2br(htmlspecialchars($r['review'])) ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="review-footer">
                            <span class="product-name">Product: <strong><?= htmlspecialchars($r['pname']) ?></strong> | Date: <?= date('M j, Y', strtotime($r['review_date'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No reviews found for your products.</p>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>