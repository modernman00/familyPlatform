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
          <h6 class="mb-0 fw-semibold text-dark" style="font-size: 1.05rem;" x-text="post.fullName"></h6>
          <div class="d-flex align-items-center gap-2 mt-0.5">
            <small class="text-muted" style="font-size: 0.85rem;" x-text="formatDate(post.post_time || post.date_created)"></small>
            <template x-if="post.postFamCode">
              <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill" style="font-size: 0.78rem; font-weight: normal; padding: 2px 8px;" x-text="'Family: ' + post.postFamCode"></span>
            </template>
          </div>
        </div>
        <template x-if="isOwnPost(post)">
          <div class="dropdown">
            <button class="btn btn-sm btn-link text-muted p-1 rounded-circle" type="button" :id="'kebab' + post.post_no" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;" title="Post options">
              <i class="bi bi-three-dots" style="font-size: 1.25rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" :aria-labelledby="'kebab' + post.post_no" style="border-radius: 12px; font-size: 0.9rem; min-width: 160px;">
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
            </ul>
          </div>
        </template>
      </div>
    </div>
  </div>

  <!-- Post Text -->
  <template x-if="post.displayMessage || (!post.video && post.postMessage)">
    <div class="post-content px-1 mb-3">
      <p class="mb-0" style="white-space: pre-line; font-size: 1.05rem; color: var(--text-main); line-height: 1.55; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;" x-text="post.displayMessage || post.postMessage"></p>
    </div>
  </template>

  <!-- Post Images -->
  <template x-if="post.images && post.images.length > 0">
    <div class="row g-2 mb-3 px-1">
      <template x-for="(img, idx) in post.images" :key="idx">
        <div :class="(post.images.length === 1 ? 'col-12' : (post.images.length === 3 && idx === 0 ? 'col-12' : 'col-6')) + ' post-img-col'">
          <a href="#" @click.prevent="openLightbox(post.images, idx)" style="display:block; overflow:hidden; border-radius:10px;">
            <img :src="'/resources/images/post/' + encodeURIComponent(img)" 
                 style="width:100%; border-radius: 10px; max-height: 380px; object-fit: cover; transition: transform 0.2s ease; cursor: pointer;" 
                 alt="" 
                 onerror="this.onerror=null; const c = this.closest('.post-img-col'); if(c) c.style.display='none';"
                 onmouseover="this.style.transform='scale(1.02)';" 
                 onmouseout="this.style.transform='scale(1)';">
          </a>
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

  <!-- Poll Section -->
  <template x-if="post.poll && post.poll.options && post.poll.options.length > 0">
    <div class="poll-container mt-2 p-3 bg-light rounded border border-light-subtle mb-3 mx-1">
      <h6 class="fw-bold mb-3" style="font-size: 0.9rem;"><i class="fa fa-bar-chart me-2 text-primary"></i><span x-text="post.poll.question"></span></h6>
      <div class="poll-options">
        <template x-for="opt in post.poll.options" :key="opt.option_id">
          <div class="poll-option mb-2 position-relative">
            <button type="button" @click="votePoll(post.post_no, opt.option_id)" 
                    class="poll-option-btn w-100 text-start btn position-relative overflow-hidden" 
                    :class="(post.poll.user_voted_option_id && post.poll.user_voted_option_id.includes(opt.option_id)) ? 'btn-outline-primary' : 'btn-outline-secondary'"
                    style="z-index: 1; font-size: 0.85rem; padding: 8px 12px;">
              <span class="position-relative" style="z-index: 2;" x-text="opt.option_text"></span>
              <span class="float-end position-relative fw-bold" style="z-index: 2;" x-text="(opt.percentage || 0) + '%'"></span>
              <div class="position-absolute top-0 start-0 h-100" 
                   :class="(post.poll.user_voted_option_id && post.poll.user_voted_option_id.includes(opt.option_id)) ? 'bg-primary' : 'bg-secondary'" 
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
        <template x-for="[type, data] in Object.entries(emojiMap)" :key="type">
          <button type="button"
                  @click="reactToPost(post.post_no, type)"
                  :title="data.label"
                  :aria-label="data.label"
                  :class="post.user_reaction === type ? 'reaction-emoji-btn active' : 'reaction-emoji-btn'"
                  style="background:none; border:none; font-size:1.6rem; cursor:pointer; padding:2px 4px; border-radius:50%; transition:transform 0.15s ease, background 0.15s;">
            <span x-text="data.icon"></span>
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
            style="font-size: 0.95rem; font-weight: 500;"
            type="button"
            @click="toggleCommentForm(post.post_no)">
      <i class="bi bi-chat-bubble"></i>
      Comment
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
            <template x-for="[type, emoji] in Object.entries(commentEmojiMap)" :key="type">
              <button type="button" @click="insertEmojiIntoComment(post.post_no, emoji)"
                      style="background:none; border:none; font-size:1.4rem; cursor:pointer; border-radius:8px; padding:3px; transition:background 0.1s;"
                      @mouseenter="$el.style.background='var(--hover-color)'"
                      @mouseleave="$el.style.background='none'">
                <span x-text="emoji"></span>
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
              <div class="px-3 py-2 rounded-3" style="background-color: #f0f2f5; display: inline-block; max-width: 90%;">
                <div class="fw-semibold text-dark" style="font-size: 0.8125rem; line-height: 1.2;" x-text="c.fullName"></div>
                <div class="mt-1" style="font-size: 0.875rem; color: #050505; word-break: break-word; line-height: 1.35;" x-text="c.comment"></div>
              </div>
            </template>

            <!-- Inline Comment Edit -->
            <template x-if="editingCommentNo === c.comment_no">
              <form @submit.prevent="saveCommentEdit(c.comment_no)" class="d-flex gap-2 align-items-center" style="max-width: 90%;">
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
              <small class="text-muted" style="font-size: 0.75rem;" x-text="formatDate(c.comment_time || c.date_created)"></small>

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
                  <template x-for="[type, emoji] in Object.entries(commentEmojiMap)" :key="type">
                    <button type="button"
                            @click="reactToComment(post.post_no, c.comment_no, type)"
                            :title="type"
                            :class="c.userReaction === type ? 'reaction-emoji-btn active' : 'reaction-emoji-btn'"
                            style="background:none; border:none; font-size:1.4rem; cursor:pointer; padding:2px 3px; border-radius:50%; transition:transform 0.15s ease;">
                      <span x-text="emoji"></span>
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
