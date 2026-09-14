'use strict';

/**
 * Initializes the modern Back to Top floating action button.
 */
export function initBackToTop() {
  const btn = document.getElementById('backToTopBtn');
  if (!btn) return;

  let ticking = false;

  const onScroll = () => {
    if (!ticking) {
      window.requestAnimationFrame(() => {
        if (window.scrollY > 300) {
          btn.classList.add('is-visible');
        } else {
          btn.classList.remove('is-visible');
        }
        ticking = false;
      });
      ticking = true;
    }
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  btn.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
}

export default initBackToTop;
