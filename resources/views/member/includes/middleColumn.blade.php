<!-- Middle Column - Feed -->
<style>
  .highlighted-post {
    animation: postHighlightPulse 2.5s ease-out;
  }
  @keyframes postHighlightPulse {
    0% { box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.6); }
    100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
  }
</style>
<div class="feed-column">
  <!-- Post Composer -->
  <div class="card post-composer mb-4 border-0 shadow-sm" style="border-radius: 16px; overflow: hidden; background-color: var(--card-bg);" data-bs-toggle="modal" id="openPostModalTrigger" data-bs-target="#postModal" tabindex="0">
    <div class="card-body d-flex align-items-center p-4">
      @if(isset($data['img']))
        <img src="/resources/images/profile/{{ $data['img'] }}" alt="profile" class="rounded-circle me-3" width="48" height="48" style="object-fit: cover;">
      @elseif(isset($data['profilePics']))
        <img src="/resources/images/profile/{{ $data['profilePics'] }}" alt="profile" class="rounded-circle me-3" width="48" height="48" style="object-fit: cover;">
      @else
        <div class="rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 48px; height: 48px; background-color: var(--primary-color); color: white; font-size: 1.2rem;">
            {{ substr($data['firstName'] ?? 'F', 0, 1) }}{{ substr($data['lastName'] ?? 'O', 0, 1) }}
        </div>
      @endif

      <div class="flex-grow-1 rounded-pill px-4 py-3" style="background-color: var(--hover-color); cursor: pointer; transition: background-color 0.2s;">
        <span style="color: var(--text-muted); font-size: 0.95rem;">Share a moment with your family...</span>
      </div>
    </div>
  </div>

  <!-- Memories Section (Hidden until loaded) -->
  <div id="memories-container" style="display: none; margin-bottom: 24px;">
    <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
      <div class="card-body p-4">
        <h5 class="fw-bold text-primary mb-3"><i class="fa fa-history me-2"></i> On This Day</h5>
        <div id="memories-content"></div>
      </div>
    </div>
  </div>

  <!-- Reactive Alpine.js Feed -->
  <div x-data="profileFeed()" class="feed-posts-container">
    <!-- Error Alert if loading fails -->
    <template x-if="errorMessage">
      <div class="alert alert-danger shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <span x-text="errorMessage"></span>
        <button class="btn btn-sm btn-outline-danger ms-3" @click="fetchPosts()">Retry</button>
      </div>
    </template>

    <!-- Skeleton Loading State -->
    <template x-if="isLoading">
      <div>
        <div class="skeleton-post shadow-sm mb-4" style="background: white; border-radius: 16px; padding: 20px; text-align: left; animation: pulse 1.5s infinite ease-in-out;">
          <div style="display: flex; align-items: center; margin-bottom: 15px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: #e2e8f0; margin-right: 15px;"></div>
            <div style="flex-grow: 1;">
              <div style="height: 12px; background: #e2e8f0; border-radius: 4px; width: 40%; margin-bottom: 8px;"></div>
              <div style="height: 10px; background: #e2e8f0; border-radius: 4px; width: 20%;"></div>
            </div>
          </div>
          <div style="height: 14px; background: #e2e8f0; border-radius: 4px; width: 100%; margin-bottom: 10px;"></div>
          <div style="height: 14px; background: #e2e8f0; border-radius: 4px; width: 85%; margin-bottom: 10px;"></div>
          <div style="height: 14px; background: #e2e8f0; border-radius: 4px; width: 60%;"></div>
        </div>
      </div>
    </template>

    <!-- Empty State -->
    <template x-if="!isLoading && posts.length === 0 && !errorMessage">
      <div class="card border-0 shadow-sm p-5 text-center mb-4" style="border-radius: 16px; background: white;">
        <div class="mb-3">
          <i class="bi bi-chat-heart text-muted" style="font-size: 3rem;"></i>
        </div>
        <h5 class="fw-bold text-dark">No family posts yet</h5>
        <p class="text-muted mb-0">Share the first memory or event with your family above!</p>
      </div>
    </template>

    <!-- Posts Loop -->
    <div id="postIt" class="postIt">
      <template x-for="post in posts" :key="post.post_no">
        <div class="card border-0 shadow-sm mb-4 p-3" :id="'post' + post.post_no" :class="'post' + post.post_no" style="border-radius: 8px; background-color: var(--card-bg);">
          <!-- Post Author Header -->
          <div class="d-flex align-items-center mb-3">
            <a :href="'/profilepage/img?dir=img&pics=' + ((post.images && post.images[0]) ? post.images[0] : '') + '&pID=' + post.post_no + '&path=profile&id=' + post.id">
              <img :src="post.profileImg" alt="img" class="rounded-circle me-3" style="width:40px; height:40px; object-fit: cover;">
            </a>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-0 fw-semibold text-dark" style="font-size: 0.9375rem;" x-text="post.fullName"></h6>
                  <div class="d-flex align-items-center gap-2 mt-0.5">
                    <small class="text-muted" style="font-size: 0.8125rem;" x-text="formatDate(post.post_time || post.date_created)"></small>
                    <template x-if="post.postFamCode">
                      <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill" style="font-size: 0.7rem; font-weight: normal; padding: 2px 6px;" x-text="'Family: ' + post.postFamCode"></span>
                    </template>
                  </div>
                </div>
                <template x-if="isOwnPost(post)">
                  <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-link text-muted p-1" title="Edit post" @click="editPost(post)">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-muted p-1" title="Delete post" @click="deletePost(post.post_no)">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </template>
              </div>
            </div>
          </div>

          <!-- Post Text -->
          <div class="post-content px-1 mb-3">
            <p class="mb-0" style="white-space: pre-line; font-size: 0.9375rem; color: #1c1e21; line-height: 1.5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;" x-text="post.postMessage"></p>
          </div>

          <!-- Post Images -->
          <template x-if="post.images && post.images.length > 0">
            <div class="row g-2 mb-3 px-1">
              <template x-for="(img, idx) in post.images" :key="idx">
                <div :class="post.images.length === 1 ? 'col-12' : (post.images.length === 3 && idx === 0 ? 'col-12' : 'col-6')">
                  <a href="#" @click.prevent="openLightbox(post.images, idx)" style="display:block; overflow:hidden; border-radius:10px;">
                    <img :src="'/resources/images/post/' + encodeURIComponent(img)" style="width:100%; border-radius: 10px; max-height: 380px; object-fit: cover; transition: transform 0.2s ease; cursor: pointer;" alt="post image" onmouseover="this.style.transform='scale(1.02)';" onmouseout="this.style.transform='scale(1)';" onerror="this.onerror=null; this.closest('a').style.display='none';">
                  </a>
                </div>
              </template>
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
                      :style="post.user_reaction ? 'color: #1877f2; font-size:0.9rem;' : 'font-size:0.9rem;'"
                      type="button"
                      @click="onPostLikeClick(post.post_no, post.user_reaction || 'like')"
                      @touchstart.passive="startPostLongPress(post.post_no)"
                      @touchend="cancelPostLongPress()"
                      @touchmove="cancelPostLongPress()">
                <span x-text="post.user_reaction ? (emojiMap[post.user_reaction]?.icon || '👍') : '👍'" style="font-size:1.1rem;"></span>
                <span x-text="post.user_reaction ? (emojiMap[post.user_reaction]?.label || 'Like') : 'Like'" class="ms-1"></span>
              </button>
            </div>

            <!-- Divider -->
            <div style="width:1px; background:var(--border-color); margin:6px 0;"></div>

            <!-- Comment Button -->
            <button class="btn btn-link text-decoration-none text-muted flex-grow-1 py-2 d-flex align-items-center justify-content-center gap-2"
                    style="font-size: 0.9rem;"
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
      </template>
    </div>
    
    <!-- Premium Alpine Lightbox Modal -->
    <div x-show="lightboxOpen" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background-color: rgba(0, 0, 0, 0.95); z-index: 9999; backdrop-filter: blur(15px); display: flex; justify-content: center; align-items: center;" x-transition.opacity.duration.300ms @keydown.escape.window="closeLightbox()" @keydown.right.window="nextLightboxImage()" @keydown.left.window="prevLightboxImage()" @click.self="closeLightbox()">
        
        <!-- Close Button -->
        <button @click="closeLightbox()" style="position: absolute; top: 20px; right: 30px; background: rgba(255,255,255,0.15); border: none; color: white; width: 45px; height: 45px; border-radius: 50%; font-size: 28px; font-weight: 300; cursor: pointer; display: flex; justify-content: center; align-items: center; transition: all 0.2s ease; z-index: 10000; box-shadow: 0 4px 12px rgba(0,0,0,0.3);" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='scale(1)';">&times;</button>
        
        <!-- Previous Button -->
        <button @click.prevent.stop="prevLightboxImage()" x-show="lightboxImages && lightboxImages.length > 1" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.15); border: none; color: white; width: 55px; height: 55px; border-radius: 50%; font-size: 24px; display: flex; justify-content: center; align-items: center; cursor: pointer; transition: all 0.2s ease; z-index: 10000; user-select: none; box-shadow: 0 4px 12px rgba(0,0,0,0.3);" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-50%) scale(1.05)';" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='translateY(-50%) scale(1)';">&#10094;</button>

        <!-- Main Image Container -->
        <div style="position: relative; width: 100vw; height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" @click.self="closeLightbox()">
            <img :src="(lightboxImages && lightboxImages[lightboxIndex]) ? '/resources/images/post/' + encodeURIComponent(lightboxImages[lightboxIndex]) : ''" style="max-width: 100%; max-height: 100%; border-radius: 4px; object-fit: contain; box-shadow: 0 30px 60px rgba(0,0,0,0.6);" alt="Enlarged image" @click.stop>
        </div>
        
        <!-- Next Button -->
        <button @click.prevent.stop="nextLightboxImage()" x-show="lightboxImages && lightboxImages.length > 1" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.15); border: none; color: white; width: 55px; height: 55px; border-radius: 50%; font-size: 24px; display: flex; justify-content: center; align-items: center; cursor: pointer; transition: all 0.2s ease; z-index: 10000; user-select: none; box-shadow: 0 4px 12px rgba(0,0,0,0.3);" onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='translateY(-50%) scale(1.05)';" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='translateY(-50%) scale(1)';">&#10095;</button>
        
        <!-- Image Counter -->
        <div x-show="lightboxImages && lightboxImages.length > 1" style="position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); color: white; font-size: 15px; font-weight: 500; background: rgba(0,0,0,0.7); padding: 8px 24px; border-radius: 30px; letter-spacing: 1px; box-shadow: 0 4px 12px rgba(0,0,0,0.4); z-index: 10000;" x-text="(lightboxIndex + 1) + ' / ' + lightboxImages.length"></div>
    </div>
  </div>

  <!-- The Modal Structure (Hidden by default) for the post image modal -->
  @includeIf('member.modals.postImg')


  <style>
    @keyframes pulse {
      0% { opacity: 0.6; }
      50% { opacity: 1; }
      100% { opacity: 0.6; }
    }
  </style>
</div>
