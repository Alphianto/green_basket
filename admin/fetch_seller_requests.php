<?php
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

$status = $_GET['status'] ?? 'pending';
$valid = ['pending', 'approved', 'rejected'];
if (!in_array($status, $valid)) $status = 'pending';

$query = "
    SELECT sr.request_id, sr.buyer_id, sr.full_name, sr.email, sr.request_status,
           u.username, up.avatar
    FROM seller_requests sr
    JOIN users u ON sr.buyer_id = u.uid
    LEFT JOIN user_profiles up ON u.uid = up.uid
    WHERE sr.request_status = ?
    ORDER BY sr.request_date DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p style='text-align:center;width:100%;color:#555;'>No $status seller requests found.</p>";
    exit;
}

while ($row = $result->fetch_assoc()) {
    $avatarPath = "../account/img/" . htmlspecialchars($row['avatar'] ?? "default_avatar.png");
    echo "
    <div class='seller-card' id='request-{$row['request_id']}'>
        <img src='{$avatarPath}' alt='Profile Picture'>
        <h3>" . htmlspecialchars($row['full_name']) . "</h3>
        <p><i class='fas fa-user'></i> " . htmlspecialchars($row['username']) . "</p>";
    if (!empty($row['email'])) {
        echo "<p><i class='fas fa-envelope'></i> " . htmlspecialchars($row['email']) . "</p>";
    }

    echo "<div class='action-buttons'>";
    echo "<a href='../account/public_profile.php?uid={$row['buyer_id']}' class='btn-profile'>
            <i class='fas fa-id-card'></i> Profile
          </a>";

    if ($status === 'pending') {
        echo "<button class='btn-approve' data-id='{$row['request_id']}'><i class='fas fa-check'></i> Approve</button>
              <button class='btn-reject' data-id='{$row['request_id']}'><i class='fas fa-times'></i> Reject</button>";
    } else {
        $icon = $status === 'approved' ? 'fa-check-circle' : 'fa-times-circle';
        $color = $status === 'approved' ? 'green' : 'red';
        echo "<span style='color:$color; font-weight:600;'><i class='fas $icon'></i> $status</span>";
    }

    echo "</div></div>";
}
?>
