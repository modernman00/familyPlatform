<!-- Google Stitch Navigation Bar -->
@php
    $navUnreadNotifCount = !empty($_SESSION['id']) ? \App\controller\NotificationController::getCalculatedUnreadCount((string)$_SESSION['id']) : 0;
    $navWhisperUnreadCount = !empty($_SESSION['id'])
        ? \App\services\KinWhisperService::getUnreadWhisperCount((string)$_SESSION['id'])
        : 0;
@endphp
<link rel="stylesheet" href="/public/css/navbar.css?v={{ assetVersion('css/navbar.css') }}">
<nav class="navbar navbar-expand-lg sticky-top brand-navbar-stitch" id="mainNavbar">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Brand Logo & Typography -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="/profilePage" style="text-decoration: none;">
            <div class="brand-logo-icon-wrapper">
                <img src="{{ $_ENV['APP_LOGO'] ?? getenv('APP_LOGO') ?: '/public/img/logo/logo-mark.png' }}" alt="FamilyPlatform Logo" width="34" height="34" style="width: 34px; height: 34px; border-radius: 9px; object-fit: contain; display: block;">
            </div>
            <div class="brand-text-container">
                <span class="brand-name-family">Family</span><span class="brand-name-platform">Platform</span>
            </div>
        </a>

        <!-- Quick mobile search icon for phone viewports -->
        <button class="btn btn-icon-stitch btn-mobile-omni-trigger d-lg-none ms-auto me-2" type="button" data-bs-toggle="modal" data-bs-target="#omniSearchModal" aria-label="Search activities, memories, and family members" title="Search activities, memories, and family members">
            <i class="bi bi-search"></i>
        </button>

        <!-- Quick mobile FamChat 3D orb icon with bell & counter -->
        <a href="/kin-whispers" class="btn famchat-3d-nav-btn famchat-3d-nav-btn--mobile d-lg-none me-2 position-relative text-decoration-none" id="mobileWhispersBtn" aria-label="{{ __('nav.whisper', 'FamChat — Private family messages') }}" title="{{ __('nav.whisper', 'FamChat — Private family messages & audio dedications') }}">
            <span class="famchat-3d-orb-wrap" aria-hidden="true">
                <span class="famchat-3d-orb">
                    <span class="famchat-3d-sheen"></span>
                    <svg class="famchat-3d-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 3C6.48 3 2 6.82 2 11.5C2 13.68 2.98 15.66 4.6 17.16L3.4 21L7.72 19.3C9.07 19.75 10.51 20 12 20C17.52 20 22 16.18 22 11.5C22 6.82 17.52 3 12 3Z" fill="#ffffff" stroke="#78350f" stroke-width="1.2" stroke-linejoin="round"/>
                        <path d="M12 15.4C12 15.4 8.2 13.1 8.2 10.6C8.2 9.4 9.15 8.5 10.25 8.5C10.95 8.5 11.58 8.85 12 9.45C12.42 8.85 13.05 8.5 13.75 8.5C14.85 8.5 15.8 9.4 15.8 10.6C15.8 13.1 12 15.4 12 15.4Z" fill="#e11d48"/>
                    </svg>
                </span>
                <span class="famchat-3d-spark famchat-3d-spark--1"></span>
                <span class="famchat-3d-spark famchat-3d-spark--2"></span>
            </span>
            <i class="bi bi-bell-fill text-warning whisper-bell-indicator position-absolute {{ $navWhisperUnreadCount > 0 ? '' : 'd-none' }}" style="font-size: 0.58rem; top: 1px; right: 1px;" title="Unread FamChat messages"></i>
            <span class="whisper-nav-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white {{ $navWhisperUnreadCount > 0 ? '' : 'd-none' }}" id="mobile_whisper_count" style="font-size: 0.62rem; padding: 2px 5px; border: 1.5px solid #ffffff; pointer-events: none;">
                {{ $navWhisperUnreadCount > 0 ? $navWhisperUnreadCount : '' }}
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation" title="Toggle navigation menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 ms-lg-2">
                <li class="nav-item profilePageNav">
                    <a class="nav-link stitch-nav-link" href="/profilePage" title="{{ __('nav.my_page', 'My Page') }} - View and manage your family profile">
                        <i class="bi bi-person-fill"></i>{{ __('nav.my_page', 'My Page') }}
                    </a>
                </li>
                <li class="nav-item familyTreeNav">
                    <a class="nav-link stitch-nav-link" href="/organogram/{{ $_SESSION['id'] ?? '' }}?mode=canvas" title="{{ __('nav.tree', 'Tree') }} - Explore living family tree and lineage">
                        <i class="bi bi-diagram-3-fill"></i>{{ __('nav.tree', 'Tree') }}
                    </a>
                </li>
                <li class="nav-item creativeStudioNav dropdown" id="creativeStudioNavItem">
                    <a class="nav-link stitch-nav-link" href="/creative-studio" id="creativeStudioNavTrigger" aria-label="Creative Studio" aria-haspopup="true" aria-expanded="false" data-no-family-tooltip="true">
                        <span class="creative-3d-icon-wrap" aria-hidden="true">
                            <span class="creative-3d-gem">
                                <span class="creative-3d-sheen"></span>
                                <svg class="creative-3d-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2.5L14.4 7.8L20.1 8.5L15.8 12.4L17 18.1L12 15.2L7 18.1L8.2 12.4L3.9 8.5L9.6 7.8L12 2.5Z" fill="#fef08a" stroke="#ffffff" stroke-width="1.2" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="creative-3d-spark creative-3d-spark--1"></span>
                            <span class="creative-3d-spark creative-3d-spark--2"></span>
                        </span>
                        <span>Creative Studio</span>
                    </a>
                    <div class="creative-studio-rich-tooltip shadow-lg" id="creativeStudioTooltipCard" role="menu" aria-label="Creative Studio tools">
                        <div class="cs-tooltip-header">
                            <i class="bi bi-stars"></i> Inside Creative Studio
                        </div>
                        <ul class="cs-tooltip-list">
                            <li>
                                <a href="/familyStudio" class="cs-dropdown-link" role="menuitem">
                                    <span class="cs-dropdown-icon cs-dropdown-icon--tree"><i class="bi bi-diagram-3-fill"></i></span>
                                    <span class="cs-dropdown-text">
                                        <span class="cs-dropdown-title">Tree Studio</span>
                                        <span class="cs-dropdown-desc">Build &amp; curate your family lineage</span>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="/family/living-heirloom" class="cs-dropdown-link" role="menuitem">
                                    <span class="cs-dropdown-icon cs-dropdown-icon--heirloom"><i class="bi bi-person-bounding-box"></i></span>
                                    <span class="cs-dropdown-text">
                                        <span class="cs-dropdown-title">Heirloom AI</span>
                                        <span class="cs-dropdown-desc">Living portraits, ageing &amp; synthesis</span>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="/poster-studio" class="cs-dropdown-link" role="menuitem">
                                    <span class="cs-dropdown-icon cs-dropdown-icon--flyer"><i class="bi bi-easel2-fill"></i></span>
                                    <span class="cs-dropdown-text">
                                        <span class="cs-dropdown-title">Flyer Studio</span>
                                        <span class="cs-dropdown-desc">Reunion, jubilee &amp; milestone posters</span>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="/member/festive-cards" class="cs-dropdown-link" role="menuitem">
                                    <span class="cs-dropdown-icon cs-dropdown-icon--cards"><i class="bi bi-envelope-heart-fill"></i></span>
                                    <span class="cs-dropdown-text">
                                        <span class="cs-dropdown-title">Festive Cards</span>
                                        <span class="cs-dropdown-desc">Living 3D greeting envelopes</span>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="/family-studio" class="cs-dropdown-link" role="menuitem">
                                    <span class="cs-dropdown-icon cs-dropdown-icon--archives"><i class="bi bi-film"></i></span>
                                    <span class="cs-dropdown-text">
                                        <span class="cs-dropdown-title">Living Archives</span>
                                        <span class="cs-dropdown-desc">4K Reel Studio &amp; Video Vault</span>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="/arcade" class="cs-dropdown-link" role="menuitem">
                                    <span class="cs-dropdown-icon cs-dropdown-icon--arcade"><i class="bi bi-trophy-fill"></i></span>
                                    <span class="cs-dropdown-text">
                                        <span class="cs-dropdown-title">Family Arcade</span>
                                        <span class="cs-dropdown-desc">Daily puzzles, live races &amp; Family Cup</span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                        <div class="cs-dropdown-footer">
                            <a href="/creative-studio" class="cs-dropdown-hub-link" role="menuitem">
                                <span>Open Creative Studio Hub</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </li>
                <li class="nav-item galleryNav">
                    <a class="nav-link stitch-nav-link {{ str_starts_with(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/profilepage/gallery') ? 'active' : '' }}" href="/profilepage/gallery" title="{{ __('nav.gallery', 'Gallery') }} - Browse your family photo and memory gallery">
                        <i class="bi bi-images"></i>{{ __('nav.gallery', 'Gallery') }}
                    </a>
                </li>
                <li class="nav-item allMembersNav">
                    <a class="nav-link stitch-nav-link" href="/allMembers" title="{{ __('nav.family', 'Family') }} - Browse connected family network">
                        <i class="bi bi-people-fill"></i>{{ __('nav.family', 'Family') }}
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center flex-wrap flex-lg-nowrap gap-2 navbar-actions-group flex-shrink-0">
                <!-- Omni-Search Trigger Button (Search People, Activities & Memories) -->
                <button class="btn btn-omni-search-trigger d-none d-lg-flex align-items-center gap-2.5" id="omniSearchTriggerBtn" type="button" aria-label="Search people, activities and memories" title="Search activities, memories, and family members (⌘K)" data-bs-toggle="modal" data-bs-target="#omniSearchModal">
                    <span class="omni-trigger-icon-box" aria-hidden="true">
                        <i class="bi bi-search"></i>
                    </span>
                    <span class="omni-trigger-text d-none d-xxl-inline pe-1">{{ __('nav.search_placeholder', 'Search people, activities and memories...') }}</span>
                    <span class="omni-trigger-text d-none d-xl-inline d-xxl-none pe-1">{{ __('nav.search', 'Search...') }}</span>
                    <span class="omni-trigger-text d-inline d-xl-none pe-1">{{ __('nav.search', 'Search...') }}</span>
                    <kbd class="omni-trigger-kbd d-none d-md-inline-flex align-items-center gap-0.5" aria-hidden="true">
                        <span class="omni-kbd-key">⌘</span><span class="omni-kbd-key">K</span>
                    </kbd>
                </button>

                <!-- Recommend / Invite Others Button -->
                <button class="btn btn-sm invite-3d-btn d-flex align-items-center gap-2 px-3 py-1 fw-bold text-white rounded-pill" id="btnOpenRecommendModal" data-bs-toggle="modal" data-bs-target="#inviteFamilyModal" type="button" title="Invite family relatives and friends to join your network">
                    <i class="bi bi-person-plus-fill text-warning"></i>
                    <span class="d-none d-xl-inline">{{ __('nav.invite_others', 'Invite Others') }}</span>
                    <span class="d-inline d-xl-none">{{ __('nav.invite', 'Invite') }}</span>
                </button>

                <!-- FamChat 3D Spatial Pill Button with Bell & Unread Counter (desktop; mobile uses top bar 3D orb) -->
                <div class="whisper-nav-wrapper position-relative d-none d-lg-block">
                    <a href="/kin-whispers" class="btn famchat-3d-nav-btn text-decoration-none d-inline-flex align-items-center justify-content-center position-relative" id="navbarWhispersBtn" title="{{ __('nav.whisper', 'FamChat — Private family messages, artifact reflections & voice dedications') }}" aria-label="FamChat">
                        <span class="famchat-3d-orb-wrap" aria-hidden="true">
                            <span class="famchat-3d-orb">
                                <span class="famchat-3d-sheen"></span>
                                <svg class="famchat-3d-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 3C6.48 3 2 6.82 2 11.5C2 13.68 2.98 15.66 4.6 17.16L3.4 21L7.72 19.3C9.07 19.75 10.51 20 12 20C17.52 20 22 16.18 22 11.5C22 6.82 17.52 3 12 3Z" fill="#ffffff" stroke="#78350f" stroke-width="1.2" stroke-linejoin="round"/>
                                    <path d="M12 15.4C12 15.4 8.2 13.1 8.2 10.6C8.2 9.4 9.15 8.5 10.25 8.5C10.95 8.5 11.58 8.85 12 9.45C12.42 8.85 13.05 8.5 13.75 8.5C14.85 8.5 15.8 9.4 15.8 10.6C15.8 13.1 12 15.4 12 15.4Z" fill="#e11d48"/>
                                </svg>
                            </span>
                            <span class="famchat-3d-spark famchat-3d-spark--1"></span>
                            <span class="famchat-3d-spark famchat-3d-spark--2"></span>
                        </span>
                        <span class="famchat-nav-label">{{ __('nav.whisper_short', 'FamChat') }}</span>
                        <span class="famchat-live-dot" aria-hidden="true"></span>
                        <i class="bi bi-bell-fill text-warning whisper-bell-indicator position-absolute {{ $navWhisperUnreadCount > 0 ? '' : 'd-none' }}" style="font-size: 0.68rem; top: -3px; right: 12px; filter: drop-shadow(0 1px 2px rgba(0,0,0,0.45)); pointer-events: none;" title="Unread FamChat messages"></i>
                        <span class="whisper-nav-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white {{ $navWhisperUnreadCount > 0 ? '' : 'd-none' }}" id="navbar_whisper_count" style="font-size: 0.7rem; padding: 2px 6px; font-weight: 800; border: 2px solid #ffffff; box-shadow: 0 2px 8px rgba(220,53,69,0.55); pointer-events: none;">
                            {{ $navWhisperUnreadCount > 0 ? $navWhisperUnreadCount : '' }}
                        </span>
                    </a>
                </div>

                <!-- Notification Component -->
                <div class="notification-wrapper position-relative">
                    <button class="btn btn-icon-stitch" id="notificationBtn" title="{{ __('nav.notifications', 'View alerts and family notifications') }}" aria-label="{{ __('nav.notifications', 'View alerts and family notifications') }}" aria-haspopup="true" aria-expanded="false" type="button">
                        <i class="bi bi-bell-fill" style="pointer-events: none;" aria-hidden="true"></i>
                        <span class="notification-badge" id="notification_count" x-data x-cloak x-show="$store.notifications.hasUnread" x-text="$store.notifications.display" data-server-count="{{ $navUnreadNotifCount }}" style="pointer-events: none;"></span>
                    </button>

                    <!-- Google Stitch Notification Dropdown -->
                    <div class="notification-dropdown shadow-lg" id="notificationDropdown">
                        <!-- Google Stitch Header with Bell Bubble -->
                        <div class="notif-header-stitch d-flex align-items-center gap-3">
                            <div class="notif-header-bubble position-relative">
                                <i class="bi bi-bell-fill text-primary"></i>
                                <span class="notif-header-badge" id="header_notif_count" x-data x-cloak x-show="$store.notifications.hasUnread" x-text="$store.notifications.display"></span>
                            </div>
                            <h5 class="mb-0 fw-bold notif-header-title">{{ __('nav.notifications', 'Notifications') }}</h5>
                            <button type="button" id="closeNotifDropdownBtn" class="btn btn-sm btn-link text-muted ms-auto d-lg-none d-inline-flex align-items-center justify-content-center p-0" style="min-width: 44px; min-height: 44px; text-decoration: none;" aria-label="Close notifications" onclick="document.getElementById('notificationDropdown')?.classList.remove('show')">
                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                            </button>
                        </div>

                        <!-- Content List -->
                        <div class="notification-content list-group notification_tab" id="notification_tab">
                            <!-- Loaded dynamically -->
                        </div>

                        <!-- Google Stitch Full-Width Pill Footer -->
                        <div class="notif-footer-stitch">
                            <button class="btn-mark-all-read-stitch" id="markAllRead" type="button" title="Mark all notifications as read">
                                {{ __('nav.mark_all_read', 'Mark All as Read') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Language Selector Dropdown -->
                <div class="dropdown">
                    @php
                        $activeLocale = \App\classes\I18n::getLocale();
                        $supportedLocales = \App\classes\I18n::getSupportedLocales();
                        $currLocale = $supportedLocales[$activeLocale] ?? $supportedLocales['en'];
                    @endphp
                    <button class="btn btn-sm btn-raised-nav lang-btn d-flex align-items-center gap-2 px-3 py-1 rounded-pill" type="button" id="langMenu" data-bs-toggle="dropdown" data-bs-display="dynamic" aria-expanded="false" aria-label="{{ __('nav.change_language', 'Change Language / Select locale') }}" title="{{ __('nav.change_language', 'Change Language / Select locale') }}">
                        <span style="font-size: 1.1rem;">{{ $currLocale['flag'] }}</span>
                        <span class="d-none d-md-inline fw-semibold text-uppercase" style="font-size: 0.78rem;">{{ $activeLocale }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-lg-end shadow-sm border-0 py-1" aria-labelledby="langMenu" style="border-radius: 12px; min-width: 140px;">
                        @foreach ($supportedLocales as $locKey => $locMeta)
                            <li>
                                <a class="dropdown-item py-1.5 px-3 d-flex align-items-center gap-2 {{ $locKey === $activeLocale ? 'active fw-bold' : '' }}" href="/locale/{{ $locKey }}">
                                    <span>{{ $locMeta['flag'] }}</span>
                                    <span>{{ $locMeta['native'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Theme Mode Toggle Icon -->
                <button type="button" class="btn btn-icon-stitch" id="darkModeToggle" x-data :aria-pressed="$store.theme.isDark ? 'true' : 'false'" @click="$store.theme.toggle()" aria-label="{{ __('nav.theme_mode', 'Theme Mode') }}" aria-pressed="false" title="{{ __('nav.theme_mode', 'Toggle dark/light theme') }}">
                    <i class="bi bi-moon-stars-fill" :class="{ 'bi-sun-fill text-warning': $store.theme.isDark, 'bi-moon-stars-fill': !$store.theme.isDark }" id="themeToggleIcon" aria-hidden="true"></i>
                </button>

                <!-- Account Settings Direct Link (Avatar Capsule) -->
                @php
                    $navFullName = trim((string)($_SESSION['fullName'] ?? 'Account'));
                    $navNameParts = preg_split('/\s+/', $navFullName, -1, PREG_SPLIT_NO_EMPTY) ?: ['A'];
                    $navInitials = mb_strtoupper(mb_substr($navNameParts[0], 0, 1) . (count($navNameParts) > 1 ? mb_substr(end($navNameParts), 0, 1) : ''));
                    $navRawImg = trim((string)($_SESSION['img'] ?? ($_SESSION['profilePics'] ?? '')));
                    $navHasPhoto = $navRawImg !== '' && !str_contains($navRawImg, 'avatarM.png') && !str_contains($navRawImg, 'avatarF.png') && !str_contains($navRawImg, 'default-avatar.');
                    $navImgSrc = $navHasPhoto ? (str_starts_with($navRawImg, '/') ? $navRawImg : '/resources/images/profile/' . rawurlencode($navRawImg)) : '';
                @endphp
                <a href="/accountSetting" class="btn btn-user-stitch btn-user-compact d-inline-flex align-items-center justify-content-center position-relative text-decoration-none" id="userMenu" aria-label="{{ $navFullName }} — {{ __('nav.settings', 'Settings') }}" title="{{ $navFullName }} — {{ __('nav.settings', 'Account Settings & Preferences') }}">
                    @if ($navHasPhoto)
                        <img src="{{ $navImgSrc }}" alt="" class="user-avatar-photo flex-shrink-0" width="36" height="36" onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'user-avatar-initials flex-shrink-0',textContent:'{{ $navInitials }}'}))">
                    @else
                        <span class="user-avatar-initials flex-shrink-0" aria-hidden="true">{{ $navInitials }}</span>
                    @endif
                    <span class="user-settings-gear-badge" aria-hidden="true"><i class="bi bi-gear-fill"></i></span>
                </a>

                <!-- Log Out Standalone Icon -->
                <a href="/signout" class="btn btn-icon-stitch btn-logout-stitch text-decoration-none d-inline-flex align-items-center justify-content-center" id="navbarLogoutBtn" aria-label="{{ __('nav.logout', 'Log Out') }}" title="{{ __('nav.logout', 'Log Out') }}">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</nav>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
(function() {
    'use strict';
    var navItem = document.getElementById('creativeStudioNavItem');
    var trigger = document.getElementById('creativeStudioNavTrigger');
    var menu = document.getElementById('creativeStudioTooltipCard');
    if (!navItem || !trigger || !menu) return;

    var closeTimer = null;

    function openMenu() {
        if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
        }
        navItem.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
    }

    function scheduleCloseMenu() {
        if (closeTimer) clearTimeout(closeTimer);
        closeTimer = setTimeout(function() {
            navItem.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            closeTimer = null;
        }, 220);
    }

    function immediateCloseMenu() {
        if (closeTimer) {
            clearTimeout(closeTimer);
            closeTimer = null;
        }
        navItem.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
    }

    // Pointer events with debounce
    navItem.addEventListener('mouseenter', openMenu);
    navItem.addEventListener('mouseleave', scheduleCloseMenu);

    // Focus management for keyboard accessibility
    navItem.addEventListener('focusin', openMenu);
    navItem.addEventListener('focusout', function(e) {
        if (!navItem.contains(e.relatedTarget)) {
            immediateCloseMenu();
        }
    });

    // Touch & Click toggle for mobile/tablet & deliberate clicks
    trigger.addEventListener('click', function(e) {
        var isMobile = window.innerWidth < 992;
        if (isMobile) {
            if (!navItem.classList.contains('is-open')) {
                e.preventDefault();
                openMenu();
            }
        } else {
            // On desktop, clicking while open allows navigation to hub, or clicking while closed opens dropdown
            if (!navItem.classList.contains('is-open')) {
                openMenu();
            }
        }
    });

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!navItem.contains(e.target)) {
            immediateCloseMenu();
        }
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && navItem.classList.contains('is-open')) {
            immediateCloseMenu();
            trigger.focus();
        }
    });

    // Close when any sub-link is clicked
    var subLinks = menu.querySelectorAll('a');
    for (var i = 0; i < subLinks.length; i++) {
        subLinks[i].addEventListener('click', function() {
            immediateCloseMenu();
        });
    }
})();

(function() {
    'use strict';
    var mainNavbar = document.getElementById('mainNavbar');
    var navCollapse = document.getElementById('navbarContent');
    if (!mainNavbar || !navCollapse) return;

    function closeMobileNav() {
        if (!navCollapse.classList.contains('show')) return;
        if (window.bootstrap && window.bootstrap.Collapse) {
            window.bootstrap.Collapse.getOrCreateInstance(navCollapse, { toggle: false }).hide();
        } else {
            navCollapse.classList.remove('show');
            var toggler = mainNavbar.querySelector('.navbar-toggler');
            if (toggler) toggler.setAttribute('aria-expanded', 'false');
        }
    }

    document.addEventListener('click', function(e) {
        if (window.innerWidth >= 992) return;
        if (navCollapse.classList.contains('show') && !mainNavbar.contains(e.target)) {
            closeMobileNav();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && navCollapse.classList.contains('show')) {
            closeMobileNav();
        }
    });
})();

(function() {
    'use strict';
    // Instant t=0ms Notification Bell & Modal Fallback Delegation
    // Guarantees zero dead clicks before module bundles or CDN Bootstrap finish hydrating.
    var notifBtn = document.getElementById('notificationBtn');
    var notifDropdown = document.getElementById('notificationDropdown');
    if (notifBtn && notifDropdown && !notifBtn.hasAttribute('data-notif-instant-bound')) {
        notifBtn.setAttribute('data-notif-instant-bound', 'true');
        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var now = Date.now();
            var lastToggle = parseInt(notifDropdown.getAttribute('data-last-toggle-ts') || '0', 10);
            if (now - lastToggle < 40) return;
            notifDropdown.setAttribute('data-last-toggle-ts', String(now));
            var isShown = notifDropdown.classList.toggle('show');
            notifBtn.setAttribute('aria-expanded', isShown ? 'true' : 'false');
        });

        document.addEventListener('click', function(e) {
            if (!notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
                if (notifDropdown.classList.contains('show')) {
                    notifDropdown.classList.remove('show');
                    notifBtn.setAttribute('aria-expanded', 'false');
                }
            }
        });

        notifDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
})();
</script>
