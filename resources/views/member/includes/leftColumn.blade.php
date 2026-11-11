<div class="profile-column" x-data="profileSidebar({ id: '{{ $data['id'] }}', firstName: '{{ $data['firstName'] }}', lastName: '{{ $data['lastName'] }}', img: '{{ $data['img'] ?? $data['profilePics'] ?? '' }}', famCode: '{{ $data['famCode'] }}' })">

    <!-- Profile Overview Card -->
    <div class="card profile-card text-center border-0 shadow-sm" style="border-radius: 20px; overflow: hidden; background-color: var(--card-bg); color: var(--text-color);">
        <div class="card-header position-relative p-0 border-0">
            <!-- Modern Brand Radial Mesh Cover -->
            <!-- Member cover photo (falls back to the brand gradient). Nothing sits on top of the photo:
                 tapping the cover opens a small edit menu; the focal point is adjustable. -->
            @php $hasCover = !empty($cover['url']); @endphp
            <!-- Kept outside the band so its programmatic click can't bubble back into openCoverMenu -->
            <input type="file" id="coverPhotoInput" accept="image/jpeg,image/png,image/webp,image/heic" hidden onchange="handleProfileCoverUpload(this)">
            <div class="cover-photo {{ $hasCover ? 'has-cover' : '' }}"
                id="profileCoverPhoto"
                role="button" tabindex="0"
                aria-label="{{ $hasCover ? 'Edit cover photo' : 'Add cover photo' }}"
                aria-haspopup="menu"
                onclick="openCoverMenu(event)"
                onkeydown="if (event.key === 'Enter' || event.key === ' ') { openCoverMenu(event); }"
                @if($hasCover) style="background-image: url('{{ $cover['url'] }}'); background-position: {{ (int) $cover['x'] }}% {{ (int) $cover['y'] }}%;" @endif>
                <!-- Only shown on the empty gradient (never over a photo) so members can discover the feature -->
                <span class="cover-photo__add {{ $hasCover ? 'd-none' : '' }}" id="coverPhotoAddHint" aria-hidden="true">
                    <i class="bi bi-image"></i> Add cover photo
                </span>

                <!-- Tap menu (appears on demand, closes on outside tap / Escape) -->
                <div class="cover-photo__menu d-none" id="coverPhotoMenu" role="menu" aria-label="Cover photo options">
                    <button type="button" role="menuitem" onclick="coverMenuAction('change', event)"><i class="bi bi-image"></i> Change photo</button>
                    <button type="button" role="menuitem" onclick="coverMenuAction('adjust', event)"><i class="bi bi-arrows-move"></i> Reposition</button>
                    <button type="button" role="menuitem" class="is-danger" onclick="coverMenuAction('remove', event)"><i class="bi bi-trash3"></i> Remove</button>
                </div>

                <!-- Reposition mode (drag or arrow keys) — only visible while adjusting -->
                <div class="cover-photo__adjust-bar d-none" id="coverAdjustBar">
                    <span class="cover-photo__adjust-hint"><i class="bi bi-arrows-move"></i> Drag to reposition</span>
                    <button type="button" class="cover-photo__btn" id="coverAdjustCancelBtn" onclick="endCoverAdjust(false)">Cancel</button>
                    <button type="button" class="cover-photo__btn cover-photo__btn--primary" id="coverAdjustSaveBtn" onclick="endCoverAdjust(true)">Save</button>
                </div>
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
            <div class="avatar-container position-absolute w-100" title="Click to enlarge profile photo" role="button" tabindex="0" aria-label="View {{ ucwords(strtolower($data['firstName'] ?? '')) }}'s profile photo in full size">
                <div class="position-relative d-inline-block">
                    <img src="{{ str_starts_with($data['img'] ?? '', '/') ? $data['img'] : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png') }}" 
                         alt="{{ ucwords(strtolower($data['firstName'] ?? '')) }} {{ ucwords(strtolower($data['lastName'] ?? '')) }}" 
                         class="avatar profileImg profile-avatar-interactive shadow-sm" 
                         id="leftColProfileAvatar"
                         width="105"
                         height="105"
                         fetchpriority="high"
                         data-profile-expand="true"
                         data-profile-name="{{ ucwords(strtolower($data['firstName'] ?? '')) }} {{ ucwords(strtolower($data['lastName'] ?? '')) }}"
                         data-profile-subtitle="{{ $leftAvatarSubtitle }}"
                         data-can-edit="true"
                         style="width: 105px; height: 105px; border-radius: 50%; border: 4px solid var(--card-bg); object-fit: cover; background: var(--card-bg); margin: 0 auto; display: block;">
                    
                    <!-- Zoom Cue (subtle on hover only) -->
                    <div class="avatar-zoom-cue" aria-hidden="true" title="Enlarge photo">
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
                    <!-- Living Avatar Studio shortcut (mirrors the camera button) -->
                    <a href="/member/emoji-studio"
                       class="avatar-emoji-btn shadow-sm"
                       id="avatarStudioShortcut"
                       title="Create your living avatar"
                       aria-label="Create your living avatar"
                       onclick="event.stopPropagation()">
                        <i class="bi bi-person-bounding-box" aria-hidden="true"></i>
                    </a>
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
                @if(empty($isPendingApproval))
                <i class="bi bi-patch-check-fill profile-verified-tick" title="{{ __('profile.verified', 'Verified') }}" aria-label="{{ __('profile.verified', 'Verified') }}"></i>
                @endif
            </h4>
            @if(!empty($isPendingApproval))
            <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill mb-2" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                <i class="bi bi-hourglass-split me-1"></i> PENDING APPROVAL
            </span>
            @endif

            @php
                $leftUserCountry = trim((string)($data['country'] ?? ''));
            @endphp
            <p class="card-text mb-3 d-inline-flex align-items-center justify-content-center gap-1" style="color: var(--text-muted); font-size: 0.85rem;">
                <span style="font-size: 1.05rem;">{{ \App\classes\CountryCatalog::getFlag($leftUserCountry) }}</span>
                <span>{{ $leftUserCountry !== '' ? $leftUserCountry : __('profile.global', 'Global') }}</span>
            </p>

            <!-- Family Code Headline & Tonal Pill(s) (Headline with Married - XXX and Birth - XXX badges) -->
            <div class="family-code-container d-flex flex-column align-items-center mb-3 w-100" id="leftColumnDualFamilyCodes" role="group" aria-label="{{ __('profile.family_codes', 'Married and Birth Family Codes') }}">
                <span class="family-code-headline text-uppercase fw-bold mb-1.5" style="font-size: 0.72rem; letter-spacing: 0.08em; color: var(--text-muted, #64748b);">
                    <i class="bi bi-qr-code me-1 opacity-75"></i>{{ $leftMarriedCode !== '' ? __('profile.family_codes_headline', 'Family Codes') : __('profile.family_code_headline', 'Family Code') }}
                </span>
                <div class="d-flex flex-row flex-wrap justify-content-center align-items-center gap-1.5 w-100">
                    @if($leftMarriedCode !== '')
                        <a href="/organogram/{{ $data['id'] ?? '' }}?mode=canvas&amp;family_code={{ urlencode($leftMarriedCode) }}&amp;lineage=primary"
                           class="family-code-pill family-code-pill--married text-decoration-none"
                           id="marriedFamCodePill"
                           title="{{ __('profile.view_married_tree_tooltip', 'View Married / Household Family Tree') }} (#{{ $leftMarriedCode }})">
                            <span class="family-code-label me-1">{{ __('profile.married_code_short', 'Married') }} -</span>
                            <span class="family-code-value">{{ $leftMarriedCode }}</span>
                        </a>
                    @endif
                    @if($leftEffectiveBirthCode !== '')
                        <a href="/organogram/{{ $data['id'] ?? '' }}?mode=canvas&amp;family_code={{ urlencode($leftEffectiveBirthCode) }}&amp;lineage={{ $leftMarriedCode !== '' ? 'birth' : 'primary' }}"
                           class="family-code-pill {{ $leftMarriedCode !== '' ? 'family-code-pill--birth' : '' }} text-decoration-none"
                           id="birthFamCodePill"
                           title="{{ __('profile.view_birth_tree_tooltip', 'View Birth / Maiden Family Tree') }} (#{{ $leftEffectiveBirthCode }})">
                            <span class="family-code-label {{ $leftMarriedCode !== '' ? 'family-code-label--birth' : '' }} me-1">{{ __('profile.birth_code_short', 'Birth') }} -</span>
                            <span class="family-code-value {{ $leftMarriedCode !== '' ? 'family-code-value--birth' : '' }}">{{ $leftEffectiveBirthCode }}</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Mobile/tablet only: collapsible Quick actions toggle (desktop always shows the buttons) --}}
            <button type="button" id="profileQuickActionsToggle" class="profile-quick-toggle d-lg-none btn fw-bold w-100 mb-2 d-inline-flex align-items-center justify-content-between" aria-expanded="false" aria-controls="profileQuickActions" onclick="var p=document.getElementById('profileQuickActions');var o=p.classList.toggle('is-open');this.setAttribute('aria-expanded',o?'true':'false');">
                <span><i class="bi bi-lightning-charge-fill me-1.5 text-warning"></i> {{ __('profile.quick_actions', 'Quick actions') }}</span>
                <i class="bi bi-chevron-down profile-quick-chevron" aria-hidden="true"></i>
            </button>

            @if(empty($isPendingApproval))
            <div id="profileQuickActions" class="profile-quick-actions d-flex flex-row flex-wrap flex-lg-column gap-2 px-2 w-100">
                <a href="/family/hub" class="btn fw-bold flex-fill text-decoration-none text-center shadow-sm" style="background: linear-gradient(135deg, #6d28d9 0%, #db2777 100%); color: #ffffff !important; border: none; border-radius: 12px; padding: 10px 12px; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(109, 40, 217, 0.3); min-height: 44px; display: inline-flex; align-items: center; justify-content: center; white-space: nowrap; flex-shrink: 0;" title="Family Hub - Access shared family applications and tools">
                    <i class="bi bi-grid-fill me-1.5 text-warning"></i> {{ __('profile.family_hub', 'Family Hub') }}
                </a>

                <a href="/organogram/{{ $data['id'] ?? '' }}?mode=canvas" class="btn btn-surface-action fw-semibold flex-fill text-decoration-none text-center d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" title="Family Tree - View and build your interactive family lineage">
                    <i class="bi bi-diagram-3-fill me-1.5" style="color: #059669;"></i> {{ __('profile.create_family_tree', 'Create Family Tree') }}
                </a>

                <a href="/kinship-calculator" class="btn btn-surface-action fw-semibold flex-fill text-decoration-none text-center d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" title="{{ __('profile.kinship_calculator', 'Kinship Calculator') }} - Calculate exact relationship degrees and shared ancestry">
                    <i class="bi bi-calculator-fill me-1.5" style="color: #a31621;"></i> {{ __('profile.kinship_calculator', 'Kinship Calculator') }}
                </a>

                <button type="button" class="btn btn-primary text-white fw-bold shadow-sm flex-fill" style="background-color: var(--primary-color, #a31621); border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" data-bs-toggle="modal" data-bs-target="#editProfileModal" title="Edit Profile - Update your personal bio, photo, and details">
                    <i class="bi bi-pencil-square me-1.5"></i> {{ __('profile.edit_profile', 'Edit Profile') }}
                </button>

                <a href="/member/emoji-studio" class="btn btn-surface-action fw-semibold flex-fill text-decoration-none text-center d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 9px 12px; font-size: 0.9rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" title="3D Avatar & Emoji Studio - Create your 3D persona and use bespoke family emojis">
                    <i class="bi bi-palette-fill me-1.5" style="color: #f59e0b;"></i> 3D Avatar Studio
                </a>

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
            @else
            <div id="profileQuickActions" class="profile-quick-actions d-flex flex-row flex-wrap flex-lg-column gap-2 px-2 w-100">
                <button type="button" class="btn btn-primary text-white fw-bold shadow-sm flex-fill" style="background-color: var(--primary-color, #a31621); border-radius: 12px; padding: 10px 12px; font-size: 0.92rem; min-height: 44px; white-space: nowrap; flex-shrink: 0;" data-bs-toggle="modal" data-bs-target="#editProfileModal" title="Edit Profile - Update your personal bio, photo, and details">
                    <i class="bi bi-pencil-square me-1.5"></i> {{ __('profile.edit_profile', 'Edit Profile') }}
                </button>

                <div class="p-3 text-center rounded-3 mt-1" style="background: rgba(163, 22, 33, 0.05); border: 1px dashed rgba(163, 22, 33, 0.25);">
                    <i class="bi bi-shield-lock-fill text-muted fs-4"></i>
                    <p class="text-muted small mb-0 mt-1" style="font-size: 0.82rem;">
                        Lineage tree, photo gallery, and journal unlock upon approval.
                    </p>
                </div>
            </div>
            @endif

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
            <script nonce="{{ csp_nonce($nonce ?? null) }}">
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

            // ── Cover photo (shared-lib FileUploader pipeline via /member/profilePage/coverImg) ──
            (function() {
                function coverCsrf() {
                    return document.querySelector('meta[name="csrf-token"]')?.content
                        || document.querySelector('input[name="token"]')?.value
                        || @json($_SESSION['token'] ?? '');
                }
                function coverNotify(icon, title, text) {
                    if (typeof Swal === 'undefined') { if (icon === 'error') alert(text || title); return; }
                    if (icon === 'success') {
                        Swal.fire({ toast: true, position: 'top-end', icon: icon, title: title, showConfirmButton: false, timer: 1500 });
                    } else {
                        Swal.fire({ icon: icon, title: title, text: text, confirmButtonColor: '#a31621' });
                    }
                }
                function applyCover(url, pos) {
                    var band = document.getElementById('profileCoverPhoto');
                    if (!band) return;
                    band.classList.toggle('has-cover', !!url);
                    band.style.backgroundImage = url ? 'url("' + url + '")' : '';
                    band.style.backgroundPosition = url ? (pos ? pos.x + '% ' + pos.y + '%' : '50% 50%') : '';
                    document.getElementById('coverPhotoAddHint')?.classList.toggle('d-none', !!url);
                    band.setAttribute('aria-label', url ? 'Edit cover photo' : 'Add cover photo');
                }
                // ── Reposition: drag (pointer) or arrow keys moves background-position; Save persists it ──
                var adjust = null;
                function readPos(band) {
                    var parts = (band.style.backgroundPosition || '50% 50%').split(' ');
                    return { x: parseFloat(parts[0]) || 50, y: parseFloat(parts[1] ?? parts[0]) || 50 };
                }
                function setPos(band, x, y) {
                    x = Math.max(0, Math.min(100, x));
                    y = Math.max(0, Math.min(100, y));
                    band.style.backgroundPosition = x.toFixed(1) + '% ' + y.toFixed(1) + '%';
                }
                function measureOverflow(band, img) {
                    // background-size: cover → scaled image overflows the band on one axis
                    var w = band.clientWidth, h = band.clientHeight;
                    var scale = Math.max(w / img.naturalWidth, h / img.naturalHeight);
                    return { x: img.naturalWidth * scale - w, y: img.naturalHeight * scale - h };
                }
                function onPointerDown(e) {
                    if (!adjust || e.target.closest('button')) return;
                    e.preventDefault();
                    adjust.drag = { startX: e.clientX, startY: e.clientY, from: readPos(adjust.band) };
                    adjust.band.setPointerCapture(e.pointerId);
                    adjust.band.classList.add('is-dragging');
                }
                function onPointerMove(e) {
                    if (!adjust || !adjust.drag) return;
                    var o = adjust.overflow, d = adjust.drag;
                    // Dragging the photo down reveals more of its top → percentage goes down
                    var x = o.x > 0 ? d.from.x - ((e.clientX - d.startX) / o.x) * 100 : 50;
                    var y = o.y > 0 ? d.from.y - ((e.clientY - d.startY) / o.y) * 100 : 50;
                    setPos(adjust.band, x, y);
                }
                function onPointerUp(e) {
                    if (!adjust || !adjust.drag) return;
                    adjust.drag = null;
                    adjust.band.classList.remove('is-dragging');
                    try { adjust.band.releasePointerCapture(e.pointerId); } catch (_) {}
                }
                function onKey(e) {
                    if (!adjust) return;
                    var step = e.shiftKey ? 10 : 2, p = readPos(adjust.band);
                    var map = { ArrowUp: [0, step], ArrowDown: [0, -step], ArrowLeft: [step, 0], ArrowRight: [-step, 0] };
                    if (e.key === 'Escape') { e.preventDefault(); window.endCoverAdjust(false); return; }
                    if (e.key === 'Enter') { e.preventDefault(); window.endCoverAdjust(true); return; }
                    if (!map[e.key]) return;
                    e.preventDefault();
                    setPos(adjust.band, p.x + map[e.key][0], p.y + map[e.key][1]);
                }

                // ── Tap-to-edit menu (keeps the photo itself free of overlays) ──
                function closeCoverMenu() {
                    document.getElementById('coverPhotoMenu')?.classList.add('d-none');
                    document.removeEventListener('pointerdown', onOutsideMenu, true);
                    document.removeEventListener('keydown', onMenuKey, true);
                }
                function onOutsideMenu(e) {
                    if (!e.target.closest('#coverPhotoMenu')) closeCoverMenu();
                }
                function onMenuKey(e) {
                    if (e.key === 'Escape') { closeCoverMenu(); document.getElementById('profileCoverPhoto')?.focus(); }
                }
                window.openCoverMenu = function(e) {
                    var band = document.getElementById('profileCoverPhoto');
                    if (!band || adjust) return;
                    if (e && e.target && e.target.closest && e.target.closest('#coverPhotoMenu, #coverAdjustBar')) return;
                    if (e) e.preventDefault();
                    if (!band.classList.contains('has-cover')) {
                        document.getElementById('coverPhotoInput')?.click();
                        return;
                    }
                    var menu = document.getElementById('coverPhotoMenu');
                    if (!menu) return;
                    if (!menu.classList.contains('d-none')) { closeCoverMenu(); return; }
                    menu.classList.remove('d-none');
                    menu.querySelector('button')?.focus({ preventScroll: true });
                    // Defer so the opening tap doesn't immediately close it
                    setTimeout(function() {
                        document.addEventListener('pointerdown', onOutsideMenu, true);
                        document.addEventListener('keydown', onMenuKey, true);
                    }, 0);
                };
                window.coverMenuAction = function(action, e) {
                    if (e) { e.preventDefault(); e.stopPropagation(); }
                    closeCoverMenu();
                    if (action === 'change') document.getElementById('coverPhotoInput')?.click();
                    if (action === 'adjust') window.startCoverAdjust();
                    if (action === 'remove') window.removeProfileCover();
                };

                window.startCoverAdjust = function() {
                    var band = document.getElementById('profileCoverPhoto');
                    if (!band || !band.classList.contains('has-cover') || adjust) return;
                    var src = (band.style.backgroundImage.match(/url\(["']?(.*?)["']?\)/) || [])[1];
                    if (!src) return;
                    var img = new Image();
                    img.onload = function() {
                        adjust = { band: band, original: band.style.backgroundPosition, overflow: measureOverflow(band, img), drag: null };
                        band.classList.add('is-adjusting');
                        band.setAttribute('tabindex', '0');
                        band.setAttribute('aria-label', 'Cover photo position. Use arrow keys to move, Enter to save, Escape to cancel.');
                        document.getElementById('coverAdjustBar')?.classList.remove('d-none');
                        band.addEventListener('pointerdown', onPointerDown);
                        band.addEventListener('pointermove', onPointerMove);
                        band.addEventListener('pointerup', onPointerUp);
                        band.addEventListener('pointercancel', onPointerUp);
                        band.addEventListener('keydown', onKey);
                        band.focus({ preventScroll: true });
                    };
                    img.src = src;
                };

                window.endCoverAdjust = async function(save) {
                    if (!adjust) return;
                    var band = adjust.band, original = adjust.original;
                    band.removeEventListener('pointerdown', onPointerDown);
                    band.removeEventListener('pointermove', onPointerMove);
                    band.removeEventListener('pointerup', onPointerUp);
                    band.removeEventListener('pointercancel', onPointerUp);
                    band.removeEventListener('keydown', onKey);
                    band.classList.remove('is-adjusting', 'is-dragging');
                    band.removeAttribute('tabindex');
                    band.removeAttribute('aria-label');
                    document.getElementById('coverAdjustBar')?.classList.add('d-none');
                    adjust = null;

                    if (!save) { band.style.backgroundPosition = original; return; }
                    var p = readPos(band);
                    try {
                        var body = new FormData();
                        body.append('x', String(Math.round(p.x)));
                        body.append('y', String(Math.round(p.y)));
                        await postCover('/member/profilePage/coverImg/position', body);
                        coverNotify('success', 'Cover position saved');
                    } catch (err) {
                        band.style.backgroundPosition = original;
                        coverNotify('error', 'Could not save position', err.message);
                    }
                };

                async function postCover(endpoint, body) {
                    var token = coverCsrf();
                    if (token) body.append('token', token);
                    var res = await fetch(endpoint, {
                        method: 'POST',
                        body: body,
                        headers: Object.assign({ 'X-Requested-With': 'XMLHttpRequest' }, token ? { 'X-CSRF-TOKEN': token } : {})
                    });
                    var json = await res.json().catch(function() { return {}; });
                    if (!res.ok) throw new Error(json.message || json.error || 'Something went wrong. Please try again.');
                    return json.message || {};
                }

                window.handleProfileCoverUpload = async function(input) {
                    var file = input && input.files && input.files[0];
                    if (!file) return;
                    var band = document.getElementById('profileCoverPhoto');
                    var previousBg = band ? band.style.backgroundImage : '';
                    var hadCover = band ? band.classList.contains('has-cover') : false;

                    if (!/^image\/(jpeg|png|webp|heic|heif)$/.test(file.type) && !/\.(jpe?g|png|webp|heic)$/i.test(file.name)) {
                        coverNotify('warning', 'Unsupported file', 'Please choose a JPG, PNG, WebP or HEIC photo.');
                        input.value = '';
                        return;
                    }
                    if (file.size > 10 * 1024 * 1024) {
                        coverNotify('warning', 'File too large', 'Cover photos must be 10 MB or smaller.');
                        input.value = '';
                        return;
                    }

                    // Optimistic local preview while the upload runs
                    var previewUrl = URL.createObjectURL(file);
                    applyCover(previewUrl);
                    if (band) band.classList.add('is-uploading');

                    try {
                        var body = new FormData();
                        body.append('coverImageFile', file);
                        var data = await postCover('/member/profilePage/coverImg', body);
                        applyCover(data.coverUrl || previewUrl);
                        coverNotify('success', 'Cover photo updated', '');
                        // Let the member frame the new photo straight away
                        if (data.coverUrl) window.startCoverAdjust();
                    } catch (err) {
                        if (band) {
                            band.style.backgroundImage = previousBg;
                            band.classList.toggle('has-cover', hadCover);
                        }
                        document.getElementById('coverPhotoAddHint')?.classList.toggle('d-none', hadCover);
                        coverNotify('error', 'Upload failed', err.message);
                    } finally {
                        if (band) band.classList.remove('is-uploading');
                        URL.revokeObjectURL(previewUrl);
                        input.value = '';
                    }
                };

                window.removeProfileCover = async function() {
                    try {
                        await postCover('/member/profilePage/coverImg/remove', new FormData());
                        applyCover(null);
                        coverNotify('success', 'Cover photo removed');
                    } catch (err) {
                        coverNotify('error', 'Could not remove cover', err.message);
                    }
                };
            })();

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
