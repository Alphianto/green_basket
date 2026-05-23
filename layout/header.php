<?php include __DIR__ . '/../session/init.php'; ?>

<style>
/* ── Header Variables (mirrors index.php tokens) ── */
:root {
  --moss:      #2C4A2E;
  --forest:    #1A3020;
  --sage:      #7A9E7E;
  --mint:      #B8D4BB;
  --cream:     #F5F0E8;
  --amber-light: #E8B07A;
  --ff-display: 'Playfair Display', Georgia, serif;
  --ff-body:    'DM Sans', sans-serif;
  --ff-mono:    'DM Mono', monospace;
  --nav-h:     72px;
  --ease-smooth: cubic-bezier(0.4,0,0.2,1);
  --ease-bounce: cubic-bezier(0.34,1.56,0.64,1);
}

/* ── Base header shell ── */
.site-header {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 1000;
  height: var(--nav-h);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 48px;
  transition: background 0.4s var(--ease-smooth),
              box-shadow 0.4s var(--ease-smooth);
}



.site-header.scrolled {
  background: rgba(26,48,32,0.96);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 2px 32px rgba(0,0,0,0.18);
}

/* ── Logo ── */
.site-logo a {
  display: flex;
  align-items: baseline;
  font-family: var(--ff-display);
  font-size: 1.6rem;
  font-weight: 900;
  letter-spacing: -0.02em;
  line-height: 1;
  gap: 0;
}

.logo-green  { color: #6EC97A; }
.logo-basket { color: var(--cream); }

/* ── Nav ── */
.nav-menu {
  display: flex;
  align-items: center;
  gap: 8px;
  list-style: none;
  padding: 0; margin: 0;
}

.nav-menu > li > a {
  font-family: var(--ff-body);
  font-size: 0.85rem;
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(245,240,232,0.85);
  padding: 8px 16px;
  border-radius: 100px;
  text-decoration: none;
  transition: color 0.2s, background 0.2s;
}

.nav-menu > li > a:hover {
  color: #fff;
  background: rgba(255,255,255,0.1);
}

/* ── Login button ── */
.btn-gradient {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-family: var(--ff-body);
  font-size: 0.82rem;
  font-weight: 600;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--forest);
  background: linear-gradient(135deg, #6EC97A, #4AAD58);
  padding: 10px 22px;
  border-radius: 100px;
  text-decoration: none;
  transition: transform 0.2s var(--ease-bounce), box-shadow 0.2s;
  box-shadow: 0 3px 16px rgba(78,173,88,0.3);
}

.btn-gradient:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(110,201,122,0.45);
}

/* ── Profile Dropdown ── */
.profile-dropdown { position: relative; }

.btn-profile-style {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 100px;
  color: var(--cream);
  font-family: var(--ff-body);
  font-size: 0.85rem;
  font-weight: 500;
  padding: 8px 18px;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
}

.btn-profile-style:hover,
.btn-profile-style.open {
  background: rgba(255,255,255,0.2);
  border-color: rgba(255,255,255,0.4);
}

.dropdown-icon {
  font-size: 0.68rem;
  transition: transform 0.25s;
}
.btn-profile-style.open .dropdown-icon { transform: rotate(180deg); }

.dropdown-link-group {
  position: absolute;
  top: calc(100% + 14px);
  right: 0;
  min-width: 220px;
  background: var(--forest);
  border: 1px solid rgba(110,201,122,0.15);
  border-radius: 18px;
  overflow: hidden;
  list-style: none;
  padding: 6px 0;
  box-shadow: 0 20px 50px rgba(0,0,0,0.35);
  opacity: 0;
  pointer-events: none;
  transform: translateY(-8px) scale(0.97);
  transform-origin: top right;
  transition: opacity 0.22s var(--ease-smooth),
              transform 0.22s var(--ease-smooth);
}

.dropdown-link-group.open {
  opacity: 1;
  pointer-events: all;
  transform: translateY(0) scale(1);
}

.dropdown-link-group li a {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 11px 18px;
  font-family: var(--ff-body);
  font-size: 0.88rem;
  color: var(--mint);
  text-decoration: none;
  transition: background 0.15s, color 0.15s;
}

.dropdown-link-group li a:hover {
  background: rgba(255,255,255,0.06);
  color: #fff;
}

.list-icon { font-size: 1rem; width: 20px; text-align: center; }

.divider {
  height: 1px;
  background: rgba(255,255,255,0.07);
  margin: 4px 18px;
}

/* ── Mobile: hide nav links, keep actions ── */
@media (max-width: 768px) {
  .site-header { padding: 0 20px; }
  .nav-menu > li:not(.login-li):not(.profile-dropdown) { display: none; }
}
</style>

<header class="site-header transparent" id="siteHeader">
  <div class="site-logo">
    <a href="/index.php" aria-label="GreenBasket Home">
      <span class="logo-green">Green</span><span class="logo-basket">Basket</span>
    </a>
  </div>

  <nav aria-label="Main navigation">
    <ul class="nav-menu">
      <li><a href="/index.php">Home</a></li>
      <li><a href="/layout/about.php">About</a></li>
      <li><a href="/contact.php">Contact</a></li>

      <?php if (isset($_SESSION['user'])): ?>
        <li class="profile-dropdown">
          <button class="btn-profile-style" id="profileBtn" aria-haspopup="true" aria-expanded="false">
            <?= htmlspecialchars($_SESSION['user'] ?? 'Profile'); ?>
            <span class="dropdown-icon">▾</span>
          </button>

          <ul class="dropdown-link-group" id="dropdownMenu" role="menu" aria-hidden="true">
            <li role="menuitem"><a href="/account/profile.php"><span class="list-icon">👤</span> My Profile</a></li>
            <li role="menuitem"><a href="/shop/orders.php"><span class="list-icon">📦</span> Orders</a></li>
            <li role="menuitem"><a href="/shop/cart.php"><span class="list-icon">🛒</span> Cart</a></li>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'seller'): ?>
              <li role="menuitem"><a href="/account/seller_dashboard.php"><span class="list-icon">💼</span> Seller Dashboard</a></li>
            <?php else: ?>
              <li role="menuitem"><a href="/account/changerole.php"><span class="list-icon">💼</span> Become a Seller</a></li>
            <?php endif; ?>

            <li role="separator"><div class="divider"></div></li>
            <li role="menuitem"><a href="/account/logout.php"><span class="list-icon">➡️</span> Logout</a></li>
          </ul>
        </li>

      <?php else: ?>
        <li class="login-li">
          <a href="/account/login.php" class="btn-gradient">
            <i class="fas fa-seedling"></i> Login
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

<!-- Reserve space so content starts below fixed header -->
<div style="height: var(--nav-h, 72px);"></div>

<script>
// Scroll-aware header
(function () {
  const header = document.getElementById('siteHeader');
  if (!header) return;
  window.addEventListener('scroll', function () {
    if (window.scrollY > 40) {
      header.classList.remove('transparent');
      header.classList.add('scrolled');
    } else {
      header.classList.add('transparent');
      header.classList.remove('scrolled');
    }
  }, { passive: true });
})();

// Profile dropdown
document.addEventListener('DOMContentLoaded', function () {
  const btn  = document.getElementById('profileBtn');
  const menu = document.getElementById('dropdownMenu');
  if (!btn || !menu) return;

  const open  = () => { menu.classList.add('open');    btn.classList.add('open');    btn.setAttribute('aria-expanded','true');  menu.setAttribute('aria-hidden','false'); };
  const close = () => { menu.classList.remove('open'); btn.classList.remove('open'); btn.setAttribute('aria-expanded','false'); menu.setAttribute('aria-hidden','true');  };
  const toggle = () => menu.classList.contains('open') ? close() : open();

  btn.addEventListener('click', e => { e.stopPropagation(); toggle(); });
  menu.addEventListener('click', e => e.stopPropagation());
  document.addEventListener('click', close);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
});
</script>
