<?php
session_start();
require_once __DIR__ . '/../session/connection.php';
$conn = Connect();
if (!$conn) die("DB Connection Failed");

// --- Ensure user is logged in and is a buyer ---
if (!isset($_SESSION['uid']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../index.php");
    exit();
}

$uid = (int)$_SESSION['uid'];
$error = '';
$success = '';

// --- Check if buyer has profile ---
$profile_check_query = "SELECT full_name, email FROM user_profiles WHERE uid = ? LIMIT 1";
$stmt = $conn->prepare($profile_check_query);
if (!$stmt) die("SQL Error (Profile Check): " . $conn->error);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: edit_profile.php');
    exit();
}
$profile = $result->fetch_assoc();
$stmt->close();

// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Verify user credentials
        $stmt = $conn->prepare("SELECT uid, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Check if already requested
                $check = $conn->prepare("SELECT request_status FROM seller_requests WHERE buyer_id = ?");
                $check->bind_param("i", $uid);
                $check->execute();
                $check_res = $check->get_result();

                if ($check_res->num_rows > 0) {
                    $row = $check_res->fetch_assoc();
                    if ($row['request_status'] === 'pending') {
                        $error = "You already have a pending request. Please wait for admin approval.";
                    } elseif ($row['request_status'] === 'approved') {
                        $error = "Your seller request is already approved! re-login";
                    } elseif ($row['request_status'] === 'rejected') {
                        $error = "Your previous request was rejected. You can try again later.";
                    }
                } else {
                    // Insert new request
                    $insert = $conn->prepare("
                        INSERT INTO seller_requests (buyer_id, full_name, email)
                        VALUES (?, ?, ?)
                    ");
                    $insert->bind_param("iss", $uid, $profile['full_name'], $profile['email']);

                    if ($insert->execute()) {
                        echo "<script>
                            alert('✅ Request sent to Green Basket administrator. Please wait for approval.');
                            window.location.href = '../index.php';
                        </script>";
                        exit();
                    } else {
                        $error = "Failed to send request. Try again later.";
                    }
                    $insert->close();
                }
                $check->close();
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "Username not found.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Seller - Green Basket</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Poetsen+One&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #c9f5ce, #f0fff1);
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh;
        }
        .form-container {
            background: white; padding: 40px 35px; border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            width: 100%; max-width: 420px; animation: fadeIn 1s ease forwards;
        }
        h2 {
            font-family: 'Poetsen One', sans-serif;
            font-size: 2rem; text-align: center;
            background: linear-gradient(90deg, #3fd734, #0c842e);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }
        p.subtitle { text-align: center; color: #555; font-size: 0.95rem; margin-bottom: 25px; }
        label { display: block; margin-bottom: 15px; }
        .label-text { display: block; font-weight: 600; color: #333; margin-bottom: 6px; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 10px 15px; border-radius: 8px;
            border: 1.5px solid #ccc; font-size: 1rem;
            transition: all 0.3s ease;
        }
        input:focus { border-color: #2eb535; box-shadow: 0 0 6px rgba(46,181,53,0.3); outline: none; }
        .submit-button {
            width: 100%; padding: 12px; border: none; border-radius: 25px;
            background: linear-gradient(90deg, #1dcc14, #38dc2c);
            color: white; font-size: 1.1rem; font-weight: 600;
            cursor: pointer; transition: 0.3s; margin-top: 15px;
        }
        .submit-button:hover { transform: scale(1.05); background: linear-gradient(270deg, #1dcc14, #38dc2c); }
        .error-message {
            padding: 10px 12px; border-radius: 8px; text-align: center;
            font-size: 0.95rem; margin-bottom: 15px;
            background: #ffdddd; color: #d8000c;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="form-container">
    <form id="roleForm" method="POST" action="">
        <h2>Become a Seller</h2>
        <p class="subtitle">Send your request to the Green Basket administrator for approval.</p>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <label>
            <span class="label-text">Username</span>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>
        </label>

        <label>
            <span class="label-text">Password</span>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </label>

        <button type="submit" class="submit-button">Send Request</button>
    </form>
</div>

<script>
document.getElementById('roleForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    if (username.length < 3 || password.length < 6) {
        e.preventDefault();
        alert('Please enter valid username and password.');
    }
});
</script>
</body>
</html>
