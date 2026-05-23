<?php include __DIR__ . '/session/init.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GreenBasket — Farm to Table</title>
  <link rel="icon" type="image/png" href="style/imgs/gb.png">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&family=Poetsen+One&family=Inter:wght@400;700&family=Lemon&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* ============================================================
   DESIGN SYSTEM — GreenBasket index.php
   ============================================================ */
:root {
  --moss:       #2C4A2E;
  --forest:     #1A3020;
  --sage:       #7A9E7E;
  --mint:       #B8D4BB;
  --cream:      #F5F0E8;
  --parchment:  #EDE7D9;
  --amber:      #C8955A;
  --amber-light:#E8B07A;
  --white:      #FFFFFF;
  --charcoal:   #1C1C1C;
  --text-muted: #6B7B6D;
  --gb-green:   #6EC97A;

  --ff-display: 'Playfair Display', Georgia, serif;
  --ff-body:    'DM Sans', sans-serif;
  --ff-mono:    'DM Mono', monospace;
  --ff-logo:    'Poetsen One', sans-serif;

  --nav-h: 52px;
  --ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
  --ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
  font-family: var(--ff-body);
  background: var(--cream);
  color: var(--charcoal);
  overflow-x: hidden;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
a { text-decoration: none; color: inherit; }
ul { list-style: none; }
img { display: block; max-width: 100%; }

/* ============================================================
   HERO — full viewport, sits behind fixed header
   ============================================================ */
.hero {
  position: relative;
  width: 100%;
  height: 100vh;
  min-height: 620px;
  overflow: hidden;
  display: flex;
  align-items: center;
  /* NO margin-top — hero starts at top, header overlays it */
}

.background-video {
  position: absolute;
  inset: 0;
  width: 100%; height: 100%;
  object-fit: cover;
  z-index: 0;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  z-index: 1;
  background:
    linear-gradient(105deg, rgba(26,48,32,0.85) 0%, rgba(26,48,32,0.42) 55%, transparent 100%),
    linear-gradient(to top, rgba(26,48,32,0.65) 0%, transparent 50%);
}

.hero-content {
  position: relative;
  z-index: 2;
  padding: 0 80px;
  max-width: 720px;
  /* Push content down so it clears the transparent nav visually */
  padding-top: var(--nav-h);
  animation: heroReveal 1.2s var(--ease-smooth) both;
}

@keyframes heroReveal {
  from { opacity: 0; transform: translateY(32px); }
  to   { opacity: 1; transform: translateY(0); }
}

.hero-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: rgba(110,201,122,0.15);
  border: 1px solid rgba(110,201,122,0.3);
  border-radius: 100px;
  padding: 6px 16px;
  font-family: var(--ff-mono);
  font-size: 0.72rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--gb-green);
  margin-bottom: 28px;
  animation: heroReveal 1.2s 0.15s var(--ease-smooth) both;
}

.hero-eyebrow-dot {
  width: 6px; height: 6px;
  background: var(--gb-green);
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%       { opacity: 0.5; transform: scale(1.4); }
}

.hero-title {
  font-family: var(--ff-display);
  font-size: clamp(3rem, 6vw, 5.5rem);
  font-weight: 900;
  line-height: 1.04;
  letter-spacing: -0.03em;
  color: var(--white);
  margin-bottom: 24px;
  animation: heroReveal 1.2s 0.25s var(--ease-smooth) both;
}

.hero-title em { font-style: italic; color: var(--gb-green); }

.hero-desc {
  font-size: 1.05rem;
  font-weight: 300;
  line-height: 1.75;
  color: rgba(245,240,232,0.82);
  max-width: 480px;
  margin-bottom: 40px;
  animation: heroReveal 1.2s 0.35s var(--ease-smooth) both;
}

.hero-actions {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
  animation: heroReveal 1.2s 0.45s var(--ease-smooth) both;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: linear-gradient(135deg, #5DF56A, #22C55E);
  color: var(--forest);
  font-weight: 700;
  font-size: 0.9rem;
  letter-spacing: 0.04em;
  padding: 16px 32px;
  border-radius: 100px;
  transition: transform 0.25s var(--ease-bounce), box-shadow 0.25s;
  box-shadow: 0 4px 24px rgba(78,197,88,0.35);
}

.btn-primary:hover {
  transform: translateY(-3px) scale(1.03);
  box-shadow: 0 10px 36px rgba(78,197,88,0.5);
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: var(--cream);
  font-weight: 500;
  font-size: 0.9rem;
  letter-spacing: 0.04em;
  padding: 16px 24px;
  border-radius: 100px;
  border: 1px solid rgba(245,240,232,0.32);
  transition: border-color 0.2s, background 0.2s;
}

.btn-secondary:hover {
  border-color: rgba(245,240,232,0.7);
  background: rgba(245,240,232,0.1);
}

/* Hero trust bar */
.hero-trust {
  position: absolute;
  bottom: 48px; left: 80px;
  z-index: 2;
  display: flex;
  align-items: center;
  gap: 32px;
  animation: heroReveal 1.2s 0.6s var(--ease-smooth) both;
}

.trust-item {
  display: flex;
  align-items: center;
  gap: 10px;
  color: rgba(245,240,232,0.72);
  font-size: 0.82rem;
  letter-spacing: 0.04em;
}

.trust-icon {
  width: 36px; height: 36px;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.18);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem;
}

.scroll-hint {
  position: absolute;
  bottom: 40px; right: 48px;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: rgba(245,240,232,0.5);
  font-family: var(--ff-mono);
  font-size: 0.65rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  animation: heroReveal 1.2s 0.7s var(--ease-smooth) both;
}

.scroll-line {
  width: 1px; height: 48px;
  background: linear-gradient(to bottom, rgba(245,240,232,0.5), transparent);
  animation: scrollPulse 2s 1s infinite;
}

@keyframes scrollPulse {
  0%, 100% { transform: scaleY(1); opacity: 0.5; }
  50%       { transform: scaleY(0.6); opacity: 0.2; }
}

/* ============================================================
   MARQUEE STRIP
   ============================================================ */
.marquee-strip {
  background: var(--moss);
  padding: 14px 0;
  overflow: hidden;
  border-top: 1px solid rgba(110,201,122,0.15);
  border-bottom: 1px solid rgba(110,201,122,0.15);
}

.marquee-track {
  display: flex;
  gap: 0;
  animation: marqueeScroll 22s linear infinite;
  white-space: nowrap;
}

.marquee-item {
  display: inline-flex;
  align-items: center;
  gap: 14px;
  padding: 0 32px;
  font-family: var(--ff-mono);
  font-size: 0.72rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--sage);
}

.marquee-sep { color: var(--gb-green); font-size: 0.6rem; }

@keyframes marqueeScroll {
  from { transform: translateX(0); }
  to   { transform: translateX(-50%); }
}

/* ============================================================
   CATEGORY SECTION — matches screenshot card sizes
   ============================================================ */
.category-section {
  padding: 96px 80px;
  background: var(--cream);
  flex: 1;
}

.category-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 56px;
  gap: 24px;
}

.category-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: var(--ff-mono);
  font-size: 0.7rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--amber);
  margin-bottom: 12px;
}

.category-eyebrow::before {
  content: '';
  display: block;
  width: 24px; height: 1px;
  background: var(--amber);
}

.category-title {
  font-family: var(--ff-display);
  font-size: clamp(2rem, 4vw, 3.2rem);
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--forest);
  line-height: 1.1;
}

.category-subtitle {
  font-size: 1rem;
  color: var(--text-muted);
  max-width: 320px;
  text-align: right;
  line-height: 1.65;
}

/* Grid: equal columns like the screenshot */
.category-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 28px;
}

/* Cards: tall portrait matching the screenshot (roughly 4:5) */
.category-card {
  position: relative;
  display: block;
  border-radius: 28px;
  overflow: hidden;
  aspect-ratio: 4 / 5;
  cursor: pointer;
  box-shadow: 0 8px 40px rgba(26,48,32,0.14);
  transition: box-shadow 0.4s var(--ease-smooth),
              transform 0.4s var(--ease-smooth);
}

.category-card:hover {
  box-shadow: 0 20px 60px rgba(26,48,32,0.28);
  transform: translateY(-6px);
}

.card-image {
  width: 100%; height: 100%;
  object-fit: cover;
  transition: transform 0.7s var(--ease-smooth);
}

.category-card:hover .card-image { transform: scale(1.07); }

/* Gradient overlay */
.card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to top,
    rgba(26,48,32,0.95) 0%,
    rgba(26,48,32,0.55) 40%,
    rgba(26,48,32,0.08) 75%,
    transparent 100%
  );
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 40px 36px;
  transition: background 0.4s;
}

.category-card:hover .card-overlay {
  background: linear-gradient(
    to top,
    rgba(26,48,32,0.97) 0%,
    rgba(26,48,32,0.65) 50%,
    rgba(26,48,32,0.12) 80%,
    transparent 100%
  );
}

/* Top badge */
.card-badge {
  position: absolute;
  top: 22px; left: 22px;
  background: rgba(245,240,232,0.15);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(245,240,232,0.28);
  border-radius: 100px;
  padding: 6px 14px;
  font-family: var(--ff-mono);
  font-size: 0.68rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--cream);
}

.card-title {
  font-family: var(--ff-display);
  font-size: 2.4rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--white);
  margin-bottom: 10px;
  transform: translateY(6px);
  transition: transform 0.4s var(--ease-smooth);
}

.category-card:hover .card-title { transform: translateY(0); }

.card-description {
  font-size: 0.9rem;
  line-height: 1.65;
  color: rgba(245,240,232,0.78);
  margin-bottom: 26px;
  max-width: 300px;
  opacity: 0;
  transform: translateY(14px);
  transition: opacity 0.35s 0.05s var(--ease-smooth),
              transform 0.35s 0.05s var(--ease-smooth);
}

.category-card:hover .card-description { opacity: 1; transform: translateY(0); }

.card-cta {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 0.84rem;
  font-weight: 700;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--gb-green);
  transition: gap 0.2s var(--ease-bounce);
}

.card-cta-arrow {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px; height: 34px;
  background: var(--gb-green);
  border-radius: 50%;
  color: var(--forest);
  font-size: 0.85rem;
  transition: transform 0.25s var(--ease-bounce), background 0.2s;
}

.category-card:hover .card-cta { gap: 14px; }
.category-card:hover .card-cta-arrow {
  transform: translateX(5px);
  background: var(--amber-light);
}

/* ============================================================
   FEATURES STRIP
   ============================================================ */
.features-section {
  background: var(--forest);
  padding: 64px 80px;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 40px;
}

.feature-item { text-align: center; }

.feature-icon-wrap {
  width: 62px; height: 62px;
  background: rgba(110,201,122,0.1);
  border: 1px solid rgba(110,201,122,0.22);
  border-radius: 18px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.65rem;
  margin: 0 auto 18px;
  transition: background 0.2s, transform 0.2s var(--ease-bounce);
}

.feature-item:hover .feature-icon-wrap {
  background: rgba(110,201,122,0.22);
  transform: translateY(-5px);
}

.feature-title {
  font-family: var(--ff-display);
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--cream);
  margin-bottom: 8px;
}

.feature-desc { font-size: 0.82rem; color: var(--sage); line-height: 1.6; }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1024px) {
  .hero-content { padding: 0 40px; padding-top: var(--nav-h); }
  .hero-trust   { left: 40px; }
  .category-section { padding: 72px 40px; }
  .features-section { padding: 56px 40px; }
  .features-grid { grid-template-columns: repeat(2, 1fr); gap: 28px; }
}

@media (max-width: 768px) {
  :root { --nav-h: 64px; }
  .hero-content { padding: 0 24px; padding-top: var(--nav-h); }
  .hero-trust { display: none; }
  .scroll-hint { display: none; }
  .category-section { padding: 56px 20px; }
  .category-header { flex-direction: column; align-items: flex-start; }
  .category-subtitle { text-align: left; }
  .category-grid { grid-template-columns: 1fr; }
  .category-card { aspect-ratio: 4/3; }
  .features-section { padding: 48px 20px; }
  .features-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
  .marquee-strip { display: none; }
}
</style>
</head>
<body>

<?php include __DIR__ . '/layout/header.php'; ?>

<!-- HERO: starts at top: 0, header overlays transparently -->
<section class="hero">
  <video autoplay muted loop playsinline class="background-video">
    <source src="style/imgs/vegivideo.mp4" type="video/mp4">
  </video>

  <div class="hero-overlay"></div>

  <div class="hero-content">
    <div class="hero-eyebrow">
      <span class="hero-eyebrow-dot"></span>
      100% Home-Grown Produce
    </div>

    <h1 class="hero-title">
      Fresh from the<br><em>garden to you</em>
    </h1>

    <p class="hero-desc">
      No harmful chemicals. No middlemen. Just real people growing real food — delivered straight from Kerala's finest local farms.
    </p>

    <div class="hero-actions">
      <a href="shop/veggies.php" class="btn-primary">
        <i class="fas fa-leaf"></i> Shop Now
      </a>
      <a href="/layout/about.php" class="btn-secondary">
        Our Story <i class="fas fa-arrow-right" style="font-size:0.8rem"></i>
      </a>
    </div>
  </div>

  <div class="hero-trust">
    <div class="trust-item"><div class="trust-icon">🌱</div> No Chemicals</div>
    <div class="trust-item"><div class="trust-icon">🏡</div> Home-Grown</div>
    <div class="trust-item"><div class="trust-icon">🤝</div> Local Sellers</div>
  </div>

  <div class="scroll-hint">
    <div class="scroll-line"></div>
    Scroll
  </div>
</section>

<!-- MARQUEE -->
<div class="marquee-strip" aria-hidden="true">
  <div class="marquee-track">
    <?php for ($i = 0; $i < 2; $i++): ?>
      <span class="marquee-item">🥦 Fresh Vegetables <span class="marquee-sep">✦</span></span>
      <span class="marquee-item">🍅 Seasonal Specials <span class="marquee-sep">✦</span></span>
      <span class="marquee-item">🌿 Zero Chemicals <span class="marquee-sep">✦</span></span>
      <span class="marquee-item">🏡 Home-Grown Only <span class="marquee-sep">✦</span></span>
      <span class="marquee-item">🚚 Local Delivery <span class="marquee-sep">✦</span></span>
      <span class="marquee-item">🥭 Tropical Fruits <span class="marquee-sep">✦</span></span>
      <span class="marquee-item">🌾 Trusted Farmers <span class="marquee-sep">✦</span></span>
    <?php endfor; ?>
  </div>
</div>

<!-- CATEGORIES -->
<section class="category-section">
  <div class="category-header">
    <div>
      <div class="category-eyebrow">Browse by Category</div>
      <h2 class="category-title">What are you<br>looking for?</h2>
    </div>
    <p class="category-subtitle">Freshness, organized just for you — straight from the source.</p>
  </div>

  <div class="category-grid">

    <a href="shop/veggies.php" class="category-card">
      <img src="style/imgs/vegi.jpg" alt="Fresh Vegetables" class="card-image">
      <span class="card-badge">🥬 Daily Harvest</span>
      <div class="card-overlay">
        <h3 class="card-title">Vegetables</h3>
        <p class="card-description">Crisp, organic, and locally grown — a daily harvest of the freshest greens with no harmful chemicals.</p>
        <span class="card-cta">
          Shop Vegetables
          <span class="card-cta-arrow"><i class="fas fa-arrow-right"></i></span>
        </span>
      </div>
    </a>

    <a href="shop/fruits.php" class="category-card">
      <img src="style/imgs/fruits.jpg" alt="Delicious Fruits" class="card-image">
      <span class="card-badge">🍓 Seasonal Picks</span>
      <div class="card-overlay">
        <h3 class="card-title">Fruits</h3>
        <p class="card-description">Juicy, sweet, and bursting with natural flavor — Kerala's finest seasonal fruits, picked at peak ripeness.</p>
        <span class="card-cta">
          Shop Fruits
          <span class="card-cta-arrow"><i class="fas fa-arrow-right"></i></span>
        </span>
      </div>
    </a>

  </div>
</section>

<!-- FEATURES -->
<section class="features-section">
  <div class="features-grid">
    <div class="feature-item">
      <div class="feature-icon-wrap">🌱</div>
      <div class="feature-title">Zero Chemicals</div>
      <p class="feature-desc">Every product is grown without harmful pesticides or additives.</p>
    </div>
    <div class="feature-item">
      <div class="feature-icon-wrap">🏡</div>
      <div class="feature-title">Home-Grown</div>
      <p class="feature-desc">Real families, real gardens — produce with a story behind every bite.</p>
    </div>
    <div class="feature-item">
      <div class="feature-icon-wrap">🚚</div>
      <div class="feature-title">Local Delivery</div>
      <p class="feature-desc">Fresh from farm to your door — fast, reliable, and eco-friendly.</p>
    </div>
    <div class="feature-item">
      <div class="feature-icon-wrap">🤝</div>
      <div class="feature-title">Trusted Sellers</div>
      <p class="feature-desc">Every seller is verified — supporting your local farming community.</p>
    </div>
  </div>
</section>

<?php include __DIR__ . '/layout/footer.php'; ?>

</body>
</html>
