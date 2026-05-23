<?php
session_start();
// Only remove checkout-related data
unset($_SESSION['checkout_start'], $_SESSION['checkout_products'], $_SESSION['checkout_total']);
echo json_encode(['status' => 'checkout_session_cleared']);
