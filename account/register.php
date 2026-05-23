<?php
session_start();
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

    if (empty($username) || empty($password) || empty($confirm_password) || empty($phone)) {
        $_SESSION['register_error'] = "All fields are required.";
        header("Location: register.php"); exit();
    }
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $_SESSION['register_error'] = "Phone number must be exactly 10 digits.";
        header("Location: register.php"); exit();
    }
    if (strlen($username) < 3 || strlen($username) > 16 || strpos($username, '-') !== false) {
        $_SESSION['register_error'] = "Username must be 3-16 characters and cannot contain a hyphen (-).";
        header("Location: register.php"); exit();
    }
    if (strlen($password) < 4) {
        $_SESSION['register_error'] = "Password must not exceed 10 characters.";
        header("Location: register.php"); exit();
    }
    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = "Passwords do not match.";
        header("Location: register.php"); exit();
    }

    $check = $conn->prepare("SELECT uid FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['register_error'] = "Username already exists!";
        header("Location: register.php"); exit();
    }
    $check->close();

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $insert = $conn->prepare("INSERT INTO users (username, password, phone, role, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())");
    $insert->bind_param("ssss", $username, $hashedPassword, $phone, $role);

    if ($insert->execute()) {
        $_SESSION['register_success'] = "Registration successful! Redirecting to login...";
        $insert->close();
        header("Location: register.php"); exit();
    } else {
        $_SESSION['register_error'] = "Error during registration. Please try again.";
        $insert->close();
        header("Location: register.php"); exit();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GreenBasket — Register</title>
  <link rel="icon" type="image/png" href="../style/imgs/gb.png">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Poetsen+One&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
  /* ── Register Page — GreenBasket ── */
  :root {
    --forest:    #1A3020;
    --moss:      #2C4A2E;
    --sage:      #7A9E7E;
    --mint:      #B8D4BB;
    --cream:     #F5F0E8;
    --parchment: #EDE7D9;
    --gb-green:  #6EC97A;
    --amber:     #E8B07A;
    --white:     #FFFFFF;
    --charcoal:  #1C1C1C;
    --muted:     #6B7B6D;

    --ff-display: 'Playfair Display', Georgia, serif;
    --ff-body:    'DM Sans', sans-serif;
    --ff-mono:    'DM Mono', monospace;
    --ff-logo:    'Poetsen One', sans-serif;

    --ease-smooth: cubic-bezier(0.4,0,0.2,1);
    --ease-bounce: cubic-bezier(0.34,1.56,0.64,1);
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }

  body {
    font-family: var(--ff-body);
    background: var(--parchment);
    color: var(--charcoal);
    min-height: 100vh;
    display: flex;
    align-items: stretch;
  }

  /* ── Left panel ── */
  .reg-panel-left {
    flex: 0 0 42%;
    position: relative;
    background: var(--forest);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 56px 52px;
    overflow: hidden;
  }

  .reg-panel-left::before {
    content: '';
    position: absolute;
    top: -100px; right: -60px;
    width: 380px; height: 380px;
    background: radial-gradient(circle, rgba(110,201,122,0.18) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  .reg-panel-left::after {
    content: '';
    position: absolute;
    bottom: -100px; left: -60px;
    width: 320px; height: 320px;
    background: radial-gradient(circle, rgba(232,176,122,0.1) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  .left-content { position: relative; z-index: 1; }

  .left-logo {
    font-family: var(--ff-logo);
    font-size: 1.8rem;
    font-weight: 900;
    letter-spacing: -0.01em;
    margin-bottom: 52px;
  }

  .left-logo .logo-green {
    background: linear-gradient(135deg, #5DF56A 0%, #22C55E 50%, #16A34A 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .left-logo .logo-basket { color: var(--cream); }

  .left-headline {
    font-family: var(--ff-display);
    font-size: clamp(2rem, 3vw, 2.8rem);
    font-weight: 900;
    line-height: 1.12;
    color: var(--white);
    margin-bottom: 18px;
    letter-spacing: -0.02em;
  }

  .left-headline em { font-style: italic; color: var(--gb-green); }

  .left-sub {
    font-size: 0.88rem;
    line-height: 1.7;
    color: rgba(245,240,232,0.58);
    max-width: 300px;
    margin-bottom: 36px;
  }

  .trust-pills { display: flex; flex-wrap: wrap; gap: 9px; }

  .trust-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.14);
    border-radius: 100px;
    padding: 7px 14px;
    font-size: 0.78rem;
    color: rgba(245,240,232,0.75);
    letter-spacing: 0.04em;
  }

  .trust-pill-dot { width: 5px; height: 5px; background: var(--gb-green); border-radius: 50%; flex-shrink: 0; }

  /* ── Right panel — form ── */
  .reg-panel-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 44px;
    background: var(--parchment);
    overflow-y: auto;
  }

  .reg-form-wrap {
    width: 100%;
    max-width: 440px;
    animation: slideUp 0.7s var(--ease-smooth) both;
  }

  @keyframes slideUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .form-header { margin-bottom: 32px; }

  .form-eyebrow {
    font-family: var(--ff-mono);
    font-size: 0.68rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--gb-green);
    margin-bottom: 12px;
  }

  .form-title {
    font-family: var(--ff-display);
    font-size: 2rem;
    font-weight: 900;
    letter-spacing: -0.02em;
    color: var(--forest);
    line-height: 1.1;
  }

  .form-subtitle {
    font-size: 0.88rem;
    color: var(--muted);
    margin-top: 8px;
    line-height: 1.6;
  }

  /* Messages */
  .error-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #FFF0F0;
    border: 1px solid #FFCDD2;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 22px;
    font-size: 0.88rem;
    color: #C62828;
  }

  /* Two-column grid for some fields */
  .fields-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0 20px;
  }

  .field-group { margin-bottom: 18px; }
  .field-group.full { grid-column: 1 / -1; }

  .field-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--forest);
    letter-spacing: 0.04em;
    margin-bottom: 7px;
  }

  .field-wrap { position: relative; }

  .field-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--sage);
    font-size: 0.88rem;
    transition: color 0.2s;
    pointer-events: none;
  }

  .field-input,
  .field-select {
    width: 100%;
    padding: 13px 14px 13px 42px;
    border-radius: 12px;
    border: 1.5px solid rgba(44,74,46,0.18);
    background: var(--white);
    font-family: var(--ff-body);
    font-size: 0.92rem;
    color: var(--charcoal);
    transition: border-color 0.25s, box-shadow 0.25s;
    outline: none;
    appearance: none;
  }

  .field-input:focus,
  .field-select:focus {
    border-color: var(--gb-green);
    box-shadow: 0 0 0 4px rgba(110,201,122,0.15);
  }

  .field-wrap:focus-within .field-icon { color: var(--gb-green); }

  /* Status messages under fields */
  .status-text {
    font-size: 0.78rem;
    margin-top: 5px;
    font-weight: 600;
    min-height: 16px;
    padding-left: 2px;
  }

  .available { color: #22C55E; }
  .taken     { color: #DC2626; }

  /* Submit */
  .btn-submit {
    width: 100%;
    padding: 15px;
    border: none;
    border-radius: 14px;
    background: linear-gradient(135deg, #5DF56A, #22C55E);
    color: var(--forest);
    font-family: var(--ff-body);
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    cursor: pointer;
    margin-top: 6px;
    transition: transform 0.25s var(--ease-bounce), box-shadow 0.25s;
    box-shadow: 0 4px 20px rgba(34,197,94,0.3);
  }

  .btn-submit:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 10px 32px rgba(34,197,94,0.45);
  }

  .form-footer-link {
    text-align: center;
    margin-top: 24px;
    font-size: 0.88rem;
    color: var(--muted);
  }

  .form-footer-link a {
    color: var(--moss);
    font-weight: 700;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 0.2s;
  }

  .form-footer-link a:hover { border-color: var(--moss); }

  /* Success Modal */
  .modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.55);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
  }

  .success-modal {
    background: var(--white);
    padding: 48px 40px;
    border-radius: 24px;
    text-align: center;
    box-shadow: 0 24px 60px rgba(0,0,0,0.25);
    animation: popIn 0.35s var(--ease-bounce) both;
    max-width: 360px;
    width: 90%;
  }

  @keyframes popIn {
    from { transform: scale(0.88); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
  }

  .success-modal .modal-icon {
    font-size: 3rem;
    color: var(--gb-green);
    margin-bottom: 16px;
  }

  .success-modal h3 {
    font-family: var(--ff-display);
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--forest);
    margin-bottom: 10px;
  }

  .success-modal p { font-size: 0.9rem; color: var(--muted); }

  /* Responsive */
  @media (max-width: 768px) {
    body { flex-direction: column; }
    .reg-panel-left {
      flex: 0 0 auto;
      padding: 36px 28px 32px;
      min-height: 200px;
    }
    .left-headline { font-size: 1.7rem; }
    .left-sub, .trust-pills { display: none; }
    .reg-panel-right { padding: 32px 20px; }
    .fields-grid { grid-template-columns: 1fr; }
    .field-group.full { grid-column: 1; }
  }
  </style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="reg-panel-left">
  <div class="left-content">
    <div class="left-logo">
      <span class="logo-green">Green</span><span class="logo-basket">Basket</span>
    </div>

    <h1 class="left-headline">
      Join the<br><em>fresh community.</em>
    </h1>

    <p class="left-sub">
      Create your free account and start shopping home-grown vegetables and seasonal fruits from trusted local farmers across Kerala.
    </p>

    <div class="trust-pills">
      <span class="trust-pill"><span class="trust-pill-dot"></span> Zero Chemicals</span>
      <span class="trust-pill"><span class="trust-pill-dot"></span> Home-Grown</span>
      <span class="trust-pill"><span class="trust-pill-dot"></span> Local Sellers</span>
      <span class="trust-pill"><span class="trust-pill-dot"></span> Trusted Platform</span>
    </div>
  </div>
</div>

<!-- RIGHT PANEL — FORM -->
<div class="reg-panel-right">
  <div class="reg-form-wrap">

    <div class="form-header">
      <div class="form-eyebrow">Get started free</div>
      <h2 class="form-title">Create Account</h2>
      <p class="form-subtitle">Join Green Basket — it only takes a minute.</p>
    </div>

    <?php if ($register_error): ?>
      <div class="error-banner">
        <i class="fas fa-circle-exclamation"></i>
        <?= htmlspecialchars($register_error) ?>
      </div>
    <?php endif; ?>

    <form id="registerForm" action="" method="POST" novalidate>

      <div class="fields-grid">

        <div class="field-group">
          <label class="field-label" for="username">Username</label>
          <div class="field-wrap">
            <i class="fas fa-user field-icon"></i>
            <input class="field-input" type="text" name="username" id="username"
                   placeholder="3–16 chars, no hyphens" required maxlength="16">
          </div>
          <div id="username-status" class="status-text"></div>
        </div>

        <div class="field-group">
          <label class="field-label" for="phone">Phone</label>
          <div class="field-wrap">
            <i class="fas fa-phone field-icon"></i>
            <input class="field-input" type="tel" name="phone" id="phone"
                   placeholder="10-digit number" required maxlength="10">
          </div>
          <div id="phone-status" class="status-text"></div>
        </div>

        <div class="field-group">
          <label class="field-label" for="password">Password</label>
          <div class="field-wrap">
            <i class="fas fa-lock field-icon"></i>
            <input class="field-input" type="password" name="password" id="password"
                   placeholder="Min 6 characters" required minlength="6">
          </div>
          <div id="password-status" class="status-text"></div>
        </div>

        <div class="field-group">
          <label class="field-label" for="confirm_password">Confirm Password</label>
          <div class="field-wrap">
            <i class="fas fa-lock field-icon"></i>
            <input class="field-input" type="password" name="confirm_password" id="confirm_password"
                   placeholder="Re-enter password" required maxlength="20">
          </div>
          <div id="confirm_password-status" class="status-text"></div>
        </div>

        <div class="field-group full">
          <label class="field-label" for="role">I want to join as</label>
          <div class="field-wrap">
            <i class="fas fa-id-badge field-icon"></i>
            <select class="field-select" name="role" id="role" required>
              <option value="buyer">🛒 Buyer</option>
              <option value="seller">💼 Seller</option>
            </select>
          </div>
        </div>

      </div><!-- /fields-grid -->

      <button type="submit" class="btn-submit" name="register" id="registerButton">
        <i class="fas fa-seedling" style="margin-right:8px"></i> Create Account
      </button>

    </form>

    <p class="form-footer-link">
      Already have an account? <a href="login.php">Log In</a>
    </p>

  </div>
</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModalOverlay">
  <div class="success-modal">
    <div class="modal-icon">✓</div>
    <h3 id="modal-title">Welcome aboard!</h3>
    <p id="modal-message">Registration successful. Redirecting to login in 2 seconds…</p>
  </div>
</div>

<script>
const usernameInput        = document.getElementById('username');
const phoneInput           = document.getElementById('phone');
const passwordInput        = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');
const registerForm         = document.getElementById('registerForm');
const modalOverlay         = document.getElementById('successModalOverlay');

let isUsernameValid = false, isPasswordValid = false,
    isConfirmPasswordValid = false, isPhoneValid = false,
    isUsernameAvailable = false, usernameCheckTimer;

function updateStatus(input, isValid, message) {
  const s = document.getElementById(input.id + '-status');
  s.textContent = message;
  s.className = isValid ? 'status-text available' : 'status-text taken';
}

phoneInput.addEventListener('input', () => {
  let value = phoneInput.value.replace(/\D/g, '').substring(0, 10);
  phoneInput.value = value;
  if (value.length === 10) { updateStatus(phoneInput, true, 'Phone number is valid.'); isPhoneValid = true; }
  else { updateStatus(phoneInput, false, 'Must be exactly 10 digits.'); isPhoneValid = false; }
});

passwordInput.addEventListener('input', () => {
  const p = passwordInput.value;
  if (p.length < 6) { updateStatus(passwordInput, false, 'At least 6 characters.'); isPasswordValid = false; }
  else { updateStatus(passwordInput, true, 'Password looks good.'); isPasswordValid = true; }
  validateConfirmPassword();
});

confirmPasswordInput.addEventListener('input', validateConfirmPassword);
function validateConfirmPassword() {
  const p = passwordInput.value, c = confirmPasswordInput.value;
  if (c.length === 0) { updateStatus(confirmPasswordInput, false, 'Confirm password required.'); isConfirmPasswordValid = false; }
  else if (p !== c)   { updateStatus(confirmPasswordInput, false, 'Passwords do not match.'); isConfirmPasswordValid = false; }
  else                { updateStatus(confirmPasswordInput, true, 'Passwords match.'); isConfirmPasswordValid = true; }
}

usernameInput.addEventListener('keydown', e => {
  if (e.key === '-' || e.keyCode === 189) {
    e.preventDefault();
    updateStatus(usernameInput, false, 'Username cannot contain hyphens (-).');
    isUsernameValid = false;
  }
});

usernameInput.addEventListener('keyup', () => {
  clearTimeout(usernameCheckTimer);
  const username = usernameInput.value.trim();
  const statusDiv = document.getElementById('username-status');
  if (username.length < 3) {
    updateStatus(usernameInput, false, 'Username must be at least 3 characters.');
    isUsernameValid = false; isUsernameAvailable = false; return;
  }
  isUsernameValid = true;
  statusDiv.textContent = 'Checking availability…';
  statusDiv.className = 'status-text';
  usernameCheckTimer = setTimeout(() => {
    fetch('../api/check_username.php?username=' + encodeURIComponent(username))
      .then(r => r.json())
      .then(data => {
        if (data.exists) { updateStatus(usernameInput, false, 'Username already taken.'); isUsernameAvailable = false; }
        else             { updateStatus(usernameInput, true, 'Username available!'); isUsernameAvailable = true; }
      })
      .catch(() => { updateStatus(usernameInput, false, 'Error checking username.'); isUsernameAvailable = false; });
  }, 500);
});

registerForm.addEventListener('submit', function(e) {
  validateConfirmPassword();
  usernameInput.dispatchEvent(new Event('keyup'));
  passwordInput.dispatchEvent(new Event('input'));
  phoneInput.dispatchEvent(new Event('input'));
  const isFormValid = isUsernameValid && isUsernameAvailable && isPhoneValid && isPasswordValid && isConfirmPasswordValid;
  if (!isFormValid) {
    e.preventDefault();
    if (!isUsernameValid || !isUsernameAvailable) usernameInput.focus();
    else if (!isPhoneValid) phoneInput.focus();
    else if (!isPasswordValid) passwordInput.focus();
    else if (!isConfirmPasswordValid) confirmPasswordInput.focus();
  }
});

window.onload = function() {
  const phpSuccessMessage = "<?= $register_success ?>";
  if (phpSuccessMessage) {
    modalOverlay.style.display = 'flex';
    setTimeout(() => { window.location.href = 'login.php'; }, 2000);
  }
};

document.addEventListener('DOMContentLoaded', () => {
  phoneInput.dispatchEvent(new Event('input'));
});
</script>

</body>
</html>
