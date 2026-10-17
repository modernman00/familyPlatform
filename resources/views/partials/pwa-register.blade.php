{{-- 
  PWA Service Worker Registration Partial: FamilyPlatform
  Registers the canonical root-scoped /sw.js worker and initializes PWA lifecycle listeners.
--}}
<script nonce="{{ $nonce ?? '' }}">
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
      navigator.serviceWorker.register('/sw.js', { scope: '/' })
        .then(function(reg) {
          if (reg.installing) {
            console.log('[PWA] Service Worker installing:', reg.scope);
          } else if (reg.waiting) {
            console.log('[PWA] Service Worker installed/waiting:', reg.scope);
          } else if (reg.active) {
            console.log('[PWA] Service Worker active:', reg.scope);
          }
        })
        .catch(function(err) {
          console.warn('[PWA] Service Worker registration failed:', err);
        });
    });
  }
</script>
