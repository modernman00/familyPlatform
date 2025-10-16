// Import a helper function to get elements by ID (assumed from your shared utils)
import { showEmojiPicker } from '../emojiPicker.js';
import { imagePreview } from '../fileUploadPreview';

// Get references to DOM elements used in the emoji picker and image preview

const emojiToggle = document.getElementById('emojiToggle'); // Button to show/hide emoji picker
const emojiList = document.getElementById('emojiPickerList'); // Container for emoji buttons


showEmojiPicker('emojiPickerList', 'data-emoji-target');

// 🟡 Toggle emoji picker visibility when the toggle button is clicked
emojiToggle.addEventListener('click', () => {
    emojiList.classList.toggle('d-none'); // Show/hide the emoji list
    emojiToggle.setAttribute('aria-expanded', emojiList.classList.contains('d-none') ? 'false' : 'true');
});


imagePreview('imageUpload', 'imagePreviewList', 'postModalImgFileNames', 'imagePreviewContainer', 'closeImagePreview');




