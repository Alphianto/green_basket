/**
 * edit_profile.js
 * Handles client-side logic for profile editing, including avatar and field validation.
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // --- AVATAR UPLOAD ELEMENTS ---
    const avatarUploadInput = document.getElementById('avatarUpload');
    const uploadAvatarBtn = document.getElementById('uploadAvatarBtn');
    const profileAvatar = document.getElementById('profileAvatar');
    const editAvatarLabel = document.querySelector('.edit-avatar-btn');

    // --- FORM VALIDATION ELEMENTS ---
    const profileForm = document.getElementById('profileForm');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const pincodeInput = document.getElementById('pincode');
    
    // Elements to show validation status
    // We need to create these status elements dynamically or ensure they exist in HTML
    function createStatusElement(input) {
        let status = document.getElementById(input.id + 'Status');
        if (!status) {
            status = document.createElement('div');
            status.id = input.id + 'Status';
            status.style.cssText = 'font-size: 0.9em; margin-top: 5px; min-height: 20px;';
            input.parentNode.appendChild(status);
        }
        return status;
    }

    // Custom message box function (replaces alerts and console errors for user-facing feedback)
    function showMessageBox(message, type = 'info') {
        let msgElement = document.getElementById('messageBox');
        if (!msgElement) {
            msgElement = document.createElement('div');
            msgElement.id = 'messageBox';
            // Basic styling for visibility
            msgElement.style.cssText = `
                position: fixed; top: 20px; right: 20px; padding: 15px 25px; 
                border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
                z-index: 1000; font-weight: 600; transition: opacity 0.5s, transform 0.5s;
                opacity: 0; transform: translateY(-20px); max-width: 90%;
            `;
            document.body.appendChild(msgElement);
        }

        msgElement.innerHTML = message;
        
        // Dynamic background/color based on type
        if (type === 'success') {
            msgElement.style.backgroundColor = '#d1e7dd';
            msgElement.style.color = '#0f5132';
            msgElement.style.border = '1px solid #badbcc';
        } else if (type === 'error') {
            msgElement.style.backgroundColor = '#f8d7da'; 
            msgElement.style.color = '#842029'; 
            msgElement.style.border = '1px solid #f5c2c7';
        } else { // info
            msgElement.style.backgroundColor = '#f8f9fa';
            msgElement.style.color = '#212529';
            msgElement.style.border = '1px solid #ced4da';
        }

        // Show and hide
        msgElement.style.opacity = '1';
        msgElement.style.transform = 'translateY(0)';
        
        setTimeout(() => {
            msgElement.style.opacity = '0';
            msgElement.style.transform = 'translateY(-20px)';
        }, 4000);
    }

    // Helper to display validation status below input field
    function setValidationStatus(input, isValid, message) {
        const statusElement = createStatusElement(input);
        
        if (isValid) {
            statusElement.innerHTML = `<span style="color: green;"><i class="fas fa-check-circle"></i> ${message}</span>`;
            input.style.borderColor = 'green';
        } else {
            statusElement.innerHTML = `<span style="color: red;"><i class="fas fa-exclamation-circle"></i> ${message}</span>`;
            input.style.borderColor = 'red';
        }
        if (!message) {
             statusElement.innerHTML = '';
             input.style.borderColor = ''; // Reset if message is empty
        }
    }
    
    // --- FIELD VALIDATION FUNCTIONS ---

    // Email validation
    function validateEmail(e) {
        const value = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (value === '') {
            setValidationStatus(emailInput, false, 'Email is required.');
            return false;
        } else if (!emailRegex.test(value)) {
            setValidationStatus(emailInput, false, 'Please enter a valid email format (e.g., user@domain.com).');
            return false;
        }
        setValidationStatus(emailInput, true, 'Valid Email.');
        return true;
    }

    // Phone validation (Max/Min 10 digits, cannot start with 0)
    function validatePhone() {
        // Allow user to type spaces/dashes, but validate only digits
        const value = phoneInput.value.replace(/\D/g, ''); 
        if (value.length === 0) {
             setValidationStatus(phoneInput, true, ''); // Optional field, pass if empty
             return true; 
        } else if (value.length !== 10) {
            setValidationStatus(phoneInput, false, 'Phone must be exactly 10 digits.');
            return false;
        } else if (value.startsWith('0')) {
            setValidationStatus(phoneInput, false, 'Phone cannot start with zero.');
            return false;
        }
        setValidationStatus(phoneInput, true, 'Valid Phone Number.');
        return true;
    }

    // Pincode validation (6 digits, must start with 6 for Kerala)
    function validatePincode() {
        const value = pincodeInput.value.trim();
        const pincodeRegex = /^6\d{5}$/; // Starts with 6, followed by 5 digits
        if (value.length === 0) {
            setValidationStatus(pincodeInput, true, ''); // Optional field, pass if empty
            return true;
        } else if (!pincodeRegex.test(value)) {
            setValidationStatus(pincodeInput, false, 'Pincode must be 6 digits and start with 6 (Kerala standard).');
            return false;
        }
        setValidationStatus(pincodeInput, true, 'Valid Pincode.');
        return true;
    }

    // Username Uniqueness Validation (AJAX)
    let usernameTimeout;
    function validateUsername() {
        clearTimeout(usernameTimeout);

        const value = usernameInput.value.trim();
        const statusElement = createStatusElement(usernameInput);

        if (value.length === 0) {
            setValidationStatus(usernameInput, false, 'Username is required.');
            return;
        }
        
        // Show loading status while waiting for AJAX
        statusElement.innerHTML = '<span style="color: #007bff;"><i class="fas fa-spinner fa-spin"></i> Checking availability...</span>';
        usernameInput.style.borderColor = '#007bff';

        // Wait 500ms before sending request to avoid spamming the server
        usernameTimeout = setTimeout(() => {
            const formData = new FormData();
            formData.append('username', value);

            fetch('../api/check_username.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.unique) {
                    setValidationStatus(usernameInput, true, data.message);
                } else {
                    setValidationStatus(usernameInput, false, data.message);
                }
            })
            .catch(error => {
                console.error('Username check failed:', error);
                setValidationStatus(usernameInput, false, 'An error occurred while checking username.');
            });
        }, 500);
    }
    
    // --- EVENT LISTENERS FOR LIVE VALIDATION ---
    if (usernameInput) {
        usernameInput.addEventListener('input', validateUsername);
    }
    if (emailInput) {
        emailInput.addEventListener('input', validateEmail);
    }
    if (phoneInput) {
        phoneInput.addEventListener('input', validatePhone);
    }
    if (pincodeInput) {
        pincodeInput.addEventListener('input', validatePincode);
    }


    // --- FORM SUBMISSION CHECK (Final Client-side check before PHP) ---
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            // Re-run all validation checks just before submission
            let isFormValid = true;

            // Note: Username check is async, relying on the 'input' event to set status/border
            // For simplicity, we assume the user waited for the last AJAX call to complete,
            // but for a strict check, you'd track the last AJAX result.
            
            // For required fields (Email, Username) and others, run sync checks:
            if (!validateEmail()) isFormValid = false;
            if (!validatePhone()) isFormValid = false;
            if (!validatePincode()) isFormValid = false;
            
            // For username, rely on the border color set by the last validation (red means error)
            // If the border is red (from an error status), we block submission.
            if (usernameInput.style.borderColor === 'red') {
                 isFormValid = false;
            } else if (usernameInput.value.trim().length === 0) {
                // Also check if required username field is just empty
                setValidationStatus(usernameInput, false, 'Username is required.');
                isFormValid = false;
            }

            if (!isFormValid) {
                e.preventDefault(); // Stop form submission
                showMessageBox('Please correct the highlighted errors before saving.', 'error');
            }
        });
    }

    
    // --- AVATAR UPLOAD LOGIC (Existing features) ---
    
    // 1. Link the camera label click to the hidden file input 
    if (editAvatarLabel && avatarUploadInput) {
        editAvatarLabel.addEventListener('click', function(e) {
            e.preventDefault(); 
            avatarUploadInput.click();
        });
    }

    // 2. Handle file selection and display the preview
    if (avatarUploadInput && profileAvatar) {
        avatarUploadInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileAvatar.src = e.target.result;
                    uploadAvatarBtn.disabled = false;
                    console.log("New file selected and previewed. Click 'Upload Avatar' to save it.");
                };
                reader.readAsDataURL(file);
            } else {
                uploadAvatarBtn.disabled = true;
            }
        });
    }

    // 3. Handle the "Upload Avatar" button click and perform AJAX
    if (uploadAvatarBtn && avatarUploadInput) {
        uploadAvatarBtn.addEventListener('click', function() {
            const file = avatarUploadInput.files[0];
            if (!file) {
                showMessageBox('Please select an image file first.', 'error');
                return;
            }

            uploadAvatarBtn.disabled = true;
            uploadAvatarBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            
            const formData = new FormData();
            formData.append('avatar', file);

            fetch('../api/upload_avatar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                return response.json().then(data => {
                    if (!response.ok) {
                        throw new Error(data.message || `HTTP error! status: ${response.status}`);
                    }
                    return data;
                });
            })
            .then(data => {
                if (data.success) {
                    showMessageBox('Avatar uploaded successfully!', 'success');
                    profileAvatar.src = data.new_avatar_url + '?' + new Date().getTime(); 
                } else {
                    showMessageBox(`Upload failed: ${data.message}`, 'error');
                }
            })
            .catch(error => {
                console.error('Upload Error:', error);
                showMessageBox('An unexpected error occurred during upload. Check console.', 'error');
            })
            .finally(() => {
                uploadAvatarBtn.disabled = false;
                uploadAvatarBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Avatar';
                avatarUploadInput.value = '';
            });
        });
    }
});