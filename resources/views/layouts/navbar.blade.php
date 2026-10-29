<!-- Google Stitch Navigation Bar -->
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
        <button class="btn btn-icon-stitch d-lg-none ms-auto me-2" type="button" data-bs-toggle="modal" data-bs-target="#omniSearchModal" aria-label="Search activities, memories, and family members" title="Search activities, memories, and family members">
            <i class="bi bi-search"></i>
        </button>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation" title="Toggle navigation menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 ms-lg-3">
                <li class="nav-item profilePageNav">
                    <a class="nav-link stitch-nav-link" href="/profilePage" title="{{ __('nav.my_page', 'My Page') }} - View and manage your family profile">
                        <i class="bi bi-person-fill me-1"></i> {{ __('nav.my_page', 'My Page') }}
                    </a>
                </li>
                <li class="nav-item familyTreeNav">
                    <a class="nav-link stitch-nav-link" href="/organogram/{{ $_SESSION['id'] ?? '' }}?mode=canvas" title="{{ __('nav.tree', 'Tree') }} - Explore living family tree and lineage">
                        <i class="bi bi-diagram-3-fill me-1"></i> {{ __('nav.tree', 'Tree') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link stitch-nav-link" href="/familyStudio" title="{{ __('nav.studio', 'Studio') }} - Family reels and creative celebrations">
                        <i class="bi bi-stars me-1"></i> {{ __('nav.studio', 'Studio') }}
                    </a>
                </li>
                <li class="nav-item familyJournalNav">
                    <a class="nav-link stitch-nav-link" href="/familyJournal" title="{{ __('nav.journal', 'Journal') }} - Family memoirs and chronicles">
                        <i class="bi bi-journal-bookmark-fill me-1"></i> {{ __('nav.journal', 'Journal') }}
                    </a>
                </li>
                <li class="nav-item reelsNav">
                    <a class="nav-link stitch-nav-link" href="/reels" title="{{ __('nav.reels', 'Reels') }} - Watch captivating family video moments">
                        <i class="bi bi-camera-reels-fill me-1"></i> {{ __('nav.reels', 'Reels') }}
                    </a>
                </li>
                <li class="nav-item allMembersNav">
                    <a class="nav-link stitch-nav-link" href="/allMembers" title="{{ __('nav.directory', 'Directory') }} - Browse connected family directory">
                        <i class="bi bi-people-fill me-1"></i> {{ __('nav.directory', 'Directory') }}
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center flex-wrap flex-lg-nowrap gap-2 navbar-actions-group">
                <!-- Omni-Search Trigger Button (Search Data, Activities & Functions) -->
                <button class="btn btn-omni-search-trigger d-none d-lg-flex align-items-center gap-2" id="omniSearchTriggerBtn" type="button" aria-label="Search data, activities, functions" title="Search activities, memories, and family members (⌘K)" data-bs-toggle="modal" data-bs-target="#omniSearchModal">
                    <i class="bi bi-search text-primary"></i>
                    <span class="d-none d-xl-inline text-muted small pe-2">{{ __('nav.search_placeholder', 'Search data, activities, functions...') }}</span>
                    <span class="d-inline d-xl-none text-muted small pe-1">{{ __('nav.search', 'Search...') }}</span>
                    <kbd class="d-none d-md-inline-block text-muted px-1.5 py-0.5 rounded border border-secondary-subtle" style="font-size: 0.68rem; background: rgba(0,0,0,0.04);">⌘K</kbd>
                </button>

                <!-- Recommend / Invite Others Button -->
                <button class="btn btn-sm d-flex align-items-center gap-1 px-3 py-1 fw-bold text-white rounded-pill shadow-sm" id="btnOpenRecommendModal" data-bs-toggle="modal" data-bs-target="#recommendFriendsModal" style="background: linear-gradient(135deg, #b8202c 0%, #a31621 100%); font-size: 0.85rem;" type="button" title="Invite family relatives and friends to join your network">
                    <i class="bi bi-person-plus-fill text-warning"></i> <span>{{ __('nav.invite_others', 'Invite Others') }}</span>
                </button>

                <!-- Notification Component -->
                <div class="notification-wrapper position-relative">
                    <button class="btn btn-icon-stitch" id="notificationBtn" title="{{ __('nav.notifications', 'View alerts and family notifications') }}" type="button">
                        <i class="bi bi-bell-fill"></i>
                    </button>
                    <span class="notification-badge" id="notification_count"></span>

                    <!-- Google Stitch Notification Dropdown -->
                    <div class="notification-dropdown shadow-lg" id="notificationDropdown">
                        <!-- Google Stitch Header with Bell Bubble -->
                        <div class="notif-header-stitch d-flex align-items-center gap-3">
                            <div class="notif-header-bubble position-relative">
                                <i class="bi bi-bell-fill text-primary"></i>
                                <span class="notif-header-badge" id="header_notif_count"></span>
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
                    <button class="btn btn-sm d-flex align-items-center gap-1 px-2 py-1 rounded-pill border" type="button" id="langMenu" data-bs-toggle="dropdown" data-bs-display="dynamic" aria-expanded="false" style="background: var(--card-bg, #fff); font-size: 0.85rem;" title="{{ __('nav.change_language', 'Change Language / Select locale') }}">
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
                    <button class="btn btn-user-stitch dropdown-toggle d-flex align-items-center gap-2" type="button" id="userMenu" data-bs-toggle="dropdown" data-bs-display="dynamic" aria-expanded="false" title="Account profile and preferences menu">
                        <span class="user-avatar-initials">{{ strtoupper(substr($_SESSION['fullName'] ?? 'A', 0, 1)) }}</span>
                        <span class="fw-semibold text-truncate" style="max-width: 130px;">{{ ucwords(strtolower($_SESSION['fullName'] ?? 'Account')) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-lg-end shadow-lg border-0 py-2 stitch-user-dropdown" aria-labelledby="userMenu" style="border-radius: 16px; min-width: 200px;">
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-medium text-primary" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#recommendFriendsModal" title="Invite family relatives and friends to join your family network">
                                <i class="bi bi-person-plus-fill me-2 text-warning"></i> {{ __('nav.invite_others', 'Invite Others') }}
                            </a>
                        </li>
                        <li class="profileNav">
                            <a class="dropdown-item py-2 px-3 fw-medium" href="/profile" data-bs-toggle="modal" data-bs-target="#editProfileModal" title="Edit your personal profile information">
                                <i class="bi bi-pencil-square me-2 text-primary"></i> {{ __('nav.edit_profile', 'Edit Profile') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-medium" href="/accountSetting" title="Manage account settings and security preferences">
                                <i class="bi bi-gear-fill me-2 text-secondary"></i> {{ __('nav.settings', 'Settings') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-medium d-flex align-items-center justify-content-between" href="javascript:void(0)" id="darkModeToggle" role="button" title="Toggle between dark and light appearance modes">
                                <span><i class="bi bi-moon-stars-fill me-2 text-warning"></i> {{ __('nav.theme_mode', 'Theme Mode') }}</span>
                                <span class="badge bg-secondary-subtle text-secondary" id="themeStatusBadge" style="font-size: 0.72rem;">Toggle</span>
                            </a>
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
