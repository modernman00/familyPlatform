{{-- @props(["post" => null]) --}}
  <!-- Comments Section -->
  <div class="comments-section pt-2" style="overflow: visible;">
    <!-- Dynamic Comment Form with emoji picker -->
    <div x-show="activeCommentForms[post.post_no]" x-transition class="mb-3" style="overflow: visible;">
      <div class="position-relative" style="overflow: visible;">
        <!-- Emoji picker popup for comment input -->
        <div x-show="commentEmojiOpen[post.post_no]" x-cloak
             x-transition.opacity.duration.120ms
             @click.outside="commentEmojiOpen[post.post_no] = false"
             @keydown.escape.window="commentEmojiOpen[post.post_no] = false"
             role="dialog" aria-label="Choose an emoji" data-testid="comment-emoji-picker"
             class="comment-picker comment-picker--emoji">
          <!-- Race-Conscious Fitzpatrick Skin Tone Palette -->
          <div :class="commentEmojiTab === 'me' ? 'd-none' : 'd-flex'" class="align-items-center justify-content-between px-2 py-1 border-bottom mb-1 bg-light rounded" role="toolbar" aria-label="Comment skin tone diversity selector">
            <span class="text-muted fw-semibold" style="font-size:0.68rem;">Skin:</span>
            <div class="d-flex align-items-center gap-1">
              <template x-for="st in skinTones" :key="st.tone">
                <button type="button" class="btn btn-sm p-0 border-0 rounded-circle"
                        :class="commentSkinTone === st.tone ? 'shadow-sm border border-primary' : 'opacity-75'"
                        style="font-size:0.82rem; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer;"
                        :title="st.label" :aria-label="st.label"
                        @click.stop="setCommentSkinTone(st.tone)" x-text="st.swatch"></button>
              </template>
            </div>
          </div>

          <div class="comment-emoji-tabs" role="tablist" aria-label="Emoji type">
            <button type="button" class="comment-emoji-tab" role="tab" data-testid="comment-emoji-tab-standard"
                    :class="commentEmojiTab === 'standard' ? 'active' : ''"
                    :aria-selected="commentEmojiTab === 'standard' ? 'true' : 'false'"
                    @click.stop="commentEmojiTab = 'standard'">😀 Standard</button>
            <button type="button" class="comment-emoji-tab" role="tab" data-testid="comment-emoji-tab-living"
                    :class="commentEmojiTab === 'living' ? 'active' : ''"
                    :aria-selected="commentEmojiTab === 'living' ? 'true' : 'false'"
                    @click.stop="commentEmojiTab = 'living'">✨ Living</button>
            <button type="button" class="comment-emoji-tab" role="tab" data-testid="comment-emoji-tab-me"
                    :class="commentEmojiTab === 'me' ? 'active' : ''"
                    :aria-selected="commentEmojiTab === 'me' ? 'true' : 'false'"
                    @click.stop="openMeStickerTab()">🙋 Me</button>
          </div>

          <template x-if="commentEmojiOpen[post.post_no] && commentEmojiTab === 'me'">
            <div class="me-sticker-grid me-sticker-grid--comment" data-testid="comment-me-grid">
              <span class="me-sticker-hint" x-show="meStickers && meStickers.available">Your avatar's skin, hair and style come from <a href="/member/emoji-studio">Emoji Studio</a>.</span>
              <span class="text-muted small" x-show="!meStickers && !meStickersError">Loading your avatar…</span>
              <span class="text-muted small" x-show="meStickersError">Could not load your avatar.</span>
              <template x-if="meStickers && !meStickers.available">
                <a href="/member/emoji-studio" class="btn btn-sm btn-warning rounded-pill fw-semibold me-sticker-unlock">🎨 Design your avatar in Emoji Studio to unlock Me-stickers</a>
              </template>
              <template x-for="st in (meStickers && meStickers.available ? meStickers.stickers : [])" :key="st.reaction">
                <button type="button" class="me-sticker-btn" :title="st.label" :aria-label="'Add my avatar: ' + st.label"
                        @click="insertMeStickerIntoComment(post.post_no, st.token, st.url, st.label)">
                  <img :src="st.url" width="52" height="52" alt="" loading="lazy">
                  <span x-text="st.label"></span>
                </button>
              </template>
            </div>
          </template>

          <div class="comment-emoji-grid" x-show="commentEmojiTab === 'standard'">
            <template x-for="emoji in commentEmojiPalette" :key="emoji">
              <button type="button" class="comment-emoji-btn" @click="insertEmojiIntoComment(post.post_no, emoji)"
                      :aria-label="'Insert ' + emoji" x-text="emoji"></button>
            </template>
          </div>

          {{-- Perf: only build the living grid while this post's picker is open on that tab --}}
          <template x-if="commentEmojiOpen[post.post_no] && commentEmojiTab === 'living'">
            <div class="comment-living-grid" data-testid="comment-living-grid">
              <template x-for="le in commentLivingPalette" :key="le.id">
                <button type="button" class="comment-living-btn" :data-emoji-id="le.id"
                        @click="insertLivingEmojiIntoComment(post.post_no, le.id)"
                        :title="le.title" :aria-label="'Insert living emoji ' + le.title">
                  <img :src="'/public/img/emoji/' + le.id + '.svg?v=20261107a'" :alt="le.title" width="38" height="38" loading="lazy" class="atg-3d-emoji">
                </button>
              </template>
            </div>
          </template>
        </div>

        <!-- GIF picker popup for comment input -->
        <div x-show="commentGifOpen[post.post_no]" x-cloak
             x-transition.opacity.duration.120ms
             @click.outside="commentGifOpen[post.post_no] = false"
             data-testid="comment-gif-picker"
             class="comment-picker comment-picker--gif">
          <div class="d-flex flex-wrap gap-1 mb-2">
            <template x-for="cat in gifCategories" :key="cat.key">
              <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill fw-semibold gif-cat-pill"
                      :class="commentGifCategory === cat.key ? 'active' : ''"
                      @click="commentGifCategory = cat.key"
                      style="font-size:0.72rem;" x-text="cat.label"></button>
            </template>
          </div>
          <div class="d-flex flex-wrap gap-2 overflow-y-auto" style="max-height:200px;">
            {{-- Perf: only build the GIF grid while this post's picker is open (~30 hidden <img> per post otherwise) --}}
            <template x-for="g in (commentGifOpen[post.post_no] ? filteredCommentGifs() : [])" :key="g.id">
              <div class="gif-item-card" :title="g.title" role="button" tabindex="0"
                   @click="selectCommentGif(post.post_no, g.url)"
                   @keydown.enter="selectCommentGif(post.post_no, g.url)">
                <img :src="g.url" :alt="g.title" loading="lazy">
              </div>
            </template>
          </div>
        </div>

        <!-- @mention autocomplete dropdown -->
        <div x-show="mentionOpen[post.post_no] && (mentionMatches[post.post_no] || []).length > 0"
             x-cloak
             x-transition.opacity.duration.100ms
             @click.outside="mentionOpen[post.post_no] = false"
             role="listbox" aria-label="Mention family member"
             data-testid="mention-autocomplete"
             class="mention-autocomplete shadow-md">
          <div class="mention-autocomplete__header d-flex align-items-center justify-content-between px-2 py-1">
            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Mention a family member</span>
            <span style="font-size: 0.7rem;">&uarr;&darr; &bull; Enter</span>
          </div>
          <div class="mention-autocomplete__list">
            <template x-for="(m, idx) in mentionMatches[post.post_no]" :key="m.id">
              <button type="button"
                      class="mention-autocomplete__item d-flex align-items-center gap-2 w-100 text-start px-2 py-1.5 border-0"
                      :class="{ 'mention-autocomplete__item--active': (mentionIndex[post.post_no] || 0) === idx }"
                      @click="selectMention(post.post_no, m)"
                      @mouseenter="mentionIndex[post.post_no] = idx"
                      :aria-selected="(mentionIndex[post.post_no] || 0) === idx">
                <img :src="m.img ? ((m.img.startsWith('/') || m.img.startsWith('http')) ? m.img : `/resources/images/profile/${m.img}`) : '/public/avatar/avatarM.png'"
                     alt="Avatar" class="rounded-circle flex-shrink-0" width="24" height="24" style="object-fit: cover;">
                <div class="flex-grow-1 min-w-0">
                  <span class="mention-autocomplete__name text-truncate d-block" x-text="m.firstName + ' ' + m.lastName"></span>
                </div>
                <span class="badge rounded-pill mention-autocomplete__badge flex-shrink-0">Family</span>
              </button>
            </template>
          </div>
        </div>

        <div x-show="replyingTo[post.post_no]" x-cloak x-collapse class="ps-2 mb-1 text-muted" style="font-size: 0.78rem;" data-testid="comment-replying-to">
          <div class="d-flex align-items-center gap-2">
            <span>Replying to <strong x-text="replyingTo[post.post_no]?.fullName"></strong></span>
            <button type="button" class="text-muted" style="background:none; border:none; padding:0 4px; cursor:pointer;" @click="cancelReply(post.post_no)" aria-label="Cancel reply">✕</button>
          </div>
        </div>
        <form @submit.prevent="submitComment(post.post_no, $event)" class="comment-composer">
          @include('csrf')
          <div class="comment-composer__field">
            <!-- Selected GIF / Photo / Video preview (inside the field, like Facebook) -->
            <div x-show="commentGifs[post.post_no]" x-cloak class="comment-composer__gif" data-testid="comment-gif-preview">
              <template x-if="isCommentVideoMedia(commentGifs[post.post_no], post.post_no)">
                <video :src="commentGifs[post.post_no] || null" muted playsinline preload="metadata"
                       style="display:block; width:auto; height:auto; max-width:200px; max-height:120px; object-fit:contain; border-radius:8px;"
                       x-on:error="clearCommentGif(post.post_no)"></video>
              </template>
              <template x-if="!isCommentVideoMedia(commentGifs[post.post_no], post.post_no)">
                <img :src="commentGifs[post.post_no] || null" alt="Selected media"
                     style="display:block; width:auto; height:auto; max-width:200px; max-height:120px; object-fit:contain;"
                     x-on:error="clearCommentGif(post.post_no)">
              </template>
              <button type="button" @click="clearCommentGif(post.post_no)" aria-label="Remove media">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
            <!-- Selected Me-sticker preview (RFC-0011): shown as the avatar, not the raw token -->
            <div x-show="commentStickers[post.post_no]" x-cloak class="comment-composer__gif comment-composer__sticker" data-testid="comment-sticker-preview">
              <img :src="commentStickers[post.post_no]?.url || null" :alt="'My avatar: ' + (commentStickers[post.post_no]?.label || '')"
                   width="72" height="72" style="display:block; width:72px; height:72px;">
              <button type="button" @click="clearCommentSticker(post.post_no)" aria-label="Remove sticker">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>
            <div class="comment-composer__row">
              <input type="text" class="comment-composer__input"
                     placeholder="{{ __('feed.write_comment', 'Write a comment...') }}" aria-label="{{ __('feed.write_comment', 'Write a comment...') }}"
                     :data-comment-input="post.post_no"
                     x-model="commentInputs[post.post_no]" :required="!commentGifs[post.post_no] && !commentStickers[post.post_no]"
                     @input="handleCommentInput(post.post_no, $event)"
                     @paste="handleCommentPaste(post.post_no, $event)"
                     @keydown="handleCommentKeydown(post.post_no, $event)">
              <label class="comment-composer__tool mb-0 d-inline-flex align-items-center justify-content-center"
                     style="cursor: pointer; min-width: 44px; min-height: 44px;"
                     title="Attach Photo or Video to your comment" aria-label="Attach photo or video">
                <i class="bi bi-camera"></i>
                <input type="file" accept="image/*,video/mp4,video/webm,video/quicktime" class="d-none" @change="attachCommentMedia(post.post_no, $event)">
              </label>
              <button type="button" class="comment-composer__tool"
                      @click.stop="toggleCommentEmoji(post.post_no)"
                      :aria-expanded="commentEmojiOpen[post.post_no] ? 'true' : 'false'"
                      title="Add Emoji - Express with emoji reactions in this comment" aria-label="Add emoji">
                <i class="bi bi-emoji-smile"></i>
              </button>
              <button type="button" class="comment-composer__tool"
                      @click.stop="toggleCommentGif(post.post_no)"
                      :aria-expanded="commentGifOpen[post.post_no] ? 'true' : 'false'"
                      data-testid="comment-gif-btn"
                      title="Add GIF - Search and attach an animated GIF to your comment" aria-label="Add GIF">
                <i class="bi bi-filetype-gif"></i>
              </button>
            </div>
          </div>
          <button type="submit" class="comment-composer__send" aria-label="Post comment" title="Submit Comment - Publish your comment to this family post"
                  :class="((commentInputs[post.post_no] && commentInputs[post.post_no].trim()) || commentGifs[post.post_no] || commentStickers[post.post_no]) ? 'is-active' : ''">
            <i class="bi bi-send-fill"></i>
          </button>
        </form>
      </div>
    </div>

    <!-- Comments List -->
    <div class="d-flex flex-column gap-2">
      <template x-for="c in threadedComments(post)" :key="c.comment_no">
        {{-- x-for clones only ONE root element: wrap the item and its thread-collapse toggle together --}}
        <div>
        <div class="d-flex align-items-start gap-2 feed-comment-item" :id="'comment' + c.comment_no" :class="'comment' + c.comment_no" :data-comment-no="c.comment_no" :style="isReply(post, c) ? 'margin-left: 2.25rem;' : ''" :data-reply="isReply(post, c) ? 'true' : 'false'">
          <img :src="c.profileImg" alt="Avatar" class="rounded-circle flex-shrink-0 feed-comment-avatar" width="32" height="32" style="width: 32px; height: 32px; min-width: 32px; min-height: 32px; max-width: 32px; max-height: 32px; aspect-ratio: 1 / 1; object-fit: cover; margin-top: 2px; border-radius: 50%; flex-shrink: 0;">
          <div class="flex-grow-1">
            <!-- Comment Bubble -->
            <template x-if="editingCommentNo !== c.comment_no">
              <div class="px-3 py-2 rounded-3 comment-bubble-box" style="background-color: #f0f2f5; display: inline-block; max-width: 90%;">
                <div class="fw-semibold text-dark" style="font-size: 0.8125rem; line-height: 1.2;" x-text="c.fullName"></div>
                <div x-show="c.comment" class="mt-1 comment-bubble-text" style="font-size: 0.875rem; color: #050505; word-break: break-word; line-height: 1.35; font-variant-emoji: emoji;" x-html="renderCommentText(c.comment)"></div>
                <template x-if="c.gif_url">
                  <div>
                    <template x-if="isCommentVideoMedia(c)">
                      <video :src="c.gif_url" controls playsinline preload="metadata" class="mt-1 rounded-3 comment-gif" data-testid="comment-video"
                             style="display:block; width:auto; height:auto; max-width:100%; max-height:240px; object-fit:contain; background:#0b0f19;"></video>
                    </template>
                    <template x-if="!isCommentVideoMedia(c)">
                      <img :src="c.gif_url" alt="Attached media" loading="lazy" class="mt-1 rounded-3 comment-gif" data-testid="comment-gif"
                           style="display:block; width:auto; height:auto; max-width:100%; max-height:220px; object-fit:contain;">
                    </template>
                  </div>
                </template>
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

            <!-- Top Reactions Preview (Living Heirloom Glass) -->
            <template x-if="getTopCommentReactions(c.reactions).length > 0">
              <div class="d-flex align-items-center gap-1 mt-1 ps-1">
                <div class="reaction-count-pill d-inline-flex gap-1.5 align-items-center" style="background:var(--card-bg); border:1px solid var(--border-color); border-radius:20px; padding:3px 9px; font-size:0.8rem; box-shadow:0 1px 4px rgba(0,0,0,0.08);">
                  <template x-for="r in getTopCommentReactions(c.reactions)" :key="r.label">
                    <span class="d-inline-flex align-items-center gap-1">
                      <span class="d-inline-flex align-items-center justify-content-center" x-html="getHeirloomGlassSvg(r.label, 20)"></span>
                      <span x-text="r.count" style="color:var(--text-muted); font-size:0.75rem;"></span>
                    </span>
                  </template>
                </div>
              </div>
            </template>

            <!-- Comment Meta + Like Button with Reaction Bar -->
            <div class="comment-meta-row">
              <span class="comment-meta-item text-muted" x-text="formatDate(c.comment_time || c.post_time || c.date_created)"></span>

              <!-- Comment Like/React button with Reaction Bar -->
              <div class="position-relative comment-meta-item"
                   @mouseenter="toggleCommentReactionBar(c.comment_no, true)"
                   @mouseleave="toggleCommentReactionBar(c.comment_no, false)"
                   @click.outside="activeCommentReactionBars[c.comment_no] = false">

                <!-- Floating Comment Reaction Bar (Heirloom Glass) -->
                <div x-show="activeCommentReactionBars[c.comment_no]"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @mouseenter="keepCommentReactionBar(c.comment_no, true)"
                     @mouseleave="keepCommentReactionBar(c.comment_no, false)"
                     class="comment-reaction-float-bar heirloom-glass-dock"
                     :data-comment-bar="c.comment_no"
                     role="toolbar" aria-label="Choose a comment reaction"
                     style="position:absolute; bottom:calc(100% + 6px); left:0; display:flex; gap:4px; z-index:100; white-space:nowrap; padding:6px 10px;">
                  {{-- Perf: build the 6 glass-gem SVG buttons only once this comment's picker has first been opened --}}
                  <template x-for="type in ((c.comment_no in activeCommentReactionBars) ? ['love', 'like', 'haha', 'shock', 'sad', 'celebrate'] : [])" :key="type">
                    <button type="button"
                            @click.stop="reactToComment(post.post_no, c.comment_no, type, $event)"
                            :title="emojiMap[type]?.label || type"
                            :data-reaction-type="type"
                            :class="(c.userReaction === type || (type === 'shock' && c.userReaction === 'wow')) ? 'heirloom-reaction-btn active' : 'heirloom-reaction-btn'"
                            :style="'--heirloom-glow:' + (emojiMap[type]?.glow || 'rgba(225,29,72,0.45)')"
                            style="min-width:44px; min-height:44px; padding:4px;">
                      <span class="heirloom-glass-glyph d-inline-flex align-items-center justify-content-center" x-html="getHeirloomGlassSvg(type, 34)"></span>
                      <span class="heirloom-tooltip-pill" x-text="emojiMap[type]?.label || type"></span>
                    </button>
                  </template>
                </div>

                <!-- Like text button -->
                <button type="button"
                        @click="onCommentLikeClick(post.post_no, c.comment_no, c.userReaction || 'like', $event)"
                        @touchstart="startCommentLongPress(c.comment_no, $event)"
                        @touchend="endCommentTouch(post.post_no, c.comment_no, $event)"
                        @touchmove="handleCommentTouchMove(c.comment_no, $event)"
                        @touchcancel="cancelCommentLongPress()"
                        class="comment-meta-btn d-inline-flex align-items-center"
                        :class="c.userReaction ? 'fw-semibold' : 'text-muted'"
                        :style="c.userReaction ? ('color:' + getReactionColor(c.userReaction)) : ''">
                  <template x-if="c.userReaction">
                    <span x-html="getHeirloomGlassSvg(c.userReaction, 22)" class="me-1.5 d-inline-flex align-items-center justify-content-center comment-like-active-glyph"></span>
                  </template>
                  <span x-text="formatReactionLabel(c.userReaction)"></span>
                </button>
              </div>

              <!-- Dedicated Mobile Touch Emoji Reaction Trigger (Brings Emojis to Life on PWA) -->
              <button type="button"
                      class="comment-meta-btn text-muted d-inline-flex d-md-none align-items-center justify-content-center p-0"
                      @click.stop="toggleCommentReactionBar(c.comment_no, !activeCommentReactionBars[c.comment_no])"
                      title="React with emoji"
                      aria-label="React with emoji to this comment"
                      style="min-width: 32px; min-height: 32px; font-size: 0.95rem; line-height: 1;">
                <span aria-hidden="true">✨</span>
              </button>

              <button type="button" class="comment-meta-btn text-muted" data-testid="comment-reply-btn" @click="startReply(post, c)">Reply</button>
              <template x-if="canEditComment(c)">
                <button type="button" class="comment-meta-btn text-muted" @click="startEditComment(c)">Edit</button>
              </template>
              <template x-if="canModerateComment(post, c)">
                <button type="button" class="comment-meta-btn text-muted" :title="canEditComment(c) ? 'Delete your comment' : 'Delete comment from your post'" @click="deleteComment(post.post_no, c.comment_no)">Delete</button>
              </template>
              <template x-if="!canEditComment(c)">
                <button type="button" class="comment-meta-btn text-danger" title="Report abusive comment" @click="openReportModal('comment', c.comment_no)">Flag</button>
              </template>

              <!-- Comment View Count (Author-clickable) -->
              <span class="comment-meta-item comment-views-count text-muted"
                    :class="canEditComment(c) ? 'cursor-pointer' : ''"
                    :role="canEditComment(c) ? 'button' : undefined"
                    :tabindex="canEditComment(c) ? '0' : undefined"
                    @click="canEditComment(c) && openCommentViewersModal(c)"
                    @keydown.enter="canEditComment(c) && openCommentViewersModal(c)"
                    :title="canEditComment(c) ? 'Click to see who viewed your comment' : ((c.views_count || 0) + ' views')">
                <i class="bi bi-eye" :class="canEditComment(c) ? 'text-primary' : ''" style="font-size: 0.8rem; line-height: 1; margin-right: 4px;"></i>
                <span x-text="c.views_count || 0"></span>
                <span class="ms-1" x-text="(c.views_count === 1) ? 'view' : 'views'"></span>
              </span>
            </div>
          </div>
        </div>

        <!-- Thread collapse toggle (shown under the last visible item of a thread with > 2 replies) -->
        <template x-if="isLastVisibleInThread(post, c) && getTotalReplyCount(post, getThreadRoot(post, c)) > 2">
          <div class="mt-1" style="margin-left: 2.25rem;">
            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 d-inline-flex align-items-center gap-1.5 fw-semibold comment-thread-toggle"
                    data-testid="thread-collapse-toggle"
                    style="font-size: 0.78rem; color: var(--primary-color, #a31621); min-height: 32px;"
                    @click="toggleThread(getThreadRoot(post, c).comment_no)">
              <template x-if="!threadExpanded[String(getThreadRoot(post, c).comment_no)]">
                <span class="d-inline-flex align-items-center gap-1">
                  <i class="bi bi-arrow-return-right"></i>
                  <span>View </span><span x-text="getHiddenReplyCount(post, getThreadRoot(post, c))"></span><span> more </span>
                  <span x-text="getHiddenReplyCount(post, getThreadRoot(post, c)) === 1 ? 'reply' : 'replies'"></span>
                </span>
              </template>
              <template x-if="threadExpanded[String(getThreadRoot(post, c).comment_no)]">
                <span class="d-inline-flex align-items-center gap-1 text-muted">
                  <i class="bi bi-chevron-up"></i>
                  <span>Collapse replies</span>
                </span>
              </template>
            </button>
          </div>
        </template>
        </div>
      </template>
    </div>
  </div>
