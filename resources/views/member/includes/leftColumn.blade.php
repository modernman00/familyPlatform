<div class="profile-column" x-data="profileSidebar({ id: '{{ $data['id'] }}', firstName: '{{ $data['firstName'] }}', lastName: '{{ $data['lastName'] }}', img: '{{ $data['img'] ?? $data['profilePics'] ?? '' }}', famCode: '{{ $data['famCode'] }}' })">

    <div class="card profile-card text-center border-0 shadow-sm" style="background-color: var(--card-bg); color: var(--text-color);">
        <div class="card-header position-relative p-0 border-0">
            <div class="cover-photo"
                style="height: 120px; background: linear-gradient(135deg, var(--primary-color) 0%, #1e6040 100%); border-radius: 16px 16px 0 0;"></div>
            <div class="avatar-container position-absolute w-100" style="top: 60px;">
                @isset($data['img'])
                    <img src="/public/img/profile/{{ $data['img'] }}" alt="Avatar" class="avatar profileImg shadow-sm" style="width: 110px; height: 110px; border-radius: 50%; border: 4px solid var(--card-bg); object-fit: cover; background: var(--card-bg);">
                @elseif(isset($data['profilePics']))
                    <img src="/public/img/profile/{{ $data['profilePics'] }}" alt="Avatar" class="avatar profileImg shadow-sm" style="width: 110px; height: 110px; border-radius: 50%; border: 4px solid var(--card-bg); object-fit: cover; background: var(--card-bg);">
                @else
                    <img src="/public/img/profile/avatarM.png" alt="Avatar" class="avatar profileImg shadow-sm" style="width: 110px; height: 110px; border-radius: 50%; border: 4px solid var(--card-bg); object-fit: cover; background: var(--card-bg);">
                @endisset
            </div>
        </div>

        <div class="card-body pt-5 mt-4 d-flex flex-column align-items-center">

            <h4 class="card-title fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: var(--text-color);">
                {{ ucwords(strtolower($data['firstName'])) }} {{ ucwords(strtolower($data['lastName'])) }}
            </h4>

            <p class="card-text mb-4" style="color: var(--text-muted);"><i class="bi bi-geo-alt"></i> {{ $data['country'] }}</p>

            <div class="family-code-pill mb-4" style="background-color: var(--secondary-color); border-radius: 20px; padding: 10px 20px; width: fit-content;">
                <small class="d-block text-uppercase" style="color: var(--text-muted); font-size: 0.7rem; letter-spacing: 1px;">Family Code</small>
                <span class="fw-bold" style="color: var(--primary-color); letter-spacing: 1px;"><i class="bi bi-hash"></i> {{ $data['famCode'] }}</span>
            </div>

            <div class="d-flex flex-column gap-2 mt-2 px-2 w-100">
                <button type="button" class="btn text-white fw-semibold" style="background-color: var(--primary-color); border-radius: 20px; padding: 10px;" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-pencil-square me-2"></i> Edit Profile
                </button>

                <button type="button" class="btn fw-semibold" style="border: 1px solid var(--border-color); color: var(--text-color); border-radius: 20px; padding: 10px;" data-bs-toggle="modal" data-bs-target="#createEventModal">
                    <i class="bi bi-calendar-plus me-2"></i> Create Event
                </button>

                <button type="button" id="directToImages" class="btn fw-semibold" style="background-color: var(--bg-color); color: var(--text-color); border: 1px solid var(--border-color); border-radius: 20px; padding: 10px;" onclick="window.location.href='/profilepage/img'">
                    <i class="bi bi-images me-2"></i> Photo Gallery
                </button>
            </div>

        </div>
    </div>

    <!-- Friend Requests Card -->
    <div class="card border-0 shadow-sm mt-4" style="border-radius: 16px; background-color: var(--card-bg);">
        
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4">
            <template x-if="friendRequests.length > 1">
                <h5 class="mb-0 fw-bold" style="font-family: 'Playfair Display', serif; color: var(--text-color);">
                    <span x-text="friendRequests.length"></span> Friend Requests
                </h5>
            </template>
            <template x-if="friendRequests.length <= 1">
                <h5 class="mb-0 fw-bold" style="font-family: 'Playfair Display', serif; color: var(--text-color);">Friend Request</h5>
            </template>
            <template x-if="friendRequests.length > 1">
                <a href="#" class="text-decoration-none" style="color: var(--primary-color); font-weight: 600; font-size: 0.9rem;">See All <i class="bi bi-chevron-right" style="font-size: 0.8rem;"></i></a>
            </template>
        </div>

        <div class="card-body">
            <!-- Loading state -->
            <template x-if="isLoadingRequests">
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                </div>
            </template>

            <!-- Empty state -->
            <template x-if="!isLoadingRequests && friendRequests.length === 0">
                <div class="text-center py-4">
                    <i class="bi bi-people text-muted fs-3"></i>
                    <p class="text-muted small mt-2 mb-0">No pending requests</p>
                </div>
            </template>

            <!-- Dynamic Requests List -->
            <template x-if="!isLoadingRequests && friendRequests.length > 0">
                <div class="d-flex flex-column gap-3">
                    <template x-for="req in friendRequests" :key="req.id">
                        <div class="d-flex flex-column align-items-center text-center p-3 border rounded-3" style="background-color: var(--hover-color); border-color: var(--border-color);">
                            <img :src="'/public/img/profile/' + req.img" alt="Avatar" class="rounded-circle shadow-sm mb-2" style="width: 60px; height: 60px; object-fit: cover;">
                            <span class="fw-semibold mb-2" x-text="req.firstName + ' ' + req.lastName"></span>
                            
                            <div class="d-flex gap-2 w-100">
                                <a :href="getAcceptUrl(req)" class="btn btn-success btn-sm flex-grow-1" title="Accept" style="text-decoration: none;">
                                    <i class="bi bi-check-lg"></i> Accept
                                </a>
                                <a :href="getDeclineUrl(req)" class="btn btn-danger btn-sm flex-grow-1" title="Decline" style="text-decoration: none;">
                                    <i class="bi bi-x-lg"></i> Decline
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>

