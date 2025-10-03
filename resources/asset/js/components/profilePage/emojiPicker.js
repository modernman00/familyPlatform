// Import emoji metadata from emojibase (English locale, full dataset)
import emojiData from 'emojibase-data/en/data.json';

// Import a helper function to get elements by ID (assumed from your shared utils)
import { id } from '@shared';

// Get references to DOM elements used in the emoji picker and image preview
const emojiTarget = document.querySelector('[data-emoji-target]'); // Where emojis will be inserted
const emojiToggle = document.getElementById('emojiToggle'); // Button to show/hide emoji picker
const emojiList = document.getElementById('emojiPickerList'); // Container for emoji buttons
const EMOJI_CACHE_KEY = 'recentEmojis'; // LocalStorage key for caching recent emojis

// Image upload and preview elements
const imageInput = id('imageUpload'); // Hidden file input for image uploads
const previewContainer = id('imagePreviewContainer'); // Wrapper for image previews
const previewList = id('imagePreviewList'); // Where preview thumbnails are shown
const closePreviewBtn = id('closeImagePreview'); // Button to clear image previews
const fileNamesDisplay = id('postModalImgFileNames'); // Text display of selected file names

// 🟡 Filter emojis to only include smileys (based on Unicode range)
const smileys = emojiData.filter(e => {
    const code = parseInt(e.hexcode, 16); // Convert hexcode to decimal
    return code >= 0x1F600 && code <= 0x1F64F; // Emoticons block range
});

// Render the filtered smiley emojis into the picker
renderEmojiList(smileys);

// 🟡 Toggle emoji picker visibility when the toggle button is clicked
emojiToggle.addEventListener('click', () => {
    emojiList.classList.toggle('d-none'); // Show/hide the emoji list
    emojiToggle.setAttribute('aria-expanded', emojiList.classList.contains('d-none') ? 'false' : 'true');
});

// 🟡 Render a list of emoji buttons into the picker
function renderEmojiList(emojis) {
    emojiList.innerHTML = ''; // Clear existing emojis

    // Load and render cached recent emojis first
    const cached = JSON.parse(localStorage.getItem(EMOJI_CACHE_KEY)) || [];
    cached.forEach(emoji => renderEmojiButton(emoji, 'Recent'));

    // Render up to 70 emojis from the filtered list
    emojis.slice(0, 70).forEach(({ emoji, label, skins }) => {
        renderEmojiButton(emoji, label); // Main emoji

        // If skin tone variants exist, render them too
        if (skins) {
            skins.forEach(({ emoji: skinEmoji }) => {
                renderEmojiButton(skinEmoji, `${label} (skin tone)`);
            });
        }
    });
}

// 🟡 Create and insert a single emoji button
function renderEmojiButton(char, label) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'emoji-btn btn btn-sm btn-light'; // Styling classes
    btn.textContent = char; // Emoji character
    btn.setAttribute('aria-label', label); // Accessibility label

    // When clicked, insert emoji into target and cache it
    btn.addEventListener('click', () => {
        emojiTarget.value += char;
        cacheEmoji(char);
    });

    emojiList.appendChild(btn); // Add button to picker
}

// 🟡 Save emoji to recent cache in localStorage
function cacheEmoji(char) {
    const recent = JSON.parse(localStorage.getItem(EMOJI_CACHE_KEY)) || [];

    // Add emoji to front of list, remove duplicates, keep max 10
    const updated = [char, ...recent.filter(e => e !== char)].slice(0, 10);
    localStorage.setItem(EMOJI_CACHE_KEY, JSON.stringify(updated));
}

// 🟡 Handle image file selection and preview thumbnails
imageInput.addEventListener('change', () => {
    const files = Array.from(imageInput.files); // Convert FileList to array
    previewList.innerHTML = ''; // Clear previous previews
    fileNamesDisplay.textContent = ''; // Clear file name display

    if (files.length === 0) {
        previewContainer.classList.add('d-none'); // Hide preview section
        return;
    }

    // For each selected image, create a thumbnail preview
    files.forEach(file => {
        if (file.size > 3 * 1024 * 1024) { // 3MB limit
            alert('File too large. Max 10MB allowed.');
        }

        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result; // Base64 image data
            img.alt = 'Preview';
            img.className = 'img-thumbnail';
            img.style.maxWidth = '100px';
            img.style.maxHeight = '100px';
            previewList.appendChild(img);
        };
        reader.readAsDataURL(file); // Convert file to Base64
    });

    // Show file names and reveal preview container
    fileNamesDisplay.textContent = files.map(f => f.name).join(', ');
    previewContainer.classList.remove('d-none');
});

// 🟡 Clear image previews and reset input
closePreviewBtn.addEventListener('click', () => {
    imageInput.value = ''; // Reset file input
    previewList.innerHTML = ''; // Clear thumbnails
    fileNamesDisplay.textContent = ''; // Clear file names
    previewContainer.classList.add('d-none'); // Hide preview section
});
