// rating.js - interactive star widgets (Product Rating Only)
document.addEventListener('DOMContentLoaded', () => {
    // Select the single star rating row (product rating)
    const starRow = document.querySelector('.stars[data-target="product_rating"]');
    
    if (starRow) {
        const targetInputId = starRow.dataset.target;
        const hiddenInput = document.getElementById(targetInputId);
        const stars = Array.from(starRow.querySelectorAll('.star'));

        // Function to visually set the rating
        function setRating(r) {
            stars.forEach(st => {
                st.classList.toggle('active', parseInt(st.dataset.value,10) <= r);
            });
            // Update the hidden input value
            if (hiddenInput) hiddenInput.value = r;
        }

        // Mouse/Touch events for interaction
        stars.forEach(st => {
            // Hover/Mouseenter effect
            st.addEventListener('mouseenter', () => {
                setRating(parseInt(st.dataset.value,10));
            });
            
            // Click/Tap effect (final selection)
            st.addEventListener('click', () => {
                setRating(parseInt(st.dataset.value,10));
            });
        });

        // Mouseleave/Touch-end event: Revert to the stored rating value
        starRow.addEventListener('mouseleave', () => {
            const cur = parseInt(hiddenInput.value || 0,10);
            setRating(cur);
        });

        // Initialize display if a value is pre-set (though unlikely on first load)
        const initialValue = parseInt(hiddenInput.value || 0, 10);
        setRating(initialValue);
    }
    
    // Server-side validation handles the required rating check, 
    // so client-side alert is removed to follow the guardrail.
});
