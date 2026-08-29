@extends('layouts.profileBase')
@section('title', 'Photo Gallery')
@section('data-page-id', 'images')
@push('styles')
    <link rel="stylesheet" href="/public/css/images.css">
    <style>
        .gallery-header-box {
            background: linear-gradient(135deg, rgba(30, 96, 64, 0.08) 0%, rgba(245, 238, 220, 0.5) 100%);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 35px;
            backdrop-filter: blur(10px);
        }
        .gallery-badge {
            background: var(--primary-color);
            color: #fff;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .gallery-back-btn {
            background: var(--card-bg);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            padding: 8px 18px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .gallery-back-btn:hover {
            background: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
            transform: translateX(-3px);
        }
        .empty-gallery-card {
            background: var(--card-bg);
            border: 2px dashed var(--border-color);
            border-radius: 24px;
            padding: 60px 20px;
            text-align: center;
            color: var(--text-muted);
        }
        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, transform 0.2s;
            z-index: 2002;
        }
        .lightbox-nav:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: translateY(-50%) scale(1.1);
        }
        .lightbox-nav.prev { left: 30px; }
        .lightbox-nav.next { right: 30px; }
        .meta-footer {
            display: flex;
            flex-direction: column;
            width: 100%;
            margin-top: auto;
        }
        .gallery-action-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            width: 100%;
        }
        .gallery-action-group .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            font-size: 0.76rem;
            font-weight: 600;
            padding: 6px 8px;
            border-radius: 10px;
            height: 36px;
            transition: all 0.2s ease;
        }
        .gallery-action-group .btn i {
            font-size: 0.85rem;
        }
    </style>
@endpush

@section('content')
    <div class="container" style="min-height: 100vh; max-width: 1300px; padding-top: 20px;">

        <!-- Gallery Header -->
        <div class="gallery-header-box shadow-sm">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <a href="/profilePage" class="gallery-back-btn shadow-sm">
                        <i class="bi bi-arrow-left"></i> Back to Profile
                    </a>
                    <div>
                        <h2 class="mb-0 fw-bold" style="font-family: 'Playfair Display', serif; color: var(--text-color);">
                            @if(isset($member['firstName']))
                                {{ ucwords(strtolower($member['firstName'])) }}'s Photo Gallery
                            @else
                                Family Photo Gallery
                            @endif
                        </h2>
                        <small class="text-muted">Moments and memories posted by author</small>
                    </div>
                </div>
                <div>
                    <span class="gallery-badge shadow-sm">
                        <i class="bi bi-images"></i> {{ $total ?? count($data) }} {{ ($total ?? count($data)) === 1 ? 'Photo' : 'Photos' }}
                    </span>
                </div>
            </div>
        </div>

        @if(empty($data))
            <!-- Empty State -->
            <div class="empty-gallery-card shadow-sm my-5">
                <div class="mb-3">
                    <i class="bi bi-camera" style="font-size: 4rem; color: var(--primary-color); opacity: 0.6;"></i>
                </div>
                <h4 class="fw-bold mb-2">No photos in this gallery yet</h4>
                <p class="mb-4">Photos attached to your published family posts will automatically appear here.</p>
                <a href="/profilePage" class="btn btn-primary px-4 py-2" style="border-radius: 20px; background: var(--primary-color); border: none;">
                    <i class="bi bi-pencil-square me-2"></i>Create a Post
                </a>
            </div>
        @else
            <!-- Photo Grid -->
            <section class="photo-grid" id="photoGrid">
                @foreach ($data as $index => $image)
                    <div class="grid-item" data-index="{{ $index }}">
                        <img
                            src="/resources/images/post/{{ rawurlencode($image['img']) }}"
                            alt="{{ $image['caption'] ?? $image['img'] }}"
                            data-img="{{ $image['img'] }}"
                            data-caption="{{ $image['caption'] ?? '' }}"
                            data-date="{{ !empty($image['created_at']) ? date('M j, Y', strtotime($image['created_at'])) : '' }}"
                            class="gallery-image"
                            onclick="openLightboxByIndex({{ $index }})"
                            onerror="this.onerror=null; this.closest('.grid-item').style.display='none';"
                            loading="lazy"
                        >

                        <div class="img-meta">
                            @if(!empty($image['caption']))
                                <p class="description mb-2" title="{{ $image['caption'] }}">{{ $image['caption'] }}</p>
                            @else
                                <p class="description mb-2 text-muted fst-italic">Shared memory</p>
                            @endif

                            <div class="meta-footer pt-2 border-top">
                                <!-- Top Row: Date & Status Badge -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    @if(!empty($image['created_at']))
                                        <span class="timestamp text-muted small">
                                            <i class="bi bi-calendar3 me-1"></i>{{ date('M j, Y', strtotime($image['created_at'])) }}
                                        </span>
                                    @else
                                        <span></span>
                                    @endif

                                    @if(!empty($isOwner))
                                        <span class="statusBadge badge {{ !empty($image['is_public']) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }}" style="font-size: 0.7rem;">
                                            <i class="bi {{ !empty($image['is_public']) ? 'bi-eye-fill' : 'bi-lock-fill' }} me-1"></i><span class="status-badge-text">{{ !empty($image['is_public']) ? 'Public' : 'Private' }}</span>
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">
                                            <i class="bi bi-eye-fill me-1"></i> Public
                                        </span>
                                    @endif
                                </div>

                                <!-- Action Buttons Row -->
                                @if(!empty($isOwner))
                                    <div class="gallery-action-group">
                                        <button type="button"
                                                class="toggleVisibilityBtn btn btn-sm {{ !empty($image['is_public']) ? 'btn-outline-success' : 'btn-outline-secondary' }}" 
                                                data-img="{{ $image['img'] }}" 
                                                data-public="{{ !empty($image['is_public']) ? '1' : '0' }}"
                                                title="{{ !empty($image['is_public']) ? 'Currently Public on your profile. Click to make Private' : 'Currently Private. Click to make Public' }}">
                                            <i class="bi {{ !empty($image['is_public']) ? 'bi-eye-fill' : 'bi-lock-fill' }} me-1"></i>
                                            <span class="vis-label">{{ !empty($image['is_public']) ? 'Make Private' : 'Make Public' }}</span>
                                        </button>

                                        <button type="button" 
                                                class="setProfilePicBtn btn btn-sm btn-outline-primary" 
                                                data-img="{{ $image['img'] }}" 
                                                title="Set as Profile Picture">
                                            <i class="bi bi-person-bounding-box me-1"></i>
                                            <span>Set Profile Pic</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        <!-- Lightbox Modal -->
        <div id="lightboxModal" class="lightbox">
            <span class="close-btn" onclick="closeLightbox()">&times;</span>
            <button class="lightbox-nav prev" id="lightboxPrev" onclick="navigateLightbox(-1)">&#10094;</button>
            <button class="lightbox-nav next" id="lightboxNext" onclick="navigateLightbox(1)">&#10095;</button>
            <img class="lightbox-content" id="lightboxImg" alt="Enlarged photo">
            <div id="lightboxCaption" class="lightbox-caption"></div>
        </div>

        <!-- Pagination -->
        @if(!empty($totalPages) && $totalPages > 1)
            <section class="pagination my-5">
                @if($page > 1)
                    <a href="?page={{ $page - 1 }}" title="Previous Page">&laquo;</a>
                @endif

                @for ($i = 1; $i <= $totalPages; $i++)
                    <a href="?page={{ $i }}" class="{{ $i == $page ? 'active' : '' }}">
                        {{ $i }}
                    </a>
                @endfor

                @if($page < $totalPages)
                    <a href="?page={{ $page + 1 }}" title="Next Page">&raquo;</a>
                @endif
            </section>
        @endif

    </div>

    <script>
        // Gallery Lightbox State
        const galleryImages = @json(array_values($data ?? []));
        let currentLightboxIndex = 0;

        const lightbox = document.getElementById('lightboxModal');
        const lightboxImg = document.getElementById('lightboxImg');
        const lightboxCaption = document.getElementById('lightboxCaption');

        function openLightboxByIndex(index) {
            if (!galleryImages || index < 0 || index >= galleryImages.length) return;
            currentLightboxIndex = index;
            const item = galleryImages[index];
            
            lightbox.style.display = "block";
            lightboxImg.style.display = '';
            lightboxImg.src = '/resources/images/post/' + encodeURIComponent(item.img);
            lightboxImg.onerror = function() {
                this.onerror = null;
                this.style.display = 'none';
            };

            let captionText = item.caption || '';
            if (item.created_at) {
                const dateStr = new Date(item.created_at).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
                captionText += (captionText ? ' • ' : '') + dateStr;
            }
            lightboxCaption.innerHTML = captionText;
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.style.display = "none";
            document.body.style.overflow = 'auto';
        }

        function navigateLightbox(direction) {
            if (!galleryImages || galleryImages.length <= 1) return;
            let nextIndex = currentLightboxIndex + direction;
            if (nextIndex < 0) nextIndex = galleryImages.length - 1;
            if (nextIndex >= galleryImages.length) nextIndex = 0;
            openLightboxByIndex(nextIndex);
        }

        // Close on click outside
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });

        // Keyboard controls
        document.addEventListener('keydown', function(e) {
            if (lightbox.style.display === 'block') {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') navigateLightbox(-1);
                if (e.key === 'ArrowRight') navigateLightbox(1);
            }
        });

        // Toggle Public/Private visibility handler
        document.querySelectorAll('.toggleVisibilityBtn').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.stopPropagation();
                const imageName = this.getAttribute('data-img');
                const isCurrentlyPublic = this.getAttribute('data-public') === '1';
                const nextPublic = isCurrentlyPublic ? 0 : 1;

                this.disabled = true;
                try {
                    const response = await fetch('/profilepage/gallery/toggle-visibility', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ imageName: imageName, is_public: nextPublic })
                    });
                    const resData = await response.json();
                    if (resData && resData.success) {
                        this.setAttribute('data-public', String(nextPublic));
                        const labelSpan = this.querySelector('.vis-label');
                        const icon = this.querySelector('i');
                        const gridItem = this.closest('.grid-item');
                        const statusBadge = gridItem ? gridItem.querySelector('.statusBadge') : null;

                        if (nextPublic === 1) {
                            this.className = 'toggleVisibilityBtn btn btn-sm btn-outline-success';
                            if (labelSpan) labelSpan.textContent = 'Make Private';
                            if (icon) icon.className = 'bi bi-eye-fill me-1';
                            this.title = 'Currently Public on your profile. Click to make Private';
                            if (statusBadge) {
                                statusBadge.className = 'statusBadge badge bg-success-subtle text-success border border-success-subtle';
                                statusBadge.innerHTML = '<i class="bi bi-eye-fill me-1"></i><span class="status-badge-text">Public</span>';
                            }
                        } else {
                            this.className = 'toggleVisibilityBtn btn btn-sm btn-outline-secondary';
                            if (labelSpan) labelSpan.textContent = 'Make Public';
                            if (icon) icon.className = 'bi bi-lock-fill me-1';
                            this.title = 'Currently Private. Click to make Public';
                            if (statusBadge) {
                                statusBadge.className = 'statusBadge badge bg-secondary-subtle text-secondary border border-secondary-subtle';
                                statusBadge.innerHTML = '<i class="bi bi-lock-fill me-1"></i><span class="status-badge-text">Private</span>';
                            }
                        }
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'success',
                                title: nextPublic === 1 ? 'Photo is now Public' : 'Photo is now Private',
                                text: nextPublic === 1 ? 'This photo is now visible to family members on your profile.' : 'This photo is now private and hidden from your profile.',
                                timer: 1800,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        if (window.Swal) Swal.fire('Error', resData?.error || 'Could not update visibility', 'error');
                    }
                } catch (err) {
                    console.error('Visibility toggle error:', err);
                } finally {
                    this.disabled = false;
                }
            });
        });

        // Set as Profile Picture handler
        document.querySelectorAll('.setProfilePicBtn').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.stopPropagation();
                
                const imageName = this.getAttribute('data-img');
                if (!imageName) return;

                const confirmation = await Swal.fire({
                    title: 'Set as Profile Picture?',
                    text: 'Would you like to set this photo as your profile avatar?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Set Profile Pic',
                    confirmButtonColor: '#1e6040'
                });

                if (!confirmation.isConfirmed) {
                    return;
                }

                try {
                    const response = await fetch('/setProfilePicFromImage', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ imageName: imageName })
                    });

                    const data = await response.json();
                    
                    if (data && (data.status === 200 || data.status === 'success' || data.message)) {
                        Swal.fire('Success', 'Profile picture updated successfully!', 'success');
                        window.location.reload();
                    } else {
                        Swal.fire('Error', 'Could not update profile picture: ' + (data.message || 'Error'), 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'An error occurred while updating your profile picture.', 'error');
                }
            });
        });
    </script>
@endsection