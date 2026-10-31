{{-- Scoped Feed Post Card Component --}}
<div class="card border-0 shadow-sm mb-4 p-3 feed-post-card" :data-post-no="post.post_no" :id="'post' + post.post_no" :class="'post' + post.post_no" :style="(post.is_official || post.id === '{{ \App\services\FamilyPlatformAccountService::getAccountId() }}') ? 'border-radius: 12px; background-color: var(--card-bg); border-left: 4px solid #059669 !important;' : (isUserTaggedInPost(post) ? 'border-radius: 12px; background-color: var(--card-bg); border-left: 4px solid #a31621 !important;' : 'border-radius: 8px; background-color: var(--card-bg);')">

  <!-- Tagged with You Heritage Ribbon -->
  <template x-if="isUserTaggedInPost(post)">
    <div class="d-flex align-items-center justify-content-between mb-2.5 py-1 px-2.5 rounded-2 shadow-2xs" style="background: rgba(163,22,33,0.06); border: 1px solid rgba(163,22,33,0.18);">
      <div class="d-flex align-items-center gap-1.5" style="color: #8a1019; font-size: 0.78rem; font-weight: 600;">
        <i class="bi bi-tag-fill" style="font-size: 0.82rem;"></i>
        <span>{{ __('feed.tagged_banner', 'You are tagged in this family memory') }}</span>
      </div>
      <span class="badge rounded-pill bg-white text-muted border px-2 py-0.5 shadow-2xs" style="font-size: 0.7rem; font-weight: 500;">
        {{ __('feed.living_memory', 'Living Memory') }}
      </span>
    </div>
  </template>

  <!-- Post Author Header -->
  <div class="d-flex align-items-center mb-3">
    <a :href="'/profilepage/img?dir=img&pics=' + ((post.images && post.images[0]) ? post.images[0] : '') + '&pID=' + post.post_no + '&path=profile&id=' + post.id">
      <img :src="post.profileImg" alt="img" class="rounded-circle me-3" style="width:40px; height:40px; object-fit: cover;">
    </a>
    <div class="flex-grow-1">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div class="d-flex align-items-center flex-wrap">
            <h6 class="mb-0 fw-semibold text-dark" style="font-size: 1.05rem;" x-text="post.fullName"></h6>
            <template x-if="post.is_official || post.id === '{{ \App\services\FamilyPlatformAccountService::getAccountId() }}'">
              <span class="badge rounded-pill text-white ms-1.5 px-2 py-0.5 d-inline-flex align-items-center gap-1 shadow-2xs" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); font-size: 0.72rem; font-weight: 600;" title="Verified FamilyPlatform Account">
                <i class="bi bi-patch-check-fill" style="color: #ffffff; font-size: 0.75rem;"></i>
                <span>{{ __('feed.official_badge', 'Official Update') }}</span>
              </span>
            </template>
            <template x-if="post.post_tags && post.post_tags.length > 0">
              <span class="text-secondary small fw-normal ms-1">
                <span x-text="window.__ ? window.__('feed.is_with', 'is with') : 'is with'"></span> 
                <template x-for="(tag, tIdx) in getSortedTagsForViewer(post).slice(0, 2)" :key="tag.user_id">
                  <span>
                    <template x-if="isCurrentUser(tag.user_id)">
                      <span class="fw-bold text-primary" x-text="window.__ ? window.__('feed.you', 'you') : 'you'"></span>
                    </template>
                    <template x-if="!isCurrentUser(tag.user_id)">
                      <span class="d-inline-flex align-items-center">
                        <a :href="'/member/profilePage?id=' + tag.user_id" class="fw-semibold text-dark text-decoration-none" style="text-decoration: underline;" x-text="tag.fullName"></a>
                        <template x-if="tag.kinship && tag.kinship !== 'Family Member' && tag.kinship !== 'Relative' && tag.kinship !== 'You'">
                          <span class="badge rounded-pill bg-light text-muted border px-2 py-0.5 ms-0.5" style="font-size: 0.75rem; font-weight: 600;" x-text="tag.kinship"></span>
                        </template>
                      </span>
                    </template>
                    <span x-show="tIdx === 0 && post.post_tags.length === 2" x-text="' ' + (window.__ ? window.__('feed.and', 'and') : 'and') + ' '"></span>
                    <span x-show="tIdx === 0 && post.post_tags.length > 2">, </span>
                  </span>
                </template>
                <template x-if="post.post_tags.length > 2">
                  <span class="dropdown d-inline">
                    <a href="#" class="fw-semibold text-primary text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" x-text="(post.post_tags.length - 2) + ' ' + (window.__ ? window.__('feed.others', 'others') : 'others')"></a>
                    <ul class="dropdown-menu shadow-sm border-0 p-2" style="border-radius: 12px; min-width: 190px;">
                      <template x-for="tag in getSortedTagsForViewer(post).slice(2)" :key="tag.user_id">
                        <li>
                          <a :href="'/member/profilePage?id=' + tag.user_id" class="dropdown-item d-flex align-items-center justify-content-between py-1.5 px-2 rounded-2">
                            <div class="d-flex align-items-center gap-2">
                              <img :src="tag.profileImg || '/resources/images/profile/avatarM.png'" class="rounded-circle" width="24" height="24" style="object-fit: cover;">
                              <span class="small fw-medium" x-text="tag.fullName"></span>
                            </div>
                            <template x-if="tag.kinship && tag.kinship !== 'Family Member' && tag.kinship !== 'Relative' && tag.kinship !== 'You'">
                              <span class="badge rounded-pill bg-light text-muted border px-2 py-0.5 ms-1" style="font-size: 0.75rem; font-weight: 600;" x-text="tag.kinship"></span>
                            </template>
                          </a>
                        </li>
                      </template>
                    </ul>
                  </span>
                </template>
              </span>
            </template>
          </div>
          <div class="d-flex align-items-center gap-2 mt-0.5">
            <small class="text-muted" style="font-size: 0.85rem;" x-text="formatDate(post.post_time || post.date_created)"></small>
            <template x-if="post.is_official || post.id === '{{ \App\services\FamilyPlatformAccountService::getAccountId() }}'">
              <span class="badge bg-emerald-50 text-emerald-800 border border-emerald-300 rounded-pill" style="font-size: 0.78rem; font-weight: 600; padding: 2px 8px;">
                ✨ {{ __('feed.platform_announcement', 'Platform Announcement') }}
              </span>
            </template>
            <template x-if="!(post.id === 'FAMILY_PLATFORM_OFFICIAL' || post.is_official) && post.postFamCode">
              <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill" style="font-size: 0.78rem; font-weight: normal; padding: 2px 8px;" x-text="(window.__ ? window.__('feed.family_label', 'Family') : 'Family') + ': ' + post.postFamCode"></span>
            </template>
          </div>
        </div>
        <template x-if="isOwnPost(post) || isUserTaggedInPost(post)">
          <div class="dropdown">
            <button class="btn btn-sm btn-link text-muted p-1 rounded-circle" type="button" :id="'kebab' + post.post_no" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;" title="Post options - Edit or remove this post" aria-label="Post options">
              <i class="bi bi-three-dots" style="font-size: 1.25rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" :aria-labelledby="'kebab' + post.post_no" style="border-radius: 12px; font-size: 0.9rem; min-width: 160px;">
              <template x-if="isOwnPost(post)">
                <div>
                  <li>
                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#" @click.prevent="editPost(post)">
                      <i class="bi bi-pencil-square text-primary"></i> {{ __('feed.edit_post', 'Edit Post') }}
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#" style="min-height: 44px;" @click.prevent="repostPost(post)">
                      <i class="bi bi-arrow-repeat text-success"></i> {{ __('feed.repost_post', 'Repost') }}
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger" href="#" @click.prevent="deletePost(post.post_no)">
                      <i class="bi bi-trash3"></i> {{ __('feed.delete_post', 'Delete Post') }}
                    </a>
                  </li>
                </div>
              </template>
              <template x-if="isUserTaggedInPost(post)">
                <li>
                  <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-warning" href="#" @click.prevent="untagSelfFromPost(post)">
                    <i class="bi bi-tag-fill"></i> {{ __('feed.remove_tag', 'Remove Tag from Post') }}
                  </a>
                </li>
              </template>
            </ul>
          </div>
        </template>
      </div>
    </div>
  </div>

  <!-- Weekly Family Trivia Badge -->
  <template x-if="post.postMessage && post.postMessage.includes('Weekly Family Trivia')">
    <div class="px-1 mb-2">
      <span class="badge rounded-pill px-3 py-1.5 text-white shadow-sm d-inline-flex align-items-center gap-1" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.5px;">
        <i class="bi bi-trophy-fill"></i> {{ __('feed.trivia_badge', 'WEEKLY FAMILY TRIVIA') }}
      </span>
    </div>
  </template>

  <!-- Two Truths & a Lie Badge -->
  <template x-if="post.two_truths || (post.postMessage && post.postMessage.includes('Two Truths & a Lie'))">
    <div class="px-1 mb-2">
      <span class="badge rounded-pill px-3 py-1.5 text-white shadow-sm d-inline-flex align-items-center gap-1.5" style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.5px;">
        <i class="bi bi-mask"></i> TWO TRUTHS &amp; A LIE
      </span>
    </div>
  </template>

  <!-- Family Chronicle / Journal Badge -->
  <template x-if="post.postMessage && (post.postMessage.includes('Family Chronicle') || post.postMessage.includes('/familyJournal/'))">
    <div class="px-1 mb-2">
      <span class="badge rounded-pill px-3 py-1.5 text-white shadow-sm d-inline-flex align-items-center gap-1.5" style="background: linear-gradient(135deg, #b8202c 0%, #8a1019 100%); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.5px;">
        <i class="bi bi-journal-bookmark-fill"></i> {{ __('feed.chronicle_badge', 'FAMILY CHRONICLE') }}
      </span>
    </div>
  </template>

  <!-- Post Text (Prominent for Text-Only Posts) -->
  <template x-if="!post.two_truths && (post.displayMessage || (!post.video && post.postMessage)) && (!post.images || post.images.length === 0) && !post.video && !post.comparison">
    <div class="post-content px-1 mb-3">
      <p class="mb-0" style="white-space: pre-line; font-size: 1.05rem; color: var(--text-main); line-height: 1.55; font-family: var(--font-sans, -apple-system, system-ui, sans-serif);" x-html="renderHashtags(post.displayMessage || post.postMessage)"></p>
      
      <!-- On-Demand Post Translation Toggle for Diaspora Viewers -->
      <div class="mt-1 d-flex align-items-center gap-2" x-show="window.APP_LOCALE && window.APP_LOCALE !== 'en'">
        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 0.8rem; color: var(--brand-primary, #a31621); font-weight: 600;" @click="window.toggleTranslatePost ? window.toggleTranslatePost(post) : null">
          <i class="bi bi-translate"></i>
          <span x-text="post._isTranslated ? (window.__ ? window.__('feed.see_original', 'See original') : 'See original') : (post._isTranslating ? (window.__ ? window.__('feed.translating', 'Translating...') : 'Translating...') : (window.__ ? window.__('feed.translate', 'Translate') : 'Translate'))"></span>
        </button>
      </div>
    </div>
  </template>

  <!-- Direct Chronicle Read CTA -->
  <template x-if="post.postMessage && post.postMessage.includes('/familyJournal/')">
    <div class="px-1 mb-3">
      <a :href="((post.postMessage.match(/(?:https?:\/\/[^\s<>&\"']+)?\/familyJournal\/\d+/) || ['/familyJournal'])[0])" 
         class="btn btn-sm text-white d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold shadow-xs" 
         style="background: linear-gradient(135deg, #a31621 0%, #8a1019 100%); border-radius: 12px; font-size: 0.88rem; min-height: 44px; text-decoration: none;">
        <i class="bi bi-book-half"></i>
        <span>{{ __('feed.read_chronicle', 'Read Full Chronicle') }}</span>
        <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
  </template>

  <!-- Then & Now (before / after) — Living 2.0 Suite -->
  <template x-if="post.comparison">
    <div class="then-now px-1 mb-3"
         x-data="{
           pos: 50,
           side: false,
           mode: 'slider',
           playingWarp: false,
           warpTimer: null,
           teased: false,
           guessedYear: null,
           startAutoTease(el) {
             if (this.teased || typeof IntersectionObserver === 'undefined') return;
             if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
             const obs = new IntersectionObserver((entries) => {
               entries.forEach((entry) => {
                 if (entry.isIntersecting && !this.teased) {
                   this.teased = true;
                   obs.disconnect();
                   const keyframes = [50, 26, 74, 50];
                   let stepIdx = 0;
                   const stepMs = 340;
                   const runStep = () => {
                     if (stepIdx >= keyframes.length || this.playingWarp) return;
                     const start = this.pos;
                     const target = keyframes[stepIdx++];
                     const t0 = performance.now();
                     const tick = (now) => {
                       const p = Math.min(1, (now - t0) / stepMs);
                       const ease = 0.5 - Math.cos(p * Math.PI) / 2;
                       this.pos = Math.round((start + (target - start) * ease) * 10) / 10;
                       if (p < 1) requestAnimationFrame(tick);
                       else runStep();
                     };
                     requestAnimationFrame(tick);
                   };
                   runStep();
                 }
               });
             }, { threshold: 0.55 });
             obs.observe(el);
           },
           toggleTimeWarp(postObj) {
             if (this.playingWarp) {
               this.playingWarp = false;
               if (this.warpTimer) cancelAnimationFrame(this.warpTimer);
               this.warpTimer = null;
               return;
             }
             this.side = false;
             this.mode = 'morph';
             this.playingWarp = true;
             if ((postObj.voice_capsule_url || postObj.voice_url) && playingVoicePostNo !== postObj.post_no) {
               togglePlayVoiceCapsule(postObj);
             }
             const duration = 3600;
             const t0 = performance.now();
             const animate = (now) => {
               if (!this.playingWarp) return;
               const elapsed = (now - t0) % duration;
               const progress = elapsed / duration;
               // Smoothly oscillate 0 (Then) -> 100 (Now) -> 0 (Then)
               this.pos = Math.round(((1 - Math.cos(progress * 2 * Math.PI)) / 2) * 100);
               this.warpTimer = requestAnimationFrame(animate);
             };
             this.warpTimer = requestAnimationFrame(animate);
           }
         }"
         x-init="startAutoTease($el)">

      <!-- Stage (Slider Mode + Living Morph Cross-Dissolve Mode) -->
      <div class="then-now__stage vse-skeleton-surface" x-show="!side" style="aspect-ratio: 4 / 5; max-height: 520px;">
        <picture class="then-now__img" style="pointer-events: none; user-select: none; -webkit-user-drag: none;">
          <source :srcset="getPostImageVariantUrl(post.comparison.after_img, 'avif')" type="image/avif">
          <source :srcset="getPostImageVariantUrl(post.comparison.after_img, 'webp')" type="image/webp">
          <img class="w-100 h-100 object-fit-cover"
               :style="mode === 'morph' ? ('transform: scale(' + (1 + (pos / 1200)).toFixed(3) + '); transition: transform 0.12s linear; pointer-events: none; user-select: none; -webkit-user-drag: none;') : 'pointer-events: none; user-select: none; -webkit-user-drag: none;'"
               :src="getPostImageUrl(post.comparison.after_img)" alt="Now" loading="lazy" decoding="async">
        </picture>
        <picture class="then-now__img then-now__img--then"
                 :style="mode === 'morph'
                   ? ('opacity: ' + ((100 - pos) / 100).toFixed(3) + '; clip-path: none; -webkit-clip-path: none; pointer-events: none; user-select: none; -webkit-user-drag: none;')
                   : ('clip-path: inset(0 ' + (100 - pos) + '% 0 0); -webkit-clip-path: inset(0 ' + (100 - pos) + '% 0 0); pointer-events: none; user-select: none; -webkit-user-drag: none;')">
          <source :srcset="getPostImageVariantUrl(post.comparison.before_img, 'avif')" type="image/avif">
          <source :srcset="getPostImageVariantUrl(post.comparison.before_img, 'webp')" type="image/webp">
          <img class="w-100 h-100 object-fit-cover" :src="getPostImageUrl(post.comparison.before_img)" alt="Then" loading="lazy" decoding="async" style="pointer-events: none; user-select: none; -webkit-user-drag: none;">
        </picture>

        <!-- Then Tag (supports "Guess the Year" Mystery Veil) -->
        <span class="then-now__tag then-now__tag--then"
              x-text="(post.comparison.mystery_year && !guessedYear) ? '🎲 Guess Year?' : post.comparison.before_label"
              style="pointer-events: none; z-index: 6;"></span>

        <!-- Golden Time-Bridge Delta Pill / Genetic Echo Crown -->
        <template x-if="post.comparison.delta_badge && (!post.comparison.mystery_year || guessedYear)">
          <span class="then-now__delta-pill" x-text="post.comparison.delta_badge" style="pointer-events: none; z-index: 6;"></span>
        </template>

        <!-- Now Tag -->
        <span class="then-now__tag then-now__tag--now" x-text="post.comparison.after_label" style="pointer-events: none; z-index: 6;"></span>

        <!-- Slider Curtain Handle (visible in slider mode) -->
        <div class="then-now__handle" x-show="mode === 'slider'" :style="'left:' + pos + '%'" aria-hidden="true" style="pointer-events: none;"><i class="bi bi-arrows-expand-vertical"></i></div>

        <input type="range" min="0" max="100" step="1" x-model.number="pos" class="then-now__range"
               @input="playingWarp = false; (pos === 50 && typeof window.triggerHaptic === 'function') ? window.triggerHaptic('detent') : null"
               aria-label="Drag to compare Then and Now" style="z-index: 5; touch-action: none;">

        <!-- Interactive "Guess the Year" Mystery Overlay Bar -->
        <template x-if="post.comparison.mystery_year && post.comparison.mystery_options && post.comparison.mystery_options.length > 0">
          <div class="then-now__mystery-bar" style="z-index: 8;" @click.stop>
            <template x-if="!guessedYear">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-1.5 w-100">
                <span class="small fw-bold text-white"><i class="bi bi-dice-5-fill text-warning me-1"></i>Which year was "Then"?</span>
                <div class="d-flex align-items-center gap-1.5">
                  <template x-for="yr in post.comparison.mystery_options" :key="yr">
                    <button type="button" class="btn btn-sm rounded-pill fw-bold px-2.5 py-1 then-now__guess-chip"
                            @click.stop="guessedYear = yr; celebrateThenNowGuess($event, yr === post.comparison.true_year)"
                            x-text="yr"></button>
                  </template>
                </div>
              </div>
            </template>
            <template x-if="guessedYear">
              <div class="d-flex align-items-center justify-content-between w-100">
                <span class="small fw-bold"
                      :style="guessedYear === post.comparison.true_year ? 'color: #fce79a;' : 'color: #ffffff;'"
                      x-text="guessedYear === post.comparison.true_year
                        ? ('🎉 Spot on! It was ' + post.comparison.before_label + (post.comparison.delta_badge ? ' (' + post.comparison.delta_badge + ')' : ''))
                        : ('✨ Nice try! It was ' + post.comparison.before_label + (post.comparison.delta_badge ? ' (' + post.comparison.delta_badge + ')' : ''))"></span>
              </div>
            </template>
          </div>
        </template>

        <!-- Floating Voice Reflection Pill on Then & Now Stage -->
        <template x-if="post.voice_capsule_url || post.voice_url">
          <button type="button"
                  class="voice-capsule-floating-pill"
                  :class="playingVoicePostNo === post.post_no ? 'is-playing' : ''"
                  style="z-index: 7;"
                  @click.stop.prevent="togglePlayVoiceCapsule(post)"
                  :aria-label="playingVoicePostNo === post.post_no ? 'Pause voice story' : 'Play voice story'">
            <span class="voice-capsule-icon" aria-hidden="true">
              <i class="bi" :class="playingVoicePostNo === post.post_no ? 'bi-pause-fill' : 'bi-mic-fill'"></i>
            </span>
            <span class="voice-capsule-label" x-text="playingVoicePostNo === post.post_no ? 'Playing Voice Story' : '15s Voice Story'"></span>
          </button>
        </template>

        <button type="button" class="position-absolute end-0 bottom-0 m-2 btn btn-sm btn-dark bg-opacity-75 text-white rounded-circle shadow-sm border border-white border-opacity-25 d-flex align-items-center justify-content-center"
                style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; z-index: 8; cursor: pointer;"
                @click.stop="window.__expandThenNow ? window.__expandThenNow(post.comparison, { fullName: post.fullName, post_desc: post.post_desc || post.postMessage, date_created: post.date_created, post_no: post.post_no, voice_capsule_url: post.voice_capsule_url }) : null"
                aria-label="Expand comparison to full screen"
                title="Expand comparison">
          <i class="bi bi-arrows-angle-expand" style="font-size: 0.9rem;"></i>
        </button>
      </div>

      <!-- Side-by-Side Stage -->
      <div class="then-now__side" x-show="side" x-cloak>
        <figure class="cursor-pointer" style="cursor: pointer;" @click="window.__expandThenNow ? window.__expandThenNow(post.comparison, { fullName: post.fullName, post_desc: post.post_desc || post.postMessage, date_created: post.date_created, post_no: post.post_no, voice_capsule_url: post.voice_capsule_url }) : null" title="Click to expand">
          <picture>
            <source :srcset="getPostImageVariantUrl(post.comparison.before_img, 'avif')" type="image/avif">
            <source :srcset="getPostImageVariantUrl(post.comparison.before_img, 'webp')" type="image/webp">
            <img :src="getPostImageUrl(post.comparison.before_img)" alt="Then" loading="lazy">
          </picture>
          <figcaption x-text="post.comparison.before_label"></figcaption>
        </figure>
        <figure class="cursor-pointer" style="cursor: pointer;" @click="window.__expandThenNow ? window.__expandThenNow(post.comparison, { fullName: post.fullName, post_desc: post.post_desc || post.postMessage, date_created: post.date_created, post_no: post.post_no, voice_capsule_url: post.voice_capsule_url }) : null" title="Click to expand">
          <picture>
            <source :srcset="getPostImageVariantUrl(post.comparison.after_img, 'avif')" type="image/avif">
            <source :srcset="getPostImageVariantUrl(post.comparison.after_img, 'webp')" type="image/webp">
            <img :src="getPostImageUrl(post.comparison.after_img)" alt="Now" loading="lazy">
          </picture>
          <figcaption x-text="post.comparison.after_label"></figcaption>
        </figure>
      </div>

      <!-- Action Dock: Slide / Morph / Time-Warp / Side-by-Side / Expand / Add Yours / Tappable Family Chain Facepile -->
      <div class="then-now__actions">
        <button type="button" class="btn btn-sm btn-light border rounded-pill"
                @click="playingWarp = false; side = !side; if (!side) mode = 'slider';"
                x-text="side ? 'Slide to compare' : 'Side by side'"></button>

        <button type="button" class="btn btn-sm border rounded-pill d-inline-flex align-items-center gap-1"
                :class="playingWarp ? 'btn-dark text-white' : 'btn-light'"
                @click="toggleTimeWarp(post)"
                aria-label="Play Time-Warp morph animation">
          <i class="bi" :class="playingWarp ? 'bi-pause-circle-fill text-warning' : 'bi-magic text-danger'"></i>
          <span x-text="playingWarp ? 'Pause Warp' : 'Time-Warp'"></span>
        </button>

        <button type="button" class="btn btn-sm btn-light border rounded-pill d-inline-flex align-items-center gap-1"
                @click="window.__expandThenNow ? window.__expandThenNow(post.comparison, { fullName: post.fullName, post_desc: post.post_desc || post.postMessage, date_created: post.date_created, post_no: post.post_no, voice_capsule_url: post.voice_capsule_url }) : null"
                aria-label="Expand Then and Now comparison">
          <i class="bi bi-arrows-angle-expand"></i> {{ __('feed.expand_comparison', 'Expand') }}
        </button>

        <button type="button" class="btn btn-sm btn-primary rounded-pill" @click="window.__openThenNow(post.post_no)">
          <i class="bi bi-hourglass-split"></i> Add your Then &amp; Now
        </button>

        <!-- Tappable Family Time-Warp Chain Facepile Pill -->
        <template x-if="post.comparison.answer_count > 0">
          <button type="button"
                  class="btn btn-sm rounded-pill d-inline-flex align-items-center gap-1.5 px-2.5 py-1 then-now__chain-pill"
                  @click="window.__openThenNowChain ? window.__openThenNowChain(post.post_no) : null"
                  :aria-label="'View ' + post.comparison.answer_count + ' family Then and Now responses'">
            <span class="then-now__facepile d-inline-flex align-items-center">
              <template x-for="(av, avIdx) in (post.comparison.chain_avatars || []).slice(0, 3)" :key="av.post_no || avIdx">
                <img :src="av.profileImg" :alt="av.fullName" class="then-now__facepile-img" width="22" height="22">
              </template>
            </span>
            <span class="small fw-semibold"
                  x-text="post.comparison.answer_count + (post.comparison.answer_count === 1 ? ' family member joined in →' : ' family members joined in →')"></span>
          </button>
        </template>
      </div>
    </div>
  </template>

  <!-- Single Post Image (Hero Adaptive Natural Aspect Bleed with Ambient Aura) -->
  <template x-if="post.images && post.images.length === 1 && !post.comparison">
    <div class="mb-3 px-1">
      <div class="position-relative feed-media-aura-container">
        <!-- Ambient Dynamic Backlight Glow (Cinematic Aura) -->
        <div class="feed-media-aura-backdrop" aria-hidden="true">
          <img :src="(post.images[0] && (post.images[0].startsWith('/public/') || post.images[0].startsWith('/images/'))) ? post.images[0] : getPostImageUrl(post.images[0])" 
               alt="" 
               loading="lazy" 
               decoding="async" 
               aria-hidden="true">
        </div>

        <!-- Main Media Stage -->
        <div class="position-relative overflow-hidden post-media-stage vse-skeleton-surface" 
             :class="[
               playingVoicePostNo === post.post_no ? 'photo-voice-playing' : '',
               ((post.post_no || 0) % 3 === 0 ? 'living-drift-a' : ((post.post_no || 0) % 3 === 1 ? 'living-drift-b' : 'living-drift-c'))
             ]"
             style="border-radius:12px; width:100%; max-height: 600px; background: rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: center;">
          <a href="#" 
             @click.prevent="typeof handleImageTap === 'function' ? handleImageTap(post, post.images, 0, $event) : openLightbox(post.images, 0)" 
             @dblclick.prevent.stop="triggerHeartBurst(post, 0)"
             role="button" 
             aria-label="View enlarged photo or double tap to like" 
             style="display:block; overflow:hidden; border-radius:12px; position:relative; width:100%;">
            <picture class="w-100 d-block" style="border-radius: 12px; overflow: hidden;">
              <source :srcset="getPostImageVariantUrl(post.images[0], 'avif')" type="image/avif">
              <source :srcset="getPostImageVariantUrl(post.images[0], 'webp')" type="image/webp">
              <img :src="(post.images[0] && (post.images[0].startsWith('/public/') || post.images[0].startsWith('/images/'))) ? post.images[0] : getPostImageUrl(post.images[0])" 
                   class="post-media-first-img living-canvas-img w-100 d-block"
                   style="border-radius: 12px; width: 100%; height: auto; max-height: 600px; object-fit: contain; cursor: pointer;" 
                   alt="Family post image" 
                   loading="lazy"
                   decoding="async">
            </picture>
          </a>

          <!-- Holographic Dynasty Specular Foil Shimmer Sheen -->
          <div class="vse-holo-foil-glint" aria-hidden="true"></div>

          <!-- Floating Voice Capsule Keepsake Pill -->
          <template x-if="post.voice_capsule_url || post.voice_url">
            <button type="button" 
                    class="voice-capsule-floating-pill" 
                    :class="playingVoicePostNo === post.post_no ? 'is-playing' : ''"
                    @click.stop.prevent="togglePlayVoiceCapsule(post)"
                    :aria-label="playingVoicePostNo === post.post_no ? 'Pause voice keepsake' : 'Play voice keepsake attached to photo'">
              <span class="voice-capsule-icon" aria-hidden="true">
                <template x-if="playingVoicePostNo !== post.post_no">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                </template>
                <template x-if="playingVoicePostNo === post.post_no">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor">
                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                  </svg>
                </template>
              </span>
              <span class="voice-capsule-label" x-text="playingVoicePostNo === post.post_no ? 'Listening to Keepsake' : 'Voice Keepsake'"></span>
              <div class="audio-mini-waveform" aria-hidden="true">
                <span class="wave-bar bar-1"></span>
                <span class="wave-bar bar-2"></span>
                <span class="wave-bar bar-3"></span>
                <span class="wave-bar bar-4"></span>
              </div>
            </button>
          </template>

          <!-- Living Lineage Spotlight Trigger Pill -->
          <template x-if="post.post_tags && post.post_tags.length > 0">
            <button type="button" 
                    class="lineage-spotlight-pill" 
                    :class="activeSpotlightPostNo === post.post_no ? 'is-active' : ''"
                    @click.stop.prevent="toggleLineageSpotlight(post)"
                    :aria-label="activeSpotlightPostNo === post.post_no ? 'Close lineage spotlight' : 'View tagged family lineage'">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="7" r="4"></circle>
                <path d="M5.5 21v-2a6.5 6.5 0 0 1 13 0v2"></path>
              </svg>
              <span x-text="post.post_tags.length === 1 ? '1 Relative Tagged' : post.post_tags.length + ' Relatives Tagged'"></span>
            </button>
          </template>

          <!-- Living Lineage Spotlight Drawer (Kinship Tree Connections) -->
          <template x-if="post.post_tags && post.post_tags.length > 0">
            <div class="lineage-spotlight-drawer" 
                 :class="activeSpotlightPostNo === post.post_no ? 'is-open' : ''"
                 @click.stop>
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="lineage-drawer-title d-flex align-items-center">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1" aria-hidden="true">
                    <circle cx="12" cy="5" r="3"></circle>
                    <line x1="12" y1="8" x2="12" y2="14"></line>
                    <path d="M5 21v-2a7 7 0 0 1 14 0v2"></path>
                  </svg>
                  Lineage Connections
                </span>
                <button type="button" 
                        class="btn-close btn-close-white" 
                        style="font-size: 0.65rem; filter: brightness(0.9);" 
                        @click.stop.prevent="activeSpotlightPostNo = null"
                        aria-label="Close spotlight"></button>
              </div>
              <div class="lineage-tags-scroll d-flex gap-2 pb-1" style="overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
                <template x-for="tag in post.post_tags" :key="tag.user_id || tag.post_tag_no || tag.fullName">
                  <a :href="'/member/organogram?focus=' + (tag.user_id || '')" 
                     class="lineage-kin-card d-flex align-items-center text-decoration-none"
                     style="white-space: nowrap; flex-shrink: 0;"
                     :title="'Locate ' + (tag.fullName || 'relative') + ' on family tree'">
                    <img :src="tag.profileImg || '/images/default-avatar.png'" 
                         class="rounded-circle me-2 border border-warning" 
                         style="width: 28px; height: 28px; object-fit: cover;" 
                         alt="" 
                         loading="lazy">
                    <div class="d-flex flex-column text-start">
                      <span class="lineage-kin-name text-white fw-bold" style="font-size: 0.75rem; line-height: 1.1;" x-text="tag.fullName"></span>
                      <span class="lineage-kin-role" style="font-size: 0.68rem; color: #fde047 !important;" x-text="tag.kinship || 'Family Member'"></span>
                    </div>
                    <svg class="ms-1.5 opacity-75" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="#fde047" stroke-width="2" aria-hidden="true">
                      <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                  </a>
                </template>
              </div>
            </div>
          </template>

          <!-- Floating Generational Particle Celebration Fountain -->
          <div class="feed-heart-burst" 
               x-show="heartBursts[post.post_no + '_0']" 
               x-cloak 
               aria-hidden="true">
            <svg class="heart-burst-svg" viewBox="0 0 24 24" width="76" height="76" fill="#ff2d55" stroke="#ffffff" stroke-width="1.2">
              <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
            <div class="heirloom-sparkle-fountain" aria-hidden="true">
              <span class="sparkle-gem" style="--dx: 55px; --dy: -45px; --color: #ffd700;">✦</span>
              <span class="sparkle-gem" style="--dx: -50px; --dy: -55px; --color: #f59e0b;">★</span>
              <span class="sparkle-gem" style="--dx: 60px; --dy: 30px; --color: #fbbf24;">✦</span>
              <span class="sparkle-gem" style="--dx: -58px; --dy: 35px; --color: #f43f5e;">♥</span>
              <span class="sparkle-gem" style="--dx: 0px; --dy: -70px; --color: #ffd700;">★</span>
              <span class="sparkle-gem" style="--dx: 0px; --dy: 65px; --color: #10b981;">✦</span>
              <span class="sparkle-ring"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <!-- Multi-Image Instagram Swipeable Carousel (> 1 images) -->
  <template x-if="post.images && post.images.length > 1 && !post.comparison">
    <div class="mb-3 px-1 post-carousel-wrapper" x-data="{ currentIdx: 0 }">
      <div class="position-relative feed-media-aura-container">
        <!-- Ambient Dynamic Backlight Glow for Carousel -->
        <div class="feed-media-aura-backdrop" aria-hidden="true">
          <img :src="(post.images[0] && (post.images[0].startsWith('/public/') || post.images[0].startsWith('/images/'))) ? post.images[0] : getPostImageUrl(post.images[0])" 
               alt="" 
               loading="lazy" 
               decoding="async" 
               aria-hidden="true">
        </div>

        <div class="position-relative overflow-hidden post-media-stage vse-skeleton-surface" 
             :class="[
               playingVoicePostNo === post.post_no ? 'photo-voice-playing' : '',
               ((post.post_no || 0) % 3 === 0 ? 'living-drift-a' : ((post.post_no || 0) % 3 === 1 ? 'living-drift-b' : 'living-drift-c'))
             ]"
             style="border-radius:12px; aspect-ratio: 1 / 1; max-height: 540px; background: #0b0f19;">
          
          <!-- Floating Glassmorphic Slide Counter (1/3) -->
          <div class="position-absolute top-0 end-0 m-2.5 px-2.5 py-1 rounded-pill shadow-sm"
               style="background: rgba(15, 23, 42, 0.72); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: #ffffff; font-size: 0.75rem; font-weight: 600; z-index: 10; letter-spacing: 0.02em;">
            <span x-text="(currentIdx + 1) + '/' + post.images.length"></span>
          </div>

          <!-- Snap-Scroll Track -->
          <div class="d-flex overflow-auto post-carousel-track" 
               style="scroll-snap-type: x mandatory; scrollbar-width: none; -webkit-overflow-scrolling: touch; height: 100%;"
               @scroll.passive="currentIdx = Math.round($event.target.scrollLeft / ($event.target.clientWidth || 1))">
            <template x-for="(img, idx) in post.images" :key="idx">
              <div class="flex-shrink-0 w-100 position-relative carousel-slide-item" 
                   :class="currentIdx === idx ? 'is-slide-active' : ''"
                   style="scroll-snap-align: start; min-width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                <a href="#" 
                   @click.prevent="typeof handleImageTap === 'function' ? handleImageTap(post, post.images, idx, $event) : openLightbox(post.images, idx)" 
                   @dblclick.prevent.stop="triggerHeartBurst(post, idx)"
                   role="button" 
                   aria-label="View enlarged photo or double tap to like" 
                   style="display:block; overflow:hidden; border-radius:12px; position:relative; width:100%; height:100%;">
                  <picture class="w-100 h-100 d-block" style="border-radius: 12px; overflow: hidden;">
                    <source :srcset="getPostImageVariantUrl(img, 'avif')" type="image/avif">
                    <source :srcset="getPostImageVariantUrl(img, 'webp')" type="image/webp">
                    <img :src="(img && (img.startsWith('/public/') || img.startsWith('/images/'))) ? img : getPostImageUrl(img)" 
                         class="post-media-first-img living-canvas-img w-100 h-100"
                         style="border-radius: 12px; max-height: 540px; object-fit: contain; cursor: pointer;" 
                         alt="Family carousel slide" 
                         loading="lazy"
                         decoding="async">
                  </picture>
                </a>

                <!-- Floating Generational Particle Celebration Fountain for Slide -->
                <div class="feed-heart-burst" 
                     x-show="heartBursts[post.post_no + '_' + idx]" 
                     x-cloak 
                     aria-hidden="true">
                  <svg class="heart-burst-svg" viewBox="0 0 24 24" width="76" height="76" fill="#ff2d55" stroke="#ffffff" stroke-width="1.2">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                  </svg>
                  <div class="heirloom-sparkle-fountain" aria-hidden="true">
                    <span class="sparkle-gem" style="--dx: 55px; --dy: -45px; --color: #ffd700;">✦</span>
                    <span class="sparkle-gem" style="--dx: -50px; --dy: -55px; --color: #f59e0b;">★</span>
                    <span class="sparkle-gem" style="--dx: 60px; --dy: 30px; --color: #fbbf24;">✦</span>
                    <span class="sparkle-gem" style="--dx: -58px; --dy: 35px; --color: #f43f5e;">♥</span>
                    <span class="sparkle-gem" style="--dx: 0px; --dy: -70px; --color: #ffd700;">★</span>
                    <span class="sparkle-gem" style="--dx: 0px; --dy: 65px; --color: #10b981;">✦</span>
                    <span class="sparkle-ring"></span>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <!-- Holographic Dynasty Specular Foil Shimmer Sheen for Carousel -->
          <div class="vse-holo-foil-glint" aria-hidden="true"></div>

          <!-- Floating Voice Capsule Keepsake Pill for Carousel -->
          <template x-if="post.voice_capsule_url || post.voice_url">
            <button type="button" 
                    class="voice-capsule-floating-pill" 
                    :class="playingVoicePostNo === post.post_no ? 'is-playing' : ''"
                    @click.stop.prevent="togglePlayVoiceCapsule(post)"
                    :aria-label="playingVoicePostNo === post.post_no ? 'Pause voice keepsake' : 'Play voice keepsake attached to photo'">
              <span class="voice-capsule-icon" aria-hidden="true">
                <template x-if="playingVoicePostNo !== post.post_no">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor">
                    <path d="M8 5v14l11-7z"/>
                  </svg>
                </template>
                <template x-if="playingVoicePostNo === post.post_no">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor">
                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                  </svg>
                </template>
              </span>
              <span class="voice-capsule-label" x-text="playingVoicePostNo === post.post_no ? 'Listening to Keepsake' : 'Voice Keepsake'"></span>
              <div class="audio-mini-waveform" aria-hidden="true">
                <span class="wave-bar bar-1"></span>
                <span class="wave-bar bar-2"></span>
                <span class="wave-bar bar-3"></span>
                <span class="wave-bar bar-4"></span>
              </div>
            </button>
          </template>

          <!-- Living Lineage Spotlight Trigger Pill for Carousel -->
          <template x-if="post.post_tags && post.post_tags.length > 0">
            <button type="button" 
                    class="lineage-spotlight-pill" 
                    :class="activeSpotlightPostNo === post.post_no ? 'is-active' : ''"
                    @click.stop.prevent="toggleLineageSpotlight(post)"
                    :aria-label="activeSpotlightPostNo === post.post_no ? 'Close lineage spotlight' : 'View tagged family lineage'">
              <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="7" r="4"></circle>
                <path d="M5.5 21v-2a6.5 6.5 0 0 1 13 0v2"></path>
              </svg>
              <span x-text="post.post_tags.length === 1 ? '1 Relative Tagged' : post.post_tags.length + ' Relatives Tagged'"></span>
            </button>
          </template>

          <!-- Living Lineage Spotlight Drawer (Kinship Tree Connections) for Carousel -->
          <template x-if="post.post_tags && post.post_tags.length > 0">
            <div class="lineage-spotlight-drawer" 
                 :class="activeSpotlightPostNo === post.post_no ? 'is-open' : ''"
                 @click.stop>
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="lineage-drawer-title d-flex align-items-center">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" class="me-1" aria-hidden="true">
                    <circle cx="12" cy="5" r="3"></circle>
                    <line x1="12" y1="8" x2="12" y2="14"></line>
                    <path d="M5 21v-2a7 7 0 0 1 14 0v2"></path>
                  </svg>
                  Lineage Connections
                </span>
                <button type="button" 
                        class="btn-close btn-close-white" 
                        style="font-size: 0.65rem; filter: brightness(0.9);" 
                        @click.stop.prevent="activeSpotlightPostNo = null"
                        aria-label="Close spotlight"></button>
              </div>
              <div class="lineage-tags-scroll d-flex gap-2 pb-1" style="overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
                <template x-for="tag in post.post_tags" :key="tag.user_id || tag.post_tag_no || tag.fullName">
                  <a :href="'/member/organogram?focus=' + (tag.user_id || '')" 
                     class="lineage-kin-card d-flex align-items-center text-decoration-none"
                     style="white-space: nowrap; flex-shrink: 0;"
                     :title="'Locate ' + (tag.fullName || 'relative') + ' on family tree'">
                    <img :src="tag.profileImg || '/images/default-avatar.png'" 
                         class="rounded-circle me-2 border border-warning" 
                         style="width: 28px; height: 28px; object-fit: cover;" 
                         alt="" 
                         loading="lazy">
                    <div class="d-flex flex-column text-start">
                      <span class="lineage-kin-name text-white fw-bold" style="font-size: 0.75rem; line-height: 1.1;" x-text="tag.fullName"></span>
                      <span class="lineage-kin-role" style="font-size: 0.68rem; color: #fde047 !important;" x-text="tag.kinship || 'Family Member'"></span>
                    </div>
                    <svg class="ms-1.5 opacity-75" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="#fde047" stroke-width="2" aria-hidden="true">
                      <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                  </a>
                </template>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- Instagram Dynamic Animated Pagination Dots -->
      <div class="d-flex justify-content-center align-items-center gap-1.5 mt-2 post-carousel-dots" aria-hidden="true">
        <template x-for="(img, dIdx) in post.images" :key="dIdx">
          <span class="carousel-dot" :class="currentIdx === dIdx ? 'is-active' : ''"></span>
        </template>
      </div>
    </div>
  </template>

  <!-- Embedded Video (YouTube, Vimeo, Cloud Stream) -->
  <template x-if="post.video && (post.video.type === 'youtube' || post.video.type === 'vimeo' || post.video.type === 'cloudflare')">
    <div class="video-embed-container mb-3 px-1"
         x-data="{
           isShort: (post.video.originalUrl && post.video.originalUrl.includes('/shorts/'))
         }">
      <div class="rounded-3 overflow-hidden shadow-sm border border-light-subtle mx-auto position-relative"
           :class="isShort ? 'ratio ratio-9x16' : 'ratio ratio-16x9'"
           :style="isShort ? 'border-radius: 14px; max-height: 560px; max-width: 380px; background: #07090e;' : 'border-radius: 14px; max-height: 440px; background: #07090e;'">
        <iframe :src="post.video.embedUrl" 
                title="Video Player" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                allowfullscreen 
                loading="lazy"
                style="width: 100%; height: 100%; border: none;"></iframe>
      </div>
    </div>
  </template>

  <!-- Direct Video Stream (World-Class Instagram-Grade Adaptive Video Rendition) -->
  <template x-if="post.video && post.video.type === 'direct'">
    <div class="video-embed-container mb-3 px-1 position-relative" 
         x-data="{
           isVideoMuted: true,
           isPlaying: false,
           videoOrientation: 'portrait',
           videoRatio: '4 / 5',
           videoMaxHeight: '560px',
           isPortrait: true,
           hasMetadata: false,
           progressPercent: 0,
           currentTimeFormatted: '0:00',
           durationFormatted: '0:00',
           showPulse: false,
           pulseIcon: 'bi-play-fill',
           showControlsOverlay: false,
           formatTime(sec) {
             if (!sec || isNaN(sec)) return '0:00';
             const m = Math.floor(sec / 60);
             const s = Math.floor(sec % 60);
             return m + ':' + (s < 10 ? '0' : '') + s;
           },
           detectVideoDimensions(el) {
             if (!el || !el.videoWidth || !el.videoHeight) return;
             this.hasMetadata = true;
             const w = el.videoWidth;
             const h = el.videoHeight;
             const ratio = w / h;

             if (ratio < 0.88) {
               this.isPortrait = true;
               this.videoOrientation = 'portrait';
               if (ratio <= 0.65) {
                 this.videoRatio = '9 / 16';
                 this.videoMaxHeight = '580px';
               } else {
                 this.videoRatio = '4 / 5';
                 this.videoMaxHeight = '540px';
               }
             } else if (ratio > 1.15) {
               this.isPortrait = false;
               this.videoOrientation = 'landscape';
               this.videoRatio = (ratio >= 2.1) ? '21 / 9' : '16 / 9';
               this.videoMaxHeight = '440px';
             } else {
               this.isPortrait = false;
               this.videoOrientation = 'square';
               this.videoRatio = '1 / 1';
               this.videoMaxHeight = '480px';
             }
           },
           togglePlayback(videoEl) {
             if (!videoEl) return;
             if (videoEl.paused) {
               videoEl.play().then(() => {
                 this.isPlaying = true;
                 this.triggerPulse('bi-play-fill');
               }).catch(() => {});
             } else {
               videoEl.pause();
               this.isPlaying = false;
               this.triggerPulse('bi-pause-fill');
             }
           },
           triggerPulse(icon) {
             this.pulseIcon = icon;
             this.showPulse = true;
             setTimeout(() => { this.showPulse = false; }, 500);
           },
           toggleMute(videoEl) {
             if (!videoEl) return;
             videoEl.muted = !videoEl.muted;
             this.isVideoMuted = videoEl.muted;
           },
           handleTimeUpdate(videoEl) {
             if (!videoEl) return;
             const cur = videoEl.currentTime;
             const dur = videoEl.duration;
             if (dur && dur > 0) {
               this.progressPercent = Math.min(100, Math.max(0, (cur / dur) * 100));
               this.currentTimeFormatted = this.formatTime(cur);
               this.durationFormatted = this.formatTime(dur);
             }
             if (post.video && post.video.trimEnd !== null && cur >= post.video.trimEnd) {
               videoEl.currentTime = post.video.trimStart || 0;
             }
           },
           seekVideo(event, videoEl) {
             if (!videoEl || !videoEl.duration) return;
             const rect = event.currentTarget.getBoundingClientRect();
             const clickX = event.clientX - rect.left;
             const ratio = Math.max(0, Math.min(1, clickX / rect.width));
             videoEl.currentTime = ratio * videoEl.duration;
           }
         }"
         x-init="$nextTick(() => { const v = $el.querySelector('video'); if (v && v.videoWidth) detectVideoDimensions(v); })">
      
      <!-- Outer Adaptive Container with Ambient Studio Glow -->
      <div class="video-rendition-frame position-relative overflow-hidden mx-auto shadow-sm"
           @mouseenter="showControlsOverlay = true"
           @mouseleave="showControlsOverlay = false"
           :style="'aspect-ratio: ' + videoRatio + '; max-height: ' + videoMaxHeight + '; width: 100%; max-width: ' + (isPortrait ? (videoRatio === '9 / 16' ? '400px' : '460px') : '100%') + '; border-radius: 14px; background: #07090e; transition: aspect-ratio 0.25s cubic-bezier(0.4, 0, 0.2, 1), max-height 0.25s cubic-bezier(0.4, 0, 0.2, 1), max-width 0.25s cubic-bezier(0.4, 0, 0.2, 1);'">

        <!-- Ambient Studio Glow Backdrop (Instagram / YouTube Studio Ambient Mode) -->
        <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden" style="border-radius: 14px; pointer-events: none; z-index: 1;">
          <div class="w-100 h-100" style="background: radial-gradient(circle at center, rgba(30, 41, 59, 0.55) 0%, rgba(7, 9, 14, 0.98) 100%);"></div>
        </div>

        <!-- Main Video Element -->
        <video class="feed-post-video w-100 h-100 position-relative"
               :src="post.video.embedUrl"
               playsinline
               webkit-playsinline
               muted
               loop
               preload="metadata"
               @click.stop="togglePlayback($el)"
               @loadedmetadata="
                 detectVideoDimensions($el);
                 if (post.video && post.video.trimStart !== null && $el.currentTime < post.video.trimStart) { 
                   $el.currentTime = post.video.trimStart; 
                 }
               "
               @play="
                 isPlaying = true;
                 if (post.video && post.video.trimStart !== null && ($el.currentTime < post.video.trimStart || (post.video.trimEnd !== null && $el.currentTime >= post.video.trimEnd))) { 
                   $el.currentTime = post.video.trimStart; 
                 }
               "
               @pause="isPlaying = false"
               @timeupdate="handleTimeUpdate($el)"
               @volumechange="isVideoMuted = $el.muted"
               style="width: 100%; height: 100%; object-fit: contain; z-index: 2; border-radius: 14px; cursor: pointer; background: transparent;"></video>

        <!-- Instagram Animated Center Pulse Ripple (Play / Pause Feedback) -->
        <div x-cloak x-show="showPulse" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-75"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-125"
             class="position-absolute top-50 start-50 translate-middle pointer-events-none d-flex align-items-center justify-content-center"
             style="z-index: 7; width: 72px; height: 72px; border-radius: 9999px; background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.25); color: #ffffff; box-shadow: 0 8px 24px rgba(0,0,0,0.45);">
          <i class="bi fs-1" :class="pulseIcon" style="color: #ffffff;"></i>
        </div>

        <!-- Instagram Format Badge (Top-Left) -->
        <div class="position-absolute d-inline-flex align-items-center gap-1.5 shadow-sm"
             style="top: 14px; left: 14px; z-index: 6; pointer-events: none;">
          <template x-if="isPortrait">
            <span class="badge rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1"
                  style="background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.22); color: #ffffff; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em;">
              <i class="bi bi-phone-fill text-info" style="font-size: 0.75rem;"></i>
              <span>PORTRAIT HD</span>
            </span>
          </template>
          <template x-if="!isPortrait && videoOrientation === 'landscape'">
            <span class="badge rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1"
                  style="background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.22); color: #ffffff; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em;">
              <i class="bi bi-badge-hd-fill text-warning" style="font-size: 0.75rem;"></i>
              <span>SUPER HD</span>
            </span>
          </template>
          <template x-if="!isPortrait && videoOrientation === 'square'">
            <span class="badge rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1"
                  style="background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.22); color: #ffffff; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em;">
              <i class="bi bi-square-fill text-success" style="font-size: 0.7rem;"></i>
              <span>SQUARE HD</span>
            </span>
          </template>
        </div>

        <!-- Instagram 1-Tap Sound Toggle Pill (Top-Right) -->
        <button type="button" 
                class="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                @click.stop="toggleMute($el.closest('.video-rendition-frame').querySelector('video'))"
                :title="isVideoMuted ? 'Unmute video' : 'Mute video'"
                :aria-label="isVideoMuted ? 'Unmute video' : 'Mute video'"
                style="top: 14px; right: 14px; z-index: 6; width: 44px; height: 44px; min-width: 44px; min-height: 44px; border-radius: 9999px; background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.25); color: #ffffff; cursor: pointer; transition: transform 0.15s ease, background 0.15s ease; white-space: nowrap; flex-shrink: 0;">
          <i class="bi" :class="isVideoMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'" style="font-size: 1.15rem; color: #ffffff;"></i>
        </button>

        <!-- Instagram Bottom Overlay Controls & Progress Scrubber -->
        <div class="position-absolute bottom-0 start-0 w-100" 
             style="z-index: 6; background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.35) 60%, transparent 100%); padding: 24px 12px 10px 12px;">
          
          <!-- Mini Control Strip: Play/Pause, Timestamps, Fullscreen -->
          <div class="d-flex align-items-center justify-content-between text-white mb-1.5" style="font-size: 0.75rem; font-weight: 600;">
            <div class="d-flex align-items-center gap-2">
              <button type="button" 
                      class="btn btn-link text-white p-0 d-inline-flex align-items-center justify-content-center text-decoration-none"
                      @click.stop="togglePlayback($el.closest('.video-rendition-frame').querySelector('video'))"
                      :title="isPlaying ? 'Pause video' : 'Play video'"
                      :aria-label="isPlaying ? 'Pause video' : 'Play video'"
                      style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; white-space: nowrap; flex-shrink: 0;">
                <i class="bi fs-5" :class="isPlaying ? 'bi-pause-fill' : 'bi-play-fill'" style="color: #ffffff;"></i>
              </button>
              <span class="font-monospace" style="letter-spacing: 0.02em;">
                <span x-text="currentTimeFormatted">0:00</span> / <span x-text="durationFormatted">0:00</span>
              </span>
            </div>

            <div class="d-flex align-items-center gap-1.5">
              <!-- Fullscreen Button -->
              <button type="button" 
                      class="btn btn-link text-white p-0 d-inline-flex align-items-center justify-content-center text-decoration-none"
                      @click.stop="
                        const v = $el.closest('.video-rendition-frame').querySelector('video');
                        if (v) {
                          if (v.requestFullscreen) { v.requestFullscreen(); }
                          else if (v.webkitRequestFullscreen) { v.webkitRequestFullscreen(); }
                        }
                      "
                      title="View Fullscreen"
                      aria-label="View Fullscreen"
                      style="width: 36px; height: 36px; min-width: 36px; min-height: 36px; white-space: nowrap; flex-shrink: 0;">
                <i class="bi bi-arrows-fullscreen" style="font-size: 0.95rem; color: #ffffff;"></i>
              </button>
            </div>
          </div>

          <!-- Interactive Instagram Scrubber Bar -->
          <div class="video-scrubber-track position-relative w-100" 
               @click.stop="seekVideo($event, $el.closest('.video-rendition-frame').querySelector('video'))"
               style="height: 6px; background: rgba(255, 255, 255, 0.28); border-radius: 9999px; cursor: pointer; overflow: hidden;">
            <div class="video-scrubber-fill h-100" 
                 :style="'width: ' + progressPercent + '%; background: #ffffff; border-radius: 9999px; box-shadow: 0 0 8px rgba(255, 255, 255, 0.85); transition: width 0.08s linear;'"></div>
          </div>

        </div>

      </div>
    </div>
  </template>

  <!-- Full Graphics OpenGraph Social Card (16:9) -->
  <template x-if="post.link_preview && (post.link_preview.image || post.link_preview.title) && !(post.postMessage || '').includes('/familyJournal/')">
    <div class="link-preview-container mb-3 px-1">
      <a :href="post.link_preview.url" 
         target="_blank" 
         rel="noopener noreferrer" 
         class="card border text-decoration-none overflow-hidden link-preview-card shadow-sm"
         style="border-radius: 14px; background-color: var(--bs-card-bg, #ffffff); border-color: var(--bs-border-color, #e2e8f0); transition: transform 0.2s ease, box-shadow 0.2s ease; display: block;">
        
        <!-- Full 16:9 Graphics Banner -->
        <template x-if="post.link_preview.image">
          <div class="position-relative overflow-hidden w-100" style="aspect-ratio: 16 / 9; background-color: #0f172a; max-height: 420px;">
            <img :src="post.link_preview.image" 
                 class="w-100 h-100 object-fit-cover" 
                 :alt="post.link_preview.title || 'Event preview image'"
                 loading="lazy"
                 onerror="this.style.display='none'">
            
            <!-- Platform Badge Pill -->
            <div class="position-absolute top-0 start-0 m-2.5">
              <span class="badge rounded-pill text-uppercase px-2.5 py-1.5 shadow-sm d-inline-flex align-items-center gap-1"
                    style="background: rgba(15, 23, 42, 0.82); backdrop-filter: blur(8px); font-size: 0.72rem; letter-spacing: 0.5px; border: 1px solid rgba(255, 255, 255, 0.18); color: #ffffff;">
                <template x-if="post.link_preview.is_party_event">
                  <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-balloon-fill text-warning"></i> PartyPlatform</span>
                </template>
                <template x-if="!post.link_preview.is_party_event">
                  <span class="d-inline-flex align-items-center gap-1"><i class="bi bi-link-45deg"></i> <span x-text="post.link_preview.domain || 'External Link'"></span></span>
                </template>
              </span>
            </div>
          </div>
        </template>

        <!-- Card Meta Details -->
        <div class="p-3" style="background-color: var(--bs-card-bg, #ffffff);">
          <div class="text-uppercase text-muted fw-semibold small mb-1 d-flex align-items-center justify-content-between" style="font-size: 0.74rem; letter-spacing: 0.5px;">
            <span class="text-truncate" x-text="post.link_preview.domain || post.link_preview.site_name"></span>
            <span class="text-primary small d-inline-flex align-items-center gap-1">
              <span x-text="post.link_preview.is_party_event ? 'Celebration Pass' : 'Web Link'"></span>
              <i class="bi bi-box-arrow-up-right" style="font-size: 0.7rem;"></i>
            </span>
          </div>

          <h6 class="fw-bold mb-1.5 text-dark" style="font-size: 1.05rem; line-height: 1.35;" x-text="post.link_preview.title"></h6>

          <template x-if="post.link_preview.description">
            <p class="text-secondary small mb-2.5" style="line-height: 1.45; font-size: 0.86rem; color: #64748b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" x-text="post.link_preview.description"></p>
          </template>

          <div class="pt-2 border-top d-flex justify-content-between align-items-center">
            <span class="small text-muted text-truncate" style="font-size: 0.78rem; max-width: 60%;" x-text="post.link_preview.url"></span>
            <span class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                  style="min-height: 40px; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0;">
              <span x-text="post.link_preview.cta_label || 'View Event / RSVP →'"></span>
            </span>
          </div>
        </div>

      </a>
    </div>
  </template>

  <!-- Two Truths & a Lie: playable game card -->
  <template x-if="post.two_truths">
    <div class="tt-feed-card mx-1 mb-3" role="group" :aria-label="'Two Truths and a Lie: ' + (post.two_truths.title || '')">
      <div class="tt-feed-head">
        <div class="tt-feed-eyebrow"><i class="bi bi-mask"></i> Spot the lie</div>
        <h6 class="tt-feed-title" :title="post.two_truths.title" x-text="post.two_truths.title"></h6>
        <p class="tt-feed-sub mb-0">
          <span x-text="post.two_truths.author_name || post.fullName"></span> says two are true and one is a lie.
        </p>
      </div>

      <div class="tt-feed-options">
        <template x-for="idx in [1, 2, 3]" :key="idx">
          <button type="button"
                  class="tt-opt"
                  :class="{
                    'is-picked': post.two_truths.user_vote && Number(post.two_truths.user_vote.guessed_lie_index) === idx,
                    'is-lie': post.two_truths.status === 'revealed' && Number(post.two_truths.lie_index) === idx,
                    'is-truth': post.two_truths.status === 'revealed' && Number(post.two_truths.lie_index) !== idx,
                    'is-locked': post.two_truths.has_voted || post.two_truths.is_author || post.two_truths.status !== 'active'
                  }"
                  :disabled="post.two_truths.has_voted || post.two_truths.is_author || post.two_truths.status !== 'active' || isVotingTwoTruths(post.post_no)"
                  @click="voteTwoTruths(post, idx)"
                  :aria-label="'Statement ' + idx + ': ' + post.two_truths['statement_' + idx]">
            <span class="tt-opt-num" x-text="idx"></span>
            <span class="tt-opt-text" x-text="post.two_truths['statement_' + idx]"></span>
            <template x-if="post.two_truths.vote_percentages">
              <span class="tt-opt-pct" x-text="twoTruthsPercent(post.two_truths, idx) + '%'"></span>
            </template>
            <template x-if="post.two_truths.vote_percentages">
              <span class="tt-opt-bar" :style="'width:' + twoTruthsPercent(post.two_truths, idx) + '%'"></span>
            </template>
            <span x-show="isVotingTwoTruths(post.post_no)" class="spinner-border spinner-border-sm tt-opt-spin" role="status" aria-hidden="true"></span>
          </button>
        </template>
      </div>

      <div class="tt-feed-foot">
        <template x-if="post.two_truths.status === 'active' && !post.two_truths.has_voted && !post.two_truths.is_author">
          <span class="tt-chip tt-chip-cta"><i class="bi bi-hand-index-thumb"></i> Tap the statement you think is the lie</span>
        </template>
        <template x-if="post.two_truths.has_voted && post.two_truths.status === 'active'">
          <span class="tt-chip tt-chip-ok" :class="{ 'tt-pop': post.two_truths.just_voted }"><i class="bi bi-lock-fill"></i> Guess locked in. Reveal soon!</span>
        </template>
        <template x-if="post.two_truths.is_author && post.two_truths.status === 'active'">
          <span class="tt-chip"><i class="bi bi-stars"></i> Your challenge is live</span>
        </template>
        <template x-if="post.two_truths.status === 'revealed' && post.two_truths.user_vote">
          <span class="tt-chip" :class="Number(post.two_truths.user_vote.is_correct) === 1 ? 'tt-chip-ok' : ''">
            <i class="bi bi-trophy-fill"></i>
            <span x-text="Number(post.two_truths.user_vote.is_correct) === 1 ? ('You spotted it! +' + post.two_truths.user_vote.points_awarded + ' pts') : ('Fooled! +' + post.two_truths.user_vote.points_awarded + ' pts for playing')"></span>
          </span>
        </template>
        <span class="tt-chip tt-chip-muted"><i class="bi bi-people-fill"></i> <span x-text="(post.two_truths.total_votes || 0) + ((post.two_truths.total_votes || 0) === 1 ? ' guess' : ' guesses')"></span></span>
      </div>
      <p class="tt-feed-error" x-show="post.two_truths.error_message" x-text="post.two_truths.error_message" role="alert"></p>
    </div>
  </template>

  <!-- Poll Section -->
  <template x-if="post.poll && post.poll.options && post.poll.options.length > 0">
    <div class="poll-container mt-2 p-3 bg-light rounded border border-light-subtle mb-3 mx-1">
      <h6 class="fw-bold mb-3" style="font-size: 0.9rem;"><i class="fa fa-bar-chart me-2 text-primary"></i><span x-text="post.poll.question"></span></h6>
      <div class="poll-options">
        <template x-for="opt in post.poll.options" :key="opt.option_id">
          <div class="poll-option mb-2 position-relative">
            <button type="button" 
                    @click="votePoll(post.post_no, opt.option_id)" 
                    :disabled="isVotingPoll(post.post_no)"
                    class="poll-option-btn w-100 text-start btn position-relative overflow-hidden" 
                    :class="[
                      isOptionVoted(post, opt.option_id) ? 'btn-outline-primary' : 'btn-outline-secondary',
                      isVotingPoll(post.post_no) ? 'opacity-75 cursor-not-allowed' : ''
                    ]"
                    style="z-index: 1; font-size: 0.85rem; padding: 8px 12px; min-height: 44px;">
              <span class="position-relative d-inline-flex align-items-center gap-1" style="z-index: 2;">
                <span x-text="opt.option_text"></span>
                <span x-show="isVotingPoll(post.post_no)" class="spinner-border spinner-border-sm ms-1 text-primary" role="status" aria-hidden="true" style="width: 0.85rem; height: 0.85rem;"></span>
              </span>
              <span class="float-end position-relative fw-bold" style="z-index: 2;" x-text="(opt.percentage || 0) + '%'"></span>
              <div class="position-absolute top-0 start-0 h-100" 
                   :class="isOptionVoted(post, opt.option_id) ? 'bg-primary' : 'bg-secondary'" 
                   :style="'width: ' + (opt.percentage || 0) + '%; opacity: 0.15; transition: width 0.5s ease; z-index: 0;'"></div>
            </button>
          </div>
        </template>
      </div>
      <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;" x-text="(post.poll.total_votes || 0) + ' votes'"></small>
    </div>
  </template>

  <!-- Actions Bar: Super-Premium Minimalist Action Row (Instagram/Apple Standard) -->
  <div class="d-flex align-items-center justify-content-between pt-2 pb-1 mb-1 feed-actions-row">
    <div class="d-flex align-items-center gap-2">

      <!-- Like Button with floating emoji reaction bar -->
      <div class="position-relative"
           @mouseenter="togglePostReactionBar(post.post_no, true)"
           @mouseleave="togglePostReactionBar(post.post_no, false)"
           @click.outside="activeReactionBars[post.post_no] = false">

        <!-- Floating Reaction Bar (Living Heirloom Glass Dock) -->
        <div x-show="activeReactionBars[post.post_no]"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @mouseenter="keepPostReactionBar(post.post_no, true)"
             @mouseleave="keepPostReactionBar(post.post_no, false)"
             class="reaction-bar-float heirloom-glass-dock"
             style="position:absolute; bottom:calc(100% + 8px); left:0; transform:none; display:flex; gap:6px; z-index:100; white-space:nowrap;">
          <template x-for="type in Object.keys(emojiMap)" :key="type">
            <button type="button"
                    @click="reactToPost(post.post_no, type)"
                    :title="emojiMap[type].label"
                    :aria-label="emojiMap[type].label"
                    :class="post.user_reaction === type ? 'heirloom-reaction-btn active' : 'heirloom-reaction-btn'"
                    :style="'--heirloom-glow:' + (emojiMap[type].glow || 'rgba(225,29,72,0.45)')">
              <span class="heirloom-glass-glyph d-inline-flex align-items-center justify-content-center" x-html="getHeirloomGlassSvg(type, 34)"></span>
              <span class="heirloom-tooltip-pill" x-text="emojiMap[type].label"></span>
            </button>
          </template>
        </div>

        <!-- Like / Active Reaction Button (Spring Micro-Pop) -->
        <button class="btn btn-action-glyph btn-action-like rounded-circle position-relative heirloom-trigger-btn"
                :class="post.user_reaction ? ('is-liked reaction-' + post.user_reaction) : ''"
                type="button"
                :title="post.user_reaction ? ('Reacted: ' + (emojiMap[post.user_reaction]?.label || 'Liked') + ' - Click to change reaction') : 'React with heirloom glass love and appreciation'"
                :aria-label="post.user_reaction ? ('Reacted ' + (emojiMap[post.user_reaction]?.label || 'Liked')) : 'Like this post'"
                @click="onPostLikeClick(post.post_no, post.user_reaction || 'love')"
                @touchstart.passive="startPostLongPress(post.post_no)"
                @touchend="cancelPostLongPress()"
                @touchmove="cancelPostLongPress()"
                style="min-width: 44px; min-height: 44px;">
          <!-- 1. Default Unreacted: Crisp Outline Heart -->
          <template x-if="!post.user_reaction">
            <i class="bi bi-heart" style="font-size: 1.35rem; line-height: 1;"></i>
          </template>
          <!-- 2. Expressive Reactions: Living Heirloom Glass 3D Vector Icon -->
          <template x-if="post.user_reaction">
            <span class="d-inline-flex align-items-center justify-content-center heirloom-active-icon"
                  x-html="getHeirloomGlassSvg(post.user_reaction, 24)"></span>
          </template>
        </button>
      </div>

      <!-- Comment Button -->
      <button class="btn btn-action-glyph btn-action-comment rounded-circle"
              style="min-width: 44px; min-height: 44px;"
              type="button"
              title="Write or view family comments on this post"
              aria-label="Comment on this post"
              @click="toggleCommentForm(post.post_no)">
        <i class="bi bi-chat" style="font-size: 1.35rem; line-height: 1;"></i>
      </button>


    </div>

    <!-- Bookmark / Save Memory Button (Right-Aligned) -->
    <div>
      <button class="btn btn-action-glyph btn-action-bookmark rounded-circle"
              :class="post._saved ? 'is-saved' : ''"
              style="min-width: 44px; min-height: 44px;"
              type="button"
              title="Save memory to family collection"
              aria-label="Save this family memory"
              @click="window.__saveMemory ? window.__saveMemory(post.post_no) : (post._saved = !post._saved)">
        <i :class="post._saved ? 'bi bi-bookmark-fill' : 'bi bi-bookmark'" style="font-size: 1.25rem; line-height: 1;"></i>
      </button>
    </div>
  </div>

  <!-- Social Proof & Engagement Meta (Reaction & Pure Count - No "Like" Label on Empathy) -->
  <div class="d-flex justify-content-between align-items-center px-1 pb-1 pt-1" style="font-size: 0.85rem;" x-show="post.post_likes > 0 || post.views_count > 0 || (post.comments && post.comments.length > 0)">
    <div>
      <template x-if="post.post_likes > 0">
        <span class="d-inline-flex align-items-center gap-1 fw-bold" style="font-size: 0.88rem; color: var(--text-main, #0f172a);">
          <span class="d-inline-flex align-items-center justify-content-center" style="line-height: 1;"
                x-html="getHeirloomGlassSvg(post.user_reaction || 'love', 18)"></span>
          <span x-text="post.post_likes"></span>
        </span>
      </template>
    </div>
    <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.825rem;">
      <!-- Post View Count -->
      <span class="d-inline-flex align-items-center gap-1 post-views-count"
            :class="isOwnPost(post) ? 'cursor-pointer text-decoration-none' : ''"
            :role="isOwnPost(post) ? 'button' : undefined"
            :tabindex="isOwnPost(post) ? '0' : undefined"
            @click="isOwnPost(post) && openPostViewersModal(post)"
            @keydown.enter="isOwnPost(post) && openPostViewersModal(post)"
            :title="isOwnPost(post) ? 'Click to see who viewed your post' : ((post.views_count || 0) + ' family views')"
            :style="isOwnPost(post) ? 'cursor: pointer;' : ''">
        <i class="bi bi-eye text-secondary" :class="isOwnPost(post) ? 'text-primary' : ''"></i>
        <span x-text="post.views_count || 0"></span>
        <span x-text="(post.views_count === 1) ? (window.__ ? window.__('feed.view', 'view') : 'view') : (window.__ ? window.__('feed.views', 'views') : 'views')"></span>
      </span>
      <span x-show="post.comments && post.comments.length > 0">
        • <span x-text="post.comments.length"></span> <span x-text="(post.comments.length === 1) ? (window.__ ? window.__('feed.comment_single', 'comment') : 'comment') : (window.__ ? window.__('feed.comments', 'comments') : 'comments')"></span>
      </span>
    </div>
  </div>

  <!-- Instagram Inline Caption Format (Under Likes / Action Row for Posts with Media) -->
  <template x-if="(post.displayMessage || (!post.video && post.postMessage)) && ((post.images && post.images.length > 0) || post.video || post.comparison)">
    <div class="post-caption-block px-1 mb-2 pt-1" style="font-size: 0.95rem; line-height: 1.5;">
      <a :href="'/member/profilePage?id=' + post.id" class="fw-bold text-decoration-none me-1.5" style="color: var(--text-main, #0f172a); font-weight: 600;" x-text="post.fullName"></a>
      <span class="post-caption-text" style="white-space: pre-line; color: var(--text-main);" x-html="renderHashtags(post.displayMessage || post.postMessage)"></span>

      <!-- On-Demand Post Translation Toggle for Diaspora Viewers -->
      <div class="mt-1 d-flex align-items-center gap-2" x-show="window.APP_LOCALE && window.APP_LOCALE !== 'en'">
        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 0.8rem; color: var(--brand-primary, #a31621); font-weight: 600;" @click="window.toggleTranslatePost ? window.toggleTranslatePost(post) : null">
          <i class="bi bi-translate"></i>
          <span x-text="post._isTranslated ? (window.__ ? window.__('feed.see_original', 'See original') : 'See original') : (post._isTranslating ? (window.__ ? window.__('feed.translating', 'Translating...') : 'Translating...') : (window.__ ? window.__('feed.translate', 'Translate') : 'Translate'))"></span>
        </button>
      </div>
    </div>
  </template>

  <!-- Comments Section -->
  <div class="comments-section pt-2">
    <!-- Dynamic Comment Form with emoji picker -->
    <div x-show="activeCommentForms[post.post_no]" x-collapse class="mb-3">
      <div class="position-relative">
        <!-- Emoji picker popup for comment input -->
        <div x-show="commentEmojiOpen[post.post_no]" x-cloak
             x-transition.opacity.duration.120ms
             @click.outside="commentEmojiOpen[post.post_no] = false"
             @keydown.escape.window="commentEmojiOpen[post.post_no] = false"
             role="dialog" aria-label="Choose an emoji" data-testid="comment-emoji-picker"
             class="comment-picker comment-picker--emoji">
          <!-- Race-Conscious Fitzpatrick Skin Tone Palette -->
          <div class="d-flex align-items-center justify-content-between px-2 py-1 border-bottom mb-1 bg-light rounded" role="toolbar" aria-label="Comment skin tone diversity selector">
            <span class="text-muted fw-semibold" style="font-size:0.68rem;">Skin:</span>
            <div class="d-flex align-items-center gap-1">
              <template x-for="st in skinTones" :key="st.tone">
                <button type="button" class="btn btn-sm p-0 border-0 rounded-circle"
                        :class="commentSkinTone === st.tone ? 'shadow-sm border border-primary' : 'opacity-75'"
                        style="font-size:0.82rem; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer;"
                        :title="st.label" :aria-label="st.label"
                        @click.stop="setCommentSkinTone(st.tone)" x-text="st.swatch"></button>
              </template>
            </div>
          </div>

          <div class="comment-emoji-grid">
            <template x-for="emoji in commentEmojiPalette" :key="emoji">
              <button type="button" class="comment-emoji-btn" @click="insertEmojiIntoComment(post.post_no, emoji)"
                      :aria-label="'Insert ' + emoji" x-text="emoji"></button>
            </template>
          </div>
        </div>

        <!-- GIF picker popup for comment input -->
        <div x-show="commentGifOpen[post.post_no]" x-cloak
             x-transition.opacity.duration.120ms
             @click.outside="commentGifOpen[post.post_no] = false"
             data-testid="comment-gif-picker"
             class="comment-picker comment-picker--gif">
          <div class="d-flex flex-wrap gap-1 mb-2">
            <template x-for="cat in gifCategories" :key="cat.key">
              <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill fw-semibold gif-cat-pill"
                      :class="commentGifCategory === cat.key ? 'active' : ''"
                      @click="commentGifCategory = cat.key"
                      style="font-size:0.72rem;" x-text="cat.label"></button>
            </template>
          </div>
          <div class="d-flex flex-wrap gap-2 overflow-y-auto" style="max-height:200px;">
            <template x-for="g in filteredCommentGifs()" :key="g.id">
              <div class="gif-item-card" :title="g.title" role="button" tabindex="0"
                   @click="selectCommentGif(post.post_no, g.url)"
                   @keydown.enter="selectCommentGif(post.post_no, g.url)">
                <img :src="g.url" :alt="g.title" loading="lazy">
              </div>
            </template>
          </div>
        </div>

        <!-- @mention autocomplete dropdown -->
        <div x-show="mentionOpen[post.post_no] && (mentionMatches[post.post_no] || []).length > 0"
             x-cloak
             x-transition.opacity.duration.100ms
             @click.outside="mentionOpen[post.post_no] = false"
             role="listbox" aria-label="Mention family member"
             data-testid="mention-autocomplete"
             class="mention-autocomplete shadow-md">
          <div class="mention-autocomplete__header d-flex align-items-center justify-content-between px-2 py-1 text-muted">
            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Mention a family member</span>
            <span style="font-size: 0.7rem;">&uarr;&darr; to navigate &bull; Enter to select</span>
          </div>
          <div class="mention-autocomplete__list">
            <template x-for="(m, idx) in mentionMatches[post.post_no]" :key="m.id">
              <button type="button"
                      class="mention-autocomplete__item d-flex align-items-center gap-2 w-100 text-start px-2 py-1.5 border-0"
                      :class="{ 'mention-autocomplete__item--active': (mentionIndex[post.post_no] || 0) === idx }"
                      @click="selectMention(post.post_no, m)"
                      @mouseenter="mentionIndex[post.post_no] = idx"
                      :aria-selected="(mentionIndex[post.post_no] || 0) === idx">
                <img :src="m.img ? ((m.img.startsWith('/') || m.img.startsWith('http')) ? m.img : `/resources/images/profile/${m.img}`) : '/public/avatar/avatarM.png'"
                     alt="Avatar" class="rounded-circle" width="24" height="24" style="object-fit: cover;">
                <div class="flex-grow-1 min-w-0">
                  <span class="fw-semibold text-dark text-truncate d-block" style="font-size: 0.8125rem;" x-text="m.firstName + ' ' + m.lastName"></span>
                </div>
                <span class="badge rounded-pill bg-light text-muted border" style="font-size: 0.68rem;">Family</span>
              </button>
            </template>
          </div>
        </div>

        <div x-show="replyingTo[post.post_no]" x-cloak x-collapse class="ps-2 mb-1 text-muted" style="font-size: 0.78rem;" data-testid="comment-replying-to">
          <div class="d-flex align-items-center gap-2">
            <span>Replying to <strong x-text="replyingTo[post.post_no]?.fullName"></strong></span>
            <button type="button" class="text-muted" style="background:none; border:none; padding:0 4px; cursor:pointer;" @click="cancelReply(post.post_no)" aria-label="Cancel reply">✕</button>
          </div>
        </div>
        <form @submit.prevent="submitComment(post.post_no, $event)" class="comment-composer">
          @include('csrf')
          <div class="comment-composer__field">
            <!-- Selected GIF preview (inside the field, like Facebook) -->
            <div x-show="commentGifs[post.post_no]" x-cloak class="comment-composer__gif" data-testid="comment-gif-preview">
              <img :src="commentGifs[post.post_no]" alt="Selected GIF"
                   style="display:block; width:auto; height:auto; max-width:200px; max-height:120px; object-fit:contain;"
                   x-on:error="clearCommentGif(post.post_no)">
              <button type="button" @click="clearCommentGif(post.post_no)" aria-label="Remove GIF">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
            <div class="comment-composer__row">
              <input type="text" class="comment-composer__input"
                     placeholder="{{ __('feed.write_comment', 'Write a comment...') }}" aria-label="{{ __('feed.write_comment', 'Write a comment...') }}"
                     :data-comment-input="post.post_no"
                     x-model="commentInputs[post.post_no]" :required="!commentGifs[post.post_no]"
                     @input="handleCommentInput(post.post_no, $event)"
                     @paste="handleCommentPaste(post.post_no, $event)"
                     @keydown="handleCommentKeydown(post.post_no, $event)">
              <button type="button" class="comment-composer__tool"
                      @click="toggleCommentEmoji(post.post_no)"
                      :aria-expanded="commentEmojiOpen[post.post_no] ? 'true' : 'false'"
                      title="Add Emoji - Express with emoji reactions in this comment" aria-label="Add emoji">
                <i class="bi bi-emoji-smile"></i>
              </button>
              <button type="button" class="comment-composer__tool"
                      @click="toggleCommentGif(post.post_no)"
                      :aria-expanded="commentGifOpen[post.post_no] ? 'true' : 'false'"
                      data-testid="comment-gif-btn"
                      title="Add GIF - Search and attach an animated GIF to your comment" aria-label="Add GIF">
                <i class="bi bi-filetype-gif"></i>
              </button>
            </div>
          </div>
          <button type="submit" class="comment-composer__send" aria-label="Post comment" title="Submit Comment - Publish your comment to this family post"
                  :class="((commentInputs[post.post_no] && commentInputs[post.post_no].trim()) || commentGifs[post.post_no]) ? 'is-active' : ''">
            <i class="bi bi-send-fill"></i>
          </button>
        </form>
      </div>
    </div>

    <!-- Comments List -->
    <div class="d-flex flex-column gap-2">
      <template x-for="c in threadedComments(post)" :key="c.comment_no">
        {{-- x-for clones only ONE root element: wrap the item and its thread-collapse toggle together --}}
        <div>
        <div class="d-flex align-items-start gap-2 feed-comment-item" :id="'comment' + c.comment_no" :class="'comment' + c.comment_no" :data-comment-no="c.comment_no" :style="isReply(post, c) ? 'margin-left: 2.25rem;' : ''" :data-reply="isReply(post, c) ? 'true' : 'false'">
          <img :src="c.profileImg" alt="Avatar" class="rounded-circle flex-shrink-0 feed-comment-avatar" width="32" height="32" style="width: 32px; height: 32px; min-width: 32px; min-height: 32px; max-width: 32px; max-height: 32px; aspect-ratio: 1 / 1; object-fit: cover; margin-top: 2px; border-radius: 50%; flex-shrink: 0;">
          <div class="flex-grow-1">
            <!-- Comment Bubble -->
            <template x-if="editingCommentNo !== c.comment_no">
              <div class="px-3 py-2 rounded-3 comment-bubble-box" style="background-color: #f0f2f5; display: inline-block; max-width: 90%;">
                <div class="fw-semibold text-dark" style="font-size: 0.8125rem; line-height: 1.2;" x-text="c.fullName"></div>
                <div x-show="c.comment" class="mt-1 comment-bubble-text" style="font-size: 0.875rem; color: #050505; word-break: break-word; line-height: 1.35; font-variant-emoji: emoji;" x-html="renderCommentText(c.comment)"></div>
                <template x-if="c.gif_url">
                  <img :src="c.gif_url" alt="GIF" loading="lazy" class="mt-1 rounded-3 comment-gif" data-testid="comment-gif"
                       style="display:block; width:auto; height:auto; max-width:100%; max-height:220px; object-fit:contain;">
                </template>
              </div>
            </template>

            <!-- Inline Comment Edit -->
            <template x-if="editingCommentNo === c.comment_no">
              <form @submit.prevent="saveCommentEdit(c.comment_no)" class="d-flex gap-2 align-items-center" style="max-width: 90%;">
    @include('csrf')
                <input type="text" class="form-control form-control-sm rounded-pill" x-model="editCommentText" required
                       style="font-size: 0.875rem; background-color: #f0f2f5; border: none; padding: 6px 14px;">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3" style="font-size: 0.78rem; flex-shrink:0;">Save</button>
                <button type="button" class="btn btn-link btn-sm text-muted" style="font-size: 0.78rem; flex-shrink:0;" @click="cancelEditComment()">Cancel</button>
              </form>
            </template>

            <!-- Top Reactions Preview (Living Heirloom Glass) -->
            <template x-if="getTopCommentReactions(c.reactions).length > 0">
              <div class="d-flex align-items-center gap-1 mt-1 ps-1">
                <div class="reaction-count-pill d-inline-flex gap-1.5 align-items-center" style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:20px; padding:3px 9px; font-size:0.8rem; box-shadow:0 1px 4px rgba(0,0,0,0.08);">
                  <template x-for="r in getTopCommentReactions(c.reactions)" :key="r.label">
                    <span class="d-inline-flex align-items-center gap-1">
                      <span class="d-inline-flex align-items-center justify-content-center" x-html="getHeirloomGlassSvg(r.label, 20)"></span>
                      <span x-text="r.count" style="color:var(--text-muted); font-size:0.75rem;"></span>
                    </span>
                  </template>
                </div>
              </div>
            </template>

            <!-- Comment Meta + Like Button with Reaction Bar -->
            <div class="comment-meta-row">
              <span class="comment-meta-item text-muted" x-text="formatDate(c.comment_time || c.post_time || c.date_created)"></span>

              <!-- Comment Like/React button with Reaction Bar -->
              <div class="position-relative comment-meta-item"
                   @mouseenter="toggleCommentReactionBar(c.comment_no, true)"
                   @mouseleave="toggleCommentReactionBar(c.comment_no, false)"
                   @click.outside="activeCommentReactionBars[c.comment_no] = false">

                <!-- Floating Comment Reaction Bar (Heirloom Glass) -->
                <div x-show="activeCommentReactionBars[c.comment_no]"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @mouseenter="keepCommentReactionBar(c.comment_no, true)"
                     @mouseleave="keepCommentReactionBar(c.comment_no, false)"
                     class="comment-reaction-float-bar heirloom-glass-dock"
                     role="toolbar" aria-label="Choose a comment reaction"
                     style="position:absolute; bottom:calc(100% + 6px); left:0; display:flex; gap:4px; z-index:100; white-space:nowrap; padding:6px 10px;">
                  <template x-for="type in ['love', 'like', 'haha', 'shock', 'sad', 'celebrate']" :key="type">
                    <button type="button"
                            @click.stop="reactToComment(post.post_no, c.comment_no, type)"
                            :title="emojiMap[type]?.label || type"
                            :class="(c.userReaction === type || (type === 'shock' && c.userReaction === 'wow')) ? 'heirloom-reaction-btn active' : 'heirloom-reaction-btn'"
                            :style="'--heirloom-glow:' + (emojiMap[type]?.glow || 'rgba(225,29,72,0.45)')"
                            style="min-width:44px; min-height:44px; padding:4px;">
                      <span class="heirloom-glass-glyph d-inline-flex align-items-center justify-content-center" x-html="getHeirloomGlassSvg(type, 34)"></span>
                      <span class="heirloom-tooltip-pill" x-text="emojiMap[type]?.label || type"></span>
                    </button>
                  </template>
                </div>

                <!-- Like text button -->
                <button type="button"
                        @click="onCommentLikeClick(post.post_no, c.comment_no, c.userReaction || 'like')"
                        @touchstart.passive="startCommentLongPress(c.comment_no)"
                        @touchend="cancelCommentLongPress()"
                        @touchmove="cancelCommentLongPress()"
                        class="comment-meta-btn d-inline-flex align-items-center"
                        :class="c.userReaction ? 'fw-semibold' : 'text-muted'"
                        :style="c.userReaction ? ('color:' + getReactionColor(c.userReaction)) : ''">
                  <template x-if="c.userReaction">
                    <span x-html="getHeirloomGlassSvg(c.userReaction, 22)" class="me-1.5 d-inline-flex align-items-center justify-content-center comment-like-active-glyph"></span>
                  </template>
                  <span x-text="formatReactionLabel(c.userReaction)"></span>
                </button>
              </div>

              <button type="button" class="comment-meta-btn text-muted" data-testid="comment-reply-btn" @click="startReply(post, c)">Reply</button>
              <template x-if="canEditComment(c)">
                <button type="button" class="comment-meta-btn text-muted" @click="startEditComment(c)">Edit</button>
              </template>
              <template x-if="canModerateComment(post, c)">
                <button type="button" class="comment-meta-btn text-muted" :title="canEditComment(c) ? 'Delete your comment' : 'Delete comment from your post'" @click="deleteComment(post.post_no, c.comment_no)">Delete</button>
              </template>

              <!-- Comment View Count (Author-clickable) -->
              <span class="comment-meta-item comment-views-count text-muted"
                    :class="canEditComment(c) ? 'cursor-pointer' : ''"
                    :role="canEditComment(c) ? 'button' : undefined"
                    :tabindex="canEditComment(c) ? '0' : undefined"
                    @click="canEditComment(c) && openCommentViewersModal(c)"
                    @keydown.enter="canEditComment(c) && openCommentViewersModal(c)"
                    :title="canEditComment(c) ? 'Click to see who viewed your comment' : ((c.views_count || 0) + ' views')">
                <i class="bi bi-eye" :class="canEditComment(c) ? 'text-primary' : ''" style="font-size: 0.8rem; line-height: 1; margin-right: 4px;"></i>
                <span x-text="c.views_count || 0"></span>
                <span class="ms-1" x-text="(c.views_count === 1) ? 'view' : 'views'"></span>
              </span>
            </div>
          </div>
        </div>

        <!-- Thread collapse toggle (shown under the last visible item of a thread with > 2 replies) -->
        <template x-if="isLastVisibleInThread(post, c) && getTotalReplyCount(post, getThreadRoot(post, c)) > 2">
          <div class="mt-1" style="margin-left: 2.25rem;">
            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 d-inline-flex align-items-center gap-1.5 fw-semibold comment-thread-toggle"
                    data-testid="thread-collapse-toggle"
                    style="font-size: 0.78rem; color: var(--primary-color, #a31621); min-height: 32px;"
                    @click="toggleThread(getThreadRoot(post, c).comment_no)">
              <template x-if="!threadExpanded[String(getThreadRoot(post, c).comment_no)]">
                <span class="d-inline-flex align-items-center gap-1">
                  <i class="bi bi-arrow-return-right"></i>
                  <span>View </span><span x-text="getHiddenReplyCount(post, getThreadRoot(post, c))"></span><span> more </span>
                  <span x-text="getHiddenReplyCount(post, getThreadRoot(post, c)) === 1 ? 'reply' : 'replies'"></span>
                </span>
              </template>
              <template x-if="threadExpanded[String(getThreadRoot(post, c).comment_no)]">
                <span class="d-inline-flex align-items-center gap-1 text-muted">
                  <i class="bi bi-chevron-up"></i>
                  <span>Collapse replies</span>
                </span>
              </template>
            </button>
          </div>
        </template>
        </div>
      </template>
    </div>
  </div>

</div>
