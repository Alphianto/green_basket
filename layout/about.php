<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About — GreenBasket</title>
<link rel="icon" type="image/png" href="../style/imgs/gb.png">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,700&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&family=Lemon&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
/* ══════════════════════════════════════════
   GreenBasket — About Page
   Developer: Antony Alphin
   ══════════════════════════════════════════ */
:root {
  --forest:      #1A3020;
  --moss:        #2C4A2E;
  --sage:        #7A9E7E;
  --mint:        #B8D4BB;
  --cream:       #F5F0E8;
  --parchment:   #EDE7D9;
  --gb-green:    #6EC97A;
  --vivid:       #22C55E;
  --amber:       #C8955A;
  --amber-light: #E8B07A;
  --white:       #FFFFFF;
  --charcoal:    #1C1C1C;
  --muted:       #6B7B6D;
  --nav-h:       68px;
  --ff-display:  'Playfair Display', Georgia, serif;
  --ff-body:     'DM Sans', sans-serif;
  --ff-mono:     'DM Mono', monospace;
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
  padding-top: var(--nav-h);
  overflow-x: hidden;
}

a { text-decoration: none; color: inherit; }
ul { list-style: none; }
img { display: block; max-width: 100%; }

/* ── headweb.php header styles ── */
.site-header1 {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 1000;
  height: var(--nav-h);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 48px;
  background: rgba(26,48,32,0.97);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  box-shadow: 0 2px 32px rgba(0,0,0,0.22);
  border-bottom: 1px solid rgba(110,201,122,0.12);
}
.site-header1 .site-logo a { display: flex; align-items: baseline; gap: 1px; text-decoration: none; }
.logo-green1 {
  background: linear-gradient(135deg, #5DF56A 0%, #22C55E 45%, #16A34A 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
  font-family: 'Lemon', cursive; font-size: 1.6rem; font-weight: 900;
}
.logo-basket1 {
  color: var(--cream);
  font-family: var(--ff-display);
  font-size: 1.55rem; font-weight: 700; letter-spacing: -0.02em;
}
.nav-menu1 { display: flex; align-items: center; gap: 2px; list-style: none; padding: 0; margin: 0; }
.nav-menu1 > li > a {
  font-family: var(--ff-body); font-size: 0.82rem; font-weight: 500;
  letter-spacing: 0.08em; text-transform: uppercase; color: rgba(245,240,232,0.82);
  padding: 8px 15px; border-radius: 100px; text-decoration: none;
  transition: color 0.2s, background 0.2s;
}
.nav-menu1 > li > a:hover { color: #fff; background: rgba(255,255,255,0.1); }
.btn-gradient1 {
  display: inline-flex; align-items: center; font-family: var(--ff-body);
  font-size: 0.8rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
  color: var(--forest) !important; -webkit-text-fill-color: var(--forest) !important;
  background: linear-gradient(135deg, #5DF56A, #22C55E); padding: 9px 22px;
  border-radius: 100px; text-decoration: none; box-shadow: 0 3px 16px rgba(78,197,88,0.32);
  transition: transform 0.2s var(--ease-bounce), box-shadow 0.2s;
}
.btn-gradient1:hover { transform: translateY(-2px) scale(1.03); box-shadow: 0 7px 22px rgba(110,201,122,0.48); }
.profile-dropdown1 { position: relative; }
.btn-profile-style1 {
  display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.2); border-radius: 100px; color: var(--cream);
  font-family: var(--ff-body); font-size: 0.84rem; font-weight: 500;
  padding: 8px 18px; cursor: pointer; transition: background 0.2s, border-color 0.2s;
}
.btn-profile-style1:hover, .btn-profile-style1.open {
  background: rgba(255,255,255,0.18); border-color: rgba(110,201,122,0.45);
}
.dropdown-icon1 { font-size: 0.66rem; transition: transform 0.22s var(--ease-smooth); display: inline-block; }
.btn-profile-style1.open .dropdown-icon1 { transform: rotate(180deg); }
.dropdown-link-group1 {
  position: absolute; top: calc(100% + 14px); right: 0; min-width: 224px;
  background: var(--forest); border: 1px solid rgba(110,201,122,0.16); border-radius: 18px;
  overflow: hidden; list-style: none; padding: 8px 0;
  box-shadow: 0 20px 52px rgba(0,0,0,0.38); opacity: 0; pointer-events: none;
  transform: translateY(-10px) scale(0.96); transform-origin: top right;
  transition: opacity 0.2s var(--ease-smooth), transform 0.2s var(--ease-smooth);
}
.dropdown-link-group1.open { opacity: 1; pointer-events: all; transform: translateY(0) scale(1); }
.dropdown-link-group1 li a {
  display: flex; align-items: center; gap: 10px; padding: 11px 18px;
  font-family: var(--ff-body); font-size: 0.87rem; color: var(--mint);
  text-decoration: none; transition: background 0.15s, color 0.15s;
}
.dropdown-link-group1 li a:hover { background: rgba(110,201,122,0.1); color: #fff; }
.list-icon1 { font-size: 1rem; width: 20px; text-align: center; flex-shrink: 0; }
.divider1 { height: 1px; background: rgba(255,255,255,0.08); margin: 4px 14px; }
@media (max-width: 768px) {
  .site-header1 { padding: 0 20px; }
  .nav-menu1 > li:not(.profile-dropdown1):not(.login-li1) { display: none; }
}

/* ══════════════════
   PAGE HERO
   ══════════════════ */
.about-hero {
  background: linear-gradient(150deg, var(--forest) 0%, var(--moss) 55%, #3d6b43 100%);
  padding: 88px 80px 96px;
  position: relative;
  overflow: hidden;
}

/* Decorative rings */
.about-hero::before {
  content: '';
  position: absolute;
  right: -120px; top: -120px;
  width: 520px; height: 520px;
  border-radius: 50%;
  border: 1px solid rgba(110,201,122,0.14);
  pointer-events: none;
}
.about-hero::after {
  content: '';
  position: absolute;
  right: -60px; top: -60px;
  width: 380px; height: 380px;
  border-radius: 50%;
  border: 1px solid rgba(110,201,122,0.2);
  pointer-events: none;
}

.hero-glow {
  position: absolute;
  left: -100px; bottom: -100px;
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(93,245,106,0.12) 0%, transparent 65%);
  border-radius: 50%;
  pointer-events: none;
}

.hero-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-family: var(--ff-mono);
  font-size: 0.68rem;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--gb-green);
  margin-bottom: 20px;
}
.hero-eyebrow::before {
  content: ''; display: block; width: 22px; height: 1px; background: var(--gb-green);
}

.hero-title {
  font-family: var(--ff-display);
  font-size: clamp(2.4rem, 5.5vw, 4rem);
  font-weight: 900;
  letter-spacing: -0.03em;
  color: var(--white);
  line-height: 1.05;
  max-width: 680px;
  margin-bottom: 24px;
}
.hero-title em { font-style: italic; color: var(--gb-green); }

.hero-desc {
  font-size: 1.05rem;
  color: rgba(245,240,232,0.72);
  max-width: 520px;
  line-height: 1.7;
  margin-bottom: 36px;
}

.hero-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: rgba(255,255,255,0.1);
  border: 1px solid rgba(255,255,255,0.18);
  border-radius: 100px;
  padding: 8px 18px;
  font-family: var(--ff-body);
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--mint);
  letter-spacing: 0.02em;
}
.hero-badge i { color: var(--gb-green); font-size: 0.85rem; }

/* ══════════════════
   SECTION SHELL
   ══════════════════ */
.section {
  padding: 80px 80px;
  max-width: 1280px;
  margin: 0 auto;
}
.section-eyebrow {
  font-family: var(--ff-mono);
  font-size: 0.65rem;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--gb-green);
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.section-eyebrow::before { content: ''; display: block; width: 18px; height: 1px; background: var(--gb-green); }
.section-title {
  font-family: var(--ff-display);
  font-size: clamp(1.7rem, 3vw, 2.6rem);
  font-weight: 900;
  color: var(--forest);
  letter-spacing: -0.025em;
  line-height: 1.1;
}
.section-title em { font-style: italic; color: var(--sage); }

/* ══════════════════
   DEVELOPER SECTION
   ══════════════════ */
.dev-section {
  background: var(--forest);
  padding: 0;
  overflow: hidden;
  position: relative;
}

/* Subtle pattern overlay */
.dev-section::before {
  content: '';
  position: absolute; inset: 0;
  background-image: radial-gradient(circle at 1px 1px, rgba(110,201,122,0.08) 1px, transparent 0);
  background-size: 28px 28px;
  pointer-events: none;
}

.dev-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 80px 80px;
  display: grid;
  grid-template-columns: 1fr 1.6fr;
  gap: 72px;
  align-items: center;
  position: relative;
  z-index: 1;
}

/* Developer card */
.dev-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(110,201,122,0.2);
  border-radius: 24px;
  padding: 40px 32px;
  text-align: center;
  position: relative;
}

.dev-avatar {
  width: 100px; height: 100px;
  border-radius: 50%;
  background: linear-gradient(135deg, #5DF56A, #16A34A);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-family: var(--ff-display);
  font-size: 2.2rem;
  font-weight: 900;
  color: var(--forest);
  box-shadow: 0 0 0 6px rgba(110,201,122,0.15), 0 0 0 12px rgba(110,201,122,0.07);
}

.dev-name {
  font-family: var(--ff-display);
  font-size: 1.55rem;
  font-weight: 900;
  color: var(--white);
  letter-spacing: -0.02em;
  margin-bottom: 4px;
}

.dev-role {
  font-family: var(--ff-mono);
  font-size: 0.7rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--gb-green);
  margin-bottom: 24px;
}

.dev-meta {
  display: flex;
  flex-direction: column;
  gap: 10px;
  text-align: left;
  margin-bottom: 24px;
}

.dev-meta-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  font-size: 0.84rem;
  color: rgba(184,212,187,0.85);
  line-height: 1.4;
}
.dev-meta-item i {
  color: var(--gb-green);
  width: 16px;
  flex-shrink: 0;
  margin-top: 2px;
  font-size: 0.82rem;
}

.dev-contact-links {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.dev-contact-link {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 14px;
  background: rgba(110,201,122,0.1);
  border: 1px solid rgba(110,201,122,0.2);
  border-radius: 10px;
  font-family: var(--ff-mono);
  font-size: 0.72rem;
  color: var(--mint);
  letter-spacing: 0.04em;
  text-decoration: none;
  transition: background 0.2s, border-color 0.2s;
  word-break: break-all;
}
.dev-contact-link:hover { background: rgba(110,201,122,0.18); border-color: rgba(110,201,122,0.4); color: #fff; }
.dev-contact-link i { color: var(--gb-green); flex-shrink: 0; }

/* Developer story */
.dev-story .section-eyebrow { /* reuse but different context */ }

.dev-story-title {
  font-family: var(--ff-display);
  font-size: clamp(1.8rem, 3vw, 2.8rem);
  font-weight: 900;
  color: var(--white);
  letter-spacing: -0.025em;
  line-height: 1.1;
  margin-bottom: 28px;
}
.dev-story-title em { font-style: italic; color: var(--gb-green); }

.dev-story p {
  font-size: 0.97rem;
  color: rgba(184,212,187,0.8);
  line-height: 1.8;
  margin-bottom: 18px;
}
.dev-story p strong { color: var(--mint); font-weight: 600; }

.dev-tech-stack {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 28px;
}
.tech-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: rgba(255,255,255,0.07);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 100px;
  font-family: var(--ff-mono);
  font-size: 0.7rem;
  color: var(--mint);
  letter-spacing: 0.06em;
}
.tech-pill i { color: var(--gb-green); }

/* ══════════════════
   PROJECT SECTION
   ══════════════════ */
.project-section {
  background: var(--white);
}
.project-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 80px 80px;
}

.project-grid {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 64px;
  align-items: center;
  margin-top: 48px;
}

.project-text p {
  font-size: 0.97rem;
  line-height: 1.82;
  color: #4a5a4c;
  margin-bottom: 16px;
}
.project-text p strong { color: var(--forest); }

.project-highlights {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 28px;
}
.project-highlight-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 16px 18px;
  background: var(--parchment);
  border-radius: 12px;
  border-left: 3px solid var(--gb-green);
}
.ph-icon {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, #5DF56A, #22C55E);
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  color: var(--forest);
  font-size: 0.95rem;
  flex-shrink: 0;
}
.ph-text h4 {
  font-family: var(--ff-body);
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--forest);
  margin-bottom: 3px;
}
.ph-text p { font-size: 0.82rem; color: var(--muted); line-height: 1.5; margin: 0; }

/* Stats block */
.project-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.stat-card {
  background: var(--parchment);
  border-radius: 16px;
  padding: 28px 20px;
  text-align: center;
  border: 1px solid rgba(44,74,46,0.08);
  transition: transform 0.2s var(--ease-smooth), box-shadow 0.2s;
}
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(26,48,32,0.1); }
.stat-number {
  font-family: var(--ff-display);
  font-size: 2.2rem;
  font-weight: 900;
  color: var(--forest);
  letter-spacing: -0.03em;
  line-height: 1;
  margin-bottom: 8px;
}
.stat-number span { color: var(--gb-green); }
.stat-label {
  font-family: var(--ff-mono);
  font-size: 0.67rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--muted);
}

/* ══════════════════
   VALUES SECTION
   ══════════════════ */
.values-section {
  background: var(--parchment);
}
.values-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 80px 80px;
}

.values-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-top: 48px;
}

.value-card {
  background: var(--white);
  border-radius: 20px;
  padding: 36px 28px;
  border: 1px solid rgba(44,74,46,0.07);
  box-shadow: 0 2px 16px rgba(26,48,32,0.06);
  position: relative;
  overflow: hidden;
  transition: transform 0.25s var(--ease-smooth), box-shadow 0.25s;
}
.value-card::after {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, #5DF56A, #22C55E);
}
.value-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 48px rgba(26,48,32,0.12);
}

.value-icon {
  width: 52px; height: 52px;
  background: linear-gradient(135deg, rgba(93,245,106,0.15), rgba(34,197,94,0.1));
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem;
  margin-bottom: 20px;
  border: 1px solid rgba(110,201,122,0.2);
}
.value-card h3 {
  font-family: var(--ff-display);
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--forest);
  margin-bottom: 10px;
  letter-spacing: -0.01em;
}
.value-card p {
  font-size: 0.88rem;
  color: var(--muted);
  line-height: 1.72;
}

/* ══════════════════
   CTA SECTION
   ══════════════════ */
.cta-section {
  background: linear-gradient(150deg, var(--forest) 0%, var(--moss) 100%);
  position: relative;
  overflow: hidden;
}
.cta-section::after {
  content: '';
  position: absolute;
  right: -80px; top: -80px;
  width: 400px; height: 400px;
  border-radius: 50%;
  border: 1px solid rgba(110,201,122,0.15);
  pointer-events: none;
}
.cta-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 80px 80px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 48px;
  position: relative;
  z-index: 1;
}
.cta-text h2 {
  font-family: var(--ff-display);
  font-size: clamp(1.8rem, 3vw, 2.8rem);
  font-weight: 900;
  color: var(--white);
  letter-spacing: -0.025em;
  margin-bottom: 12px;
}
.cta-text h2 em { font-style: italic; color: var(--gb-green); }
.cta-text p { font-size: 1rem; color: rgba(184,212,187,0.78); max-width: 440px; line-height: 1.65; }
.cta-buttons { display: flex; gap: 14px; flex-shrink: 0; flex-wrap: wrap; }
.btn-cta-primary {
  display: inline-flex; align-items: center; gap: 8px;
  background: linear-gradient(135deg, #5DF56A, #22C55E);
  color: var(--forest); font-family: var(--ff-body); font-size: 0.86rem;
  font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;
  padding: 14px 30px; border-radius: 100px; text-decoration: none;
  box-shadow: 0 4px 20px rgba(34,197,94,0.35);
  transition: transform 0.2s var(--ease-bounce), box-shadow 0.2s;
  -webkit-text-fill-color: var(--forest);
}
.btn-cta-primary:hover { transform: translateY(-2px) scale(1.03); box-shadow: 0 8px 28px rgba(34,197,94,0.5); }
.btn-cta-outline {
  display: inline-flex; align-items: center; gap: 8px;
  background: transparent; color: var(--mint);
  font-family: var(--ff-body); font-size: 0.86rem; font-weight: 600;
  letter-spacing: 0.06em; text-transform: uppercase;
  padding: 13px 28px; border-radius: 100px; text-decoration: none;
  border: 1.5px solid rgba(184,212,187,0.35);
  transition: border-color 0.2s, background 0.2s, color 0.2s;
}
.btn-cta-outline:hover { border-color: rgba(184,212,187,0.7); background: rgba(255,255,255,0.06); color: #fff; }

/* ══════════════════
   SCROLL ANIMATIONS
   ══════════════════ */
.fade-up {
  opacity: 0;
  transform: translateY(36px);
  transition: opacity 0.65s var(--ease-smooth), transform 0.65s var(--ease-smooth);
}
.fade-up.visible { opacity: 1; transform: translateY(0); }
.fade-up:nth-child(1) { transition-delay: 0.05s; }
.fade-up:nth-child(2) { transition-delay: 0.15s; }
.fade-up:nth-child(3) { transition-delay: 0.25s; }

/* ══════════════════
   RESPONSIVE
   ══════════════════ */
@media (max-width: 1024px) {
  .about-hero, .section, .dev-inner, .project-inner, .values-inner, .cta-inner { padding-left: 40px; padding-right: 40px; }
  .dev-inner { grid-template-columns: 1fr; gap: 48px; }
  .project-grid { grid-template-columns: 1fr; gap: 40px; }
  .cta-inner { flex-direction: column; text-align: center; }
  .cta-text p { max-width: 100%; }
  .cta-buttons { justify-content: center; }
}
@media (max-width: 768px) {
  .about-hero, .section, .dev-inner, .project-inner, .values-inner, .cta-inner { padding-left: 24px; padding-right: 24px; padding-top: 56px; padding-bottom: 56px; }
  .values-grid { grid-template-columns: 1fr; }
  .project-stats { grid-template-columns: repeat(2, 1fr); }
  .hero-title { font-size: 2.1rem; }
}
@media (max-width: 480px) {
  .project-stats { grid-template-columns: 1fr 1fr; }
}
</style>
</head>

<body>

<?php include __DIR__ . '/headweb.php'; ?>

<!-- ══ HERO ══ -->
<section class="about-hero">
  <div class="hero-glow"></div>
  <div class="hero-eyebrow">About GreenBasket</div>
  <h1 class="hero-title">Hyper-local produce,<br><em>straight from the garden.</em></h1>
  <p class="hero-desc">A community-driven marketplace connecting urban home growers with conscious local buyers — no middlemen, no cold chains, just fresh.</p>
  <div class="hero-badges">
    <span class="hero-badge"><i class="fas fa-seedling"></i> 100% Locally Grown</span>
    <span class="hero-badge"><i class="fas fa-leaf"></i> Zero Industrial Chain</span>
    <span class="hero-badge"><i class="fas fa-code"></i> Self-Developed PHP Project</span>
    <span class="hero-badge"><i class="fas fa-graduation-cap"></i> MCA 2024–2026</span>
  </div>
</section>

<!-- ══ DEVELOPER SECTION ══ -->
<section class="dev-section">
  <div class="dev-inner">

    <!-- Left: Card -->
    <div class="dev-card fade-up">
      <div class="dev-avatar">AA</div>
      <div class="dev-name">Antony Alphin</div>
      <div class="dev-role">Developer &amp; Creator</div>

      <div class="dev-meta">
        <div class="dev-meta-item">
          <i class="fas fa-graduation-cap"></i>
          <span>MCA Student, 2024–2026<br>
            <strong style="color:var(--mint)">Depaul Institute of Science and Technology</strong><br>
            Angamaly, Kerala</span>
        </div>
        <div class="dev-meta-item">
          <i class="fas fa-university"></i>
          <span>Affiliated with <strong style="color:var(--mint)">Mahatma Gandhi University</strong>, Kottayam</span>
        </div>
        <div class="dev-meta-item">
          <i class="fas fa-map-marker-alt"></i>
          <span>Pallipuram P.O., Ernakulam, Kerala, India</span>
        </div>
      </div>

      <div class="dev-contact-links">
        <a href="mailto:antonyalphin57@gmail.com" class="dev-contact-link">
          <i class="fas fa-envelope"></i> antonyalphin57@gmail.com
        </a>
        <a href="mailto:antoalphin@depaul.edu.in" class="dev-contact-link">
          <i class="fas fa-envelope"></i> antoalphin@depaul.edu.in
        </a>
      </div>
    </div>

    <!-- Right: Story -->
    <div class="dev-story fade-up">
      <div class="section-eyebrow">The Developer</div>
      <h2 class="dev-story-title">Built from scratch,<br><em>line by line.</em></h2>

      <p>GreenBasket is a <strong>fully self-developed PHP project</strong> by Antony Alphin, created as part of the Master of Computer Applications (MCA) programme at Depaul Institute of Science and Technology, Angamaly — affiliated with Mahatma Gandhi University.</p>

      <p>Every feature you see — from the product listings and seller dashboards to the cart system, session management, and filter engine — was <strong>designed, coded, and refined</strong> entirely by Antony. No templates, no frameworks, just raw PHP, MySQL, and front-end craftsmanship.</p>

      <p>The project was born out of a simple observation: neighbours grow surplus vegetables and fruits on their terraces and gardens with <strong>no way to share or sell them locally</strong>. GreenBasket solves exactly that — a hyper-local, zero-middleman produce marketplace that puts the community first.</p>

      <div class="dev-tech-stack">
        <span class="tech-pill"><i class="fab fa-php"></i> PHP 8</span>
        <span class="tech-pill"><i class="fas fa-database"></i> MySQL</span>
        <span class="tech-pill"><i class="fab fa-html5"></i> HTML5</span>
        <span class="tech-pill"><i class="fab fa-css3-alt"></i> CSS3</span>
        <span class="tech-pill"><i class="fab fa-js"></i> JavaScript</span>
        <span class="tech-pill"><i class="fas fa-server"></i> Apache</span>
        <span class="tech-pill"><i class="fas fa-lock"></i> Sessions &amp; Auth</span>
        <span class="tech-pill"><i class="fas fa-filter"></i> Filter Engine</span>
      </div>
    </div>

  </div>
</section>

<!-- ══ PROJECT SECTION ══ -->
<section class="project-section">
  <div class="project-inner">
    <div class="section-eyebrow">The Project</div>
    <h2 class="section-title">What is <em>GreenBasket?</em></h2>

    <div class="project-grid">
      <div class="project-text">
        <p>GreenBasket is a <strong>community produce marketplace</strong> built on the idea that the freshest food travels the shortest distance. Instead of buying from supermarkets that source from distant farms, users can browse and purchase directly from local home growers in their own neighbourhood.</p>
        <p>The platform supports <strong>both buyers and sellers</strong> — anyone can register as a seller, list their home-grown fruits and vegetables, set prices, and manage orders. Buyers can filter by price, location, rating, and deals to find exactly what they need.</p>
        <p>Built entirely as a <strong>self-initiated MCA academic project</strong>, GreenBasket demonstrates full-stack web development — from database schema design and secure session handling to a polished, responsive user interface.</p>

        <div class="project-highlights">
          <div class="project-highlight-item">
            <div class="ph-icon"><i class="fas fa-store"></i></div>
            <div class="ph-text">
              <h4>Seller Dashboard</h4>
              <p>Growers can list products, manage inventory, and track orders.</p>
            </div>
          </div>
          <div class="project-highlight-item">
            <div class="ph-icon"><i class="fas fa-filter"></i></div>
            <div class="ph-text">
              <h4>Smart Filter Engine</h4>
              <p>Filter by price range, location, discount, and star ratings.</p>
            </div>
          </div>
          <div class="project-highlight-item">
            <div class="ph-icon"><i class="fas fa-star"></i></div>
            <div class="ph-text">
              <h4>Ratings System</h4>
              <p>Independent product and seller ratings build community trust.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="project-stats">
        <div class="stat-card fade-up">
          <div class="stat-number">2<span>+</span></div>
          <div class="stat-label">Categories</div>
        </div>
        <div class="stat-card fade-up">
          <div class="stat-number">0<span>%</span></div>
          <div class="stat-label">Middlemen</div>
        </div>
        <div class="stat-card fade-up">
          <div class="stat-number">1<span>k+</span></div>
          <div class="stat-label">Lines of Code</div>
        </div>
        <div class="stat-card fade-up">
          <div class="stat-number">5<span>★</span></div>
          <div class="stat-label">Fresh Ambition</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ VALUES ══ -->
<section class="values-section">
  <div class="values-inner">
    <div class="section-eyebrow">Core Values</div>
    <h2 class="section-title">Why <em>GreenBasket?</em></h2>

    <div class="values-grid">
      <div class="value-card fade-up">
        <div class="value-icon">🌿</div>
        <h3>Hyper-Local Freshness</h3>
        <p>Produce travels metres, not kilometres. From a neighbour's terrace garden to your kitchen in hours — taste a freshness no supermarket can match.</p>
      </div>
      <div class="value-card fade-up">
        <div class="value-icon">🤝</div>
        <h3>Community Empowerment</h3>
        <p>Every home gardener becomes a micro-entrepreneur. GreenBasket gives urban growers a dignified, simple way to share their surplus and earn.</p>
      </div>
      <div class="value-card fade-up">
        <div class="value-icon">🌍</div>
        <h3>Minimal Footprint</h3>
        <p>Local trade means less packaging, zero cold-chain, and dramatically reduced carbon emissions. Conscious consumption starts at the neighbourhood level.</p>
      </div>
    </div>
  </div>
</section>

<!-- ══ CTA ══ -->
<section class="cta-section">
  <div class="cta-inner">
    <div class="cta-text">
      <h2>Ready to shop or sell <em>local?</em></h2>
      <p>Join GreenBasket today — whether you have a terrace full of tomatoes or just want the freshest vegetables in town.</p>
    </div>
    <div class="cta-buttons">
      <a href="/account/register.php" class="btn-cta-primary"><i class="fas fa-seedling"></i> Join GreenBasket</a>
      <a href="/shop/vegetables.php" class="btn-cta-outline"><i class="fas fa-basket-shopping"></i> Browse Produce</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>

<script>
// Dropdown for headweb.php
document.addEventListener('DOMContentLoaded', function () {
  const btn  = document.getElementById('profileBtn1');
  const menu = document.getElementById('dropdownMenu1');
  if (!btn || !menu) return;
  const open  = () => { menu.classList.add('open'); btn.classList.add('open'); btn.setAttribute('aria-expanded','true'); };
  const close = () => { menu.classList.remove('open'); btn.classList.remove('open'); btn.setAttribute('aria-expanded','false'); };
  btn.addEventListener('click', e => { e.stopPropagation(); menu.classList.contains('open') ? close() : open(); });
  menu.addEventListener('click', e => e.stopPropagation());
  document.addEventListener('click', close);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
});

// Scroll fade-in
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.08, rootMargin: '0px 0px -60px 0px' });

document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
</script>
</body>
</html>
