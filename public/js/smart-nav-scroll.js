/**
 * FamilyPlatform Smart Nav & Bottom Bar Scroll Auto-Hide Engine
 * High-performance, rAF-throttled scroll coordination for mobile & desktop.
 * Smoothly hides top navbar & bottom bar on scroll-down to maximize content viewport;
 * Instantly restores them on scroll-up or top-of-page.
 */
(function() {
    'use strict';

    var lastScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
    var ticking = false;
    var SCROLL_DOWN_THRESHOLD = 8;
    var SCROLL_UP_THRESHOLD = 5;
    var MIN_SCROLL_TOP = 65;

    function getNavElements() {
        return {
            topNav: document.getElementById('mainNavbar') || document.querySelector('.public-navbar'),
            bottomNav: document.getElementById('pwaBottomNav')
        };
    }

    function isSafeToHide() {
        // Guard 1: Never hide if a modal is open
        if (document.body.classList.contains('modal-open') || document.querySelector('.modal.show')) {
            return false;
        }

        // Guard 2: Never hide if a mobile collapse menu is currently open
        var collapseMenu = document.querySelector('#navbarContent.show, #navbarNav.show');
        if (collapseMenu) {
            return false;
        }

        // Guard 3: Never hide if a dropdown or notification panel is open
        var openDropdown = document.querySelector('.dropdown-menu.show, #notificationDropdown.show, .mobile-pwa-dropdown.show');
        if (openDropdown) {
            return false;
        }

        // Guard 4: Never hide if an input or textarea currently has focus (keyboard active)
        var activeEl = document.activeElement;
        if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.isContentEditable)) {
            return false;
        }

        return true;
    }

    function onScrollTick() {
        var currentScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
        var diff = currentScrollY - lastScrollY;
        var navs = getNavElements();

        if (currentScrollY <= MIN_SCROLL_TOP) {
            // Near top of page: always reveal
            if (navs.topNav) navs.topNav.classList.remove('nav-hidden');
            if (navs.bottomNav) navs.bottomNav.classList.remove('nav-hidden');
        } else if (diff > SCROLL_DOWN_THRESHOLD && currentScrollY > MIN_SCROLL_TOP) {
            // Scrolling DOWN: auto-hide if safe
            if (isSafeToHide()) {
                if (navs.topNav) navs.topNav.classList.add('nav-hidden');
                if (navs.bottomNav) navs.bottomNav.classList.add('nav-hidden');
            }
        } else if (diff < -SCROLL_UP_THRESHOLD) {
            // Scrolling UP: instantly reveal
            if (navs.topNav) navs.topNav.classList.remove('nav-hidden');
            if (navs.bottomNav) navs.bottomNav.classList.remove('nav-hidden');
        }

        lastScrollY = Math.max(0, currentScrollY);
        ticking = false;
    }

    function handleScroll() {
        if (!ticking) {
            window.requestAnimationFrame(onScrollTick);
            ticking = true;
        }
    }

    // Attach passive scroll listener
    window.addEventListener('scroll', handleScroll, { passive: true });

    // Focus-within protection: if any control receives keyboard focus, reveal navigation immediately
    document.addEventListener('focusin', function(e) {
        var navs = getNavElements();
        if ((navs.topNav && navs.topNav.contains(e.target)) || (navs.bottomNav && navs.bottomNav.contains(e.target))) {
            if (navs.topNav) navs.topNav.classList.remove('nav-hidden');
            if (navs.bottomNav) navs.bottomNav.classList.remove('nav-hidden');
        }
    });

    // When modal closes or menu collapses, re-evaluate
    document.addEventListener('hidden.bs.modal', function() {
        var navs = getNavElements();
        if (navs.topNav) navs.topNav.classList.remove('nav-hidden');
        if (navs.bottomNav) navs.bottomNav.classList.remove('nav-hidden');
    });

    // Expose utility for programmatic controls if needed
    window.FamilyNavControl = {
        show: function() {
            var navs = getNavElements();
            if (navs.topNav) navs.topNav.classList.remove('nav-hidden');
            if (navs.bottomNav) navs.bottomNav.classList.remove('nav-hidden');
        },
        hide: function() {
            if (isSafeToHide()) {
                var navs = getNavElements();
                if (navs.topNav) navs.topNav.classList.add('nav-hidden');
                if (navs.bottomNav) navs.bottomNav.classList.add('nav-hidden');
            }
        }
    };
})();
