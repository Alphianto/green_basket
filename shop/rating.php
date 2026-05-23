<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB connection failed.");

if (!isset($_SESSION['uid'])) {
    header('Location: ../account/login.php');
    exit();
}
$buyer_id = (int)$_SESSION['uid'];

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$seller_id  = isset($_GET['seller_id']) ? (int)$_GET['seller_id'] : 0;
$order_id   = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

$error = '';

// POST: process submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_rating = isset($_POST['product_rating']) ? (int)$_POST['product_rating'] : 0;
    $product_review = trim($_POST['review'] ?? '');
    $pid = (int)($_POST['product_id'] ?? 0);
    $sid = (int)($_POST['seller_id'] ?? 0);

    if ($product_rating < 1 || $product_rating > 5) {
        $error = "Please provide a product rating (1-5 stars) before submitting.";
    } else {
        $ins = $conn->prepare("INSERT INTO ratings (product_id, seller_id, buyer_id, rating, review, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $ins->bind_param("iiiis", $pid, $sid, $buyer_id, $product_rating, $product_review);
        $ins->execute();
        $ins->close();
        header("Location: orders.php");
        exit();
    }
}

// GET: fetch product & seller info to display
$product = null;
if ($product_id) {
    $q = $conn->prepare("SELECT product_id, pname, img, price FROM products WHERE product_id = ?");
    $q->bind_param("i", $product_id);
    $q->execute();
    $res = $q->get_result();
    $product = $res->fetch_assoc();
    $q->close();
}

$seller = null;
if ($seller_id) {
    $s = $conn->prepare("SELECT uid, full_name FROM user_profiles WHERE uid = ?");
    $s->bind_param("i", $seller_id);
    $s->execute();
    $rs = $s->get_result();
    $seller = $rs->fetch_assoc();
    $s->close();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Rate & Review — GreenBasket</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&family=Lemon&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style/rating.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
<?php include __DIR__ . '/../layout/headweb.php'; ?>

<main class="rating-page">
    <a href="orders.php" class="back-link">← Back to Orders</a>
    <h1>Share Your Feedback</h1>

    <div class="rating-top">
        <div class="prod">
            <img src="<?= htmlspecialchars($product['img'] ?? '/assets/img/placeholder.png') ?>" alt="<?= htmlspecialchars($product['pname'] ?? '') ?>" onerror="this.src='/assets/img/placeholder.png'">
            <div class="prod-info">
                <h2><?= htmlspecialchars($product['pname'] ?? 'Product') ?></h2>
                <div class="price">₹<?= isset($product['price']) ? number_format((float)$product['price'],2) : '-' ?></div>
            </div>
        </div>

        <div class="seller-block">
            <div class="text-muted">Sold by:</div>
            <a class="seller-link" href="../seller/profile.php?id=<?= (int)$seller['uid'] ?>"><?= htmlspecialchars($seller['full_name'] ?? 'Seller') ?></a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="form-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" id="ratingForm" class="rating-form">
        <input type="hidden" name="product_id" value="<?= (int)$product_id ?>">
        <input type="hidden" name="seller_id" value="<?= (int)$seller_id ?>">

        <section class="block product-rating-block">
            <h3>How would you rate this product?</h3>
            <div class="stars-container">
                <div class="stars" data-target="product_rating">
                    <span data-value="1" class="star">★</span>
                    <span data-value="2" class="star">★</span>
                    <span data-value="3" class="star">★</span>
                    <span data-value="4" class="star">★</span>
                    <span data-value="5" class="star">★</span>
                </div>
                <div class="rating-message">Tap to rate</div>
            </div>

            <input type="hidden" name="product_rating" id="product_rating" value="">
            <label for="review">Write a detailed review (optional):</label>
            <textarea name="review" id="review" placeholder="Tell others about the quality, freshness, and packaging of this item."></textarea>
        </section>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Submit Review</button>
        </div>
    </form>
</main>

<script src="../script/rating.js"></script>
</body>
</html>
