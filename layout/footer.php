<style>
/* ── Footer Design System ── */
:root {
  --footer-bg: #111111;
  --footer-surface: #1A1A1A;
  --footer-border: rgba(255,255,255,0.07);
  --footer-text: rgba(245,240,232,0.5);
  --footer-text-strong: #F5F0E8;
  --footer-green: #6EC97A;
  --footer-amber: #E8B07A;
  --forest: #1A3020;
  --ff-display: 'Playfair Display', Georgia, serif;
  --ff-body: 'DM Sans', sans-serif;
  --ff-mono: 'DM Mono', monospace;
  --ease-bounce: cubic-bezier(0.34,1.56,0.64,1);
}

/* ── Shell ── */
footer {
  background: var(--footer-bg);
  color: var(--footer-text-strong);
  font-family: var(--ff-body);
  width: 100%;
}

/* ── Main grid ── */
.footer-main {
  display: grid;
  grid-template-columns: 1.7fr 1fr 1fr 1fr;
  gap: 56px;
  padding: 80px 80px 56px;
  border-bottom: 1px solid var(--footer-border);
}

/* ── Brand column ── */
.footer-logo {
  font-family: var(--ff-display);
  font-size: 2rem;
  font-weight: 900;
  letter-spacing: -0.02em;
  margin-bottom: 16px;
  line-height: 1;
}

.footer-logo .logo-green  { color: var(--footer-green); }
.footer-logo .logo-basket { color: var(--footer-text-strong); }

.footer-tagline {
  font-size: 0.88rem;
  line-height: 1.8;
  color: var(--footer-text);
  max-width: 290px;
  margin-bottom: 32px;
}

/* Newsletter widget */
.newsletter-label {
  font-family: var(--ff-mono);
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
  border-radius: 12px;
  overflow: hidden;
  max-width: 300px;
  transition: border-color 0.2s;
}

.footer-newsletter:focus-within {
  border-color: rgba(110,201,122,0.4);
}

.footer-newsletter input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  padding: 13px 16px;
  font-family: var(--ff-body);
  font-size: 0.82rem;
  color: var(--footer-text-strong);
}

.footer-newsletter input::placeholder { color: rgba(245,240,232,0.28); }

.footer-newsletter button {
  background: var(--footer-green);
  border: none;
  color: var(--forest);
  font-family: var(--ff-body);
  font-weight: 700;
  font-size: 0.78rem;
  letter-spacing: 0.06em;
  padding: 13px 18px;
  cursor: pointer;
  transition: background 0.2s;
  white-space: nowrap;
}

.footer-newsletter button:hover { background: var(--footer-amber); }

/* ── Link columns ── */
.footer-col-title {
  font-family: var(--ff-display);
  font-size: 1rem;
  font-weight: 700;
  color: var(--footer-text-strong);
  margin-bottom: 22px;
  position: relative;
  padding-bottom: 12px;
}

.footer-col-title::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0;
  width: 24px; height: 2px;
  background: var(--footer-green);
  border-radius: 2px;
}

.footer-col-links {
  display: flex;
  flex-direction: column;
  gap: 12px;
  list-style: none;
  padding: 0; margin: 0;
}

.footer-col-links li a {
  font-size: 0.86rem;
  color: var(--footer-text);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  transition: color 0.2s, gap 0.2s;
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

.footer-col-links li a:hover { color: var(--footer-text-strong); gap: 10px; }
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
  color: rgba(245,240,232,0.28);
  font-family: var(--ff-mono);
  letter-spacing: 0.04em;
}

.footer-copy strong {
  color: rgba(245,240,232,0.5);
  font-weight: 500;
}

/* Social icons */
.footer-socials {
  display: flex;
  gap: 8px;
}

.social-link {
  width: 40px; height: 40px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: rgba(245,240,232,0.4);
  font-size: 0.9rem;
  text-decoration: none;
  transition: background 0.2s, color 0.2s, border-color 0.2s,
              transform 0.2s cubic-bezier(0.34,1.56,0.64,1);
}

.social-link:hover {
  background: rgba(110,201,122,0.12);
  border-color: rgba(110,201,122,0.3);
  color: var(--footer-green);
  transform: translateY(-4px);
}

/* ── Responsive ── */
@media (max-width: 1024px) {
  .footer-main { grid-template-columns: 1fr 1fr; gap: 40px; padding: 56px 40px 40px; }
  .footer-bottom { padding: 24px 40px; }
}

@media (max-width: 640px) {
  .footer-main { grid-template-columns: 1fr; gap: 36px; padding: 48px 20px 36px; }
  .footer-bottom { padding: 20px; flex-direction: column; align-items: flex-start; }
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
        Your local source for the freshest, organically home-grown produce. From real people, real farms — straight to your table.
      </p>
      <div class="newsletter-label">Get fresh updates</div>
      <div class="footer-newsletter">
        <input type="email" placeholder="your@email.com" aria-label="Email for newsletter">
        <button type="button">Subscribe</button>
      </div>
    </div>

    <!-- Main Links -->
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
        <li><a href="/legal/terms.php">Terms & Conditions</a></li>
        <li><a href="/legal/privacy.php">Privacy Policy</a></li>
        <li><a href="/legal/disclosures.php">Disclosures</a></li>
        <li><a href="/support/shipping.php">Shipping & Returns</a></li>
      </ul>
    </div>

  </div>

  <div class="footer-bottom">
    <p class="footer-copy">
      &copy; <?php echo date('Y'); ?> <strong>Green Basket</strong> — All rights reserved. Made with 🌿 in Kerala.
    </p>

    <div class="footer-socials">
      <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
      <a href="#" class="social-link" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      <a href="#" class="social-link" aria-label="Twitter / X"><i class="fab fa-x-twitter"></i></a>
      <a href="#" class="social-link" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
      <a href="#" class="social-link" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
    </div>
  </div>
</footer>
