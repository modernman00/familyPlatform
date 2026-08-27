// Import a helper function to get elements by ID (assumed from your shared utils)
import { showEmojiPicker, initEmojiPickerUX } from '../emojiPicker.js';
import { imagePreview } from '../fileUploadPreview';

// Get references to DOM elements used in the emoji picker and image preview - SHOW EMOJIs FOR POST

const emojiToggle = document.getElementById('emojiPost'); // Button to show/hide emoji picker
const emojiContainer = document.getElementById('emojiPickerContainer'); // Container for emoji picker
const closeEmojiBtn = document.getElementById('closeEmojiPicker'); // Close button

if (document.getElementById('emojiListPost')) {
    showEmojiPicker('emojiListPost', 'data-emoji-target');
}
if (emojiToggle && emojiContainer) {
    initEmojiPickerUX('emojiPost', 'emojiPickerContainer');
}

// 🟡 Toggle emoji picker visibility when the toggle button is clicked
if (emojiToggle && emojiContainer) {
    emojiToggle.addEventListener('click', () => {
        emojiContainer.classList.toggle('d-none'); // Show/hide the emoji container
        emojiToggle.setAttribute('aria-expanded', emojiContainer.classList.contains('d-none') ? 'false' : 'true');
    });
}

// Close button handler
if (closeEmojiBtn && emojiContainer && emojiToggle) {
    closeEmojiBtn.addEventListener('click', () => {
        emojiContainer.classList.add('d-none');
        emojiToggle.setAttribute('aria-expanded', 'false');
    });
}

if (document.getElementById('imageUpload')) {
    imagePreview('imageUpload', 'imagePreviewList', 'postModalImgFileNames', 'imagePreviewContainer', 'closeImagePreview');
}

// Poll Creation UI Logic
const addPollBtn = document.getElementById('addPollBtn');
const pollContainer = document.getElementById('pollCreationContainer');
const addOptionBtn = document.getElementById('addPollOptionBtn');
const optionsContainer = document.getElementById('pollOptionsContainer');
const removePollBtn = document.getElementById('removePollBtn');

const MAX_POLL_OPTIONS = 6;

const closePoll = () => {
    if (!pollContainer) return;
    pollContainer.classList.add('d-none');
    if (addPollBtn) addPollBtn.classList.remove('poll-active');
    // Reset to two blank options
    pollContainer.querySelectorAll('input').forEach(input => (input.value = ''));
    if (optionsContainer) {
        const extras = optionsContainer.querySelectorAll('.poll-builder__option');
        extras.forEach((el, i) => { if (i > 1) el.remove(); });
    }
};

if (addPollBtn && pollContainer) {
    addPollBtn.addEventListener('click', () => {
        const isHidden = pollContainer.classList.contains('d-none');
        if (isHidden) {
            pollContainer.classList.remove('d-none');
            addPollBtn.classList.add('poll-active');
            const questionInput = pollContainer.querySelector('input[name="poll_question"]');
            if (questionInput) setTimeout(() => questionInput.focus(), 60);
        } else {
            closePoll();
        }
    });

    if (removePollBtn) {
        removePollBtn.addEventListener('click', closePoll);
    }

    if (addOptionBtn && optionsContainer) {
        addOptionBtn.addEventListener('click', () => {
            const current = optionsContainer.querySelectorAll('.poll-builder__option').length;
            if (current >= MAX_POLL_OPTIONS) return;

            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'poll_options[]';
            input.className = 'poll-builder__option';
            input.placeholder = `Option ${current + 1}`;
            input.maxLength = 80;
            optionsContainer.appendChild(input);
            input.focus();

            if (current + 1 >= MAX_POLL_OPTIONS) {
                addOptionBtn.style.display = 'none';
            }
        });
    }
}

// Expose so the submit handler can reset the builder after a successful post
window.__resetPollBuilder = closePoll;

