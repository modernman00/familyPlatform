import emojiData from 'emojibase-data/en/data.json';
import { id, log } from '@modernman00/shared-js-lib';

const EMOJI_CACHE_KEY = 'recentEmojis'; // LocalStorage key for caching recent emojis


// 🟡 Filter emojis to only include smileys (based on Unicode range)
const smileys = emojiData.filter(e => {
  const code = parseInt(e.hexcode, 16); // Convert hexcode to decimal
  return code >= 0x1F600 && code <= 0x1F64F; // Emoticons block range
});


/**
 * Renders the filtered smiley emojis into the picker.
 * @param {string} emojiContainerId - The ID of the container element for the emoji buttons.
 * @param {string} emojiTargetDataClass - The data class attribute for the emoji target i.e data-emoji-target.
 */
export const showEmojiPicker = (emojiContainerId, emojiTargetDataClass) => {
  // Render the filtered smiley emojis into the picker
  renderEmojiList(smileys, emojiContainerId, emojiTargetDataClass);
}

/**
 * Render a list of emoji buttons into the picker
 * @param {Array} emojis - An array of emoji objects from emojibase
 * @param {string} emojiContainerId - The ID of the container element for the emoji buttons
 * @param {string} emojiTargetDataClass - The data class attribute for the emoji target i.e data-emoji-target
 * 
 */

const renderEmojiList = (emojis, emojiContainerId, emojiTargetDataClass) => {

  const emojiList = id(emojiContainerId); // Container for emoji buttons
 
  emojiList.innerHTML = ''; // Clear existing emojis

  const emojiTarget = document.querySelector(`[${emojiTargetDataClass}]`); // Where emojis will be inserted

  // Load and render cached recent emojis first
  const cached = JSON.parse(localStorage.getItem(EMOJI_CACHE_KEY)) || [];

  cached.forEach(emoji => renderEmojiButton(emoji, 'Recent', emojiList, emojiTarget));

  // Render up to 70 emojis from the filtered list
  emojis.slice(0, 70).forEach(({ emoji, label, skins }) => {
    renderEmojiButton(emoji, label, emojiList, emojiTarget); // Main emoji

    // If skin tone variants exist, render them too
    if (skins) {
      skins.forEach(({ emoji: skinEmoji }) => {
        renderEmojiButton(skinEmoji, `${label} (skin tone)`, emojiList, emojiTarget);
      });
    }
  });
}

// 🟡 Create and insert a single emoji button
const renderEmojiButton = (char, label, emojiContainer, emojiTarget) => {
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

  emojiContainer.appendChild(btn); // Add button to picker
}

// 🟡 Save emoji to recent cache in localStorage
const cacheEmoji = (char) => {
  const recent = JSON.parse(localStorage.getItem(EMOJI_CACHE_KEY)) || [];

  // Add emoji to front of list, remove duplicates, keep max 10
  const updated = [char, ...recent.filter(e => e !== char)].slice(0, 10);
  localStorage.setItem(EMOJI_CACHE_KEY, JSON.stringify(updated));
}
