{{-- @props(["post" => null]) --}}
<style>
  .link-preview-card {
    --lp-bg: #f8fafc;
    --lp-border: #e2e8f0;
    --lp-title: #0f172a;
    --lp-desc: #475569;
    --lp-meta: #475569;
    --lp-url: #64748b;
    --lp-accent: #2563eb;
    --lp-divider: rgba(15, 23, 42, 0.08);
    --lp-icon-bg: rgba(15, 23, 42, 0.05);
    --lp-icon-color: #334155;
    border-radius: 14px;
    background-color: var(--lp-bg) !important;
    border: 1px solid var(--lp-border) !important;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    display: block;
    color: var(--lp-title);
  }
  .link-preview-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08) !important;
  }
  .link-preview-card:active {
    transform: scale(0.99);
  }
  .link-preview-card__body {
    background-color: var(--lp-bg) !important;
    color: var(--lp-title);
  }
  .link-preview-card__eyebrow {
    color: var(--lp-meta) !important;
    font-size: 0.74rem;
    letter-spacing: 0.5px;
  }
  .link-preview-card__type {
    color: var(--lp-accent) !important;
    white-space: nowrap;
    flex-shrink: 0;
  }
  .link-preview-card__title {
    color: var(--lp-title) !important;
    font-size: 1.02rem;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
  }
  .link-preview-card__desc {
    color: var(--lp-desc) !important;
    font-size: 0.86rem;
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .link-preview-card__footer {
    border-top: 1px solid var(--lp-divider) !important;
    gap: 8px;
  }
  .link-preview-card__url {
    color: var(--lp-url) !important;
    font-size: 0.78rem;
  }
  .link-preview-card__icon-tile {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background-color: var(--lp-icon-bg);
    color: var(--lp-icon-color);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
  }
  .link-preview-card__cta {
    background: linear-gradient(135deg, #a31621 0%, #8a1019 100%) !important;
    color: #ffffff !important;
    border: none !important;
    min-height: 44px;
    min-width: 44px;
    font-size: 0.82rem;
    white-space: nowrap;
    flex-shrink: 0;
  }
  [data-bs-theme="dark"] .link-preview-card,
  body.dark-mode .link-preview-card,
  body.dark-theme .link-preview-card,
  html.dark .link-preview-card,
  html.dark-mode .link-preview-card {
    --lp-bg: #162032;
    --lp-border: rgba(148, 163, 184, 0.24);
    --lp-title: #f8fafc;
    --lp-desc: #cbd5e1;
    --lp-meta: #94a3b8;
    --lp-url: #94a3b8;
    --lp-accent: #60a5fa;
    --lp-divider: rgba(148, 163, 184, 0.16);
    --lp-icon-bg: rgba(255, 255, 255, 0.08);
    --lp-icon-color: #e2e8f0;
  }
  [data-bs-theme="dark"] .link-preview-card:hover,
  body.dark-mode .link-preview-card:hover,
  body.dark-theme .link-preview-card:hover,
  html.dark .link-preview-card:hover,
  html.dark-mode .link-preview-card:hover {
    border-color: rgba(148, 163, 184, 0.42) !important;
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.35) !important;
  }
</style>
  <!-- Full Graphics OpenGraph Social Card (16:9) (Suppressed for Linked Native Photos & Videos & 3D Stage Posts) -->
  <template x-if="post.link_preview && (post.link_preview.image || post.link_preview.title || post.link_preview.url) && !(post.postMessage || '').includes('/familyJournal/') && !post.link_preview.is_photo_link && !post.link_preview.is_google_photo && !post.link_preview.is_video && (!post.images || post.images.length === 0) && (!post.videos || post.videos.length === 0) && !post.video && !getPostStageMeta(post)">
    <div class="link-preview-container mb-3 px-1" x-data="{ imgFailed: false }">
      <a :href="post.link_preview.url"
         target="_blank"
         rel="noopener noreferrer"
         class="card text-decoration-none overflow-hidden link-preview-card shadow-sm">

        <!-- Full 16:9 Graphics Banner (Hidden cleanly if image is absent or fails to load) -->
        <template x-if="post.link_preview.image && !imgFailed">
          <div class="position-relative overflow-hidden w-100" style="aspect-ratio: 16 / 9; background-color: #0f172a; max-height: 420px;">
            <img :src="post.link_preview.image"
                 class="w-100 h-100 object-fit-cover"
                 :alt="post.link_preview.title || 'Link preview image'"
                 loading="lazy"
                 x-on:error="imgFailed = true">

            <!-- Platform Badge Pill -->
            <div class="position-absolute top-0 start-0 m-2.5">
              <span class="badge rounded-pill text-uppercase px-2.5 py-1.5 shadow-sm d-inline-flex align-items-center gap-1"
                    style="background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); font-size: 0.72rem; letter-spacing: 0.5px; border: 1px solid rgba(255, 255, 255, 0.18); color: #ffffff;">
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
        <div class="p-3 link-preview-card__body">
          <div class="text-uppercase fw-semibold small mb-1.5 d-flex align-items-center justify-content-between gap-2 link-preview-card__eyebrow">
            <span class="text-truncate" x-text="post.link_preview.site_name || post.link_preview.domain || 'Web Link'"></span>
            <span class="small d-inline-flex align-items-center gap-1 link-preview-card__type">
              <span x-text="post.link_preview.is_party_event ? 'Celebration Pass' : 'Web Link'"></span>
              <i class="bi bi-box-arrow-up-right" style="font-size: 0.7rem;" aria-hidden="true"></i>
            </span>
          </div>

          <div class="d-flex align-items-start gap-2.5 mb-2">
            <!-- Branded Platform Icon Tile when no preview image is available -->
            <template x-if="!post.link_preview.image || imgFailed">
              <div class="link-preview-card__icon-tile" aria-hidden="true">
                <i class="bi"
                   :class="(() => {
                     const d = String(post.link_preview.domain || post.link_preview.url || '').toLowerCase();
                     if (d.includes('facebook.com') || d.includes('fb.watch') || d.includes('fb.com')) return 'bi-facebook text-primary';
                     if (d.includes('youtube.com') || d.includes('youtu.be')) return 'bi-youtube text-danger';
                     if (d.includes('instagram.com') || d.includes('instagr.am')) return 'bi-instagram text-danger';
                     if (d.includes('tiktok.com')) return 'bi-tiktok';
                     if (d.includes('twitter.com') || d.includes('x.com')) return 'bi-twitter-x';
                     if (d.includes('linkedin.com') || d.includes('lnkd.in')) return 'bi-linkedin text-primary';
                     if (post.link_preview.is_party_event) return 'bi-balloon-fill text-warning';
                     return 'bi-globe2';
                   })()"></i>
              </div>
            </template>

            <div class="min-w-0 flex-grow-1">
              <h6 class="fw-bold mb-1 link-preview-card__title"
                  :title="post.link_preview.title"
                  x-text="post.link_preview.title || post.link_preview.domain || 'External Link'"></h6>

              <template x-if="post.link_preview.description">
                <p class="small mb-0 link-preview-card__desc" x-text="post.link_preview.description"></p>
              </template>
            </div>
          </div>

          <div class="pt-2 d-flex justify-content-between align-items-center link-preview-card__footer">
            <span class="small text-truncate link-preview-card__url"
                  style="max-width: 58%;"
                  :title="post.link_preview.url"
                  x-text="post.link_preview.url"></span>
            <span class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm link-preview-card__cta">
              <span x-text="post.link_preview.cta_label || 'Open Link →'"></span>
            </span>
          </div>
        </div>

      </a>
    </div>
  </template>
