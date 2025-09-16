    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <i class="bi bi-people-fill me-2"></i>{{$_ENV['APP_NAME']}}
            </a>
            <form class="d-flex d-none d-md-flex mx-auto" role="search">
                <input class="form-control me-2 rounded-pill" type="search" placeholder="Search" aria-label="Search">
            </form>
            <div class="d-flex align-items-center">
                    <div class="position-relative me-3">
                        <button class="btn btn-outline-secondary position-relative notification_count">
                            <i class="bi bi-bell"></i>
                            <span class="notification-badge" id="notification_count"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end w-100" aria-labelledby="notification_tab" id="notification_tab" style="width:300px">
                        <!-- Notification content will be inserted here -->
                        </div>
                    </div>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i> {{$data['fullName']}}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                        <li><a class="dropdown-item" href="/profile"  data-bs-toggle="modal" data-bs-target="#editProfileModal">Profile</a></li>

                        <li><a class="dropdown-item" href="/allMembers">All members</a></li>

                        <li><a class="dropdown-item" href="/organogram?id={{ $data['id'] }}">Family Tree</a></li>
                        <hr 
                        
                        <li><a class="dropdown-item" href="/setting?id={{ $data['id'] }}">Settings</a></li>
                        
                        <li><hr 
                        class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/signout">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>