<?php
session_start();
$login_error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Green Basket - Log In</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #f7f7f7; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .form-container { 
            background: rgba(255,255,255,0.95); 
            padding: 40px 30px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.15); 
            width: 100%; 
            max-width: 400px; 
            animation: fadeIn 0.8s ease forwards;
        }
        .auth-form h2 { 
            font-family: 'Poetsen One', sans-serif; 
            font-size: 2rem; 
            text-align: center; 
            background: linear-gradient(90deg, #3fd734, #0c842e); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            margin-bottom: 10px;
        }
        .subtitle { text-align: center; font-size: 0.95rem; color: #555; margin-bottom: 20px; }
        label { display: block; margin-bottom: 15px; }
        .label-text { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 10px 15px; border-radius: 8px; border: 1.5px solid #ccc; font-size: 1rem; transition: all 0.3s ease;
        }
        input:focus { border-color: #2eb535; box-shadow: 0 0 5px rgba(46,181,53,0.3); outline: none; }
        .submit-button { 
            width: 100%; 
            padding: 12px; 
            border: none; 
            border-radius: 25px; 
            background: linear-gradient(90deg, #1dcc14, #38dc2c); 
            color: white; 
            font-size: 1.1rem; 
            font-weight: 600; 
            cursor: pointer; 
            transition: all 0.4s ease; 
            margin-top: 10px; 
        }
        .submit-button:hover { background: linear-gradient(270deg, #1dcc14, #38dc2c); transform: scale(1.05); }
        .form-footer { text-align: center; margin-top: 15px; font-size: 0.9rem; }
        .form-footer a { color: #0c842e; text-decoration: none; font-weight: 600; }
        .form-footer a:hover { text-decoration: underline; }
        .error-message { background: #ffdddd; color: #d8000c; padding: 10px 12px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-size: 0.9rem; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="form-container">
    <form class="auth-form" id="loginForm" action="../session/session.php" method="POST" novalidate>
        <h2>Welcome Back</h2>
        <p class="subtitle">Log in to access your Green Basket account.</p>

        <?php if ($login_error): ?>
            <div class="error-message"><?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>

        <label for="username">
            <span class="label-text">Username</span>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>
        </label>

        <label for="password">
            <span class="label-text">Password</span>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </label>

        <button type="submit" class="submit-button" name="login">Log In</button>

        <p class="form-footer">
            Don’t have an account? <a href="register.php">Register here</a>
        </p>
    </form>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    let errorMsg = '';

    if (username.length < 3) {
        errorMsg += 'Username must be at least 3 characters long.\n';
    }
    if (password.length < 6) {
        errorMsg += 'Password must be at least 6 characters long.\n';
    }

    if (errorMsg) {
        e.preventDefault();
        alert(errorMsg);
    }
});
</script>

</body>
</html>
