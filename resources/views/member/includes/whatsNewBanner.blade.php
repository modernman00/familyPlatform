{{-- Dismissible "What's New This Week" Member Feed Banner --}}
<style nonce="{{ csp_nonce($nonce ?? null) }}">
  .fp-whats-new-card {
    border-radius: var(--radius-lg, 18px);
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
    overflow: hidden;
  }
  .fp-whats-new-badge {
    background-color: #a31621;
    color: #ffffff !important;
    font-size: var(--text-xs, 0.75rem);
    letter-spacing: 0.04em;
    font-weight: 700;
    text-transform: uppercase;
  }
  .fp-whats-new-title {
    color: #0f172a;
    font-size: var(--text-lg, 1.15rem);
    font-weight: 700;
    line-height: 1.35;
  }
  .fp-whats-new-text {
    color: #334155;
    font-size: var(--text-sm, 0.875rem);
    line-height: 1.5;
  }
  .fp-whats-new-item {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: var(--radius-md, 12px);
    padding: 12px 14px;
  }
  .fp-whats-new-item-title {
    color: #0f172a;
    font-size: var(--text-sm, 0.875rem);
    font-weight: 600;
  }
  .fp-whats-new-item-desc {
    color: #475569;
    font-size: var(--text-xs, 0.8125rem);
    line-height: 1.45;
  }
  .fp-whats-new-cta {
    background-color: #a31621;
    color: #ffffff !important;
    border: 1px solid #a31621;
    border-radius: 12px;
    min-height: 44px;
    padding: 10px 16px;
    font-size: var(--text-sm, 0.875rem);
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
    transition: transform 0.15s ease, filter 0.15s ease;
  }
  .fp-whats-new-cta:hover {
    filter: brightness(1.08);
    color: #ffffff !important;
  }
  .fp-whats-new-cta:active {
    transform: scale(0.98);
  }
  .fp-whats-new-cta:focus-visible,
  .fp-whats-new-secondary:focus-visible,
  .fp-whats-new-dismiss:focus-visible {
    outline: 2px solid #a31621;
    outline-offset: 2px;
  }
  .fp-whats-new-secondary {
    background-color: #f1f5f9;
    color: #0f172a !important;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    min-height: 44px;
    padding: 10px 14px;
    font-size: var(--text-sm, 0.875rem);
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
    transition: transform 0.15s ease, background-color 0.15s ease;
  }
  .fp-whats-new-secondary:hover {
    background-color: #e2e8f0;
  }
  .fp-whats-new-secondary:active {
    transform: scale(0.98);
  }
  .fp-whats-new-dismiss {
    background-color: transparent;
    color: #475569;
    border: 1px solid transparent;
    border-radius: 12px;
    min-width: 44px;
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: background-color 0.15s ease, transform 0.15s ease;
  }
  .fp-whats-new-dismiss:hover {
    background-color: #f1f5f9;
    color: #0f172a;
  }
  .fp-whats-new-dismiss:active {
    transform: scale(0.98);
  }

  /* Dark Mode Overrides (WCAG AA Contrast) */
  body.dark-mode .fp-whats-new-card,
  body.dark-theme .fp-whats-new-card,
  [data-bs-theme="dark"] .fp-whats-new-card,
  html.dark .fp-whats-new-card,
  html.dark-mode .fp-whats-new-card {
    background: #1e293b;
    border-color: rgba(148, 163, 184, 0.3);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
  }
  body.dark-mode .fp-whats-new-title,
  body.dark-theme .fp-whats-new-title,
  [data-bs-theme="dark"] .fp-whats-new-title,
  html.dark .fp-whats-new-title,
  html.dark-mode .fp-whats-new-title {
    color: #f8fafc;
  }
  body.dark-mode .fp-whats-new-text,
  body.dark-theme .fp-whats-new-text,
  [data-bs-theme="dark"] .fp-whats-new-text,
  html.dark .fp-whats-new-text,
  html.dark-mode .fp-whats-new-text {
    color: #cbd5e1;
  }
  body.dark-mode .fp-whats-new-item,
  body.dark-theme .fp-whats-new-item,
  [data-bs-theme="dark"] .fp-whats-new-item,
  html.dark .fp-whats-new-item,
  html.dark-mode .fp-whats-new-item {
    background-color: #0f172a;
    border-color: rgba(148, 163, 184, 0.25);
  }
  body.dark-mode .fp-whats-new-item-title,
  body.dark-theme .fp-whats-new-item-title,
  [data-bs-theme="dark"] .fp-whats-new-item-title,
  html.dark .fp-whats-new-item-title,
  html.dark-mode .fp-whats-new-item-title {
    color: #f8fafc;
  }
  body.dark-mode .fp-whats-new-item-desc,
  body.dark-theme .fp-whats-new-item-desc,
  [data-bs-theme="dark"] .fp-whats-new-item-desc,
  html.dark .fp-whats-new-item-desc,
  html.dark-mode .fp-whats-new-item-desc {
    color: #cbd5e1;
  }
  body.dark-mode .fp-whats-new-secondary,
  body.dark-theme .fp-whats-new-secondary,
  [data-bs-theme="dark"] .fp-whats-new-secondary,
  html.dark .fp-whats-new-secondary,
  html.dark-mode .fp-whats-new-secondary {
    background-color: #334155;
    color: #f8fafc !important;
    border-color: #475569;
  }
  body.dark-mode .fp-whats-new-secondary:hover,
  body.dark-theme .fp-whats-new-secondary:hover,
  [data-bs-theme="dark"] .fp-whats-new-secondary:hover,
  html.dark .fp-whats-new-secondary:hover,
  html.dark-mode .fp-whats-new-secondary:hover {
    background-color: #475569;
  }
  body.dark-mode .fp-whats-new-dismiss,
  body.dark-theme .fp-whats-new-dismiss,
  [data-bs-theme="dark"] .fp-whats-new-dismiss,
  html.dark .fp-whats-new-dismiss,
  html.dark-mode .fp-whats-new-dismiss {
    color: #cbd5e1;
  }
  body.dark-mode .fp-whats-new-dismiss:hover,
  body.dark-theme .fp-whats-new-dismiss:hover,
  [data-bs-theme="dark"] .fp-whats-new-dismiss:hover,
  html.dark .fp-whats-new-dismiss:hover,
  html.dark-mode .fp-whats-new-dismiss:hover {
    background-color: #334155;
    color: #ffffff;
  }
</style>

<section
  id="whatsNewFeedBanner"
  class="card mb-4 border-0 fp-whats-new-card"
  aria-label="What is new this week on FamilyPlatform"
  x-data="{ dismissed: $persist(false).as('fp_whats_new_2026_11_w3_dismissed'), expanded: false }"
  x-show="!dismissed"
  x-transition.opacity.duration.250ms
  x-cloak
>
  <div class="card-body p-4">
    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
      <div class="d-flex flex-column gap-1">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span class="badge rounded-pill px-2 py-1 fp-whats-new-badge">
            What’s New
          </span>
        </div>
        <h2 class="mb-0 fp-whats-new-title">
          Fresh updates for your family circle this week
        </h2>
      </div>

      <button
        type="button"
        class="fp-whats-new-dismiss"
        @click="dismissed = true"
        aria-label="Dismiss What’s New banner"
        title="Dismiss"
      >
        <i class="bi bi-x-lg" aria-hidden="true"></i>
      </button>
    </div>

    <p class="mb-3 fp-whats-new-text">
      Organise Christmas &amp; birthday gift ideas without duplicates, call relatives in FamilyChat, and enjoy smoother video playback across the feed.
    </p>

    <div class="d-flex align-items-center gap-2 flex-wrap">
      <a href="/wishlists" class="btn fp-whats-new-cta d-inline-flex align-items-center gap-2 text-decoration-none">
        <i class="bi bi-gift-fill" aria-hidden="true"></i>
        <span>Open Gift Wishlists</span>
      </a>

      <button
        type="button"
        class="btn fp-whats-new-secondary d-inline-flex align-items-center gap-2"
        @click="expanded = !expanded"
        :aria-expanded="expanded.toString()"
      >
        <span x-text="expanded ? 'Hide details' : 'See all 4 updates'">See all 4 updates</span>
        <i class="bi" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'" aria-hidden="true"></i>
      </button>

      <a href="/playbook" class="btn fp-whats-new-secondary d-inline-flex align-items-center gap-2 text-decoration-none">
        <i class="bi bi-book" aria-hidden="true"></i>
        <span>Playbook</span>
      </a>
    </div>

    <div x-show="expanded" x-collapse x-cloak class="mt-3">
      <div class="row g-2">
        <div class="col-12 col-md-6">
          <div class="fp-whats-new-item h-100">
            <div class="fp-whats-new-item-title mb-1">
              <i class="bi bi-gift-fill me-1" style="color: #d97706;" aria-hidden="true"></i>
              Family Gift Wishlists
            </div>
            <div class="fp-whats-new-item-desc">
              Pin Christmas &amp; birthday gift ideas for chosen days and let relatives claim items quietly so nobody buys duplicates.
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6">
          <div class="fp-whats-new-item h-100">
            <div class="fp-whats-new-item-title mb-1">
              <i class="bi bi-chat-heart-fill me-1" style="color: #a31621;" aria-hidden="true"></i>
              FamilyChat &amp; Voice/Video Calls
            </div>
            <div class="fp-whats-new-item-desc">
              Encrypted messaging is now FamilyChat, complete with built-in voice and video calling across your family circle.
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6">
          <div class="fp-whats-new-item h-100">
            <div class="fp-whats-new-item-title mb-1">
              <i class="bi bi-phone-fill me-1" style="color: #2563eb;" aria-hidden="true"></i>
              Phone Share &amp; Smoother Videos
            </div>
            <div class="fp-whats-new-item-desc">
              Share photos and links straight from your phone’s share menu, plus smoother autoplay and direct Google Photos video playback.
            </div>
          </div>
        </div>

        <div class="col-12 col-md-6">
          <div class="fp-whats-new-item h-100">
            <div class="fp-whats-new-item-title mb-1">
              <i class="bi bi-film me-1" style="color: #059669;" aria-hidden="true"></i>
              Family Studio &amp; Video Spotlights
            </div>
            <div class="fp-whats-new-item-desc">
              Edit and sync clips effortlessly in the upgraded Family Video Studio, and enjoy curated family-safe video spotlights in your feed.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
