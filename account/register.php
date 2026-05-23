<?php
session_start();
// Assuming connection.php and Connect() are correctly defined and connect to the database.
require '../session/connection.php';
$conn = Connect();

$register_error = $_SESSION['register_error'] ?? null;
$register_success = $_SESSION['register_success'] ?? null;
unset($_SESSION['register_error'], $_SESSION['register_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'] ?? 'buyer';

    // --- PHP Validation ---
    
    // 1. Check all required fields
    if (empty($username) || empty($password) || empty($confirm_password) || empty($phone)) {
        $_SESSION['register_error'] = "All fields are required.";
        header("Location: register.php");
        exit();
    }
    
    // 2. Validate phone (10 digits exactly, as requested by JS logic)
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $_SESSION['register_error'] = "Phone number must be exactly 10 digits.";
        header("Location: register.php");
        exit();
    }
    
    // 3. Validate username (3-16 chars, no hyphen on the server side)
    if (strlen($username) < 3 || strlen($username) > 16 || strpos($username, '-') !== false) {
        $_SESSION['register_error'] = "Username must be 3-16 characters and cannot contain a hyphen (-).";
        header("Location: register.php");
        exit();
    }

    // 4. Validate password (max 10 chars)
    if (strlen($password) < 4) {
        $_SESSION['register_error'] = "Password must not exceed 10 characters.";
        header("Location: register.php");
        exit();
    }

    // 5. Check password match
    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = "Passwords do not match.";
        header("Location: register.php");
        exit();
    }

    // 6. Check if username already exists
    $check = $conn->prepare("SELECT uid FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['register_error'] = "Username already exists!";
        header("Location: register.php");
        exit();
    }
    $check->close();

    // 7. Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // 8. Insert new user
    // The role is sanitized by using a simple selection from a known set of values.
    $insert = $conn->prepare("INSERT INTO users (username, password, phone, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
    $insert->bind_param("ssss", $username, $hashedPassword, $phone, $role);

    if ($insert->execute()) {
        // Set success message for client-side redirection
        $_SESSION['register_success'] = "Registration successful! Redirecting to login...";
        $insert->close();
        // Redirect back to register.php to display success message via JS before delayed redirect.
        header("Location: register.php");
        exit();
    } else {
        $_SESSION['register_error'] = "Error during registration. Please try again.";
        $insert->close();
        header("Location: register.php");
        exit();
    }
    
    // Close connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Green Basket - Register</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Base styles */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #f7f7f7; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .form-container { background: rgba(255,255,255,0.95); padding: 40px 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); width: 100%; max-width: 420px; animation: fadeIn 1s ease forwards; }
        
        /* Typography & Header */
        h2 { font-family: 'Poetsen One', sans-serif; font-size: 2rem; text-align: center; background: linear-gradient(90deg, #3fd734, #0c842e); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 10px; }
        .subtitle { text-align: center; font-size: 0.95rem; color: #555; margin-bottom: 20px; }
        
        /* Form Elements */
        label { display: block; margin-bottom: 15px; }
        .label-text { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        input[type="text"], input[type="password"], input[type="tel"], select { width: 100%; padding: 10px 15px; border-radius: 8px; border: 1.5px solid #ccc; font-size: 1rem; transition: all 0.3s ease; }
        input:focus, select:focus { border-color: #2eb535; box-shadow: 0 0 5px rgba(46,181,53,0.3); outline: none; }
        
        /* Buttons */
        .submit-button { width: 100%; padding: 12px; border: none; border-radius: 25px; background: linear-gradient(90deg, #1dcc14, #38dc2c); color: white; font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: all 0.4s ease; margin-top: 10px; }
        .submit-button:hover { background: linear-gradient(270deg, #1dcc14, #38dc2c); transform: scale(1.02); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }

        /* Messages and Statuses */
        .error-message, .php-error-message, .php-success-message { padding: 10px 12px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-size: 0.9rem; }
        .php-error-message { background: #ffdddd; color: #d8000c; }
        .php-success-message { background: #ddffdd; color: #2e7d32; }
        
        /* Real-time Statuses */
        .status-text { font-size: 0.85rem; margin-top: 5px; font-weight: 600; min-height: 18px; }
        .available { color: #2eb535; }
        .taken, .error-message { color: #d8000c; } /* Use error-message for local JS errors too */
        
        /* Footer */
        .form-footer { text-align: center; margin-top: 15px; font-size: 0.9rem; }
        .form-footer a { color: #0c842e; text-decoration: none; font-weight: 600; }
        .form-footer a:hover { text-decoration: underline; }

        /* Custom Success Modal (No Pop-up for error) */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6);
            display: none; justify-content: center; align-items: center; z-index: 1000;
        }
        .success-modal {
            background: white; padding: 30px; border-radius: 12px; text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); transform: scale(0.9);
            animation: modalPop 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards;
        }
        .success-modal .icon { font-size: 3rem; color: #1dcc14; margin-bottom: 15px; }
        .success-modal h3 { font-size: 1.5rem; color: #333; margin-bottom: 10px; }
        .success-modal p { color: #555; }

        /* Animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes modalPop { to { transform: scale(1); } }
    </style>
</head>
<body>
<div class="form-container">
    <form class="auth-form" id="registerForm" action="" method="POST" novalidate>
        <h2>Create Account</h2>
        <p class="subtitle">Join Green Basket today.</p>

        <?php if ($register_error): ?>
            <div class="php-error-message"><?= htmlspecialchars($register_error) ?></div>
        <?php endif; ?>

        <label>
            <span class="label-text">Username </span>
            <input type="text" name="username" id="username" placeholder="Enter username (3-16 chars, no hyphens)" required maxlength="16">
            <div id="username-status" class="status-text"></div>
        </label>

        <label>
            <span class="label-text">Phone </span>
            <input type="tel" name="phone" id="phone" placeholder="Enter 10-digit phone number" required maxlength="10">
            <div id="phone-status" class="status-text"></div>
        </label>

        <label>
            <span class="label-text">Password</span>
            <input type="password" name="password" id="password" placeholder="Enter Password " required minlength="6">
            <div id="password-status" class="status-text"></div>
        </label>

        <label>
            <span class="label-text">Confirm Password</span>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter password" required maxlength="20">
            <div id="confirm_password-status" class="status-text"></div>
        </label>

        <label>
            <span class="label-text">Role</span>
            <select name="role" required>
                <option value="buyer">Buyer</option>
                <option value="seller">Seller</option>
            </select>
        </label>

        <button type="submit" class="submit-button" name="register" id="registerButton">Register</button>

        <p class="form-footer">Already have an account? <a href="login.php">Log In</a></p>
    </form>
</div>

<!-- Custom Success Modal -->
<div class="modal-overlay" id="successModalOverlay">
    <div class="success-modal">
        <div class="icon">✓</div>
        <h3 id="modal-title">Registration Successful!</h3>
        <p id="modal-message">You will be redirected to the login page in 2 seconds.</p>
    </div>
</div>


<script>
// Elements
const usernameInput = document.getElementById('username');
const phoneInput = document.getElementById('phone');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');
const registerForm = document.getElementById('registerForm');
const modalOverlay = document.getElementById('successModalOverlay');

// --- 1. Real-time Validation Functions ---

let isUsernameValid = false;
let isPasswordValid = false;
let isConfirmPasswordValid = false;
let isPhoneValid = false;
let isUsernameAvailable = false;
let usernameCheckTimer;

function updateStatus(input, isValid, message) {
    const statusDiv = document.getElementById(input.id + '-status');
    statusDiv.textContent = message;
    statusDiv.className = isValid ? 'status-text available' : 'status-text taken';
}

// Validation for Phone
phoneInput.addEventListener('input', () => {
    // Restrict to digits only and limit to 10
    let value = phoneInput.value.replace(/\D/g, '').substring(0, 10);
    phoneInput.value = value;
    
    if (value.length === 10) {
        updateStatus(phoneInput, true, "Phone number is valid.");
        isPhoneValid = true;
    } else {
        updateStatus(phoneInput, false, "Must be exactly 10 digits.");
        isPhoneValid = false;
    }
});

// Validation for Password
passwordInput.addEventListener('input', () => {
    const password = passwordInput.value;
    
    // Max length is enforced by HTML maxlength="10"
    if (password.length < 6) {
        updateStatus(passwordInput, false, "Password must be at least 6 characters.");
        isPasswordValid = false;
    } else {
        updateStatus(passwordInput, true, "Password looks good.");
        isPasswordValid = true;
    }
    // Re-check confirm password whenever main password changes
    validateConfirmPassword();
});

// Validation for Confirm Password
confirmPasswordInput.addEventListener('input', validateConfirmPassword);
function validateConfirmPassword() {
    const password = passwordInput.value;
    const confirm = confirmPasswordInput.value;

    if (confirm.length === 0) {
        updateStatus(confirmPasswordInput, false, "Confirm password is required.");
        isConfirmPasswordValid = false;
    } else if (password !== confirm) {
        updateStatus(confirmPasswordInput, false, "Passwords do not match.");
        isConfirmPasswordValid = false;
    } else {
        updateStatus(confirmPasswordInput, true, "Passwords match.");
        isConfirmPasswordValid = true;
    }
}

// Validation and Availability Check for Username
usernameInput.addEventListener('keydown', (e) => {
    // Prevent typing the hyphen (-) character (key code 189 or key value check)
    if (e.key === '-' || e.keyCode === 189) {
        e.preventDefault();
        updateStatus(usernameInput, false, "Username cannot contain hyphens (-).");
        isUsernameValid = false;
    }
});

usernameInput.addEventListener('keyup', () => {
    clearTimeout(usernameCheckTimer);
    const username = usernameInput.value.trim();
    const statusDiv = document.getElementById('username-status');

    if (username.length < 3) {
        updateStatus(usernameInput, false, "Username must be at least 3 characters.");
        isUsernameValid = false;
        isUsernameAvailable = false;
        return;
    }


    // Set valid state until availability check completes
    isUsernameValid = true;
    statusDiv.textContent = "Checking availability...";
    statusDiv.className = "status-text"; 

    // Debounce the fetch request
    usernameCheckTimer = setTimeout(() => {
        fetch('../api/check_username.php?username=' + encodeURIComponent(username))
            .then(res => res.json())
            .then(data => {
                if (data.exists) {
                    updateStatus(usernameInput, false, "Username already taken.");
                    isUsernameAvailable = false;
                } else {
                    updateStatus(usernameInput, true, "Username available!");
                    isUsernameAvailable = true;
                }
            })
            .catch(() => {
                updateStatus(usernameInput, false, "Error checking username.");
                isUsernameAvailable = false;
            });
    }, 500); // Wait 500ms after last keypress
});


// --- 2. Form Submission Handler (Prevents popups) ---

registerForm.addEventListener('submit', function(e) {
    // Run all validations one last time
    validateConfirmPassword();
    // Trigger keyup on load to ensure initial state validation is visible
    usernameInput.dispatchEvent(new Event('keyup'));
    passwordInput.dispatchEvent(new Event('input'));
    phoneInput.dispatchEvent(new Event('input'));
    
    // Check all boolean flags for a successful submission
    const isFormValid = isUsernameValid && isUsernameAvailable && isPhoneValid && isPasswordValid && isConfirmPasswordValid;

    if (!isFormValid) {
        e.preventDefault(); // Stop form submission
        // Optionally scroll to the first invalid field for better UX
        if (!isUsernameValid || !isUsernameAvailable) usernameInput.focus();
        else if (!isPhoneValid) phoneInput.focus();
        else if (!isPasswordValid) passwordInput.focus();
        else if (!isConfirmPasswordValid) confirmPasswordInput.focus();
    }
    // If the form is valid, it will submit to the PHP script
});


// --- 3. Success Modal and Redirect Logic ---

window.onload = function() {
    // Get the PHP success message
    const phpSuccessMessage = "<?= $register_success ?>";
    
    if (phpSuccessMessage) {
        // Show the custom success modal
        modalOverlay.style.display = 'flex';
        
        // Redirect after 2 seconds
        setTimeout(() => {
            window.location.href = "login.php";
        }, 2000);
    }
};

// Initial state check for UX
document.addEventListener('DOMContentLoaded', () => {
    // Set initial phone mask/length
    phoneInput.dispatchEvent(new Event('input'));
    // Do not run other validations immediately to avoid showing errors on an empty form.
});
</script>
</body>
</html>
