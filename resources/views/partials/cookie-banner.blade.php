@php
    // GDPR-3 — consent notice. Enabled by default; set COOKIE_CONSENT_ENABLED=0 to hide.
    $ccEnabled = (int) ($_ENV['COOKIE_CONSENT_ENABLED'] ?? getenv('COOKIE_CONSENT_ENABLED') ?: 1) === 1;
    $ccTtlDays = (int) ($_ENV['COOKIE_CONSENT_TTL_DAYS'] ?? 180);
    $ccPrivacyUrl = $_ENV['COOKIE_CONSENT_PRIVACY_URL'] ?? '/privacy';
@endphp

@if($ccEnabled)
<div id="cookieBanner" role="dialog" aria-live="polite" aria-label="Cookie notice" hidden>
  <div class="cookie-card">
    <div class="cookie-copy">
      <strong>Cookies on Family Platform</strong>
      <p>
        We use <em>strictly necessary</em> cookies to keep you signed in and the site
        secure. With your permission we'd also store a few cookies to remember your
        preferences and understand how the app is used. Read our
        <a href="{{ $ccPrivacyUrl }}">Privacy &amp; Cookie Policy</a>.
      </p>
    </div>
    <div class="cookie-actions">
      <button type="button" id="cookieReject" class="cookie-btn cookie-btn-ghost">Necessary only</button>
      <button type="button" id="cookieAccept" class="cookie-btn cookie-btn-solid">Accept all</button>
    </div>
  </div>
</div>

<style nonce="{{ $nonce ?? '' }}">
  #cookieBanner {
    position: fixed;
    left: 0; right: 0; bottom: 0;
    /* Below Bootstrap's modal (1055) and offcanvas — a notice must never sit on
       top of a dialog the user is filling in. */
    z-index: 1040;
    padding: 0.9rem clamp(0.75rem, 3vw, 2rem);
    /* The whole banner is inert to hit-testing except the two buttons, so it
       never blocks a click (or a test's visibility check) on the page beneath. */
    pointer-events: none;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
  }
  #cookieBanner[hidden] { display: none !important; }
  #cookieBanner .cookie-btn,
  #cookieBanner .cookie-copy a { pointer-events: auto; }
  #cookieBanner .cookie-card {
    max-width: 60rem;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.85rem 1.5rem;
    background: #ffffff;
    color: #1f1d17;
    border: 1px solid #e2e0d6;
    border-radius: 14px;
    box-shadow: 0 12px 40px -12px rgba(20, 19, 14, 0.35);
    padding: 1rem 1.2rem;
  }
  #cookieBanner .cookie-copy { flex: 1 1 22rem; }
  #cookieBanner .cookie-copy strong { display: block; font-size: 0.92rem; margin-bottom: 0.2rem; }
  #cookieBanner .cookie-copy p { margin: 0; font-size: 0.83rem; line-height: 1.5; color: #5c5749; }
  #cookieBanner .cookie-copy a { color: #00857a; font-weight: 600; }
  #cookieBanner .cookie-actions { display: flex; gap: 0.55rem; flex: 0 0 auto; }
  #cookieBanner .cookie-btn {
    font: inherit;
    font-size: 0.83rem;
    font-weight: 600;
    padding: 0.55rem 1.1rem;
    border-radius: 999px;
    cursor: pointer;
    border: 1px solid transparent;
    transition: background-color .15s ease, border-color .15s ease, transform .15s ease;
  }
  #cookieBanner .cookie-btn-ghost { background: #ffffff; border-color: #cfccc0; color: #3c3830; }
  #cookieBanner .cookie-btn-ghost:hover { border-color: #00bfa5; color: #00857a; }
  #cookieBanner .cookie-btn-solid { background: #00bfa5; color: #ffffff; }
  #cookieBanner .cookie-btn-solid:hover { background: #00a794; transform: translateY(-1px); }
  #cookieBanner .cookie-btn:focus-visible { outline: 2px solid #00857a; outline-offset: 2px; }
  @media (max-width: 560px) {
    #cookieBanner .cookie-actions { width: 100%; }
    #cookieBanner .cookie-btn { flex: 1; }
  }
  @media (prefers-color-scheme: dark) {
    #cookieBanner .cookie-card { background: #1d1b16; color: #ece7db; border-color: #34302a; }
    #cookieBanner .cookie-copy p { color: #a29a88; }
    #cookieBanner .cookie-btn-ghost { background: #1d1b16; border-color: #45403a; color: #d8d2c4; }
  }
</style>

<script nonce="{{ $nonce ?? '' }}">
(function () {
  'use strict';
  if (window.__cookieConsent) return;

  var NAME = 'cookie_consent';
  var TTL = {{ $ccTtlDays }};

  function read() {
    var m = document.cookie.match(/(?:^|;\s*)cookie_consent=([^;]+)/);
    return m ? decodeURIComponent(m[1]) : null;
  }
  function write(level) {
    var d = new Date();
    d.setTime(d.getTime() + TTL * 864e5);
    var secure = location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = NAME + '=' + encodeURIComponent(level) +
      '; expires=' + d.toUTCString() + '; path=/; SameSite=Lax' + secure;
  }

  // Public API for any future non-essential script (analytics, etc.):
  //   if (window.cookieConsent.allows('analytics')) { … }
  window.cookieConsent = {
    level: read() || null,
    allows: function (category) {
      if (category === 'necessary') return true;
      return this.level === 'all';
    },
    onChange: [],
  };
  window.__cookieConsent = true;

  function decide(level) {
    write(level);
    window.cookieConsent.level = level;
    var b = document.getElementById('cookieBanner');
    if (b) b.hidden = true;
    window.cookieConsent.onChange.forEach(function (fn) {
      try { fn(level); } catch (e) { /* ignore */ }
    });
    if (typeof window.gtag === 'function') {
      var g = level === 'all' ? 'granted' : 'denied';
      window.gtag('consent', 'update', { analytics_storage: g, ad_storage: g });
    }
  }

  function init() {
    if (read()) return; // already chosen — never re-prompt
    var b = document.getElementById('cookieBanner');
    if (!b) return;
    b.hidden = false;
    var a = document.getElementById('cookieAccept');
    var r = document.getElementById('cookieReject');
    if (a) a.addEventListener('click', function () { decide('all'); });
    if (r) r.addEventListener('click', function () { decide('necessary'); });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>
@endif
