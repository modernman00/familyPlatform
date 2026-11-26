{{-- @props(["post" => null]) --}}
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
             :data-post-bar="post.post_no"
             style="position:absolute; bottom:calc(100% + 8px); left:0; transform:none; display:flex; gap:6px; z-index:100; white-space:nowrap;">
          {{-- Perf: build the 6 glass-gem SVG buttons only once this post's picker has first been opened --}}
          <template x-for="type in ((post.post_no in activeReactionBars) ? ['love', 'like', 'haha', 'shock', 'sad', 'celebrate'] : [])" :key="type">
            <button type="button"
                    @click="reactToPost(post.post_no, type, $event)"
                    :title="emojiMap[type]?.label || type"
                    :aria-label="emojiMap[type]?.label || type"
                    :data-reaction-type="type"
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
                @click="onPostLikeClick(post.post_no, post.user_reaction || 'love', $event)"
                @touchstart="startPostLongPress(post.post_no, $event)"
                @touchend="endPostTouch(post.post_no, $event)"
                @touchmove="handlePostTouchMove(post.post_no, $event)"
                @touchcancel="cancelPostLongPress()"
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

      <!-- Comment Button (Heritage Acoustic Yarn Capsule) -->
      <button class="btn btn-action-glyph btn-action-comment rounded-circle"
              style="min-width: 44px; min-height: 44px;"
              type="button"
              title="Write or view family comments on this post"
              aria-label="Comment on this post"
              @click="toggleCommentForm(post.post_no)">
        <i class="bi bi-chat-quote-fill" style="font-size: 1.3rem; line-height: 1;"></i>
      </button>

      <!-- Share Button (The Swift Aerodynamic Glider - Author or Permitted Family Members) -->
      <template x-if="canSharePost(post)">
        <button class="btn btn-action-glyph btn-action-share rounded-circle"
                style="min-width: 44px; min-height: 44px;"
                type="button"
                :title="isOwnPost(post) ? 'Share with family relatives or via social apps' : ('Share ' + (post.fullName || 'this') + ' post (author will be notified via Web Push)')"
                aria-label="Share this memory"
                @click.stop="sharePost(post)">
          <i class="bi bi-send-fill" style="font-size: 1.15rem; line-height: 1; transform: rotate(-15deg); display: inline-block;"></i>
        </button>
      </template>
      <template x-if="!canSharePost(post)">
        <span class="d-inline-flex align-items-center justify-content-center text-muted rounded-circle"
              style="min-width: 44px; min-height: 44px; opacity: 0.55;"
              title="The author has turned off sharing for this post"
              aria-label="Sharing turned off by author">
          <i class="bi bi-lock-fill" style="font-size: 1.1rem; line-height: 1;"></i>
        </span>
      </template>

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
  <template x-if="(getMediaCaption(post) || post.ar_emoji) && ((post.images && post.images.length > 0) || post.video || (post.videos && post.videos.length > 0) || post.comparison)">
    <div class="post-caption-block px-1 mb-2 pt-1" style="font-size: 0.95rem; line-height: 1.5;">
      <a :href="'/member/profilePage?id=' + post.id" class="fw-bold text-decoration-none me-1.5" style="color: var(--text-main, #0f172a); font-weight: 600;" x-text="post.fullName"></a>
      <span class="post-caption-text" style="white-space: pre-line; color: var(--text-main);" x-html="renderHashtags(getMediaCaption(post), post.ar_emoji)"></span>

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
