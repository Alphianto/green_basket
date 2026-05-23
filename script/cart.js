document.addEventListener("DOMContentLoaded", () => {
  const increaseBtns = document.querySelectorAll(".qty-increase");
  const decreaseBtns = document.querySelectorAll(".qty-decrease");
  const qtyInputs = document.querySelectorAll(".qty-input");
  const removeBtns = document.querySelectorAll(".remove-item-btn");

  function updateItemTotal(card) {
    const price = parseFloat(card.dataset.price);
    const qty = parseFloat(card.querySelector(".qty-input").value);
    card.querySelector(".item-total span").textContent = (price * qty).toFixed(2);
    updateCartSummary();
    syncQuantity(card.dataset.productId, qty);
  }

  function updateCartSummary() {
    let subtotal = 0;
    document.querySelectorAll(".item-total span").forEach(el => subtotal += parseFloat(el.textContent));
    const shipping = subtotal >= 100 ? 0 : 5;
    const tax = subtotal * 0.05;
    const total = subtotal + shipping + tax;

    const subtotalEl = document.getElementById("subtotalValue");
    const shippingEl = document.getElementById("shippingValue");
    const taxEl = document.getElementById("taxValue");
    const totalEl = document.getElementById("totalValue");

    if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2);
    if (shippingEl) shippingEl.textContent = shipping.toFixed(2);
    if (taxEl) taxEl.textContent = tax.toFixed(2);
    if (totalEl) totalEl.textContent = total.toFixed(2);
  }

  function syncQuantity(productId, quantity) {
    fetch("../session/update_cart_quantity.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "product_id=" + encodeURIComponent(productId) + "&quantity=" + encodeURIComponent(quantity),
    }).catch(err => {
      console.warn("syncQuantity error:", err);
    });
  }

  increaseBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".cart-item-card");
      const input = card.querySelector(".qty-input");
      let current = parseFloat(input.value);
      const stock = parseFloat(card.dataset.stock);
      // add 0.1 with rounding to 3 decimals
      let next = +(current + 0.1).toFixed(3);
      if(next <= stock) input.value = next.toFixed(3);
      else input.value = stock.toFixed(3);
      updateItemTotal(card);
    });
  });

  decreaseBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".cart-item-card");
      const input = card.querySelector(".qty-input");
      let current = parseFloat(input.value);
      let next = +(current - 0.1).toFixed(3);
      if(next >= 0.1) input.value = next.toFixed(3);
      else input.value = (0.1).toFixed(3);
      updateItemTotal(card);
    });
  });

  qtyInputs.forEach(input => {
    input.addEventListener("input", () => {
      const card = input.closest(".cart-item-card");
      const stock = parseFloat(card.dataset.stock);
      let val = parseFloat(input.value);
      if(isNaN(val) || val < 0.1) val = 0.1;
      if(val > stock) val = stock;
      input.value = val.toFixed(3);
      updateItemTotal(card);
    });
  });

  removeBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".cart-item-card");
      const productId = card.dataset.productId;
      card.remove();
      updateCartSummary();
      fetch("../session/remove_from_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "product_id=" + encodeURIComponent(productId),
      }).catch(err => console.warn("remove error:", err));
    });
  });

  // --- Proceed to Checkout: redirect with product_id, quantity, price (first item) ---
  const checkoutBtns = document.querySelectorAll(".checkout-btn");
  checkoutBtns.forEach(btn => {
    btn.addEventListener("click", (e) => {
      // Prevent default if it's a <button type="submit"> inside a form (there isn't one, but safe)
      e.preventDefault();

      const cartCards = document.querySelectorAll(".cart-item-card");
      if (!cartCards || cartCards.length === 0) {
        alert("Your cart is empty!");
        return;
      }

      // Use the first item (same behavior as you requested)
      const firstItem = cartCards[0];
      const productId = firstItem.dataset.productId;
      const priceRaw = firstItem.dataset.price;
      const qtyInput = firstItem.querySelector(".qty-input");
      const quantity = qtyInput ? parseFloat(qtyInput.value) : 1;

      if (!productId || !priceRaw || isNaN(quantity) || quantity <= 0) {
        alert("Error: Missing or invalid product details. Please refresh and try again.");
        return;
      }

      // Format price to two decimal places
      const price = parseFloat(priceRaw).toFixed(2);

      // Build the target URL — same path as your payment.php
      const query = new URLSearchParams({
        product_id: productId,
        quantity: quantity,
        price: price
      });

      // redirect
      window.location.href = `payment.php?${query.toString()}`;
    });
  });

  // Initial summary calc (after DOM loaded)
  updateCartSummary();
});
