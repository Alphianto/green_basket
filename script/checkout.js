document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const productId = parseInt(body.dataset.productId);
    const userId = parseInt(body.dataset.userId);
    const sellerId = parseInt(body.dataset.sellerId); // ✅ from PHP
    const currentPrice = parseFloat(body.dataset.currentPrice);
    const expiryDate = new Date(body.dataset.expiry);

    const quantityInput = document.getElementById('quantityInput');
    const totalPriceValueSpan = document.getElementById('totalPriceValue');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const buyNowBtn = document.getElementById('buyNowBtn');
    const messageBox = document.getElementById('messageBox');
    const expiryDateSpan = document.getElementById('expiryDate');

    // --- Helper for messages ---
    function displayMessage(msg, type = 'info') {
        messageBox.textContent = msg;
        messageBox.className = 'message-box ' + type;
        messageBox.style.display = 'block';
        setTimeout(() => messageBox.style.display = 'none', 3000);
    }

    // --- Quantity and Price Logic ---
    function updateTotalPrice() {
        let quantity = parseFloat(quantityInput.value);
        const maxQuantity = parseFloat(quantityInput.getAttribute('max'));

        if (isNaN(quantity) || quantity < 0.1) quantity = 0.1;
        if (quantity > maxQuantity) quantity = maxQuantity;

        quantityInput.value = quantity.toFixed(1);
        const totalPrice = quantity * currentPrice;
        totalPriceValueSpan.textContent = totalPrice.toFixed(2);
    }

    quantityInput.addEventListener('input', updateTotalPrice);
    updateTotalPrice();

    // --- Highlight Expiry if near ---
    const now = new Date();
    const diffHours = (expiryDate - now) / (1000 * 60 * 60);
    if (diffHours <= 24) expiryDateSpan.style.color = 'red';

    // --- Prevent Seller from Buying or Adding ---
    const isOwnProduct = (userId === sellerId);

    if (isOwnProduct) {
        // Visually disable both buttons
        addToCartBtn.style.opacity = '0.6';
        buyNowBtn.style.opacity = '0.6';
        addToCartBtn.style.cursor = 'not-allowed';
        buyNowBtn.style.cursor = 'not-allowed';

        // Add event listener to show message and block actions
        addToCartBtn.addEventListener('click', (e) => {
            e.preventDefault();
            displayMessage("🚫 It's your own product — you can't add it to cart.", 'error');
        });

        buyNowBtn.addEventListener('click', (e) => {
            e.preventDefault();
            displayMessage("🚫 It's your own product — you can't buy it.", 'error');
        });

        return; // Stop rest of logic for sellers
    }

    // --- Normal Buyer Logic Below ---

    addToCartBtn.addEventListener('click', async () => {
        const quantity = parseFloat(quantityInput.value);
        try {
            const res = await fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId, quantity })
            });
            const data = await res.json();
            displayMessage(data.message, data.success ? 'success' : 'error');
        } catch (err) {
            console.error(err);
            displayMessage('Network error', 'error');
        }
    });

    buyNowBtn.addEventListener('click', () => {
        const quantity = parseFloat(quantityInput.value);
        const totalPrice = parseFloat(totalPriceValueSpan.textContent);
        window.location.href = `payment.php?product_id=${productId}&quantity=${quantity}&price=${totalPrice.toFixed(2)}`;
    });
});
