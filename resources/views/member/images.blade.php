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
            position: absolute;
            top: 20px;
            left: 20px;
            background: transparent;
            color: var(--text-color);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.2rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            z-index: 10;
        }
        .gallery-back-btn:hover {
            background: var(--primary-color-subtle, rgba(163,22,33, 0.1));
            color: var(--primary-color);
            transform: scale(1.1);
        }
        .gallery-back-btn i {
            margin-right: 0;
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
            padding: 8px 10px;
            border-radius: 10px;
            min-height: 44px;
            transition: all 0.2s ease;
        }
        .gallery-action-group .btn:active {
            transform: scale(0.98);
        }
        .gallery-action-group .btn i {
            font-size: 0.85rem;
        }
        .gallery-action-group .deletePhotoBtn {
            grid-column: span 2;
            border-color: #ef4444;
            color: #dc2626;
        }
        .gallery-action-group .deletePhotoBtn:hover,
        .gallery-action-group .deletePhotoBtn:focus-visible {
            background-color: #dc2626;
            color: #ffffff !important;
            border-color: #dc2626;
        }
        .grid-item {
            position: relative;
        }
        .photo-select-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(6px);
            border: 1.5px solid rgba(255, 255, 255, 0.9);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 12;
            transition: transform 0.18s ease, background-color 0.18s ease, border-color 0.18s ease;
            margin: 0;
        }
        /* Expand touch hit area to satisfy 44x44px Fitts's law mandate without bloating visual footprint */
        .photo-select-overlay::after {
            content: '';
            position: absolute;
            inset: -9px;
            min-width: 44px;
            min-height: 44px;
        }
        .photo-select-overlay:hover {
            background: rgba(15, 23, 42, 0.82);
            transform: scale(1.08);
        }
        .photo-select-overlay:active {
            transform: scale(0.95);
        }
        .photo-select-overlay input.selectPhotoCheckbox {
            width: 15px;
            height: 15px;
            cursor: pointer;
            accent-color: #dc2626;
            margin: 0;
        }
        .grid-item.is-selected {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.25), var(--shadow);
        }
        .grid-item.is-selected .photo-select-overlay {
            background: #dc2626;
            border-color: #ffffff;
        }
        .batch-action-bar {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: var(--shadow);
        }
        .batch-action-bar .btn {
            min-height: 44px;
            min-width: 44px;
            white-space: nowrap;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 12px;
            padding: 8px 16px;
            transition: all 0.2s ease;
        }
        .batch-action-bar .btn:active {
            transform: scale(0.98);
        }
        #deleteSelectedPhotosBtn {
            background-color: #dc2626;
            border-color: #dc2626;
            color: #ffffff !important;
        }
        #deleteSelectedPhotosBtn:hover:not(:disabled) {
            background-color: #b91c1c;
            border-color: #b91c1c;
            color: #ffffff !important;
        }
        #deleteSelectedPhotosBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('content')
    <div class="container" style="min-height: 100vh; max-width: 1300px; padding-top: 20px; position: relative;">

        <!-- Back Button (icon-only, top-left) -->
        <a href="/profilePage" class="gallery-back-btn" title="Back to Profile - Return to main feed and profile page" aria-label="Back to profile">
            <i class="bi bi-chevron-left"></i>
        </a>

        <!-- Gallery Header -->
        <div class="gallery-header-box shadow-sm">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h2 class="mb-0 fw-bold" style="font-family: var(--font-heading, 'Outfit', sans-serif); color: var(--text-color);">
                            @if(isset($member['firstName']))
                                {{ ucwords(strtolower($member['firstName'])) }}'s Photo Gallery
                            @else
                                Family Photo Gallery
                            @endif
                        </h2>
                        <small class="text-muted">Moments and memories posted by author</small>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="gallery-badge shadow-sm" id="galleryTotalBadge">
                        <i class="bi bi-images"></i> <span id="galleryTotalCount">{{ $total ?? count($data) }}</span> <span id="galleryTotalLabel">{{ ($total ?? count($data)) === 1 ? 'Photo' : 'Photos' }}</span>
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
            @if(!empty($isOwner))
                <!-- Batch Selection Toolbar -->
                <div class="batch-action-bar" id="batchActionBar">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleSelectAllPhotosBtn" title="Select or deselect all photos on this page">
                            <i class="bi bi-check2-square me-1"></i>
                            <span id="toggleSelectAllLabel">Select All</span>
                        </button>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2" id="selectedPhotosCounter" style="font-size: 0.8rem;">
                            0 Selected
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-danger" id="deleteSelectedPhotosBtn" disabled title="Delete all selected pictures from your gallery">
                            <i class="bi bi-trash3 me-1"></i>
                            <span id="deleteSelectedBtnLabel">Delete Selected</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Photo Grid -->
            <section class="photo-grid" id="photoGrid">
                @foreach ($data as $index => $image)
                    <div class="grid-item" data-index="{{ $index }}" data-img="{{ $image['img'] }}">
                        @if(!empty($isOwner))
                            <label class="photo-select-overlay" title="Select picture for batch deletion" onclick="event.stopPropagation();">
                                <input
                                    type="checkbox"
                                    class="selectPhotoCheckbox"
                                    value="{{ $image['img'] }}"
                                    data-img="{{ $image['img'] }}"
                                    aria-label="Select picture {{ $image['caption'] ?? $image['img'] }}"
                                >
                            </label>
                        @endif
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

                                        <button type="button" 
                                                class="deletePhotoBtn btn btn-sm btn-outline-danger" 
                                                data-img="{{ $image['img'] }}" 
                                                title="Delete picture from gallery">
                                            <i class="bi bi-trash3 me-1"></i>
                                            <span>Delete</span>
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
            <span class="close-btn" onclick="closeLightbox()" title="Close viewer" aria-label="Close photo viewer">&times;</span>
            <button class="lightbox-nav prev" id="lightboxPrev" onclick="navigateLightbox(-1)" title="Previous photo" aria-label="Previous photo">&#10094;</button>
            <button class="lightbox-nav next" id="lightboxNext" onclick="navigateLightbox(1)" title="Next photo" aria-label="Next photo">&#10095;</button>
            <img class="lightbox-content" id="lightboxImg" alt="Enlarged photo">
            <div id="lightboxCaption" class="lightbox-caption"></div>
            @if(!empty($isOwner))
                <div class="lightbox-actions text-center mt-3 pb-3" style="position: relative; z-index: 2003;">
                    <button type="button" class="btn btn-sm btn-danger px-4 py-2" id="lightboxDeleteBtn" style="border-radius: 20px; font-weight: 600; min-height: 44px; color: #ffffff; background-color: #dc2626; border-color: #dc2626;" title="Delete this picture permanently from your gallery" aria-label="Delete this picture">
                        <i class="bi bi-trash3 me-1"></i>Delete This Picture
                    </button>
                </div>
            @endif
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

        function getSafePostImgUrl(img) {
            if (!img || typeof img !== 'string') return '';
            let clean = img.trim();
            if (clean.startsWith('http://') || clean.startsWith('https://') || clean.startsWith('blob:') || clean.startsWith('data:')) {
                return clean;
            }
            const parts = clean.split(/[/\\]/);
            const basename = parts[parts.length - 1];
            return '/resources/images/post/' + encodeURIComponent(basename);
        }

        function openLightboxByIndex(index) {
            if (!galleryImages || index < 0 || index >= galleryImages.length) return;
            currentLightboxIndex = index;
            const item = galleryImages[index];
            
            lightbox.style.display = "block";
            lightboxImg.style.display = '';
            lightboxImg.src = getSafePostImgUrl(item.img);
            lightboxImg.onerror = function() {
                this.onerror = null;
                this.alt = 'Image could not be loaded';
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

        // Delete Photo handler with confirmation and optimistic removal
        async function executeDeletePhoto(imageName, triggerButton = null) {
            if (!imageName) return;

            const confirmation = await Swal.fire({
                title: 'Delete Picture?',
                text: 'Are you sure you want to delete this picture from your gallery? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete Picture',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true
            });

            if (!confirmation.isConfirmed) {
                return;
            }

            let originalHtml = '';
            if (triggerButton) {
                originalHtml = triggerButton.innerHTML;
                triggerButton.disabled = true;
                triggerButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span><span>Deleting...</span>';
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await fetch('/profilepage/gallery/delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        imageName: imageName,
                        csrf_token: csrfToken
                    })
                });

                const resData = await response.json();

                if (resData && (resData.success || resData.status === 200)) {
                    if (window.Swal) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Picture Deleted',
                            text: resData.message || 'Picture removed from your gallery successfully.',
                            timer: 1600,
                            showConfirmButton: false
                        });
                    }
                    if (lightbox && lightbox.style.display === 'block') {
                        closeLightbox();
                    }

                    // Remove item from grid with smooth animation
                    const targetBtn = document.querySelector(`.deletePhotoBtn[data-img="${CSS.escape(imageName)}"]`);
                    const gridItem = targetBtn ? targetBtn.closest('.grid-item') : null;
                    if (gridItem) {
                        gridItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                        gridItem.style.opacity = '0';
                        gridItem.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            gridItem.remove();
                            const remainingItems = document.querySelectorAll('.photo-grid .grid-item');
                            if (remainingItems.length === 0) {
                                window.location.reload();
                            }
                        }, 300);
                    } else {
                        window.location.reload();
                    }
                } else {
                    if (window.Swal) {
                        Swal.fire('Cannot Delete', resData?.error || 'Could not delete picture.', 'error');
                    }
                    if (triggerButton) {
                        triggerButton.disabled = false;
                        triggerButton.innerHTML = originalHtml;
                    }
                }
            } catch (err) {
                console.error('Delete photo error:', err);
                if (window.Swal) {
                    Swal.fire('Error', 'An error occurred while deleting the picture.', 'error');
                }
                if (triggerButton) {
                    triggerButton.disabled = false;
                    triggerButton.innerHTML = originalHtml;
                }
            }
        }

        document.querySelectorAll('.deletePhotoBtn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const imageName = this.getAttribute('data-img');
                executeDeletePhoto(imageName, this);
            });
        });

        const lightboxDeleteBtn = document.getElementById('lightboxDeleteBtn');
        if (lightboxDeleteBtn) {
            lightboxDeleteBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (galleryImages && galleryImages[currentLightboxIndex]) {
                    const currentImg = galleryImages[currentLightboxIndex].img;
                    executeDeletePhoto(currentImg, this);
                }
            });
        }

        // =========================================================================
        // Batch Photo Deletion Management (Checkbox Overlay)
        // =========================================================================
        const selectPhotoCheckboxes = document.querySelectorAll('.selectPhotoCheckbox');
        const toggleSelectAllBtn = document.getElementById('toggleSelectAllPhotosBtn');
        const toggleSelectAllLabel = document.getElementById('toggleSelectAllLabel');
        const selectedCounter = document.getElementById('selectedPhotosCounter');
        const deleteSelectedBtn = document.getElementById('deleteSelectedPhotosBtn');
        const deleteSelectedBtnLabel = document.getElementById('deleteSelectedBtnLabel');

        function updateBatchToolbarState() {
            if (!selectPhotoCheckboxes.length) return;

            const selected = Array.from(selectPhotoCheckboxes).filter(cb => cb.checked);
            const count = selected.length;

            if (selectedCounter) {
                selectedCounter.textContent = `${count} Selected`;
                if (count > 0) {
                    selectedCounter.className = 'badge bg-danger text-white border border-danger px-3 py-2';
                } else {
                    selectedCounter.className = 'badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2';
                }
            }

            if (deleteSelectedBtn) {
                deleteSelectedBtn.disabled = (count === 0);
            }

            if (deleteSelectedBtnLabel) {
                deleteSelectedBtnLabel.textContent = count > 1 ? `Delete Selected (${count})` : (count === 1 ? 'Delete Selected (1)' : 'Delete Selected');
            }

            if (toggleSelectAllLabel) {
                const allSelected = (count > 0 && count === selectPhotoCheckboxes.length);
                toggleSelectAllLabel.textContent = allSelected ? 'Deselect All' : 'Select All';
                if (toggleSelectAllBtn) {
                    const icon = toggleSelectAllBtn.querySelector('i');
                    if (icon) {
                        icon.className = allSelected ? 'bi bi-dash-square me-1' : 'bi bi-check2-square me-1';
                    }
                }
            }
        }

        selectPhotoCheckboxes.forEach(cb => {
            cb.addEventListener('change', function(e) {
                e.stopPropagation();
                const card = this.closest('.grid-item');
                if (card) {
                    card.classList.toggle('is-selected', this.checked);
                }
                updateBatchToolbarState();
            });

            // Prevent clicking the label from triggering openLightboxByIndex
            const parentLabel = cb.closest('.photo-select-overlay');
            if (parentLabel) {
                parentLabel.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });

        if (toggleSelectAllBtn) {
            toggleSelectAllBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const anyUnselected = Array.from(selectPhotoCheckboxes).some(cb => !cb.checked);
                selectPhotoCheckboxes.forEach(cb => {
                    cb.checked = anyUnselected;
                    const card = cb.closest('.grid-item');
                    if (card) {
                        card.classList.toggle('is-selected', anyUnselected);
                    }
                });
                updateBatchToolbarState();
            });
        }

        if (deleteSelectedBtn) {
            deleteSelectedBtn.addEventListener('click', async function(e) {
                e.stopPropagation();
                const selected = Array.from(selectPhotoCheckboxes).filter(cb => cb.checked);
                if (selected.length === 0) return;

                const selectedImages = selected.map(cb => cb.getAttribute('data-img')).filter(Boolean);
                const count = selectedImages.length;
                const photoWord = count === 1 ? 'picture' : 'pictures';

                const confirmation = await Swal.fire({
                    title: `Delete ${count} ${photoWord}?`,
                    text: `Are you sure you want to delete ${count} ${photoWord} from your gallery? This action cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: `Yes, Delete ${count} ${photoWord}`,
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true
                });

                if (!confirmation.isConfirmed) {
                    return;
                }

                const originalBtnHtml = deleteSelectedBtn.innerHTML;
                deleteSelectedBtn.disabled = true;
                deleteSelectedBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span><span>Deleting...</span>';

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const response = await fetch('/profilepage/gallery/delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            imageNames: selectedImages,
                            csrf_token: csrfToken
                        })
                    });

                    const resData = await response.json();

                    if (resData && (resData.success || resData.status === 200)) {
                        if (window.Swal) {
                            await Swal.fire({
                                icon: 'success',
                                title: `${count} ${photoWord} Deleted`,
                                text: resData.message || `${count} ${photoWord} removed from your gallery successfully.`,
                                timer: 1700,
                                showConfirmButton: false
                            });
                        }

                        // Animate removal of all deleted cards
                        selected.forEach(cb => {
                            const gridItem = cb.closest('.grid-item');
                            if (gridItem) {
                                gridItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                gridItem.style.opacity = '0';
                                gridItem.style.transform = 'scale(0.9)';
                                setTimeout(() => gridItem.remove(), 300);
                            }
                        });

                        setTimeout(() => {
                            const remainingItems = document.querySelectorAll('.photo-grid .grid-item');
                            if (remainingItems.length === 0) {
                                window.location.reload();
                            } else {
                                updateBatchToolbarState();
                                const galleryTotalCount = document.getElementById('galleryTotalCount');
                                if (galleryTotalCount) {
                                    const currentTotal = parseInt(galleryTotalCount.textContent, 10) || remainingItems.length;
                                    const newTotal = Math.max(0, currentTotal - count);
                                    galleryTotalCount.textContent = newTotal;
                                    const galleryTotalLabel = document.getElementById('galleryTotalLabel');
                                    if (galleryTotalLabel) {
                                        galleryTotalLabel.textContent = newTotal === 1 ? 'Photo' : 'Photos';
                                    }
                                }
                            }
                        }, 350);
                    } else {
                        if (window.Swal) {
                            Swal.fire('Cannot Delete Pictures', resData?.error || 'Could not delete the selected pictures.', 'error');
                        }
                        deleteSelectedBtn.disabled = false;
                        deleteSelectedBtn.innerHTML = originalBtnHtml;
                    }
                } catch (err) {
                    console.error('Batch delete error:', err);
                    if (window.Swal) {
                        Swal.fire('Error', 'An error occurred while deleting the selected pictures.', 'error');
                    }
                    deleteSelectedBtn.disabled = false;
                    deleteSelectedBtn.innerHTML = originalBtnHtml;
                }
            });
        }
    </script>
@endsection