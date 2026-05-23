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
    <title>Manage Orders | Green Basket Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Status Buttons */
        .btn-status {
            padding: 6px 14px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            color: #fff;
        }
        .btn-pending { background-color: #f39c12; }
        .btn-pending:hover { background-color: #e67e22; transform: scale(1.05); }
        .btn-delivered { background-color: #3498db; }
        .btn-delivered:hover { background-color: #2980b9; transform: scale(1.05); }
        .btn-completed { background-color: #2ecc71; }
        .btn-completed:hover { background-color: #27ae60; transform: scale(1.05); }
        .btn-cancelled { background-color: #e74c3c; }
        .btn-cancelled:hover { background-color: #c0392b; transform: scale(1.05); }
        /* Table adjustments */
        .data-table th, .data-table td { padding: 12px 10px; border-bottom: 1px solid #e0e0e0; text-align: left; }
        .data-table tbody tr:hover { background-color: #f5f5f5; }
    </style>
</head>
<body>

<!-- Sidebar -->
<?php include 'admin_sidebar.php';?>

<div class="main-container">
    <div class="header glass-container">
        <h1>All Orders</h1>
        <div class="user-info">
            <span>Welcome, Admin</span>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="admin-page-container">
            <h2>All Orders</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Buyer</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="order-table-body">
                        <?php
                        $query = "SELECT o.order_id, o.product_id, o.buyer_id, o.quantity, o.price, o.status, u.username, p.pname
                                  FROM orders o
                                  JOIN users u ON o.buyer_id = u.uid
                                  JOIN products p ON o.product_id = p.product_id
                                  ORDER BY o.order_id DESC";
                        $result = $conn->query($query);

                        if (!$result) {
                            echo "<tr><td colspan='7'>Query Error: " . $conn->error . "</td></tr>";
                        } elseif ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $status_class = strtolower($row['status']);
                                echo "<tr id='order-{$row['order_id']}'>
                                    <td>{$row['order_id']}</td>
                                    <td>".htmlspecialchars($row['username'])."</td>
                                    <td>".htmlspecialchars($row['pname'])."</td>
                                    <td>{$row['quantity']}</td>
                                    <td>₹{$row['price']}</td>
                                    <td class='status-cell'>".htmlspecialchars($row['status'])."</td>
                                    <td>
                                        <button class='btn-status btn-{$status_class}' data-order='{$row['order_id']}' data-status='{$row['status']}'>".htmlspecialchars($row['status'])."</button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No orders found</td></tr>";
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
    $('.btn-status').click(function(){
        var btn = $(this);
        var order_id = btn.data('order');
        var current_status = btn.data('status');

        // Determine next status
        var next_status = current_status.toLowerCase() === 'pending' ? 'Delivered' :
                          current_status.toLowerCase() === 'delivered' ? 'Completed' :
                          current_status.toLowerCase() === 'completed' ? 'Completed' :
                          current_status.toLowerCase() === 'cancelled' ? 'Cancelled' : 'Pending';

        $.ajax({
            url: 'admin_action_order.php',
            type: 'POST',
            data: { order_id: order_id, status: next_status },
            dataType: 'json',
            success: function(response){
                if(response.status === 'success'){
                    btn.text(response.new_status)
                       .data('status', response.new_status.toLowerCase())
                       .removeClass('btn-pending btn-delivered btn-completed btn-cancelled')
                       .addClass('btn-' + response.new_status.toLowerCase());
                    btn.closest('tr').find('.status-cell').text(response.new_status);
                } else {
                    alert(response.message);
                }
            },
            error: function(){
                alert('AJAX error: could not update order status.');
            }
        });
    });
});
</script>
</body>
</html>
