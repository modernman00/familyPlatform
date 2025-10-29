    /**
 * Initializes long-press behavior for all visible reaction buttons.
 * This loop ensures onboarding clarity for contributors working with dynamic comment blocks.
 */
document.querySelectorAll('.reaction-button').forEach(btn => {
  const commentNo = btn.dataset.commentNo;
  setupLongPressReaction(commentNo);
});

/**
 * Sets up long-press behavior on a reaction button to reveal emoji options.
 * This improves mobile UX and teaches contributors how to scaffold gesture-based interactions.
 *
 * @param {string} commentNo - Unique identifier for the comment block
 */
const setupLongPressReaction =(commentNo)=>{
  const button = document.getElementById(`like-button-${commentNo}`);
  const reactionBar = document.getElementById(`reaction-bar-${commentNo}`);
  if (!button || !reactionBar) return; // 🧩 Defensive check for missing DOM elements

  let pressTimer; // ⏱️ Used to track long-press duration

  // 📱 Mobile long-press: triggers on touchstart after delay
  button.addEventListener('touchstart', (e) => {
    e.preventDefault(); // 🧼 Prevent accidental scroll or tap
    pressTimer = setTimeout(() => {
      reactionBar.classList.add('show'); // 🎉 Reveal emoji bar
    }, 400); // ⏱️ Customize delay to match UX expectations
  });

  // 🧼 Cancel long-press if user lifts finger or scrolls
  button.addEventListener('touchend', () => clearTimeout(pressTimer));
  button.addEventListener('touchmove', () => clearTimeout(pressTimer));

  // 🖱️ Optional desktop fallback: long-press via mouse
  button.addEventListener('mousedown', () => {
    pressTimer = setTimeout(() => {
      reactionBar.classList.add('show');
    }, 500); // Slightly longer for desktop UX
  });

  // 🧼 Cancel desktop long-press if mouse leaves or releases
  button.addEventListener('mouseup', () => clearTimeout(pressTimer));
  button.addEventListener('mouseleave', () => clearTimeout(pressTimer));
}

