{{-- Scoped Feed Post Card Component --}}
<div class="card border-0 shadow-sm mb-4 p-3 feed-post-card" :data-post-no="post.post_no" :id="'post' + post.post_no" :class="'post' + post.post_no" :style="(post.is_official || post.id === '{{ \App\services\FamilyPlatformAccountService::getAccountId() }}') ? 'border-radius: 12px; background-color: var(--card-bg); border-left: 4px solid #059669 !important;' : ((post.is_historian || post.id === '{{ \App\services\FamilyHistorianService::HISTORIAN_ID }}') ? 'border-radius: 12px; background-color: var(--card-bg); border-left: 4px solid #4338ca !important;' : (isUserTaggedInPost(post) ? 'border-radius: 12px; background-color: var(--card-bg); border-left: 4px solid #a31621 !important;' : 'border-radius: 8px; background-color: var(--card-bg);'))">


  @include("components.feed.partials.header")

  @include("components.feed.partials.text-stage")

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
          <template x-if="getPostImageVariantUrl(post.comparison.after_img, 'avif', post.image_variants || false)">
            <source :srcset="getPostImageVariantUrl(post.comparison.after_img, 'avif', post.image_variants || false)" type="image/avif">
          </template>
          <template x-if="getPostImageVariantUrl(post.comparison.after_img, 'webp', post.image_variants || false)">
            <source :srcset="getPostImageVariantUrl(post.comparison.after_img, 'webp', post.image_variants || false)" type="image/webp">
          </template>
          <img class="w-100 h-100 object-fit-cover"
               :style="mode === 'morph' ? ('transform: scale(' + (1 + (pos / 1200)).toFixed(3) + '); transition: transform 0.12s linear; pointer-events: none; user-select: none; -webkit-user-drag: none;') : 'pointer-events: none; user-select: none; -webkit-user-drag: none;'"
               :src="getPostImageUrl(post.comparison.after_img)" alt="Now" loading="lazy" decoding="async"
               x-on:error="$event.target.style.display='none'">
        </picture>
        <picture class="then-now__img then-now__img--then"
                 :style="mode === 'morph'
                   ? ('opacity: ' + ((100 - pos) / 100).toFixed(3) + '; clip-path: none; -webkit-clip-path: none; pointer-events: none; user-select: none; -webkit-user-drag: none;')
                   : ('clip-path: inset(0 ' + (100 - pos) + '% 0 0); -webkit-clip-path: inset(0 ' + (100 - pos) + '% 0 0); pointer-events: none; user-select: none; -webkit-user-drag: none;')">
          <template x-if="getPostImageVariantUrl(post.comparison.before_img, 'avif', post.image_variants || false)">
            <source :srcset="getPostImageVariantUrl(post.comparison.before_img, 'avif', post.image_variants || false)" type="image/avif">
          </template>
          <template x-if="getPostImageVariantUrl(post.comparison.before_img, 'webp', post.image_variants || false)">
            <source :srcset="getPostImageVariantUrl(post.comparison.before_img, 'webp', post.image_variants || false)" type="image/webp">
          </template>
          <img class="w-100 h-100 object-fit-cover" :src="getPostImageUrl(post.comparison.before_img)" alt="Then" loading="lazy" decoding="async" style="pointer-events: none; user-select: none; -webkit-user-drag: none;" x-on:error="$event.target.style.display='none'">
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
            <template x-if="getPostImageVariantUrl(post.comparison.before_img, 'avif', post.image_variants || false)">
              <source :srcset="getPostImageVariantUrl(post.comparison.before_img, 'avif', post.image_variants || false)" type="image/avif">
            </template>
            <template x-if="getPostImageVariantUrl(post.comparison.before_img, 'webp', post.image_variants || false)">
              <source :srcset="getPostImageVariantUrl(post.comparison.before_img, 'webp', post.image_variants || false)" type="image/webp">
            </template>
            <img :src="getPostImageUrl(post.comparison.before_img)" alt="Then" loading="lazy" x-on:error="$event.target.style.display='none'">
          </picture>
          <figcaption x-text="post.comparison.before_label"></figcaption>
        </figure>
        <figure class="cursor-pointer" style="cursor: pointer;" @click="window.__expandThenNow ? window.__expandThenNow(post.comparison, { fullName: post.fullName, post_desc: post.post_desc || post.postMessage, date_created: post.date_created, post_no: post.post_no, voice_capsule_url: post.voice_capsule_url }) : null" title="Click to expand">
          <picture>
            <template x-if="getPostImageVariantUrl(post.comparison.after_img, 'avif', post.image_variants || false)">
              <source :srcset="getPostImageVariantUrl(post.comparison.after_img, 'avif', post.image_variants || false)" type="image/avif">
            </template>
            <template x-if="getPostImageVariantUrl(post.comparison.after_img, 'webp', post.image_variants || false)">
              <source :srcset="getPostImageVariantUrl(post.comparison.after_img, 'webp', post.image_variants || false)" type="image/webp">
            </template>
            <img :src="getPostImageUrl(post.comparison.after_img)" alt="Now" loading="lazy" x-on:error="$event.target.style.display='none'">
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


  @include("components.feed.partials.media-single")

  @include("components.feed.partials.media-carousel")

  <!-- Multiple Videos Horizontal Track (Up to 3 Videos side-by-side) -->
  <template x-if="post.videos && post.videos.length > 1">
    <div class="multi-video-horizontal-container mb-3 px-1 position-relative"
         x-data="{
           activeVidIdx: 0,
           _isInView: false,
           _isProgrammaticScroll: false,
           _scrollEndTimer: null,
           getCards() {
             const track = this.$refs.videoScrollTrack;
             return track ? Array.from(track.querySelectorAll('.multi-video-card')) : [];
           },
           playCardVideo(idx, force = false) {
             const cards = this.getCards();
             const card = cards[idx];
             if (!card) return;
             const targetVid = card.querySelector('video');
             document.querySelectorAll('video').forEach(other => {
               if (other !== targetVid && !other.paused) {
                 other._autoPlaying = false;
                 other.pause();
               }
             });
             if (!targetVid) return;
             if (force) {
               targetVid._userManuallyPaused = false;
             } else if (targetVid._userManuallyPaused) {
               return;
             }
             targetVid.defaultMuted = true;
             targetVid.muted = true;
             targetVid.setAttribute('muted', '');
             targetVid.playsInline = true;
             targetVid.setAttribute('playsinline', '');
             targetVid.setAttribute('webkit-playsinline', '');
             if (targetVid.preload !== 'auto') {
               targetVid.preload = 'auto';
             }
             const attemptPlay = () => {
               if ((!this._isInView && !force) || this.activeVidIdx !== idx || targetVid._userManuallyPaused) return;
               if (!targetVid.paused) return;
               targetVid.muted = true;
               targetVid.defaultMuted = true;
               targetVid.setAttribute('muted', '');
               targetVid._autoPlaying = true;
               const p = targetVid.play();
               if (p !== undefined) {
                 p.then(() => {
                   this.isPlaying = true;
                   this.setBuffering(false, targetVid);
                 }).catch(() => {
                   targetVid._autoPlaying = false;
                   targetVid.muted = true;
                   targetVid.defaultMuted = true;
                   targetVid.setAttribute('muted', '');
                   targetVid.play().then(() => {
                     this.isPlaying = true;
                     this.setBuffering(false, targetVid);
                   }).catch(() => {
                     if (targetVid.paused) {
                       targetVid.dispatchEvent(new CustomEvent('buffering-clear'));
                     }
                   });
                 });
               }
             };
             attemptPlay();
             if (targetVid.readyState < 2) {
               if (!targetVid._awaitingReadyPlay) {
                 targetVid._awaitingReadyPlay = true;
                 const onReady = () => {
                   targetVid._awaitingReadyPlay = false;
                   targetVid.removeEventListener('loadeddata', onReady);
                   targetVid.removeEventListener('canplay', onReady);
                   targetVid.removeEventListener('error', onFail);
                   if (this.activeVidIdx === idx && targetVid.paused) {
                     attemptPlay();
                   }
                 };
                 const onFail = () => {
                   targetVid._awaitingReadyPlay = false;
                   targetVid.removeEventListener('loadeddata', onReady);
                   targetVid.removeEventListener('canplay', onReady);
                   targetVid.removeEventListener('error', onFail);
                   targetVid.dispatchEvent(new CustomEvent('buffering-clear'));
                 };
                 targetVid.addEventListener('loadeddata', onReady, { once: true });
                 targetVid.addEventListener('canplay', onReady, { once: true });
                 targetVid.addEventListener('error', onFail, { once: true });
               }
               if (targetVid.networkState === 0 && !targetVid.currentSrc) {
                 try { targetVid.load(); } catch (_) {}
               }
             }
           },
           scrollToCard(idx, autoPlay = true) {
             const track = this.$refs.videoScrollTrack;
             const cards = this.getCards();
             if (!track || !cards.length) return;
             const safeIdx = Math.max(0, Math.min(idx, cards.length - 1));
             const card = cards[safeIdx];
             if (card) {
               this._isProgrammaticScroll = true;
               this.activeVidIdx = safeIdx;
               const targetLeft = Math.max(0, card.offsetLeft - 4);
               track.scrollTo({ left: targetLeft, behavior: 'smooth' });
               if (autoPlay) {
                 this.playCardVideo(safeIdx, true);
                 setTimeout(() => {
                   this._isProgrammaticScroll = false;
                   this.playCardVideo(safeIdx, false);
                 }, 420);
               } else {
                 setTimeout(() => {
                   this._isProgrammaticScroll = false;
                 }, 420);
               }
             }
           },
           onVideoEnded(curIdx, vEl) {
             const cards = this.getCards();
             if (cards.length <= 1) return;
             if (curIdx < cards.length - 1) {
               this.scrollToCard(curIdx + 1, true);
             } else {
               this.scrollToCard(0, true);
             }
           },
           onScroll(e) {
             const track = e.target;
             const cards = this.getCards();
             if (!cards.length) return;
             if (!this._isProgrammaticScroll) {
               const maxScroll = Math.max(0, track.scrollWidth - track.clientWidth);
               let closestIdx = 0;
               if (maxScroll > 0 && track.scrollLeft >= maxScroll - 16) {
                 closestIdx = cards.length - 1;
               } else {
                 let minDiff = Infinity;
                 cards.forEach((child, i) => {
                   const diff = Math.abs(track.scrollLeft - child.offsetLeft);
                   if (diff < minDiff) {
                     minDiff = diff;
                     closestIdx = i;
                   }
                 });
               }
               const newIdx = Math.max(0, Math.min(closestIdx, cards.length - 1));
               if (newIdx !== this.activeVidIdx) {
                 this.activeVidIdx = newIdx;
                 this.playCardVideo(newIdx);
               }
             }
             if (this._scrollEndTimer) clearTimeout(this._scrollEndTimer);
             this._scrollEndTimer = setTimeout(() => {
               this._isProgrammaticScroll = false;
               this.playCardVideo(this.activeVidIdx);
             }, 180);
           },
           initMultiVideoObserver() {
             if (window.__FPFeedVideoCoordinator) {
               window.__FPFeedVideoCoordinator.register(this.$el, {
                 getVideo: () => {
                   const cards = this.getCards();
                   const card = cards[this.activeVidIdx];
                   return card ? card.querySelector('video') : null;
                 },
                 play: () => {
                   this._isInView = true;
                   this.playCardVideo(this.activeVidIdx);
                 },
                 pause: () => {
                   this._isInView = false;
                   this.getCards().forEach(c => {
                     const v = c.querySelector('video');
                     if (v) {
                       v._autoPlaying = false;
                       if (!v.paused) v.pause();
                     }
                   });
                 }
               });
               return;
             }
             if (typeof IntersectionObserver === 'undefined') return;
             const obs = new IntersectionObserver((entries) => {
               entries.forEach(entry => {
                 if (entry.isIntersecting && entry.intersectionRatio >= 0.25) {
                   this._isInView = true;
                   this.playCardVideo(this.activeVidIdx);
                 } else if (!entry.isIntersecting || entry.intersectionRatio < 0.15) {
                   this._isInView = false;
                   this.getCards().forEach(c => {
                     const v = c.querySelector('video');
                     if (v) {
                       v._autoPlaying = false;
                       if (!v.paused) v.pause();
                       v._userManuallyPaused = false;
                     }
                   });
                 }
               });
             }, { threshold: [0.0, 0.15, 0.25, 0.5] });
             obs.observe(this.$el);
           }
         }"
         x-init="$nextTick(() => initMultiVideoObserver())">
      <!-- Subtle Pagination Dots (No Video Numbering) -->
      <div class="d-flex align-items-center justify-content-end mb-1.5 px-1" x-show="post.videos.length > 1">
        <div class="d-flex align-items-center gap-1.5">
          <template x-for="(v, vIdx) in post.videos" :key="'dot-' + vIdx">
            <span class="rounded-pill transition-all"
                  :style="activeVidIdx === vIdx 
                    ? 'width: 18px; height: 6px; background: #a31621;' 
                    : 'width: 6px; height: 6px; background: rgba(15, 23, 42, 0.25); cursor: pointer;'"
                  @click.stop.prevent="scrollToCard(vIdx, true)"></span>
          </template>
        </div>
      </div>

      <!-- Left / Previous Navigation Arrow -->
      <button type="button"
              class="btn position-absolute start-0 top-50 translate-middle-y shadow d-inline-flex align-items-center justify-content-center p-0 rounded-circle"
              style="z-index: 20; width: 44px; height: 44px; min-width: 44px; min-height: 44px; background: rgba(15, 23, 42, 0.82); backdrop-filter: blur(8px); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.25); margin-left: 6px; pointer-events: auto;"
              x-show="activeVidIdx > 0"
              @click.stop.prevent="scrollToCard(activeVidIdx - 1, true)"
              aria-label="Previous video">
        <i class="bi bi-chevron-left" style="font-size: 1.1rem; pointer-events: none;"></i>
      </button>

      <!-- Right / Next Navigation Arrow -->
      <button type="button"
              class="btn position-absolute end-0 top-50 translate-middle-y shadow d-inline-flex align-items-center justify-content-center p-0 rounded-circle"
              style="z-index: 20; width: 44px; height: 44px; min-width: 44px; min-height: 44px; background: rgba(15, 23, 42, 0.82); backdrop-filter: blur(8px); color: #ffffff; border: 1px solid rgba(255, 255, 255, 0.25); margin-right: 6px; pointer-events: auto;"
              x-show="activeVidIdx < post.videos.length - 1"
              @click.stop.prevent="scrollToCard(activeVidIdx + 1, true)"
              aria-label="Next video">
        <i class="bi bi-chevron-right" style="font-size: 1.1rem; pointer-events: none;"></i>
      </button>

      <!-- Horizontal Scrollable Track -->
      <div class="multi-video-scroll-track d-flex gap-2.5 overflow-x-auto pb-2"
           x-ref="videoScrollTrack"
           @scroll.passive="onScroll($event)"
           style="scroll-snap-type: x mandatory; scrollbar-width: thin; -webkit-overflow-scrolling: touch; scroll-padding: 0 4px; height: clamp(340px, 50vh, 460px);">
        <template x-for="(vid, vIdx) in post.videos" :key="vIdx">
          <div class="multi-video-card flex-shrink-0 position-relative rounded-3 overflow-hidden shadow-sm border border-light-subtle d-flex flex-column h-100"
               :style="'scroll-snap-align: start; flex: 0 0 auto; width: ' + (isCardLandscape ? 'clamp(300px, 82%, 520px)' : (post.videos.length === 2 ? 'clamp(240px, 48.5%, 360px)' : 'clamp(210px, 44%, 300px)')) + '; height: 100%; background: #0b0f19; transition: width 0.25s cubic-bezier(0.16, 1, 0.3, 1);'"
               x-data="{
                 isMuted: true,
                 isPlaying: false,
                 isBuffering: false,
                 _bufferingTimer: null,
                 hasCardMetadata: false,
                 progressPercent: 0,
                 isCardLandscape: (vid.type !== 'direct'),
                 cardAspect: (vid.type !== 'direct' ? '16 / 9' : '4 / 5'),
                 setBuffering(val, vEl) {
                   if (this._bufferingTimer) {
                     clearTimeout(this._bufferingTimer);
                     this._bufferingTimer = null;
                   }
                   if (val && vEl && (vEl.paused || vEl.ended || vEl.readyState >= 3)) {
                     this.isBuffering = false;
                     return;
                   }
                   this.isBuffering = !!val;
                   if (this.isBuffering) {
                     this._bufferingTimer = setTimeout(() => {
                       this.isBuffering = false;
                       this._bufferingTimer = null;
                     }, 6000);
                   }
                 },
                 detectCardDimensions(el) {
                   if (!el) return;
                   requestAnimationFrame(() => {
                     if (!el.videoWidth || !el.videoHeight) return;
                     const r = el.videoWidth / el.videoHeight;
                     const exactRatio = (el.videoWidth / el.videoHeight).toFixed(4) + ' / 1';
                     if (r > 1.15) {
                       this.isCardLandscape = true;
                       this.cardAspect = exactRatio;
                     } else {
                       this.isCardLandscape = false;
                       this.cardAspect = exactRatio;
                     }
                   });
                 },
                 togglePlay(vEl) {
                   if (!vEl) return;
                   if (vEl.paused) {
                     vEl._userManuallyPaused = false;
                     const track = $el.closest('.multi-video-scroll-track');
                     if (track) {
                       track.querySelectorAll('video').forEach(other => {
                         if (other !== vEl && !other.paused) other.pause();
                       });
                     }
                     vEl.play().then(() => {
                       this.isPlaying = true;
                       this.setBuffering(false, vEl);
                     }).catch(() => {
                       this.setBuffering(false, vEl);
                     });
                   } else {
                     vEl._userManuallyPaused = true;
                     vEl.pause();
                     this.isPlaying = false;
                     this.setBuffering(false, vEl);
                   }
                 },
                 toggleMute(vEl) {
                   if (!vEl) return;
                   vEl.muted = !vEl.muted;
                   this.isMuted = vEl.muted;
                 },
                 onTimeUpdate(vEl) {
                   if (!vEl || !vEl.duration) return;
                   const rawStart = (vid && typeof vid.trimStart === 'number' && vid.trimStart > 0) ? vid.trimStart : 0;
                   const rawEnd = (vid && typeof vid.trimEnd === 'number' && vid.trimEnd > rawStart) ? vid.trimEnd : (rawStart + 60);
                   const effectiveEnd = Math.min(vEl.duration, rawEnd, rawStart + 60);
                   const clipDur = Math.max(0.1, effectiveEnd - rawStart);
                   const relCur = Math.max(0, Math.min(clipDur, vEl.currentTime - rawStart));
                   this.progressPercent = Math.min(100, Math.max(0, (relCur / clipDur) * 100));
                   if (vEl.currentTime >= effectiveEnd) {
                     onVideoEnded(vIdx, vEl);
                   }
                 }
               }"
               x-init="$nextTick(() => {
                 const v = $el.querySelector('video');
                 if (v) {
                   if (vid && vid.hlsUrl && window.__FPFeedVideoCoordinator && typeof window.__FPFeedVideoCoordinator.attachHls === 'function') {
                     window.__FPFeedVideoCoordinator.attachHls(v, vid.hlsUrl, vid.embedUrl);
                   }
                   if (v.videoWidth) detectCardDimensions(v);
                 }
               })">
            <!-- Full Video Mode Button Top-Right -->
            <button type="button"
                    class="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                    @click.stop="openVideoModal(post.videos, vIdx, isMuted, post)"
                    title="Play in full video mode"
                    aria-label="Play in full video mode"
                    style="top: 8px; right: 60px; z-index: 6; width: 44px; height: 44px; min-width: 44px; min-height: 44px; border-radius: 9999px; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff; cursor: pointer; white-space: nowrap; flex-shrink: 0;">
              <i class="bi bi-arrows-fullscreen" style="font-size: 0.85rem; color: #ffffff;"></i>
            </button>

            <!-- Mute/Unmute Pill Top-Right -->
            <button type="button"
                    class="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                    @click.stop="toggleMute($el.closest('.multi-video-card').querySelector('video'))"
                    :title="isMuted ? 'Unmute video' : 'Mute video'"
                    :aria-label="isMuted ? 'Unmute video' : 'Mute video'"
                    style="top: 8px; right: 8px; z-index: 6; width: 44px; height: 44px; min-width: 44px; min-height: 44px; border-radius: 9999px; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff; cursor: pointer; white-space: nowrap; flex-shrink: 0;">
              <i class="bi" :class="isMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'" style="font-size: 0.9rem; color: #ffffff;"></i>
            </button>

            <!-- Video Player (Direct Stream or Embedded) -->
            <template x-if="vid.type === 'direct'">
              <div class="position-relative w-100 h-100 flex-grow-1 overflow-hidden" style="min-height: 320px; background: #0b0f19;">
                <!-- High-Res Poster Thumbnail Overlay (Stays visible until first video frame decodes) -->
                <img x-show="!hasCardMetadata && vid.thumbnailUrl"
                     :src="vid.thumbnailUrl"
                     class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none"
                     style="z-index: 3; object-fit: contain !important; width: 100%; height: 100%; position: absolute; inset: 0;"
                     loading="lazy"
                     alt="Video thumbnail preview"
                     x-on:error="$el.style.display = 'none'">

                <video referrerpolicy="no-referrer" class="w-100 h-100 position-absolute top-0 start-0 atg-4k-video-render"
                       :src="vid.embedUrl"
                       :poster="(!hasCardMetadata && vid.thumbnailUrl) ? vid.thumbnailUrl : null"
                       width="640"
                       height="360"
                       playsinline
                       webkit-playsinline
                       muted
                       preload="metadata"
                       @loadedmetadata="detectCardDimensions($el)"
                       @loadeddata="hasCardMetadata = true; setBuffering(false, $el); detectCardDimensions($el)"
                       @waiting="setBuffering(true, $el)"
                       @canplay="setBuffering(false, $el)"
                       @canplaythrough="setBuffering(false, $el)"
                       @playing="hasCardMetadata = true; setBuffering(false, $el)"
                       @seeked="setBuffering(false, $el)"
                       @stalled="setBuffering(false, $el)"
                       @abort="setBuffering(false, $el)"
                       @emptied="setBuffering(false, $el)"
                       @buffering-clear="setBuffering(false, $el)"
                       x-on:error="setBuffering(false, $el); if (!$el.dataset.retried && vid.originalUrl && $el.src !== vid.originalUrl && vid.originalUrl.startsWith('http')) { $el.dataset.retried = '1'; const shouldAutoResume = !!(isPlaying || $el._autoPlaying); $el.src = vid.originalUrl; $el.load(); if (shouldAutoResume) { $el.addEventListener('canplay', () => { if (!$el._userManuallyPaused) { $el.muted = true; $el.play().catch(() => {}); } }, { once: true }); } }"
                       @click.stop="togglePlay($el)"
                       @dblclick.stop="openVideoModal(post.videos, vIdx, isMuted, post)"
                       @play="isPlaying = true"
                       @pause="isPlaying = false; setBuffering(false, $el)"
                       @ended="setBuffering(false, $el); onVideoEnded(vIdx, $el)"
                       @timeupdate="if (isBuffering && !$el.paused && $el.readyState >= 2) setBuffering(false, $el); onTimeUpdate($el)"
                       style="object-fit: contain !important; width: 100%; height: 100%; position: absolute; inset: 0; z-index: 2; cursor: pointer; border-radius: 12px; background: #0b0f19;">
                  <track kind="captions" srclang="en" label="English Captions">
                </video>

                <!-- Buffering Spinner Overlay -->
                <div x-show="isBuffering" x-cloak class="position-absolute top-50 start-50 translate-middle pointer-events-none" style="z-index: 5;">
                  <div class="spinner-border text-light" role="status" style="width: 2rem; height: 2rem; border-width: 2.5px; opacity: 0.88;">
                    <span class="visually-hidden">Loading video…</span>
                  </div>
                </div>

                <!-- Bottom Scrubber Bar -->
                <div class="position-absolute bottom-0 start-0 w-100" style="height: 4px; background: rgba(255, 255, 255, 0.2); z-index: 5;">
                  <div class="h-100" :style="'width: ' + progressPercent + '%; background: #ffffff; transition: width 0.1s linear;'"></div>
                </div>
              </div>
            </template>

            <!-- Embedded Third-Party (YouTube / Vimeo / Cloudflare) -->
            <template x-if="vid.type !== 'direct'">
              <div class="w-100 h-100 position-relative flex-grow-1" style="background: #0b0f19;">
                <iframe :src="vid.embedUrl"
                        title="Video Player"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        loading="lazy"
                        style="width: 100%; height: 100%; border: none;"></iframe>
                <!-- Embedded Full Video Mode Button -->
                <button type="button"
                        class="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                        @click.stop="openVideoModal(post.videos, vIdx, false, post)"
                        title="Play in full video mode"
                        aria-label="Play in full video mode"
                        style="top: 8px; right: 8px; z-index: 6; width: 44px; height: 44px; min-width: 44px; min-height: 44px; border-radius: 9999px; background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.25); color: #ffffff; cursor: pointer; white-space: nowrap; flex-shrink: 0;">
                  <i class="bi bi-arrows-fullscreen" style="font-size: 0.85rem; color: #ffffff;"></i>
                </button>
              </div>
            </template>
          </div>
        </template>
      </div>
    </div>
  </template>

  <!-- Embedded Video (YouTube, Vimeo, Cloud Stream, TikTok) -->
  <template x-if="(!post.videos || post.videos.length <= 1) && post.video && (post.video.type === 'youtube' || post.video.type === 'vimeo' || post.video.type === 'cloudflare' || post.video.type === 'tiktok')">
    <div class="video-embed-container feed-video-breakout mb-3"
         x-data="{
           isShort: Boolean(post.video.isShort || post.video.type === 'tiktok' || (post.video.originalUrl && post.video.originalUrl.includes('/shorts/')) || (post.video.aspect_ratio && (parseFloat(post.video.aspect_ratio.split('/')[0]) / (parseFloat(post.video.aspect_ratio.split('/')[1]) || 1)) < 0.88) || /(?:^|[\s&quot;'(])#(?:shorts?|reels?|tiktok|afv)\b/i.test(String(post.postMessage || post.displayMessage || '')))
         }">
      <div class="video-rendition-frame overflow-hidden shadow-sm border border-light-subtle position-relative d-flex align-items-center justify-content-center"
           style="border-radius: 12px; width: 100%; max-width: 100%; background: #07090e;">
        <div class="w-100"
             :style="isShort ? 'max-width: min(460px, 100%); aspect-ratio: 9 / 16; max-height: min(820px, 84vh); margin: 0 auto;' : 'max-width: 100%; aspect-ratio: 16 / 9; max-height: min(720px, 80vh); margin: 0 auto;'">
          <iframe :src="post.video.embedUrl" 
                  title="Video Player" 
                  frameborder="0" 
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                  allowfullscreen 
                  loading="lazy"
                  style="width: 100%; height: 100%; border: none; display: block;"></iframe>
        </div>
        <button type="button"
                class="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                @click.stop="openVideoModal([post.video], 0, false, post)"
                title="View Fullscreen in full video mode"
                aria-label="View Fullscreen in full video mode"
                style="top: 10px; right: 10px; z-index: 6; width: 44px; height: 44px; min-width: 44px; min-height: 44px; border-radius: 9999px; background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.25); color: #ffffff; cursor: pointer; white-space: nowrap; flex-shrink: 0;">
          <i class="bi bi-arrows-fullscreen" style="font-size: 0.88rem; color: #ffffff;"></i>
        </button>
      </div>
    </div>
  </template>

  <!-- Direct Video Stream (World-Class Instagram-Grade Adaptive Video Rendition) -->
  <template x-if="(!post.videos || post.videos.length <= 1) && post.video && post.video.type === 'direct'">
    <div class="video-embed-container feed-video-breakout mb-3 position-relative"
         :class="{ 'is-cinema-expanded': cinemaExpanded && !isPortrait }"
         x-data="{
           isVideoMuted: true,
           isPlaying: false,
           isBuffering: false,
           _isInView: false,
           videoOrientation: (post.video && post.video.aspect_ratio && (parseFloat(post.video.aspect_ratio.split('/')[0]) / (parseFloat(post.video.aspect_ratio.split('/')[1]) || 1)) > 1.15) ? 'landscape' : ((post.video && post.video.aspect_ratio && (parseFloat(post.video.aspect_ratio.split('/')[0]) / (parseFloat(post.video.aspect_ratio.split('/')[1]) || 1)) >= 0.88) ? 'square' : 'portrait'),
           videoRatio: (post.video && post.video.aspect_ratio) ? (((parseFloat(post.video.aspect_ratio.split('/')[0]) / (parseFloat(post.video.aspect_ratio.split('/')[1]) || 1)) < 0.88) ? '4 / 5' : (((parseFloat(post.video.aspect_ratio.split('/')[0]) / (parseFloat(post.video.aspect_ratio.split('/')[1]) || 1)) > 1.6) ? '16 / 10' : post.video.aspect_ratio)) : '4 / 5',
           naturalVideoRatio: (post.video && post.video.aspect_ratio) ? post.video.aspect_ratio : '9 / 16',
           videoMaxHeight: (post.video && post.video.aspect_ratio && (parseFloat(post.video.aspect_ratio.split('/')[0]) / (parseFloat(post.video.aspect_ratio.split('/')[1]) || 1)) >= 0.88) ? 'min(680px, 72vh)' : '880px',
           videoFocalPos: (post.video && typeof post.video.focal_x === 'number' && typeof post.video.focal_y === 'number') ? (post.video.focal_x + '% ' + post.video.focal_y + '%') : '50% 28%',
           isPortrait: !(post.video && post.video.aspect_ratio && (parseFloat(post.video.aspect_ratio.split('/')[0]) / (parseFloat(post.video.aspect_ratio.split('/')[1]) || 1)) >= 0.88),
           portraitExpanded: false,
           cinemaExpanded: false,
           viewMode: 'cinema',
           showHookCard: true,
           hasMetadata: false,
           progressPercent: 0,
           currentTimeFormatted: '0:00',
           durationFormatted: '0:00',
           showPulse: false,
           pulseIcon: 'bi-play-fill',
           showControlsOverlay: false,
           controlsVisible: true,
           _controlsHideTimer: null,
           scheduleControlsHide() {
             if (this._controlsHideTimer) clearTimeout(this._controlsHideTimer);
             if (!this.isPlaying || this.showControlsOverlay) return;
             this._controlsHideTimer = setTimeout(() => {
               if (this.isPlaying && !this.showControlsOverlay) {
                 this.controlsVisible = false;
               }
             }, 2500);
           },
           revealControls(autoHide = true) {
             this.controlsVisible = true;
             if (this._controlsHideTimer) clearTimeout(this._controlsHideTimer);
             if (autoHide && this.isPlaying) {
               this.scheduleControlsHide();
             }
           },
           formatTime(sec) {
             if (!sec || isNaN(sec)) return '0:00';
             const m = Math.floor(sec / 60);
             const s = Math.floor(sec % 60);
             return m + ':' + (s < 10 ? '0' : '') + s;
           },
           getDerivedHook(raw) {
             if (!raw || typeof raw !== 'string') return '';
             let clean = raw.replace(/https?:\/\/[^\s]+/g, '').trim();
             if (!clean) return '';
             const lines = clean.split(/\r?\n|[.!?]\s+/).map(s => s.trim()).filter(Boolean);
             const first = lines[0] || clean;
             if (first.length <= 80) return first;
             const truncated = first.substring(0, 75);
             const lastSpace = truncated.lastIndexOf(' ');
             return (lastSpace > 25 ? truncated.substring(0, lastSpace) : truncated) + '...';
           },
           toggleViewMode() {
             this.viewMode = (this.viewMode === 'reel') ? 'cinema' : 'reel';
           },
           togglePortraitExpand() {
             this.portraitExpanded = !this.portraitExpanded;
             if (this.isPortrait) {
               this.videoRatio = this.portraitExpanded ? (this.naturalVideoRatio || '9 / 16') : '4 / 5';
               this.videoMaxHeight = this.portraitExpanded ? 'min(760px, 76vh)' : '880px';
             }
           },
           toggleCinemaExpand() {
             this.cinemaExpanded = !this.cinemaExpanded;
             if (this.videoOrientation === 'landscape' && this.naturalVideoRatio) {
               const parts = this.naturalVideoRatio.split('/');
               const r = parseFloat(parts[0]) / (parseFloat(parts[1]) || 1);
               this.videoRatio = (!this.cinemaExpanded && r > 1.6) ? '16 / 10' : this.naturalVideoRatio;
               this.videoMaxHeight = this.cinemaExpanded ? '78vh' : 'min(680px, 72vh)';
             }
           },
           detectVideoDimensions(el) {
             if (!el || !el.videoWidth || !el.videoHeight) return;
             const w = el.videoWidth;
             const h = el.videoHeight;
             const ratio = w / h;
             const exactRatio = ratio.toFixed(4) + ' / 1';
             this.naturalVideoRatio = exactRatio;

             if (ratio < 0.88) {
               this.isPortrait = true;
               this.videoOrientation = 'portrait';
               this.videoRatio = this.portraitExpanded ? exactRatio : '4 / 5';
               this.videoMaxHeight = this.portraitExpanded ? 'min(760px, 76vh)' : '880px';
             } else if (ratio > 1.15) {
               this.isPortrait = false;
               this.videoOrientation = 'landscape';
               this.videoRatio = (!this.cinemaExpanded && ratio > 1.6) ? '16 / 10' : exactRatio;
               this.videoMaxHeight = this.cinemaExpanded ? '78vh' : 'min(680px, 72vh)';
             } else {
               this.isPortrait = false;
               this.videoOrientation = 'square';
               this.videoRatio = exactRatio;
               this.videoMaxHeight = 'min(720px, 78vh)';
             }

             if (!(post.video && typeof post.video.focal_x === 'number' && typeof post.video.focal_y === 'number')
                 && window.AtgMediaSupremacy && typeof window.AtgMediaSupremacy.analyzeVideoFrame === 'function') {
               const analysis = window.AtgMediaSupremacy.analyzeVideoFrame(el);
               if (analysis && analysis.focalPosition) {
                 this.videoFocalPos = analysis.focalPosition;
               }
             }
           },
           togglePlayback(videoEl) {
             if (!videoEl) return;
             if (videoEl.paused) {
               videoEl._userManuallyPaused = false;
               videoEl.playsInline = true;
               if (window.__FPFeedVideoCoordinator) {
                 window.__FPFeedVideoCoordinator.notifyPlaying(this.$el);
               }
               videoEl.play().then(() => {
                 this.isPlaying = true;
               }).catch(() => {});
             } else {
               videoEl._userManuallyPaused = true;
               videoEl._autoPlaying = false;
               videoEl.pause();
               this.isPlaying = false;
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
           getTrimBounds(videoEl) {
             const maxCap = (this.videoOrientation === 'landscape') ? 300 : 180;
             const rawStart = (post.video && typeof post.video.trimStart === 'number' && post.video.trimStart > 0) ? post.video.trimStart : 0;
             const rawEnd = (post.video && typeof post.video.trimEnd === 'number' && post.video.trimEnd > rawStart) ? post.video.trimEnd : (rawStart + maxCap);
             const rawDur = (videoEl && videoEl.duration && !isNaN(videoEl.duration) && videoEl.duration > 0) ? videoEl.duration : rawEnd;
             const end = Math.min(rawDur, rawEnd, rawStart + maxCap);
             const clipDur = Math.max(0.1, end - rawStart);
             return { start: rawStart, end, clipDur };
           },
           handleTimeUpdate(videoEl) {
             if (!videoEl) return;
             const { start, end, clipDur } = this.getTrimBounds(videoEl);
             const cur = videoEl.currentTime;
             if (cur >= end) {
               videoEl.currentTime = start;
               return;
             }
             const relCur = Math.max(0, Math.min(clipDur, cur - start));
             this.progressPercent = Math.min(100, Math.max(0, (relCur / clipDur) * 100));
             this.currentTimeFormatted = this.formatTime(relCur);
             this.durationFormatted = this.formatTime(clipDur);
           },
           seekVideo(event, videoEl) {
             if (!videoEl || !videoEl.duration) return;
             const { start, clipDur } = this.getTrimBounds(videoEl);
             const rect = event.currentTarget.getBoundingClientRect();
             const clickX = event.clientX - rect.left;
             const ratio = Math.max(0, Math.min(1, clickX / rect.width));
             videoEl.currentTime = start + (ratio * clipDur);
           },
           initSingleVideoObserver() {
             const v = this.$el.querySelector('video');
             if (v) {
               if (post.video && post.video.hlsUrl && window.__FPFeedVideoCoordinator && typeof window.__FPFeedVideoCoordinator.attachHls === 'function') {
                 window.__FPFeedVideoCoordinator.attachHls(v, post.video.hlsUrl, post.video.embedUrl);
               }
               if (v.videoWidth) this.detectVideoDimensions(v);
             }
             const playVideoInView = () => {
               const vidEl = this.$el.querySelector('video');
               if (!vidEl || vidEl._userManuallyPaused) return;
               vidEl.defaultMuted = true;
               vidEl.muted = true;
               vidEl.setAttribute('muted', '');
               vidEl.playsInline = true;
               vidEl.setAttribute('playsinline', '');
               vidEl.setAttribute('webkit-playsinline', '');
               if (vidEl.preload !== 'auto') vidEl.preload = 'auto';
               const tryPlay = () => {
                 if (!this._isInView || vidEl._userManuallyPaused) return;
                 if (!vidEl.paused) {
                   this.isPlaying = true;
                   this.isBuffering = false;
                   return;
                 }
                 vidEl.muted = true;
                 vidEl.defaultMuted = true;
                 vidEl.setAttribute('muted', '');
                 vidEl._autoPlaying = true;
                 const p = vidEl.play();
                 if (p !== undefined) {
                   p.then(() => {
                     this.isPlaying = true;
                     this.isBuffering = false;
                   }).catch((err) => {
                     vidEl._autoPlaying = false;
                     if (!vidEl.muted) {
                       vidEl.muted = true;
                       vidEl.defaultMuted = true;
                       vidEl.setAttribute('muted', '');
                       this.isVideoMuted = true;
                       vidEl.play().then(() => {
                         this.isPlaying = true;
                         this.isBuffering = false;
                       }).catch(() => {});
                     } else if (err && err.name === 'AbortError' && this._isInView && !vidEl._userManuallyPaused) {
                       requestAnimationFrame(() => {
                         if (this._isInView && !vidEl._userManuallyPaused && vidEl.paused) {
                           tryPlay();
                         }
                       });
                     }
                   });
                 }
               };
               tryPlay();
               if (vidEl.readyState < 2) {
                 if (!vidEl._awaitingSinglePlay) {
                   vidEl._awaitingSinglePlay = true;
                   const onReady = () => {
                     vidEl._awaitingSinglePlay = false;
                     vidEl.removeEventListener('loadeddata', onReady);
                     vidEl.removeEventListener('canplay', onReady);
                     if (this._isInView && vidEl.paused) {
                       tryPlay();
                     }
                   };
                   vidEl.addEventListener('loadeddata', onReady, { once: true });
                   vidEl.addEventListener('canplay', onReady, { once: true });
                 }
                 if (vidEl.networkState === 0 && !vidEl.currentSrc) {
                   try { vidEl.load(); } catch (_) {}
                 }
               }
             };
             const pauseVideoOutOfView = () => {
               const vidEl = this.$el.querySelector('video');
               if (!vidEl) return;
               vidEl._autoPlaying = false;
               if (!vidEl.paused) {
                 vidEl.pause();
                 this.isPlaying = false;
               }
             };

             if (window.__FPFeedVideoCoordinator) {
               window.__FPFeedVideoCoordinator.register(this.$el, {
                 getVideo: () => this.$el.querySelector('video'),
                 play: () => {
                   this._isInView = true;
                   playVideoInView();
                 },
                 pause: () => {
                   this._isInView = false;
                   pauseVideoOutOfView();
                 }
               });
               return;
             }

             if (typeof IntersectionObserver === 'undefined') return;
             const obs = new IntersectionObserver((entries) => {
               entries.forEach(entry => {
                 const vidEl = this.$el.querySelector('video');
                 if (!vidEl) return;
                 if (entry.isIntersecting && entry.intersectionRatio >= 0.25) {
                   this._isInView = true;
                   document.querySelectorAll('video').forEach(other => {
                     if (other !== vidEl && !other.paused) {
                       other._autoPlaying = false;
                       other.pause();
                     }
                   });
                   playVideoInView();
                 } else if (!entry.isIntersecting || entry.intersectionRatio < 0.15) {
                   this._isInView = false;
                   pauseVideoOutOfView();
                   vidEl._userManuallyPaused = false;
                 }
               });
             }, { threshold: [0.0, 0.15, 0.25, 0.5] });
             obs.observe(this.$el);
           }
         }"
         x-init="$nextTick(() => initSingleVideoObserver())">
      
      <!-- Outer Adaptive Container: True-Aspect Uncropped Framing Across All Video Types -->
      <div class="video-rendition-frame feed-media-aura-container position-relative overflow-hidden mx-auto shadow-sm"
           @mouseenter="showControlsOverlay = true; revealControls(false)"
           @mouseleave="showControlsOverlay = false; if (isPlaying) scheduleControlsHide()"
           :style="'aspect-ratio: ' + (viewMode === 'reel' ? (isPortrait ? videoRatio : '9 / 16') : videoRatio) + '; max-height: ' + (viewMode === 'reel' ? '720px' : (cinemaExpanded && !isPortrait ? '75vh' : videoMaxHeight)) + '; width: 100%; max-width: ' + (viewMode === 'reel' && !isPortrait ? '420px' : '100%') + '; border-radius: 16px; background: ' + (((viewMode === 'reel' && !isPortrait) || (isPortrait && !portraitExpanded)) ? '#0b0f19' : 'rgba(11, 15, 25, 0.72)') + '; transition: aspect-ratio 0.3s cubic-bezier(0.4, 0, 0.2, 1), max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1);'">

        <!-- Ambient Edge-Bleed Aura Backdrop for True-Aspect Contain Mode -->
        <div class="feed-media-aura-backdrop" x-show="!((viewMode === 'reel' && !isPortrait) || (isPortrait && !portraitExpanded)) && post.video && post.video.thumbnailUrl" x-cloak aria-hidden="true" style="z-index: 1; inset: 0;">
          <img :src="post.video ? post.video.thumbnailUrl : ''" alt="" loading="lazy">
        </div>

        <!-- High-Res Video Poster Overlay before stream frames decode (Guarantees zero white flash) -->
        <img x-show="!hasMetadata && post.video && post.video.thumbnailUrl"
             :src="post.video.thumbnailUrl"
             class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none"
             :style="'z-index: 3; border-radius: 16px; object-fit: ' + (((viewMode === 'reel' && !isPortrait) || (isPortrait && !portraitExpanded) || (!isPortrait && videoOrientation === 'landscape' && !cinemaExpanded)) ? 'cover' : 'contain') + ' !important; object-position: ' + videoFocalPos + ';'"
             loading="lazy"
             alt="Video preview thumbnail"
             x-on:error="$el.style.display = 'none'">

        <!-- Main Video Element (True-Aspect Uncropped Default for All Video Types) -->
        <video referrerpolicy="no-referrer" class="feed-post-video atg-4k-video-render w-100 h-100 position-relative"
               :src="(post.video && post.video.hlsUrl) ? undefined : post.video.embedUrl"
               :data-hls-url="post.video && post.video.hlsUrl ? post.video.hlsUrl : ''"
               :data-fallback-src="post.video ? post.video.embedUrl : ''"
               :poster="(!hasMetadata && post.video && post.video.thumbnailUrl) ? post.video.thumbnailUrl : null"
               playsinline
               webkit-playsinline
               muted
               loop
               preload="metadata"
               @click.stop="togglePlayback($el); revealControls(true)"
               @dblclick.stop="openVideoModal([post.video], 0, isVideoMuted, post)"
               @loadedmetadata="
                 detectVideoDimensions($el);
                 const b = getTrimBounds($el);
                 durationFormatted = formatTime(b.clipDur);
                 if ($el.currentTime < b.start) { 
                   $el.currentTime = b.start; 
                 }
               "
               @loadeddata="hasMetadata = true; isBuffering = false; detectVideoDimensions($el)"
               @waiting="if (!$el.paused && !$el.ended && $el.readyState < 3) isBuffering = true"
               @canplay="isBuffering = false"
               @canplaythrough="isBuffering = false"
               @playing="hasMetadata = true; isBuffering = false"
               @seeked="isBuffering = false"
               @stalled="isBuffering = false"
               @abort="isBuffering = false"
               @emptied="isBuffering = false"
               x-on:error="isBuffering = false; if (!$el.dataset.retried && post.video && post.video.originalUrl && $el.src !== post.video.originalUrl && post.video.originalUrl.startsWith('http')) { $el.dataset.retried = '1'; const shouldAutoResume = !!(isPlaying || $el._autoPlaying); $el.src = post.video.originalUrl; $el.load(); if (shouldAutoResume) { $el.addEventListener('canplay', () => { if (!$el._userManuallyPaused) { $el.muted = true; $el.play().catch(() => {}); } }, { once: true }); } }"
               @play="
                 isPlaying = true;
                 scheduleControlsHide();
                 const b = getTrimBounds($el);
                 if ($el.currentTime < b.start || $el.currentTime >= b.end) { 
                   $el.currentTime = b.start; 
                 }
               "
               @pause="isPlaying = false; isBuffering = false; revealControls(false)"
               @timeupdate="if (isBuffering && !$el.paused && $el.readyState >= 2) isBuffering = false; handleTimeUpdate($el)"
               @volumechange="isVideoMuted = $el.muted"
               :style="'width: 100%; height: 100%; object-fit: ' + (((viewMode === 'reel' && !isPortrait) || (isPortrait && !portraitExpanded) || (!isPortrait && videoOrientation === 'landscape' && !cinemaExpanded)) ? 'cover' : 'contain') + ' !important; object-position: ' + videoFocalPos + '; z-index: 2; border-radius: 16px; cursor: pointer; background: transparent;'"></video>

        <!-- Buffering Spinner Overlay -->
        <div x-show="isBuffering" x-cloak class="position-absolute top-50 start-50 translate-middle pointer-events-none" style="z-index: 5;">
          <div class="spinner-border text-light" role="status" style="width: 2.25rem; height: 2.25rem; border-width: 2.5px; opacity: 0.88;">
            <span class="visually-hidden">Loading video…</span>
          </div>
        </div>

        <!-- Format Badge & 1-Tap Aspect / Theatre Switcher (Top-Left, auto-hides during playback) -->
        <div class="position-absolute d-inline-flex align-items-center gap-2 shadow-sm"
             :class="(controlsVisible || !isPlaying) ? 'opacity-100' : 'opacity-0 pointer-events-none'"
             style="top: 12px; left: 12px; z-index: 6; transition: opacity 0.25s ease;">
          <template x-if="isPortrait">
            <div class="d-inline-flex align-items-center gap-2">
              <span class="badge rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1 pointer-events-none"
                    style="background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.22); color: #ffffff; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em;">
                <i class="bi bi-phone-fill text-info" style="font-size: 0.75rem;"></i>
                <span>PORTRAIT</span>
              </span>
              <button type="button"
                      class="btn d-inline-flex align-items-center gap-1 px-3 rounded-pill shadow-sm"
                      @click.stop="togglePortraitExpand()"
                      :title="portraitExpanded ? 'Collapse to 4:5 feed frame' : 'Expand to 9:16 full frame'"
                      :aria-label="portraitExpanded ? 'Collapse to 4:5 feed frame' : 'Expand to 9:16 full frame'"
                      style="background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.25); color: #ffffff; font-size: 0.72rem; font-weight: 700; cursor: pointer; white-space: nowrap; flex-shrink: 0; min-height: 44px; min-width: 44px;">
                <i class="bi" :class="portraitExpanded ? 'bi-arrows-angle-contract text-warning' : 'bi-arrows-angle-expand text-info'"></i>
                <span x-text="portraitExpanded ? '4:5' : '9:16'"></span>
              </button>
            </div>
          </template>
          <template x-if="!isPortrait && videoOrientation === 'landscape'">
            <div class="d-inline-flex align-items-center gap-2">
              <span class="badge rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1 pointer-events-none"
                    style="background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.22); color: #ffffff; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em;">
                <i class="bi bi-badge-4k-fill text-warning" style="font-size: 0.75rem;"></i>
                <span>4K ULTRA HD</span>
              </span>
              <!-- 1-Tap Reel / Cinema Mode Switcher -->
              <button type="button" 
                      class="btn d-inline-flex align-items-center gap-1 px-3 rounded-pill shadow-sm"
                      @click.stop="toggleViewMode()"
                      :title="viewMode === 'reel' ? 'Switch to Cinema 16:9 view' : 'Switch to Immersive Reel 9:16 view'"
                      :aria-label="viewMode === 'reel' ? 'Switch to Cinema 16:9 view' : 'Switch to Immersive Reel 9:16 view'"
                      style="background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.25); color: #ffffff; font-size: 0.72rem; font-weight: 700; cursor: pointer; white-space: nowrap; flex-shrink: 0; min-height: 44px; min-width: 44px;">
                <i class="bi" :class="viewMode === 'reel' ? 'bi-aspect-ratio text-info' : 'bi-phone text-warning'"></i>
                <span x-text="viewMode === 'reel' ? 'REEL' : 'CINEMA'"></span>
              </button>
              <!-- Desktop Theatre / Cinema Expand Toggle -->
              <button type="button"
                      class="btn d-none d-md-inline-flex align-items-center gap-1 px-3 rounded-pill shadow-sm"
                      @click.stop="toggleCinemaExpand()"
                      :title="cinemaExpanded ? 'Exit Theatre View' : 'Expand to Theatre View'"
                      :aria-label="cinemaExpanded ? 'Exit Theatre View' : 'Expand to Theatre View'"
                      style="background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.25); color: #ffffff; font-size: 0.72rem; font-weight: 700; cursor: pointer; white-space: nowrap; flex-shrink: 0; min-height: 44px; min-width: 44px;">
                <i class="bi" :class="cinemaExpanded ? 'bi-fullscreen-exit text-warning' : 'bi-display text-info'"></i>
                <span x-text="cinemaExpanded ? 'EXIT THEATRE' : 'THEATRE'"></span>
              </button>
            </div>
          </template>
          <template x-if="!isPortrait && videoOrientation === 'square'">
            <span class="badge rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1 pointer-events-none"
                  style="background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.22); color: #ffffff; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em;">
              <i class="bi bi-square-fill text-success" style="font-size: 0.7rem;"></i>
              <span>SQUARE HD</span>
            </span>
          </template>
        </div>

        <!-- Full Video Mode Pill Top-Right (44x44px tap target, 8px separation from mute) -->
        <button type="button" 
                class="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                @click.stop="openVideoModal([post.video], 0, isVideoMuted, post)"
                title="View Fullscreen in full video mode"
                aria-label="View Fullscreen in full video mode"
                style="top: 12px; right: 64px; z-index: 6; width: 44px; height: 44px; min-width: 44px; min-height: 44px; border-radius: 9999px; background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.25); color: #ffffff; cursor: pointer; transition: transform 0.15s ease, background 0.15s ease; white-space: nowrap; flex-shrink: 0;">
          <i class="bi bi-arrows-fullscreen" style="font-size: 0.88rem; color: #ffffff;"></i>
        </button>

        <!-- Instagram 1-Tap Sound Toggle Pill (Top-Right, 44x44px tap target) -->
        <button type="button" 
                class="btn position-absolute d-inline-flex align-items-center justify-content-center p-0 shadow-sm"
                @click.stop="toggleMute($el.closest('.video-rendition-frame').querySelector('video'))"
                :title="isVideoMuted ? 'Unmute video' : 'Mute video'"
                :aria-label="isVideoMuted ? 'Unmute video' : 'Mute video'"
                style="top: 12px; right: 12px; z-index: 6; width: 44px; height: 44px; min-width: 44px; min-height: 44px; border-radius: 9999px; background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.25); color: #ffffff; cursor: pointer; transition: transform 0.15s ease, background 0.15s ease; white-space: nowrap; flex-shrink: 0;">
          <i class="bi" :class="isVideoMuted ? 'bi-volume-mute-fill' : 'bi-volume-up-fill'" style="font-size: 0.92rem; color: #ffffff;"></i>
        </button>

        <!-- Instagram Bottom Overlay Controls & Progress Scrubber (auto-hides during playback) -->
        <div class="position-absolute bottom-0 start-0 w-100" 
             :class="(controlsVisible || !isPlaying) ? 'opacity-100' : 'opacity-0 pointer-events-none'"
             style="z-index: 6; background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.35) 60%, transparent 100%); padding: 24px 12px 10px 12px; transition: opacity 0.25s ease;">
          
          <!-- Mini Control Strip: Play/Pause, Timestamps, Fullscreen -->
          <div class="d-flex align-items-center justify-content-between text-white mb-1.5" style="font-size: 0.75rem; font-weight: 600;">
            <div class="d-flex align-items-center gap-2">
              <button type="button" 
                      class="btn btn-link text-white p-0 d-inline-flex align-items-center justify-content-center text-decoration-none"
                      @click.stop="togglePlayback($el.closest('.video-rendition-frame').querySelector('video'))"
                      :title="isPlaying ? 'Pause video' : 'Play video'"
                      :aria-label="isPlaying ? 'Pause video' : 'Play video'"
                      style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                <i class="bi fs-5" :class="isPlaying ? 'bi-pause-fill' : 'bi-play-fill'" style="color: #ffffff;"></i>
              </button>
              <span class="font-monospace" style="letter-spacing: 0.02em;">
                <span x-text="currentTimeFormatted">0:00</span> / <span x-text="durationFormatted">0:00</span>
              </span>
            </div>

            <div class="d-flex align-items-center gap-1.5">
              <!-- Full Video Mode Button -->
              <button type="button" 
                      class="btn btn-link text-white p-0 d-inline-flex align-items-center justify-content-center text-decoration-none"
                      @click.stop="openVideoModal([post.video], 0, isVideoMuted, post)"
                      title="Play in full video mode"
                      aria-label="Play in full video mode"
                      style="width: 44px; height: 44px; min-width: 44px; min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                <i class="bi bi-arrows-fullscreen" style="font-size: 0.88rem; color: #ffffff;"></i>
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

        <!-- Minimal 3px Progress Hairline when Overlay Controls Auto-Hide -->
        <div x-show="isPlaying && !controlsVisible"
             x-cloak
             class="position-absolute bottom-0 start-0 w-100 pointer-events-none"
             style="height: 3px; background: rgba(255, 255, 255, 0.18); z-index: 5;">
          <div class="h-100" :style="'width: ' + progressPercent + '%; background: #ffffff; transition: width 0.08s linear;'"></div>
        </div>

      </div>
    </div>
  </template>


  @include("components.feed.partials.link-preview")

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
                  :title="post.two_truths.is_author ? 'As the storyteller, you cannot vote on your own challenge. Other family members can vote directly here!' : (post.two_truths.has_voted ? 'Your guess is locked in' : 'Tap to guess this statement as the lie')"
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
          <span class="tt-chip" title="Other family members can vote directly on this post in the feed"><i class="bi bi-stars"></i> Your challenge is live &bull; family can vote here</span>
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

  <!-- 3D Futuristic & Translucent Poll / Trivia / Game Show Arena -->
  <template x-if="post.poll && post.poll.options && post.poll.options.length > 0">
    <div class="fp-poll-arena mx-1 mb-3"
         :class="'fp-poll-arena--' + getPollArenaMeta(post).theme"
         data-testid="feed-poll-arena-card">
      <div class="fp-poll-arena__orb fp-poll-arena__orb--a" aria-hidden="true"></div>
      <div class="fp-poll-arena__orb fp-poll-arena__orb--b" aria-hidden="true"></div>
      <div class="fp-poll-arena__grid" aria-hidden="true"></div>

      <!-- Top Header Bar: 3D Arena Badge + Live Vote Counter Pill -->
      <div class="fp-poll-arena__top">
        <span class="fp-poll-arena__badge">
          <span x-text="getPollArenaMeta(post).badge"></span>
        </span>
        <span class="fp-poll-arena__votes-pill">
          <i class="bi bi-people-fill"></i>
          <span x-text="(post.poll.total_votes || 0) + ((post.poll.total_votes || 0) === 1 ? ' vote' : ' votes')"></span>
        </span>
      </div>

      <!-- Optional Clean Intro Context (Stripped of duplicate question & boilerplate) -->
      <template x-if="getPollCleanIntro(post)">
        <div class="fp-poll-arena__intro" x-html="renderHashtags(getPollCleanIntro(post), post.ar_emoji)"></div>
      </template>

      <!-- Hero Question Typography (Displayed ONCE in crisp 3D contrast) -->
      <h6 class="fp-poll-arena__question" x-text="post.poll.question"></h6>

      <p class="fp-poll-arena__subtitle mb-3" x-text="getPollArenaMeta(post).subtitle"></p>

      <!-- Tactile 3D Translucent Option Bars -->
      <div class="fp-poll-arena__options">
        <template x-for="(opt, optIdx) in post.poll.options" :key="opt.option_id">
          <button type="button"
                  @click="votePoll(post.post_no, opt.option_id)"
                  :disabled="isVotingPoll(post.post_no)"
                  class="fp-poll-opt poll-option-btn"
                  :class="{
                    'is-voted': isOptionVoted(post, opt.option_id),
                    'is-leading': getLeadingPollOptionId(post) !== null && String(getLeadingPollOptionId(post)) === String(opt.option_id),
                    'opacity-75 cursor-not-allowed': isVotingPoll(post.post_no)
                  }">
            <div class="fp-poll-opt__fill"
                 :style="'width: ' + (opt.percentage || 0) + '%;'"></div>

            <span class="fp-poll-opt__letter" x-text="getPollOptionLetter(optIdx)"></span>

            <span class="fp-poll-opt__label">
              <span x-text="opt.option_text"></span>
              <template x-if="isOptionVoted(post, opt.option_id)">
                <i class="bi bi-check-circle-fill fp-poll-opt__check" title="Your vote"></i>
              </template>
              <template x-if="getLeadingPollOptionId(post) !== null && String(getLeadingPollOptionId(post)) === String(opt.option_id)">
                <span class="fp-poll-opt__crown" title="Leading choice">👑</span>
              </template>
              <span x-show="isVotingPoll(post.post_no)" class="spinner-border spinner-border-sm ms-1" role="status" aria-hidden="true" style="width: 0.85rem; height: 0.85rem;"></span>
            </span>

            <span class="fp-poll-opt__pct" x-text="(opt.percentage || 0) + '%'"></span>
          </button>
        </template>
      </div>

      <!-- Arena Footer -->
      <div class="fp-poll-arena__footer">
        <span class="fp-poll-arena__hint">
          <i class="bi bi-hand-index-thumb-fill"></i>
          <span>Tap any option to vote or switch your pick</span>
        </span>
        <span class="fp-poll-arena__discuss">
          <i class="bi bi-chat-dots-fill"></i>
          <span>Debate below</span>
        </span>
      </div>
    </div>
  </template>

  @include("components.feed.partials.actions")

  @include("components.feed.partials.comments")

</div>
