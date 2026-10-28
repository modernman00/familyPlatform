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
            @php
                $leftPrimaryCode = ltrim((string)($primaryFamCode ?? ($data['primaryFamCode'] ?? ($data['famCode'] ?? ''))), '#');
                $leftBirthCode = ltrim((string)($maternalFamCode ?? ($data['maternalFamCode'] ?? ($data['otherFamCode'] ?? ''))), '#');
                $leftHasDualCodes = (!empty($hasMaternalCode) || !empty($data['hasMaternalCode']) || ($leftBirthCode !== '' && strtoupper($leftBirthCode) !== strtoupper($leftPrimaryCode)));
                // When the user holds two distinct codes (primary + birth), primary is their Married Code and secondary is their Birth Code.
                // When the user only holds one code (no separate birth/married split), their single code is their Birth Code and Married Code is hidden.
                $leftMarriedCode = ($leftHasDualCodes && $leftBirthCode !== '') ? $leftPrimaryCode : '';
                $leftEffectiveBirthCode = ($leftHasDualCodes && $leftBirthCode !== '') ? $leftBirthCode : $leftPrimaryCode;
                $leftAvatarSubtitle = $leftMarriedCode !== ''
                    ? __('profile.family_member', 'Family Member') . ' • ' . __('profile.married_family_code', 'Married Code') . ': ' . $leftMarriedCode . ' • ' . __('profile.birth_family_code', 'Birth Code') . ': ' . $leftEffectiveBirthCode
                    : __('profile.family_member', 'Family Member') . ' • ' . __('profile.birth_family_code', 'Birth Code') . ': ' . $leftEffectiveBirthCode;
            @endphp
            <div class="avatar-container position-absolute w-100" style="top: 55px;" title="Click to enlarge profile photo" role="button" tabindex="0" aria-label="View {{ ucwords(strtolower($data['firstName'] ?? '')) }}'s profile photo in full size">
                <div class="position-relative d-inline-block">
                    <img src="{{ str_starts_with($data['img'] ?? '', '/') ? $data['img'] : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png') }}" 
                         alt="{{ ucwords(strtolower($data['firstName'] ?? '')) }} {{ ucwords(strtolower($data['lastName'] ?? '')) }}" 
                         class="avatar profileImg profile-avatar-interactive shadow-sm" 
                         id="leftColProfileAvatar"
                         data-profile-expand="true"
                         data-profile-name="{{ ucwords(strtolower($data['firstName'] ?? '')) }} {{ ucwords(strtolower($data['lastName'] ?? '')) }}"
                         data-profile-subtitle="{{ $leftAvatarSubtitle }}"
                         data-can-edit="true"
                         style="width: 105px; height: 105px; border-radius: 50%; border: 4px solid var(--card-bg); object-fit: cover; background: var(--card-bg); margin: 0 auto; display: block;">
                    
                    <!-- Zoom Cue -->
                    <div class="avatar-zoom-cue" aria-hidden="true" title="Enlarge photo" style="right: auto; left: 0; bottom: 0;">
                        <i class="bi bi-arrows-angle-expand"></i>
                    </div>

                    <!-- Direct Profile Image Change Camera Button Overlay -->
                    <button type="button" 
                            class="avatar-camera-btn shadow-sm" 
                            id="directAvatarChangeBtn" 
                            title="Change profile photo" 
                            aria-label="Change profile photo"
                            onclick="triggerDirectAvatarFileSelect(event)">
                        <i class="bi bi-camera-fill"></i>
                    </button>
                    <input type="file" 
                           id="directAvatarUploadInput" 
                           name="direct_img" 
                           accept="image/*" 
                           style="display: none;" 
                           onchange="handleDirectAvatarUpload(this)">
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

            <!-- Family Code Tonal Pill(s) (Split into Married & Birth; hide Married Code if user does not have one) -->
            <div class="d-flex flex-column align-items-center gap-2 mb-3 w-100" id="leftColumnDualFamilyCodes" role="group" aria-label="{{ __('profile.family_codes', 'Married and Birth Family Codes') }}">
                @if($leftMarriedCode !== '')
                    <a href="/organogram/{{ $data['id'] ?? '' }}?mode=canvas&amp;family_code={{ urlencode($leftMarriedCode) }}&amp;lineage=primary"
                       class="family-code-pill family-code-pill--married text-decoration-none"
                       id="marriedFamCodePill"
                       title="{{ __('profile.view_married_tree_tooltip', 'View Married / Household Family Tree') }} (#{{ $leftMarriedCode }})">
                        <small class="family-code-label me-1.5">{{ __('profile.married_family_code', 'Married Code') }}:</small>
                        <span class="family-code-value">{{ $leftMarriedCode }}</span>
                    </a>
                @endif
                @if($leftEffectiveBirthCode !== '')
                    <a href="/organogram/{{ $data['id'] ?? '' }}?mode=canvas&amp;family_code={{ urlencode($leftEffectiveBirthCode) }}&amp;lineage={{ $leftMarriedCode !== '' ? 'birth' : 'primary' }}"
                       class="family-code-pill {{ $leftMarriedCode !== '' ? 'family-code-pill--birth' : '' }} text-decoration-none"
                       id="birthFamCodePill"
                       title="{{ __('profile.view_birth_tree_tooltip', 'View Birth / Maiden Family Tree') }} (#{{ $leftEffectiveBirthCode }})">
                        <small class="family-code-label {{ $leftMarriedCode !== '' ? 'family-code-label--birth' : '' }} me-1.5">{{ __('profile.birth_family_code', 'Birth Code') }}:</small>
                        <span class="family-code-value {{ $leftMarriedCode !== '' ? 'family-code-value--birth' : '' }}">{{ $leftEffectiveBirthCode }}</span>
                    </a>
                @endif
            </div>

            <div class="d-flex flex-row flex-wrap flex-lg-column gap-2 px-2 w-100">
                <a href="/family/hub" class="btn fw-bold flex-fill text-decoration-none text-center shadow-sm" style="background: linear-gradient(135deg, #6d28d9 0%, #db2777 100%); color: #ffffff !important; border: none; border-radius: 12px; padding: 10px 12px; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(109, 40, 217, 0.3); min-height: 44px; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; flex-shrink: 0;" title="Family Hub - Access shared family applications and tools">
                    <i class="bi bi-grid-fill me-1.5 text-warning"></i> {{ __('profile.family_hub', 'Family Hub') }}
                </a>

                <a href="/organogram/{{ $data['id'] ?? '' }}?mode=canvas" class="btn btn-surface-action fw-semibold flex-fill text-decoration-none text-center d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" title="Family Tree - View and build your interactive family lineage">
                    <i class="bi bi-diagram-3-fill me-1.5" style="color: #059669;"></i> {{ __('profile.create_family_tree', 'Create Family Tree') }}
                </a>

                <button type="button" class="btn btn-primary text-white fw-bold shadow-sm flex-fill" style="background-color: var(--primary-color, #a31621); border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" data-bs-toggle="modal" data-bs-target="#editProfileModal" title="Edit Profile - Update your personal bio, photo, and details">
                    <i class="bi bi-pencil-square me-1.5"></i> {{ __('profile.edit_profile', 'Edit Profile') }}
                </button>

                <a href="/accountSetting#family-connections" class="btn btn-surface-action fw-semibold flex-fill text-decoration-none text-center d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" title="Family Connections - Manage friends, requests, and kin links">
                    <i class="bi bi-people-fill me-1.5" style="color: #2563eb;"></i> {{ __('profile.family_connections', 'Family Connections') }}
                </a>

                <a href="/accountSetting#v-pills-familycode" class="btn btn-surface-action fw-semibold flex-fill text-decoration-none text-center d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" title="Family Code Management - Manage and share your unique family codes">
                    <i class="bi bi-qr-code me-1.5" style="color: #8b5cf6;"></i> {{ __('profile.family_code_mgt', 'Family Code Mgt') }}
                </a>

                <button type="button" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" data-bs-toggle="modal" data-bs-target="#inviteFamilyModal" title="{{ __('profile.invite_family', 'Invite Family') }} - Share your family code via WhatsApp or SMS">
                    <i class="bi bi-person-plus-fill me-1.5" style="color: #b8202c;"></i> {{ __('profile.invite_family', 'Invite Family') }}
                </button>

                <button type="button" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" data-bs-toggle="modal" data-bs-target="#createEventModal">
                    <i class="bi bi-calendar-plus me-1.5" style="color: #2563eb;"></i> {{ __('profile.create_event', 'Create Event') }}
                </button>

                <button type="button" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" data-bs-toggle="modal" data-bs-target="#createAnnouncementModal">
                    <i class="bi bi-megaphone-fill me-1.5" style="color: #d97706;"></i> {{ __('profile.announcement', 'Announcement') }}
                </button>

                <a href="/familyJournal" class="btn btn-surface-action fw-semibold flex-fill text-decoration-none text-center d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                    <i class="bi bi-journal-bookmark-fill me-1.5" style="color: #059669;"></i> Family Journal
                </a>

                <a href="/family/roundtable" class="btn fw-semibold flex-fill text-decoration-none text-center shadow-xs" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff !important; border: 1.5px solid #d4af37; border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; flex-shrink: 0;">
                    <i class="bi bi-shield-shaded me-1.5 text-warning"></i> Family RoundTable
                </a>

                <button type="button" id="directToImages" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" onclick="window.location.href='/profilepage/gallery'">
                    <i class="bi bi-images me-1.5" style="color: #6366f1;"></i> {{ __('profile.photo_gallery', 'Photo Gallery') }}
                </button>

                <button type="button" id="quickLinkThenNow" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" onclick="scrollToProfileWidget('thenNowSection')" aria-label="{{ __('profile.then_and_now', 'Then & Now') }}">
                    <i class="bi bi-hourglass-split me-1.5" style="color: #b8202c;"></i> {{ __('profile.then_and_now', 'Then & Now') }}
                </button>

                <button type="button" id="quickLinkUpcomingEvents" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" onclick="scrollToProfileWidget('upcomingEventsSection', 'allUpcomingEventsModal')" aria-label="{{ __('profile.upcoming_events', 'Upcoming Events') }}">
                    <i class="bi bi-calendar-event-fill me-1.5" style="color: #2563eb;"></i> {{ __('profile.upcoming_events', 'Upcoming Events') }}
                </button>

                <button type="button" id="quickLinkFamilyHeritage" class="btn btn-surface-action fw-semibold flex-fill d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" onclick="scrollToProfileWidget('familyHeritageSection')" aria-label="{{ __('profile.family_heritage', 'Family Rituals & Quests') }}">
                    <i class="bi bi-stars me-1.5" style="color: #059669;"></i> {{ __('profile.family_heritage', 'Family Rituals & Quests') }}
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

            window.triggerDirectAvatarFileSelect = function(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                var input = document.getElementById('directAvatarUploadInput');
                if (input) {
                    input.click();
                }
            };

            window.handleDirectAvatarUpload = async function(input) {
                if (!input || !input.files || input.files.length === 0) return;
                var file = input.files[0];
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid File',
                            text: 'Please select a valid image file (JPG, PNG, GIF, WebP).',
                            confirmButtonColor: '#a31621'
                        });
                    } else {
                        alert('Please select a valid image file.');
                    }
                    input.value = '';
                    return;
                }

                if (file.size > 10 * 1024 * 1024) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'File Too Large',
                            text: 'Profile image size must not exceed 10 MB.',
                            confirmButtonColor: '#a31621'
                        });
                    } else {
                        alert('Profile image size must not exceed 10 MB.');
                    }
                    input.value = '';
                    return;
                }

                // Instant client preview across all avatars
                var reader = new FileReader();
                reader.onload = function(e) {
                    var previewUrl = e.target.result;
                    document.querySelectorAll('.avatar.profileImg, #profilePreview, #pfViewerThumb, #pfViewerMainImg').forEach(function(img) {
                        img.src = previewUrl;
                    });
                };
                reader.readAsDataURL(file);

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Updating Profile Picture...',
                        text: 'Uploading and saving your new photo.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    });
                }

                var formData = new FormData();
                formData.append('img', file);
                formData.append('id', @json((string)($data['id'] ?? '')));

                var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content 
                    || document.querySelector('input[name="token"]')?.value 
                    || @json($_SESSION['token'] ?? '');
                if (csrfToken) {
                    formData.append('token', csrfToken);
                }

                try {
                    var response = await fetch('/member/profilePage/editProfile', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {})
                        }
                    });

                    var result = await response.json().catch(function() { return {}; });

                    if (response.ok) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Profile picture updated successfully!',
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true
                            });
                        }
                        setTimeout(function() {
                            window.location.reload();
                        }, 900);
                    } else {
                        var errMsg = result.message || result.error || 'Failed to update profile picture. Please try again.';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Upload Failed',
                                text: errMsg,
                                confirmButtonColor: '#a31621',
                                showCancelButton: true,
                                confirmButtonText: 'Open Edit Profile',
                                cancelButtonText: 'Dismiss'
                            }).then(function(res) {
                                if (res.isConfirmed) {
                                    var modalEl = document.getElementById('editProfileModal');
                                    if (modalEl && window.bootstrap && window.bootstrap.Modal) {
                                        window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                                    }
                                }
                            });
                        } else {
                            alert(errMsg);
                        }
                    }
                } catch (err) {
                    console.error('Direct Avatar Upload Error:', err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Could not connect to the server. Please try again.',
                            confirmButtonColor: '#a31621'
                        });
                    } else {
                        alert('Network error while updating profile picture.');
                    }
                } finally {
                    input.value = '';
                }
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
