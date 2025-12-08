// Import a helper function to get elements by ID (assumed from your shared utils)
import { showEmojiPicker } from '../emojiPicker.js';
import { imagePreview } from '../fileUploadPreview';

// Get references to DOM elements used in the emoji picker and image preview - SHOW EMOJIs FOR POST

const emojiToggle = document.getElementById('emojiPost'); // Button to show/hide emoji picker
const emojiContainer = document.getElementById('emojiPickerContainer'); // Container for emoji picker
const closeEmojiBtn = document.getElementById('closeEmojiPicker'); // Close button

showEmojiPicker('emojiListPost', 'data-emoji-target');

// 🟡 Toggle emoji picker visibility when the toggle button is clicked
emojiToggle.addEventListener('click', () => {
    emojiContainer.classList.toggle('d-none'); // Show/hide the emoji container
    emojiToggle.setAttribute('aria-expanded', emojiContainer.classList.contains('d-none') ? 'false' : 'true');
});

// Close button handler
if (closeEmojiBtn) {
    closeEmojiBtn.addEventListener('click', () => {
        emojiContainer.classList.add('d-none');
        emojiToggle.setAttribute('aria-expanded', 'false');
    });
}


imagePreview('imageUpload', 'imagePreviewList', 'postModalImgFileNames', 'imagePreviewContainer', 'closeImagePreview');




