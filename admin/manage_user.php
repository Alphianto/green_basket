<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

// Admin authentication
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Current page for sidebar active link
$admin_full_name ='Admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Users | Green Basket Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php include 'admin_sidebar.php';?>

<div class="main-container">
    <div class="header glass-container">
        <h1>All Users details</h1>
        <div class="user-info">
            <span>Welcome, <?= htmlspecialchars($admin_full_name) ?></span>
            <a href="../logout.php" class="btn logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="admin-page-container">
            <h2>All Users</h2>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>UID</th>
                            <th>Username</th>
                            <th>phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="user-table-body">
                        <?php
                        // Fetch users excluding admins
                        $query = "SELECT uid, username, phone , role, status FROM users WHERE role != 'admin' ORDER BY uid DESC";
                        $result = $conn->query($query);

                        if (!$result) {
                            echo "<tr><td colspan='6'>Query Error: " . $conn->error . "</td></tr>";
                        } elseif ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $btn_text = $row['status'] === 'active' ? 'Ban' : 'Unban';
                                $btn_class = $row['status'] === 'active' ? 'btn-danger' : 'btn-success';
                                echo "<tr id='user-{$row['uid']}'>
                                    <td>{$row['uid']}</td>
                                    <td>".htmlspecialchars($row['username'])."</td>
                                    <td>".htmlspecialchars($row['phone'])."</td>
                                    <td>".htmlspecialchars($row['role'])."</td>
                                    <td class='status-cell'>".htmlspecialchars($row['status'])."</td>
                                    <td>
                                        <button class='btn-action $btn_class' data-uid='{$row['uid']}' data-action='{$btn_text}'>$btn_text</button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No users found</td></tr>";
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
        var user_id = btn.data('uid');
        var action = btn.data('action').toLowerCase(); // ban or unban

        $.ajax({
            url: 'admin_action_user.php',
            type: 'POST',
            data: { user_id: user_id, action: action },
            dataType: 'json',
            success: function(response){
                if(response.status === 'success'){
                    var row = $('#user-' + user_id);
                    row.find('.status-cell').text(response.new_status);

                    if(response.new_status === 'banned'){
                        btn.text('Unban').removeClass('btn-danger').addClass('btn-success').data('action','Unban');
                    } else {
                        btn.text('Ban').removeClass('btn-success').addClass('btn-danger').data('action','Ban');
                    }
                } else {
                    alert(response.message);
                }
            },
            error: function(){
                alert('AJAX error: could not update user status.');
            }
        });
    });
});
</script>
</body>
</html>
