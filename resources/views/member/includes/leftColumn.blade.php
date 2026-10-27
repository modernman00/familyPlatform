<div class="profile-column" x-data="profileSidebar({ id: '{{ $data['id'] }}', firstName: '{{ $data['firstName'] }}', lastName: '{{ $data['lastName'] }}', img: '{{ $data['img'] ?? $data['profilePics'] ?? '' }}', famCode: '{{ $data['famCode'] }}' })">

    <!-- Profile Overview Card -->
    <div class="card profile-card text-center border-0 shadow-sm" style="border-radius: 20px; overflow: hidden; background-color: var(--card-bg); color: var(--text-color);">
        <div class="card-header position-relative p-0 border-0">
            <!-- Modern Brand Radial Mesh Cover -->
            <div class="cover-photo"
                style="height: 120px; background: linear-gradient(135deg, #2a0a0d 0%, #2a0a0d 40%, #8a1019 80%, #b8202c 100%); position: relative;">
                <span class="position-absolute top-0 end-0 m-3 badge bg-white bg-opacity-25 text-white fw-bold px-2 py-1 rounded-pill" style="font-size: 0.7rem; letter-spacing: 0.04em;">
                    <i class="bi bi-shield-check text-warning"></i> {{ __('profile.verified', 'VERIFIED') }}
                </span>
            </div>
            <div class="avatar-container position-absolute w-100" style="top: 55px;" title="Click to enlarge profile photo" role="button" tabindex="0" aria-label="View {{ ucwords(strtolower($data['firstName'] ?? '')) }}'s profile photo in full size">
                <img src="{{ str_starts_with($data['img'] ?? '', '/') ? $data['img'] : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png') }}" 
                     alt="{{ ucwords(strtolower($data['firstName'] ?? '')) }} {{ ucwords(strtolower($data['lastName'] ?? '')) }}" 
                     class="avatar profileImg profile-avatar-interactive shadow-sm" 
                     data-profile-expand="true"
                     data-profile-name="{{ ucwords(strtolower($data['firstName'] ?? '')) }} {{ ucwords(strtolower($data['lastName'] ?? '')) }}"
                     data-profile-subtitle="{{ __('profile.family_member', 'Family Member') }} • {{ $data['famCode'] ?? '' }}"
                     data-can-edit="true"
                     style="width: 105px; height: 105px; border-radius: 50%; border: 4px solid var(--card-bg); object-fit: cover; background: var(--card-bg);">
                <div class="avatar-zoom-cue" aria-hidden="true" title="Enlarge photo">
                    <i class="bi bi-arrows-angle-expand"></i>
                </div>
            </div>
        </div>

        <div class="card-body pt-5 mt-3 d-flex flex-column align-items-center">

            <h4 class="card-title fw-bold mb-1" style="color: var(--text-color); font-size: 1.25rem; letter-spacing: -0.01em;">
                {{ ucwords(strtolower($data['firstName'])) }} {{ ucwords(strtolower($data['lastName'])) }}
            </h4>

            <p class="card-text mb-3" style="color: var(--text-muted); font-size: 0.85rem;">
                <span class="me-1" style="font-size: 1.05rem;">{{ \App\classes\CountryCatalog::getFlag($data['country'] ?? '') }}</span>
                <span>{{ $data['country'] ?? 'Global' }}</span>
            </p>

            <!-- Family Code Tonal Pill -->
            <div class="family-code-pill mb-3">
                <small class="family-code-label me-1">{{ __('profile.family_code', 'Family Code') }}:</small>
                <span class="family-code-value"><i class="bi bi-hash"></i>{{ $data['famCode'] }}</span>
            </div>

            <div class="d-flex flex-row flex-wrap flex-lg-column gap-2 px-2 w-100">
                <a href="/family/fun-place" class="btn fw-bold flex-fill text-decoration-none text-center shadow-sm" style="background: linear-gradient(135deg, #6d28d9 0%, #db2777 100%); color: #ffffff !important; border: none; border-radius: 12px; padding: 10px 12px; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(109, 40, 217, 0.3); min-height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="bi bi-stars me-1.5 text-warning"></i> {{ __('profile.the_fun_place', 'The Fun Place') }}
                </a>

                <button type="button" class="btn btn-primary text-white fw-bold shadow-sm flex-fill" style="background-color: var(--primary-color, #a31621); border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px;" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-pencil-square me-1.5"></i> {{ __('profile.edit_profile', 'Edit Profile') }}
                </button>

                <button type="button" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px;" data-bs-toggle="modal" data-bs-target="#createEventModal">
                    <i class="bi bi-calendar-plus me-1.5" style="color: #2563eb;"></i> {{ __('profile.create_event', 'Create Event') }}
                </button>

                <button type="button" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px;" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal">
                    <i class="bi bi-megaphone-fill me-1.5" style="color: #d97706;"></i> {{ __('profile.announcement', 'Announcement') }}
                </button>

                <a href="/familyJournal" class="btn btn-surface-action fw-semibold flex-fill text-decoration-none text-center d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px;">
                    <i class="bi bi-journal-bookmark-fill me-1.5" style="color: #059669;"></i> {{ __('profile.family_journal', 'Family Journal') }}
                </a>

                <a href="/family/roundtable" class="btn fw-semibold flex-fill text-decoration-none text-center shadow-xs" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff !important; border: 1.5px solid #d4af37; border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="bi bi-shield-shaded me-1.5 text-warning"></i> {{ __('profile.family_roundtable', 'Family RoundTable') }}
                </a>

                <button type="button" id="directToImages" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px;" onclick="window.location.href='/profilepage/gallery'">
                    <i class="bi bi-images me-1.5" style="color: #6366f1;"></i> {{ __('profile.photo_gallery', 'Photo Gallery') }}
                </button>

                <button type="button" id="quickLinkThenNow" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" onclick="scrollToProfileWidget('thenNowSection')" aria-label="{{ __('profile.then_and_now', 'Then & Now') }}">
                    <i class="bi bi-hourglass-split me-1.5" style="color: #b8202c;"></i> {{ __('profile.then_and_now', 'Then & Now') }}
                </button>

                <button type="button" id="quickLinkUpcomingEvents" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" onclick="scrollToProfileWidget('upcomingEventsSection', 'allUpcomingEventsModal')" aria-label="{{ __('profile.upcoming_events', 'Upcoming Events') }}">
                    <i class="bi bi-calendar-event-fill me-1.5" style="color: #2563eb;"></i> {{ __('profile.upcoming_events', 'Upcoming Events') }}
                </button>

                <button type="button" id="quickLinkFamilyHeritage" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" onclick="scrollToProfileWidget('familyHeritageSection')" aria-label="{{ __('profile.family_heritage', 'Family Heritage') }}">
                    <i class="bi bi-stars me-1.5" style="color: #059669;"></i> {{ __('profile.family_heritage', 'Family Heritage') }}
                </button>

                <a href="/how-to-use" class="btn btn-surface-action fw-semibold flex-fill text-decoration-none text-center d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                    <i class="bi bi-book-half me-1.5" style="color: #0284c7;"></i> {{ __('profile.how_to_use', 'How to Use Guide') }}
                </a>
            </div>

            <style>
            .highlight-section-pulse {
                animation: profileWidgetPulse 2.5s ease-out;
                scroll-margin-top: 80px;
            }
            @keyframes profileWidgetPulse {
                0% { box-shadow: 0 0 0 6px rgba(163, 22, 33, 0.7); }
                50% { box-shadow: 0 0 0 3px rgba(163, 22, 33, 0.35); }
                100% { box-shadow: 0 0 0 0 rgba(163, 22, 33, 0); }
            }
            </style>
            <script>
            window.scrollToProfileWidget = function(targetId, modalFallbackId) {
                var target = document.getElementById(targetId);
                if (target && target.classList.contains('d-none')) {
                    target.classList.remove('d-none');
                }
                if (target && target.offsetParent !== null) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    target.classList.add('highlight-section-pulse');
                    setTimeout(function() {
                        target.classList.remove('highlight-section-pulse');
                    }, 2500);
                    return false;
                }
                if (modalFallbackId) {
                    var modalEl = document.getElementById(modalFallbackId);
                    if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                        var bsModal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
                        bsModal.show();
                        return false;
                    }
                }
                if (target) {
                    target.classList.remove('d-none');
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    target.classList.add('highlight-section-pulse');
                    setTimeout(function() {
                        target.classList.remove('highlight-section-pulse');
                    }, 2500);
                }
                return false;
            };
            </script>

        </div>
    </div>

    <!-- Kin Requests Card (Only shown if there are pending requests) -->
    <div class="card border-0 shadow-sm mt-4" 
         x-show="!isLoadingRequests && friendRequests && friendRequests.length > 0" 
         x-cloak 
         x-transition
         style="border-radius: 20px; background-color: var(--card-bg);">

        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4">
            <template x-if="friendRequests.length > 1">
                <h5 class="mb-0 fw-bold" style="color: var(--text-color); font-size: 1.05rem;">
                    <span x-text="friendRequests.length"></span> {{ __('profile.kin_requests', 'Kin Requests') }}
                </h5>
            </template>
            <template x-if="friendRequests.length === 1">
                <h5 class="mb-0 fw-bold" style="color: var(--text-color); font-size: 1.05rem;">{{ __('profile.kin_request', 'Kin Request') }}</h5>
            </template>
            <template x-if="friendRequests.length > 1">
                <a href="/allMembers" class="text-decoration-none" style="color: var(--primary-color); font-weight: 600; font-size: 0.85rem;">{{ __('profile.see_all', 'See All') }} <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i></a>
            </template>
        </div>

        <div class="card-body">
            <!-- Dynamic Requests List -->
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
        </div>
    </div>
</div>
