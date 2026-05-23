<?php
session_start();
// Include your header/footer layouts (assuming they are in the 'layout' folder)
// The actual content of the header/footer is assumed to be handled by the include files.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - GreenBasket</title>
    <link rel="icon" type="image/png" href="../style/imgs/gb.png">
    <!-- Include the necessary Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&family=Lemon&display=swap" rel="stylesheet">
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* --- Internal CSS for about.php --- */

        /* --- Fonts and Colors (Define the common variables) --- */
        :root {
            --color-primary: #2eb535; /* Primary Green */
            --color-primary-dark: #0fb22d; /* Darker Green */
            --color-text-dark: #333;
            --color-bg-light: #f7f8fa;
        }

        /* Basic Reset and Typography */
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--color-text-dark);
            background-color: var(--color-bg-light);
        }
        
        /* Assuming 'btn-primary' style from your existing files */
        .btn-primary {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            background: var(--color-primary);
            color: white;
            transition: background 0.3s, transform 0.3s;
        }

        .btn-primary:hover {
            background: var(--color-primary-dark);
            transform: translateY(-2px);
        }

        /* --- 1. Loading Overlay UI --- */
        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.5s ease-out; /* Smooth fade-out */
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--color-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-text {
            margin-top: 15px;
            font-family: 'Poetsen One', sans-serif;
            color: var(--color-primary-dark);
            font-size: 1.2rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* --- Main Content Layout --- */
        .about-page {
            padding-top: 0; 
            min-height: 80vh; /* Ensure content fills the screen vertically */
        }
        
        .section-title {
            font-family: 'Poetsen One', sans-serif;
            color: var(--color-primary-dark);
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .text-center {
            text-align: center;
        }

        /* --- 2. Hero Section --- */
        .about-hero {
            /* Placeholder background - REMOVED TEXT PARAMETER */
            background: url('/style/imgs/Gemini_Generated_Image_svhx6lsvhx6lsvhx.png') no-repeat center center/cover;
            height: 450px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            color: white;
        }

        .about-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4); 
        }

        .hero-content {
            z-index: 10;
            max-width: 800px;
            padding: 20px;
        }

        .hero-title {
            font-family: 'Lemon', cursive;
            font-size: 3.5rem;
            margin-bottom: 10px;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            font-family: 'Inter', sans-serif;
            margin-bottom: 30px;
        }

        /* --- 3. Mission Section --- */
        .mission-section {
            display: flex;
            gap: 40px;
            padding: 60px 10%;
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            transform: translateY(-50px);
            position: relative;
        }

        .mission-text {
            flex: 2;
        }

        .mission-image {
            flex: 1;
            min-width: 300px;
            height: 250px;
            /* Placeholder background - REMOVED TEXT PARAMETER */
            background: url('/style/imgs/Gemini_Generated_Image_2gi5e52gi5e52gi5.png') center center/cover;
            border-radius: 8px;
            overflow: hidden;
        }

        .mission-text p {
            line-height: 1.7;
            margin-bottom: 15px;
            color: var(--color-text-dark);
            font-family: 'Inter', Arial, sans-serif;
        }

        /* --- 4. Value Proposition Grid --- */
        .values-section {
            padding: 50px 10%;
            margin-top: 30px;
            text-align: center;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .value-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
            border-top: 5px solid var(--color-primary);
        }

        .value-card .icon {
            font-size: 2.5rem;
            color: var(--color-primary-dark);
            margin-bottom: 15px;
        }

        .value-card h3 {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            color: var(--color-text-dark);
            margin-bottom: 10px;
        }

        /* --- 5. Call to Action (CTA) --- */
        .cta-section {
            background: var(--color-primary-dark);
            color: white;
            padding: 60px 10%;
            text-align: center;
            margin-top: 50px;
        }

        .cta-section h2 {
            font-family: 'Poetsen One', sans-serif;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .btn-secondary {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            background: white;
            color: var(--color-primary-dark);
            transition: background 0.3s, transform 0.3s;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background: #f0f0f0;
            transform: translateY(-2px);
        }

        /* --- 6. Scroll Fade-In Animation (reusable) --- */
        .scroll-fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .scroll-fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Stagger the grid items */
        .values-grid .scroll-fade-in:nth-child(1) { transition-delay: 0.1s; }
        .values-grid .scroll-fade-in:nth-child(2) { transition-delay: 0.2s; }
        .values-grid .scroll-fade-in:nth-child(3) { transition-delay: 0.3s; }

        /* --- Responsive Adjustments --- */
        @media (max-width: 900px) {
            .mission-section {
                flex-direction: column;
                padding: 40px 5%;
            }
            .mission-image {
                height: 200px;
            }
        }

        @media (max-width: 600px) {
            .hero-title {
                font-size: 2.5rem;
            }
            .hero-subtitle {
                font-size: 1.2rem;
            }
            .mission-section {
                transform: translateY(-20px);
            }
        }
    </style>
    <link rel="stylesheet" href="../style/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&family=Lemon&display=swap" rel="stylesheet">
</head>
<body>

    <!-- 🌟 UI Loading Overlay 🌟 -->
    <div id="loading-overlay">
        <div class="spinner"></div>
        <div class="loading-text">Loading GreenBasket Story...</div>
    </div>

    <!-- UI Component: Header (Assumed to be included) -->
    <?php include __DIR__ . '/header.php';?>

    <main class="about-page">
        <!-- Hero Section -->
        <section class="about-hero">
            <div class="hero-content">
                <!-- UPDATED TEXT -->
                <h1 class="hero-title">Our Community Harvest: Home-Grown, Hyper-Local.</h1>
                <p class="hero-subtitle">Connecting neighbors to share fresh, surplus produce from terraces and gardens.</p>
                <a href="shop/vegetables.php" class="btn-primary">Find Local Produce <i class="fas fa-arrow-right"></i></a>
            </div>
        </section>

        <!-- Our Mission Section (Fade-in on scroll) -->
        <section class="mission-section scroll-fade-in">
            <div class="mission-text">
                <!-- UPDATED TEXT -->
                <h2 class="section-title"><i class="fas fa-hand-holding-heart"></i> Our Hyper-Local Commitment</h2>
                <p>GreenBasket is a true <strong style="font-family: 'Inter', Arial, sans-serif;">community trade platform</strong>. We bypass industrial supply chains entirely, enabling regular individuals—your neighbors, hobbyists, and urban gardeners—to sell the excess bounty from their own small plots, terraces, and backyard gardens.</p>
                <p>This hyper-local model guarantees <strong style="font-family: 'Inter', Arial, sans-serif;">maximum freshness</strong>, drastically reduces food miles, and fosters a resilient local food ecosystem where everyone can participate as both a buyer and a seller.</p>
            </div>
            <div class="mission-image">
                <!-- Placeholder for a relevant image -->
            </div>
        </section>

        <!-- Value Proposition Grid (Animated) -->
        <section class="values-section">
            <h2 class="section-title text-center">Why GreenBasket is Different?</h2>
            <div class="values-grid">
                <!-- UPDATED CARD 1 -->
                <div class="value-card scroll-fade-in">
                    <i class="fas fa-location-arrow icon"></i>
                    <h3>Hyper-Local Freshness</h3>
                    <p>Produce travels the shortest distance possible: from a neighbor's garden to your kitchen. Taste the difference only hours-fresh ingredients can offer.</p>
                </div>
                <!-- UPDATED CARD 2 -->
                <div class="value-card scroll-fade-in">
                    <i class="fas fa-store icon"></i>
                    <h3>Empowering Urban Sellers</h3>
                    <p>We provide a simple, reliable way for home gardeners to monetize their passion and share their natural surplus with the community.</p>
                </div>
                <!-- UPDATED CARD 3 -->
                <div class="value-card scroll-fade-in">
                    <i class="fas fa-globe-asia icon"></i>
                    <h3>Minimal Footprint</h3>
                    <p>By prioritizing local, small-scale trade, we minimize waste and carbon emissions associated with long-distance transportation and industrial packaging.</p>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section scroll-fade-in">
            <div class="cta-content">
                <!-- UPDATED TEXT -->
                <h2>Ready to Share or Shop Local?</h2>
                <p>Join our growing community of urban sellers and conscious buyers today.</p>
                <a href="account/register.php" class="btn-secondary">Join GreenBasket Today</a>
            </div>
        </section>

    </main>

    <!-- UI Component: Footer (Assumed to be included) -->
    <?php include __DIR__ . '/footer.php';?>

    <script>
        // --- Internal JavaScript for about.php ---

        // 1. Loading UI Logic
        document.addEventListener('DOMContentLoaded', () => {
            const overlay = document.getElementById('loading-overlay');
            // Wait a brief moment for smooth effect before hiding the loader
            setTimeout(() => {
                overlay.style.opacity = '0';
                // Remove element after transition finishes to allow interaction
                overlay.addEventListener('transitionend', () => {
                    overlay.style.display = 'none';
                }, { once: true });
            }, 300); // 300ms delay for smooth effect
        });

        // 2. Scroll Fade-In Animation Logic
        const faders = document.querySelectorAll('.scroll-fade-in');

        const appearOptions = {
            // Determines how much of the target must be visible to trigger
            threshold: 0, 
            // Margin around the viewport. Negative margin means element appears earlier.
            rootMargin: "0px 0px -100px 0px" 
        };

        const appearOnScroll = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) {
                    return;
                }
                // When element enters view, add 'visible' class
                entry.target.classList.add('visible');
                // Stop observing the element once it has appeared
                observer.unobserve(entry.target);
            });
        }, appearOptions);

        // Apply the observer to all elements with the 'scroll-fade-in' class
        faders.forEach(fader => {
            appearOnScroll.observe(fader);
        });
    </script>
</body>
</html>
