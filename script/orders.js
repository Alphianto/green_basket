// ✅ Wait for DOM to load
document.addEventListener('DOMContentLoaded', () => {

    // --- LOCAL ORDERS SEARCH (Product Name Only) ---
    const input = document.getElementById('ordersSearch');
    if (input) {
        const cards = Array.from(document.querySelectorAll('.order-card'));
        const ordersList = document.getElementById('ordersList');
        // Setup empty search message
        const emptyMessage = ordersList.querySelector('.empty');
        const originalEmptyHTML = '<div class="empty-search" style="text-align:center;padding:30px;color:#666;background:#fff;border-radius:10px;display:none;">No orders found matching your search.</div>';
        
        // Logic to conditionally insert the search empty message
        if (cards.length > 0 && !ordersList.querySelector('.empty-search')) {
            ordersList.insertAdjacentHTML('beforeend', originalEmptyHTML);
        }

        const searchEmptyMessage = ordersList.querySelector('.empty-search');
        if (searchEmptyMessage) searchEmptyMessage.style.display = 'none'; 

        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            let resultsFound = false;

            cards.forEach(card => {
                // Fetch product data attribute
                const product = (card.dataset.product || '').toLowerCase();
                
                // Check ONLY product name as requested
                const show = !q || product.includes(q); 
                
                card.style.display = show ? '' : 'none';
                if (show) {
                    resultsFound = true;
                }
            });

            // Toggle empty message based on search results
            if (q !== '') {
                if (emptyMessage) emptyMessage.style.display = 'none';
                if (searchEmptyMessage) searchEmptyMessage.style.display = resultsFound ? 'none' : 'block';
            } else {
                // When search is cleared
                if (emptyMessage) emptyMessage.style.display = cards.length === 0 ? 'block' : 'none';
                if (searchEmptyMessage) searchEmptyMessage.style.display = 'none';
            }
        });
    }

    // --- ORDER CANCELLATION LOGIC (Moved to end of DOMContentLoaded) ---
    const handleOrderCancellation = (e) => {
        // Check if the clicked element is the cancel link
        if (!e.target.classList.contains('cancel-order-link')) {
            return;
        }
        
        // 1. Stop the link from navigating to the PHP file (Fixes bad UI/index)
        e.preventDefault(); 

        const link = e.target;
        const orderId = link.dataset.orderId;

        // 2. Pop-up message: "confirm cancellation"
        const isConfirmed = confirm('Confirm cancellation? Are you sure you want to cancel this order?');
        
        if (isConfirmed) {
            // Disable the link during processing
            link.textContent = 'Processing...';
            link.style.pointerEvents = 'none';

            // 3. Send AJAX POST request to the server (Fixes "Invalid request")
            fetch('cancel_order_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order_id=${encodeURIComponent(orderId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 4. Update the UI: status to 'Cancelled' and remove the link
                    const statusActionRow = link.closest('.status-action-row');
                    const card = link.closest('.order-card');
                    
                    // Change status text and class
                    const statusDiv = statusActionRow.querySelector('.status');
                    if (statusDiv) {
                        statusDiv.textContent = 'Cancelled';
                        statusDiv.classList.remove('status-pending');
                        statusDiv.classList.add('status-cancelled');
                    }
                    
                    // Remove the cancel link
                    link.remove();
                    
                    // Disable the Rate & Review link on the right side
                    const rateLink = card?.querySelector('.order-right .rate-link');
                    if (rateLink) {
                        rateLink.classList.remove('not-reviewed');
                        rateLink.classList.add('disabled');
                        rateLink.textContent = 'Rate & Review'; 
                        rateLink.removeAttribute('href');
                    }
                } else {
                    alert('Cancellation failed: ' + (data.message || 'Server error.'));
                    // Re-enable the link if cancellation fails
                    link.textContent = 'Cancel Order';
                    link.style.pointerEvents = 'auto';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during cancellation. Please try again.');
                link.textContent = 'Cancel Order';
                link.style.pointerEvents = 'auto';
            });
        }
    };

    // 5. Attach the listener correctly at the end
    // Use the document as a fallback if 'ordersList' is not found immediately
    document.getElementById('ordersList')?.addEventListener('click', handleOrderCancellation);
    // document.addEventListener('click', handleOrderCancellation); // Uncomment this line if the above one doesn't work consistently.
});