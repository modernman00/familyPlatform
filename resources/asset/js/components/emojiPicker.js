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
 * Initialize UX behaviors like "click outside to close"
 * @param {string} toggleId - ID of the button that opens the picker
 * @param {string} containerId - ID of the picker container
 */
export const initEmojiPickerUX = (toggleId, containerId) => {
  const toggle = id(toggleId);
  const container = id(containerId);

  if (!toggle || !container) return;

  document.addEventListener('click', (e) => {
    if (!container.classList.contains('d-none')) {
      if (!container.contains(e.target) && !toggle.contains(e.target)) {
        container.classList.add('d-none');
        toggle.setAttribute('aria-expanded', 'false');
      }
    }
  });
}

const renderEmojiList = (emojis, emojiContainerId, emojiTargetDataClass) => {
  const emojiList = id(emojiContainerId);
  if (!emojiList) return;

  emojiList.innerHTML = '';
  emojiList.classList.add('modern-emoji-picker');

  const form = emojiList.closest('form');
  const emojiTarget = form.querySelector(`[${emojiTargetDataClass}]`);

  const cached = JSON.parse(localStorage.getItem(EMOJI_CACHE_KEY)) || [];

  if (cached.length > 0) {
    const recentHeader = document.createElement('div');
    recentHeader.className = 'emoji-section-header';
    recentHeader.textContent = 'Recently Used';
    emojiList.appendChild(recentHeader);

    const recentGrid = document.createElement('div');
    recentGrid.className = 'emoji-grid';
    cached.forEach(emoji => renderEmojiButton(emoji, 'Recent', recentGrid, emojiTarget, emojiList));
    emojiList.appendChild(recentGrid);
  }

  const allHeader = document.createElement('div');
  allHeader.className = 'emoji-section-header';
  allHeader.textContent = 'All Smileys';
  emojiList.appendChild(allHeader);

  const allGrid = document.createElement('div');
  allGrid.className = 'emoji-grid';
  emojis.slice(0, 100).forEach(({ emoji, label, skins }) => {
    renderEmojiButton(emoji, label, allGrid, emojiTarget, emojiList);

    if (skins) {
      skins.forEach(({ emoji: skinEmoji }) => {
        renderEmojiButton(skinEmoji, `${label} (skin tone)`, allGrid, emojiTarget, emojiList);
      });
    }
  });
  emojiList.appendChild(allGrid);
}

// 🟡 Create and insert a single emoji button
const renderEmojiButton = (char, label, emojiContainer, emojiTarget, pickerList) => {
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'modern-emoji-btn';
  btn.textContent = char;
  btn.setAttribute('aria-label', label);

  btn.addEventListener('click', (e) => {
    e.preventDefault();
    if (emojiTarget) {
      const start = emojiTarget.selectionStart;
      const end = emojiTarget.selectionEnd;
      const text = emojiTarget.value;
      
      emojiTarget.value = text.substring(0, start) + char + text.substring(end);
      
      const newPos = start + char.length;
      emojiTarget.setSelectionRange(newPos, newPos);
      emojiTarget.focus();
      
      cacheEmoji(char);

      // Auto-dismiss the picker
      const pickerContainer = pickerList.parentElement;
      if (pickerContainer) {
        pickerContainer.classList.add('d-none');
        // Update aria state on the toggle button if found
        const form = pickerContainer.closest('form');
        if (form) {
          const toggle = form.querySelector('[aria-expanded="true"]');
          if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
      }
    }
  });

  emojiContainer.appendChild(btn);
}

// 🟡 Save emoji to recent cache in localStorage
const cacheEmoji = (char) => {
  const recent = JSON.parse(localStorage.getItem(EMOJI_CACHE_KEY)) || [];

  // Add emoji to front of list, remove duplicates, keep max 10
  const updated = [char, ...recent.filter(e => e !== char)].slice(0, 10);
  localStorage.setItem(EMOJI_CACHE_KEY, JSON.stringify(updated));
}
