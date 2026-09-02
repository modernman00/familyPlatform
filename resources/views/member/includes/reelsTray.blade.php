<div class="reels-tray-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-camera-reels-fill text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.01em;">Family Reels & Stories</h6>
        </div>
        <a href="/reels" class="text-decoration-none small fw-bold" style="color: var(--brand-primary);">
            Watch All <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
        </a>
    </div>

    <div class="reels-tray-scroll">
        <!-- 1. Create Reel Card (Facebook-Style First Card) -->
        <div class="reel-fb-create-card" data-bs-toggle="modal" data-bs-target="#createReelModal">
            <div class="reel-fb-create-top">
                <img src="{{ !empty($data['img']) ? (str_starts_with($data['img'], '/') ? $data['img'] : '/resources/images/profile/' . $data['img']) : '/resources/images/profile/avatarM.png' }}" 
                     alt="You">
            </div>
            <div class="reel-fb-create-bottom">
                <div class="reel-fb-create-plus">
                    <i class="bi bi-plus-lg"></i>
                </div>
                <span class="reel-fb-create-label">Create Reel</span>
            </div>
        </div>

        <!-- 2. Dynamic Video Cover Thumbnail Cards -->
        @forelse(($recentReels ?? []) as $reelItem)
            @php
                $creatorAvatar = !empty($reelItem['profilePics']) ? (str_starts_with($reelItem['profilePics'], '/') ? $reelItem['profilePics'] : '/resources/images/profile/' . $reelItem['profilePics']) : '/resources/images/profile/avatarM.png';
                $rawThumb = !empty($reelItem['thumbnail_url']) ? (string)$reelItem['thumbnail_url'] : '';
                $coverThumb = (!empty($rawThumb) && !str_contains($rawThumb, 'placeholder.com')) ? $rawThumb : $creatorAvatar;
                $creatorName = ucwords(strtolower($reelItem['firstName'] ?? 'Family'));
                $tag = !empty($reelItem['category']) ? $reelItem['category'] : 'Reel';
            @endphp
            <a href="/reels?id={{ $reelItem['id'] }}" class="reel-fb-card">
                <!-- Video Cover Frame Image -->
                <img src="{{ $coverThumb }}" 
                     alt="{{ $creatorName }}'s Reel" 
                     class="reel-fb-thumb"
                     onerror="this.src='{{ $creatorAvatar }}'">

                <!-- Legibility Dark Gradient -->
                <div class="reel-fb-overlay"></div>

                <!-- Creator Avatar Ring (Top-Left) -->
                <div class="reel-fb-avatar-bubble">
                    <img src="{{ $creatorAvatar }}" alt="{{ $creatorName }}">
                </div>

                <!-- Creator Name & Category (Bottom) -->
                <div class="reel-fb-footer">
                    <div class="reel-fb-tag">#{{ $tag }}</div>
                    <div class="reel-fb-creator">{{ $creatorName }}</div>
                </div>
            </a>
        @empty
            <!-- Fallback Guide Card -->
            <a href="/reels" class="reel-fb-card d-flex flex-column align-items-center justify-content-center text-center p-2" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);">
                <i class="bi bi-camera-reels-fill text-warning fs-2 mb-2"></i>
                <div class="small fw-bold text-white">Watch Family Reels</div>
            </a>
        @endforelse
    </div>
</div>
