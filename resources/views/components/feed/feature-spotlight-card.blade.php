{{-- Single-root Alpine x-if compatible Feature Spotlight Card --}}
<div x-data="featureSpotlightSlot({{ (int)($slotIndex ?? 0) }})"
     x-init="initSlot()"
     x-show="currentSpotlight !== null && !isHidden"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 transform translate-y-2"
     x-transition:enter-end="opacity-100 transform translate-y-0"
     class="fp-spotlight-card mb-4"
     :data-spotlight-id="currentSpotlight ? currentSpotlight.id : ''"
     role="region"
     aria-label="Feature Spotlight">

  <!-- Editorial Header -->
  <div class="fp-spotlight-header d-flex align-items-start justify-content-between gap-3"
       :class="currentSpotlight ? ('fp-spotlight-header--' + (currentSpotlight.theme || 'indigo-gold')) : 'fp-spotlight-header--indigo-gold'">
    <div class="fp-spotlight-glow" aria-hidden="true"></div>

    <div class="d-flex align-items-center gap-3 position-relative" style="min-width: 0;">
      <div class="fp-spotlight-medallion" aria-hidden="true">
        <i class="bi" :class="currentSpotlight ? currentSpotlight.icon : 'bi-stars'"></i>
      </div>
      <div style="min-width: 0;">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span class="fp-spotlight-kicker">
            <i class="bi bi-sparkles" aria-hidden="true"></i>
            <span x-text="currentSpotlight ? currentSpotlight.badge : 'Feature Spotlight'"></span>
          </span>
        </div>
        <p class="fp-spotlight-feature-name" x-text="currentSpotlight ? currentSpotlight.subtitle : ''"></p>
      </div>
    </div>

    <button type="button"
            class="fp-spotlight-dismiss-btn position-relative"
            @click="dismissCurrent()"
            aria-label="Dismiss this feature spotlight"
            title="Dismiss this spotlight">
      <i class="bi bi-x-lg" aria-hidden="true"></i>
    </button>
  </div>

  <!-- Editorial Body -->
  <div class="fp-spotlight-body">
    <h3 class="fp-spotlight-headline" x-text="currentSpotlight ? currentSpotlight.title : ''"></h3>
    <p class="fp-spotlight-story" x-text="currentSpotlight ? currentSpotlight.story : ''"></p>

    <!-- What You Can Do -->
    <div class="fp-spotlight-highlights" x-show="currentSpotlight && Array.isArray(currentSpotlight.highlights) && currentSpotlight.highlights.length > 0">
      <template x-for="(item, idx) in (currentSpotlight && currentSpotlight.highlights ? currentSpotlight.highlights : [])" :key="idx">
        <div class="fp-spotlight-highlight-item">
          <i class="bi bi-check-circle-fill fp-spotlight-highlight-check" aria-hidden="true"></i>
          <span x-text="item"></span>
        </div>
      </template>
    </div>

    <!-- Action Bar -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div class="d-flex align-items-center flex-wrap gap-2">
        <a :href="currentSpotlight ? currentSpotlight.cta_url : '/how-to-use'"
           @click="handleCtaClick($event)"
           class="fp-spotlight-cta"
           data-spin="true">
          <span x-text="currentSpotlight ? currentSpotlight.cta_label : 'Explore'"></span>
          <i class="bi bi-arrow-right" aria-hidden="true"></i>
        </a>

        <button type="button"
                class="fp-spotlight-secondary-btn"
                @click="nextSpotlight()"
                title="Show another FamilyPlatform feature idea">
          <i class="bi bi-shuffle" aria-hidden="true"></i>
          <span>Next Idea</span>
        </button>
      </div>

      <a href="/how-to-use"
         class="fp-spotlight-secondary-btn"
         title="Open the complete How to Use Guide">
        <i class="bi bi-book-half" aria-hidden="true"></i>
        <span>All Features</span>
      </a>
    </div>
  </div>
</div>
