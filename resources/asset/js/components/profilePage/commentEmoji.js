import { qSelAll, qSel, id, log } from '@shared'
import { set } from 'lodash';

// =============================================
// REACTION DATA STORAGE
// =============================================

/**
 * This object stores ALL the reaction data for EVERY comment
 * Think of it like a big scoreboard that tracks:
 * - How many of each reaction type each comment has
 * - What reaction the current user has given (if any)
 */
const reactionsData = {
  // Comment ID 700: 2 likes, 1 love, no other reactions, current user hasn't reacted
  700: { like: 2, love: 1, haha: 0, wow: 0, sad: 0, angry: 0, userReaction: null },
  
  // Comment ID 701: 5 likes, 2 loves, 3 hahas, etc.
  701: { like: 5, love: 2, haha: 3, wow: 1, sad: 0, angry: 0, userReaction: null },
  
  // Add more comments as needed...
  702: { like: 3, love: 4, haha: 1, wow: 2, sad: 0, angry: 0, userReaction: null },
};

/**
 * We need 'let' instead of 'const' for hoverTimeout because:
 * - We need to CHANGE its value multiple times (clear and reset it)
 * - 'const' would make it permanent (can't change)
 * - 'let' allows reassignment
 */
let hoverTimeout;

// =============================================
// REACTION MANAGEMENT FUNCTIONS
// =============================================

/**
 * REMOVE a user's reaction from a comment
 * @param {number} commentId - Which comment to remove reaction from
 */
const removeReaction = (commentId) => {
  // Get the reaction data for this specific comment
  const data = reactionsData[commentId];
  
  // Safety check: If no data or user hasn't reacted, do nothing
  if (!data?.userReaction) return;
  
  // Decrease the count for the reaction type user had
  // Example: If user had 'love', then data.love becomes data.love - 1
  data[data.userReaction]--;
  
  // Mark that user no longer has any reaction
  data.userReaction = null;
  
  // Show a message to the user
  showNotification('Reaction removed!');
  
  // Update the display to show the changes
  updateReactionDisplay(commentId);
};

/**
 * ADD or UPDATE a user's reaction to a comment
 * @param {number} commentId - Which comment to react to
 * @param {string} reaction - Type of reaction ('like', 'love', etc.)
 * @param {string} emoji - The emoji character to display
 */
const updateReaction = (commentId, reaction, emoji) => {
  // Get the reaction data for this specific comment
  const data = reactionsData[commentId];
  
  // Safety check: If no data found, show warning and stop
  if (!data) {
    console.warn(`No reaction data for comment ${commentId}`);
    return;
  }

  // If user already had a different reaction, remove it first
  // Example: User had 'like' but now clicks 'love' - remove the 'like'
  if (data.userReaction && data.userReaction !== reaction) {
    data[data.userReaction]--; // Decrease old reaction count
  }

  // If user is adding a new reaction or changing to a different one
  if (!data.userReaction || data.userReaction !== reaction) {
    data[reaction]++;          // Increase new reaction count
    data.userReaction = reaction; // Remember user's choice
    showNotification(`Reacted with ${reaction}!`);
  }

  // Update the display to show the new reaction
  updateReactionDisplay(commentId);
};

/**
 * SHOW a temporary message to the user (like a toast notification)
 * @param {string} message - Text to show in the notification
 * @param {boolean} isError - Whether this is an error message (changes color)
 */
const showNotification = (message, isError = false) => {
  // Find the notification element in the HTML
  const notification = document.getElementById('notification');
  if (!notification) return;
  
  // Set the message text
  notification.textContent = message;
  
  // Change color: red for errors, blue for success
  notification.style.backgroundColor = isError ? 'var(--danger-color)' : 'var(--primary-color)';
  
  // Make the notification visible
  notification.classList.add('show');

  // Hide the notification after 3 seconds automatically
  setTimeout(() => {
    notification.classList.remove('show');
  }, 3000);
};

/**
 * UPDATE the visual display of reactions for a comment
 * This function makes the HTML match our data
 * @param {number} commentId - Which comment to update
 */
const updateReactionDisplay = (commentId) => {
  // Get data and all the HTML elements we need to update
  const data = reactionsData[commentId];
  const likeButton = document.getElementById(`like-button-${commentId}`);
  const likeCount = document.getElementById(`like-count-${commentId}`);
  const reactionPreview = document.getElementById(`reaction-preview-${commentId}`);

  // Safety check: Make sure all elements exist before trying to update them
  if (!data || !likeButton || !likeCount || !reactionPreview) return;

  // ===== CALCULATE TOTAL REACTIONS =====
  /**
   * We use 'reduce' to add up all reaction counts
   * It's like going through each reaction type and keeping a running total
   */
  const reactionTypes = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];
  const totalReactions = reactionTypes.reduce((total, type) => {
    return total + data[type]; // Add this reaction type's count to the total
  }, 0); // Start counting from 0

  // ===== UPDATE THE LIKE BUTTON =====
  /**
   * Map reaction types to their emoji characters
   * This is like a dictionary: 'like' → '👍', 'love' → '❤️', etc.
   */
  const emojiMap = {
    'like': '👍',
    'love': '❤️', 
    'haha': '😄',
    'wow': '😮',
    'sad': '😢',
    'angry': '😠'
  };

  // Check if current user has reacted to this comment
  if (data.userReaction) {
    // USER HAS REACTED - Show their reaction
    
    // Change the button icon to show which reaction user chose
    likeButton.querySelector('.reaction-icon').textContent = emojiMap[data.userReaction];
    
    // Change the button text (Like → Love, Like → Haha, etc.)
    likeButton.querySelector('span').textContent = 
      data.userReaction.charAt(0).toUpperCase() + data.userReaction.slice(1);
    
    // Add blue color to show it's active
    likeButton.classList.add('active');
  } else {
    // USER HAS NOT REACTED - Show default state
    
    // Reset to default thumbs-up icon
    likeButton.querySelector('.reaction-icon').className = 'bi bi-hand-thumbs-up reaction-icon';
    
    // Reset text to "Like"
    likeButton.querySelector('span').textContent = 'Like';
    
    // Remove blue color
    likeButton.classList.remove('active');
  }

  // Update the like counter number
  likeCount.textContent = data.like;

  // ===== UPDATE REACTION PREVIEW =====
  // Clear any existing preview content
  reactionPreview.innerHTML = '';

  // Only show preview if there are any reactions
  if (totalReactions > 0) {
    // Find which reactions have counts (filter) and take top 3 (slice)
    const topReactions = reactionTypes
      .filter(type => data[type] > 0)  // Only keep reactions with count > 0
      .slice(0, 3);                    // Take only first 3

    // Create and add emoji previews for top reactions
    topReactions.forEach(type => {
      // Create a div element for this emoji
      const emojiPreview = document.createElement('div');
      emojiPreview.className = 'reaction-preview-emoji';
      emojiPreview.textContent = emojiMap[type]; // Set the emoji character
      reactionPreview.appendChild(emojiPreview); // Add to preview area
    });

    // Add the reaction count text (e.g., "5 reactions")
    const countElement = document.createElement('div');
    countElement.className = 'reaction-preview-count';
    
    // Use proper grammar: "1 reaction" vs "5 reactions"
    countElement.textContent = totalReactions === 1 
      ? '1 reaction' 
      : `${totalReactions} reactions`;
    
    reactionPreview.appendChild(countElement);
  }
};

// =============================================
// EVENT HANDLING SYSTEM - WITH ERROR FIXES
// =============================================

/**
 * MAIN FUNCTION: Set up all event listeners for reactions
 * This is the "brain" that makes everything interactive
 */
const initializeReactionsSimple = () => {
  console.log('🔄 Starting simple reaction system...');
  
  // Handle clicks on reaction emojis
  document.addEventListener('click', (event) => {
    if (event.target.classList?.contains('reaction-option')) {
      const commentDiv = event.target.closest('.commentDiv');
      if (!commentDiv) return;
      
      const commentNo = commentDiv.getAttribute('data-comment-no');
      const reaction = event.target.getAttribute('data-reaction');
      
      // Animate and update
      event.target.classList.add('bounce');
      setTimeout(() => event.target.classList.remove('bounce'), 500);
      updateReaction(commentNo, reaction, event.target.textContent.trim());
      
      // Hide reaction bar
      const reactionBar = document.getElementById(`reaction-bar-${commentNo}`);
      if (reactionBar) reactionBar.classList.remove('show');
    }
  });
  
  // Handle clicks on like buttons
  document.addEventListener('click', (event) => {
    const likeButton = event.target.closest('[id^="like-button-"]');
    if (likeButton) {
      const commentNo = likeButton.id.replace('like-button-', '');
      const currentReaction = reactionsData[commentNo]?.userReaction;
      
      currentReaction ? removeReaction(commentNo) : updateReaction(commentNo, 'like', '👍');
    }
  });
  
  // Show reaction bar on hover
  // document.addEventListener('mouseenter', (event) => {
  //   const likeButton = event.target.closest('[id^="like-button-"]');
  //   if (likeButton) {
  //     const commentNo = likeButton.id.replace('like-button-', '');
  //     const reactionBar = document.getElementById(`reaction-bar-${commentNo}`);
      
  //     if (reactionBar) {
  //       clearTimeout(hoverTimeout);
  //       hoverTimeout = setTimeout(() => reactionBar.classList.add('show'), 300);
  //     }
  //   }
  // }, true);
  
  // Hide reaction bar on mouse leave
  document.addEventListener('mouseleave', (event) => {
    const likeButton = event.target.closest('[id^="like-button-"]');
    const reactionBarElement = event.target.closest('.reaction-bar');
    
    if (likeButton || reactionBarElement) {
      clearTimeout(hoverTimeout);
      setTimeout(() => {
        document.querySelectorAll('.reaction-bar.show').forEach(bar => {
          if (!bar.matches(':hover')) bar.classList.remove('show');
        });
      }, 200);
    }
  }, true);

};

// Start the simple version
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeReactionsSimple);
} else {
  initializeReactionsSimple();
}

// =============================================
// INITIALIZATION - START THE SYSTEM
// =============================================

// Start the simple version
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initializeReactionsSimple);
} else {
  initializeReactionsSimple();
}



// =============================================
// EXTRA COMMENT FUNCTIONS (Remove/Report)
// =============================================

/**
 * REMOVE a comment when trash button is clicked
 * @param {number} commentId - Which comment to remove
 */


// =============================================
// DEBUGGING HELPERS
// =============================================

/**
 * DEBUG FUNCTION: Check what's causing the closest error
 * This will help us understand why event.target.closest fails
 */
const debugEventTarget = (event) => {
  console.log('=== DEBUG EVENT TARGET ===');
  console.log('Event type:', event.type);
  console.log('Event target id:', event.target.id);
  console.log('Event target type:', typeof event.target);
  console.log('Event target constructor:', event.target?.constructor?.name);
  console.log('Has closest method:', typeof event.target?.closest);
  console.log('==========================');
};

// Optional: Uncomment to see debug info for all clicks
// document.addEventListener('click', debugEventTarget);