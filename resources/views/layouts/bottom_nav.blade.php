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
    <a href="/organogram/{{ $_SESSION['id'] ?? '' }}" class="pwa-tab-item" data-tab="tree" title="Family Tree">
        <div class="pwa-tab-icon-wrap">
            <i class="bi bi-diagram-3-fill"></i>
        </div>
        <span class="pwa-tab-label">Tree</span>
    </a>

    <!-- Family Studio / Reels -->
    <a href="/familyStudio" class="pwa-tab-item" data-tab="reels" title="Family Studio & Reels">
        <div class="pwa-tab-icon-wrap">
            <i class="bi bi-camera-reels-fill"></i>
        </div>
        <span class="pwa-tab-label">Studio</span>
    </a>

    <!-- Directory -->
    <a href="/allMembers" class="pwa-tab-item" data-tab="members" title="Member Directory">
        <div class="pwa-tab-icon-wrap">
            <i class="bi bi-people-fill"></i>
        </div>
        <span class="pwa-tab-label">Directory</span>
    </a>

    <!-- Notifications & Alerts -->
    <a href="javascript:void(0)" class="pwa-tab-item position-relative" id="pwaNotifTab" data-tab="notifications" title="Alerts & Notifications">
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
        });
    } catch(e) {}
})();
</script>
