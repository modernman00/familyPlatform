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
      <!-- 1. Scenic Backdrops & 3D Futuristic Translucent Stage Card (when post_style is active) -->
      <template x-if="getPostStageMeta(post)">
        <div class="fp-text-stage"
             :class="'fp-text-stage--' + (post.post_style || getPostStageMeta(post).slug) + (getPostStageMeta(post).category === 'scenic' ? ' fp-text-stage--scenic' : '')"
             data-testid="feed-text-stage-card">
          <div class="fp-text-stage__orb fp-text-stage__orb--a" aria-hidden="true"></div>
          <div class="fp-text-stage__orb fp-text-stage__orb--b" aria-hidden="true"></div>
          <template x-if="getPostStageMeta(post).category === 'scenic'">
            <div class="fp-text-stage__bokeh" aria-hidden="true"></div>
          </template>
          <template x-if="(post.post_style || getPostStageMeta(post).slug) === 'solar_galaxy'">
            <div class="fp-cosmos-orrery" aria-hidden="true">
              <div class="fp-cosmos-galaxy fp-cosmos-galaxy--primary"></div>
              <div class="fp-cosmos-galaxy fp-cosmos-galaxy--distant"></div>
              <div class="fp-cosmos-comet"></div>
              <div class="fp-cosmos-system">
                <div class="fp-cosmos-sun"></div>
                <div class="fp-cosmos-orbit fp-cosmos-orbit--1"><div class="fp-cosmos-planet fp-cosmos-planet--mercury"></div></div>
                <div class="fp-cosmos-orbit fp-cosmos-orbit--2"><div class="fp-cosmos-planet fp-cosmos-planet--venus"></div></div>
                <div class="fp-cosmos-orbit fp-cosmos-orbit--3"><div class="fp-cosmos-planet fp-cosmos-planet--earth"><div class="fp-cosmos-moon-orbit"><div class="fp-cosmos-moon"></div></div></div></div>
                <div class="fp-cosmos-orbit fp-cosmos-orbit--4"><div class="fp-cosmos-planet fp-cosmos-planet--mars"></div></div>
                <div class="fp-cosmos-orbit fp-cosmos-orbit--5"><div class="fp-cosmos-planet fp-cosmos-planet--saturn"><div class="fp-cosmos-saturn-ring"></div></div></div>
                <div class="fp-cosmos-orbit fp-cosmos-orbit--6"><div class="fp-cosmos-planet fp-cosmos-planet--neptune"></div></div>
              </div>
            </div>
          </template>
          <template x-if="getPostStageMeta(post).category !== 'scenic'">
            <div class="fp-text-stage__grid" aria-hidden="true"></div>
          </template>

          <template x-if="getPostStageMeta(post).badge">
            <div class="fp-text-stage__header">
              <span class="fp-stage-badge">
                <template x-if="(post.post_style || getPostStageMeta(post).slug) === 'alert_pulse'">
                  <span class="fp-stage-badge__pulse" aria-hidden="true"></span>
                </template>
                <span x-text="getPostStageMeta(post).badge"></span>
              </span>
            </div>
          </template>

          <template x-if="getMediaCaption(post)">
            <div class="fp-text-stage__body"
                 :class="(getMediaCaption(post) || '').length <= 120 ? 'fp-text-stage__body--hero' : ((getMediaCaption(post) || '').length <= 260 ? 'fp-text-stage__body--prose' : 'fp-text-stage__body--extended')"
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
                    <span class="small text-truncate" style="font-size: 0.78rem; max-width: 60%; color: rgba(203, 213, 225, 0.75);" x-text="post.link_preview.site_name || post.link_preview.domain || 'Web Link'"></span>
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
          <!-- 2a. Bespoke Gift Wishlist Feed Announcement Card (auto-parses 🎁 New ... Gift Wishlist posts) -->
          <template x-if="(post.postMessage || '').includes('Gift Wishlist:') && (post.postMessage || '').includes('/wishlists')">
            <div class="fp-feed-wishlist-card rounded-4 overflow-hidden border shadow-xs my-1"
                 style="border-color: rgba(245, 158, 11, 0.45) !important; background-color: var(--card-bg, #ffffff);"
                 x-data="{
                   get parsed() {
                     const raw = String(post.postMessage || '');
                     const lines = raw.split(/\r?\n/).map(l => l.trim()).filter(Boolean);
                     let occasion = 'Christmas';
                     let title = 'Family Gift Wishlist';
                     let pinDays = '14 days';
                     let targetUrl = '/wishlists';
                     const items = [];

                     lines.forEach(line => {
                       const headerMatch = line.match(/New\s+(.+?)\s+Gift\s+Wishlist:\s*(.+)$/i);
                       if (headerMatch) {
                         occasion = headerMatch[1].trim();
                         title = headerMatch[2].trim();
                         return;
                       }
                       if (line.startsWith('•') || line.startsWith('*') || line.startsWith('-')) {
                         const clean = line.replace(/^[•*\-]\s*/, '').trim();
                         const priceMatch = clean.match(/^(.*?)\s*\(([£$€₦]|CAD|AUD|GBP|USD|EUR|NGN)([\d,.]+)\)$/);
                         if (priceMatch) {
                           items.push({ name: priceMatch[1].trim(), price: priceMatch[2] + priceMatch[3], isMore: false });
                         } else {
                           items.push({ name: clean, price: '', isMore: clean.startsWith('…and ') || clean.startsWith('...and ') });
                         }
                         return;
                       }
                       const pinMatch = line.match(/Pinned\s+for\s+(\d+\s+days?)/i);
                       if (pinMatch) {
                         pinDays = pinMatch[1];
                       }
                       const urlMatch = line.match(/(\/wishlists(?:#wishlist-\d+)?)/i);
                       if (urlMatch) {
                         targetUrl = urlMatch[1];
                       }
                     });
                     return { occasion, title, pinDays, targetUrl, items };
                   }
                 }">
              <!-- Header Ribbon -->
              <div class="p-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom"
                   style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.14) 0%, rgba(163, 22, 33, 0.07) 100%); border-color: rgba(245, 158, 11, 0.25) !important;">
                <div class="d-flex align-items-center gap-2.5 min-w-0">
                  <div class="rounded-3 d-flex align-items-center justify-content-center shadow-2xs flex-shrink-0"
                       style="width: 44px; height: 44px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #0f172a; font-size: 1.3rem;">
                    🎁
                  </div>
                  <div class="min-w-0">
                    <div class="d-flex align-items-center gap-1.5 flex-wrap mb-1">
                      <span class="badge rounded-pill"
                            style="background-color: #fbbf24 !important; color: #0f172a !important; font-weight: 700; font-size: 0.74rem;"
                            x-text="'🎄 ' + parsed.occasion + ' Wishlist'"></span>
                      <span class="badge rounded-pill border"
                            style="background-color: var(--card-bg, #ffffff) !important; color: var(--text-main, #0f172a) !important; border-color: rgba(148, 163, 184, 0.4) !important; font-weight: 600; font-size: 0.73rem;"
                            x-text="'📌 Pinned for ' + parsed.pinDays"></span>
                    </div>
                    <h4 class="fw-bold mb-0 text-truncate"
                        style="font-size: 1.08rem; color: var(--text-main, #0f172a); letter-spacing: -0.01em;"
                        :title="parsed.title"
                        x-text="parsed.title"></h4>
                  </div>
                </div>
                <span class="badge rounded-pill px-2.5 py-1.5 fp-fw-badge-emerald"
                      style="font-size: 0.75rem;"
                      x-text="parsed.items.filter(i => !i.isMore).length + (parsed.items.filter(i => !i.isMore).length === 1 ? ' Gift Idea' : ' Gift Ideas')"></span>
              </div>

              <!-- Structured Gift Items List -->
              <div class="p-3 d-grid gap-2">
                <template x-for="(it, idx) in parsed.items" :key="idx">
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2.5 px-3 rounded-3 border"
                       style="background-color: var(--surface-alt, rgba(148, 163, 184, 0.06)); border-color: var(--border-color, #e2e8f0) !important;">
                    <div class="d-flex align-items-center gap-2.5 min-w-0">
                      <span class="rounded-2 d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0 fp-fw-index-pill"
                            style="width: 28px; height: 28px; font-size: 0.78rem;"
                            x-text="it.isMore ? '+' : (idx + 1)"></span>
                      <span class="fw-semibold text-truncate"
                            style="color: var(--text-main, #0f172a); font-size: 0.92rem;"
                            :title="it.name"
                            x-text="it.name"></span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                      <template x-if="it.price">
                        <span class="badge rounded-pill px-2.5 py-1 fp-fw-badge-emerald"
                              style="font-weight: 800; font-size: 0.78rem;"
                              x-text="it.price"></span>
                      </template>
                      <template x-if="!it.isMore">
                        <a :href="parsed.targetUrl"
                           class="btn btn-sm rounded-pill fw-bold d-inline-flex align-items-center gap-1 px-3"
                           style="min-height: 44px; min-width: 44px; background-color: #fbbf24; color: #0f172a !important; font-size: 0.78rem; white-space: nowrap; flex-shrink: 0; text-decoration: none;">
                          <i class="bi bi-gift-fill" aria-hidden="true"></i>
                          <span>Claim</span>
                        </a>
                      </template>
                    </div>
                  </div>
                </template>

                <!-- Action Footer (No raw /wishlists#wishlist-1 URL text) -->
                <div class="pt-2 mt-1 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top"
                     style="border-color: var(--border-color, #e2e8f0) !important;">
                  <span class="small text-muted" style="font-size: 0.8rem;">
                    <i class="bi bi-shield-check text-success me-1" aria-hidden="true"></i>Claim with your name or secretly so relatives avoid duplicate gifts.
                  </span>
                  <a :href="parsed.targetUrl"
                     class="btn btn-sm text-white rounded-pill fw-bold d-inline-flex align-items-center gap-1.5 px-3 py-2 shadow-2xs"
                     style="background: #a31621; min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0; text-decoration: none;">
                    <i class="bi bi-gift" aria-hidden="true"></i>
                    <span>Open Wishlist &amp; Claim Gifts</span>
                    <i class="bi bi-arrow-right-short" style="font-size: 1.15rem;" aria-hidden="true"></i>
                  </a>
                </div>
              </div>
            </div>
          </template>

          <!-- 2b. Regular Text Post -->
          <template x-if="!((post.postMessage || '').includes('Gift Wishlist:') && (post.postMessage || '').includes('/wishlists'))">
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

