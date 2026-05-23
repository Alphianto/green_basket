<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$uid = $_SESSION['uid'];
$username = $_SESSION['user'];
$role = $_SESSION['role'];
?>
