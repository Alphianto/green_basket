document.addEventListener('DOMContentLoaded', () => {
    // *** FIX: Read values directly from the <body> data attributes ***
    const bodyElement = document.body;
    
    // We get the final MAX_PRICE_DB from the DOM.
    const MAX_PRICE_DB = parseFloat(bodyElement.dataset.maxPrice) || 100; 
    const USER_CITY = bodyElement.dataset.userCity || '';
    
    // Use this constant for all initializations and resets
    const initialMaxPrice = MAX_PRICE_DB;

    const productList = document.querySelector('.product-listing');
    const productCardsOriginal = Array.from(document.querySelectorAll('.product-card'));

    const searchInput = document.getElementById('productSearch');
    const priceRange = document.getElementById('priceRange');
    const maxPriceInput = document.getElementById('maxPriceInput');
    const priceValueDisplay = document.getElementById('priceValue');
    const priceOrder = document.getElementById('priceOrder');
    const productRatingInputs = document.querySelectorAll('input[name="minProductRating"]');
    const sellerRatingInputs = document.querySelectorAll('input[name="minSellerRating"]');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const nearMeCheckbox = document.getElementById('filterNearMe');
    const discountOnlyCheckbox = document.getElementById('filterDiscountOnly'); 

    const slider = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    const fillColour = '#2eb535'; 
    const trackColour = '#ddd';



    // Initialize slider & max price box using the correctly retrieved max price
    priceRange.max = initialMaxPrice;
    priceRange.value = initialMaxPrice;
    priceValueDisplay.textContent = `₹${initialMaxPrice}`;
    if (maxPriceInput) maxPriceInput.value = initialMaxPrice;

    // Sync slider & number input (no change)
    priceRange.addEventListener('input', () => {
        if (maxPriceInput) maxPriceInput.value = priceRange.value;
        priceValueDisplay.textContent = `₹${priceRange.value}`;
        applyFilters();
    });
    
    if (maxPriceInput) {
        maxPriceInput.addEventListener('input', () => {
            priceRange.value = maxPriceInput.value;
            priceValueDisplay.textContent = `₹${maxPriceInput.value}`;
            applyFilters();
        });
    }

    function applyFilters() {
        const searchText = searchInput.value.toLowerCase();
        const maxPrice = parseFloat(priceRange.value);

        const minProductRating = parseFloat(document.querySelector('input[name="minProductRating"]:checked').value) || 0;
        const minSellerRating = parseFloat(document.querySelector('input[name="minSellerRating"]:checked').value) || 0;
        const orderBy = priceOrder.value;

        const filtered = productCardsOriginal.filter(card => {
            const name = card.dataset.name.toLowerCase();
            const price = parseFloat(card.dataset.currentPrice) || parseFloat(card.dataset.price) || 0;
            const productRating = parseFloat(card.dataset.productRating) || 0;
            const sellerRating = parseFloat(card.dataset.sellerRating) || 0;
            const city = card.dataset.city || '';
            const hasDiscount = card.dataset.hasDiscount === "1";

            if (searchText && !name.includes(searchText)) return false;
            
            // Retaining the floating point fix:
            // Ensures products priced exactly at the max value (like 210) are included.
            if (price > maxPrice + 0.01) return false; 
            
            if (productRating < minProductRating) return false;
            if (sellerRating < minSellerRating) return false;
            if (nearMeCheckbox.checked && USER_CITY && city.toLowerCase() !== USER_CITY.toLowerCase()) return false;
            if (discountOnlyCheckbox && discountOnlyCheckbox.checked && !hasDiscount) return false;

            return true;
        });

        if (orderBy === 'desc') filtered.sort((a,b)=>parseFloat(b.dataset.currentPrice||b.dataset.price)-parseFloat(a.dataset.currentPrice||a.dataset.price));
        if (orderBy === 'asc') filtered.sort((a,b)=>parseFloat(a.dataset.currentPrice||a.dataset.price)-parseFloat(b.dataset.currentPrice||b.dataset.price));

        productList.innerHTML = '';
        if (filtered.length > 0) {
            filtered.forEach(card => productList.appendChild(card));
        } else {
            const p = document.createElement('p');
            p.className = 'no-results';
            p.style.cssText = 'text-align:center;padding:50px;width:100%;';
            p.textContent = `No products match your current filters.`;
            productList.appendChild(p);
        }
    }

    const updateSliderFill = () => {
                const min = parseInt(slider.min, 10);
                const max = parseInt(slider.max, 10);
                const value = parseInt(slider.value, 10);

                // 1. Calculate the percentage fill
                const percent = ((value - min) / (max - min)) * 100;

                // 2. Update the track background style using linear-gradient
                // The gradient transitions from the fill colour to the track colour at the calculated percentage point.
                slider.style.background = `linear-gradient(to right, ${fillColour} 0%, ${fillColour} ${percent}%, ${trackColour} ${percent}%, ${trackColour} 100%)`;

                // 3. Update the displayed price text
                priceValue.textContent = `₹${value}`;
            };
            updateSliderFill();
            slider.addEventListener('input', updateSliderFill);

    // Event listeners
    searchInput.addEventListener('input', applyFilters);
    priceOrder.addEventListener('change', applyFilters);
    nearMeCheckbox.addEventListener('change', applyFilters);
    if (discountOnlyCheckbox) discountOnlyCheckbox.addEventListener('change', applyFilters);

    productRatingInputs.forEach(r => r.addEventListener('change', applyFilters));
    sellerRatingInputs.forEach(r => r.addEventListener('change', applyFilters));

    clearFiltersBtn.addEventListener('click', e => {
        e.preventDefault();
        searchInput.value = '';
        // Use initialMaxPrice for reset
        priceRange.value = initialMaxPrice; 
        if (maxPriceInput) maxPriceInput.value = initialMaxPrice; 
        priceValueDisplay.textContent = `₹${initialMaxPrice}`; 
        priceOrder.value = '';
        document.querySelector('input[name="minProductRating"][value="0"]').checked = true;
        document.querySelector('input[name="minSellerRating"][value="0"]').checked = true;
        nearMeCheckbox.checked = false;
        if (discountOnlyCheckbox) discountOnlyCheckbox.checked = false;
        applyFilters();
    });

    // Initial filtering
    applyFilters();
});