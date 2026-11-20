{{-- @props(["post" => null]) --}}
  <!-- Weekly Family Trivia Badge (Only when no 3D Poll Arena is rendered) -->
  <template x-if="!hasPollArena(post) && post.postMessage && post.postMessage.includes('Weekly Family Trivia')">
    <div class="px-1 mb-2">
      <span class="badge rounded-pill px-3 py-1.5 text-white shadow-sm d-inline-flex align-items-center gap-1" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.5px;">
        <i class="bi bi-trophy-fill"></i> {{ __('feed.trivia_badge', 'WEEKLY FAMILY TRIVIA') }}
      </span>
    </div>
  </template>

  <!-- Two Truths & a Lie Badge (Only when the 3D Two Truths Card is not already rendering its own header) -->
  <template x-if="!post.two_truths && (post.postMessage && post.postMessage.includes('Two Truths & a Lie'))">
    <div class="px-1 mb-2">
      <span class="badge rounded-pill px-3 py-1.5 text-white shadow-sm d-inline-flex align-items-center gap-1.5" style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.5px;">
        <i class="bi bi-mask"></i> TWO TRUTHS &amp; A LIE
      </span>
    </div>
  </template>

  <!-- Family Chronicle / Journal Badge (Only when not already rendered inside a 3D Text Stage) -->
  <template x-if="!getPostStageMeta(post) && post.postMessage && (post.postMessage.includes('Family Chronicle') || post.postMessage.includes('/familyJournal/'))">
    <div class="px-1 mb-2">
      <span class="badge rounded-pill px-3 py-1.5 text-white shadow-sm d-inline-flex align-items-center gap-1.5" style="background: linear-gradient(135deg, #b8202c 0%, #8a1019 100%); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.5px;">
        <i class="bi bi-journal-bookmark-fill"></i> {{ __('feed.chronicle_badge', 'FAMILY CHRONICLE') }}
      </span>
    </div>
  </template>



  <!-- Post Text (Prominent for Text-Only Posts & 3D Futuristic Translucent Stage Cards) -->
  <template x-if="!post.two_truths && !hasPollArena(post) && (getMediaCaption(post) || post.ar_emoji || getPostStageMeta(post)) && (!post.images || post.images.length === 0) && !post.video && (!post.videos || post.videos.length === 0) && !post.comparison">
    <div class="post-content px-1 mb-3">
      <!-- 1. 3D Futuristic & Translucent Stage Card (when post_style is active) -->
      <template x-if="getPostStageMeta(post)">
        <div class="fp-text-stage" :class="'fp-text-stage--' + (post.post_style || getPostStageMeta(post).slug)" data-testid="feed-text-stage-card">
          <div class="fp-text-stage__orb fp-text-stage__orb--a" aria-hidden="true"></div>
          <div class="fp-text-stage__orb fp-text-stage__orb--b" aria-hidden="true"></div>
          <div class="fp-text-stage__grid" aria-hidden="true"></div>

          <div class="fp-text-stage__header">
            <span class="fp-stage-badge">
              <template x-if="(post.post_style || getPostStageMeta(post).slug) === 'alert_pulse'">
                <span class="fp-stage-badge__pulse" aria-hidden="true"></span>
              </template>
              <span x-text="getPostStageMeta(post).badge"></span>
            </span>
          </div>

          <template x-if="getMediaCaption(post)">
            <div class="fp-text-stage__body"
                 :class="(getMediaCaption(post) || '').length <= 95 ? 'fp-text-stage__body--hero' : 'fp-text-stage__body--prose'"
                 x-html="renderHashtags(getMediaCaption(post), post.ar_emoji)"></div>
          </template>

          <!-- Embedded Glassmorphic Link Preview inside 3D Stage -->
          <template x-if="post.link_preview && (post.link_preview.image || post.link_preview.title) && !(post.postMessage || '').includes('/familyJournal/') && !post.link_preview.is_photo_link && !post.link_preview.is_google_photo && !post.link_preview.is_video">
            <div class="fp-text-stage__preview-wrapper">
              <a :href="post.link_preview.url"
                 target="_blank"
                 rel="noopener noreferrer"
                 class="fp-text-stage__preview-card">
                
                <!-- 16:9 Image Banner -->
                <template x-if="post.link_preview.image">
                  <div class="position-relative overflow-hidden w-100" style="aspect-ratio: 16 / 9; max-height: 280px; background-color: #020617;">
                    <img :src="post.link_preview.image"
                         class="w-100 h-100 object-fit-cover"
                         :alt="post.link_preview.title || 'Link preview image'"
                         loading="lazy"
                         onerror="this.style.display='none'">
                    
                    <div class="position-absolute top-0 start-0 m-2.5">
                      <span class="badge rounded-pill text-uppercase px-2.5 py-1.5 shadow-sm d-inline-flex align-items-center gap-1"
                            style="background: rgba(15, 23, 42, 0.88); backdrop-filter: blur(8px); font-size: 0.72rem; letter-spacing: 0.5px; border: 1px solid rgba(255, 255, 255, 0.22); color: #ffffff;">
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

                <!-- Embedded Preview Details -->
                <div class="p-3 text-start" style="color: #ffffff;">
                  <div class="text-uppercase fw-semibold small mb-1 d-flex align-items-center justify-content-between" style="font-size: 0.74rem; letter-spacing: 0.5px; color: rgba(255, 255, 255, 0.7);">
                    <span class="text-truncate" x-text="post.link_preview.domain || post.link_preview.site_name"></span>
                    <span class="small d-inline-flex align-items-center gap-1" style="color: #60a5fa;">
                      <span x-text="post.link_preview.is_party_event ? 'Celebration Pass' : 'Web Link'"></span>
                      <i class="bi bi-box-arrow-up-right" style="font-size: 0.7rem;"></i>
                    </span>
                  </div>

                  <h6 class="fw-bold mb-1.5" style="font-size: 1.02rem; line-height: 1.35; color: #ffffff;" x-text="post.link_preview.title"></h6>

                  <template x-if="post.link_preview.description">
                    <p class="small mb-2.5" style="line-height: 1.45; font-size: 0.85rem; color: rgba(226, 232, 240, 0.88); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" x-text="post.link_preview.description"></p>
                  </template>

                  <div class="pt-2 d-flex justify-content-between align-items-center" style="border-top: 1px solid rgba(255, 255, 255, 0.15);">
                    <span class="small text-truncate" style="font-size: 0.78rem; max-width: 60%; color: rgba(203, 213, 225, 0.75);" x-text="post.link_preview.url"></span>
                    <span class="btn btn-sm btn-light rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                          style="min-height: 38px; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0; color: #0f172a !important;">
                      <span x-text="post.link_preview.cta_label || 'Open Link →'"></span>
                    </span>
                  </div>
                </div>

              </a>
            </div>
          </template>

          <!-- Smart Contextual Family Tree or Family Chronicle Action Trigger inside 3D Stage -->
          <template x-if="hasFamilyTreeCall(post) || getChronicleLink(post)">
            <div class="fp-text-stage__footer d-flex flex-wrap gap-2 justify-content-center">
              <template x-if="getChronicleLink(post)">
                <a :href="getChronicleLink(post)" class="fp-text-stage__cta">
                  <i class="bi bi-book-half"></i>
                  <span>{{ __('feed.read_chronicle', 'Read Full Chronicle') }}</span>
                  <i class="bi bi-arrow-right-short" style="font-size: 1.25rem;"></i>
                </a>
              </template>
              <template x-if="hasFamilyTreeCall(post)">
                <a href="/organogram" class="fp-text-stage__cta">
                  <i class="bi bi-diagram-3-fill"></i>
                  <span>Add Your Branch to Family Tree</span>
                  <i class="bi bi-arrow-right-short" style="font-size: 1.25rem;"></i>
                </a>
              </template>
            </div>
          </template>
        </div>
      </template>

      <!-- 2. Standard Plain Text Post (when post_style is not set) -->
      <template x-if="!getPostStageMeta(post)">
        <div>
          <template x-if="getMediaCaption(post)">
            <p class="mb-0" style="white-space: pre-line; font-size: 1.05rem; color: var(--text-main); line-height: 1.55; font-family: var(--font-sans, -apple-system, system-ui, sans-serif);" x-html="renderHashtags(getMediaCaption(post), post.ar_emoji)"></p>
          </template>
          <template x-if="hasFamilyTreeCall(post)">
            <div class="mt-2.5">
              <a href="/organogram" class="btn btn-sm rounded-pill fw-bold d-inline-flex align-items-center gap-1.5 px-3 py-1.5 shadow-2xs" style="background: linear-gradient(135deg, #065f46 0%, #059669 100%); color: #ffffff !important; min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0; text-decoration: none;">
                <i class="bi bi-diagram-3-fill"></i>
                <span>Add Your Branch to Family Tree</span>
                <i class="bi bi-arrow-right-short"></i>
              </a>
            </div>
          </template>
        </div>
      </template>


      @include("components.feed.partials.stage-converter")

      <!-- Active Translation Status Pill (Only Visible Once User Requested Translation) -->
      <div class="mt-1.5 d-flex align-items-center gap-2" x-show="post._isTranslated || post._isTranslating" x-cloak>
        <span class="badge bg-light text-muted border d-inline-flex align-items-center gap-1.5 py-1 px-2" style="font-size: 0.78rem; font-weight: 500;">
          <i class="bi bi-translate text-primary"></i>
          <template x-if="post._isTranslating">
            <span class="d-inline-flex align-items-center gap-1">
              <span class="spinner-border spinner-border-sm text-primary" role="status" style="width: 11px; height: 11px; border-width: 1.5px;"></span>
              <span x-text="window.__ ? window.__('feed.translating', 'Translating...') : 'Translating...'"></span>
            </span>
          </template>
          <template x-if="post._isTranslated">
            <span class="d-inline-flex align-items-center gap-1">
              <span x-text="(window.__ ? window.__('feed.translated_label', 'Translated') : 'Translated') + (post._detectedSource ? ' (' + post._detectedSource.toUpperCase() + ')' : '')"></span>
              &bull;
              <button type="button" class="btn btn-sm btn-link p-0 text-decoration-underline text-primary fw-semibold" style="font-size: 0.78rem;" @click="window.toggleTranslatePost(post)">
                <span x-text="window.__ ? window.__('feed.see_original', 'See original') : 'See original'"></span>
              </button>
            </span>
          </template>
        </span>
      </div>
    </div>
  </template>

  <!-- Direct Chronicle Read CTA (When not already embedded in the 3D Stage Card) -->
  <template x-if="post.postMessage && post.postMessage.includes('/familyJournal/') && (!getPostStageMeta(post) || hasPollArena(post) || (post.images && post.images.length > 0))">
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

