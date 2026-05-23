<?php
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)($_POST['request_id'] ?? 0);
    $action = strtolower(trim($_POST['action'] ?? ''));

    if (!$request_id || !in_array($action, ['approve', 'reject'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
        exit();
    }

    // Fetch buyer info
    $query = "SELECT buyer_id, full_name FROM seller_requests WHERE request_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Request not found.']);
        exit();
    }

    $data = $res->fetch_assoc();
    $buyer_id = $data['buyer_id'];
    $full_name = $data['full_name'];
    $stmt->close();

    if ($action === 'approve') {
        $conn->begin_transaction();
        try {
            // 1️⃣ Update user role to seller
            $updateUser = $conn->prepare("UPDATE users SET role = 'seller' WHERE uid = ?");
            $updateUser->bind_param("i", $buyer_id);
            $updateUser->execute();

            // 2️⃣ Update request status instead of deleting
            $updateReq = $conn->prepare("
                UPDATE seller_requests 
                SET request_status = 'approved', approved_date = NOW() 
                WHERE request_id = ?
            ");
            $updateReq->bind_param("i", $request_id);
            $updateReq->execute();

            // 3️⃣ Log admin action
            $log = $conn->prepare("
                INSERT INTO system_logs (event_type, description, event_time)
                VALUES ('seller_approval', CONCAT('Approved seller request for ', ?), NOW())
            ");
            $log->bind_param("s", $full_name);
            $log->execute();

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Seller approved successfully!']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Approval failed.']);
        }
    } 
    else { // 🚫 Reject action
        try {
            $updateReq = $conn->prepare("
                UPDATE seller_requests 
                SET request_status = 'rejected', approved_date = NOW() 
                WHERE request_id = ?
            ");
            $updateReq->bind_param("i", $request_id);
            $updateReq->execute();

            $log = $conn->prepare("
                INSERT INTO system_logs (event_type, description, event_time)
                VALUES ('seller_rejection', CONCAT('Rejected seller request for ', ?), NOW())
            ");
            $log->bind_param("s", $full_name);
            $log->execute();

            echo json_encode(['status' => 'success', 'message' => 'Seller request rejected.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Rejection failed.']);
        }
    }

    $conn->close();
}
?>
