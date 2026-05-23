document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.getElementById('passwordForm');
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    
    // Function to handle error display (console log for simplicity)
    const displayError = (message) => {
        // In a production app, this would update a dedicated error div in the HTML
        console.error("Password Validation Error: " + message);
        // Using alert only for immediate feedback in this context, use a custom modal in production!
        // alert("Error: " + message); 
    };

    if (passwordForm) {
        passwordForm.addEventListener('submit', function(event) {
            
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // 1. Check if New Password and Confirm Password match
            if (newPassword !== confirmPassword) {
                event.preventDefault();
                displayError("New password and confirmation password do not match.");
                confirmPasswordInput.focus();
                return false;
            }

            // 2. Check password strength (minimum length 8)
            if (newPassword.length < 8) {
                event.preventDefault();
                displayError("New password must be at least 8 characters long.");
                newPasswordInput.focus();
                return false;
            }
            
            // 3. Prevent submission if Current Password is empty
            const currentPassword = document.getElementById('current_password').value;
            if (currentPassword.length === 0) {
                event.preventDefault();
                displayError("Please enter your current password.");
                document.getElementById('current_password').focus();
                return false;
            }

            console.log("Client-side validation passed. Submitting password change request...");
            // Allow form submission to proceed to PHP logic
            return true;
        });
    }
});
