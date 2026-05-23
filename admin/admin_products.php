<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

// Admin authentication
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products | Green Basket Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
    /* Ban/Unban style buttons for products */
    .btn-action {
        padding: 6px 14px;
        border: none;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        color: #fff;
    }
    .btn-action.btn-danger { background-color: #e74c3c; }
    .btn-action.btn-danger:hover { background-color: #c0392b; transform: scale(1.05); }
    .btn-action.btn-success { background-color: #2ecc71; }
    .btn-action.btn-success:hover { background-color: #27ae60; transform: scale(1.05); }
    .btn-action:disabled { opacity: 0.6; cursor: not-allowed; }
    .data-table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
    .data-table th, .data-table td { padding: 12px 10px; text-align: left; border-bottom: 1px solid #e0e0e0; }
    .data-table tbody tr:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php include 'admin_sidebar.php';?>

<div class="main-container">
    <div class="header glass-container">
        <h1>All Products</h1>
        <div class="user-info">
            <span>Welcome, Admin</span>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="admin-page-container">
            <h2>All Products</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>PID</th>
                            <th>Name</th>
                            <th>Seller</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body">
                        <?php
                        // Fetch products with seller name
                        $query = "SELECT p.product_id, p.pname, u.username AS seller, p.price, p.p_status 
                                  FROM products p 
                                  JOIN users u ON p.seller_id = u.uid 
                                  ORDER BY p.product_id DESC";
                        $result = $conn->query($query);

                        if (!$result) {
                            echo "<tr><td colspan='6'>Query Error: " . $conn->error . "</td></tr>";
                        } elseif ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $btn_text = $row['p_status'] === 'available' ? 'Deactivate' : 'Activate';
                                $btn_class = $row['p_status'] === 'available' ? 'btn-danger' : 'btn-success';
                                echo "<tr id='product-{$row['product_id']}'>
                                    <td>{$row['product_id']}</td>
                                    <td>".htmlspecialchars($row['pname'])."</td>
                                    <td>".htmlspecialchars($row['seller'])."</td>
                                    <td>₹".number_format($row['price'],2)."</td>
                                    <td class='status-cell'>".htmlspecialchars($row['p_status'])."</td>
                                    <td>
                                        <button class='btn-action $btn_class' data-pid='{$row['product_id']}' data-action='{$btn_text}'>$btn_text</button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No products found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('.btn-action').click(function(){
        var btn = $(this);
        var pid = btn.data('pid');
        var action = btn.data('action').toLowerCase(); // activate or deactivate

        $.ajax({
            url: 'admin_action_product.php',
            type: 'POST',
            data: { product_id: pid, action: action },
            dataType: 'json',
            success: function(response){
                if(response.status === 'success'){
                    var row = $('#product-' + pid);
                    row.find('.status-cell').text(response.new_status);

                    if(response.new_status === 'available'){
                        btn.text('Deactivate').removeClass('btn-success').addClass('btn-danger').data('action','Deactivate');
                    } else {
                        btn.text('Activate').removeClass('btn-danger').addClass('btn-success').data('action','Activate');
                    }
                } else {
                    alert(response.message);
                }
            },
            error: function(){
                alert('AJAX error: could not update product status.');
            }
        });
    });
});
</script>
</body>
</html>
