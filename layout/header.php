<?php include __DIR__ . '/../session/init.php'; ?>

<style>
/* ── Header — GreenBasket ── */
:root {
  --moss:        #2C4A2E;
  --forest:      #1A3020;
  --sage:        #7A9E7E;
  --mint:        #B8D4BB;
  --cream:       #F5F0E8;
  --amber-light: #E8B07A;
  --gb-green:    #6EC97A;
  --ff-display:  'Playfair Display', Georgia, serif;
  --ff-body:     'DM Sans', sans-serif;
  --nav-h:       72px;
  --ease-smooth: cubic-bezier(0.4,0,0.2,1);
  --ease-bounce: cubic-bezier(0.34,1.56,0.64,1);
}

/* ── Shell ── */
.site-header {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 1000;
  height: var(--nav-h);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 52px;
  background: transparent;
  transition: background 0.4s var(--ease-smooth),
              box-shadow 0.4s var(--ease-smooth),
              backdrop-filter 0.4s;
}

.site-header.scrolled {
  background: rgba(26,48,32,0.97);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  box-shadow: 0 2px 40px rgba(0,0,0,0.22);
}

/* ── Logo ── */
.site-logo a {
  display: flex;
  align-items: baseline;
  font-family: var(--ff-display);
  font-size: 1.65rem;
  font-weight: 900;
  letter-spacing: -0.02em;
  text-decoration: none;
}

/* Bright gradient "Green" — as in Poetsen One / Lemon style */
.logo-green {
  background: linear-gradient(135deg, #5DF56A 0%, #22C55E 45%, #16A34A 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-family: 'Lemon', cursive;
  font-weight: 900;
}

.logo-basket {
  color: var(--cream);
  font-family:  'Playfair Display', Georgia, serif;
}

/* ── Nav ── */
.nav-menu {
  display: flex;
  align-items: center;
  gap: 4px;
  list-style: none;
  padding: 0; margin: 0;
}

.nav-menu > li > a {
  font-family: var(--ff-body);
  font-size: 0.83rem;
  font-weight: 500;
  letter-spacing: 0.09em;
  text-transform: uppercase;
  color: rgba(245,240,232,0.88);
  padding: 9px 16px;
  border-radius: 100px;
  text-decoration: none;
  transition: color 0.2s, background 0.2s;
}

.nav-menu > li > a:hover {
  color: #fff;
  background: rgba(255,255,255,0.12);
}

/* ── Login CTA ── */
.btn-gradient {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-family: var(--ff-body);
  font-size: 0.82rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--forest) !important;
  background: linear-gradient(135deg, #5DF56A, #22C55E);
  padding: 10px 24px;
  border-radius: 100px;
  text-decoration: none;
  transition: transform 0.25s var(--ease-bounce), box-shadow 0.25s;
  box-shadow: 0 3px 18px rgba(78,197,88,0.35);
  -webkit-text-fill-color: var(--forest) !important;
}

.btn-gradient:hover {
  transform: translateY(-2px) scale(1.04);
  box-shadow: 0 8px 24px rgba(110,201,122,0.5);
}

/* ── Profile Dropdown ── */
.profile-dropdown { position: relative; }

.btn-profile-style {
  display: flex;
  align-items: center;
  gap: 9px;
  background: rgba(255,255,255,0.12);
  border: 1px solid rgba(255,255,255,0.22);
  border-radius: 100px;
  color: var(--cream);
  font-family: var(--ff-body);
  font-size: 0.85rem;
  font-weight: 500;
  padding: 9px 20px;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
}

.btn-profile-style:hover,
.btn-profile-style.open {
  background: rgba(255,255,255,0.22);
  border-color: rgba(110,201,122,0.5);
}

.dropdown-icon {
  font-size: 0.68rem;
  transition: transform 0.25s var(--ease-smooth);
  display: inline-block;
}

.btn-profile-style.open .dropdown-icon { transform: rotate(180deg); }

/* Dropdown menu */
.dropdown-link-group {
  position: absolute;
  top: calc(100% + 16px);
  right: 0;
  min-width: 230px;
  background: var(--forest);
  border: 1px solid rgba(110,201,122,0.18);
  border-radius: 20px;
  overflow: hidden;
  list-style: none;
  padding: 8px 0;
  box-shadow: 0 24px 60px rgba(0,0,0,0.4);

  /* Hidden state */
  opacity: 0;
  pointer-events: none;
  transform: translateY(-10px) scale(0.96);
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
  gap: 11px;
  padding: 12px 20px;
  font-family: var(--ff-body);
  font-size: 0.88rem;
  color: var(--mint);
  text-decoration: none;
  transition: background 0.15s, color 0.15s;
}

.dropdown-link-group li a:hover {
  background: rgba(110,201,122,0.1);
  color: #fff;
}

.list-icon { font-size: 1.05rem; width: 22px; text-align: center; flex-shrink: 0; }

.divider {
  height: 1px;
  background: rgba(255,255,255,0.08);
  margin: 5px 16px;
}

/* ── Mobile ── */
@media (max-width: 768px) {
  .site-header { padding: 0 20px; }
  .nav-menu > li:not(.login-li):not(.profile-dropdown) { display: none; }
}
</style>

<header class="site-header" id="siteHeader">
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

<!-- NO spacer div here — hero covers full viewport including header area -->

<script>
(function () {
  const header = document.getElementById('siteHeader');
  if (!header) return;

  function syncHeader() {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }

  window.addEventListener('scroll', syncHeader, { passive: true });
  syncHeader();
})();

document.addEventListener('DOMContentLoaded', function () {
  const btn  = document.getElementById('profileBtn');
  const menu = document.getElementById('dropdownMenu');
  if (!btn || !menu) return;

  const open  = () => {
    menu.classList.add('open');
    btn.classList.add('open');
    btn.setAttribute('aria-expanded', 'true');
    menu.setAttribute('aria-hidden', 'false');
  };
  const close = () => {
    menu.classList.remove('open');
    btn.classList.remove('open');
    btn.setAttribute('aria-expanded', 'false');
    menu.setAttribute('aria-hidden', 'true');
  };

  btn.addEventListener('click', e => { e.stopPropagation(); menu.classList.contains('open') ? close() : open(); });
  menu.addEventListener('click', e => e.stopPropagation());
  document.addEventListener('click', close);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
});
</script>
