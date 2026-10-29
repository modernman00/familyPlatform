import { log } from "../global";

/**
 * 🧠 Extracts the top 3 reaction types by count.
 * Filters out metadata keys like 'comment_no' and 'total', then sorts descending.
 *
 * @param {Object} counts - Reaction summary object including totals and metadata
 * @returns {Array} - Array of top 3 reactions like [ ['love', 5], ['wow', 3], ['likes', 2] ]
 */
const getTopReactions = (counts = {}) =>
  Object.entries(counts)
    .filter(([label, count]) =>
      !['comment_no', 'total', 'totalReactions'].includes(label) && count > 0
    ) // 🧼 Remove metadata and zero-count reactions
    .sort(([, a], [, b]) => b - a) // 🔢 Sort descending by count
    .slice(0, 3); // 🎯 Return top 3 reactions only


/**
 * 🧠 Renders the top 3 reactions into the preview section of a comment.
 * Uses emoji map for visual clarity and teaches contributors how to safely update the DOM.
 *
 * @param {Object} counts - Reaction counts object from the server
 * @param {string|number} commentNo - Unique identifier for the comment block
 */
export const renderTopReactions = (counts) => {
  const top = getTopReactions(counts); // 🧠 Get top 3 reactions

  const html = top
    .map(([label, count]) => {
      if (typeof window !== 'undefined' && typeof window.__getHeirloomGlassSvg === 'function') {
        const svg = window.__getHeirloomGlassSvg(label, 20);
        return `<span class="d-inline-flex align-items-center gap-1">${svg}<span>${count}</span></span>`;
      }
      // 🧩 Fallback map
      const emojiMap = {
        likes: '👍', like: '👍', love: '❤️', haha: '😄',
        wow: '😮', shock: '😮', sad: '😢', angry: '😠', celebrate: '✨'
      };
      const emoji = emojiMap[label] ?? ''; // 🧼 Fallback if label missing
      return `${emoji} ${count}`; // 🖼️ Render emoji + count
    })
    .join(' '); // 🧵 Combine into single HTML string
  return html;
};

