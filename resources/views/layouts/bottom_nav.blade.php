<style>
/* Scroll-stability & Frosted Acrylic Standard (v2.1 Instagram/iOS Glassmorphism) */
.pwa-bottom-nav {
    background: var(--nav-frosted-bg, rgba(255, 255, 255, 0.88)) !important;
    -webkit-backdrop-filter: blur(20px) saturate(180%) !important;
    backdrop-filter: blur(20px) saturate(180%) !important;
    border-top: 0.5px solid var(--nav-border, rgba(0, 0, 0, 0.08)) !important;
    -webkit-transform: translate3d(0, 0, 0);
    transform: translate3d(0, 0, 0);
    will-change: transform;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.2s ease !important;
    touch-action: manipulation !important;
    -webkit-tap-highlight-color: transparent !important;
    padding-bottom: env(safe-area-inset-bottom, 0px);
}
.pwa-bottom-nav.nav-hidden {
    transform: translate3d(0, 105%, 0) !important;
}
body.dark-mode .pwa-bottom-nav, 
body.dark-theme .pwa-bottom-nav,
[data-bs-theme="dark"] .pwa-bottom-nav { 
    background: var(--nav-frosted-bg, rgba(20, 20, 22, 0.88)) !important;
    border-top: 0.5px solid var(--nav-border, rgba(255, 255, 255, 0.08)) !important;
}
@media (max-width: 991.98px) {
    /* Android resizes the layout viewport for the keyboard and would float the bar over the input */
    body:has(input:focus, textarea:focus, select:focus, [contenteditable="true"]:focus) .pwa-bottom-nav { display: none !important; }
}

/* Active Tab Micro-Dot & Tonal Icon Fill */
.pwa-tab-item {
    color: var(--text-tertiary, #475569) !important;
    min-height: 44px;
    min-width: 44px;
    touch-action: manipulation !important;
    -webkit-tap-highlight-color: transparent !important;
    transition: color 150ms ease, transform 120ms cubic-bezier(0.16, 1, 0.3, 1);
}
.pwa-tab-item:active {
    transform: scale(0.92);
}
body.dark-mode .pwa-tab-item, 
body.dark-theme .pwa-tab-item,
[data-bs-theme="dark"] .pwa-tab-item { 
    color: #94a3b8 !important;
}
.pwa-tab-item .pwa-tab-icon-outline { display: inline-block; }
.pwa-tab-item .pwa-tab-icon-filled { display: none; }

.pwa-tab-item.active {
    color: var(--brand-primary, #a31621) !important;
}
.pwa-tab-item.active .pwa-tab-icon-outline { display: none; }
.pwa-tab-item.active .pwa-tab-icon-filled { 
    display: inline-block;
    color: var(--brand-primary, #a31621) !important;
}
.pwa-tab-item.active .pwa-tab-label {
    color: var(--brand-primary, #a31621) !important;
    font-weight: 700;
}
.pwa-tab-label {
    font-weight: 600;
    letter-spacing: -0.01em;
}
.pwa-tab-item.active .pwa-tab-icon-wrap::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background-color: var(--brand-primary, #a31621);
    box-shadow: 0 0 6px var(--brand-primary-glow, rgba(163, 22, 33, 0.5));
}
.pwa-tab-center-action {
    touch-action: manipulation !important;
    -webkit-tap-highlight-color: transparent !important;
    padding-bottom: env(safe-area-inset-bottom, 0px);
}
.pwa-tab-center-action:active .pwa-center-btn-bubble {
    transform: scale(0.92);
}
.pwa-tab-center-action .pwa-center-btn-bubble {
    background: var(--brand-primary-gradient, linear-gradient(135deg, #a31621 0%, #8a1019 100%)) !important;
    border: 3px solid var(--surface-card, #ffffff) !important;
    box-shadow: 0 4px 14px var(--brand-primary-glow, rgba(163, 22, 33, 0.42)) !important;
    transition: transform 0.15s cubic-bezier(0.16, 1, 0.3, 1);
}
body.dark-mode .pwa-tab-center-action .pwa-center-btn-bubble,
body.dark-theme .pwa-tab-center-action .pwa-center-btn-bubble,
[data-bs-theme="dark"] .pwa-tab-center-action .pwa-center-btn-bubble {
    border-color: #141416 !important;
}
/* 3D Spatial FamChat Orb in Mobile Bottom Bar */
.pwa-famchat-3d-orb {
    position: relative;
    width: var(--space-8, 32px);
    height: var(--space-8, 32px);
    border-radius: var(--radius-md, 12px);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background:
        radial-gradient(circle at 30% 24%, rgba(255, 255, 255, 0.85) 0%, rgba(255, 255, 255, 0) 42%),
        linear-gradient(135deg, var(--accent-gold, #f59e0b) 0%, var(--brand-primary, #a31621) 58%, var(--brand-primary-hover, #8a1019) 100%);
    color: #ffffff !important;
    box-shadow:
        0 2.5px 0 var(--brand-primary-hover, #8a1019),
        0 5px 12px var(--brand-primary-glow, rgba(163, 22, 33, 0.35)),
        inset 0 1px 1.5px rgba(255, 255, 255, 0.8);
    transform-style: preserve-3d;
    animation: pwaFamchatOrbFloat 3.2s var(--ease, ease-in-out) infinite;
}
.pwa-famchat-3d-orb i {
    color: #ffffff !important;
    font-size: var(--text-base, 1rem) !important;
    filter: drop-shadow(0 1px 2px rgba(80, 7, 36, 0.55));
}
.pwa-famchat-3d-orb::after {
    content: '';
    position: absolute;
    top: -2px;
    right: -2px;
    width: var(--space-2, 8px);
    height: var(--space-2, 8px);
    border-radius: 50%;
    background: #4ade80;
    border: 1.5px solid #ffffff;
    box-shadow: 0 0 6px #4ade80;
}
#pwaWhisperTab .pwa-tab-label {
    color: var(--brand-primary, #a31621) !important;
    font-weight: 800;
}
body.dark-mode #pwaWhisperTab .pwa-tab-label,
[data-bs-theme="dark"] #pwaWhisperTab .pwa-tab-label {
    color: #fda4af !important;
}
@keyframes pwaFamchatOrbFloat {
    0%, 100% { transform: translateY(0) rotateX(10deg) rotateY(-10deg); }
    50% { transform: translateY(-2px) rotateX(-8deg) rotateY(12deg) scale(1.05); }
}
@media (prefers-reduced-motion: reduce) {
    .pwa-famchat-3d-orb { animation: none !important; }
}
</style>
<!-- Mobile PWA Bottom Tab Navigation (Native App Store / Instagram Standard) -->
<nav class="pwa-bottom-nav d-lg-none" id="pwaBottomNav" aria-label="Mobile App Navigation">
    <!-- Home / Feed -->
    <a href="/profilePage" class="pwa-tab-item" data-tab="feed" title="{{ __('nav.home', 'Home Feed') }} - Go to main family updates and posts">
        <div class="pwa-tab-icon-wrap">
            <i class="bi bi-house pwa-tab-icon-outline"></i>
            <i class="bi bi-house-door-fill pwa-tab-icon-filled"></i>
        </div>
        <span class="pwa-tab-label">{{ __('nav.home', 'Home') }}</span>
    </a>

    <!-- Family Tree -->
    <a href="/organogram/{{ $_SESSION['id'] ?? '' }}?mode=canvas" class="pwa-tab-item" data-tab="tree" title="{{ __('nav.tree', 'Family Tree') }} - Explore living family tree and lineage">
        <div class="pwa-tab-icon-wrap">
            <i class="bi bi-diagram-3 pwa-tab-icon-outline"></i>
            <i class="bi bi-diagram-3-fill pwa-tab-icon-filled"></i>
        </div>
        <span class="pwa-tab-label">{{ __('nav.tree', 'Tree') }}</span>
    </a>

    <!-- Quick Camera & Photo Upload (Universal iPhone & Mobile 1-Tap Trigger) -->
    <button type="button" class="pwa-tab-item pwa-tab-center-action" id="pwaQuickCameraBtn" aria-label="{{ __('nav.add_photo', 'Add photo or memory from camera roll') }}" title="{{ __('nav.add_photo', 'Add Photo or Video') }} - Instant camera capture or photo upload">
        <div class="pwa-center-btn-bubble shadow-md">
            <i class="bi bi-camera-fill"></i>
        </div>
        <span class="pwa-tab-label">{{ __('nav.add_photo', 'Add Photo') }}</span>
    </button>

    <!-- FamChat (private kin messages — 3D Living Spatial Orb) -->
    <a href="/kin-whispers" class="pwa-tab-item position-relative" id="pwaWhisperTab" data-tab="whisper" title="{{ __('nav.whisper', 'FamChat') }} - Private kin notes and voice dedications">
        <div class="pwa-tab-icon-wrap position-relative">
            <span class="pwa-famchat-3d-orb" aria-hidden="true">
                <i class="bi bi-chat-heart-fill"></i>
            </span>
            <span class="pwa-tab-badge whisper-portal-unread-badge d-none"><span class="whisper-portal-unread-count"></span></span>
        </div>
        <span class="pwa-tab-label">{{ __('nav.whisper', 'FamChat') }}</span>
    </a>

    <!-- Notifications & Alerts -->
    <a href="javascript:void(0)" role="button" aria-haspopup="true" class="pwa-tab-item position-relative" id="pwaNotifTab" data-tab="notifications" aria-label="{{ __('nav.alerts', 'Alerts') }} — View family notifications and invitations">
        <div class="pwa-tab-icon-wrap position-relative">
            <i class="bi bi-bell pwa-tab-icon-outline"></i>
            <i class="bi bi-bell-fill pwa-tab-icon-filled"></i>
            <span class="pwa-tab-badge" id="pwa_bottom_badge" aria-hidden="true" x-data x-cloak x-show="$store.notifications.hasUnread" x-text="$store.notifications.display" style="display: none;"></span>
        </div>
        <span class="pwa-tab-label">{{ __('nav.alerts', 'Alerts') }}</span>
    </a>
</nav>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function() {
    try {
        var p = window.location.pathname.toLowerCase();
        var nav = document.getElementById('pwaBottomNav');
        if (!nav) return;
        var tabs = nav.querySelectorAll('.pwa-tab-item');
        tabs.forEach(function(t) {
            var dt = t.dataset.tab;
            if (dt === 'tree' && p.indexOf('/organogram') !== -1) {
                t.classList.add('active');
            } else if (dt === 'whisper' && p.indexOf('/kin-whispers') !== -1) {
                t.classList.add('active');
            } else if (dt === 'members' && p.indexOf('/allmembers') !== -1) {
                t.classList.add('active');
            } else if (dt === 'feed' && (p.indexOf('/profilepage') !== -1 || p === '/' || p === '')) {
                t.classList.add('active');
            }
            if (t.classList.contains('active')) {
                t.setAttribute('aria-current', 'page');
            }

            // Data-Saver-Aware Speculative Tab Prefetching on pointerdown (80-120ms head start)
            var href = t.getAttribute('href');
            if (href && href.charAt(0) === '/' && !t.classList.contains('active')) {
                t.addEventListener('pointerdown', function() {
                    var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
                    if (conn && (conn.saveData || conn.effectiveType === '2g' || conn.effectiveType === 'slow-2g')) {
                        return;
                    }
                    if (!document.querySelector('link[rel="prefetch"][href="' + href + '"]')) {
                        var link = document.createElement('link');
                        link.rel = 'prefetch';
                        link.href = href;
                        link.as = 'document';
                        document.head.appendChild(link);
                    }
                }, { once: true, passive: true });
            }
        });

        // Universal 1-Tap Mobile Camera Roll Trigger
        var cameraBtn = document.getElementById('pwaQuickCameraBtn');
        if (cameraBtn) {
            cameraBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof window.ensureComposerBundle === 'function') {
                    window.ensureComposerBundle();
                }
                var postModalEl = document.getElementById('postModal');
                var fileInput = document.getElementById('imageUpload');
                if (postModalEl && window.bootstrap && window.bootstrap.Modal) {
                    var modal = window.bootstrap.Modal.getOrCreateInstance(postModalEl);
                    modal.show();
                    if (fileInput) {
                        try {
                            fileInput.click();
                        } catch (err) {
                            console.warn('[Camera] Direct click fallback:', err);
                        }
                    }
                } else {
                    window.location.href = '/profilePage?open_camera=1';
                }
            });
        }

        // Auto-open modal if navigated from another page via ?open_camera=1
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('open_camera')) {
            window.history.replaceState({}, document.title, window.location.pathname);
            if (typeof window.ensureComposerBundle === 'function') {
                window.ensureComposerBundle();
            }
            setTimeout(function() {
                var postModalEl = document.getElementById('postModal');
                var fileInput = document.getElementById('imageUpload');
                if (postModalEl && window.bootstrap && window.bootstrap.Modal) {
                    var modal = window.bootstrap.Modal.getOrCreateInstance(postModalEl);
                    modal.show();
                    if (fileInput) {
                        try {
                            fileInput.click();
                        } catch (err) {}
                    }
                }
            }, 300);
        }
    } catch(e) {}
})();
</script>
