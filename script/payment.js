document.addEventListener('DOMContentLoaded', () => {
  const body = document.body;
  const paymentForm = document.getElementById('paymentForm');
  const payButton = document.getElementById('payButton');
  const messageBox = document.getElementById('messageBox');
  const finalTotalAmountSpan = document.getElementById('finalTotalAmount');
  const codFeeInfo = document.getElementById('codFeeInfo');
  const successModal = document.getElementById('successModal');
  const modalMessage = document.getElementById('modalMessage');
  const timerSpan = document.getElementById('timer');

  const methodRadios = document.querySelectorAll('input[name="payment_method"]');
  const cardDetailsDiv = document.getElementById('cardDetails');
  const upiDetailsDiv = document.getElementById('upiDetails');
  const cashDetailsDiv = document.getElementById('cashDetails');

  const cardNumberInput = document.getElementById('cardNumber');
  const cardExpiryInput = document.getElementById('cardExpiry');
  const cardCVVInput = document.getElementById('cardCVV');
  const cardNameInput = document.getElementById('cardName');
  const upiIdInput = document.getElementById('upiId');

  const cardNumberError = document.getElementById('cardNumberError');
  const cardExpiryError = document.getElementById('cardExpiryError');
  const cardCVVError = document.getElementById('cardCVVError');
  const upiIdError = document.getElementById('upiIdError');

  const initialTotalAmount = parseFloat(body.dataset.initialTotal || 0);
  const productId = body.dataset.productId;
  const quantity = body.dataset.quantity;
  const pricePerUnit = body.dataset.pricePerUnit;

  let currentMethod = 'card';
  const COD_FEE = 10.00;
  const delay = ms => new Promise(resolve => setTimeout(resolve, ms));

  function clearAllErrors() {
    cardNumberError.textContent = '';
    cardExpiryError.textContent = '';
    cardCVVError.textContent = '';
    upiIdError.textContent = '';
  }

  function displayMessage(msg, type) {
    messageBox.textContent = msg;
    messageBox.className = `message-box ${type}`;
    messageBox.classList.remove('hidden');
    setTimeout(() => messageBox.classList.add('hidden'), 5000);
  }

  function showSuccessAndRedirect(message) {
    modalMessage.innerHTML = `<i class="fas fa-check-circle success-icon"></i> ${message}`;
    successModal.classList.add('visible');
    successModal.classList.remove('hidden');
    let timer = 5;
    timerSpan.textContent = timer;
    const countdown = setInterval(() => {
      timer--;
      timerSpan.textContent = timer;
      if (timer <= 0) {
        clearInterval(countdown);
        window.location.href = 'orders.php';
      }
    }, 1000);
  }

  function showFailurePopup(message) {
    const modal = document.getElementById('successModal');
    const modalMsg = document.getElementById('modalMessage');
    const modalTitle = modal.querySelector('h2');

    modalTitle.textContent = "Payment Failed!";
    modalTitle.classList.add("error-title");
    modalMsg.innerHTML = `<i class="fas fa-times-circle error-icon"></i> <span style="display:inline-block;vertical-align:middle;margin-left:8px;">${message}</span>`;
    modal.querySelector('.success-icon-container').innerHTML =
      `<i class="fas fa-times-circle error-icon"></i>`;
    const rt = modal.querySelector('.redirect-timer');
    rt.innerHTML = `<button onclick="window.location.reload()" class="retry-btn">Retry Payment</button>`;

    modal.classList.remove('hidden');
    modal.classList.add('visible');
  }

  function updateFinalTotal() {
    let finalAmount = initialTotalAmount;
    if (currentMethod === 'cash') {
      finalAmount += COD_FEE;
      codFeeInfo.style.display = 'block';
      payButton.innerHTML = `<i class="fas fa-lock"></i> Place Order (Cash)`;
    } else {
      codFeeInfo.style.display = 'none';
      payButton.innerHTML = `<i class="fas fa-lock"></i> Process Payment`;
    }
    finalTotalAmountSpan.textContent = `₹${finalAmount.toFixed(2)}`;
    finalTotalAmountSpan.dataset.finalAmount = finalAmount.toFixed(2);
  }

  function switchPaymentMethod(method) {
    currentMethod = method;
    [cardDetailsDiv, upiDetailsDiv, cashDetailsDiv].forEach(d => d.classList.add('hidden'));
    [cardNumberInput, cardExpiryInput, cardCVVInput, cardNameInput, upiIdInput].forEach(i => i.required = false);

    if (method === 'card') {
      cardDetailsDiv.classList.remove('hidden');
      [cardNumberInput, cardExpiryInput, cardCVVInput, cardNameInput].forEach(i => i.required = true);
    } else if (method === 'upi') {
      upiDetailsDiv.classList.remove('hidden');
      upiIdInput.required = true;
    } else if (method === 'cash') {
      cashDetailsDiv.classList.remove('hidden');
      cardNumberInput.value = ''; cardExpiryInput.value = ''; cardCVVInput.value = ''; cardNameInput.value = ''; upiIdInput.value = '';
    }

    clearAllErrors();
    updateFinalTotal();
  }

  methodRadios.forEach(r => r.addEventListener('change', e => switchPaymentMethod(e.target.value)));
  const checkedRadio = document.querySelector('input[name="payment_method"]:checked');
  if (checkedRadio) switchPaymentMethod(checkedRadio.value);

  // Formatting & validators (unchanged)
  cardNumberInput.addEventListener('input', e => {
    let v = e.target.value.replace(/\s/g, '').replace(/\D/g, '').substring(0, 16);
    e.target.value = v.replace(/(\d{4})/g, '$1 ').trim();
  });
  cardExpiryInput.addEventListener('input', e => {
    let v = e.target.value.replace(/\D/g, '').substring(0, 4);
    if (v.length > 2) v = v.substring(0, 2) + '/' + v.substring(2);
    e.target.value = v;
  });
  cardCVVInput.addEventListener('input', e => e.target.value = e.target.value.replace(/\D/g, '').substring(0, 3));
  cardNameInput.addEventListener('input', e => e.target.value = e.target.value.replace(/[^a-zA-Z\s'-]/g, ''));

  function validateCardDetails() {
    let valid = true;
    const cardNum = cardNumberInput.value.replace(/\s/g, '');
    if (!/^\d{16}$/.test(cardNum)) { cardNumberError.textContent = 'Card number must be 16 digits.'; valid = false; }
    if (!/^\d{3}$/.test(cardCVVInput.value)) { cardCVVError.textContent = 'CVV must be 3 digits.'; valid = false; }
    const expiry = cardExpiryInput.value;
    if (!/^\d{2}\/\d{2}$/.test(expiry)) { cardExpiryError.textContent = 'Format must be MM/YY'; valid = false; }
    else {
      const [mm, yy] = expiry.split('/').map(n => parseInt(n, 10));
      const now = new Date();
      const currentYear = now.getFullYear() % 100;
      const currentMonth = now.getMonth() + 1;
      if (mm < 1 || mm > 12) { cardExpiryError.textContent = 'Invalid month'; valid = false; }
      else if (yy < currentYear || (yy === currentYear && mm < currentMonth)) { cardExpiryError.textContent = 'Card expired'; valid = false; }
    }
    return valid;
  }

  function validateUpiDetails() {
    let valid = true;
    if (!/^[a-zA-Z0-9.\-]+@[a-zA-Z0-9\-\.]+$/.test(upiIdInput.value.trim())) {
      upiIdError.textContent = 'Invalid UPI ID'; valid = false;
    }
    return valid;
  }

  function validateCashDetails() { return true; }

  // Submit handler
  paymentForm.addEventListener('submit', async e => {
    e.preventDefault();
    clearAllErrors();

    let formValid = false, paymentDetail = '';

    if (currentMethod === 'card') { formValid = validateCardDetails(); paymentDetail = cardNumberInput.value.replace(/\s/g, ''); }
    else if (currentMethod === 'upi') { formValid = validateUpiDetails(); paymentDetail = upiIdInput.value.trim(); }
    else if (currentMethod === 'cash') { formValid = validateCashDetails(); paymentDetail = 'COD'; }

    if (!formValid) { displayMessage('Please correct the errors.', 'error'); return; }

    payButton.disabled = true;
    payButton.textContent = 'Processing...';
    await delay(4000);

    const finalAmount = parseFloat(finalTotalAmountSpan.dataset.finalAmount);
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('price_per_unit', pricePerUnit);
    formData.append('payment_method', currentMethod);
    formData.append('final_amount', finalAmount.toFixed(2));
    formData.append('payment_detail', paymentDetail);

    try {
      const response = await fetch('payment_action.php', { method: 'POST', body: formData });
      const text = await response.text();
      console.log("Server raw response:", text);

      let data;
      try { data = JSON.parse(text); } catch (err) {
        console.error("Invalid JSON from server:", text);
        payButton.textContent = "Process Failed";
        displayMessage('Server sent invalid response. Check console.', 'error');
        payButton.disabled = false;
        return;
      }

      if (data.success) {
        setTimeout(() => {
          const modalText = currentMethod === 'cash'
            ? 'Your Cash on Delivery order has been placed successfully.'
            : data.message;
          showSuccessAndRedirect(modalText);
        }, 2000);
      } else {
        console.error("Server Error:", data.message);
        payButton.textContent = "Process Failed";
        displayMessage(data.message || 'Payment failed.', 'error');
        setTimeout(() => showFailurePopup(data.message || "Payment failed due to server error."), 2000);
        payButton.disabled = false;
        updateFinalTotal();
      }
    } catch (err) {
      console.error('Network error:', err);
      payButton.textContent = "Process Failed";
      displayMessage('Network error. Please try again.', 'error');
      setTimeout(() => showFailurePopup("Network connection error. Please try again."), 2000);
      payButton.disabled = false;
      updateFinalTotal();
    }
  });
});
