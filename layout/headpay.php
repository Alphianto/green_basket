<?php include __DIR__ . '/../session/init.php'; ?>

<header class="site-header2">
  <div class="site-logo"><a href="/index.php">
    <span class="logo-green1">Green</span>
    <span class="logo-basket1">Basket</span></a>
  </div>
  <nav>
    <ul class="nav-menu1">
      <li><a href="/index.php">home</a></li>
      <li><a href="/layout/about.php">about</a></li>
      <li><a href="/contact.php">contact</a></li>

      <?php if (isset($_SESSION['user'])): ?>
        <li class="profile-dropdown1">
          <button class="btn-profile-style1" id="profileBtn1" aria-haspopup="true" aria-expanded="false">
                <?= htmlspecialchars($_SESSION['user'] ?? 'Profile'); ?> <span class="dropdown-icon1">▾</span>
          </button>

          <ul class="dropdown-link-group1" id="dropdownMenu1" role="menu" aria-hidden="true">
            <li><a href="/account/profile.php"><span class="list-icon1">👤</span> My Profile</a></li>
            <li><a href="/shop/orders.php"><span class="list-icon1">📦</span> Orders</a></li>
            <li><a href="/shop/cart.php"><span class="list-icon1">🛒</span> Cart</a></li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller'): ?>
              <li><a href="/account/seller_dashboard.php"><span class="list-icon">💼</span> Seller Dashboard</a></li>
            <?php else: ?>
              <li><a href="/seller/register.php"><span class="list-icon">💼</span> Become a Seller</a></li>
            <?php endif; ?>
            <li class="divider1"></li>
            <li><a href="/account/logout.php"><span class="list-icon1">➡️</span> Logout</a></li>
          </ul>
        </li>
      <?php else: ?>
        <li class="login-li1">
          <a href="/account/login.php" class="btn-gradient1">Login</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const profileBtn = document.getElementById('profileBtn');
  const dropdownMenu = document.getElementById('dropdownMenu');

  if (!profileBtn || !dropdownMenu) return;

  function openMenu() {
    dropdownMenu.classList.add('open');
    dropdownMenu.setAttribute('aria-hidden', 'false');
    profileBtn.setAttribute('aria-expanded', 'true');
    profileBtn.classList.add('open');
  }
  function closeMenu() {
    dropdownMenu.classList.remove('open');
    dropdownMenu.setAttribute('aria-hidden', 'true');
    profileBtn.setAttribute('aria-expanded', 'false');
    profileBtn.classList.remove('open');
  }
  function toggleMenu() {
    if (dropdownMenu.classList.contains('open')) closeMenu();
    else openMenu();
  }

  profileBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    toggleMenu();
  });

  dropdownMenu.addEventListener('click', function (e) {
    e.stopPropagation();
    // Uncomment the next line if you want menu to close when clicking a link:
    // if (e.target.closest('a')) closeMenu();
  });

  document.addEventListener('click', function () {
    closeMenu();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeMenu();
  });
});
</script>
