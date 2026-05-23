document.addEventListener('DOMContentLoaded', function() {
    // Select the camera link (which is now an anchor tag with the class)
    const editAvatarBtn = document.querySelector('.edit-avatar-btn');
    // The hidden file input
    const avatarUploadInput = document.getElementById('avatarUpload');
    // The image element to preview the new avatar
    const profileAvatar = document.getElementById('profileAvatar');

    if (editAvatarBtn && avatarUploadInput && profileAvatar) {
        // We override the default link behavior to trigger the file input IF it's clicked.
        // However, since the user wanted it to be a link to edit_profile.php, we will
        // allow the default link behavior to handle the navigation.
        
        // This is the original logic to allow a click to open the file dialog for a quick change:
        /*
        editAvatarBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent navigating to edit_profile.php for quick upload
            avatarUploadInput.click();
        });
        */

        // 2. Handle the file selection and display the preview
        avatarUploadInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Update the avatar image source for preview
                    profileAvatar.src = e.target.result;
                    // In a real application, you would also upload this file to the server here.
                    console.log("New file selected for profile picture. Ready to upload to server.");
                    // You might want to automatically submit the form or show a 'Save' button here.
                };
                reader.readAsDataURL(file);
            }
        });
    } else {
        console.error("Required elements for avatar functionality not found.");
    }

    // Optional: Add hover effect logic for avatar (already mostly handled in CSS)
    // Removed explicit JS hover for cleaner code, relying solely on CSS.
});
