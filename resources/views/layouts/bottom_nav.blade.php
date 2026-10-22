<!-- Mobile PWA Bottom Tab Navigation (Native App Store / Facebook Standard) -->
<nav class="pwa-bottom-nav d-lg-none" id="pwaBottomNav" aria-label="Mobile App Navigation">
    <!-- Home / Feed -->
    <a href="/profilePage" class="pwa-tab-item" data-tab="feed" title="Home Feed">
        <div class="pwa-tab-icon-wrap">
            <i class="bi bi-house-door-fill"></i>
        </div>
        <span class="pwa-tab-label">Home</span>
    </a>

    <!-- Family Tree -->
    <a href="/organogram/{{ $_SESSION['id'] ?? '' }}?mode=canvas" class="pwa-tab-item" data-tab="tree" title="Family Tree">
        <div class="pwa-tab-icon-wrap">
            <i class="bi bi-diagram-3-fill"></i>
        </div>
        <span class="pwa-tab-label">Tree</span>
    </a>

    <!-- Quick Camera & Photo Upload (Universal iPhone & Mobile 1-Tap Trigger) -->
    <button type="button" class="pwa-tab-item pwa-tab-center-action" id="pwaQuickCameraBtn" aria-label="Add photo or memory from camera roll" title="Add Photo or Video">
        <div class="pwa-center-btn-bubble shadow-md">
            <i class="bi bi-camera-fill"></i>
        </div>
        <span class="pwa-tab-label">Add Photo</span>
    </button>

    <!-- Family Studio / Reels -->
    <a href="/familyStudio" class="pwa-tab-item" data-tab="reels" title="Family Studio & Reels">
        <div class="pwa-tab-icon-wrap">
            <i class="bi bi-camera-reels-fill"></i>
        </div>
        <span class="pwa-tab-label">Studio</span>
    </a>

    <!-- Notifications & Alerts -->
    <a href="javascript:void(0)" role="button" aria-haspopup="true" class="pwa-tab-item position-relative" id="pwaNotifTab" data-tab="notifications" title="Alerts & Notifications">
        <div class="pwa-tab-icon-wrap position-relative">
            <i class="bi bi-bell-fill"></i>
            <span class="pwa-tab-badge" id="pwa_bottom_badge" style="display: none;"></span>
        </div>
        <span class="pwa-tab-label">Alerts</span>
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

        function pinBottomNav() {
            if (window.visualViewport) {
                var vv = window.visualViewport;
                var offset = Math.max(0, window.innerHeight - vv.height - vv.offsetTop);
                if (offset > 120) {
                    nav.style.transform = 'translateY(' + (-offset) + 'px)';
                } else {
                    nav.style.transform = 'translateY(0)';
                }
            }
        }
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', pinBottomNav, { passive: true });
            window.visualViewport.addEventListener('scroll', pinBottomNav, { passive: true });
        }
        window.addEventListener('resize', pinBottomNav, { passive: true });
        window.addEventListener('orientationchange', pinBottomNav, { passive: true });
    } catch(e) {}
})();
</script>
