<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB Connection Failed");

// 1. Authentication and Authorization Check
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../../index.php");
    exit();
}
$seller_id = (int)$_SESSION['uid'];

// 2. Get Product ID from URL
if (!isset($_GET['edit_id']) || !is_numeric($_GET['edit_id'])) {
    $_SESSION['error'] = "Invalid product ID.";
    header("Location: products.php");
    exit();
}
$product_id = (int)$_GET['edit_id'];

// --- MOCK CATEGORIES (Updated to use category names as strings, matching the database schema) ---
$categories = [
    ['category_name' => 'vegetables'],
    ['category_name' => 'fruits'],
    ['category_name' => 'dairy'],
    ['category_name' => 'grains & pulses'],
    ['category_name' => 'meat & seafood'],
];
// -------------------------------------------------------------------


// 3. Fetch Existing Product Details (Updated for p_description and category)
$fetch_query = "
    SELECT 
        product_id, pname, p_description, category, price, quantity, 
        discount, p_expiry_date, product_tag, img 
    FROM products 
    WHERE product_id = ? AND seller_id = ?
";
$stmt = $conn->prepare($fetch_query);
if (!$stmt) {
    die("SQL Prepare Failed: (" . $conn->errno . ") " . $conn->error); 
}
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();
$product_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check if product exists and belongs to the seller
if (!$product_data) {
    $_SESSION['error'] = "Product not found or access denied.";
    header("Location: products.php");
    exit();
}


// 4. Handle Form Submission (Update Product)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Note: 'description' and 'category_id' updated to 'p_description' and 'category'
    $pname = $_POST['pname'];
    $p_description = $_POST['p_description'];
    $category = $_POST['category']; // Category is now a string
    $price = (float)$_POST['price'];
    $quantity = (float)$_POST['quantity'];
    // $unit removed as it is not in the product table schema
    $discount = (int)$_POST['discount'];
    $p_expiry_date = $_POST['expiry_date'];
    $product_tag = $_POST['product_tag'];
    $old_img = $_POST['old_img'];
    $new_img = $old_img; // Start with the old image path

    $error = false;

    // --- Image Upload Handling ---
    if (isset($_FILES['img']) && $_FILES['img']['error'] === 0) {
        $target_dir = "../shop/";
        $filename = basename($_FILES['img']['name']);
        $new_img_path = "images/" . time() . "_" . $filename; 
        $target_file = $target_dir . $new_img_path;

        // Move the uploaded file
        if (move_uploaded_file($_FILES['img']['tmp_name'], $target_file)) {
            $new_img = $new_img_path;
            
            // Delete old image if it's not the default and not the new image
            if ($old_img && $old_img !== 'default.jpg' && file_exists($target_dir . $old_img)) {
                 unlink($target_dir . $old_img);
            }
        } else {
            $_SESSION['error'] = "Failed to upload new image. Keeping old image.";
            $error = true;
        }
    }

    if (!$error) {
        // Prepare the update query (unit column removed)
        $update_query = "
            UPDATE products SET 
                pname = ?, p_description = ?, category = ?, price = ?, 
                quantity = ?, discount = ?, p_expiry_date = ?, 
                product_tag = ?, img = ?
            WHERE product_id = ? AND seller_id = ?
        ";
        
        $stmt = $conn->prepare($update_query);
        if ($stmt) {
            // Corrected Bind parameters: 11 placeholders require 11 types (sss d d i s s s i i)
            // s=string, d=double/float, i=integer
            $stmt->bind_param(
                "sssddisssii", 
                $pname, $p_description, $category, $price, 
                $quantity, $discount, $p_expiry_date, 
                $product_tag, $new_img, $product_id, $seller_id
            );

            if ($stmt->execute()) {
                $_SESSION['message'] = "Product '{$pname}' updated successfully!";
                $stmt->close();
                $conn->close();
                header("Location: products.php");
                exit();
            } else {
                $_SESSION['error'] = "Error updating product: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Database error preparing update statement.";
        }
    }
    // Reload data if there was an error to show the user the latest state
    $conn->close();
    header("Location: product_edit.php?edit_id=" . $product_id);
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product: <?= htmlspecialchars($product_data['pname']) ?></title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <link rel="stylesheet" href="../style/seller_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --color-primary-blue: #007bff;
            --color-success-green: #28a745;
            --color-danger-red: #dc3545;
            --color-secondary-dark: #333;
        }
        body { background: #f7f8fa; font-family: 'Inter', sans-serif; }
        .main-content { padding: 20px; }
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        h1 { 
            font-size: 1.5rem; 
            margin-bottom: 25px; 
            color: var(--color-secondary-dark); 
            display: flex;
            align-items: center;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #555;
            font-size: 0.9rem;
        }
        input[type="text"], input[type="number"], input[type="date"], select, textarea {
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--color-primary-blue);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        .image-upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .image-upload-area:hover {
            border-color: var(--color-primary-blue);
        }
        .image-preview {
            margin-top: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .image-preview img {
            max-width: 150px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 10px;
        }
        .btn-update {
            background-color: var(--color-success-green);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 20px;
            width: 100%;
        }
        .btn-update:hover {
            background-color: #1e7e34;
        }
        .message-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .message-box.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6fb;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('img');
            const previewContainer = document.getElementById('imagePreviewContainer');

            // Function to display image preview
            function previewImage(file) {
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewContainer.innerHTML = `
                            <img src="${e.target.result}" alt="Image Preview" />
                            <span>New Image Selected</span>
                        `;
                    };
                    reader.readAsDataURL(file);
                } else {
                     // If no new file is selected, show the old image placeholder
                     const oldImgPath = document.getElementById('old_img').value;
                     const oldImgUrl = '../shop/' + oldImgPath;
                     previewContainer.innerHTML = `
                         <img src="${oldImgUrl}" alt="Current Image" 
                              onerror="this.onerror=null;this.src='https://placehold.co/150x100/CCCCCC/333333?text=No+Image';" />
                         <span>Current Image</span>
                     `;
                }
            }

            // Event listener for image input change
            imageInput.addEventListener('change', function() {
                previewImage(this.files[0]);
            });

            // Initial load of the current image
            previewImage(null); 
            
            // Clear message after a few seconds
            const msgBox = document.querySelector('.message-box');
            if (msgBox) {
                setTimeout(() => {
                    msgBox.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</head>
<body>
<?php include 'seller_sidebar.php'; ?>
    <div class="main-content">
        <div class="form-container">
            <h1><i class="fas fa-edit mr-2"></i> Edit Product: <?= htmlspecialchars($product_data['pname']) ?></h1>

            <?php 
            // Display success or error message from POST redirect
            if (isset($_SESSION['message'])): ?>
                <div class="message-box success">
                    <i class="fas fa-check-circle mr-2"></i> <?= $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php elseif (isset($_SESSION['error'])): ?>
                <div class="message-box error">
                    <i class="fas fa-exclamation-triangle mr-2"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <!-- Hidden input for the old image path and product ID -->
                <input type="hidden" name="old_img" id="old_img" value="<?= htmlspecialchars($product_data['img']) ?>">
                <input type="hidden" name="product_id" value="<?= $product_id ?>">

                <div class="form-grid">
                    <!-- Product Name -->
                    <div class="form-group">
                        <label for="pname">Product Name *</label>
                        <input type="text" id="pname" name="pname" value="<?= htmlspecialchars($product_data['pname']) ?>" required>
                    </div>

                    <!-- Category (Updated to reflect the 'category' string field in DB) -->
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['category_name']) ?>" 
                                    <?= (strtolower($cat['category_name']) === strtolower($product_data['category'])) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucwords($cat['category_name'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Description (Updated to reflect the 'p_description' field in DB) -->
                    <div class="form-group full-width">
                        <label for="p_description">Description</label>
                        <textarea id="p_description" name="p_description"><?= htmlspecialchars($product_data['p_description']) ?></textarea>
                    </div>

                    <!-- Price -->
                    <div class="form-group">
                        <label for="price">Price (₹/unit) *</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars($product_data['price']) ?>" required>
                    </div>

                    <!-- Quantity -->
                    <div class="form-group">
                        <label for="quantity">Quantity *</label>
                        <input type="number" id="quantity" name="quantity" step="0.01" value="<?= htmlspecialchars($product_data['quantity']) ?>" required>
                    </div>

                    <!-- Discount -->
                    <div class="form-group">
                        <label for="discount">Discount (%)</label>
                        <input type="number" id="discount" name="discount" min="0" max="100" value="<?= htmlspecialchars($product_data['discount']) ?>">
                    </div>

                    <!-- Expiry Date -->
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date *</label>
                        <!-- Note: date input requires YYYY-MM-DD format -->
                        <input type="date" id="expiry_date" name="expiry_date" value="<?= htmlspecialchars($product_data['p_expiry_date']) ?>" required>
                    </div>

                    <!-- Product Tag -->
                    <div class="form-group">
                        <label for="product_tag">Product Tag (e.g., organic, fresh)</label>
                        <input type="text" id="product_tag" name="product_tag" value="<?= htmlspecialchars($product_data['product_tag']) ?>">
                    </div>

                    <!-- Product Image -->
                    <div class="form-group full-width">
                        <label for="img">Product Image</label>
                        <div class="image-upload-area" onclick="document.getElementById('img').click()">
                            <p>Click to upload new image (JPG, PNG, WEBP) | Max 5MB</p>
                            <div class="image-preview" id="imagePreviewContainer">
                                <!-- Initial preview will be loaded by JS -->
                            </div>
                        </div>
                        <input type="file" id="img" name="img" accept="image/jpeg,image/png,image/webp" style="display: none;">
                    </div>
                </div>

                <button type="submit" class="btn-update">Update Product</button>
            </form>
        </div>
    </div>
</body>
</html>
