{{--
  Unified TikTok-Style Guest Gate Overlay
  Displays a dismissible dark-glass sign-in / sign-up overlay over shared public
  previews (posts, Daily Sparks, etc.) with one-tap OAuth, email/Family Code,
  a >=44px Skip button, and non-blocking zero-PII funnel telemetry.

  Expected props:
  - $shareType (string: 'post'|'spark'|'arcade'|'festive_card')
  - $itemKey (string|int)
  - $registerUrl (string)
  - $loginUrl (string)
  - $gateTitle (string, optional)
  - $gateSubtitle (string, optional)
  - $referrerSource (string, optional)
--}}
@php
    $ggShareType = (string)($shareType ?? 'post');
    $ggItemKey = (string)($itemKey ?? '');
    $ggRegisterUrl = (string)($registerUrl ?? '/register');
    $ggLoginUrl = (string)($loginUrl ?? '/login');
    $ggTitle = (string)($gateTitle ?? 'Sign in to FamilyPlatform');
    $ggSubtitle = (string)($gateSubtitle ?? 'Join your private family circle to view, react, and preserve memories together.');
    $ggRefSource = (string)($referrerSource ?? 'direct');
@endphp

<style nonce="{{ csp_nonce($nonce ?? null) }}">
    [x-cloak] { display: none !important; }
    .fp-gg-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9990;
        background: rgba(5, 8, 16, 0.72);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .fp-gg-backdrop.is-hidden {
        display: none !important;
    }
    .fp-gg-card {
        position: relative;
        width: 100%;
        max-width: 410px;
        background: #141923;
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 28px 64px rgba(0, 0, 0, 0.65);
        color: #f8fafc;
        text-align: center;
    }
    .fp-gg-topbar {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 4px;
    }
    .fp-gg-skip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        min-width: 44px;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.05);
        color: #cbd5e1;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
        transition: background 0.15s ease, color 0.15s ease, transform 0.12s ease;
    }
    .fp-gg-skip:hover {
        background: rgba(255, 255, 255, 0.12);
        color: #ffffff;
    }
    .fp-gg-skip:active { transform: scale(0.98); }
    .fp-gg-skip:focus-visible { outline: 2px solid #fbbf24; outline-offset: 2px; }
    .fp-gg-title {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.25;
        color: #ffffff;
        margin-bottom: 8px;
        letter-spacing: -0.01em;
    }
    .fp-gg-subtitle {
        font-size: 0.875rem;
        line-height: 1.5;
        color: #cbd5e1;
        margin-bottom: 20px;
    }
    .fp-gg-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 18px;
    }
    .fp-gg-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        min-height: 46px;
        padding: 10px 18px;
        border-radius: 999px;
        background: #222938;
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #f8fafc;
        font-size: 0.9375rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        flex-shrink: 0;
        transition: background 0.15s ease, transform 0.12s ease, border-color 0.15s ease;
    }
    .fp-gg-btn:hover {
        background: #2d3649;
        border-color: rgba(255, 255, 255, 0.24);
        color: #ffffff;
    }
    .fp-gg-btn:active { transform: scale(0.98); }
    .fp-gg-btn:focus-visible { outline: 2px solid #fbbf24; outline-offset: 2px; }
    .fp-gg-btn--primary {
        background: #1d72fe;
        border-color: #1d72fe;
        color: #ffffff;
        box-shadow: 0 8px 20px rgba(29, 114, 254, 0.35);
    }
    .fp-gg-btn--primary:hover {
        background: #1761dc;
        border-color: #1761dc;
    }
    .fp-gg-legal {
        font-size: 0.75rem;
        line-height: 1.5;
        color: #94a3b8;
        margin-bottom: 16px;
    }
    .fp-gg-legal a {
        color: #e2e8f0;
        text-decoration: underline;
    }
    .fp-gg-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 16px;
        font-size: 0.875rem;
        color: #cbd5e1;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .fp-gg-footer a {
        color: #fbbf24;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 0 8px;
    }
    .fp-gg-footer a:hover { text-decoration: underline; }
    .fp-gg-reopen {
        position: fixed;
        bottom: max(20px, env(safe-area-inset-bottom));
        right: 20px;
        z-index: 9980;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 44px;
        padding: 10px 18px;
        border-radius: 999px;
        background: #1d72fe;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 0.875rem;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45);
        white-space: nowrap;
        flex-shrink: 0;
    }
    .fp-gg-reopen.is-hidden {
        display: none !important;
    }
    .fp-gg-reopen:active { transform: scale(0.98); }
    .fp-gg-reopen:focus-visible { outline: 2px solid #fbbf24; outline-offset: 2px; }
</style>

<div class="fp-gg-root"
     data-share-type="{{ $ggShareType }}"
     data-item-key="{{ $ggItemKey }}"
     data-referrer-source="{{ $ggRefSource }}"
     x-data="{ open: true }"
     @keydown.escape.window="if (open) { open = false; $dispatch('fp-gg-skip'); }">

    <div class="fp-gg-backdrop"
         role="dialog"
         aria-modal="true"
         aria-labelledby="fpGuestGateTitle"
         x-show="open"
         x-trap.noscroll="open">
        <div class="fp-gg-card" @click.outside="open = false; $dispatch('fp-gg-skip')">
            <div class="fp-gg-topbar">
                <button type="button"
                        class="fp-gg-skip"
                        data-gg-skip
                        @click="open = false; $dispatch('fp-gg-skip')"
                        aria-label="Skip sign-in and preview content">
                    Skip
                </button>
            </div>

            <h2 id="fpGuestGateTitle" class="fp-gg-title">{{ $ggTitle }}</h2>
            <p class="fp-gg-subtitle">{{ $ggSubtitle }}</p>

            <div class="fp-gg-actions">
                <a href="{{ $ggRegisterUrl }}"
                   class="fp-gg-btn fp-gg-btn--primary"
                   data-spin="true"
                   data-gg-track="signup_click"
                   data-gg-provider="email">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Use email or Family Code</span>
                </a>

                <a href="/auth/facebook"
                   class="fp-gg-btn"
                   data-spin="true"
                   data-gg-track="auth_click"
                   data-gg-provider="facebook">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2" aria-hidden="true">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span>Continue with Facebook</span>
                </a>

                <a href="/auth/google"
                   class="fp-gg-btn"
                   data-spin="true"
                   data-gg-track="auth_click"
                   data-gg-provider="google">
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17Z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24Z"/>
                        <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15Z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98Z"/>
                    </svg>
                    <span>Continue with Google</span>
                </a>
            </div>

            <p class="fp-gg-legal">
                By continuing, you agree to FamilyPlatform’s
                <a href="/termOfUse">Terms of Use</a> and
                <a href="/privacy">Privacy Policy</a>.
            </p>

            <div class="fp-gg-footer">
                <div>
                    Already a family member?
                    <a href="{{ $ggLoginUrl }}"
                       data-gg-track="auth_click"
                       data-gg-provider="login">Sign in</a>
                </div>
            </div>
        </div>
    </div>

    <button type="button"
            class="fp-gg-reopen is-hidden"
            data-gg-reopen
            x-show="!open"
            @click="open = true">
        <span>Sign in / Join Family</span>
    </button>
</div>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function () {
    const root = document.querySelector('.fp-gg-root');
    if (!root) return;

    const shareType = root.dataset.shareType || 'post';
    const itemKey = root.dataset.itemKey || '';
    const referrerSource = root.dataset.referrerSource || 'direct';

    function sendTrack(eventType, provider) {
        const payload = JSON.stringify({
            shareType: shareType,
            itemKey: itemKey,
            eventType: eventType,
            provider: provider || '',
            referrerSource: referrerSource
        });
        try {
            if (navigator.sendBeacon) {
                const blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon('/api/public/share/track', blob);
                return;
            }
        } catch (_) {}
        try {
            fetch('/api/public/share/track', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: payload,
                keepalive: true
            }).catch(function () {});
        } catch (_) {}
    }

    sendTrack('view', '');

    const backdrop = root.querySelector('.fp-gg-backdrop');
    const skipBtn = root.querySelector('[data-gg-skip]');
    const reopenBtn = root.querySelector('[data-gg-reopen]');

    function dismissGate() {
        sendTrack('skip', '');
        if (!window.Alpine && backdrop && reopenBtn) {
            backdrop.classList.add('is-hidden');
            reopenBtn.classList.remove('is-hidden');
            reopenBtn.removeAttribute('x-cloak');
        }
    }

    function reopenGate() {
        if (!window.Alpine && backdrop && reopenBtn) {
            backdrop.classList.remove('is-hidden');
            reopenBtn.classList.add('is-hidden');
        }
    }

    if (skipBtn) {
        skipBtn.addEventListener('click', dismissGate);
    }
    if (reopenBtn) {
        reopenBtn.addEventListener('click', reopenGate);
    }
    if (backdrop) {
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) {
                dismissGate();
            }
        });
    }

    root.querySelectorAll('[data-gg-track]').forEach(function (el) {
        el.addEventListener('click', function () {
            sendTrack(el.dataset.ggTrack || 'signup_click', el.dataset.ggProvider || '');
        });
    });
})();
</script>
