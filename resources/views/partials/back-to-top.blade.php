<!-- Modern Back to Top Floating Action Button -->
<button id="backToTopBtn" class="back-to-top-btn" aria-label="Back to top" title="Back to top" type="button">
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="back-to-top-icon">
    <path d="M18 15l-6-6-6 6"/>
  </svg>
</button>

<script nonce="{{ $nonce ?? '' }}">
(function() {
  function setupBackToTop() {
    var btn = document.getElementById('backToTopBtn');
    if (!btn || btn.dataset.initialized) return;
    btn.dataset.initialized = 'true';

    var ticking = false;
    function onScroll() {
      if (!ticking) {
        window.requestAnimationFrame(function() {
          if (window.scrollY > 300) {
            btn.classList.add('is-visible');
          } else {
            btn.classList.remove('is-visible');
          }
          ticking = false;
        });
        ticking = true;
      }
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    btn.addEventListener('click', function(e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupBackToTop, { once: true });
  } else {
    setupBackToTop();
  }
})();
</script>
