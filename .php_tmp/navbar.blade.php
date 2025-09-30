    <!-- Navigation Bar -->

    <link rel="stylesheet" href="public/css/navbar.css">
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-primary" href="#">
                 <img src="{{ $_ENV['IMG_CONTRACT'] }}" alt="logo" class="nav-logo"> {{ $_ENV['APP_NAME'] }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/profilePage">My Page</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/organogram?id={{ $data['id'] }}">Family Tree</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/allMembers">All members</a>
                    </li>
                </ul>
    
                <div class="d-flex align-items-center gap-2">
                    {{-- <div class="notification-container"> --}}
                        <!-- Fixed Notification Component -->
                        <div class="notification-wrapper">
                            <button class="btn btn-outline-secondary notification-btn" id="notificationBtn">
                                <i class="bi bi-bell"></i>
                                <span class="notification-badge" id="notification_count"></span>
                            </button>

                            {{-- NOTIFICATION DROPDOWN --}}
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-header">
                                    <span>Notifications</span>
                                  
                                </div>
                                <div class="notification-content list-group  notification_tab" id="notification_tab">

                                    {{--  Notifications will be dynamically loaded here --}}

                                </div>
                                <div class="notification-actions">
                                    <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                                    <button class="btn btn-sm btn-outline-secondary" id="markAllRead">Mark All as
                                        Read</button>
                                </div>
                            </div>
                        </div>
                    {{-- </div> --}}
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="userMenu"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> {{ $data['fullName'] }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="/profile" data-bs-toggle="modal"
                                    data-bs-target="#editProfileModal">Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="/setting?id={{ $data['id'] }}">Settings</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>

                            <li><a class="dropdown-item" href="/signout">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
