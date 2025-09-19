    <!-- Navigation Bar -->
      <style>
    
        
        .notification-container {
            display: flex;
            justify-content: center;
            margin: 40px 0;
        }
        
        /* Fixed notification styles */
        .notification-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .notification-btn {
            position: relative;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .notification-btn:hover {
            background-color: #e9ecef;
            transform: scale(1.05);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 350px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            z-index: 1000;
            margin-top: 10px;
            display: none;
            overflow: hidden;
        }
        
        .notification-dropdown.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .notification-header {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-content {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .notification-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f1f1;
            display: flex;
            align-items: flex-start;
            transition: background-color 0.2s;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item.unread {
            background-color: #e8f4ff;
        }
        
        .notification-icon {
            margin-right: 12px;
            font-size: 1.2rem;
            color: #0d6efd;
            flex-shrink: 0;
        }
        
        .notification-text {
            flex-grow: 1;
            font-size: 0.9rem;
        }
        
        .notification-time {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 4px;
        }
        
        .notification-actions {
            padding: 10px 15px;
            text-align: center;
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        
        .code-sample {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            overflow-x: auto;
        }
        
        .problem-list {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
        }
        
        .problem-list h4 {
            color: #856404;
            margin-top: 0;
        }
    </style>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="bi bi-people-fill me-2"></i>SocialConnect
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
                {{-- <form class="d-flex ms-auto me-3 my-2 my-lg-0" role="search">
                    <input class="form-control me-2 rounded-pill" type="search" placeholder="Search"
                        aria-label="Search" id="searchFamily" name="searchFamily">
                </form> --}}
                <div class="d-flex align-items-center">
                    {{-- <div class="notification-container"> --}}
                        <!-- Fixed Notification Component -->
                        <div class="notification-wrapper">
                            <button class="btn btn-outline-secondary notification-btn" id="notificationBtn">
                                <i class="bi bi-bell"></i>
                                <span class="notification-badge" id="notificationCount">3</span>
                            </button>
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-header">
                                    <span>Notifications</span>
                                    <span class="badge bg-primary rounded-pill">3</span>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-item unread">
                                        <div class="notification-icon"><i class="bi bi-person-plus"></i></div>
                                        <div class="notification-text">
                                            <strong>New connection request</strong>
                                            <div class="notification-time">10 minutes ago</div>
                                        </div>
                                    </div>
                                    <div class="notification-item unread">
                                        <div class="notification-icon"><i class="bi bi-chat"></i></div>
                                        <div class="notification-text">
                                            <strong>You have a new message</strong>
                                            <div class="notification-time">45 minutes ago</div>
                                        </div>
                                    </div>
                                    <div class="notification-item">
                                        <div class="notification-icon"><i class="bi bi-heart"></i></div>
                                        <div class="notification-text">
                                            <strong>Someone liked your post</strong>
                                            <div class="notification-time">2 hours ago</div>
                                        </div>
                                    </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const markAllReadBtn = document.getElementById('markAllRead');
            const notificationCount = document.getElementById('notificationCount');

            // Toggle dropdown visibility
            notificationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('show');
            });

            // Mark all as read functionality
            markAllReadBtn.addEventListener('click', function() {
                const unreadItems = document.querySelectorAll('.notification-item.unread');
                unreadItems.forEach(item => {
                    item.classList.remove('unread');
                });

                // Update notification count
                notificationCount.textContent = '0';
                notificationCount.style.display = 'none';
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.remove('show');
                }
            });

            // Prevent dropdown from closing when clicking inside it
            notificationDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
