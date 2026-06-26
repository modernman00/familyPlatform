@extends('layouts.profileBase')
@section('title', 'images')
@section('data-page-id', 'images')
@push('styles')
    <link rel="stylesheet" href="/public/css/images.css">
@endpush
@section('content')
    <div class="container" style="min-height: 100vh;">

        <!-- Gallery Header -->
        <div class="gallery-header mt-4">
            <h1>Family Memories</h1>
            <p>Relive the beautiful moments we've shared together</p>
        </div>

        <!-- Slideshow Container -->
        <section class="photo-grid" id="photoGrid">

            @foreach ($data as $image)
                <div class="grid-item">

                    <img src="/resources/images/post/{{ $image['img'] }}" alt="{{ $image['caption'] ?? $image['img'] }}"
                        data-id="{{ $image['id'] }}" class="gallery-image" onclick="openLightbox(this)">

                    <div class="img-meta">
                        @if(!empty($image['caption']))
                            <p class="description">{{ $image['caption'] }}</p>
                        @endif

                        <div class="meta-footer">
                            <button class="likeBtn" id="likeBtn{{ $image['id'] }}">
                                <i class="bi bi-heart-fill"></i> {{ $image['likes'] }}
                            </button>
                            <button class="setProfilePicBtn btn btn-sm btn-outline-primary" data-img="{{ $image['img'] }}" title="Set as Profile Picture">
                                <i class="bi bi-person-circle"></i>
                            </button>
                            <div class="timestamp">{{ humanTiming($image['created_at']) }} ago</div>
                        </div>
                    </div>

                </div>
            @endforeach

        </section>

        <!-- Lightbox Modal -->
        <div id="lightboxModal" class="lightbox">
            <span class="close-btn" onclick="closeLightbox()">&times;</span>
            <img class="lightbox-content" id="lightboxImg">
            <div id="lightboxCaption" class="lightbox-caption"></div>
        </div>

        <!-- Pagination -->
        <section class="pagination">
            @for ($i = 1; $i <= $totalPages; $i++)
                <a href="?page={{ $i }}" class="{{ $i == $page ? 'active' : '' }}">
                    {{ $i }}
                </a>
            @endfor
        </section>

    </div>

    <script>
        // Lightbox functionality
        const lightbox = document.getElementById('lightboxModal');
        const lightboxImg = document.getElementById('lightboxImg');
        const lightboxCaption = document.getElementById('lightboxCaption');

        function openLightbox(imgElement) {
            lightbox.style.display = "block";
            lightboxImg.src = imgElement.src;
            // Use alt text or caption for description
            const caption = imgElement.alt || '';
            lightboxCaption.innerHTML = caption;
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        function closeLightbox() {
            lightbox.style.display = "none";
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        // Close on click outside
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });

        // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && lightbox.style.display === 'block') {
            closeLightbox();
        }
    });

    // Set as Profile Picture handler
    document.querySelectorAll('.setProfilePicBtn').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.stopPropagation(); // Prevent lightbox from opening
            
            const imageName = this.getAttribute('data-img');
            
            if (!confirm('Set this image as your profile picture?')) {
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
                
                if (data.status === 'success') {
                    alert('Profile picture updated successfully! Refresh the page to see changes.');
                } else {
                    alert('Failed to update profile picture: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating your profile picture.');
            }
        });
    });
</script>

@endsection