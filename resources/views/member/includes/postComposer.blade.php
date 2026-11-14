@php
  $composerId = $composerId ?? 'openPostModalTrigger';
  $avatarSrc = str_starts_with($data['img'] ?? '', '/')
    ? $data['img']
    : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png');
@endphp

<div class="card post-composer post-composer--hero mb-3 mb-lg-4 border-0" id="{{ $composerId }}">
  <div class="post-composer__accent" aria-hidden="true"></div>
  <div class="card-body p-3 p-sm-4">
    <!-- Header Row: Clear Title + Always-Visible Primary CTA -->
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
      <div class="d-flex align-items-center gap-2 min-w-0">
        <span class="post-composer__badge flex-shrink-0" aria-hidden="true">
          <i class="bi bi-chat-heart-fill"></i>
        </span>
        <div class="min-w-0">
          <div class="post-composer__title">{{ __('feed.share_moment_heading', 'Share a Moment') }}</div>
          <div class="post-composer__subtitle d-none d-sm-block">{{ __('feed.share_moment_subheading', 'Photos, milestones, stories & polls for your family') }}</div>
        </div>
      </div>

      <button type="button"
              class="btn btn-primary post-composer__cta rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-2 flex-shrink-0"
              data-bs-toggle="modal"
              data-bs-target="#postModal"
              title="Create Post - Share stories, photos, and milestones with family"
              aria-label="{{ __('feed.post_btn', 'Post') }}">
        <i class="bi bi-plus-lg"></i>
        <span>{{ __('feed.post_btn', 'Post') }}</span>
      </button>
    </div>

    <!-- Prompt Row: Avatar + Unclipped Interactive Prompt Pill -->
    <div class="d-flex align-items-center gap-2 gap-sm-3">
      <img src="{{ $avatarSrc }}"
           alt="{{ e(($data['firstName'] ?? 'Member') . ' profile photo') }}"
           class="rounded-circle flex-shrink-0 composer-avatar-img"
           width="44"
           height="44">

      <div role="button"
           tabindex="0"
           class="composer-prompt composer-prompt--hero flex-grow-1 d-flex align-items-center justify-content-between gap-2 px-3 py-2"
           data-bs-toggle="modal"
           data-bs-target="#postModal"
           onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); this.click(); }"
           aria-label="{{ __('feed.share_prompt', 'Share a moment with your family...') }}">
        <span class="composer-prompt__text">{{ __('feed.share_prompt', 'Share a moment with your family...') }}</span>
        <i class="bi bi-pencil-square composer-prompt__icon flex-shrink-0" aria-hidden="true"></i>
      </div>
    </div>

    <!-- Quick-Action Pills: Labeled, >=44px Tap Targets, Scroll-Safe on Mobile -->
    <div class="composer-quick-actions d-flex align-items-center gap-2 mt-3 pt-3">
      <button type="button"
              class="btn composer-pill-btn composer-pill-btn--photo"
              data-bs-toggle="modal"
              data-bs-target="#postModal"
              title="Upload Photos - Add memories from your device or camera"
              aria-label="{{ __('feed.photo', 'Add a photo to post') }}">
        <i class="bi bi-camera-fill" aria-hidden="true"></i>
        <span>{{ __('feed.photo_label', 'Photo') }}</span>
      </button>

      <button type="button"
              class="btn composer-pill-btn composer-pill-btn--video"
              data-bs-toggle="modal"
              data-bs-target="#postModal"
              onclick="setTimeout(function () { var vb = document.getElementById('addVideoBtn'); var vc = document.getElementById('videoEmbedContainer'); if (vc && vc.classList.contains('d-none') && vb) vb.click(); }, 120)"
              title="Upload Video - Embed or share family video clips"
              aria-label="{{ __('feed.video', 'Add a video to post') }}">
        <i class="bi bi-play-btn-fill" aria-hidden="true"></i>
        <span>{{ __('feed.video_label', 'Video') }}</span>
      </button>

      <button type="button"
              class="btn composer-pill-btn composer-pill-btn--thennow"
              data-bs-toggle="modal"
              data-bs-target="#postModal"
              onclick="setTimeout(function () { if (window.__openThenNow) window.__openThenNow(''); }, 0)"
              title="Then &amp; Now - Compare milestone photos across years"
              aria-label="{{ __('feed.then_now', 'Then & Now photo comparison') }}">
        <i class="bi bi-hourglass-split" aria-hidden="true"></i>
        <span>{{ __('feed.then_now_label', 'Then & Now') }}</span>
      </button>

      <button type="button"
              class="btn composer-pill-btn composer-pill-btn--poll"
              data-bs-toggle="modal"
              data-bs-target="#postModal"
              title="Family Poll - Gather opinions and vote on dates or plans"
              aria-label="{{ __('feed.poll', 'Create a poll') }}">
        <i class="bi bi-bar-chart-fill" aria-hidden="true"></i>
        <span>{{ __('feed.poll_label', 'Poll') }}</span>
      </button>

      <button type="button"
              class="btn composer-pill-btn composer-pill-btn--announce"
              data-bs-toggle="modal"
              data-bs-target="#createAnnouncementModal"
              title="Family Announcement - Broadcast pinned notices to all relatives"
              aria-label="{{ __('feed.announcement', 'Broadcast family announcement') }}">
        <i class="bi bi-megaphone-fill" aria-hidden="true"></i>
        <span>{{ __('feed.announce_label', 'Announce') }}</span>
      </button>

      <button type="button"
              class="btn composer-pill-btn composer-pill-btn--journal"
              onclick="window.location.href='/familyJournal/create'"
              title="Family Journal - Write memoirs, stories, and chronicles"
              aria-label="{{ __('feed.journal', 'Write family journal chronicle') }}">
        <i class="bi bi-journal-bookmark-fill" aria-hidden="true"></i>
        <span>{{ __('feed.journal_label', 'Journal') }}</span>
      </button>
    </div>
  </div>
</div>
