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
}
body.dark-mode .pwa-bottom-nav, 
body.dark-theme .pwa-bottom-nav,
[data-bs-theme="dark"] .pwa-bottom-nav { 
    background: var(--nav-frosted-bg, rgba(20, 20, 22, 0.88)) !important;
    border-top: 0.5px solid var(--nav-border, rgba(255, 255, 255, 0.08)) !important;
}
@media (max-width: 991.98px) {
    /* Android resizes the layout viewport for the keyboard and would float the bar over the input */
    body:has(input:focus, textarea:focus, select:focus) .pwa-bottom-nav { display: none !important; }
}

/* Active Tab Micro-Dot & Tonal Icon Fill */
.pwa-tab-item {
    color: var(--text-tertiary, #94a3b8) !important;
    transition: color 150ms ease, transform 120ms cubic-bezier(0.16, 1, 0.3, 1);
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
.pwa-tab-center-action .pwa-center-btn-bubble {
    background: var(--brand-primary-gradient, linear-gradient(135deg, #a31621 0%, #8a1019 100%)) !important;
    border: 3px solid var(--surface-card, #ffffff) !important;
    box-shadow: 0 4px 14px var(--brand-primary-glow, rgba(163, 22, 33, 0.42)) !important;
}
body.dark-mode .pwa-tab-center-action .pwa-center-btn-bubble,
body.dark-theme .pwa-tab-center-action .pwa-center-btn-bubble,
[data-bs-theme="dark"] .pwa-tab-center-action .pwa-center-btn-bubble {
    border-color: #141416 !important;
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

    <!-- Family Studio / Reels -->
    <a href="/familyStudio" class="pwa-tab-item" data-tab="reels" title="{{ __('nav.studio', 'Family Studio') }} - Watch captivating family reels and celebrations">
        <div class="pwa-tab-icon-wrap">
            <i class="bi bi-camera-reels pwa-tab-icon-outline"></i>
            <i class="bi bi-camera-reels-fill pwa-tab-icon-filled"></i>
        </div>
        <span class="pwa-tab-label">{{ __('nav.studio', 'Studio') }}</span>
    </a>

    <!-- Notifications & Alerts -->
    <a href="javascript:void(0)" role="button" aria-haspopup="true" class="pwa-tab-item position-relative" id="pwaNotifTab" data-tab="notifications" title="{{ __('nav.alerts', 'Alerts & Notifications') }} - View family notifications and invitations">
        <div class="pwa-tab-icon-wrap position-relative">
            <i class="bi bi-bell pwa-tab-icon-outline"></i>
            <i class="bi bi-bell-fill pwa-tab-icon-filled"></i>
            <span class="pwa-tab-badge" id="pwa_bottom_badge" x-data x-cloak x-show="$store.notifications.hasUnread" x-text="$store.notifications.display" style="display: none;"></span>
        </div>
        <span class="pwa-tab-label">{{ __('nav.alerts', 'Alerts') }}</span>
    </a>
</nav>

<script>
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
            } else if (dt === 'reels' && (p.indexOf('/reels') !== -1 || p.indexOf('/familystudio') !== -1)) {
                t.classList.add('active');
            } else if (dt === 'members' && p.indexOf('/allmembers') !== -1) {
                t.classList.add('active');
            } else if (dt === 'feed' && (p.indexOf('/profilepage') !== -1 || p === '/' || p === '')) {
                t.classList.add('active');
            }
            if (t.classList.contains('active')) {
                t.setAttribute('aria-current', 'page');
            }
        });

        // Universal 1-Tap Mobile Camera Roll Trigger
        var cameraBtn = document.getElementById('pwaQuickCameraBtn');
        if (cameraBtn) {
            cameraBtn.addEventListener('click', function(e) {
                e.preventDefault();
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
