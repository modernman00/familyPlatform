{{-- @props(["post" => null]) --}}
  <!-- Single Post Image (Hero Adaptive Natural Aspect Bleed with Ambient Aura & ATG Saliency Focal Engine) -->
  <template x-if="post.images && post.images.length === 1 && !post.comparison">
    <div class="mb-3 px-1"
         x-data="{
           singleAspect: '4 / 3',
           singleFocal: '50% 34%',
           imageError: false,
           onSingleImageLoad(el) {
             this.imageError = false;
             if (window.AtgMediaSupremacy && typeof window.AtgMediaSupremacy.analyzeImage === 'function') {
               const info = window.AtgMediaSupremacy.analyzeImage(el);
               this.singleAspect = info.aspectRatio;
               this.singleFocal = info.focalPosition;
             } else if (el.naturalWidth && el.naturalHeight) {
               const r = el.naturalWidth / el.naturalHeight;
               const clamped = Math.min(1.91, Math.max(0.80, r));
               this.singleAspect = clamped.toFixed(4) + ' / 1';
               this.singleFocal = r < 0.92 ? '50% 28%' : '50% 38%';
             }
           },
           onSingleImageError() {
             this.imageError = true;
             this.singleAspect = '4 / 3';
           }
         }"
         x-init="$nextTick(() => {
           const singleImg = $el.querySelector('img.post-media-first-img');
           if (singleImg && singleImg.complete) {
             if (singleImg.naturalWidth && singleImg.naturalHeight) {
               onSingleImageLoad(singleImg);
             } else if (singleImg.getAttribute('src')) {
               onSingleImageError();
             }
           }
         })">
      <div class="position-relative feed-media-aura-container">
        <!-- Ambient Dynamic Backlight Glow (Cinematic Aura) -->
        <div class="feed-media-aura-backdrop" aria-hidden="true" x-show="!imageError">
          <img :src="getPostImageUrl(post.images[0])" 
               alt="" 
               loading="lazy" 
               decoding="async" 
               aria-hidden="true"
               x-on:error="$event.target.style.display='none'">
        </div>

        <!-- Main Media Stage -->
        <div class="position-relative overflow-hidden post-media-stage vse-skeleton-surface" 
             :class="[
               playingVoicePostNo === post.post_no ? 'photo-voice-playing' : '',
               ((post.post_no || 0) % 3 === 0 ? 'living-drift-a' : ((post.post_no || 0) % 3 === 1 ? 'living-drift-b' : 'living-drift-c'))
             ]"
             :style="'border-radius:12px; width:100%; min-height: 220px; max-height: 600px; aspect-ratio: ' + singleAspect + '; background: rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: center; transition: aspect-ratio 0.35s cubic-bezier(0.16, 1, 0.3, 1);'">
          <a href="#" 
             x-show="!imageError"
             @click.prevent="typeof handleImageTap === 'function' ? handleImageTap(post, post.images, 0, $event) : openLightbox(post.images, 0, post)" 
             @dblclick.prevent.stop="triggerHeartBurst(post, 0)"
             role="button" 
             aria-label="View enlarged photo or double tap to like" 
             style="display:block; overflow:hidden; border-radius:12px; position:relative; width:100%; height:100%;">
            <template x-if="isGifMedia(post.images[0])">
              <img :src="getPostImageUrl(post.images[0])" 
                   class="post-media-first-img living-canvas-img w-100 h-100"
                   :style="'border-radius: 12px; width: 100%; height: 100%; max-height: 600px; object-fit: contain; cursor: pointer; image-rendering: auto;'" 
                   alt="Family post GIF" 
                   @load="onSingleImageLoad($event.target)"
                   x-on:error="onSingleImageError()"
                   loading="lazy">
            </template>
            <template x-if="!isGifMedia(post.images[0])">
              <picture class="w-100 h-100 d-block" style="border-radius: 12px; overflow: hidden; height: 100%;">
                <template x-if="getPostImageVariantUrl(post.images[0], 'avif', post.image_variants || false)">
                  <source :srcset="getPostImageVariantUrl(post.images[0], 'avif', post.image_variants || false)" type="image/avif">
                </template>
                <template x-if="getPostImageVariantUrl(post.images[0], 'webp', post.image_variants || false)">
                  <source :srcset="getPostImageVariantUrl(post.images[0], 'webp', post.image_variants || false)" type="image/webp">
                </template>
                <img :src="getPostImageUrl(post.images[0])" 
                     class="post-media-first-img living-canvas-img w-100 h-100"
                     :style="'border-radius: 12px; width: 100%; height: 100%; max-height: 600px; object-fit: cover; object-position: ' + singleFocal + '; cursor: pointer; image-rendering: auto;'" 
                     alt="Family post image" 
                     @load="onSingleImageLoad($event.target)"
                     x-on:error="onSingleImageError()"
                     loading="lazy"
                     decoding="async">
              </picture>
            </template>
          </a>
          <template x-if="imageError">
            <div class="post-media-stage__fallback" role="img" aria-label="Photo unavailable">
              <i class="bi bi-image" style="font-size: 2rem; color: #64748b;"></i>
              <span>Photo unavailable</span>
            </div>
          </template>

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

