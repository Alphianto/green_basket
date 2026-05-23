<?php include __DIR__ . '/session/init.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GreenBasket — Farm to Table</title>
  <link rel="icon" type="image/png" href="style/imgs/gb.png">
  <!-- Fonts: Playfair Display (editorial) + DM Sans (refined body) -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Lemon&display=swap" rel="stylesheet">

<style>
/* ============================================================
   DESIGN SYSTEM — GreenBasket
   Aesthetic: Organic Luxury — editorial photography meets
   botanical illustration, earthy and refined.
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

  --ff-display: 'Playfair Display', Georgia, serif;
  --ff-body:    'DM Sans', sans-serif;
  --ff-mono:    'DM Mono', monospace;

  --nav-h: 72px;
  --ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
  --ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* ── Reset & Base ───────────────────────────────────────── */
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
   HEADER
   ============================================================ */
.site-header {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 1000;
  height: var(--nav-h);
  display: flex;
  align-items: center;
  background: rgba(26, 48, 32, 0.96);
  justify-content: space-between;
  padding: 0 48px;
  transition: background 0.4s var(--ease-smooth),
              box-shadow 0.4s var(--ease-smooth),
              backdrop-filter 0.4s;
}


/* Scrolled state */
.site-header.scrolled {
  background: rgba(26, 48, 32, 0.96);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 2px 32px rgba(0,0,0,0.18);
}

/* Logo */
.site-logo a {
  display: flex;
  align-items: baseline;
  gap: 0;
  font-family: var(--ff-display);
  font-size: 1.6rem;
  font-weight: 900;
  letter-spacing: -0.02em;
  line-height: 1;
}

.logo-leaf {
  display: inline-block;
  width: 28px;
  height: 28px;
  margin-right: 8px;
  vertical-align: middle;
}

.logo-green  { color: #6EC97A; }
.logo-basket { color: var(--cream); }

/* Nav */
.nav-menu {
  display: flex;
  align-items: center;
  gap: 8px;
}

.nav-menu > li > a {
  font-size: 0.85rem;
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: rgba(245,240,232,0.85);
  padding: 8px 16px;
  border-radius: 100px;
  transition: color 0.2s, background 0.2s;
}

.nav-menu > li > a:hover {
  color: var(--white);
  background: rgba(255,255,255,0.1);
}

/* Login CTA */
.btn-gradient {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.82rem;
  font-weight: 600;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--forest) !important;
  background: linear-gradient(135deg, #6EC97A, #B8D4BB);
  padding: 10px 22px;
  border-radius: 100px;
  transition: transform 0.2s var(--ease-bounce), box-shadow 0.2s;
}

.btn-gradient:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(110,201,122,0.4);
  background: rgba(255,255,255,0.1) !important;
  color: var(--white) !important;
}

/* Profile Dropdown */
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
  font-size: 0.7rem;
  transition: transform 0.25s;
}
.btn-profile-style.open .dropdown-icon { transform: rotate(180deg); }

.dropdown-link-group {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  min-width: 210px;
  background: var(--forest);
  border: 1px solid rgba(110,201,122,0.15);
  border-radius: 16px;
  overflow: hidden;
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
  font-size: 0.88rem;
  color: var(--mint);
  transition: background 0.15s, color 0.15s;
}

.dropdown-link-group li a:hover {
  background: rgba(255,255,255,0.06);
  color: var(--white);
}

.list-icon { font-size: 1rem; width: 20px; text-align: center; }

.divider {
  height: 1px;
  background: rgba(255,255,255,0.08);
  margin: 4px 0;
}

/* ============================================================
   HERO
   ============================================================ */
.hero {
  position: relative;
  width: 100%;
  height: 100vh;
  min-height: 600px;
  overflow: hidden;
  display: flex;
  align-items: center;
}

.background-video {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 0;
}

/* Multi-layer overlay for atmosphere */
.hero-overlay {
  position: absolute;
  inset: 0;
  z-index: 1;
  background:
    linear-gradient(105deg, rgba(26,48,32,0.82) 0%, rgba(26,48,32,0.4) 55%, transparent 100%),
    linear-gradient(to top, rgba(26,48,32,0.6) 0%, transparent 50%);
}

/* Decorative grain texture */
.hero-overlay::after {
  content: '';
  position: absolute;
  inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
  opacity: 0.3;
  pointer-events: none;
}

.hero-content {
  position: relative;
  z-index: 2;
  padding: 0 80px;
  max-width: 720px;
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
  color: #6EC97A;
  margin-bottom: 28px;
  animation: heroReveal 1.2s 0.15s var(--ease-smooth) both;
}

.hero-eyebrow-dot {
  width: 6px; height: 6px;
  background: #6EC97A;
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

.hero-title em {
  font-style: italic;
  color: #6EC97A;
}

.hero-desc {
  font-size: 1.05rem;
  font-weight: 300;
  line-height: 1.75;
  color: rgba(245,240,232,0.8);
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
  background: linear-gradient(135deg, #6EC97A, #4AAD58);
  color: var(--forest);
  font-weight: 700;
  font-size: 0.9rem;
  letter-spacing: 0.04em;
  padding: 16px 32px;
  border-radius: 100px;
  transition: transform 0.25s var(--ease-bounce), box-shadow 0.25s;
  box-shadow: 0 4px 24px rgba(78,173,88,0.35);
}

.btn-primary:hover {
  transform: translateY(-3px) scale(1.03);
  box-shadow: 0 10px 36px rgba(78,173,88,0.45);
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
  border: 1px solid rgba(245,240,232,0.3);
  transition: border-color 0.2s, background 0.2s;
}

.btn-secondary:hover {
  border-color: rgba(245,240,232,0.7);
  background: rgba(245,240,232,0.08);
}

/* Hero trust bar */
.hero-trust {
  position: absolute;
  bottom: 48px;
  left: 80px;
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
  color: rgba(245,240,232,0.7);
  font-size: 0.82rem;
  letter-spacing: 0.04em;
}

.trust-icon {
  width: 36px; height: 36px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem;
}

/* Scroll indicator */
.scroll-hint {
  position: absolute;
  bottom: 40px;
  right: 48px;
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
  width: 1px;
  height: 48px;
  background: linear-gradient(to bottom, rgba(245,240,232,0.5), transparent);
  animation: scrollPulse 2s 1s infinite;
}

@keyframes scrollPulse {
  0%, 100% { transform: scaleY(1); opacity: 0.5; }
  50%       { transform: scaleY(0.6); opacity: 0.2; }
}

/* ============================================================
   DIVIDER STRIP
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

.marquee-sep {
  color: #6EC97A;
  font-size: 0.6rem;
}

@keyframes marqueeScroll {
  from { transform: translateX(0); }
  to   { transform: translateX(-50%); }
}

/* ============================================================
   CATEGORY SECTION
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

.category-header-left {}

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

/* Grid */
.category-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

/* Cards */
.category-card {
  position: relative;
  display: block;
  border-radius: 24px;
  overflow: hidden;
  aspect-ratio: 4/5;
  cursor: pointer;
}

/* First card larger */
.category-card:first-child {
  aspect-ratio: 3/4;
}

.card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.7s var(--ease-smooth);
}

.category-card:hover .card-image {
  transform: scale(1.06);
}

/* Overlay gradient */
.card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to top,
    rgba(26,48,32,0.92) 0%,
    rgba(26,48,32,0.5) 40%,
    rgba(26,48,32,0.05) 80%,
    transparent 100%
  );
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 36px 32px;
  transition: background 0.4s;
}

.category-card:hover .card-overlay {
  background: linear-gradient(
    to top,
    rgba(26,48,32,0.97) 0%,
    rgba(26,48,32,0.6) 45%,
    rgba(26,48,32,0.1) 80%,
    transparent 100%
  );
}

/* Card top badge */
.card-badge {
  position: absolute;
  top: 20px;
  left: 20px;
  background: rgba(245,240,232,0.15);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(245,240,232,0.25);
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
  font-size: 2.2rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--white);
  margin-bottom: 10px;
  transform: translateY(8px);
  transition: transform 0.4s var(--ease-smooth);
}

.category-card:hover .card-title {
  transform: translateY(0);
}

.card-description {
  font-size: 0.88rem;
  line-height: 1.65;
  color: rgba(245,240,232,0.75);
  margin-bottom: 24px;
  max-width: 300px;
  opacity: 0;
  transform: translateY(12px);
  transition: opacity 0.4s 0.05s var(--ease-smooth),
              transform 0.4s 0.05s var(--ease-smooth);
}

.category-card:hover .card-description {
  opacity: 1;
  transform: translateY(0);
}

.card-cta {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 0.82rem;
  font-weight: 600;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: #6EC97A;
  transition: gap 0.2s var(--ease-bounce);
}

.card-cta-arrow {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px; height: 32px;
  background: #6EC97A;
  border-radius: 50%;
  color: var(--forest);
  font-size: 0.85rem;
  transition: transform 0.25s var(--ease-bounce), background 0.2s;
}

.category-card:hover .card-cta { gap: 14px; }
.category-card:hover .card-cta-arrow {
  transform: translateX(4px);
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

.feature-item {
  text-align: center;
}

.feature-icon-wrap {
  width: 60px; height: 60px;
  background: rgba(110,201,122,0.1);
  border: 1px solid rgba(110,201,122,0.2);
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.6rem;
  margin: 0 auto 18px;
  transition: background 0.2s, transform 0.2s var(--ease-bounce);
}

.feature-item:hover .feature-icon-wrap {
  background: rgba(110,201,122,0.2);
  transform: translateY(-4px);
}

.feature-title {
  font-family: var(--ff-display);
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--cream);
  margin-bottom: 8px;
}

.feature-desc {
  font-size: 0.82rem;
  color: var(--sage);
  line-height: 1.6;
}

/* ============================================================
   FOOTER
   ============================================================ */
footer {
  background: var(--charcoal);
  color: var(--cream);
  font-family: var(--ff-body);
}

.footer-main {
  display: grid;
  grid-template-columns: 1.6fr 1fr 1fr 1fr;
  gap: 48px;
  padding: 72px 80px 48px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}

/* Brand col */
.footer-brand-col {}

.footer-logo {
  font-family: var(--ff-display);
  font-size: 1.8rem;
  font-weight: 900;
  letter-spacing: -0.02em;
  margin-bottom: 16px;
}

.footer-logo .logo-green  { color: #6EC97A; }
.footer-logo .logo-basket { color: var(--cream); }

.footer-tagline {
  font-size: 0.88rem;
  line-height: 1.75;
  color: rgba(245,240,232,0.55);
  max-width: 280px;
  margin-bottom: 28px;
}

/* Newsletter inline */
.footer-newsletter {
  display: flex;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 100px;
  overflow: hidden;
  max-width: 300px;
}

.footer-newsletter input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  padding: 12px 18px;
  font-family: var(--ff-body);
  font-size: 0.82rem;
  color: var(--cream);
}

.footer-newsletter input::placeholder { color: rgba(245,240,232,0.35); }

.footer-newsletter button {
  background: #6EC97A;
  border: none;
  color: var(--forest);
  font-weight: 700;
  font-size: 0.78rem;
  letter-spacing: 0.06em;
  padding: 12px 20px;
  cursor: pointer;
  transition: background 0.2s;
}

.footer-newsletter button:hover { background: var(--amber-light); }

/* Link columns */
.footer-col-title {
  font-family: var(--ff-display);
  font-size: 0.9rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: var(--cream);
  margin-bottom: 20px;
}

.footer-col-links {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.footer-col-links a {
  font-size: 0.85rem;
  color: rgba(245,240,232,0.5);
  transition: color 0.2s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.footer-col-links a:hover { color: var(--cream); }

/* Footer bottom */
.footer-bottom {
  padding: 24px 80px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
}

.footer-copy {
  font-size: 0.78rem;
  color: rgba(245,240,232,0.35);
}

.footer-socials {
  display: flex;
  gap: 10px;
}

.social-link {
  width: 38px; height: 38px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: rgba(245,240,232,0.5);
  font-size: 0.9rem;
  transition: background 0.2s, color 0.2s, border-color 0.2s, transform 0.2s var(--ease-bounce);
}

.social-link:hover {
  background: rgba(110,201,122,0.15);
  border-color: rgba(110,201,122,0.3);
  color: #6EC97A;
  transform: translateY(-3px);
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1024px) {
  .site-header { padding: 0 28px; }
  .hero-content { padding: 0 40px; }
  .hero-trust   { left: 40px; }
  .category-section { padding: 72px 40px; }
  .features-section { padding: 56px 40px; }
  .features-grid { grid-template-columns: repeat(2, 1fr); gap: 28px; }
  .footer-main { grid-template-columns: 1fr 1fr; padding: 56px 40px 40px; }
  .footer-bottom { padding: 20px 40px; }
}

@media (max-width: 768px) {
  :root { --nav-h: 64px; }
  .site-header { padding: 0 20px; }
  .nav-menu > li:not(.login-li):not(.profile-dropdown) { display: none; }
  .hero-content { padding: 0 24px; }
  .hero-trust { display: none; }
  .scroll-hint { display: none; }
  .category-section { padding: 56px 20px; }
  .category-header { flex-direction: column; align-items: flex-start; }
  .category-subtitle { text-align: left; }
  .category-grid { grid-template-columns: 1fr; }
  .category-card { aspect-ratio: 4/3 !important; }
  .features-section { padding: 48px 20px; }
  .features-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
  .footer-main { grid-template-columns: 1fr; padding: 48px 20px 32px; gap: 36px; }
  .footer-bottom { padding: 20px; flex-direction: column; align-items: flex-start; }
  .footer-newsletter { max-width: 100%; }
  .marquee-strip { display: none; }
}
</style>
</head>
<body>

<!-- ================================================== HEADER -->
<?php include __DIR__ . '/layout/header.php'; ?>

<!-- ================================================== HERO -->
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
    <div class="trust-item">
      <div class="trust-icon">🌱</div>
      No Chemicals
    </div>
    <div class="trust-item">
      <div class="trust-icon">🏡</div>
      Home-Grown
    </div>
    <div class="trust-item">
      <div class="trust-icon">🤝</div>
      Local Sellers
    </div>
  </div>

  <div class="scroll-hint">
    <div class="scroll-line"></div>
    Scroll
  </div>
</section>

<!-- ================================================== MARQUEE -->
<div class="marquee-strip" aria-hidden="true">
  <div class="marquee-track">
    <!-- Duplicated for seamless loop -->
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

<!-- ================================================== CATEGORIES -->
<section class="category-section">
  <div class="category-header">
    <div class="category-header-left">
      <div class="category-eyebrow">Browse by Category</div>
      <h2 class="category-title">What are you<br>looking for?</h2>
    </div>
    <p class="category-subtitle">Freshness, organized just for you — straight from the source.</p>
  </div>

  <div class="category-grid">

    <!-- VEGETABLES -->
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

    <!-- FRUITS -->
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

<!-- ================================================== FEATURES -->
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

<!-- ================================================== FOOTER -->
<?php include __DIR__ . '/layout/footer.php'; ?>

<script>
// Header scroll behaviour
(function () {
  const header = document.querySelector('.site-header');
  if (!header) return;
  header.classList.add('transparent');

  const onScroll = () => {
    if (window.scrollY > 40) {
      header.classList.remove('transparent');
      header.classList.add('scrolled');
    } else {
      header.classList.add('transparent');
      header.classList.remove('scrolled');
    }
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

// Profile dropdown
document.addEventListener('DOMContentLoaded', function () {
  const profileBtn  = document.getElementById('profileBtn');
  const dropdownMenu = document.getElementById('dropdownMenu');
  if (!profileBtn || !dropdownMenu) return;

  const open  = () => { dropdownMenu.classList.add('open'); profileBtn.classList.add('open'); profileBtn.setAttribute('aria-expanded','true'); dropdownMenu.setAttribute('aria-hidden','false'); };
  const close = () => { dropdownMenu.classList.remove('open'); profileBtn.classList.remove('open'); profileBtn.setAttribute('aria-expanded','false'); dropdownMenu.setAttribute('aria-hidden','true'); };
  const toggle = () => dropdownMenu.classList.contains('open') ? close() : open();

  profileBtn.addEventListener('click', e => { e.stopPropagation(); toggle(); });
  dropdownMenu.addEventListener('click', e => e.stopPropagation());
  document.addEventListener('click', close);
  document.addEventListener('keydown', e => e.key === 'Escape' && close());
});
</script>

</body>
</html>
