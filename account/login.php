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
  <title>GreenBasket — Log In</title>
  <link rel="icon" type="image/png" href="../style/imgs/gb.png">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Poetsen+One&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
  /* ── Login Page — GreenBasket ── */
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

  /* ── Left panel — decorative botanical ── */
  .login-panel-left {
    flex: 0 0 46%;
    position: relative;
    background: var(--forest);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 56px 52px;
    overflow: hidden;
  }

  /* Decorative blobs */
  .login-panel-left::before {
    content: '';
    position: absolute;
    top: -120px; left: -80px;
    width: 420px; height: 420px;
    background: radial-gradient(circle, rgba(110,201,122,0.18) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  .login-panel-left::after {
    content: '';
    position: absolute;
    bottom: -80px; right: -60px;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(232,176,122,0.12) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  /* Background image tint */
  .left-bg-img {
    position: absolute;
    inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    opacity: 0.18;
    z-index: 0;
  }

  .left-content {
    position: relative;
    z-index: 1;
  }

  /* Logo on left panel */
  .left-logo {
    font-family: var(--ff-logo);
    font-size: 1.8rem;
    font-weight: 900;
    letter-spacing: -0.01em;
    margin-bottom: 48px;
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
    font-size: clamp(2.2rem, 3.5vw, 3rem);
    font-weight: 900;
    line-height: 1.1;
    color: var(--white);
    margin-bottom: 20px;
    letter-spacing: -0.02em;
  }

  .left-headline em { font-style: italic; color: var(--gb-green); }

  .left-sub {
    font-size: 0.9rem;
    line-height: 1.7;
    color: rgba(245,240,232,0.6);
    max-width: 320px;
    margin-bottom: 40px;
  }

  /* Trust pills */
  .trust-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }

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

  .trust-pill-dot {
    width: 5px; height: 5px;
    background: var(--gb-green);
    border-radius: 50%;
    flex-shrink: 0;
  }

  /* ── Right panel — form ── */
  .login-panel-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 40px;
    background: var(--parchment);
  }

  .login-form-wrap {
    width: 100%;
    max-width: 420px;
    animation: slideUp 0.7s var(--ease-smooth) both;
  }

  @keyframes slideUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  /* Form header */
  .form-header { margin-bottom: 36px; }

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
    font-size: 2.2rem;
    font-weight: 900;
    letter-spacing: -0.02em;
    color: var(--forest);
    line-height: 1.1;
  }

  .form-subtitle {
    font-size: 0.9rem;
    color: var(--muted);
    margin-top: 8px;
    line-height: 1.6;
  }

  /* Error banner */
  .error-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #FFF0F0;
    border: 1px solid #FFCDD2;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 24px;
    font-size: 0.88rem;
    color: #C62828;
  }

  .error-banner i { flex-shrink: 0; }

  /* Field groups */
  .field-group { margin-bottom: 22px; }

  .field-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--forest);
    letter-spacing: 0.04em;
    margin-bottom: 8px;
  }

  .field-wrap {
    position: relative;
  }

  .field-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--sage);
    font-size: 0.9rem;
    transition: color 0.2s;
    pointer-events: none;
  }

  .field-input {
    width: 100%;
    padding: 14px 16px 14px 44px;
    border-radius: 14px;
    border: 1.5px solid rgba(44,74,46,0.18);
    background: var(--white);
    font-family: var(--ff-body);
    font-size: 0.95rem;
    color: var(--charcoal);
    transition: border-color 0.25s, box-shadow 0.25s;
    outline: none;
  }

  .field-input:focus {
    border-color: var(--gb-green);
    box-shadow: 0 0 0 4px rgba(110,201,122,0.15);
  }

  .field-wrap:focus-within .field-icon { color: var(--gb-green); }

  /* Submit button */
  .btn-submit {
    width: 100%;
    padding: 16px;
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
    margin-top: 8px;
    transition: transform 0.25s var(--ease-bounce),
                box-shadow 0.25s;
    box-shadow: 0 4px 20px rgba(34,197,94,0.3);
  }

  .btn-submit:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 10px 32px rgba(34,197,94,0.45);
  }

  .btn-submit:active { transform: translateY(0) scale(0.99); }

  /* Footer link */
  .form-footer-link {
    text-align: center;
    margin-top: 28px;
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

  /* ── Responsive ── */
  @media (max-width: 768px) {
    body { flex-direction: column; }
    .login-panel-left {
      flex: 0 0 auto;
      padding: 40px 28px 36px;
      min-height: 220px;
    }
    .left-headline { font-size: 1.8rem; }
    .left-sub { display: none; }
    .trust-pills { display: none; }
    .login-panel-right { padding: 36px 24px; }
  }
  </style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="login-panel-left">
  <!-- Optional: add a background veg/fruit image here -->
  <!-- <img src="../style/imgs/vegi.jpg" class="left-bg-img" alt=""> -->

  <div class="left-content">
    <div class="left-logo">
      <span class="logo-green">Green</span><span class="logo-basket">Basket</span>
    </div>

    <h1 class="left-headline">
      Your table,<br><em>freshly served.</em>
    </h1>

    <p class="left-sub">
      Log back in and continue exploring home-grown vegetables and seasonal fruits — straight from Kerala's trusted local farmers.
    </p>

    <div class="trust-pills">
      <span class="trust-pill"><span class="trust-pill-dot"></span> Zero Chemicals</span>
      <span class="trust-pill"><span class="trust-pill-dot"></span> Home-Grown</span>
      <span class="trust-pill"><span class="trust-pill-dot"></span> Local Sellers</span>
    </div>
  </div>
</div>

<!-- RIGHT PANEL — FORM -->
<div class="login-panel-right">
  <div class="login-form-wrap">

    <div class="form-header">
      <div class="form-eyebrow">Welcome back</div>
      <h2 class="form-title">Log In</h2>
      <p class="form-subtitle">Good to see you again. Enter your details below.</p>
    </div>

    <?php if ($login_error): ?>
      <div class="error-banner">
        <i class="fas fa-circle-exclamation"></i>
        <?= htmlspecialchars($login_error) ?>
      </div>
    <?php endif; ?>

    <form id="loginForm" action="../session/session.php" method="POST" novalidate>

      <div class="field-group">
        <label class="field-label" for="username">Username</label>
        <div class="field-wrap">
          <i class="fas fa-user field-icon"></i>
          <input class="field-input" type="text" id="username" name="username"
                 placeholder="Enter your username" required autocomplete="username">
        </div>
      </div>

      <div class="field-group">
        <label class="field-label" for="password">Password</label>
        <div class="field-wrap">
          <i class="fas fa-lock field-icon"></i>
          <input class="field-input" type="password" id="password" name="password"
                 placeholder="Enter your password" required autocomplete="current-password">
        </div>
      </div>

      <button type="submit" class="btn-submit" name="login">
        <i class="fas fa-seedling" style="margin-right:8px"></i> Log In
      </button>

    </form>

    <p class="form-footer-link">
      Don't have an account? <a href="register.php">Register here</a>
    </p>

  </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
  const username = document.getElementById('username').value.trim();
  const password = document.getElementById('password').value.trim();
  let errorMsg = '';
  if (username.length < 3) errorMsg += 'Username must be at least 3 characters long.\n';
  if (password.length < 6) errorMsg += 'Password must be at least 6 characters long.\n';
  if (errorMsg) { e.preventDefault(); alert(errorMsg); }
});
</script>

</body>
</html>
