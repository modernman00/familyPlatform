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

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-1 ms-lg-3">
                <li class="nav-item profilePageNav">
                    <a class="nav-link stitch-nav-link" href="/profilePage">
                        <i class="bi bi-person-fill me-1"></i> My Page
                    </a>
                </li>
                <li class="nav-item familyTreeNav">
                    <a class="nav-link stitch-nav-link" href="/organogram/{{ $_SESSION['id'] ?? '' }}?mode=canvas">
                        <i class="bi bi-diagram-3-fill me-1"></i> Tree
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link stitch-nav-link" href="/familyStudio">
                        <i class="bi bi-stars me-1"></i> Studio
                    </a>
                </li>
                <li class="nav-item familyJournalNav">
                    <a class="nav-link stitch-nav-link" href="/familyJournal">
                        <i class="bi bi-journal-bookmark-fill me-1"></i> Journal
                    </a>
                </li>
                <li class="nav-item reelsNav">
                    <a class="nav-link stitch-nav-link" href="/reels">
                        <i class="bi bi-camera-reels-fill me-1"></i> Reels
                    </a>
                </li>
                <li class="nav-item allMembersNav">
                    <a class="nav-link stitch-nav-link" href="/allMembers">
                        <i class="bi bi-people-fill me-1"></i> Directory
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <!-- Recommend / Invite Others Button -->
                <button class="btn btn-sm d-flex align-items-center gap-1 px-3 py-1 fw-bold text-white rounded-pill shadow-sm" id="btnOpenRecommendModal" data-bs-toggle="modal" data-bs-target="#recommendFriendsModal" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); font-size: 0.85rem;" type="button">
                    <i class="bi bi-person-plus-fill text-warning"></i> <span>Invite Others</span>
                </button>

                <!-- Notification Component -->
                <div class="notification-wrapper position-relative">
                    <button class="btn btn-icon-stitch" id="notificationBtn" title="Notifications" type="button">
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
                            <h5 class="mb-0 fw-bold notif-header-title">Notifications</h5>
                        </div>

                        <!-- Content List -->
                        <div class="notification-content list-group notification_tab" id="notification_tab">
                            <!-- Loaded dynamically -->
                        </div>

                        <!-- Google Stitch Full-Width Pill Footer -->
                        <div class="notif-footer-stitch">
                            <button class="btn-mark-all-read-stitch" id="markAllRead" type="button">
                                Mark All as Read
                            </button>
                        </div>
                    </div>
                </div>

                <!-- User Profile Dropdown (Refined Capsule with Avatar Monogram) -->
                <div class="dropdown">
                    <button class="btn btn-user-stitch dropdown-toggle d-flex align-items-center gap-2" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="user-avatar-initials">{{ strtoupper(substr($_SESSION['fullName'] ?? 'A', 0, 1)) }}</span>
                        <span class="fw-semibold text-truncate" style="max-width: 130px;">{{ ucwords(strtolower($_SESSION['fullName'] ?? 'Account')) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2" aria-labelledby="userMenu" style="border-radius: 16px; min-width: 200px;">
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-medium text-primary" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#recommendFriendsModal">
                                <i class="bi bi-person-plus-fill me-2 text-warning"></i> Invite Others
                            </a>
                        </li>
                        <li class="profileNav">
                            <a class="dropdown-item py-2 px-3 fw-medium" href="/profile" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-medium" href="/accountSetting">
                                <i class="bi bi-gear-fill me-2 text-secondary"></i> Settings
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-medium d-flex align-items-center justify-content-between" href="javascript:void(0)" id="darkModeToggle" role="button">
                                <span><i class="bi bi-moon-stars-fill me-2 text-warning"></i> Theme Mode</span>
                                <span class="badge bg-secondary-subtle text-secondary" id="themeStatusBadge" style="font-size: 0.72rem;">Toggle</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 px-3 fw-medium text-danger" href="/signout">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
