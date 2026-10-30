<div class="reels-tray-wrapper" id="familyReelsTray">
    <!-- =========================================================
         Instagram Mirror: Mobile Stories Tray (< 768px Viewports)
         ========================================================= -->
    @php
        $effectiveCreatorGroups = $groupedReels ?? \App\model\Reel::groupReelsByCreator($recentReels ?? []);
    @endphp
    <div class="ig-stories-tray d-md-none" id="igMobileStoriesTray">
        <div class="ig-stories-scroll">
            <!-- 1. Your Story / Add Moment (Instagram Self Avatar with Plus Badge) -->
            <div class="ig-story-item" role="button" tabindex="0" data-bs-toggle="modal" data-bs-target="#createReelModal" aria-label="Add to your family story">
                <div class="ig-story-avatar-wrap is-self">
                    <img src="{{ !empty($data['img']) ? (str_starts_with($data['img'], '/') ? $data['img'] : '/resources/images/profile/' . $data['img']) : '/resources/images/profile/avatarM.png' }}" 
                         alt="Your Story" 
                         class="ig-story-avatar">
                    <div class="ig-story-plus-badge" title="Add Moment">
                        <i class="bi bi-plus-lg"></i>
                    </div>
                </div>
                <span class="ig-story-label">{{ __('feed.your_story', 'Your Story') }}</span>
            </div>

            <!-- 2. Dynamic Family Stories Circles (Grouped by Creator - Instagram Standard) -->
            @forelse($effectiveCreatorGroups as $creatorGroup)
                @php
                    $creatorAvatar = !empty($creatorGroup['profilePics']) ? (str_starts_with($creatorGroup['profilePics'], '/') ? $creatorGroup['profilePics'] : '/resources/images/profile/' . $creatorGroup['profilePics']) : '/resources/images/profile/avatarM.png';
                    $creatorFirstName = ucwords(strtolower($creatorGroup['firstName'] ?? 'Family'));
                    $isBirthday = !empty($creatorGroup['is_birthday']);
                    $reelsCount = (int)($creatorGroup['reels_count'] ?? 1);
                    $firstReel = $creatorGroup['latest_reel'] ?? ($creatorGroup['reels'][0] ?? null);
                    $firstReelId = (int)($firstReel['id'] ?? 0);
                    $momentsLabel = $reelsCount > 1 ? "{$reelsCount} moments by {$creatorFirstName}" : "Story by {$creatorFirstName}";
                @endphp
                <a href="/reels?id={{ $firstReelId }}" 
                   class="ig-story-item"
                   title="{{ $momentsLabel }}">
                    <div class="ig-story-avatar-wrap {{ $isBirthday ? 'is-gold' : 'is-active' }}">
                        <img src="{{ $creatorAvatar }}" 
                             alt="{{ $creatorFirstName }}" 
                             class="ig-story-avatar" 
                             loading="lazy" 
                             onerror="this.src='/resources/images/profile/avatarM.png'">
                        @if($reelsCount > 1)
                            <span class="ig-story-count-badge" title="{{ $reelsCount }} moments">{{ $reelsCount }}</span>
                        @endif
                    </div>
                    <span class="ig-story-label">{{ $creatorFirstName }}</span>
                </a>
            @empty
                <div class="ig-story-item text-muted opacity-75">
                    <div class="ig-story-avatar-wrap" style="background: var(--border-subtle, #e2e8f0);">
                        <div class="ig-story-avatar d-flex align-items-center justify-content-center bg-light text-muted">
                            <i class="bi bi-camera" style="font-size: 1.25rem;"></i>
                        </div>
                    </div>
                    <span class="ig-story-label">{{ __('feed.no_stories', 'No Stories') }}</span>
                </div>
            @endforelse
        </div>
    </div>

    <!-- =========================================================
         Facebook Mirror: Desktop Reels Card Carousel (>= 768px Viewports)
         ========================================================= -->
    <div class="d-none d-md-block">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="reel-tray-icon-badge">
                    <i class="bi bi-camera-reels-fill"></i>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.01em;">Family Reels &amp; Stories</h6>
                    <div class="text-muted" style="font-size: 0.72rem;">Short-form milestones, celebrations &amp; oral history</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-light rounded-pill fw-bold px-2.5 py-1 border" data-bs-toggle="modal" data-bs-target="#createReelModal" style="font-size: 0.75rem; min-height: 34px;">
                    <i class="bi bi-plus-circle-fill text-primary me-1"></i>New
                </button>
                <a href="/reels" class="text-decoration-none small fw-bold d-inline-flex align-items-center gap-1" style="color: var(--brand-primary); min-height: 34px;">
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
                    <div class="reel-fb-create-plus">
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <span class="reel-fb-create-label">Create Reel</span>
                </div>
            </div>

        <!-- 2. Dynamic Video Cover Thumbnail Cards (Grouped by Creator - Instagram Standard) -->
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

                <!-- True-Aspect Direct Video Frame & Hover Preview (Overrides stretched legacy canvas thumbs) -->
                @if($isDirectVideo)
                    <video class="reel-fb-video-thumb"
                           src="{{ $videoUrl }}#t=0.8"
                           muted
                           loop
                           playsinline
                           webkit-playsinline
                           preload="metadata"></video>
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
</div>

