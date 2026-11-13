@extends ('layouts.profileBase')
@section('title', 'Profile')
@section('data-page-id', 'getProfilePage')
{{-- custom css --}}
@push('styles')
    <link rel="preload" href="/public/css/outfit.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="/public/css/outfit.css"></noscript>
    <link rel="stylesheet" href="/public/css/seeProfile.css?v={{ time() }}">
@endpush


@section('data-page-id', 'seeProfile')
@section('content')

    <div class="profile-container">
        @php
            $hasCover = !empty($cover['url']);
            $canEdit = !empty($canEditCover);
            $coverUrl = $cover['url'] ?? '';
            $coverX = isset($cover['x']) ? (int)$cover['x'] : 50;
            $coverY = isset($cover['y']) ? (int)$cover['y'] : 50;
            $targetMemberId = (string)($viewedMemberId ?? ($data['id'] ?? ''));
            $viewerIdForProfile = (string)($_SESSION['auth']['id'] ?? ($_SESSION['id'] ?? ''));
            $canSeeProfileFamCode = ($viewerIdForProfile !== '' && $viewerIdForProfile === $targetMemberId)
                || \App\controller\BaseController::viewerCanSeeFamilyCode((string)($data['primaryFamCode'] ?? ($data['famCode'] ?? '')), $viewerIdForProfile)
                || \App\controller\BaseController::viewerCanSeeFamilyCode((string)($data['maternalFamCode'] ?? ($data['otherFamCode'] ?? '')), $viewerIdForProfile);
            $heroRawPrimary = ltrim((string)($data['primaryFamCode'] ?? ($data['famCode'] ?? '')), '#');
            $heroRawBirth = ltrim((string)($data['maternalFamCode'] ?? ($data['otherFamCode'] ?? '')), '#');
            $heroPrimaryCode = ($viewerIdForProfile === $targetMemberId || \App\controller\BaseController::viewerCanSeeFamilyCode($heroRawPrimary, $viewerIdForProfile)) ? $heroRawPrimary : '';
            $heroBirthCode = ($viewerIdForProfile === $targetMemberId || \App\controller\BaseController::viewerCanSeeFamilyCode($heroRawBirth, $viewerIdForProfile)) ? $heroRawBirth : '';
            $heroHasDualCodes = ($heroPrimaryCode !== '' && $heroBirthCode !== '' && strtoupper($heroBirthCode) !== strtoupper($heroPrimaryCode));
            $heroMarriedCode = $heroHasDualCodes ? $heroPrimaryCode : '';
            $heroEffectiveBirthCode = $heroHasDualCodes ? $heroBirthCode : ($heroPrimaryCode !== '' ? $heroPrimaryCode : $heroBirthCode);
            $relationshipChipLabel = !empty($data['relationship_to_viewer'])
                ? (string)$data['relationship_to_viewer']
                : ($viewerIdForProfile === $targetMemberId ? 'Your profile' : 'Connected kin · family branch');
        @endphp

        <!-- Profile Header Card (Artboards 13 & 14: Cover Banner + Identity Row + 4 Profile Tabs) -->
        <div class="sp-header-card">
            <div class="profile-hero {{ $hasCover ? 'has-cover' : '' }} {{ $canEdit ? 'can-edit-cover' : '' }}" id="seeProfileHero"
                 @if($hasCover) style="background-image: url('{{ $coverUrl }}'); background-position: {{ $coverX }}% {{ $coverY }}%;" @endif>
                <div class="hero-background" id="seeProfileHeroBg"
                     @if($hasCover) style="background-image: url('{{ $coverUrl }}'); background-position: {{ $coverX }}% {{ $coverY }}%;" @endif></div>
                <div class="hero-scrim"></div>

                @if(!$hasCover)
                    <div class="sp-cover-placeholder-label">
                        <i class="bi bi-image"></i>
                        <span>Family cover photo</span>
                    </div>
                @endif

                @if($canEdit)
                    <!-- Hidden file upload input for cover photo -->
                    <input type="file" id="seeCoverInput" accept="image/jpeg,image/png,image/webp,image/heic" hidden onchange="handleSeeProfileCoverUpload(this)">

                    <!-- Controls on top right -->
                    <div class="see-profile-cover-controls" id="seeProfileCoverControls">
                        @if($hasCover)
                            <button type="button" class="btn-see-cover-control" id="seeProfileCoverRepositionBtn" onclick="startSeeProfileCoverAdjust(event)" title="Reposition cover photo">
                                <i class="bi bi-arrows-move me-1"></i> Reposition
                            </button>
                            <button type="button" class="btn-see-cover-control btn-icon-only" id="seeProfileCoverMenuBtn" onclick="toggleSeeProfileCoverMenu(event)" title="Cover options" aria-label="Cover photo options">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div class="see-cover-dropdown d-none" id="seeProfileCoverDropdown" role="menu">
                                <button type="button" role="menuitem" onclick="seeProfileCoverAction('change', event)"><i class="bi bi-image"></i> Change photo</button>
                                <button type="button" role="menuitem" onclick="seeProfileCoverAction('adjust', event)"><i class="bi bi-arrows-move"></i> Reposition</button>
                                <button type="button" role="menuitem" class="is-danger" onclick="seeProfileCoverAction('remove', event)"><i class="bi bi-trash3"></i> Remove</button>
                            </div>
                        @else
                            <button type="button" class="btn-see-cover-control" id="seeProfileCoverAddBtn" onclick="seeProfileCoverAction('change', event)" title="Add cover photo">
                                <i class="bi bi-image me-1"></i> Add Cover Photo
                            </button>
                        @endif
                    </div>

                    <!-- Adjust Mode Bar (Drag or Arrow Keys) -->
                    <div class="see-cover-adjust-bar d-none" id="seeProfileCoverAdjustBar">
                        <span class="see-cover-adjust-hint">
                            <i class="bi bi-arrows-move me-2"></i> Drag to reposition &bull; Arrow keys also move
                        </span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-cover-pill btn-cover-cancel" id="seeCoverCancelBtn" onclick="endSeeProfileCoverAdjust(false)">Cancel</button>
                            <button type="button" class="btn-cover-pill btn-cover-save" id="seeCoverSaveBtn" onclick="endSeeProfileCoverAdjust(true)">Save</button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- White Identity & Action Strip -->
            <div class="sp-identity-strip">
                <div class="hero-content">
                    <div class="profile-avatar-wrapper" style="cursor: pointer;" title="Click to enlarge profile photo" role="button" tabindex="0" aria-label="View {{ $data['fullName'] }}'s profile photo in full size">
                        <img src="/resources/images/profile/{{ $data['profilePics'] ?? 'avatarM.png' }}" 
                             alt="{{ $data['fullName'] }}" 
                             class="profile-avatar profile-avatar-interactive"
                             data-profile-expand="true"
                             data-profile-name="{{ $data['fullName'] }}"
                             data-profile-subtitle="{{ $canSeeProfileFamCode && !empty($data['famCode']) ? ('Family Member • ' . $data['famCode']) : 'Connected Kin' }}"
                             data-can-edit="false"
                             onerror="this.src='/resources/images/profile/avatarM.png'">
                        <div class="avatar-ring d-none"></div>
                    </div>

                    <div class="profile-main-info flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                            <h1 class="profile-name mb-0">{{ $data['fullName'] }}</h1>
                            <span class="sp-relation-chip">{{ $relationshipChipLabel }}</span>
                        </div>
                        <div class="profile-meta flex-wrap">
                            <div class="meta-item">
                                <i class="bi bi-geo-alt"></i>
                                <span>Lives in {{ $data['country'] ?? 'Not Specified' }}</span>
                            </div>
                            <span class="sp-meta-dot">&middot;</span>
                            <div class="meta-item d-inline-flex align-items-center gap-2 flex-wrap">
                                @if($heroMarriedCode !== '')
                                    <span class="family-code-badge" title="{{ __('profile.married_family_code', 'Married Code') }}">{{ __('profile.married_family_code', 'Married Code') }}: {{ $heroMarriedCode }}</span>
                                @endif
                                @if($heroEffectiveBirthCode !== '')
                                    <span class="family-code-badge" title="{{ __('profile.birth_family_code', 'Birth Code') }}">{{ __('profile.birth_family_code', 'Birth Code') }}: {{ $heroEffectiveBirthCode }}</span>
                                @endif
                                @if($heroMarriedCode === '' && $heroEffectiveBirthCode === '')
                                    <span class="family-code-badge" title="Connected Kin"><i class="bi bi-people me-1"></i>Connected Kin</span>
                                @endif
                            </div>
                            <span class="sp-meta-dot">&middot;</span>
                            <span class="sp-meta-stat">{{ count($relativesWithImgs ?? []) }} immediate family &middot; {{ count($publicPhotos ?? []) }} {{ count($publicPhotos ?? []) === 1 ? 'public photo' : 'public photos' }}</span>
                        </div>
                    </div>

                    <!-- Right-Aligned Action Pills (Artboards 13 & 14) -->
                    <div class="sp-header-actions">
                        <a href="/kin-whispers?recipient_id={{ urlencode($targetMemberId) }}" class="sp-action-pill sp-action-pill--primary" title="Send a private Whisper to {{ $data['fullName'] }}">
                            <i class="bi bi-lock"></i>
                            <span>Whisper</span>
                        </a>
                        <span class="sp-action-pill sp-action-pill--connected" title="Connected family member">
                            <i class="bi bi-check2"></i>
                            <span>Connected kin</span>
                        </span>
                        <a href="/organogram/{{ urlencode($targetMemberId) }}" class="sp-action-pill sp-action-pill--outline" title="Locate {{ $data['fullName'] }} on the interactive family tree">
                            <i class="bi bi-diagram-3"></i>
                            <span>Show in tree</span>
                        </a>
                    </div>
                </div>

                <!-- 4 Profile Section Tabs (About | Photos | Family | Timeline) -->
                <div class="sp-tabs-bar" role="tablist" aria-label="Profile sections">
                    <a href="#spSectionAbout" class="sp-tab-link active" data-sp-tab="about">About</a>
                    <a href="#spSectionPhotos" class="sp-tab-link" data-sp-tab="photos">Photos ({{ count($publicPhotos ?? []) }})</a>
                    <a href="#spSectionFamily" class="sp-tab-link" data-sp-tab="family">Family ({{ count($relativesWithImgs ?? []) }})</a>
                    <a href="#spSectionTimeline" class="sp-tab-link" data-sp-tab="timeline">Timeline</a>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <!-- Left Column (Basic info, Family info, Kin-only Contact, Public photo gallery, Timeline) -->
            <div class="left-column">
                <!-- Basic Information Card -->
                <div class="info-card" id="spSectionAbout">
                    <h3>Basic information</h3>
                    <div class="sp-kv-grid">
                        <div class="sp-kv-cell">
                            <div class="detail-label">Gender</div>
                            <div class="detail-value">{{ $data['gender'] ?? 'Not Specified' }}</div>
                        </div>
                        <div class="sp-kv-cell">
                            <div class="detail-label">Date of Birth</div>
                            <div class="detail-value">{{ !empty($data['day']) ? ($data['day'] . ' / ' . ($data['month'] ?? '') . ((($data['show_birth_year'] ?? 'off') === 'on' && !empty($data['year'])) ? ' / ' . $data['year'] : '')) : 'Not Specified' }}</div>
                        </div>
                        <div class="sp-kv-cell">
                            <div class="detail-label">Relationship status</div>
                            <div class="detail-value">{{ $data['maritalStatus'] ?? 'Not Specified' }}</div>
                        </div>
                        <div class="sp-kv-cell">
                            <div class="detail-label">Lives in</div>
                            <div class="detail-value">{{ $data['country'] ?? 'Not Specified' }}</div>
                        </div>
                        <div class="sp-kv-cell">
                            <div class="detail-label">Occupation</div>
                            <div class="detail-value">{{ $data['occupation'] ?? 'Not Specified' }}</div>
                        </div>
                        <div class="sp-kv-cell">
                            <div class="detail-label">Education</div>
                            <div class="detail-value">{{ $data['education'] ?? 'Not Specified' }}</div>
                        </div>
                        <div class="sp-kv-cell sp-kv-cell--full">
                            <div class="detail-label">Interests</div>
                            <div class="detail-value">{{ $data['interests'] ?? 'Not Specified' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Family Information Card -->
                <div class="info-card">
                    <h3>Family information</h3>
                    <div class="sp-kv-grid">
                        <div class="sp-kv-cell">
                            <div class="detail-label">Father</div>
                            <div class="detail-value">{{ !empty($data['father_name']) ? $data['father_name'] : ($data['father']['fullName'] ?? 'Not Provided') }}</div>
                        </div>
                        <div class="sp-kv-cell">
                            <div class="detail-label">Mother</div>
                            <div class="detail-value">{{ !empty($data['mother_name']) ? $data['mother_name'] : ($data['mother']['fullName'] ?? 'Not Provided') }}</div>
                        </div>
                        <div class="sp-kv-cell">
                            <div class="detail-label">Spouse / partner</div>
                            <div class="detail-value">{{ !empty($data['spouse_name']) ? $data['spouse_name'] : ($data['spouse']['fullName'] ?? 'Not Provided') }}</div>
                        </div>
                        @if($heroMarriedCode !== '')
                            <div class="sp-kv-cell">
                                <div class="detail-label">{{ __('profile.married_family_code', 'Married Code') }}</div>
                                <div class="detail-value"><span class="sp-code-pill">{{ $heroMarriedCode }}</span></div>
                            </div>
                        @endif
                        @if($heroEffectiveBirthCode !== '')
                            <div class="sp-kv-cell">
                                <div class="detail-label">{{ __('profile.birth_family_code', 'Birth Code') }}</div>
                                <div class="detail-value"><span class="sp-code-pill">{{ $heroEffectiveBirthCode }}</span></div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Contact Card (Artboards 13 & 14: Marked as kin-only) -->
                <div class="info-card">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <h3 class="mb-0">Contact</h3>
                        <span class="sp-kin-lock-badge">
                            <i class="bi bi-lock"></i>
                            <span>Only connected kin can see this</span>
                        </span>
                    </div>
                    <div class="sp-contact-grid">
                        <div class="sp-contact-box">
                            <div class="sp-contact-icon"><i class="bi bi-telephone"></i></div>
                            <div class="min-w-0">
                                <div class="sp-contact-label">Mobile</div>
                                <div class="sp-contact-val text-truncate">{{ $data['mobile'] ?? 'Not Provided' }}</div>
                            </div>
                        </div>
                        <div class="sp-contact-box">
                            <div class="sp-contact-icon"><i class="bi bi-envelope"></i></div>
                            <div class="min-w-0">
                                <div class="sp-contact-label">Email</div>
                                <div class="sp-contact-val text-truncate" title="{{ $data['email'] ?? 'Not Provided' }}">{{ $data['email'] ?? 'Not Provided' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Public Photo Gallery Card -->
                <div class="info-card" id="spSectionPhotos">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h3 class="mb-0">Public photo gallery</h3>
                        <span class="sp-gallery-count-link">{{ count($publicPhotos ?? []) }} {{ count($publicPhotos ?? []) === 1 ? 'photo' : 'photos' }}</span>
                    </div>

                    @if(!empty($publicPhotos))
                        <div class="public-gallery-grid">
                            @foreach ($publicPhotos as $idx => $photo)
                                <div class="public-gallery-item" onclick="openSeeProfileLightbox({{ $idx }})">
                                    <img 
                                        src="/resources/images/post/{{ rawurlencode($photo['img']) }}" 
                                        alt="{{ $photo['caption'] ?? $photo['img'] }}" 
                                        class="public-gallery-img"
                                        loading="lazy"
                                        onerror="this.closest('.public-gallery-item').style.display='none';"
                                    >
                                    <div class="public-gallery-meta">
                                        <p class="public-gallery-caption" title="{{ $photo['caption'] ?? 'Shared memory' }}">
                                            {{ !empty($photo['caption']) ? $photo['caption'] : 'Shared memory' }}
                                        </p>
                                        @if(!empty($photo['created_at']))
                                            <span class="public-gallery-date">
                                                {{ date('j M Y', strtotime($photo['created_at'])) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="sp-empty-gallery-placeholder">
                            <i class="bi bi-images"></i>
                            <span>No public gallery photos shared yet.</span>
                        </div>
                    @endif
                </div>

                <!-- Then & Now Timeline Section -->
                <div id="spSectionTimeline">
                    @includeIf('member.includes.thenNowTimeline', ['memberId' => $targetMemberId, 'memberName' => (string)($data['fullName'] ?? ''), 'tnSuffix' => 'seen'])
                </div>
            </div>

            <!-- Right Column (How you're related + Immediate family + Coming up) -->
            <div class="right-column">
                <!-- Deep Burgundy "How you're related" Path Card (Artboards 13 & 14) -->
                <div class="sp-kinship-path-card">
                    <div class="sp-kinship-eyebrow">HOW YOU'RE RELATED</div>
                    @php
                        $viewerDisplayName = trim((string)($_SESSION['fullName'] ?? ($_SESSION['firstName'] ?? 'You')));
                        $viewerInitials = strtoupper(substr($viewerDisplayName, 0, 1)) ?: 'Y';
                        $targetInitials = strtoupper(substr((string)($data['firstName'] ?? ($data['fullName'] ?? 'K')), 0, 1)) ?: 'K';
                        $bridgeParentName = !empty($data['father_name'])
                            ? (string)$data['father_name']
                            : (!empty($data['mother_name']) ? (string)$data['mother_name'] : 'Shared family branch');
                        $bridgeInitials = strtoupper(substr($bridgeParentName, 0, 1)) ?: 'F';
                    @endphp
                    <div class="sp-kinship-steps">
                        <div class="sp-kinship-step">
                            <div class="sp-kinship-node">{{ $viewerInitials }}</div>
                            <div class="min-w-0">
                                <div class="sp-kinship-name text-truncate">You ({{ $viewerDisplayName }})</div>
                            </div>
                        </div>
                        <div class="sp-kinship-connector"></div>
                        <div class="sp-kinship-step">
                            <div class="sp-kinship-node">{{ $bridgeInitials }}</div>
                            <div class="min-w-0">
                                <div class="sp-kinship-name text-truncate">{{ $bridgeParentName }}</div>
                                <div class="sp-kinship-sub">Family branch connection</div>
                            </div>
                        </div>
                        <div class="sp-kinship-connector"></div>
                        <div class="sp-kinship-step">
                            <div class="sp-kinship-node sp-kinship-node--active">{{ $targetInitials }}</div>
                            <div class="min-w-0">
                                <div class="sp-kinship-name text-truncate">{{ $data['fullName'] }}</div>
                                <div class="sp-kinship-sub sp-kinship-sub--highlight">{{ $relationshipChipLabel }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Immediate Family Card -->
                <div class="info-card" id="spSectionFamily">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0">Immediate family <span class="sp-muted-count">&middot; {{ count($relativesWithImgs ?? []) }}</span></h3>
                        <a href="/organogram/{{ urlencode($targetMemberId) }}" class="sp-card-action-link">Full tree</a>
                    </div>
                    @if(!empty($relativesWithImgs))
                        <div class="sp-family-list">
                            @foreach ($relativesWithImgs as $relative)
                                @if(!empty($relative['isLinked']) && !empty($relative['id']))
                                    <a href="/allMembers/seeProfile/{{ $relative['id'] }}" class="family-member-link sp-family-row text-decoration-none" title="View {{ $relative['fullName'] }}'s profile">
                                        <img src="{{ $relative['img'] }}" alt="{{ $relative['fullName'] }}" class="sp-family-avatar" onerror="this.src='/resources/images/profile/avatarM.png'">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="sp-family-name text-truncate" title="{{ $relative['fullName'] }}">{{ $relative['fullName'] }}</div>
                                            <div class="sp-family-rel">{{ $relative['relationship'] }}</div>
                                        </div>
                                        <i class="bi bi-chevron-right sp-family-chevron"></i>
                                    </a>
                                @else
                                    <div class="sp-family-row unlinked-member" title="{{ $relative['fullName'] }} (Not yet on FamilyPlatform)">
                                        <img src="{{ $relative['img'] }}" alt="{{ $relative['fullName'] }}" class="sp-family-avatar" onerror="this.src='/resources/images/profile/avatarM.png'">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="sp-family-name text-truncate" title="{{ $relative['fullName'] }}">{{ $relative['fullName'] }}</div>
                                            <div class="sp-family-rel">{{ $relative['relationship'] }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-0 py-2">No immediate family members listed yet.</p>
                    @endif
                </div>

                <!-- Coming Up Card (Artboard 13) -->
                <div class="info-card">
                    <h3 class="mb-3">Coming up</h3>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="sp-date-badge">
                            <span class="sp-date-day">{{ !empty($data['day']) ? $data['day'] : '🎉' }}</span>
                            <span class="sp-date-mon">{{ !empty($data['month']) ? strtoupper(substr((string)$data['month'], 0, 3)) : 'CEL' }}</span>
                        </div>
                        <div class="min-w-0">
                            <div class="fw-bold text-dark">{{ $data['firstName'] ?? $data['fullName'] }}'s birthday</div>
                            <div class="text-muted small">Celebrate with a custom family keepsake</div>
                        </div>
                    </div>
                    <a href="/poster-studio?app=familyplatform" class="sp-poster-cta-btn">
                        <i class="bi bi-stars"></i>
                        <span>Create a birthday poster</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox for seeProfile Public Photos -->
    <div id="seeProfileLightbox" class="see-profile-lightbox" onclick="closeSeeProfileLightbox(event)">
        <span class="see-profile-lightbox-close" onclick="closeSeeProfileLightbox()">&times;</span>
        <img class="see-profile-lightbox-content" id="seeProfileLightboxImg" alt="Enlarged Photo">
        <div id="seeProfileLightboxCaption" class="see-profile-lightbox-caption"></div>
    </div>

    <script nonce="{{ csp_nonce($nonce ?? null) }}">
        const seeProfilePublicPhotos = @json(array_values($publicPhotos ?? []));

        function getSafeSeeProfileImgUrl(img) {
            if (!img || typeof img !== 'string') return '';
            let clean = img.trim();
            if (clean.startsWith('http://') || clean.startsWith('https://') || clean.startsWith('blob:') || clean.startsWith('data:')) {
                return clean;
            }
            const parts = clean.split(/[/\\]/);
            const basename = parts[parts.length - 1];
            return '/resources/images/post/' + encodeURIComponent(basename);
        }

        function openSeeProfileLightbox(index) {
            if (!seeProfilePublicPhotos || index < 0 || index >= seeProfilePublicPhotos.length) return;
            const photo = seeProfilePublicPhotos[index];
            const modal = document.getElementById('seeProfileLightbox');
            const img = document.getElementById('seeProfileLightboxImg');
            const caption = document.getElementById('seeProfileLightboxCaption');

            if (modal && img) {
                img.src = getSafeSeeProfileImgUrl(photo.img);
                if (caption) {
                    caption.textContent = photo.caption || (photo.created_at ? 'Posted on ' + photo.created_at : '');
                }
                modal.classList.add('active');
            }
        }

        function closeSeeProfileLightbox(e) {
            if (e && e.target && e.target.tagName === 'IMG') return;
            const modal = document.getElementById('seeProfileLightbox');
            if (modal) modal.classList.remove('active');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('seeProfileLightbox');
                if (modal) modal.classList.remove('active');
            }
        });

        /* ── seeProfile Cover Photo Management & Adjustment ── */
        (function() {
            const memberId = '{{ (string)($viewedMemberId ?? ($data['id'] ?? '')) }}';
            const canEdit = @json(!empty($canEditCover));
            if (!canEdit) return;

            let adjust = null;

            function seeCoverCsrf() {
                return document.querySelector('meta[name="csrf-token"]')?.content
                    || document.querySelector('input[name="token"]')?.value
                    || @json($_SESSION['token'] ?? '');
            }

            function seeCoverNotify(type, title, text) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        icon: type,
                        title: title,
                        text: text || '',
                        timer: 2500,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else if (typeof toastr !== 'undefined' && toastr[type]) {
                    toastr[type](text || title);
                } else {
                    console.log('[' + type.toUpperCase() + '] ' + title + (text ? ': ' + text : ''));
                }
            }

            function applySeeCover(url, pos) {
                const hero = document.getElementById('seeProfileHero');
                const heroBg = document.getElementById('seeProfileHeroBg');
                const hasUrl = Boolean(url);
                if (hero) {
                    hero.classList.toggle('has-cover', hasUrl);
                    hero.style.backgroundImage = hasUrl ? `url("${url}")` : '';
                    hero.style.backgroundPosition = hasUrl ? (pos ? `${pos.x}% ${pos.y}%` : '50% 50%') : '';
                }
                if (heroBg) {
                    heroBg.style.backgroundImage = hasUrl ? `url("${url}")` : '';
                    heroBg.style.backgroundPosition = hasUrl ? (pos ? `${pos.x}% ${pos.y}%` : '50% 50%') : '';
                }

                // Update controls dynamically
                const controls = document.getElementById('seeProfileCoverControls');
                if (controls) {
                    if (hasUrl) {
                        controls.innerHTML = `
                            <button type="button" class="btn-see-cover-control" id="seeProfileCoverRepositionBtn" onclick="startSeeProfileCoverAdjust(event)" title="Reposition cover photo">
                                <i class="bi bi-arrows-move me-1"></i> Reposition
                            </button>
                            <button type="button" class="btn-see-cover-control btn-icon-only" id="seeProfileCoverMenuBtn" onclick="toggleSeeProfileCoverMenu(event)" title="Cover options" aria-label="Cover photo options">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <div class="see-cover-dropdown d-none" id="seeProfileCoverDropdown" role="menu">
                                <button type="button" role="menuitem" onclick="seeProfileCoverAction('change', event)"><i class="bi bi-image"></i> Change photo</button>
                                <button type="button" role="menuitem" onclick="seeProfileCoverAction('adjust', event)"><i class="bi bi-arrows-move"></i> Reposition</button>
                                <button type="button" role="menuitem" class="is-danger" onclick="seeProfileCoverAction('remove', event)"><i class="bi bi-trash3"></i> Remove</button>
                            </div>
                        `;
                    } else {
                        controls.innerHTML = `
                            <button type="button" class="btn-see-cover-control" id="seeProfileCoverAddBtn" onclick="seeProfileCoverAction('change', event)" title="Add cover photo">
                                <i class="bi bi-image me-1"></i> Add Cover Photo
                            </button>
                        `;
                    }
                }
            }

            function readPos(el) {
                const parts = (el.style.backgroundPosition || '50% 50%').split(' ');
                return {
                    x: parseFloat(parts[0]) || 50,
                    y: parseFloat(parts[1] !== undefined ? parts[1] : parts[0]) || 50
                };
            }

            function setPos(el, x, y) {
                x = Math.max(0, Math.min(100, x));
                y = Math.max(0, Math.min(100, y));
                const posStr = `${x.toFixed(1)}% ${y.toFixed(1)}%`;
                el.style.backgroundPosition = posStr;
                const hero = document.getElementById('seeProfileHero');
                if (hero) hero.style.backgroundPosition = posStr;
            }

            function measureOverflow(el, img) {
                const w = el.clientWidth || 800;
                const h = el.clientHeight || 300;
                const scale = Math.max(w / (img.naturalWidth || w), h / (img.naturalHeight || h));
                return {
                    x: (img.naturalWidth || w) * scale - w,
                    y: (img.naturalHeight || h) * scale - h
                };
            }

            function onPointerDown(e) {
                if (!adjust || e.target.closest('button')) return;
                e.preventDefault();
                adjust.drag = {
                    startX: e.clientX,
                    startY: e.clientY,
                    from: readPos(adjust.target)
                };
                adjust.hero.setPointerCapture(e.pointerId);
                adjust.hero.classList.add('is-dragging');
            }

            function onPointerMove(e) {
                if (!adjust || !adjust.drag) return;
                const o = adjust.overflow;
                const d = adjust.drag;
                const x = o.x > 0 ? d.from.x - ((e.clientX - d.startX) / o.x) * 100 : 50;
                const y = o.y > 0 ? d.from.y - ((e.clientY - d.startY) / o.y) * 100 : 50;
                setPos(adjust.target, x, y);
            }

            function onPointerUp(e) {
                if (!adjust || !adjust.drag) return;
                adjust.drag = null;
                adjust.hero.classList.remove('is-dragging');
                try { adjust.hero.releasePointerCapture(e.pointerId); } catch (_) {}
            }

            function onKey(e) {
                if (!adjust) return;
                const step = e.shiftKey ? 10 : 2;
                const p = readPos(adjust.target);
                const map = {
                    ArrowUp: [0, step],
                    ArrowDown: [0, -step],
                    ArrowLeft: [step, 0],
                    ArrowRight: [-step, 0]
                };
                if (e.key === 'Escape') {
                    e.preventDefault();
                    window.endSeeProfileCoverAdjust(false);
                    return;
                }
                if (e.key === 'Enter') {
                    e.preventDefault();
                    window.endSeeProfileCoverAdjust(true);
                    return;
                }
                if (!map[e.key]) return;
                e.preventDefault();
                setPos(adjust.target, p.x + map[e.key][0], p.y + map[e.key][1]);
            }

            function closeCoverMenu() {
                const menu = document.getElementById('seeProfileCoverDropdown');
                if (menu) menu.classList.add('d-none');
                document.removeEventListener('pointerdown', onOutsideMenu, true);
                document.removeEventListener('keydown', onMenuKey, true);
            }

            function onOutsideMenu(e) {
                if (!e.target.closest('#seeProfileCoverDropdown') && !e.target.closest('#seeProfileCoverMenuBtn')) {
                    closeCoverMenu();
                }
            }

            function onMenuKey(e) {
                if (e.key === 'Escape') closeCoverMenu();
            }

            window.toggleSeeProfileCoverMenu = function(e) {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                const menu = document.getElementById('seeProfileCoverDropdown');
                if (!menu) return;
                if (!menu.classList.contains('d-none')) {
                    closeCoverMenu();
                    return;
                }
                menu.classList.remove('d-none');
                setTimeout(() => {
                    document.addEventListener('pointerdown', onOutsideMenu, true);
                    document.addEventListener('keydown', onMenuKey, true);
                }, 0);
            };

            window.seeProfileCoverAction = function(action, e) {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                closeCoverMenu();
                if (action === 'adjust') {
                    window.startSeeProfileCoverAdjust();
                } else if (action === 'change') {
                    document.getElementById('seeCoverInput')?.click();
                } else if (action === 'remove') {
                    window.removeSeeProfileCover();
                }
            };

            window.startSeeProfileCoverAdjust = function(e) {
                if (e) { e.preventDefault(); e.stopPropagation(); }
                closeCoverMenu();
                const hero = document.getElementById('seeProfileHero');
                const heroBg = document.getElementById('seeProfileHeroBg');
                if (!hero || !heroBg || adjust) return;

                const bgStyle = heroBg.style.backgroundImage || hero.style.backgroundImage || '';
                const match = bgStyle.match(/url\(["']?(.*?)["']?\)/);
                const src = match ? match[1] : '';
                if (!src) return;

                const img = new Image();
                img.onload = function() {
                    adjust = {
                        hero: hero,
                        target: heroBg,
                        original: heroBg.style.backgroundPosition || hero.style.backgroundPosition || '50% 50%',
                        overflow: measureOverflow(hero, img),
                        drag: null
                    };

                    hero.classList.add('is-adjusting');
                    document.getElementById('seeProfileCoverControls')?.classList.add('d-none');
                    document.getElementById('seeProfileCoverAdjustBar')?.classList.remove('d-none');

                    hero.addEventListener('pointerdown', onPointerDown);
                    hero.addEventListener('pointermove', onPointerMove);
                    hero.addEventListener('pointerup', onPointerUp);
                    hero.addEventListener('pointercancel', onPointerUp);
                    window.addEventListener('keydown', onKey);
                };
                img.src = src;
            };

            window.endSeeProfileCoverAdjust = async function(save) {
                if (!adjust) return;
                const hero = adjust.hero;
                const heroBg = adjust.target;
                const original = adjust.original;

                hero.removeEventListener('pointerdown', onPointerDown);
                hero.removeEventListener('pointermove', onPointerMove);
                hero.removeEventListener('pointerup', onPointerUp);
                hero.removeEventListener('pointercancel', onPointerUp);
                window.removeEventListener('keydown', onKey);

                hero.classList.remove('is-adjusting', 'is-dragging');
                document.getElementById('seeProfileCoverAdjustBar')?.classList.add('d-none');
                document.getElementById('seeProfileCoverControls')?.classList.remove('d-none');

                const saveBtn = document.getElementById('seeCoverSaveBtn');

                if (!save) {
                    heroBg.style.backgroundPosition = original;
                    hero.style.backgroundPosition = original;
                    adjust = null;
                    return;
                }

                const pos = readPos(heroBg);
                adjust = null;

                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';
                }

                try {
                    const token = seeCoverCsrf();
                    const body = new FormData();
                    body.append('x', String(Math.round(pos.x)));
                    body.append('y', String(Math.round(pos.y)));
                    body.append('memberId', memberId);
                    if (token) body.append('token', token);

                    const res = await fetch('/member/profilePage/coverImg/position', {
                        method: 'POST',
                        body: body,
                        headers: Object.assign({ 'X-Requested-With': 'XMLHttpRequest' }, token ? { 'X-CSRF-TOKEN': token } : {})
                    });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(json.message || json.error || 'Could not save cover position.');

                    seeCoverNotify('success', 'Cover position saved');
                } catch (err) {
                    heroBg.style.backgroundPosition = original;
                    hero.style.backgroundPosition = original;
                    seeCoverNotify('error', 'Could not save position', err.message);
                } finally {
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = 'Save';
                    }
                }
            };

            window.handleSeeProfileCoverUpload = async function(input) {
                const file = input && input.files && input.files[0];
                if (!file) return;

                if (!/^image\/(jpeg|png|webp|heic|heif)$/.test(file.type) && !/\.(jpe?g|png|webp|heic)$/i.test(file.name)) {
                    seeCoverNotify('warning', 'Unsupported file', 'Please choose a JPG, PNG, WebP or HEIC photo.');
                    input.value = '';
                    return;
                }
                if (file.size > 10 * 1024 * 1024) {
                    seeCoverNotify('warning', 'File too large', 'Cover photos must be 10 MB or smaller.');
                    input.value = '';
                    return;
                }

                const hero = document.getElementById('seeProfileHero');
                const previousBg = hero ? hero.style.backgroundImage : '';
                const previewUrl = URL.createObjectURL(file);
                applySeeCover(previewUrl);

                if (hero) hero.classList.add('is-uploading');

                try {
                    const token = seeCoverCsrf();
                    const body = new FormData();
                    body.append('coverImageFile', file);
                    body.append('memberId', memberId);
                    if (token) body.append('token', token);

                    const res = await fetch('/member/profilePage/coverImg', {
                        method: 'POST',
                        body: body,
                        headers: Object.assign({ 'X-Requested-With': 'XMLHttpRequest' }, token ? { 'X-CSRF-TOKEN': token } : {})
                    });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(json.message || json.error || 'Cover photo upload failed.');

                    const data = json.message || {};
                    const finalUrl = data.coverUrl || previewUrl;
                    applySeeCover(finalUrl);
                    seeCoverNotify('success', 'Cover photo updated');

                    setTimeout(() => {
                        window.startSeeProfileCoverAdjust();
                    }, 200);
                } catch (err) {
                    applySeeCover(previousBg.replace(/^url\(["']?/, '').replace(/["']?\)$/, ''));
                    seeCoverNotify('error', 'Upload failed', err.message);
                } finally {
                    if (hero) hero.classList.remove('is-uploading');
                    URL.revokeObjectURL(previewUrl);
                    input.value = '';
                }
            };

            window.removeSeeProfileCover = async function() {
                if (!confirm('Are you sure you want to remove your cover photo?')) return;
                try {
                    const token = seeCoverCsrf();
                    const body = new FormData();
                    body.append('memberId', memberId);
                    if (token) body.append('token', token);

                    const res = await fetch('/member/profilePage/coverImg/remove', {
                        method: 'POST',
                        body: body,
                        headers: Object.assign({ 'X-Requested-With': 'XMLHttpRequest' }, token ? { 'X-CSRF-TOKEN': token } : {})
                    });
                    const json = await res.json().catch(() => ({}));
                    if (!res.ok) throw new Error(json.message || json.error || 'Could not remove cover photo.');

                    applySeeCover('');
                    seeCoverNotify('success', 'Cover photo removed');
                } catch (err) {
                    seeCoverNotify('error', 'Could not remove cover', err.message);
                }
            };
        })();
    </script>

    @includeIf('member.modals.thenNowComparisonModal')

@endsection