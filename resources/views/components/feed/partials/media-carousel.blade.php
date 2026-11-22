{{-- @props(["post" => null]) --}}
  <!-- Multi-Image Instagram Swipeable Carousel (> 1 images) -->
  <template x-if="post.images && post.images.length > 1 && !post.comparison">
    <div class="mb-3 px-1 post-carousel-wrapper" 
         x-data="{ 
           currentIdx: 0,
           carouselAspect: '4 / 3',
           slideAspects: {},
           slideFocals: {},
           slideErrors: {},
           onCarouselImageLoad(el, idx) {
             this.slideErrors[idx] = false;
             if (window.AtgMediaSupremacy && typeof window.AtgMediaSupremacy.analyzeImage === 'function') {
               const info = window.AtgMediaSupremacy.analyzeImage(el);
               this.slideAspects[idx] = info.aspectRatio;
               this.slideFocals[idx] = info.focalPosition;
               if (idx === 0 || idx === this.currentIdx) {
                 this.carouselAspect = info.aspectRatio;
               }
             } else if (el.naturalWidth && el.naturalHeight) {
               const r = el.naturalWidth / el.naturalHeight;
               const clamped = Math.min(1.91, Math.max(0.80, r));
               const aspectStr = clamped.toFixed(4) + ' / 1';
               this.slideAspects[idx] = aspectStr;
               this.slideFocals[idx] = r < 0.92 ? '50% 28%' : '50% 38%';
               if (idx === 0 || idx === this.currentIdx) {
                 this.carouselAspect = aspectStr;
               }
             }
           },
           onCarouselImageError(idx) {
             this.slideErrors[idx] = true;
             this.slideAspects[idx] = '4 / 3';
             if (idx === 0 || idx === this.currentIdx) {
               this.carouselAspect = '4 / 3';
             }
           },
           nextSlide() {
             if (this.currentIdx < post.images.length - 1) {
               this.goToSlide(this.currentIdx + 1);
             }
           },
           prevSlide() {
             if (this.currentIdx > 0) {
               this.goToSlide(this.currentIdx - 1);
             }
           },
           goToSlide(idx) {
             const track = this.$refs.carouselTrack;
             if (track) {
               track.scrollTo({ left: idx * track.clientWidth, behavior: 'smooth' });
             }
             this.currentIdx = idx;
             if (this.slideAspects[idx]) {
               this.carouselAspect = this.slideAspects[idx];
             }
           }
         }"
         x-init="$nextTick(() => {
           const imgs = $refs.carouselTrack?.querySelectorAll('img') || [];
           imgs.forEach((imgEl, i) => {
             if (imgEl && imgEl.complete) {
               if (imgEl.naturalWidth && imgEl.naturalHeight) {
                 onCarouselImageLoad(imgEl, i);
               } else if (imgEl.getAttribute('src')) {
                 onCarouselImageError(i);
               }
             }
           });
         })">
      <div class="position-relative feed-media-aura-container">
        <!-- Ambient Dynamic Backlight Glow for Carousel (Synchronised to Active Slide) -->
        <div class="feed-media-aura-backdrop" aria-hidden="true" x-show="!slideErrors[currentIdx]">
          <img :src="getPostImageUrl(post.images[currentIdx] || post.images[0])" 
               alt="" 
               loading="lazy" 
               decoding="async" 
               aria-hidden="true"
               x-on:error="$event.target.style.display='none'">
        </div>

        <div class="position-relative overflow-hidden post-media-stage vse-skeleton-surface" 
             :class="[
               playingVoicePostNo === post.post_no ? 'photo-voice-playing' : '',
               ((post.post_no || 0) % 3 === 0 ? 'living-drift-a' : ((post.post_no || 0) % 3 === 1 ? 'living-drift-b' : 'living-drift-c'))
             ]"
             :style="'border-radius:12px; aspect-ratio: ' + (slideAspects[currentIdx] || carouselAspect) + '; min-height: 220px; max-height: 600px; width: 100%; background: #0b0f19; transition: aspect-ratio 0.38s cubic-bezier(0.16, 1, 0.3, 1);'">
          
          <!-- Fanciful Instagram Multi-Photo 3-Layer Luxury Stacked Badge -->
          <div class="position-absolute top-0 end-0 m-3 carousel-fanciful-badge"
               title="Multiple photos in carousel — swipe or tap arrows to view all"
               aria-label="Multiple photos in post">
            <svg class="carousel-cards-icon" viewBox="0 0 26 26" width="18" height="18" fill="none" aria-hidden="true">
              <!-- Deepest 3rd card (gold shimmer fan) -->
              <rect x="9" y="2" width="14" height="14" rx="3.2" fill="rgba(255, 215, 0, 0.22)" stroke="#ffd700" stroke-width="1.3" opacity="0.85" />
              <!-- Middle 2nd card -->
              <rect x="5.5" y="5.5" width="14" height="14" rx="3.2" fill="rgba(30, 41, 59, 0.9)" stroke="#fde047" stroke-width="1.4" />
              <!-- Front Hero card -->
              <rect x="2" y="9" width="14.5" height="14.5" rx="3.4" fill="#0f172a" stroke="#ffffff" stroke-width="1.7" />
              <!-- Golden Sun & Mountain Panorama inside Front Card -->
              <circle cx="6.8" cy="13.8" r="1.6" fill="#ffd700" />
              <path d="M3.6 20.8l3.8-4.6 2.7 3.1 2.2-2.3 2.8 3.8H3.6z" fill="#ffffff" opacity="0.95" />
            </svg>
            <span class="carousel-badge-dot" aria-hidden="true"></span>
            <span class="carousel-counter-text" x-text="(currentIdx + 1) + ' / ' + post.images.length"></span>
          </div>

          <!-- Desktop / Touch Navigation Chevrons -->
          <button type="button" 
                  class="carousel-nav-btn carousel-nav-prev" 
                  x-show="currentIdx > 0" 
                  x-cloak 
                  @click.stop.prevent="prevSlide()"
                  aria-label="Previous photo in carousel">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
          </button>
          <button type="button" 
                  class="carousel-nav-btn carousel-nav-next" 
                  x-show="currentIdx < post.images.length - 1" 
                  x-cloak 
                  @click.stop.prevent="nextSlide()"
                  aria-label="Next photo in carousel">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>

          <!-- Snap-Scroll Track -->
          <div class="d-flex overflow-auto post-carousel-track" 
               x-ref="carouselTrack"
               style="scroll-snap-type: x mandatory; scrollbar-width: none; -webkit-overflow-scrolling: touch; height: 100%; width: 100%;"
               @scroll.passive="currentIdx = Math.round($event.target.scrollLeft / ($event.target.clientWidth || 1))">
            <template x-for="(img, idx) in post.images" :key="idx">
              <div class="flex-shrink-0 w-100 position-relative carousel-slide-item" 
                   :class="currentIdx === idx ? 'is-slide-active' : ''"
                   style="scroll-snap-align: start; min-width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                <a href="#" 
                   x-show="!slideErrors[idx]"
                   @click.prevent="typeof handleImageTap === 'function' ? handleImageTap(post, post.images, idx, $event) : openLightbox(post.images, idx, post)" 
                   @dblclick.prevent.stop="triggerHeartBurst(post, idx)"
                   role="button" 
                   aria-label="View enlarged photo or double tap to like" 
                   style="display:block; overflow:hidden; border-radius:12px; position:relative; width:100%; height:100%;">
                  <template x-if="isGifMedia(img)">
                    <img :src="getPostImageUrl(img)" 
                         class="post-media-first-img living-canvas-img w-100 h-100"
                         :style="'border-radius: 12px; width: 100%; height: 100%; max-height: 600px; object-fit: contain; cursor: pointer;'" 
                         alt="Family carousel GIF slide" 
                         @load="onCarouselImageLoad($event.target, idx)"
                         x-on:error="onCarouselImageError(idx)"
                         loading="lazy">
                  </template>
                  <template x-if="!isGifMedia(img)">
                    <picture class="w-100 h-100 d-block" style="border-radius: 12px; overflow: hidden; height: 100%;">
                      <template x-if="getPostImageVariantUrl(img, 'avif', post.image_variants || false)">
                        <source :srcset="getPostImageVariantUrl(img, 'avif', post.image_variants || false)" type="image/avif">
                      </template>
                      <template x-if="getPostImageVariantUrl(img, 'webp', post.image_variants || false)">
                        <source :srcset="getPostImageVariantUrl(img, 'webp', post.image_variants || false)" type="image/webp">
                      </template>
                      <img :src="getPostImageUrl(img)" 
                           class="post-media-first-img living-canvas-img w-100 h-100"
                           :style="'border-radius: 12px; width: 100%; height: 100%; max-height: 600px; object-fit: cover; object-position: ' + (slideFocals[idx] || '50% 34%') + '; cursor: pointer; image-rendering: auto;'" 
                           alt="Family carousel slide" 
                           @load="onCarouselImageLoad($event.target, idx)"
                           x-on:error="onCarouselImageError(idx)"
                           loading="lazy"
                           decoding="async">
                    </picture>
                  </template>
                </a>
                <template x-if="slideErrors[idx]">
                  <div class="post-media-stage__fallback" role="img" aria-label="Photo unavailable">
                    <i class="bi bi-image" style="font-size: 2rem; color: #64748b;"></i>
                    <span>Photo unavailable</span>
                  </div>
                </template>

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
          <!-- ATG Super Avatar Quick-Launch Pill (Active Slide) -->
          <template x-if="!slideErrors[currentIdx] && !isGifMedia(post.images[currentIdx] || post.images[0]) && (getPostImageUrl(post.images[currentIdx] || post.images[0]) || '').startsWith('/')">
            <a :href="'/member/emojiStudio?tab=photo-avatar&photo_url=' + encodeURIComponent(getPostImageUrl(post.images[currentIdx] || post.images[0]))"
               @click.stop
               class="position-absolute bottom-0 end-0 m-2 d-inline-flex align-items-center gap-1 px-3 rounded-pill text-white text-decoration-none shadow-sm"
               title="Transform this photo into an OpenArt-grade Living Avatar"
               aria-label="Transform this photo into a Living Avatar"
               style="z-index: 4; min-height: 44px; min-width: 44px; background: rgba(15, 23, 42, 0.78); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.24); font-size: 0.78rem; font-weight: 600; white-space: nowrap; flex-shrink: 0;">
              <span aria-hidden="true">✨</span>
              <span>Living Avatar</span>
            </a>
          </template>

        </div>
      </div>

      <!-- Instagram Dynamic Animated Pagination Dots -->
      <div class="d-flex justify-content-center align-items-center gap-1.5 mt-2 post-carousel-dots" aria-label="Photo carousel pagination">
        <template x-for="(img, dIdx) in post.images" :key="dIdx">
          <button type="button" 
                  class="carousel-dot border-0 p-0" 
                  :class="currentIdx === dIdx ? 'is-active' : ''"
                  @click.stop.prevent="goToSlide(dIdx)"
                  :aria-label="'Go to photo ' + (dIdx + 1)"
                  style="cursor: pointer;"></button>
        </template>
      </div>
    </div>
  </template>
