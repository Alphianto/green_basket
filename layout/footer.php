<style>
/* ── Footer — GreenBasket ── */
:root {
  --footer-bg:          #0E1F11;
  --footer-surface:     #152018;
  --footer-border:      rgba(110,201,122,0.1);
  --footer-text:        rgba(245,240,232,0.48);
  --footer-text-strong: #F5F0E8;
  --footer-green:       #6EC97A;
  --footer-amber:       #E8B07A;
  --forest-footer:      #1A3020;
  --ff-display-f:       'Playfair Display', Georgia, serif;
  --ff-body-f:          'DM Sans', sans-serif;
  --ff-mono-f:          'DM Mono', monospace;
  --ease-bounce-f:      cubic-bezier(0.34,1.56,0.64,1);
}

footer {
  background: var(--footer-bg);
  color: var(--footer-text-strong);
  font-family: var(--ff-body-f);
  width: 100%;
}

/* ── Top grid ── */
.footer-main {
  display: grid;
  grid-template-columns: 1.8fr 1fr 1fr 1fr;
  gap: 60px;
  padding: 84px 80px 60px;
  border-bottom: 1px solid var(--footer-border);
}

/* ── Brand column ── */
.footer-logo {
  font-family: var(--ff-display-f);
  font-size: 2rem;
  font-weight: 900;
  letter-spacing: -0.02em;
  margin-bottom: 18px;
  line-height: 1;
}

.footer-logo .logo-green {
  background: linear-gradient(135deg, #5DF56A 0%, #22C55E 45%, #16A34A 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  background-clip: text;
  font-family: 'Lemon', cursive;
}

.footer-logo .logo-basket {
  color: var(--footer-text-strong);
  font-family:  'Playfair Display', Georgia, serif;
}

.footer-tagline {
  font-size: 0.88rem;
  line-height: 1.8;
  color: var(--footer-text);
  max-width: 295px;
  margin-bottom: 32px;
}

/* Newsletter */
.newsletter-label {
  font-family: var(--ff-mono-f);
  font-size: 0.68rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--footer-green);
  margin-bottom: 12px;
}

.footer-newsletter {
  display: flex;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 14px;
  overflow: hidden;
  max-width: 308px;
  transition: border-color 0.2s;
}

.footer-newsletter:focus-within { border-color: rgba(110,201,122,0.4); }

.footer-newsletter input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  padding: 13px 16px;
  font-family: var(--ff-body-f);
  font-size: 0.82rem;
  color: var(--footer-text-strong);
}

.footer-newsletter input::placeholder { color: rgba(245,240,232,0.28); }

.footer-newsletter button {
  background: var(--footer-green);
  border: none;
  color: var(--forest-footer);
  font-family: var(--ff-body-f);
  font-weight: 700;
  font-size: 0.78rem;
  letter-spacing: 0.06em;
  padding: 13px 20px;
  cursor: pointer;
  transition: background 0.2s;
  white-space: nowrap;
}

.footer-newsletter button:hover { background: var(--footer-amber); }

/* ── Link columns ── */
.footer-col-title {
  font-family: var(--ff-display-f);
  font-size: 1rem;
  font-weight: 700;
  color: var(--footer-text-strong);
  margin-bottom: 24px;
  position: relative;
  padding-bottom: 14px;
}

.footer-col-title::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0;
  width: 26px; height: 2px;
  background: var(--footer-green);
  border-radius: 2px;
}

.footer-col-links {
  display: flex;
  flex-direction: column;
  gap: 13px;
  list-style: none;
  padding: 0; margin: 0;
}

.footer-col-links li a {
  font-size: 0.86rem;
  color: var(--footer-text);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: color 0.2s, gap 0.2s var(--ease-bounce-f);
}

.footer-col-links li a::before {
  content: '';
  display: block;
  width: 4px; height: 4px;
  background: var(--footer-green);
  border-radius: 50%;
  opacity: 0;
  transition: opacity 0.2s;
  flex-shrink: 0;
}

.footer-col-links li a:hover { color: var(--footer-text-strong); gap: 12px; }
.footer-col-links li a:hover::before { opacity: 1; }

/* ── Bottom bar ── */
.footer-bottom {
  padding: 28px 80px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 20px;
}

.footer-copy {
  font-size: 0.78rem;
  color: rgba(245,240,232,0.3);
  font-family: var(--ff-mono-f);
  letter-spacing: 0.04em;
  line-height: 1.7;
}

.footer-copy strong { color: rgba(245,240,232,0.55); font-weight: 500; }

.footer-dev-credit {
  font-size: 0.75rem;
  color: rgba(245,240,232,0.28);
  font-family: var(--ff-mono-f);
  letter-spacing: 0.06em;
  margin-top: 4px;
}

.footer-dev-credit span {
  color: var(--footer-green);
  font-weight: 600;
}

/* Social icons */
.footer-socials { display: flex; gap: 8px; }

.social-link {
  width: 40px; height: 40px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  color: rgba(245,240,232,0.4);
  font-size: 0.9rem;
  text-decoration: none;
  transition: background 0.2s, color 0.2s, border-color 0.2s,
              transform 0.25s var(--ease-bounce-f);
}

.social-link:hover {
  background: rgba(110,201,122,0.14);
  border-color: rgba(110,201,122,0.32);
  color: var(--footer-green);
  transform: translateY(-5px);
}

/* ── Responsive ── */
@media (max-width: 1024px) {
  .footer-main { grid-template-columns: 1fr 1fr; gap: 44px; padding: 60px 40px 44px; }
  .footer-bottom { padding: 24px 40px; }
}

@media (max-width: 640px) {
  .footer-main { grid-template-columns: 1fr; gap: 36px; padding: 52px 24px 40px; }
  .footer-bottom { padding: 20px 24px; flex-direction: column; align-items: flex-start; }
  .footer-newsletter { max-width: 100%; }
}
</style>

<footer>
  <div class="footer-main">

    <!-- Brand -->
    <div>
      <div class="footer-logo">
        <span class="logo-green">Green</span><span class="logo-basket">Basket</span>
      </div>
      <p class="footer-tagline">
        Your local source for the freshest, organically home-grown produce. From real people, real farms — straight to your table in Kerala.
      </p>
      <div class="newsletter-label">Get fresh updates</div>
      <div class="footer-newsletter">
        <input type="email" placeholder="antonyalphin57@gmail.com" aria-label="Email for newsletter">
        <button type="button">Subscribe</button>
      </div>
    </div>

    <!-- Explore -->
    <div>
      <h3 class="footer-col-title">Explore</h3>
      <ul class="footer-col-links">
        <li><a href="/">Home</a></li>
        <li><a href="/shop/index.php">Shop Now</a></li>
        <li><a href="/shop/veggies.php">Vegetables</a></li>
        <li><a href="/shop/fruits.php">Fruits</a></li>
        <li><a href="/layout/about.php">About Us</a></li>
        <li><a href="/contact.php">Contact</a></li>
      </ul>
    </div>

    <!-- Account -->
    <div>
      <h3 class="footer-col-title">Account</h3>
      <ul class="footer-col-links">
        <li><a href="/account/login.php">Login</a></li>
        <li><a href="/account/profile.php">My Profile</a></li>
        <li><a href="/shop/cart.php">Cart</a></li>
        <li><a href="/shop/orders.php">Track Order</a></li>
        <li><a href="/account/changerole.php">Become a Seller</a></li>
        <li><a href="/support/faq.php">FAQ</a></li>
      </ul>
    </div>

    <!-- Legal -->
    <div>
      <h3 class="footer-col-title">Legal</h3>
      <ul class="footer-col-links">
        <li><a href="/legal/terms.php">Terms &amp; Conditions</a></li>
        <li><a href="/legal/privacy.php">Privacy Policy</a></li>
        <li><a href="/legal/disclosures.php">Disclosures</a></li>
        <li><a href="/support/shipping.php">Shipping &amp; Returns</a></li>
      </ul>
    </div>

  </div>

  <div class="footer-bottom">
    <div>
      <p class="footer-copy">
        &copy; <?php echo date('Y'); ?> <strong>Green Basket</strong> — All rights reserved. Made with 🌿 in Kerala.
      </p>
      <p class="footer-dev-credit">Designed &amp; developed by <span>Antony Alphin</span></p>
    </div>

    <div class="footer-socials">
      <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
      <a href="#" class="social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      <a href="#" class="social-link" aria-label="Twitter / X"><i class="fab fa-x-twitter"></i></a>
      <a href="#" class="social-link" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
      <a href="#" class="social-link" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
    </div>
  </div>
</footer>
