@extends ('layouts.profileBase')
@section('title', 'Profile')
@section('data-page-id', 'getProfilePage')
{{-- custom css --}}
@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/seeProfile.css?v={{ time() }}">
@endpush


@section('data-page-id', 'seeProfile')
@section('content')

    <div class="profile-container">
        <!-- Profile Hero Section -->
        <div class="profile-hero">
            <div class="hero-background"></div>
            <div class="hero-content">
                <div class="profile-avatar-wrapper">
                    <img src="/resources/images/profile/{{ $data['profilePics'] ?? 'avatarM.png' }}" alt="{{ $data['fullName'] }}" class="profile-avatar">
                    <div class="avatar-ring"></div>
                </div>
                <div class="profile-main-info">
                    <h1 class="profile-name">{{ $data['fullName'] }}</h1>
                    <div class="profile-meta">
                        <div class="meta-item">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>Lives in {{ $data['country'] ?? 'Not Specified' }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="family-code-badge">{{ $data['famCode'] }}</span>
                        </div>
                    </div>

                    <!-- Stats Bar -->
                    <div class="profile-stats-bar">
                        <div class="profile-stat-pill">
                            <i class="bi bi-chat-square-text-fill"></i>
                            <span><strong>{{ $postCount ?? 0 }}</strong> {{ ($postCount ?? 0) === 1 ? 'Post' : 'Posts' }}</span>
                        </div>
                        <div class="profile-stat-pill">
                            <i class="bi bi-images"></i>
                            <span><strong>{{ count($publicPhotos ?? []) }}</strong> {{ count($publicPhotos ?? []) === 1 ? 'Public Photo' : 'Public Photos' }}</span>
                        </div>
                        <div class="profile-stat-pill">
                            <i class="bi bi-people-fill"></i>
                            <span><strong>{{ count($relativesWithImgs ?? []) }}</strong> Immediate Family</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <!-- Left Column -->
            <div class="left-column">
                <!-- Basic Information Card -->
                <div class="info-card">
                    <h3><i class="bi bi-info-circle-fill me-2"></i>Basic Information</h3>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="{{ ($data['gender'] ?? '') == 'Female' ? 'bi bi-gender-female' : 'bi bi-gender-male' }}"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Gender</div>
                            <div class="detail-value">{{ $data['gender'] ?? 'Not Specified' }}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-calendar-event"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Date of Birth</div>
                            <div class="detail-value">{{ !empty($data['day']) ? $data['day'] . ' / ' . ($data['month'] ?? '') . ' / ' . ($data['year'] ?? '') : 'Not Specified' }}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-heart"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Relationship Status</div>
                            <div class="detail-value">{{ $data['maritalStatus'] ?? 'Not Specified' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Family Information Card -->
                <div class="info-card">
                    <h3><i class="bi bi-people-fill me-2"></i>Family Information</h3>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-person"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Father</div>
                            <div class="detail-value">{{ !empty($data['father_name']) ? $data['father_name'] : ($data['father']['fullName'] ?? 'Not Provided') }}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-person"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Mother</div>
                            <div class="detail-value">{{ !empty($data['mother_name']) ? $data['mother_name'] : ($data['mother']['fullName'] ?? 'Not Provided') }}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-person-heart"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Spouse/Partner</div>
                            <div class="detail-value">{{ !empty($data['spouse_name']) ? $data['spouse_name'] : ($data['spouse']['fullName'] ?? 'Not Provided') }}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-hash"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Family Code</div>
                            <div class="detail-value">{{ $data['famCode'] ?? 'Not Provided' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Card -->
                <div class="info-card">
                    <h3><i class="bi bi-telephone-fill me-2"></i>Contact Information</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="bi bi-phone"></i> {{ $data['mobile'] ?? 'Not Provided' }}
                        </div>
                        <div class="contact-item">
                            <i class="bi bi-envelope"></i> {{ $data['email'] ?? 'Not Provided' }}
                        </div>
                        <div class="contact-item">
                            <i class="bi bi-house"></i> {{ $data['country'] ?? 'Not Provided' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="right-column">
                <!-- Immediate Family Card -->
                <div class="info-card">
                    <h3><i class="bi bi-diagram-3-fill me-2"></i>Immediate Family</h3>
                    @if(!empty($relativesWithImgs))
                        <div class="family-members">
                            @foreach ($relativesWithImgs as $relative)
                            <a href="/allMembers/setProfile/{{ $relative['id'] }}" title="View {{ $relative['fullName'] }}'s profile">    
                                <div class="family-member">
                                    <img src="{{ $relative['img'] }}" alt="{{ $relative['fullName'] }}" class="member-avatar" onerror="this.src='/resources/images/profile/avatarM.png'">
                                    <div class="member-name text-truncate">{{ $relative['fullName'] }}</div>
                                    <div class="member-relation">{{ $relative['relationship'] }}</div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-0 py-2">No immediate family members listed yet.</p>
                    @endif
                </div>

                <!-- Additional Information Card -->
                <div class="info-card">
                    <h3><i class="bi bi-plus-circle-fill me-2"></i>Additional Information</h3>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-briefcase"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Occupation</div>
                            <div class="detail-value">{{ $data['occupation'] ?? 'Not Specified' }}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-book"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Education</div>
                            <div class="detail-value">{{ $data['education'] ?? 'Not Specified' }}</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-icon"><i class="bi bi-star"></i></div>
                        <div class="detail-content">
                            <div class="detail-label">Interests</div>
                            <div class="detail-value">{{ $data['interests'] ?? 'Not Specified' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Public Photo Gallery Section (Full Width) -->
            <div class="profile-gallery-card">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <div>
                        <h3 class="mb-1"><i class="bi bi-images me-2" style="color: var(--accent-color);"></i>Public Photo Gallery</h3>
                        <p class="text-muted small mb-0">Photos made public by {{ $data['fullName'] }} from their gallery</p>
                    </div>
                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2">
                        <i class="bi bi-camera-fill me-1"></i> {{ count($publicPhotos ?? []) }} {{ count($publicPhotos ?? []) === 1 ? 'Public Photo' : 'Public Photos' }}
                    </span>
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
                                            <i class="bi bi-calendar3 me-1"></i>{{ date('M j, Y', strtotime($photo['created_at'])) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle bg-light p-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-camera text-muted fs-2"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color: var(--text-color);">No Public Photos Shared</h6>
                        <p class="text-muted small mb-0">This member has not shared any gallery photos publicly yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Lightbox for seeProfile Public Photos -->
    <div id="seeProfileLightbox" class="see-profile-lightbox" onclick="closeSeeProfileLightbox(event)">
        <span class="see-profile-lightbox-close" onclick="closeSeeProfileLightbox()">&times;</span>
        <img class="see-profile-lightbox-content" id="seeProfileLightboxImg" alt="Enlarged Photo">
        <div id="seeProfileLightboxCaption" class="see-profile-lightbox-caption"></div>
    </div>

    <script>
        const seeProfilePublicPhotos = @json(array_values($publicPhotos ?? []));

        function openSeeProfileLightbox(index) {
            if (!seeProfilePublicPhotos || index < 0 || index >= seeProfilePublicPhotos.length) return;
            const photo = seeProfilePublicPhotos[index];
            const modal = document.getElementById('seeProfileLightbox');
            const img = document.getElementById('seeProfileLightboxImg');
            const caption = document.getElementById('seeProfileLightboxCaption');

            if (modal && img) {
                img.src = '/resources/images/post/' + encodeURIComponent(photo.img);
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
    </script>

@endsection