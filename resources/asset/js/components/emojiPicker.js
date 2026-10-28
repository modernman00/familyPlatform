import emojiData from 'emojibase-data/en/data.json';
import { id } from '@modernman00/shared-js-lib';

const EMOJI_CACHE_KEY = 'recentEmojis'; // LocalStorage key for caching recent emojis
const EMOJI_SKIN_KEY = 'preferredEmojiSkinTone'; // LocalStorage key for preferred Fitzpatrick skin tone (0-5)

/**
 * 6 Fitzpatrick Skin Tone Modifiers
 * 0: Default / Neutral Yellow (no modifier)
 * 1: Light (1F3FB)
 * 2: Medium-Light (1F3FC)
 * 3: Medium (1F3FD)
 * 4: Medium-Dark (1F3FE)
 * 5: Dark (1F3FF)
 */
export const SKIN_TONES = [
  { tone: 0, label: 'Default / Neutral', swatch: '🟡' },
  { tone: 1, label: 'Light skin tone', swatch: '🏻' },
  { tone: 2, label: 'Medium-light skin tone', swatch: '🏼' },
  { tone: 3, label: 'Medium skin tone', swatch: '🏽' },
  { tone: 4, label: 'Medium-dark skin tone', swatch: '🏾' },
  { tone: 5, label: 'Dark skin tone', swatch: '🏿' },
];

/**
 * Curated Emoji Categories
 */
export const EMOJI_CATEGORIES = [
  { id: 'recent', label: 'Recently Used', icon: '🕒' },
  { id: 'smileys', label: 'Smileys & Emotion', icon: '😀' },
  { id: 'people', label: 'People & Gestures', icon: '🧑' },
  { id: 'hearts', label: 'Hearts & Affection', icon: '❤️' },
  { id: 'celebration', label: 'Celebration & Events', icon: '🎉' },
  { id: 'nature', label: 'Food & Nature', icon: '🍕' },
  { id: 'activities', label: 'Activities & Objects', icon: '⚽' },
];

// Pre-filter emoji database into distinct category sets for instant tab switching
const CATEGORY_MAP = {
  smileys: emojiData.filter(e => e.group === 0 && e.subgroup !== 14),
  people: emojiData.filter(e => e.group === 1),
  hearts: emojiData.filter(e => e.subgroup === 14 || (e.tags && (e.tags.includes('heart') || e.tags.includes('love')))),
  celebration: emojiData.filter(e => [60, 61, 62, 63].includes(e.subgroup) || (e.tags && (e.tags.includes('party') || e.tags.includes('birthday') || e.tags.includes('celebration')))),
  nature: emojiData.filter(e => e.group === 3 || e.group === 4),
  activities: emojiData.filter(e => e.group === 5 || e.group === 6 || e.group === 7),
};

/**
 * Get preferred skin tone from localStorage (defaults to 0: neutral/yellow)
 */
export const getPreferredSkinTone = () => {
  try {
    const val = localStorage.getItem(EMOJI_SKIN_KEY);
    if (val !== null) {
      const parsed = parseInt(val, 10);
      if (!isNaN(parsed) && parsed >= 0 && parsed <= 5) {
        return parsed;
      }
    }
  } catch (_) {}
  return 0;
};

/**
 * Save preferred skin tone to localStorage
 */
export const setPreferredSkinTone = (tone) => {
  try {
    localStorage.setItem(EMOJI_SKIN_KEY, String(tone));
  } catch (_) {}
};

/**
 * Retrieve the resolved emoji character adapted to a specific skin tone
 */
export const getEmojiForTone = (item, tone = 0) => {
  if (!item) return '';
  if (typeof item === 'string') return item;
  if (tone > 0 && Array.isArray(item.skins) && item.skins.length > 0) {
    const match = item.skins.find(s => s.tone === tone);
    if (match && match.emoji) {
      return match.emoji;
    }
  }
  return item.emoji || '';
};

/**
 * Renders the race-conscious, categorized modern emoji picker.
 * @param {string} emojiContainerId - The ID of the container element for the emoji buttons.
 * @param {string} emojiTargetDataClass - The data class attribute for the emoji target (e.g. data-emoji-target).
 */
export const showEmojiPicker = (emojiContainerId, emojiTargetDataClass) => {
  renderModernEmojiPicker(emojiContainerId, emojiTargetDataClass);
};

/**
 * Initialize UX behaviours like "click outside to close"
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
};

/**
 * Internal modern emoji picker builder
 */
const renderModernEmojiPicker = (emojiContainerId, emojiTargetDataClass) => {
  const emojiList = id(emojiContainerId);
  if (!emojiList) return;

  emojiList.innerHTML = '';
  emojiList.classList.add('modern-emoji-picker');

  const form = emojiList.closest('form');
  const emojiTarget = form ? form.querySelector(`[${emojiTargetDataClass}]`) : null;

  let currentSkinTone = getPreferredSkinTone();
  let currentCategory = 'smileys';
  let searchQuery = '';

  // ── 1. Top Header: Search Box & Fitzpatrick Skin Tone Diversity Palette ──
  const headerWrapper = document.createElement('div');
  headerWrapper.className = 'emoji-picker-header px-2.5 pt-2 pb-1.5 border-bottom';

  // Search input row (with right padding so it does not collide with the popover close button)
  const searchRow = document.createElement('div');
  searchRow.className = 'input-group input-group-sm mb-2 pe-4';
  searchRow.innerHTML = `
    <span class="input-group-text bg-transparent border-end-0 text-muted py-1 px-2" style="border-radius: 8px 0 0 8px;">
      <i class="bi bi-search" style="font-size: 0.75rem;"></i>
    </span>
    <input type="text" class="form-control form-control-sm border-start-0 border-end-0 shadow-none emoji-search-field"
      placeholder="Search emojis (e.g. smile, clap, heart)..." style="font-size: 0.8rem; height: 32px;" autocomplete="off">
    <button type="button" class="btn btn-sm btn-light border border-start-0 text-muted emoji-search-clear d-none"
      style="border-radius: 0 8px 8px 0; font-size: 0.75rem; padding: 0 8px;" title="Clear search">&times;</button>
  `;
  headerWrapper.appendChild(searchRow);

  const searchInput = searchRow.querySelector('.emoji-search-field');
  const clearSearchBtn = searchRow.querySelector('.emoji-search-clear');

  // Race-Conscious Fitzpatrick Diversity Palette Row
  const skinToneBar = document.createElement('div');
  skinToneBar.className = 'emoji-skin-bar d-flex align-items-center justify-content-between pt-1';
  skinToneBar.setAttribute('role', 'toolbar');
  skinToneBar.setAttribute('aria-label', 'Skin tone diversity selector');

  const skinTitle = document.createElement('span');
  skinTitle.className = 'text-muted fw-semibold me-1 text-nowrap';
  skinTitle.style.fontSize = '0.72rem';
  skinTitle.textContent = 'Skin Tone:';
  skinToneBar.appendChild(skinTitle);

  const skinSwatchesContainer = document.createElement('div');
  skinSwatchesContainer.className = 'd-flex align-items-center gap-1';

  SKIN_TONES.forEach(st => {
    const swatchBtn = document.createElement('button');
    swatchBtn.type = 'button';
    swatchBtn.className = `emoji-skin-swatch ${st.tone === currentSkinTone ? 'active' : ''}`;
    swatchBtn.setAttribute('data-tone', st.tone);
    swatchBtn.setAttribute('title', st.label);
    swatchBtn.setAttribute('aria-label', st.label);
    swatchBtn.textContent = st.swatch;

    swatchBtn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      currentSkinTone = st.tone;
      setPreferredSkinTone(currentSkinTone);

      // Update swatch active states
      skinSwatchesContainer.querySelectorAll('.emoji-skin-swatch').forEach(btn => {
        btn.classList.toggle('active', parseInt(btn.getAttribute('data-tone'), 10) === currentSkinTone);
      });

      // Instantly refresh current view
      renderContent();
    });

    skinSwatchesContainer.appendChild(swatchBtn);
  });
  skinToneBar.appendChild(skinSwatchesContainer);
  headerWrapper.appendChild(skinToneBar);

  emojiList.appendChild(headerWrapper);

  // ── 2. Category Tab Navigation ──
  const tabsNav = document.createElement('div');
  tabsNav.className = 'emoji-category-tabs d-flex overflow-x-auto px-2 py-1.5 border-bottom gap-1';
  tabsNav.setAttribute('role', 'tablist');

  EMOJI_CATEGORIES.forEach(cat => {
    const tabBtn = document.createElement('button');
    tabBtn.type = 'button';
    tabBtn.className = `emoji-cat-tab-btn ${cat.id === currentCategory ? 'active' : ''}`;
    tabBtn.setAttribute('role', 'tab');
    tabBtn.setAttribute('data-category', cat.id);
    tabBtn.setAttribute('title', cat.label);
    tabBtn.setAttribute('aria-label', cat.label);
    tabBtn.innerHTML = `<span>${cat.icon}</span>`;

    tabBtn.addEventListener('click', (e) => {
      e.preventDefault();
      currentCategory = cat.id;
      searchQuery = '';
      if (searchInput) searchInput.value = '';
      if (clearSearchBtn) clearSearchBtn.classList.add('d-none');

      tabsNav.querySelectorAll('.emoji-cat-tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-category') === currentCategory);
      });

      renderContent();
    });

    tabsNav.appendChild(tabBtn);
  });
  emojiList.appendChild(tabsNav);

  // ── 3. Content Scroll Container ──
  const contentArea = document.createElement('div');
  contentArea.className = 'emoji-content-area p-2 overflow-y-auto';
  contentArea.style.maxHeight = '240px';
  emojiList.appendChild(contentArea);

  // Search input listeners
  searchInput.addEventListener('input', () => {
    searchQuery = searchInput.value.toLowerCase().trim();
    if (clearSearchBtn) {
      clearSearchBtn.classList.toggle('d-none', !searchQuery);
    }
    renderContent();
  });

  clearSearchBtn.addEventListener('click', (e) => {
    e.preventDefault();
    searchInput.value = '';
    searchQuery = '';
    clearSearchBtn.classList.add('d-none');
    renderContent();
    searchInput.focus();
  });

  // ── 4. Main Content Renderer ──
  const renderContent = () => {
    contentArea.innerHTML = '';

    // If searching, render search results across all emojis
    if (searchQuery) {
      const searchHeader = document.createElement('div');
      searchHeader.className = 'emoji-section-header';
      searchHeader.textContent = `Search results for "${searchQuery}"`;
      contentArea.appendChild(searchHeader);

      const matched = emojiData.filter(e => {
        if (!e.label && !e.tags) return false;
        const inLabel = e.label && e.label.toLowerCase().includes(searchQuery);
        const inTags = Array.isArray(e.tags) && e.tags.some(t => t.toLowerCase().includes(searchQuery));
        return inLabel || inTags;
      }).slice(0, 100);

      if (matched.length === 0) {
        const emptyMsg = document.createElement('div');
        emptyMsg.className = 'text-center py-4 text-muted small';
        emptyMsg.innerHTML = '<i class="bi bi-emoji-neutral fs-4 d-block mb-1 opacity-50"></i>No matching emojis found.';
        contentArea.appendChild(emptyMsg);
        return;
      }

      const grid = document.createElement('div');
      grid.className = 'emoji-grid';
      matched.forEach(item => {
        renderSingleEmojiButton(item, grid, emojiTarget, emojiList, currentSkinTone);
      });
      contentArea.appendChild(grid);
      return;
    }

    // Tab-based view
    if (currentCategory === 'recent') {
      const cached = getRecentEmojis();
      const header = document.createElement('div');
      header.className = 'emoji-section-header';
      header.textContent = 'Recently Used';
      contentArea.appendChild(header);

      if (cached.length === 0) {
        const emptyMsg = document.createElement('div');
        emptyMsg.className = 'text-center py-4 text-muted small';
        emptyMsg.innerHTML = '<i class="bi bi-clock-history fs-4 d-block mb-1 opacity-50"></i>No recent emojis yet.';
        contentArea.appendChild(emptyMsg);
        return;
      }

      const grid = document.createElement('div');
      grid.className = 'emoji-grid';
      cached.forEach(char => {
        renderDirectCharButton(char, grid, emojiTarget, emojiList);
      });
      contentArea.appendChild(grid);
      return;
    }

    // Category view
    const catConfig = EMOJI_CATEGORIES.find(c => c.id === currentCategory);
    const header = document.createElement('div');
    header.className = 'emoji-section-header d-flex align-items-center justify-content-between';
    header.innerHTML = `
      <span>${catConfig ? catConfig.label : 'Emojis'}</span>
      ${currentCategory === 'people' ? '<span class="badge bg-light text-muted border" style="font-size: 0.65rem;">Race &amp; Tone Conscious</span>' : ''}
    `;
    contentArea.appendChild(header);

    const items = CATEGORY_MAP[currentCategory] || [];
    const grid = document.createElement('div');
    grid.className = 'emoji-grid';

    items.forEach(item => {
      renderSingleEmojiButton(item, grid, emojiTarget, emojiList, currentSkinTone);
    });

    contentArea.appendChild(grid);
  };

  // Initial render
  renderContent();
};

/**
 * Render single emoji item button with skin-tone awareness and optional individual tone popover
 */
const renderSingleEmojiButton = (item, container, emojiTarget, pickerList, currentTone) => {
  const resolvedChar = getEmojiForTone(item, currentTone);
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'modern-emoji-btn';
  btn.textContent = resolvedChar;
  btn.setAttribute('aria-label', item.label || 'Emoji');
  btn.setAttribute('title', item.label || 'Emoji');

  // If item has skin variations, mark it for visual clue & long-press tone support
  if (Array.isArray(item.skins) && item.skins.length > 0) {
    btn.classList.add('has-skin-tones');
  }

  const insertEmoji = (charToInsert) => {
    if (emojiTarget) {
      const start = emojiTarget.selectionStart ?? emojiTarget.value.length;
      const end = emojiTarget.selectionEnd ?? emojiTarget.value.length;
      const text = emojiTarget.value || '';

      emojiTarget.value = text.substring(0, start) + charToInsert + text.substring(end);

      const newPos = start + charToInsert.length;
      emojiTarget.setSelectionRange(newPos, newPos);
      emojiTarget.focus();

      cacheEmoji(charToInsert);

      // Auto-dismiss floating popovers; keep in-flow composer drawers open for multi-emoji selection
      const pickerContainer = pickerList.parentElement;
      if (pickerContainer && !pickerContainer.classList.contains('composer-drawer')) {
        pickerContainer.classList.add('d-none');
        const form = pickerContainer.closest('form');
        if (form) {
          const toggle = form.querySelector('[aria-expanded="true"]');
          if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
      }
    }
  };

  // Prevent input focus loss on desktop browsers
  btn.addEventListener('mousedown', (e) => e.preventDefault());

  // Tap handling on mobile devices
  btn.addEventListener('touchstart', (e) => {
    e.preventDefault();
    insertEmoji(resolvedChar);
  }, { passive: false });

  // Click handling
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    insertEmoji(resolvedChar);
  });

  // Long press / right-click tone picker for individual emoji
  if (Array.isArray(item.skins) && item.skins.length > 0) {
    btn.addEventListener('contextmenu', (e) => {
      e.preventDefault();
      showIndividualTonePicker(btn, item, (chosenChar) => insertEmoji(chosenChar));
    });
  }

  container.appendChild(btn);
};

/**
 * Render directly cached emoji character button (from Recent history)
 */
const renderDirectCharButton = (char, container, emojiTarget, pickerList) => {
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'modern-emoji-btn';
  btn.textContent = char;
  btn.setAttribute('aria-label', `Recent ${char}`);

  const insertEmoji = () => {
    if (emojiTarget) {
      const start = emojiTarget.selectionStart ?? emojiTarget.value.length;
      const end = emojiTarget.selectionEnd ?? emojiTarget.value.length;
      const text = emojiTarget.value || '';

      emojiTarget.value = text.substring(0, start) + char + text.substring(end);

      const newPos = start + char.length;
      emojiTarget.setSelectionRange(newPos, newPos);
      emojiTarget.focus();

      cacheEmoji(char);

      const pickerContainer = pickerList.parentElement;
      if (pickerContainer && !pickerContainer.classList.contains('composer-drawer')) {
        pickerContainer.classList.add('d-none');
        const form = pickerContainer.closest('form');
        if (form) {
          const toggle = form.querySelector('[aria-expanded="true"]');
          if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
      }
    }
  };

  btn.addEventListener('mousedown', (e) => e.preventDefault());
  btn.addEventListener('touchstart', (e) => {
    e.preventDefault();
    insertEmoji();
  }, { passive: false });
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    insertEmoji();
  });

  container.appendChild(btn);
};

/**
 * Show inline popup above an individual emoji with all 6 skin tone variants
 */
const showIndividualTonePicker = (anchorBtn, item, onSelect) => {
  const existing = document.querySelector('.emoji-tone-popover');
  if (existing) existing.remove();

  const popover = document.createElement('div');
  popover.className = 'emoji-tone-popover shadow-sm border rounded-pill p-1 d-flex gap-1 bg-white position-absolute';
  popover.style.zIndex = '1060';
  popover.style.top = '-36px';
  popover.style.left = '50%';
  popover.style.transform = 'translateX(-50%)';

  // Tone 0 (Base/Yellow)
  const baseBtn = document.createElement('button');
  baseBtn.type = 'button';
  baseBtn.className = 'emoji-tone-popover-item';
  baseBtn.textContent = item.emoji;
  baseBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    popover.remove();
    onSelect(item.emoji);
  });
  popover.appendChild(baseBtn);

  // Tones 1-5
  item.skins.forEach(s => {
    const sBtn = document.createElement('button');
    sBtn.type = 'button';
    sBtn.className = 'emoji-tone-popover-item';
    sBtn.textContent = s.emoji;
    sBtn.title = s.label;
    sBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      popover.remove();
      onSelect(s.emoji);
    });
    popover.appendChild(sBtn);
  });

  anchorBtn.style.position = 'relative';
  anchorBtn.appendChild(popover);

  const dismiss = (evt) => {
    if (!popover.contains(evt.target)) {
      popover.remove();
      document.removeEventListener('click', dismiss);
    }
  };
  setTimeout(() => document.addEventListener('click', dismiss), 50);
};

/**
 * Cache selected emoji to localStorage
 */
const cacheEmoji = (char) => {
  try {
    const recent = getRecentEmojis();
    const updated = [char, ...recent.filter(e => e !== char)].slice(0, 16);
    localStorage.setItem(EMOJI_CACHE_KEY, JSON.stringify(updated));
  } catch (_) {}
};

/**
 * Read cached emojis from localStorage
 */
const getRecentEmojis = () => {
  try {
    return JSON.parse(localStorage.getItem(EMOJI_CACHE_KEY)) || [];
  } catch (_) {
    return [];
  }
};
