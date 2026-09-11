<!-- Middle Column - Feed -->
<style>
  .highlighted-post {
    animation: postHighlightPulse 2.5s ease-out;
  }
  @keyframes postHighlightPulse {
    0% { box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.6); }
    100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
  }
</style>
<div class="feed-column">
  @if(!empty($unclaimedMatch))
  <div class="card mb-4 border-0 shadow-sm unclaimed-match-card" id="unclaimedMatchBanner" style="border-radius: 16px; background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%); border-left: 5px solid #2563eb !important;">
    <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #dbeafe; color: #2563eb; font-size: 1.4rem;">
          <i class="bi bi-diagram-3-fill"></i>
        </div>
        <div>
          <h6 class="fw-bold mb-1 text-dark" style="font-size: 1rem;">
            👋 Welcome {{ htmlspecialchars($data['firstName'] ?? 'there') }}! Connect to your family tree?
          </h6>
          <p class="mb-0 text-muted" style="font-size: 0.88rem;">
            Your family member created a place for <strong>{{ htmlspecialchars($unclaimedMatch['full_name']) }}</strong> (<em>{{ htmlspecialchars($unclaimedMatch['role']) }}</em>) in the family tree. Is this you?
          </p>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-semibold" id="btnClaimNode" onclick="claimFamilyNode({{ (int)$unclaimedMatch['node_id'] }})">
          <i class="bi bi-check2-circle me-1"></i> Yes, that's me!
        </button>
        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2" onclick="dismissFamilyNode({{ (int)$unclaimedMatch['node_id'] }})">
          Not now
        </button>
      </div>
    </div>
  </div>
  <script>
    function claimFamilyNode(nodeId) {
      const btn = document.getElementById('btnClaimNode');
      if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Linking...'; }
      fetch('/api/claim-family-node', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ node_id: nodeId })
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'success') {
          const banner = document.getElementById('unclaimedMatchBanner');
          if (banner) {
            banner.innerHTML = '<div class="card-body p-3 text-center text-success fw-bold"><i class="bi bi-check-circle-fill me-2"></i> Successfully connected to your family tree! Refreshing...</div>';
            setTimeout(() => window.location.reload(), 1200);
          }
        } else {
          alert(data.message || 'Unable to claim node.');
          if (btn) { btn.disabled = false; btn.innerText = "Yes, that's me!"; }
        }
      })
      .catch(err => {
        console.error(err);
        if (btn) { btn.disabled = false; btn.innerText = "Yes, that's me!"; }
      });
    }

    function dismissFamilyNode(nodeId) {
      const banner = document.getElementById('unclaimedMatchBanner');
      if (banner) banner.style.display = 'none';
      fetch('/api/dismiss-claim-node', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ node_id: nodeId })
      }).catch(console.error);
    }
  </script>
  @endif

  <!-- Family Reels & Stories Tray -->
  @includeIf('member.includes.reelsTray')

  <!-- Post Composer (kept above the Kinship widget - creating a post is the
       primary action; kin discovery is secondary) -->
  <div class="card post-composer mb-4 border-0 shadow-sm" id="openPostModalTrigger" style="border-radius: 16px; overflow: hidden; background-color: var(--card-bg);">
    <div class="card-body p-3 p-sm-4">
      <div class="d-flex align-items-center gap-3">
        <img src="{{ str_starts_with($data['img'] ?? '', '/') ? $data['img'] : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png') }}" alt="profile" class="rounded-circle flex-shrink-0" width="44" height="44" style="object-fit: cover;">

        <div role="button" tabindex="0" class="composer-prompt flex-grow-1 rounded-pill px-3 py-3 text-truncate" data-bs-toggle="modal" data-bs-target="#postModal"
          style="background-color: var(--bg-color); border: 1px solid var(--border-color); cursor: text; transition: border-color 0.2s, background-color 0.2s;">
          <span style="color: var(--text-muted); font-size: 0.95rem;">Share a moment with your family...</span>
        </div>
      </div>

      <div class="composer-quick-actions d-flex align-items-center gap-1 mt-3 pt-3" style="border-top: 1px solid var(--border-color);">
        <button type="button" class="btn composer-quick-action flex-fill d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#postModal" aria-label="Add a photo to a post">
          <i class="bi bi-camera-fill" style="color: #22c55e; font-size: 1.15rem;"></i>
          <span class="fw-semibold" style="font-size: 0.85rem; color: var(--text-muted);">Photo</span>
        </button>
        <button type="button" class="btn composer-quick-action flex-fill d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#postModal" aria-label="Add a video to a post">
          <i class="bi bi-play-btn-fill" style="color: #ef4444; font-size: 1.15rem;"></i>
          <span class="fw-semibold" style="font-size: 0.85rem; color: var(--text-muted);">Video</span>
        </button>
        <button type="button" class="btn composer-quick-action flex-fill d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#postModal" aria-label="Create a poll">
          <i class="bi bi-bar-chart-fill" style="color: #f97316; font-size: 1.15rem;"></i>
          <span class="fw-semibold" style="font-size: 0.85rem; color: var(--text-muted);">Poll</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Kinship Radar: Mobile-Only (visible < 992px, hidden on desktop where sidebar shows it) -->
  <!-- Distinct id from the desktop copy below (rightColumn.blade.php) - both render the
       same partial and would otherwise share id="kinshipRadarWidget", which left
       getElementById('kinshipRadarWidget') in kinshipRadar.js always binding to this
       (frequently hidden) copy instead of whichever one is actually visible. -->
  <div class="d-lg-none">
    @includeIf('member.includes.kinshipSuggestions', ['kinshipWidgetId' => 'kinshipRadarWidgetMobile'])
  </div>

  <!-- Memories Section (Hidden until loaded) -->
  <div id="memories-container" style="display: none; margin-bottom: 24px;">
    <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
      <div class="card-body p-4">
        <h5 class="fw-bold text-primary mb-3"><i class="fa fa-history me-2"></i> On This Day</h5>
        <div id="memories-content"></div>
      </div>
    </div>
  </div>

  <!-- Reactive Alpine.js Feed -->
  <div x-data="profileFeed({ famCode: '{{ $data['famCode'] ?? '' }}', userId: '{{ $data['id'] ?? '' }}' })" class="feed-posts-container">
    <!-- Error Alert if loading fails -->
    <template x-if="errorMessage">
      <div class="alert alert-danger shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <span x-text="errorMessage"></span>
        <button class="btn btn-sm btn-outline-danger ms-3" @click="fetchPosts()">Retry</button>
      </div>
    </template>

    <!-- Skeleton Loading State -->
    <template x-if="isLoading">
      <div>
        <div class="skeleton-post shadow-sm mb-4" style="background: white; border-radius: 16px; padding: 20px; text-align: left; animation: pulse 1.5s infinite ease-in-out;">
          <div style="display: flex; align-items: center; margin-bottom: 15px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: #e2e8f0; margin-right: 15px;"></div>
            <div style="flex-grow: 1;">
              <div style="height: 12px; background: #e2e8f0; border-radius: 4px; width: 40%; margin-bottom: 8px;"></div>
              <div style="height: 10px; background: #e2e8f0; border-radius: 4px; width: 20%;"></div>
            </div>
          </div>
          <div style="height: 14px; background: #e2e8f0; border-radius: 4px; width: 100%; margin-bottom: 10px;"></div>
          <div style="height: 14px; background: #e2e8f0; border-radius: 4px; width: 85%; margin-bottom: 10px;"></div>
          <div style="height: 14px; background: #e2e8f0; border-radius: 4px; width: 60%;"></div>
        </div>
      </div>
    </template>

    <!-- Empty State -->
    <template x-if="!isLoading && posts.length === 0 && !errorMessage">
      <div class="card border-0 shadow-sm p-5 text-center mb-4" style="border-radius: 16px; background: white;">
        <div class="mb-3">
          <i class="bi bi-chat-heart text-muted" style="font-size: 3rem;"></i>
        </div>
        <h5 class="fw-bold text-dark">No family posts yet</h5>
        <p class="text-muted mb-0">Share the first memory or event with your family above!</p>
      </div>
    </template>

    <!-- Posts Loop -->
    <div id="postIt" class="postIt">
      <template x-for="post in posts" :key="post.post_no">
        @include('components.feed.post-card')
      </template>
    </div>
    
    <!-- Premium Alpine Lightbox Modal -->
    <div x-cloak x-show="lightboxOpen" 
         class="lightbox-overlay"
         style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.92); z-index: 99999; backdrop-filter: blur(12px); display: flex; align-items: center; justify-content: center;"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @keydown.escape.window="closeLightbox()"
         @keydown.right.window="nextLightboxImage()"
         @keydown.left.window="prevLightboxImage()"
         @touchstart.passive="onLightboxTouchStart($event)"
         @touchend.passive="onLightboxTouchEnd($event)"
         @click="closeLightbox()">
        
        <!-- Close Button (High contrast, 48px touch target) -->
        <button type="button" 
                @click.stop="closeLightbox()" 
                aria-label="Close photo view"
                style="position: absolute; top: 18px; right: 20px; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.25); color: #ffffff; width: 46px; height: 46px; border-radius: 50%; font-size: 26px; line-height: 1; cursor: pointer; display: flex; justify-content: center; align-items: center; transition: all 0.2s ease; z-index: 100002; box-shadow: 0 4px 14px rgba(0,0,0,0.5);">
            <i class="bi bi-x-lg" style="font-size: 1.15rem;"></i>
        </button>
        
        <!-- Previous Button -->
        <button type="button" 
                @click.stop.prevent="prevLightboxImage()" 
                x-show="lightboxImages && lightboxImages.length > 1" 
                aria-label="Previous photo"
                style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.25); color: #ffffff; width: 50px; height: 50px; border-radius: 50%; font-size: 22px; display: flex; justify-content: center; align-items: center; cursor: pointer; transition: all 0.2s ease; z-index: 100002; user-select: none; box-shadow: 0 4px 14px rgba(0,0,0,0.5);">
            <i class="bi bi-chevron-left"></i>
        </button>

        <!-- Main Image Container -->
        <div class="lightbox-image-container" 
             style="position: relative; max-width: 92vw; max-height: 86vh; display: flex; align-items: center; justify-content: center; user-select: none;" 
             @click.stop>
            
            <!-- Loading Spinner -->
            <div x-show="lightboxLoading" 
                 class="spinner-border text-light position-absolute" 
                 style="width: 3rem; height: 3rem; z-index: 100000;" 
                 role="status">
                <span class="visually-hidden">Loading photo...</span>
            </div>

            <!-- Image View -->
            <img :src="getPostImageUrl(lightboxImages[lightboxIndex])" 
                 x-on:load="lightboxLoading = false; lightboxError = false;"
                 x-on:error="lightboxLoading = false; lightboxError = true;"
                 x-show="!lightboxError"
                 style="max-width: 100%; max-height: 84vh; border-radius: 8px; object-fit: contain; box-shadow: 0 25px 60px rgba(0,0,0,0.7); transition: transform 0.2s ease;" 
                 alt="Enlarged family photo"
                 @click.stop>

            <!-- Fallback if Image Fails to Load -->
            <div x-show="lightboxError" 
                 class="text-center text-white p-4" 
                 style="background: rgba(30,30,30,0.85); border-radius: 12px; border: 1px solid rgba(255,255,255,0.2);">
                <i class="bi bi-image text-secondary mb-2" style="font-size: 2.5rem; display: block;"></i>
                <p class="mb-2 fw-semibold">Photo unavailable</p>
                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" @click.stop="closeLightbox()">Close</button>
            </div>
        </div>
        
        <!-- Next Button -->
        <button type="button" 
                @click.stop.prevent="nextLightboxImage()" 
                x-show="lightboxImages && lightboxImages.length > 1" 
                aria-label="Next photo"
                style="position: absolute; right: 18px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.25); color: #ffffff; width: 50px; height: 50px; border-radius: 50%; font-size: 22px; display: flex; justify-content: center; align-items: center; cursor: pointer; transition: all 0.2s ease; z-index: 100002; user-select: none; box-shadow: 0 4px 14px rgba(0,0,0,0.5);">
            <i class="bi bi-chevron-right"></i>
        </button>
        
        <!-- Image Counter Badge -->
        <div x-show="lightboxImages && lightboxImages.length > 1" 
             style="position: absolute; bottom: 25px; left: 50%; transform: translateX(-50%); color: #ffffff; font-size: 14px; font-weight: 600; background: rgba(0,0,0,0.75); padding: 6px 20px; border-radius: 30px; letter-spacing: 0.5px; box-shadow: 0 4px 14px rgba(0,0,0,0.5); z-index: 100002; border: 1px solid rgba(255,255,255,0.2);" 
             x-text="(lightboxIndex + 1) + ' / ' + lightboxImages.length"></div>
    </div>
  </div>

  <!-- The Modal Structure (Hidden by default) for the post image modal -->
  @includeIf('member.modals.postImg')


  <style>
    @keyframes pulse {
      0% { opacity: 0.6; }
      50% { opacity: 1; }
      100% { opacity: 0.6; }
    }
  </style>
</div>
