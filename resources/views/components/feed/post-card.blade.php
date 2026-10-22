{{-- Scoped Feed Post Card Component --}}
<div class="card border-0 shadow-sm mb-4 p-3" :id="'post' + post.post_no" :class="'post' + post.post_no" style="border-radius: 8px; background-color: var(--card-bg);">
  <!-- Post Author Header -->
  <div class="d-flex align-items-center mb-3">
    <a :href="'/profilepage/img?dir=img&pics=' + ((post.images && post.images[0]) ? post.images[0] : '') + '&pID=' + post.post_no + '&path=profile&id=' + post.id">
      <img :src="post.profileImg" alt="img" class="rounded-circle me-3" style="width:40px; height:40px; object-fit: cover;">
    </a>
    <div class="flex-grow-1">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div class="d-flex align-items-center flex-wrap">
            <h6 class="mb-0 fw-semibold text-dark" style="font-size: 1.05rem;" x-text="post.fullName"></h6>
            <template x-if="post.post_tags && post.post_tags.length > 0">
              <span class="text-secondary small fw-normal ms-1">
                is with 
                <template x-for="(tag, tIdx) in post.post_tags.slice(0, 2)" :key="tag.user_id">
                  <span>
                    <a :href="'/member/profilePage?id=' + tag.user_id" class="fw-semibold text-dark text-decoration-none" style="text-decoration: underline;" x-text="tag.fullName"></a><span x-show="tIdx === 0 && post.post_tags.length === 2"> and </span><span x-show="tIdx === 0 && post.post_tags.length > 2">, </span>
                  </span>
                </template>
                <template x-if="post.post_tags.length > 2">
                  <span class="dropdown d-inline">
                    <a href="#" class="fw-semibold text-primary text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" x-text="(post.post_tags.length - 2) + ' others'"></a>
                    <ul class="dropdown-menu shadow-sm border-0 p-2" style="border-radius: 12px; min-width: 180px;">
                      <template x-for="tag in post.post_tags.slice(2)" :key="tag.user_id">
                        <li>
                          <a :href="'/member/profilePage?id=' + tag.user_id" class="dropdown-item d-flex align-items-center gap-2 py-1.5 px-2 rounded-2">
                            <img :src="tag.profileImg || '/resources/images/profile/avatarM.png'" class="rounded-circle" width="24" height="24" style="object-fit: cover;">
                            <span class="small fw-medium" x-text="tag.fullName"></span>
                          </a>
                        </li>
                      </template>
                    </ul>
                  </span>
                </template>
              </span>
            </template>
          </div>
          <div class="d-flex align-items-center gap-2 mt-0.5">
            <small class="text-muted" style="font-size: 0.85rem;" x-text="formatDate(post.post_time || post.date_created)"></small>
            <template x-if="post.postFamCode">
              <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill" style="font-size: 0.78rem; font-weight: normal; padding: 2px 8px;" x-text="'Family: ' + post.postFamCode"></span>
            </template>
          </div>
        </div>
        <template x-if="isOwnPost(post) || isUserTaggedInPost(post)">
          <div class="dropdown">
            <button class="btn btn-sm btn-link text-muted p-1 rounded-circle" type="button" :id="'kebab' + post.post_no" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;" title="Post options">
              <i class="bi bi-three-dots" style="font-size: 1.25rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" :aria-labelledby="'kebab' + post.post_no" style="border-radius: 12px; font-size: 0.9rem; min-width: 160px;">
              <template x-if="isOwnPost(post)">
                <div>
                  <li>
                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="#" @click.prevent="editPost(post)">
                      <i class="bi bi-pencil-square text-primary"></i> Edit Post
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger" href="#" @click.prevent="deletePost(post.post_no)">
                      <i class="bi bi-trash3"></i> Delete Post
                    </a>
                  </li>
                </div>
              </template>
              <template x-if="isUserTaggedInPost(post)">
                <li>
                  <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-warning" href="#" @click.prevent="untagSelfFromPost(post)">
                    <i class="bi bi-tag-fill"></i> Remove Tag from Post
                  </a>
                </li>
              </template>
            </ul>
          </div>
        </template>
      </div>
    </div>
  </div>

  <!-- Weekly Family Trivia Badge -->
  <template x-if="post.postMessage && post.postMessage.includes('Weekly Family Trivia')">
    <div class="px-1 mb-2">
      <span class="badge rounded-pill px-3 py-1.5 text-white shadow-sm d-inline-flex align-items-center gap-1" style="background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.5px;">
        <i class="bi bi-trophy-fill"></i> WEEKLY FAMILY TRIVIA
      </span>
    </div>
  </template>

  <!-- Family Chronicle / Journal Badge -->
  <template x-if="post.postMessage && (post.postMessage.includes('Family Chronicle') || post.postMessage.includes('/familyJournal/'))">
    <div class="px-1 mb-2">
      <span class="badge rounded-pill px-3 py-1.5 text-white shadow-sm d-inline-flex align-items-center gap-1.5" style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.5px;">
        <i class="bi bi-journal-bookmark-fill"></i> FAMILY CHRONICLE
      </span>
    </div>
  </template>

  <!-- Post Text -->
  <template x-if="post.displayMessage || (!post.video && post.postMessage)">
    <div class="post-content px-1 mb-3">
      <p class="mb-0" style="white-space: pre-line; font-size: 1.05rem; color: var(--text-main); line-height: 1.55; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;" x-html="renderHashtags(post.displayMessage || post.postMessage)"></p>
    </div>
  </template>

  <!-- Direct Chronicle Read CTA -->
  <template x-if="post.postMessage && post.postMessage.includes('/familyJournal/')">
    <div class="px-1 mb-3">
      <a :href="((post.postMessage.match(/(?:https?:\/\/[^\s<>&\"']+)?\/familyJournal\/\d+/) || ['/familyJournal'])[0])" 
         class="btn btn-sm text-white d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold shadow-xs" 
         style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); border-radius: 12px; font-size: 0.88rem; min-height: 44px; text-decoration: none;">
        <i class="bi bi-book-half"></i>
        <span>Read Full Chronicle</span>
        <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
  </template>

  <!-- Post Images -->
  <template x-if="post.images && post.images.length > 0">
    <div class="row g-2 mb-3 px-1">
      <template x-for="(img, idx) in post.images" :key="idx">
        <div :class="(post.images.length === 1 ? 'col-12' : (post.images.length === 3 && idx === 0 ? 'col-12' : 'col-6')) + ' post-img-col'">
          <div class="position-relative overflow-hidden" style="border-radius:10px;">
            <a href="#" 
               @click.prevent="openLightbox(post.images, idx)" 
               @dblclick.prevent.stop="triggerHeartBurst(post, idx)"
               role="button" 
               aria-label="View enlarged photo or double tap to like" 
               style="display:block; overflow:hidden; border-radius:10px; position:relative;">
              <img :src="(img && (img.startsWith('/public/') || img.startsWith('/images/'))) ? img : getPostImageUrl(img)" 
                   class="post-media-first-img w-100"
                   style="border-radius: 10px; max-height: 380px; object-fit: cover; cursor: pointer;" 
                   alt="Family post image" 
                   loading="lazy"
                   onerror="this.onerror=null; const c = this.closest('.post-img-col'); if(c) c.style.display='none';">
            </a>

            <!-- Floating Spring Heart Burst (Instagram Standard) -->
            <div class="feed-heart-burst" 
                 x-show="heartBursts[post.post_no + '_' + idx]" 
                 x-cloak 
                 aria-hidden="true">
              <svg class="heart-burst-svg" viewBox="0 0 24 24" width="76" height="76" fill="#ff2d55" stroke="#ffffff" stroke-width="1.2">
                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
              </svg>
            </div>
          </div>
        </div>
      </template>
    </div>
  </template>

  <!-- Embedded Video (YouTube, Vimeo, Cloud Stream) -->
  <template x-if="post.video && (post.video.type === 'youtube' || post.video.type === 'vimeo' || post.video.type === 'cloudflare')">
    <div class="video-embed-container mb-3 px-1">
      <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm border border-light-subtle" style="border-radius: 12px; max-height: 380px;">
        <iframe :src="post.video.embedUrl" 
                title="Video Player" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                allowfullscreen 
                loading="lazy"
                style="width: 100%; height: 100%; border: none;"></iframe>
      </div>
    </div>
  </template>

  <!-- Direct Video Stream -->
  <template x-if="post.video && post.video.type === 'direct'">
    <div class="video-embed-container mb-3 px-1">
      <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm border border-light-subtle" style="border-radius: 12px; max-height: 380px;">
        <video :src="post.video.embedUrl" controls preload="metadata" style="width: 100%; height: 100%; object-fit: contain; background: #000;"></video>
      </div>
    </div>
  </template>

  <!-- Full Graphics OpenGraph Social Card (16:9) -->
  <template x-if="post.link_preview && (post.link_preview.image || post.link_preview.title)">
    <div class="link-preview-container mb-3 px-1">
      <a :href="post.link_preview.url" 
         target="_blank" 
         rel="noopener noreferrer" 
         class="card border text-decoration-none overflow-hidden link-preview-card shadow-sm"
         style="border-radius: 14px; background-color: var(--bs-card-bg, #ffffff); border-color: var(--bs-border-color, #e2e8f0); transition: transform 0.2s ease, box-shadow 0.2s ease; display: block;">
        
        <!-- Full 16:9 Graphics Banner -->
        <template x-if="post.link_preview.image">
          <div class="position-relative overflow-hidden w-100" style="aspect-ratio: 16 / 9; background-color: #0f172a; max-height: 420px;">
            <img :src="post.link_preview.image" 
                 class="w-100 h-100 object-fit-cover" 
                 :alt="post.link_preview.title || 'Event preview image'"
                 loading="lazy"
                 onerror="this.style.display='none'">
            
            <!-- Platform Badge Pill -->
            <div class="position-absolute top-0 start-0 m-2.5">
              <span class="badge rounded-pill text-uppercase px-2.5 py-1.5 shadow-sm d-inline-flex align-items-center gap-1"
                    style="background: rgba(15, 23, 42, 0.82); backdrop-filter: blur(8px); font-size: 0.72rem; letter-spacing: 0.5px; border: 1px solid rgba(255, 255, 255, 0.18); color: #ffffff;">
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
        <div class="p-3" style="background-color: var(--bs-card-bg, #ffffff);">
          <div class="text-uppercase text-muted fw-semibold small mb-1 d-flex align-items-center justify-content-between" style="font-size: 0.74rem; letter-spacing: 0.5px;">
            <span class="text-truncate" x-text="post.link_preview.domain || post.link_preview.site_name"></span>
            <span class="text-primary small d-inline-flex align-items-center gap-1">
              <span x-text="post.link_preview.is_party_event ? 'Celebration Pass' : 'Web Link'"></span>
              <i class="bi bi-box-arrow-up-right" style="font-size: 0.7rem;"></i>
            </span>
          </div>

          <h6 class="fw-bold mb-1.5 text-dark" style="font-size: 1.05rem; line-height: 1.35;" x-text="post.link_preview.title"></h6>

          <template x-if="post.link_preview.description">
            <p class="text-secondary small mb-2.5" style="line-height: 1.45; font-size: 0.86rem; color: #64748b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" x-text="post.link_preview.description"></p>
          </template>

          <div class="pt-2 border-top d-flex justify-content-between align-items-center">
            <span class="small text-muted text-truncate" style="font-size: 0.78rem; max-width: 60%;" x-text="post.link_preview.url"></span>
            <span class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm"
                  style="min-height: 40px; font-size: 0.82rem; white-space: nowrap; flex-shrink: 0;">
              <span x-text="post.link_preview.cta_label || 'View Event / RSVP →'"></span>
            </span>
          </div>
        </div>

      </a>
    </div>
  </template>

  <!-- Poll Section -->
  <template x-if="post.poll && post.poll.options && post.poll.options.length > 0">
    <div class="poll-container mt-2 p-3 bg-light rounded border border-light-subtle mb-3 mx-1">
      <h6 class="fw-bold mb-3" style="font-size: 0.9rem;"><i class="fa fa-bar-chart me-2 text-primary"></i><span x-text="post.poll.question"></span></h6>
      <div class="poll-options">
        <template x-for="opt in post.poll.options" :key="opt.option_id">
          <div class="poll-option mb-2 position-relative">
            <button type="button" 
                    @click="votePoll(post.post_no, opt.option_id)" 
                    :disabled="isVotingPoll(post.post_no)"
                    class="poll-option-btn w-100 text-start btn position-relative overflow-hidden" 
                    :class="[
                      isOptionVoted(post, opt.option_id) ? 'btn-outline-primary' : 'btn-outline-secondary',
                      isVotingPoll(post.post_no) ? 'opacity-75 cursor-not-allowed' : ''
                    ]"
                    style="z-index: 1; font-size: 0.85rem; padding: 8px 12px; min-height: 44px;">
              <span class="position-relative d-inline-flex align-items-center gap-1" style="z-index: 2;">
                <span x-text="opt.option_text"></span>
                <span x-show="isVotingPoll(post.post_no)" class="spinner-border spinner-border-sm ms-1 text-primary" role="status" aria-hidden="true" style="width: 0.85rem; height: 0.85rem;"></span>
              </span>
              <span class="float-end position-relative fw-bold" style="z-index: 2;" x-text="(opt.percentage || 0) + '%'"></span>
              <div class="position-absolute top-0 start-0 h-100" 
                   :class="isOptionVoted(post, opt.option_id) ? 'bg-primary' : 'bg-secondary'" 
                   :style="'width: ' + (opt.percentage || 0) + '%; opacity: 0.15; transition: width 0.5s ease; z-index: 0;'"></div>
            </button>
          </div>
        </template>
      </div>
      <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;" x-text="(post.poll.total_votes || 0) + ' votes'"></small>
    </div>
  </template>

  <!-- Post Metrics -->
  <div class="d-flex justify-content-between px-1 pb-2 text-muted border-bottom" style="font-size: 0.825rem;">
    <div>
      <span x-show="post.post_likes > 0">
        <i class="bi bi-hand-thumbs-up-fill text-primary"></i>
        <span x-text="post.post_likes"></span>
      </span>
    </div>
    <div>
      <span x-show="post.comments && post.comments.length > 0">
        <span x-text="post.comments.length"></span> comments
      </span>
    </div>
  </div>

  <!-- Actions Bar (Like with Reactions / Comment Trigger) -->
  <div class="d-flex justify-content-around pt-1 pb-1 mb-2 border-top">

    <!-- Like Button with floating emoji reaction bar -->
    <div class="position-relative flex-grow-1"
         @mouseenter="togglePostReactionBar(post.post_no, true)"
         @mouseleave="togglePostReactionBar(post.post_no, false)"
         @click.outside="activeReactionBars[post.post_no] = false">

      <!-- Floating Reaction Bar -->
      <div x-show="activeReactionBars[post.post_no]"
           x-transition:enter="transition ease-out duration-150"
           x-transition:enter-start="opacity-0 translate-y-1"
           x-transition:enter-end="opacity-100 translate-y-0"
           x-transition:leave="transition ease-in duration-100"
           x-transition:leave-start="opacity-100"
           x-transition:leave-end="opacity-0"
           @mouseenter="keepPostReactionBar(post.post_no, true)"
           @mouseleave="keepPostReactionBar(post.post_no, false)"
           class="reaction-bar-float"
           style="position:absolute; bottom:calc(100% + 6px); left:50%; transform:translateX(-50%); background:var(--card-bg); border-radius:30px; padding:6px 10px; box-shadow:0 4px 20px rgba(0,0,0,0.18); display:flex; gap:4px; z-index:100; white-space:nowrap;">
        <template x-for="type in Object.keys(emojiMap)" :key="type">
          <button type="button"
                  @click="reactToPost(post.post_no, type)"
                  :title="emojiMap[type].label"
                  :aria-label="emojiMap[type].label"
                  :class="post.user_reaction === type ? 'reaction-emoji-btn active' : 'reaction-emoji-btn'"
                  style="background:none; border:none; font-size:1.6rem; cursor:pointer; padding:2px 4px; border-radius:50%; transition:transform 0.15s ease, background 0.15s;">
            <span x-text="emojiMap[type].icon"></span>
          </button>
        </template>
      </div>

      <!-- Like / Active Reaction Button -->
      <button class="btn btn-link text-decoration-none w-100 py-2 d-flex align-items-center justify-content-center gap-1"
              :class="post.user_reaction ? 'fw-semibold' : 'text-muted'"
              :style="post.user_reaction ? 'color: #1877f2; font-size:0.95rem; font-weight:600;' : 'font-size:0.95rem; font-weight:500;'"
              type="button"
              @click="onPostLikeClick(post.post_no, post.user_reaction || 'like')"
              @touchstart.passive="startPostLongPress(post.post_no)"
              @touchend="cancelPostLongPress()"
              @touchmove="cancelPostLongPress()">
        <span x-text="post.user_reaction ? (emojiMap[post.user_reaction]?.icon || '👍') : '👍'" style="font-size:1.15rem;"></span>
        <span x-text="post.user_reaction ? (emojiMap[post.user_reaction]?.label || 'Like') : 'Like'" class="ms-1"></span>
      </button>
    </div>

    <!-- Divider -->
    <div style="width:1px; background:var(--border-color); margin:6px 0;"></div>

    <!-- Comment Button -->
    <button class="btn btn-link text-decoration-none text-muted flex-grow-1 py-2 d-flex align-items-center justify-content-center gap-2"
            style="font-size: 0.95rem; font-weight: 500; min-height: 44px;"
            type="button"
            @click="toggleCommentForm(post.post_no)">
      <i class="bi bi-chat-bubble"></i>
      Comment
    </button>

    <!-- Divider -->
    <div style="width:1px; background:var(--border-color); margin:6px 0;"></div>

    <!-- Share Button (Web Share API) -->
    <button class="btn btn-link text-decoration-none text-muted flex-grow-1 py-2 d-flex align-items-center justify-content-center gap-2"
            style="font-size: 0.95rem; font-weight: 500; min-height: 44px;"
            type="button"
            @click="sharePostToSocialMedia(post)"
            title="Share this memory">
      <i class="bi bi-share"></i>
      Share
    </button>
  </div>

  <!-- Comments Section -->
  <div class="comments-section border-top pt-3">
    <!-- Dynamic Comment Form with emoji picker -->
    <div x-show="activeCommentForms[post.post_no]" x-transition class="mb-3">
      <div class="position-relative">
        <!-- Emoji picker popup for comment input -->
        <div x-show="commentEmojiOpen[post.post_no]"
             x-transition
             @click.outside="commentEmojiOpen[post.post_no] = false"
             style="position:absolute; bottom:calc(100% + 6px); left:0; background:var(--card-bg); border-radius:16px; padding:10px; box-shadow:0 4px 20px rgba(0,0,0,0.15); z-index:200; width:260px;">
          <div style="display:grid; grid-template-columns:repeat(8,1fr); gap:4px;">
            <template x-for="type in Object.keys(commentEmojiMap)" :key="type">
              <button type="button" @click="insertEmojiIntoComment(post.post_no, commentEmojiMap[type])"
                      style="background:none; border:none; font-size:1.4rem; cursor:pointer; border-radius:8px; padding:3px; transition:background 0.1s;"
                      @mouseenter="$el.style.background='var(--hover-color)'"
                      @mouseleave="$el.style.background='none'">
                <span x-text="commentEmojiMap[type]"></span>
              </button>
            </template>
            <!-- Additional common emojis -->
            <template x-for="emoji in ['😊','😍','🥰','😎','🤣','😭','🙏','🔥','💯','🎉','👏','✨','💪','🤔','😅','🥳']" :key="emoji">
              <button type="button" @click="insertEmojiIntoComment(post.post_no, emoji)"
                      style="background:none; border:none; font-size:1.4rem; cursor:pointer; border-radius:8px; padding:3px; transition:background 0.1s;"
                      @mouseenter="$el.style.background='var(--hover-color)'"
                      @mouseleave="$el.style.background='none'">
                <span x-text="emoji"></span>
              </button>
            </template>
          </div>
        </div>

        <form @submit.prevent="submitComment(post.post_no)" class="d-flex gap-2 align-items-center">
    @include('csrf')
          <!-- Emoji toggle button -->
          <button type="button"
                  @click="toggleCommentEmoji(post.post_no)"
                  :aria-expanded="commentEmojiOpen[post.post_no] ? 'true' : 'false'"
                  style="background:none; border:none; font-size:1.3rem; cursor:pointer; padding:4px 6px; border-radius:50%; flex-shrink:0; transition:background 0.15s;"
                  @mouseenter="$el.style.background='var(--hover-color)'"
                  @mouseleave="$el.style.background='none'"
                  title="Add emoji">
            😊
          </button>
          <input type="text" class="form-control rounded-pill"
                 placeholder="Write a comment..."
                 x-model="commentInputs[post.post_no]" required
                 style="font-size: 0.875rem; background-color: #f0f2f5; border: none; padding: 8px 16px;">
          <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3" style="font-size: 0.8rem; flex-shrink:0;">Post</button>
        </form>
      </div>
    </div>

    <!-- Comments List -->
    <div class="d-flex flex-column gap-2">
      <template x-for="c in post.comments" :key="c.comment_no">
        <div class="d-flex align-items-start gap-2">
          <img :src="c.profileImg" alt="Avatar" class="rounded-circle" width="32" height="32" style="object-fit: cover; margin-top: 2px;">
          <div class="flex-grow-1">
            <!-- Comment Bubble -->
            <template x-if="editingCommentNo !== c.comment_no">
              <div class="px-3 py-2 rounded-3 comment-bubble-box" style="background-color: #f0f2f5; display: inline-block; max-width: 90%;">
                <div class="fw-semibold text-dark" style="font-size: 0.8125rem; line-height: 1.2;" x-text="c.fullName"></div>
                <div class="mt-1 comment-bubble-text" style="font-size: 0.875rem; color: #050505; word-break: break-word; line-height: 1.35; font-variant-emoji: emoji;" x-text="c.comment"></div>
              </div>
            </template>

            <!-- Inline Comment Edit -->
            <template x-if="editingCommentNo === c.comment_no">
              <form @submit.prevent="saveCommentEdit(c.comment_no)" class="d-flex gap-2 align-items-center" style="max-width: 90%;">
    @include('csrf')
                <input type="text" class="form-control form-control-sm rounded-pill" x-model="editCommentText" required
                       style="font-size: 0.875rem; background-color: #f0f2f5; border: none; padding: 6px 14px;">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3" style="font-size: 0.78rem; flex-shrink:0;">Save</button>
                <button type="button" class="btn btn-link btn-sm text-muted" style="font-size: 0.78rem; flex-shrink:0;" @click="cancelEditComment()">Cancel</button>
              </form>
            </template>

            <!-- Top Reactions Preview -->
            <div x-show="getTopCommentReactions(c.reactions).length > 0" class="d-flex align-items-center gap-1 mt-1 ps-1">
              <div class="reaction-count-pill d-flex gap-1 align-items-center" style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:20px; padding:1px 7px; font-size:0.78rem; box-shadow:0 1px 4px rgba(0,0,0,0.08);">
                <template x-for="r in getTopCommentReactions(c.reactions)" :key="r.emoji">
                  <span>
                    <span x-text="r.emoji"></span>
                    <span x-text="r.count" style="color:var(--text-muted); font-size:0.74rem; margin-left:1px;"></span>
                  </span>
                </template>
              </div>
            </div>

            <!-- Comment Meta + Like Button with Reaction Bar -->
            <div class="d-flex align-items-center gap-3 ps-2 mt-1">
              <small class="text-muted" style="font-size: 0.75rem;" x-text="formatDate(c.comment_time || c.post_time || c.date_created)"></small>

              <!-- Comment Like/React button -->
              <div class="position-relative"
                   @mouseenter="toggleCommentReactionBar(c.comment_no, true)"
                   @mouseleave="toggleCommentReactionBar(c.comment_no, false)"
                   @click.outside="activeCommentReactionBars[c.comment_no] = false">

                <!-- Floating Comment Reaction Bar -->
                <div x-show="activeCommentReactionBars[c.comment_no]"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @mouseenter="keepCommentReactionBar(c.comment_no, true)"
                     @mouseleave="keepCommentReactionBar(c.comment_no, false)"
                     style="position:absolute; bottom:calc(100% + 4px); left:0; background:var(--card-bg); border-radius:30px; padding:5px 8px; box-shadow:0 4px 18px rgba(0,0,0,0.16); display:flex; gap:3px; z-index:100; white-space:nowrap;">
                  <template x-for="type in Object.keys(commentEmojiMap)" :key="type">
                    <button type="button"
                            @click="reactToComment(post.post_no, c.comment_no, type)"
                            :title="type"
                            :class="c.userReaction === type ? 'reaction-emoji-btn active' : 'reaction-emoji-btn'"
                            style="background:none; border:none; font-size:1.4rem; cursor:pointer; padding:2px 3px; border-radius:50%; transition:transform 0.15s ease;">
                      <span x-text="commentEmojiMap[type]"></span>
                    </button>
                  </template>
                </div>

                <!-- Like text button -->
                <button type="button"
                        @click="onCommentLikeClick(post.post_no, c.comment_no, c.userReaction || 'like')"
                        @touchstart.passive="startCommentLongPress(c.comment_no)"
                        @touchend="cancelCommentLongPress()"
                        @touchmove="cancelCommentLongPress()"
                        :class="c.userReaction ? 'text-primary fw-semibold' : 'text-muted'"
                        style="background:none; border:none; font-size:0.78rem; cursor:pointer; padding:0; line-height:1;">
                  <span x-show="c.userReaction" x-text="commentEmojiMap[c.userReaction] || ''"></span>
                  <span x-text="c.userReaction ? c.userReaction.charAt(0).toUpperCase() + c.userReaction.slice(1) : 'Like'"></span>
                </button>
              </div>

              <template x-if="canEditComment(c)">
                <button type="button" class="text-muted" style="background:none; border:none; font-size:0.78rem; cursor:pointer; padding:0;" @click="startEditComment(c)">Edit</button>
              </template>
              <template x-if="canModerateComment(post, c)">
                <button type="button" class="text-muted" style="background:none; border:none; font-size:0.78rem; cursor:pointer; padding:0;" @click="deleteComment(post.post_no, c.comment_no)">Delete</button>
              </template>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>

</div>
