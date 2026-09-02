<div class="profile-column" x-data="profileSidebar({ id: '{{ $data['id'] }}', firstName: '{{ $data['firstName'] }}', lastName: '{{ $data['lastName'] }}', img: '{{ $data['img'] ?? $data['profilePics'] ?? '' }}', famCode: '{{ $data['famCode'] }}' })">

    <!-- Profile Overview Card -->
    <div class="card profile-card text-center border-0 shadow-sm" style="border-radius: 20px; overflow: hidden; background-color: var(--card-bg); color: var(--text-color);">
        <div class="card-header position-relative p-0 border-0">
            <!-- Modern Brand Radial Mesh Cover -->
            <div class="cover-photo"
                style="height: 120px; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 80%, #6366f1 100%); position: relative;">
                <span class="position-absolute top-0 end-0 m-3 badge bg-white bg-opacity-25 text-white fw-bold px-2 py-1 rounded-pill" style="font-size: 0.7rem; letter-spacing: 0.04em;">
                    <i class="bi bi-shield-check text-warning"></i> VERIFIED
                </span>
            </div>
            <div class="avatar-container position-absolute w-100" style="top: 55px;">
                <img src="{{ str_starts_with($data['img'] ?? '', '/') ? $data['img'] : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png') }}" alt="Avatar" class="avatar profileImg shadow-sm" style="width: 105px; height: 105px; border-radius: 50%; border: 4px solid var(--card-bg); object-fit: cover; background: var(--card-bg);">
            </div>
        </div>

        <div class="card-body pt-5 mt-3 d-flex flex-column align-items-center">

            <h4 class="card-title fw-bold mb-1" style="color: var(--text-color); font-size: 1.25rem; letter-spacing: -0.01em;">
                {{ ucwords(strtolower($data['firstName'])) }} {{ ucwords(strtolower($data['lastName'])) }}
            </h4>

            <p class="card-text mb-3" style="color: var(--text-muted); font-size: 0.85rem;"><i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $data['country'] ?? 'Global' }}</p>

            <!-- Family Code Tonal Pill -->
            <div class="family-code-pill mb-4" style="background-color: var(--secondary-color); border: 1px solid #c7d2fe; border-radius: 9999px; padding: 6px 18px; width: fit-content;">
                <small class="d-inline text-uppercase fw-bold me-1" style="color: #4f46e5; font-size: 0.72rem; letter-spacing: 0.05em;">Family Code:</small>
                <span class="fw-bold" style="color: #312e81; font-size: 0.85rem;"><i class="bi bi-hash"></i>{{ $data['famCode'] }}</span>
            </div>

            <div class="d-flex flex-column gap-2 px-2 w-100">
                <button type="button" class="btn text-white fw-bold shadow-sm" style="background-color: var(--primary-color); border-radius: 12px; padding: 10px; font-size: 0.88rem;" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-pencil-square me-2"></i> Edit Profile
                </button>

                <button type="button" class="btn fw-semibold" style="border: 1px solid var(--border-color); color: var(--text-color); background: var(--hover-color); border-radius: 12px; padding: 10px; font-size: 0.88rem;" data-bs-toggle="modal" data-bs-target="#createEventModal">
                    <i class="bi bi-calendar-plus me-2 text-primary"></i> Create Event
                </button>

                <button type="button" id="directToImages" class="btn fw-semibold" style="background-color: var(--card-bg); color: var(--text-color); border: 1px solid var(--border-color); border-radius: 12px; padding: 10px; font-size: 0.88rem;" onclick="window.location.href='/profilepage/gallery'">
                    <i class="bi bi-images me-2 text-secondary"></i> Photo Gallery
                </button>
            </div>

        </div>
    </div>

    <!-- Friend Requests Card -->
    <div class="card border-0 shadow-sm mt-4" style="border-radius: 20px; background-color: var(--card-bg);">
        
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4">
            <template x-if="friendRequests.length > 1">
                <h5 class="mb-0 fw-bold" style="color: var(--text-color); font-size: 1.05rem;">
                    <span x-text="friendRequests.length"></span> Kin Requests
                </h5>
            </template>
            <template x-if="friendRequests.length <= 1">
                <h5 class="mb-0 fw-bold" style="color: var(--text-color); font-size: 1.05rem;">Kin Requests</h5>
            </template>
            <template x-if="friendRequests.length > 1">
                <a href="/allMembers" class="text-decoration-none" style="color: var(--primary-color); font-weight: 600; font-size: 0.85rem;">See All <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i></a>
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
                    <div class="mb-2 d-inline-flex align-items-center justify-content-center rounded-circle bg-light p-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-people text-muted fs-4"></i>
                    </div>
                    <p class="text-muted small mt-1 mb-0">No pending kin requests</p>
                </div>
            </template>

            <!-- Dynamic Requests List -->
            <template x-if="!isLoadingRequests && friendRequests.length > 0">
                <div class="d-flex flex-column gap-3">
                    <template x-for="req in friendRequests" :key="req.id">
                        <div class="d-flex flex-column align-items-center text-center p-3 border rounded-3" style="background-color: var(--hover-color); border-color: var(--border-color); border-radius: 14px;">
                            <img :src="'/resources/images/profile/' + req.img" alt="Avatar" class="rounded-circle shadow-sm mb-2" style="width: 56px; height: 56px; object-fit: cover;">
                            <span class="fw-semibold mb-2" style="font-size: 0.9rem;" x-text="req.firstName + ' ' + req.lastName"></span>
                            
                            <div class="d-flex gap-2 w-100">
                                <a :href="getAcceptUrl(req)" class="btn btn-primary btn-sm flex-grow-1 fw-bold rounded-pill" title="Accept" style="text-decoration: none;">
                                    <i class="bi bi-check-lg"></i> Accept
                                </a>
                                <a :href="getDeclineUrl(req)" class="btn btn-outline-danger btn-sm flex-grow-1 fw-bold rounded-pill" title="Decline" style="text-decoration: none;">
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
