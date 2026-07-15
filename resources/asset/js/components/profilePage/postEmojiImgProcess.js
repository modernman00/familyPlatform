// Import a helper function to get elements by ID (assumed from your shared utils)
import { showEmojiPicker, initEmojiPickerUX } from '../emojiPicker.js';
import { imagePreview } from '../fileUploadPreview';

// Get references to DOM elements used in the emoji picker and image preview - SHOW EMOJIs FOR POST

const emojiToggle = document.getElementById('emojiPost'); // Button to show/hide emoji picker
const emojiContainer = document.getElementById('emojiPickerContainer'); // Container for emoji picker
const closeEmojiBtn = document.getElementById('closeEmojiPicker'); // Close button

showEmojiPicker('emojiListPost', 'data-emoji-target');
initEmojiPickerUX('emojiPost', 'emojiPickerContainer');

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

// Poll Creation UI Logic
const addPollBtn = document.getElementById('addPollBtn');
const pollContainer = document.getElementById('pollCreationContainer');
const addOptionBtn = document.getElementById('addPollOptionBtn');
const optionsContainer = document.getElementById('pollOptionsContainer');
const removePollBtn = document.getElementById('removePollBtn');

if (addPollBtn && pollContainer) {
    addPollBtn.addEventListener('click', () => {
        pollContainer.classList.remove('d-none');
    });

    removePollBtn.addEventListener('click', () => {
        pollContainer.classList.add('d-none');
        // Reset inputs
        const inputs = pollContainer.querySelectorAll('input');
        inputs.forEach(input => input.value = '');
    });

    addOptionBtn.addEventListener('click', () => {
        const optionCount = optionsContainer.querySelectorAll('input').length + 1;
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'poll_options[]';
        input.className = 'form-control mb-2';
        input.placeholder = `Option ${optionCount}`;
        input.style.borderRadius = '10px';
        optionsContainer.appendChild(input);
    });
}
