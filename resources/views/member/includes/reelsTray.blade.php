<div class="reels-tray-wrapper" id="familyReelsTray">
    @php
        $effectiveCreatorGroups = $groupedReels ?? \App\model\Reel::groupReelsByCreator($recentReels ?? []);
    @endphp

    <!-- =========================================================
         Unified Always-On Facebook Reels Card Carousel (Mobile & Desktop)
         ========================================================= -->
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2 reel-tray-header">
        <div class="d-flex align-items-center gap-2">
            <div class="reel-tray-icon-badge" aria-hidden="true">
                <i class="bi bi-camera-reels-fill"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold reel-tray-title" style="letter-spacing: -0.01em;">Family Reels &amp; Stories</h6>
                <div class="reel-tray-subtitle" style="font-size: 0.72rem;">Short-form milestones, celebrations &amp; oral history</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-light rounded-pill fw-bold px-3 py-1 border d-inline-flex align-items-center justify-content-center reel-tray-new-btn" data-bs-toggle="modal" data-bs-target="#createReelModal" style="font-size: 0.75rem; min-height: 44px; min-width: 44px;">
                <i class="bi bi-plus-circle-fill text-primary me-1"></i>New
            </button>
            <a href="/reels" class="text-decoration-none small fw-bold d-inline-flex align-items-center gap-1 reel-tray-watch-link" style="color: var(--brand-primary, #a31621); min-height: 44px; min-width: 44px;">
                <span>Watch All</span> <i class="bi bi-chevron-right" style="font-size: 0.75rem;"></i>
            </a>
        </div>
    </div>

    <!-- Category Quick Filter Pills -->
    <div class="reel-tray-filter-bar mb-2" role="tablist" aria-label="Filter Family Reels">
        <button type="button" class="reel-tray-filter-pill active" data-reel-filter="all">All</button>
        <button type="button" class="reel-tray-filter-pill" data-reel-filter="milestone">🎉 Milestones</button>
        <button type="button" class="reel-tray-filter-pill" data-reel-filter="reunion">🏡 Reunions</button>
        <button type="button" class="reel-tray-filter-pill" data-reel-filter="storytelling">📖 Stories</button>
        <button type="button" class="reel-tray-filter-pill" data-reel-filter="first_steps">👶 Kids</button>
        <button type="button" class="reel-tray-filter-pill" data-reel-filter="wedding">💍 Weddings</button>
        <button type="button" class="reel-tray-filter-pill" data-reel-filter="night_out">🌙 Night Out</button>
        <button type="button" class="reel-tray-filter-pill" data-reel-filter="weekend_out">🏖️ Weekend Out</button>
        <button type="button" class="reel-tray-filter-pill" data-reel-filter="holiday">✈️ Holidays</button>
        <button type="button" class="reel-tray-filter-pill" data-reel-filter="birthday">🎂 Birthdays</button>
    </div>

    <div class="reels-tray-scroll" id="reelsTrayScroll">
        <!-- 1. Create Reel Card (Facebook-Style First Card) -->
        <div class="reel-fb-create-card" role="button" tabindex="0" data-bs-toggle="modal" data-bs-target="#createReelModal" aria-label="Create Family Reel">
            <div class="reel-fb-create-top">
                <img src="{{ !empty($data['img']) ? (str_starts_with($data['img'], '/') ? $data['img'] : '/resources/images/profile/' . $data['img']) : '/resources/images/profile/avatarM.png' }}" 
                     alt="You">
            </div>
            <div class="reel-fb-create-bottom">
                <div class="reel-fb-create-plus" aria-hidden="true">
                    <i class="bi bi-plus-lg"></i>
                </div>
                <span class="reel-fb-create-label">Create Reel</span>
            </div>
        </div>

        <!-- 2. Dynamic Video Cover Thumbnail Cards (Grouped by Creator - Facebook Standard) -->
        @forelse($effectiveCreatorGroups as $creatorGroup)
            @php
                $reelItem = $creatorGroup['latest_reel'] ?? ($creatorGroup['reels'][0] ?? []);
                $reelsCount = (int)($creatorGroup['reels_count'] ?? 1);
                $creatorAvatar = !empty($creatorGroup['profilePics']) ? (str_starts_with($creatorGroup['profilePics'], '/') ? $creatorGroup['profilePics'] : '/resources/images/profile/' . $creatorGroup['profilePics']) : '/resources/images/profile/avatarM.png';
                $rawThumb = !empty($reelItem['thumbnail_url']) ? (string)$reelItem['thumbnail_url'] : '';
                $coverThumb = (!empty($rawThumb) && !str_contains($rawThumb, 'placeholder.com')) ? $rawThumb : $creatorAvatar;
                $creatorName = ucwords(strtolower($creatorGroup['firstName'] ?? 'Family'));
                $tag = !empty($reelItem['category']) ? (string)$reelItem['category'] : 'Reel';
                $tagSlug = strtolower($tag);
                $videoUrl = !empty($reelItem['video_url']) ? (string)$reelItem['video_url'] : '';
                $aspectRatio = !empty($reelItem['aspect_ratio']) ? (string)$reelItem['aspect_ratio'] : '9:16';
                $isDirectVideo = (bool)preg_match('/\.(mp4|webm|mov|mkv|ogv)(\?.*)?$/i', $videoUrl) || str_starts_with($videoUrl, '/resources/videos/reels/');
                $isLandscape = in_array($aspectRatio, ['16:9', 'cinema'], true);
                $viewsCount = (int)($reelItem['views_count'] ?? 0);
                $likesCount = (int)($reelItem['likes_count'] ?? 0);
                $isOwner = isset($data['id']) && (string)$data['id'] === (string)($creatorGroup['user_id'] ?? '');
                $allCategories = implode(' ', array_map('strtolower', $creatorGroup['categories'] ?? [$tagSlug]));
            @endphp
            <a href="/reels?id={{ $reelItem['id'] }}"
               class="reel-fb-card {{ $isLandscape ? 'is-landscape' : '' }} {{ $reelsCount > 1 ? 'has-multiple-moments' : '' }}"
               data-reel-id="{{ $reelItem['id'] }}"
               data-reel-category="{{ $tagSlug }}"
               data-reel-categories="{{ $allCategories }}"
               data-video-url="{{ $videoUrl }}"
               data-thumb-url="{{ $rawThumb }}"
               data-aspect="{{ $aspectRatio }}"
               data-is-owner="{{ $isOwner ? '1' : '0' }}"
               title="{{ !empty($reelItem['caption']) ? $reelItem['caption'] : $creatorName . '\'s Family Moments (' . $reelsCount . ')' }}">

                <!-- Ambient Blurred Backdrop (prevents stretching or black bars on landscape 16:9 videos) -->
                <div class="reel-fb-ambient-bg" style="background-image: url('{{ $coverThumb }}');"></div>

                <!-- Static Cover Frame Image (Fallback / Poster) -->
                <img src="{{ $coverThumb }}" 
                     alt="{{ $creatorName }}'s Reel" 
                     class="reel-fb-thumb"
                     loading="lazy"
                     onerror="this.src='{{ $creatorAvatar }}'">

                <!-- True-Aspect Direct Video Frame & Always-On Silent Preview -->
                @if($isDirectVideo)
                    <video class="reel-fb-video-thumb"
                           src="{{ $videoUrl }}#t=0.8"
                           muted
                           loop
                           playsinline
                           webkit-playsinline
                           poster="{{ $coverThumb }}"
                           preload="none"></video>
                @endif

                <!-- Legibility Dark Gradient -->
                <div class="reel-fb-overlay"></div>

                <!-- Creator Avatar Ring (Top-Left) -->
                <div class="reel-fb-avatar-bubble">
                    <img src="{{ $creatorAvatar }}" alt="{{ $creatorName }}" onerror="this.src='/resources/images/profile/avatarM.png'">
                </div>

                <!-- Top-Right Play / Activity Badge -->
                <div class="reel-fb-play-badge">
                    @if($reelsCount > 1)
                        <span title="{{ $reelsCount }} moments by {{ $creatorName }}" class="badge rounded-pill bg-danger text-white border border-light me-1" style="font-size: 0.62rem; padding: 2px 6px;">
                            <i class="bi bi-collection-play-fill me-1"></i>{{ $reelsCount }}
                        </span>
                    @endif
                    <span title="{{ $viewsCount }} views" class="d-inline-flex align-items-center gap-1">
                        <i class="bi bi-eye-fill text-info" style="font-size: 0.65rem;"></i>
                        <span>{{ $viewsCount }}</span>
                    </span>
                    @if($likesCount > 0)
                        <span title="{{ $likesCount }} likes" class="d-inline-flex align-items-center gap-1 ms-1">
                            <i class="bi bi-heart-fill text-danger" style="font-size: 0.55rem;"></i>
                            <span>{{ $likesCount }}</span>
                        </span>
                    @endif
                </div>

                <!-- Creator Name & Category (Bottom) -->
                <div class="reel-fb-footer">
                    <div class="reel-fb-tag">#{{ str_replace('_', ' ', $tag) }}</div>
                    <div class="reel-fb-creator">{{ $creatorName }}</div>
                    @if(!empty($reelItem['caption']))
                        <div class="reel-fb-caption-snippet">{{ $reelItem['caption'] }}</div>
                    @elseif($reelsCount > 1)
                        <div class="reel-fb-caption-snippet">{{ $reelsCount }} moments shared</div>
                    @endif
                </div>
            </a>
        @empty
            <!-- Fallback Guide Card -->
            <a href="/reels" class="reel-fb-card d-flex flex-column align-items-center justify-content-center text-center p-2" style="background: linear-gradient(135deg, #2a0a0d 0%, #2a0a0d 100%);">
                <i class="bi bi-camera-reels-fill text-warning fs-2 mb-2"></i>
                <div class="small fw-bold text-white">Watch Family Reels</div>
            </a>
        @endforelse
    </div>
</div>
