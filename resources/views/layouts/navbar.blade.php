<!-- Google Stitch Navigation Bar -->
<link rel="stylesheet" href="/public/css/navbar.css?v={{ time() }}">
<nav class="navbar navbar-expand-lg sticky-top brand-navbar-stitch" id="mainNavbar">
    <div class="container-fluid px-3 px-lg-4">
        <!-- Brand Logo & Typography -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="/profilePage" style="text-decoration: none;">
            <div class="brand-logo-icon-wrapper">
                <svg width="30" height="30" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="32" height="32" rx="9" fill="url(#brandGradientMain)" />
                    <!-- Interlocking Heritage Canopy & Home -->
                    <path d="M16 6.5L24 13.5V25C24 25.5523 23.5523 26 23 26H9C8.44772 26 8 25.5523 8 25V13.5L16 6.5Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M13 26V18.5C13 18.2239 13.2239 18 13.5 18H18.5C18.7761 18 19 18.2239 19 18.5V26" stroke="white" stroke-width="1.8"/>
                    <!-- Gold Kinship Spark -->
                    <circle cx="16" cy="12" r="2.2" fill="#fbbf24"/>
                    <defs>
                        <linearGradient id="brandGradientMain" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#6366f1" />
                            <stop offset="0.5" stop-color="#4f46e5" />
                            <stop offset="1" stop-color="#312e81" />
                        </linearGradient>
                    </defs>
                </svg>
            </div>
            <div class="brand-text-container">
                <span class="brand-name-family">Family</span><span class="brand-name-platform">Platform</span>
            </div>
        </a>

        <button class="navbar-toggler border-0 shadow-none p-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
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
                    <a class="nav-link stitch-nav-link" href="/organogram/{{ $_SESSION['id'] ?? '' }}">
                        <i class="bi bi-diagram-3-fill me-1"></i> Family Tree
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link stitch-nav-link" href="/familyStudio">
                        <i class="bi bi-stars text-warning me-1"></i> Family Studio
                    </a>
                </li>
                <li class="nav-item reelsNav">
                    <a class="nav-link stitch-nav-link" href="/reels">
                        <i class="bi bi-camera-reels-fill text-danger me-1"></i> Reels
                    </a>
                </li>
                <li class="nav-item allMembersNav">
                    <a class="nav-link stitch-nav-link" href="/allMembers">
                        <i class="bi bi-people-fill me-1"></i> Directory
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2">
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

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-user-stitch dropdown-toggle d-flex align-items-center gap-2" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span class="fw-semibold text-truncate" style="max-width: 140px;">{{ ucwords(strtolower($_SESSION['fullName'] ?? 'Account')) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2" aria-labelledby="userMenu" style="border-radius: 16px; min-width: 200px;">
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
