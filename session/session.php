<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Input validation
    if (empty($_POST['username']) || empty($_POST['password'])) {
        $_SESSION['login_error'] = "Please enter both username and password.";
        header("Location: ../account/login.php");
        exit;
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // DB connection
    require_once __DIR__ . '/connection.php';
    $conn = Connect();

    if (!$conn) {
        $_SESSION['login_error'] = "Database connection failed.";
        header("Location: ../account/login.php");
        exit;
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT uid, username, password, role, status FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if ($user['status'] === 'banned') {
            $_SESSION['login_error'] = "Your account has been banned.";
        } elseif ($user['status'] === 'inactive') {
            $_SESSION['login_error'] = "Your account is inactive.";
        } elseif (password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['uid'] = $user['uid'];
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/admin_dashboard.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            $_SESSION['login_error'] = "Incorrect password.";
        }
    } else {
        $_SESSION['login_error'] = "Username not found.";
    }

    $stmt->close();
    $conn->close();

    // Redirect back with error
    header("Location: ../account/login.php");
    exit;
}
?>
