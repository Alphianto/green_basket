<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB connection failed.");

// Seller info
$seller_id = (int)($_SESSION['uid'] ?? 1);
$seller_name = $_SESSION['user'] ?? 'Seller';

// Fetch city from sellers table or fallback to user_profiles
$city = '';
$seller_stmt = $conn->prepare("SELECT city FROM sellers WHERE seller_id = ?");
if ($seller_stmt) {
    $seller_stmt->bind_param("i", $seller_id);
    $seller_stmt->execute();
    $res = $seller_stmt->get_result();
    if ($row = $res->fetch_assoc()) $city = $row['city'] ?? '';
    $seller_stmt->close();
} else {
    $up_stmt = $conn->prepare("SELECT city FROM user_profiles WHERE uid = ?");
    if ($up_stmt) {
        $up_stmt->bind_param("i", $seller_id);
        $up_stmt->execute();
        $res = $up_stmt->get_result();
        if ($row = $res->fetch_assoc()) $city = $row['city'] ?? '';
        $up_stmt->close();
    }
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pname = trim($_POST['pname'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $quantity = (float)($_POST['quantity'] ?? 0);
    $unit = trim($_POST['unit'] ?? 'kg');
    $p_expiry_date = trim($_POST['p_expiry_date'] ?? '');
    $discount = (float)($_POST['discount'] ?? 0);
    $product_tag = trim($_POST['product_tag'] ?? '');
    $img_path = '';

    // Validate required fields
    if ($pname === '' || $category === '' || $price <= 0 || $quantity <= 0 || $p_expiry_date === '') {
        $message = "Please fill required fields correctly.";
        $message_type = 'error';
    } else {
        // --- Convert quantity to kg ---
        switch ($unit) {
            case 'pcs':
                $quantity_in_kg = $quantity * 0.2;
                break;
            case 'gram':
                $quantity_in_kg = $quantity * 0.001;
                break;
            case 'dozen':
                $quantity_in_kg = $quantity * 12 * 0.15;
                break;
            default:
                $quantity_in_kg = $quantity;
        }

        $quantity = round($quantity_in_kg, 4);
        $total_quantity = $quantity;

        // --- Image upload ---
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = __DIR__ . "/../shop/imgs/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            $image_file_type = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
            $unique_filename = uniqid('prod_') . '.' . $image_file_type;
            $target_file = $target_dir . $unique_filename;
            $img_path = 'imgs/' . $unique_filename;

            $check = @getimagesize($_FILES['product_image']['tmp_name']);
            if ($check === false) {
                $message = "File is not a valid image.";
                $message_type = 'error';
            } elseif ($_FILES['product_image']['size'] > 5 * 1024 * 1024) {
                $message = "File too large (max 5MB).";
                $message_type = 'error';
            } elseif (!in_array($image_file_type, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $message = "Invalid image type. Allowed: JPG, JPEG, PNG, GIF, WEBP.";
                $message_type = 'error';
            } elseif (!move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                $message = "Image upload failed.";
                $message_type = 'error';
            }
        } else {
            $message = "Please attach a product image.";
            $message_type = 'error';
        }
    }

    // --- Insert into DB ---
    if ($message_type !== 'error') {
        $sql = "INSERT INTO products 
                (seller_id, pname, p_description, category, price, quantity, total_quantity, discount, p_expiry_date, product_tag, city, img, p_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available')";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            $message = "Server error while preparing the database query.";
            $message_type = 'error';
        } else {
            $stmt->bind_param(
                "isssddddssss",
                $seller_id,
                $pname,
                $description,
                $category,
                $price,
                $quantity,
                $total_quantity,
                $discount,
                $p_expiry_date,
                $product_tag,
                $city,
                $img_path
            );

            if ($stmt->execute()) {
                $message = "Product \"{$pname}\" added successfully.";
                $message_type = 'success';
            } else {
                error_log("Execute failed: " . $stmt->error);
                $message = "Database error: Could not save product.";
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Add Product - GreenBasket</title>
<link rel="icon" type="image/png" href="../style/imgs/gb.png">
<link rel="stylesheet" href="../style/seller_dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
.add-product-container { max-width:800px; margin:0 auto; background:var(--color-card); padding:30px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.05); }
.form-row, .form-row-third { display:grid; gap:20px; }
.form-row { grid-template-columns:1fr 1fr; }
.form-row-third { grid-template-columns:1fr 1fr 1fr; }
@media(max-width:600px){ .form-row,.form-row-third{grid-template-columns:1fr;} }
.image-upload-area { display:flex; flex-direction:column; align-items:center; border:2px dashed #ddd; border-radius:8px; padding:20px; cursor:pointer; }
#image-preview { width:150px; height:150px; object-fit:cover; border-radius:8px; border:1px solid #eee; margin-bottom:10px; }
#product_image { display:none; }
.message-box { padding:15px; margin-bottom:20px; border-radius:8px; font-weight:600; }
.message-box.success { background:#e6ffe6; border:1px solid #36a; color:#1b6e2a; }
.message-box.error { background:#ffe6e6; border:1px solid #d00000; color:#900; }
input, select, textarea { width:100%; padding:8px; border-radius:5px; border:1px solid #ccc; box-sizing:border-box; }
</style>
</head>
<body>

<?php include 'seller_sidebar.php'; ?>

<div class="main-content">
  <header class="dashboard-header"><h1><i class="fas fa-plus-circle"></i> Add New Product</h1></header>

  <div class="add-product-container">
    <?php if($message): ?>
      <div class="message-box <?= htmlspecialchars($message_type) ?>">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" novalidate>
      <div class="form-row">
        <div class="form-group">
          <label>Product Name *</label>
          <input type="text" name="pname" placeholder="Product name" required />
        </div>
        <div class="form-group">
          <label>Category *</label>
          <select name="category" required>
            <option value="">Select Category</option>
            <option value="fruits">Fruits</option>
            <option value="vegetables">Vegetables</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="3"></textarea>
      </div>

      <div class="form-row-third">
        <div class="form-group">
          <label>Price (₹/unit) *</label>
          <input type="number" step="0.01" name="price" required />
        </div>
        <div class="form-group">
          <label>Quantity *</label>
          <input type="number" step="0.1" name="quantity" required />
        </div>
        <div class="form-group">
          <label>Unit *</label>
          <select name="unit" required>
            <option value="kg">Kilogram</option>
            <option value="pcs">Pieces</option>
            <option value="gram">Gram</option>
            <option value="dozen">Dozen</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Discount (%)</label>
          <input type="number" name="discount" min="0" max="100" value="0" />
        </div>
        <div class="form-group">
          <label>Expiry Date *</label>
          <input type="date" name="p_expiry_date" required min="<?= date('Y-m-d') ?>" />
        </div>
      </div>

      <div class="form-group">
        <label>Product Tag *</label>
        <input type="text" name="product_tag" placeholder="e.g., organic, fresh" required />
      </div>

      <div class="form-group">
        <label>Product Image *</label>
        <input type="file" id="product_image" name="product_image" accept="image/*" required />
        <label for="product_image" class="image-upload-area">
          <img id="image-preview" src="" alt="Preview" />
          <p>Click to upload (JPG, PNG, WEBP) | Max 5MB</p>
        </label>
      </div>

      <button type="submit" class="btn-primary">Add Product</button>
    </form>
  </div>
</div>

<script>
document.getElementById('product_image').addEventListener('change', function(e){
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = function(evt){ document.getElementById('image-preview').src = evt.target.result; }
  reader.readAsDataURL(file);
});
</script>
</body>
</html>
