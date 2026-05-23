// script/payment_cart.js
document.addEventListener('DOMContentLoaded', () => {
  // Elements
  const methodRadios = document.querySelectorAll("input[name='paymentMethod']");
  const cardDetails = document.getElementById('cardDetails');
  const upiDetails = document.getElementById('upiDetails');
  const codNote = document.getElementById('codNote');
  const payBtn = document.getElementById('payNowBtn');
  const messageBox = document.getElementById('messageBox');
  const successModal = document.getElementById('successModal');
  const timerEl = document.getElementById('timer');
  const baseTotal = parseFloat(document.body.dataset.baseTotal || '0');
  const totalQty = parseFloat(document.body.dataset.totalQty || '0');

  // helpers
  function showMessage(msg, type = 'error') {
    messageBox.textContent = msg;
    messageBox.className = 'message-box ' + (type === 'success' ? 'success' : 'error');
    messageBox.classList.remove('hidden');
  }
  function hideMessage() {
    messageBox.classList.add('hidden');
  }
  function formatRupee(n) {
    return '₹' + Number(n).toFixed(2);
  }

  // compute payable total based on method (COD adds 10 per unit)
  function computePayable(method) {
    let extra = 0;
    if (method === 'cod') {
      // ₹10 per unit (quantity may be decimal)
      extra = 10 * totalQty;
    }
    return baseTotal + extra;
  }

  // init display
  document.getElementById('payableNow').textContent = formatRupee(baseTotal);

  // toggle methods
  methodRadios.forEach(r => {
    r.addEventListener('change', (e) => {
      const val = e.target.value;
      cardDetails.classList.add('hidden');
      upiDetails.classList.add('hidden');
      codNote.classList.add('hidden');

      if (val === 'card') cardDetails.classList.remove('hidden');
      if (val === 'upi') upiDetails.classList.remove('hidden');
      if (val === 'cod') codNote.classList.remove('hidden');

      // update payable display
      document.getElementById('payableNow').textContent = formatRupee(computePayable(val));
      hideMessage();
    });
  });

  // format card number input spacing
  const cardNumberInput = document.getElementById('cardNumber');
  if (cardNumberInput) {
    cardNumberInput.addEventListener('input', (e) => {
      const v = e.target.value.replace(/\D/g, '').slice(0,16);
      e.target.value = v.replace(/(.{4})/g, '$1 ').trim();
    });
  }

  // validate functions
  function validateCard() {
    const num = (document.getElementById('cardNumber').value || '').replace(/\s+/g, '');
    const expiry = (document.getElementById('expiry').value || '').trim();
    const cvv = (document.getElementById('cvv').value || '').trim();
    const name = (document.getElementById('cardName').value || '').trim();

    if (!/^\d{16}$/.test(num)) return 'Invalid card number (expect 16 digits).';
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry)) return 'Invalid expiry date (MM/YY).';
    if (!/^\d{3}$/.test(cvv)) return 'Invalid CVV (3 digits).';
    if (name.length < 2) return 'Enter cardholder name.';
    return null;
  }
  function validateUPI() {
    const upi = (document.getElementById('upiId').value || '').trim();
    if (!/^[\w.\-]{2,}@[\w.\-]{2,}$/.test(upi)) return 'Invalid UPI ID.';
    return null;
  }

  // Payment submit
  payBtn.addEventListener('click', (ev) => {
    ev.preventDefault();
    hideMessage();

    const selected = document.querySelector("input[name='paymentMethod']:checked");
    if (!selected) return showMessage('Select a payment method.');

    const method = selected.value;

    // Validate
    let err = null;
    if (method === 'card') err = validateCard();
    else if (method === 'upi') err = validateUPI();
    // COD has no input validation beyond selection

    if (err) {
      showMessage(err, 'error');
      return;
    }

    // Disable UI
    payBtn.disabled = true;
    payBtn.textContent = 'Processing...';

    // Send request to server action
    fetch('../shop/payment_cart_action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ method: method })
    })
    .then(r => r.json())
    .then(data => {
      if (!data) throw new Error('No response from server.');
      if (data.success) {
        // Success flow: show modal, clear checkout-session, redirect after countdown
        // Make sure server already cleared cart and updated profiles.
        fetch('../session/clear_checkout.php').catch(()=>{ /* ignore network errors */ });

        // Show modal
        const modal = successModal;
        modal.classList.remove('hidden');
        modal.classList.add('visible');
        modal.setAttribute('aria-hidden', 'false');

        // Update modal text if server returned message
        const titleEl = document.getElementById('modalTitle');
        const msgEl = document.getElementById('modalMessage');
        titleEl.textContent = 'Success';
        msgEl.textContent = data.message || 'Order placed successfully.';

        // Countdown then redirect (only on success)
        let sec = 5;
        timerEl.textContent = sec;
        const id = setInterval(() => {
          sec -= 1;
          timerEl.textContent = sec;
          if (sec <= 0) {
            clearInterval(id);
            // redirect to orders page from server response or fallback
            window.location.href = data.redirect || '../shop/orders.php';
          }
        }, 1000);
      } else {
        // failure: show message and re-enable button
        showMessage(data.message || 'Payment failed. Try again.', 'error');
        payBtn.disabled = false;
        payBtn.textContent = 'Pay Now';
      }
    })
    .catch(err => {
      showMessage('Server error: ' + (err.message || 'unknown'), 'error');
      payBtn.disabled = false;
      payBtn.textContent = 'Pay Now';
    });
  });

  // Auto clear checkout session after 3 minutes (frontend trigger only)
  setTimeout(() => {
    fetch('../session/clear_checkout.php').catch(()=>{});
  }, 180000);
});
