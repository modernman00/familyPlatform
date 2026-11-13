<!-- Google Stitch Navigation Bar -->
@php
    $navUnreadNotifCount = !empty($_SESSION['id']) ? \App\controller\NotificationController::getCalculatedUnreadCount((string)$_SESSION['id']) : 0;
    $navWhisperUnreadCount = !empty($_SESSION['id'])
        ? \App\services\KinWhisperService::getUnreadWhisperCount((string)$_SESSION['id'])
        : 0;
@endphp
<link rel="stylesheet" href="/public/css/navbar.css?v={{ time() }}">
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

        <!-- Quick mobile whisper icon with bell & counter -->
        <a href="/kin-whispers" class="btn btn-icon-stitch d-lg-none me-2 position-relative text-decoration-none" id="mobileWhispersBtn" aria-label="{{ __('nav.whisper', 'Whisper — Private family messages') }}" title="{{ __('nav.whisper', 'Whisper — Private family messages & audio dedications') }}">
            <i class="bi bi-chat-dots-fill" style="color: #a31621; font-size: 1.05rem;"></i>
            <i class="bi bi-bell-fill text-warning whisper-bell-indicator position-absolute {{ $navWhisperUnreadCount > 0 ? '' : 'd-none' }}" style="font-size: 0.55rem; top: 3px; right: 3px;" title="Unread whispers"></i>
            <span class="whisper-nav-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white {{ $navWhisperUnreadCount > 0 ? '' : 'd-none' }}" id="mobile_whisper_count" style="font-size: 0.6rem; padding: 2px 4px; pointer-events: none;">
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
                <li class="nav-item creativeStudioNav">
                    <a class="nav-link stitch-nav-link" href="/creative-studio" title="Creative Studio — Tree Studio, Flyers &amp; Posters, Festive Cards, and Living Archives" data-tooltip="Creative Studio — Tree Studio, Flyers &amp; Posters, Festive Cards, and Living Archives" aria-describedby="creativeStudioTooltipCard">
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
                    <div class="creative-studio-rich-tooltip" id="creativeStudioTooltipCard" role="tooltip">
                        <div class="cs-tooltip-header">
                            <i class="bi bi-stars"></i> Inside Creative Studio
                        </div>
                        <ul class="cs-tooltip-list">
                            <li class="cs-tooltip-item">
                                <i class="bi bi-diagram-3-fill text-info"></i>
                                <span><strong>Tree Studio</strong> — Build &amp; curate your family lineage</span>
                            </li>
                            <li class="cs-tooltip-item">
                                <i class="bi bi-easel2-fill text-warning"></i>
                                <span><strong>Flyer Studio</strong> — Reunion, jubilee &amp; milestone posters</span>
                            </li>
                            <li class="cs-tooltip-item">
                                <i class="bi bi-envelope-heart-fill text-danger"></i>
                                <span><strong>Festive Cards</strong> — Living 3D greeting envelopes</span>
                            </li>
                            <li class="cs-tooltip-item">
                                <i class="bi bi-film text-light"></i>
                                <span><strong>Living Archives</strong> — 4K Reel Studio &amp; Video Vault</span>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item reelsNav">
                    <a class="nav-link stitch-nav-link" href="/reels" title="{{ __('nav.reels', 'Reels') }} - Watch captivating family video moments">
                        <i class="bi bi-camera-reels-fill"></i>{{ __('nav.reels', 'Reels') }}
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
                <button class="btn btn-sm invite-3d-btn d-flex align-items-center gap-2 px-3 py-1 fw-bold text-white rounded-pill" id="btnOpenRecommendModal" data-bs-toggle="modal" data-bs-target="#recommendFriendsModal" type="button" title="Invite family relatives and friends to join your network">
                    <i class="bi bi-person-plus-fill text-warning"></i>
                    <span class="d-none d-xl-inline">{{ __('nav.invite_others', 'Invite Others') }}</span>
                    <span class="d-inline d-xl-none">{{ __('nav.invite', 'Invite') }}</span>
                </button>

                <!-- Kin Whispers Direct Action Button with Bell & Unread Counter -->
                <div class="whisper-nav-wrapper position-relative">
                    <a href="/kin-whispers" class="btn btn-icon-stitch text-decoration-none d-inline-flex align-items-center justify-content-center position-relative" id="navbarWhispersBtn" title="{{ __('nav.whisper', 'Whisper — Private family messages, artifact reflections & voice dedications') }}" aria-label="Whispers">
                        <i class="bi bi-chat-dots-fill" style="color: #a31621; font-size: 1.15rem;"></i>
                        <i class="bi bi-bell-fill text-warning whisper-bell-indicator position-absolute {{ $navWhisperUnreadCount > 0 ? '' : 'd-none' }}" style="font-size: 0.65rem; top: 4px; right: 4px; filter: drop-shadow(0 1px 2px rgba(0,0,0,0.35));" title="Unread whispers"></i>
                    </a>
                    <span class="whisper-nav-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white {{ $navWhisperUnreadCount > 0 ? '' : 'd-none' }}" id="navbar_whisper_count" style="font-size: 0.68rem; padding: 2px 5px; font-weight: 700; box-shadow: 0 2px 6px rgba(220,53,69,0.45); pointer-events: none;">
                        {{ $navWhisperUnreadCount > 0 ? $navWhisperUnreadCount : '' }}
                    </span>
                </div>

                <!-- Notification Component -->
                <div class="notification-wrapper position-relative">
                    <button class="btn btn-icon-stitch" id="notificationBtn" title="{{ __('nav.notifications', 'View alerts and family notifications') }}" type="button">
                        <i class="bi bi-bell-fill"></i>
                    </button>
                    <span class="notification-badge" id="notification_count" x-data x-cloak x-show="$store.notifications.hasUnread" x-text="$store.notifications.display" data-server-count="{{ $navUnreadNotifCount }}"></span>

                    <!-- Google Stitch Notification Dropdown -->
                    <div class="notification-dropdown shadow-lg" id="notificationDropdown">
                        <!-- Google Stitch Header with Bell Bubble -->
                        <div class="notif-header-stitch d-flex align-items-center gap-3">
                            <div class="notif-header-bubble position-relative">
                                <i class="bi bi-bell-fill text-primary"></i>
                                <span class="notif-header-badge" id="header_notif_count" x-data x-cloak x-show="$store.notifications.hasUnread" x-text="$store.notifications.display"></span>
                            </div>
                            <h5 class="mb-0 fw-bold notif-header-title">{{ __('nav.notifications', 'Notifications') }}</h5>
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

                <!-- User Profile Dropdown (Refined Capsule with Avatar Monogram) -->
                <div class="dropdown">
                    @php
                        $navFullName = trim((string)($_SESSION['fullName'] ?? 'Account'));
                        $navNameParts = preg_split('/\s+/', $navFullName, -1, PREG_SPLIT_NO_EMPTY) ?: ['A'];
                        $navInitials = mb_strtoupper(mb_substr($navNameParts[0], 0, 1) . (count($navNameParts) > 1 ? mb_substr(end($navNameParts), 0, 1) : ''));
                        $navRawImg = trim((string)($_SESSION['img'] ?? ($_SESSION['profilePics'] ?? '')));
                        $navHasPhoto = $navRawImg !== '' && !str_contains($navRawImg, 'avatarM.png') && !str_contains($navRawImg, 'avatarF.png');
                        $navImgSrc = $navHasPhoto ? (str_starts_with($navRawImg, '/') ? $navRawImg : '/resources/images/profile/' . rawurlencode($navRawImg)) : '';
                    @endphp
                    <button class="btn btn-user-stitch btn-user-compact dropdown-toggle d-flex align-items-center" type="button" id="userMenu" data-bs-toggle="dropdown" data-bs-display="dynamic" aria-expanded="false" aria-label="{{ $navFullName }} — account menu" title="{{ $navFullName }} — account profile and preferences">
                        @if ($navHasPhoto)
                            <img src="{{ $navImgSrc }}" alt="" class="user-avatar-photo flex-shrink-0" width="36" height="36" onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'user-avatar-initials flex-shrink-0',textContent:'{{ $navInitials }}'}))">
                        @else
                            <span class="user-avatar-initials flex-shrink-0" aria-hidden="true">{{ $navInitials }}</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-lg-end shadow-lg border-0 py-2 stitch-user-dropdown" aria-labelledby="userMenu" style="border-radius: 16px; min-width: 200px;">
                        <li>
                            <button type="button" class="dropdown-item d-lg-none py-2 px-3 fw-medium text-primary text-start w-100 border-0 bg-transparent" data-bs-toggle="modal" data-bs-target="#recommendFriendsModal" title="Invite family relatives and friends to join your family network">
                                <i class="bi bi-person-plus-fill me-2 text-warning"></i> {{ __('nav.invite_others', 'Invite Others') }}
                            </button>
                        </li>
                        <li class="profileNav">
                            <a class="dropdown-item py-2 px-3 fw-medium" href="/profile" data-bs-toggle="modal" data-bs-target="#editProfileModal" title="Edit your personal profile information">
                                <i class="bi bi-pencil-square me-2 text-primary"></i> {{ __('nav.edit_profile', 'Edit Profile') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-medium d-flex align-items-center gap-2" href="/creative-studio" title="Creative Studio — Tree Studio, Flyers &amp; Posters, Festive Cards, and Living Archives">
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
                        </li>
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-medium" href="/familyStudio" title="{{ __('nav.studio', 'Tree Studio') }} — Curate your family lineage and generations">
                                <i class="bi bi-diagram-3-fill me-2 text-primary"></i> {{ __('nav.studio', 'Tree Studio') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-medium" href="/kinship-calculator" title="Calculate genealogical relationship degrees and shared DNA">
                                <i class="bi bi-calculator me-2 text-danger"></i> Kinship Calculator
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-medium" href="/accountSetting" title="Manage account settings and security preferences">
                                <i class="bi bi-gear-fill me-2 text-secondary"></i> {{ __('nav.settings', 'Settings') }}
                            </a>
                        </li>
                        <li id="pwaAmbientInstallLi" style="display: none;">
                            <button type="button" class="dropdown-item py-2 px-3 fw-medium text-success d-flex align-items-center justify-content-between pwa-ambient-install-trigger text-start w-100 border-0 bg-transparent" id="pwaAmbientInstallBtn" title="Install FamilyPlatform app on your device for fast access & alerts">
                                <span><i class="bi bi-download me-2 text-success"></i> Install App</span>
                                <span class="badge bg-success-subtle text-success" style="font-size: 0.72rem;">Native App</span>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item py-2 px-3 fw-medium d-flex align-items-center justify-content-between text-start w-100 border-0 bg-transparent" id="darkModeToggle" title="Toggle between dark and light appearance modes">
                                <span><i class="bi bi-moon-stars-fill me-2 text-warning"></i> {{ __('nav.theme_mode', 'Theme Mode') }}</span>
                                <span class="badge bg-secondary-subtle text-secondary" id="themeStatusBadge" style="font-size: 0.72rem;">Toggle</span>
                            </button>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-medium text-danger" href="/signout" title="Sign out safely from your family account">
                                <i class="bi bi-box-arrow-right me-2"></i> {{ __('nav.logout', 'Logout') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
