<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Approvals | Green Basket Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .filter-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin: 15px 25px;
        }
        .filter-bar select {
            padding: 8px 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background: #fff;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .seller-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            padding: 20px;
        }
        .seller-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            padding: 20px;
            transition: transform 0.3s ease;
        }
        .seller-card:hover { transform: translateY(-5px); }
        .seller-card img {
            width: 90px; height: 90px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 3px solid var(--color-brand-primary, #2eb535);
        }
        .seller-card h3 { margin: 10px 0 5px; font-size: 1.2rem; color: #333; }
        .seller-card p { color: #666; font-size: 0.9rem; margin-bottom: 8px; }
        .action-buttons {
            display: flex; justify-content: center; gap: 8px; margin-top: 10px;
        }
        .action-buttons button, .action-buttons a {
            border: none; outline: none; cursor: pointer;
            padding: 8px 12px; border-radius: 8px; font-size: 0.85rem;
            transition: background 0.3s ease; text-decoration: none; color: white;
        }
        .btn-approve { background: #28a745; }
        .btn-reject { background: #dc3545; }
        .btn-profile { background: #007bff; }
        .btn-approve:hover { background: #218838; }
        .btn-reject:hover { background: #c82333; }
        .btn-profile:hover { background: #0069d9; }

        .toast {
            position: fixed;
            bottom: 30px; right: 30px;
            background: rgba(40,167,69,0.95);
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 500;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 9999;
        }
        .toast.error { background: rgba(220,53,69,0.95); }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-container">
    <div class="header glass-container">
        <h1>Seller Approvals</h1>
        <div class="user-info">
            <span>Welcome, Admin</span>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="admin-page-container">
            <div class="filter-bar">
                <label for="statusFilter">Filter by Status:&nbsp;</label>
                <select id="statusFilter">
                    <option value="pending" selected>Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div id="seller-card-grid" class="seller-card-grid">
                <!-- Loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
function showToast(message, isError = false) {
    const toast = $('#toast');
    toast.text(message);
    toast.removeClass('error').toggleClass('error', isError);
    toast.fadeIn(300).delay(2000).fadeOut(400);
}

function loadRequests(status='pending') {
    $.ajax({
        url: 'fetch_seller_requests.php',
        type: 'GET',
        data: { status: status },
        success: function(html) {
            $('#seller-card-grid').html(html);
        },
        error: function() {
            $('#seller-card-grid').html('<p style="color:red;text-align:center;">Failed to load data.</p>');
        }
    });
}

$(document).ready(function(){
    loadRequests(); // load pending initially

    $('#statusFilter').change(function(){
        const status = $(this).val();
        loadRequests(status);
    });

    $(document).on('click', '.btn-approve, .btn-reject', function(){
        const btn = $(this);
        const requestId = btn.data('id');
        const action = btn.hasClass('btn-approve') ? 'approve' : 'reject';

        $.ajax({
            url: 'admin_action_seller.php',
            type: 'POST',
            dataType: 'json',
            data: { request_id: requestId, action: action },
            success: function(response){
                if(response.status === 'success'){
                    showToast(response.message);
                    loadRequests($('#statusFilter').val());
                } else {
                    showToast(response.message, true);
                }
            },
            error: function(){
                showToast('Server error. Try again later.', true);
            }
        });
    });
});
</script>
</body>
</html>
