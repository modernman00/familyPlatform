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
                <h4 class="fw-bold" style="font-family: 'Playfair Display', serif; color: var(--text-color);">No Photos Yet</h4>
                <p class="text-muted mb-4">Photos attached to social feed posts will automatically appear in this gallery.</p>
                <a href="/profilePage" class="btn text-white px-4 py-2" style="background-color: var(--primary-color); border-radius: 20px;">
                    <i class="bi bi-pencil-square me-1"></i> Create a Post
                </a>
            </div>
        @else
            <!-- Photo Grid -->
            <section class="photo-grid" id="photoGrid">
                @foreach ($data as $index => $image)
                    <div class="grid-item" data-index="{{ $index }}">
                        <img 
                            src="/resources/images/post/{{ $image['img'] }}" 
                            alt="{{ $image['caption'] ?? $image['img'] }}"
                            data-img="{{ $image['img'] }}"
                            data-caption="{{ $image['caption'] ?? '' }}"
                            data-date="{{ !empty($image['created_at']) ? date('M j, Y', strtotime($image['created_at'])) : '' }}"
                            class="gallery-image" 
                            onclick="openLightboxByIndex({{ $index }})"
                            onerror="this.onerror=null; this.src='/resources/images/post/{{ $image['img'] }}';"
                            loading="lazy"
                        >

                        <div class="img-meta">
                            @if(!empty($image['caption']))
                                <p class="description mb-2" title="{{ $image['caption'] }}">{{ $image['caption'] }}</p>
                            @else
                                <p class="description mb-2 text-muted fst-italic">Shared memory</p>
                            @endif

                            <div class="meta-footer pt-2 border-top">
                                @if(!empty($isOwner))
                                    <button class="setProfilePicBtn btn btn-sm" data-img="{{ $image['img'] }}" title="Set as Profile Picture">
                                        <i class="bi bi-person-bounding-box"></i> Set as Avatar
                                    </button>
                                @else
                                    <span class="badge bg-light text-dark"><i class="bi bi-camera"></i> Photo</span>
                                @endif

                                @if(!empty($image['created_at']))
                                    <div class="timestamp text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>{{ date('M j, Y', strtotime($image['created_at'])) }}
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
            lightboxImg.src = '/resources/images/post/' + item.img;
            lightboxImg.onerror = function() {
                this.onerror = null;
                this.src = '/resources/images/post/' + item.img;
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

        // Set as Profile Picture handler
        document.querySelectorAll('.setProfilePicBtn').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.stopPropagation();
                
                const imageName = this.getAttribute('data-img');
                if (!imageName) return;

                const confirmation = await Swal.fire({
                    title: 'Are you sure?',
                    text: 'Would you like to set this photo as your profile avatar?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'OK'
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