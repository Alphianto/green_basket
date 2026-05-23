/**
 * ADMIN DASHBOARD INTERACTION SCRIPT (TRADITIONAL NAVIGATION)
 *
 * This version of the script removes all Single Page Application (SPA) logic 
 * (AJAX fetching, URL manipulation via history.pushState). 
 * It only handles CSS class manipulation for a smooth page-load animation.
 */

document.addEventListener('DOMContentLoaded', () => {
    
    const body = document.body;

    /**
     * Triggers the staggered slide-up animation for content elements 
     * that are statically present in the DOM after a full page load.
     * @param {HTMLElement} container - The main content wrapper element.
     */
    const triggerSlideUpAnimation = (container) => {
        // Find all animatable elements defined by the CSS classes
        const animatableElements = container.querySelectorAll('.stat-card, .quick-action-btn, .table-responsive, .admin-page-container');
        
        // 1. Ensure the 'visible' class is removed temporarily to restart the transition
        animatableElements.forEach(el => {
            el.classList.remove('visible');
            // Force a DOM reflow (this is crucial to restart the CSS transition)
            void el.offsetWidth;
        });

        // 2. Re-add the 'visible' class with a staggered delay
        setTimeout(() => {
            animatableElements.forEach((el, index) => {
                // Apply a slight delay to achieve the staggered effect
                el.style.transitionDelay = `${index * 0.05}s`; 
                el.classList.add('visible');
            });
        }, 50); 
    };

    /**
     * Executes once the entire page, including assets, has loaded.
     */
    window.onload = () => {
        // 1. Initial Page Fade-In: 
        // Removes the loading class to trigger the CSS transition (opacity: 0 to opacity: 1)
        body.classList.remove('loading');
        body.classList.add('loaded');
        
        // 2. Content Animation:
        // Triggers the slide-up animation for the static content
        const staticContent = document.getElementById('dashboard-content-area');
        if (staticContent) {
            triggerSlideUpAnimation(staticContent);
        }
    };
    
    // NOTE: All navigation and link handling is now done by the browser 
    // using the standard 'href' attribute in your PHP files.
});/**
 * ADMIN DASHBOARD INTERACTION SCRIPT (TRADITIONAL NAVIGATION)
 *
 * This version of the script removes all Single Page Application (SPA) logic 
 * (AJAX fetching, URL manipulation via history.pushState). 
 * It only handles CSS class manipulation for a smooth page-load animation.
 */

document.addEventListener('DOMContentLoaded', () => {
    
    const body = document.body;

    /**
     * Triggers the staggered slide-up animation for content elements 
     * that are statically present in the DOM after a full page load.
     * @param {HTMLElement} container - The main content wrapper element.
     */
    const triggerSlideUpAnimation = (container) => {
        // Find all animatable elements defined by the CSS classes
        const animatableElements = container.querySelectorAll('.stat-card, .quick-action-btn, .table-responsive, .admin-page-container, .card');
        
        // 1. Ensure the 'visible' class is removed temporarily to restart the transition
        animatableElements.forEach(el => {
            el.classList.remove('visible');
            // Force a DOM reflow (this is crucial to restart the CSS transition)
            void el.offsetWidth;
        });

        // 2. Re-add the 'visible' class with a staggered delay
        setTimeout(() => {
            animatableElements.forEach((el, index) => {
                // Apply a slight delay to achieve the staggered effect
                el.style.transitionDelay = `${index * 0.05}s`; 
                el.classList.add('visible');
            });
        }, 50); 
    };

    /**
     * Executes once the entire page, including assets, has loaded.
     */
    window.onload = () => {
        // 1. Initial Page Fade-In: 
        // Removes the loading class to trigger the CSS transition (opacity: 0 to opacity: 1)
        body.classList.remove('loading');
        body.classList.add('loaded');
        
        // 2. Content Animation:
        // Triggers the slide-up animation for the static content
        const staticContent = document.getElementById('dashboard-content-area');
        if (staticContent) {
            triggerSlideUpAnimation(staticContent);
        }
    };
    
    // NOTE: All navigation and link handling is now done by the browser 
    // via traditional full-page loads, so no AJAX/history logic is required here.
});
