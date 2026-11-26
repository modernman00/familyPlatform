<style>
  [x-cloak] { display: none !important; }
  .fp-spark-card {
    border-radius: 20px;
    background-color: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    color: var(--text-color, #0f172a);
    overflow: visible;
  }
  .fp-spark-header {
    border-top-left-radius: 19px;
    border-top-right-radius: 19px;
    background: linear-gradient(135deg, #5c0a12 0%, #8a1019 100%);
    color: #ffffff;
  }
  .fp-spark-header > div:first-child span {
    color: #ffffff;
  }
  .fp-spark-header .btn-warning,
  .fp-spark-header .btn-warning i,
  .fp-spark-header .btn-warning span {
    color: #0f172a !important;
  }
  .fp-spark-badge {
    background-color: rgba(254, 243, 199, 0.18);
    border: 1px solid rgba(252, 211, 77, 0.55);
    color: #fef3c7 !important;
    font-size: var(--text-xs, 0.75rem);
    letter-spacing: 0.04em;
    white-space: nowrap;
    flex-shrink: 0;
  }
  .fp-spark-byline {
    font-size: var(--text-xs, 0.75rem);
    font-weight: 600;
    color: #fef3c7 !important;
    opacity: 0.92;
  }
  .fp-spark-title {
    font-size: var(--text-base, 1rem);
    font-weight: 700;
    line-height: 1.35;
    letter-spacing: -0.01em;
    color: var(--text-main, #0f172a);
    overflow-wrap: anywhere;
  }
  .fp-spark-prompt {
    font-size: var(--text-sm, 0.875rem);
    line-height: 1.5;
    color: var(--text-muted-aa, #475569);
    overflow-wrap: anywhere;
  }
  .fp-spark-btn-action {
    min-height: var(--tap-min, 44px);
    min-width: var(--tap-min, 44px);
    font-size: var(--text-sm, 0.875rem) !important;
    font-weight: 600;
    line-height: 1.25;
    white-space: nowrap;
    flex-shrink: 0;
    transition: transform 0.15s ease, filter 0.15s ease;
  }
  .fp-spark-btn-action:active {
    transform: scale(0.98);
  }
  .fp-spark-option-btn {
    min-height: var(--tap-min, 44px);
    border-radius: 12px;
    border: 1.5px solid var(--border-color, #cbd5e1);
    background-color: var(--card-bg, #ffffff);
    color: var(--text-color, #0f172a);
    font-size: var(--text-sm, 0.875rem);
    line-height: 1.35;
    text-align: left;
    transition: border-color 0.15s ease, transform 0.15s ease;
  }
  .fp-spark-option-pct {
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    font-style: normal !important;
    color: var(--text-muted-aa, #475569);
  }
  .fp-spark-meta {
    font-size: var(--text-xs, 0.75rem);
    font-weight: 500;
    line-height: 1.4;
    color: var(--text-muted-aa, #475569);
  }
  .fp-spark-option-btn.is-selected {
    border-color: #a31621;
    background-color: rgba(163, 22, 33, 0.06);
  }
  .fp-spark-option-btn:active {
    transform: scale(0.98);
  }
  .fp-spark-reply-bubble {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #0f172a !important;
    font-style: normal !important;
    border-radius: 14px;
  }
  .fp-spark-empty-title {
    font-size: var(--text-sm, 0.875rem);
    font-weight: 600;
    color: var(--text-main, #0f172a);
  }
  .fp-spark-empty-sub {
    font-size: var(--text-xs, 0.75rem);
    line-height: 1.45;
    color: var(--text-muted-aa, #475569);
  }
  .fp-spark-reply-author {
    font-size: var(--text-sm, 0.875rem);
    font-style: normal !important;
    color: #0f172a !important;
  }
  .fp-spark-reply-edited {
    font-size: var(--text-xs, 0.75rem);
    font-style: normal !important;
    color: var(--text-muted-aa, #475569);
  }
  .fp-spark-reply-body {
    font-size: var(--text-sm, 0.875rem);
    line-height: 1.5;
    font-style: normal !important;
    color: #1e293b !important;
  }
  .fp-spark-modal-title {
    font-size: var(--text-lg, 1.25rem);
    font-weight: 700;
    line-height: 1.3;
  }
  .fp-spark-modal-label {
    font-size: var(--text-sm, 0.875rem);
    font-weight: 600;
    font-style: normal !important;
    color: var(--text-main, #0f172a);
    margin-bottom: 6px;
  }
  /* Neutralise main.css/form.scss global uppercase placeholder, retina 16px lock, and tiny italic .small rules */
  .fp-spark-card .small {
    font-size: var(--text-xs, 0.75rem);
    font-style: normal !important;
  }
  .fp-spark-card input[type="text"].form-control,
  .fp-spark-card textarea.form-control {
    font-size: var(--text-sm, 0.875rem) !important;
    line-height: 1.4;
  }
  @media (max-width: 767.98px) and (pointer: coarse) {
    .fp-spark-card input[type="text"].form-control,
    .fp-spark-card textarea.form-control {
      font-size: max(16px, 1rem) !important;
    }
  }
  .fp-spark-card .form-control::-webkit-input-placeholder,
  .fp-spark-card input::-webkit-input-placeholder,
  .fp-spark-card textarea::-webkit-input-placeholder {
    text-transform: none !important;
    font-size: var(--text-sm, 0.875rem) !important;
    font-style: normal !important;
    letter-spacing: normal !important;
    color: var(--text-muted-aa, #475569) !important;
    opacity: 0.85;
  }
  .fp-spark-card .form-control::-moz-placeholder,
  .fp-spark-card input::-moz-placeholder,
  .fp-spark-card textarea::-moz-placeholder {
    text-transform: none !important;
    font-size: var(--text-sm, 0.875rem) !important;
    font-style: normal !important;
    letter-spacing: normal !important;
    color: var(--text-muted-aa, #475569) !important;
    opacity: 0.85;
  }
  .fp-spark-card .form-control::placeholder,
  .fp-spark-card input::placeholder,
  .fp-spark-card textarea::placeholder {
    text-transform: none !important;
    font-size: var(--text-sm, 0.875rem) !important;
    font-style: normal !important;
    letter-spacing: normal !important;
    color: var(--text-muted-aa, #475569) !important;
    opacity: 0.85;
  }

  /* 5-Selector Dark Mode Parity */
  body.dark-mode .fp-spark-card,
  body.dark-theme .fp-spark-card,
  [data-bs-theme="dark"] .fp-spark-card,
  html.dark .fp-spark-card,
  html.dark-mode .fp-spark-card {
    background-color: var(--card-bg, #1e293b);
    border-color: var(--border-color, #334155);
    color: var(--text-color, #f8fafc);
  }
  body.dark-mode .fp-spark-title,
  body.dark-theme .fp-spark-title,
  [data-bs-theme="dark"] .fp-spark-title,
  html.dark .fp-spark-title,
  html.dark-mode .fp-spark-title,
  body.dark-mode .fp-spark-empty-title,
  body.dark-theme .fp-spark-empty-title,
  [data-bs-theme="dark"] .fp-spark-empty-title,
  html.dark .fp-spark-empty-title,
  html.dark-mode .fp-spark-empty-title,
  body.dark-mode .fp-spark-modal-label,
  body.dark-theme .fp-spark-modal-label,
  [data-bs-theme="dark"] .fp-spark-modal-label,
  html.dark .fp-spark-modal-label,
  html.dark-mode .fp-spark-modal-label {
    color: #f8fafc !important;
  }
  body.dark-mode .fp-spark-prompt,
  body.dark-theme .fp-spark-prompt,
  [data-bs-theme="dark"] .fp-spark-prompt,
  html.dark .fp-spark-prompt,
  html.dark-mode .fp-spark-prompt,
  body.dark-mode .fp-spark-meta,
  body.dark-theme .fp-spark-meta,
  [data-bs-theme="dark"] .fp-spark-meta,
  html.dark .fp-spark-meta,
  html.dark-mode .fp-spark-meta,
  body.dark-mode .fp-spark-option-pct,
  body.dark-theme .fp-spark-option-pct,
  [data-bs-theme="dark"] .fp-spark-option-pct,
  html.dark .fp-spark-option-pct,
  html.dark-mode .fp-spark-option-pct,
  body.dark-mode .fp-spark-empty-sub,
  body.dark-theme .fp-spark-empty-sub,
  [data-bs-theme="dark"] .fp-spark-empty-sub,
  html.dark .fp-spark-empty-sub,
  html.dark-mode .fp-spark-empty-sub,
  body.dark-mode .fp-spark-reply-edited,
  body.dark-theme .fp-spark-reply-edited,
  [data-bs-theme="dark"] .fp-spark-reply-edited,
  html.dark .fp-spark-reply-edited,
  html.dark-mode .fp-spark-reply-edited {
    color: #cbd5e1 !important;
  }
  body.dark-mode .fp-spark-option-btn,
  body.dark-theme .fp-spark-option-btn,
  [data-bs-theme="dark"] .fp-spark-option-btn,
  html.dark .fp-spark-option-btn,
  html.dark-mode .fp-spark-option-btn {
    background-color: #0f172a;
    border-color: #475569;
    color: #f8fafc;
  }
  body.dark-mode .fp-spark-option-btn.is-selected,
  body.dark-theme .fp-spark-option-btn.is-selected,
  [data-bs-theme="dark"] .fp-spark-option-btn.is-selected,
  html.dark .fp-spark-option-btn.is-selected,
  html.dark-mode .fp-spark-option-btn.is-selected {
    border-color: #f87171;
    background-color: rgba(163, 22, 33, 0.22);
  }
  body.dark-mode .fp-spark-reply-bubble,
  body.dark-theme .fp-spark-reply-bubble,
  [data-bs-theme="dark"] .fp-spark-reply-bubble,
  html.dark .fp-spark-reply-bubble,
  html.dark-mode .fp-spark-reply-bubble {
    background-color: #0f172a;
    border-color: #334155;
    color: #f8fafc !important;
  }
  body.dark-mode .fp-spark-reply-author,
  body.dark-theme .fp-spark-reply-author,
  [data-bs-theme="dark"] .fp-spark-reply-author,
  html.dark .fp-spark-reply-author,
  html.dark-mode .fp-spark-reply-author {
    color: #f8fafc !important;
  }
  body.dark-mode .fp-spark-reply-body,
  body.dark-theme .fp-spark-reply-body,
  [data-bs-theme="dark"] .fp-spark-reply-body,
  html.dark .fp-spark-reply-body,
  html.dark-mode .fp-spark-reply-body {
    color: #e2e8f0 !important;
  }
  body.dark-mode .fp-spark-card .form-control::placeholder,
  body.dark-theme .fp-spark-card .form-control::placeholder,
  [data-bs-theme="dark"] .fp-spark-card .form-control::placeholder,
  html.dark .fp-spark-card .form-control::placeholder,
  html.dark-mode .fp-spark-card .form-control::placeholder {
    color: #94a3b8 !important;
  }
  .fp-spark-delete-btn {
    color: #b91c1c;
    border-color: #b91c1c;
  }
  body.dark-mode .fp-spark-delete-btn,
  body.dark-theme .fp-spark-delete-btn,
  [data-bs-theme="dark"] .fp-spark-delete-btn,
  html.dark .fp-spark-delete-btn,
  html.dark-mode .fp-spark-delete-btn {
    color: #fca5a5 !important;
    border-color: #fca5a5 !important;
  }
  .fp-spark-skeleton {
    height: 16px;
    border-radius: 8px;
    background: linear-gradient(90deg, rgba(148,163,184,0.18) 25%, rgba(148,163,184,0.32) 50%, rgba(148,163,184,0.18) 75%);
    background-size: 200% 100%;
    animation: fpSparkPulse 1.4s ease-in-out infinite;
  }
  @keyframes fpSparkPulse {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }
</style>

<section class="card shadow-sm mb-4 fp-spark-card"
         id="dailyFamilySparkCard"
         data-id="dailyFamilySparkCard"
         aria-label="Daily Family Spark"
         x-data="dailyFamilySparkWidget()"
         x-init="initSpark()"
         x-cloak>

  <!-- Crimson Branded Header (Strict WCAG AA White/Gold on Crimson) -->
  <div class="fp-spark-header px-3 px-sm-4 py-3 d-flex align-items-center justify-content-between gap-2 flex-wrap">
    <div class="d-flex align-items-center gap-2 min-w-0">
      <span class="badge rounded-pill px-3 py-1 fw-bold fp-spark-badge"
            x-text="spark ? spark.badge_label : 'DAILY SPARK'"></span>
      <span class="fp-spark-byline"
            x-text="spark ? ('By ' + spark.created_by_name) : 'Loading today\'s spark...'"></span>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button type="button"
              class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 fp-spark-btn-action d-inline-flex align-items-center gap-2"
              @click.stop="openCreateModal()">
        <i class="bi bi-stars" aria-hidden="true"></i>
        <span>Spark a Question</span>
      </button>
    </div>
  </div>

  <!-- Card Body -->
  <div class="card-body p-3 p-sm-4">

    <!-- State 6: Loading Skeleton -->
    <template x-if="loading && !loaded">
      <div class="d-flex flex-column gap-3 py-2" aria-busy="true">
        <div class="fp-spark-skeleton" style="width: 55%; height: 22px;"></div>
        <div class="fp-spark-skeleton" style="width: 90%;"></div>
        <div class="fp-spark-skeleton" style="width: 75%;"></div>
        <div class="d-flex gap-2 mt-1">
          <div class="fp-spark-skeleton" style="width: 120px; height: 44px; border-radius: 999px;"></div>
          <div class="fp-spark-skeleton" style="width: 140px; height: 44px; border-radius: 999px;"></div>
        </div>
      </div>
    </template>

    <!-- State 5: Error / Offline Banner with Retry -->
    <template x-if="errorMessage">
      <div class="alert alert-danger d-flex align-items-center justify-content-between gap-2 rounded-3 mb-3" role="alert">
        <span class="fw-semibold" style="font-size: var(--text-sm, 0.875rem);" x-text="errorMessage"></span>
        <button type="button"
                class="btn btn-sm btn-outline-danger fp-spark-btn-action px-3"
                @click="loadSpark()">
          Retry
        </button>
      </div>
    </template>

    <!-- Loaded Spark Content -->
    <template x-if="loaded && spark">
      <div class="d-flex flex-column gap-3">
        <!-- Title & Prompt (Zero mid-word truncation) -->
        <div>
          <h3 class="fw-bold mb-1 fp-spark-title"
              x-text="spark.title"></h3>
          <p class="mb-0 fp-spark-prompt"
             x-text="spark.prompt_text"></p>
        </div>

        <!-- Optional Archive Flashback Photo -->
        <template x-if="spark.media_url">
          <div class="rounded-3 overflow-hidden border" style="max-height: 280px; background: #0f172a;">
            <img :src="sparkMediaUrl(spark.media_url)"
                 alt="Family archive memory"
                 width="640"
                 height="280"
                 class="w-100 object-fit-cover"
                 style="max-height: 280px;"
                 loading="lazy"
                 decoding="async">
          </div>
        </template>

        <!-- 1-Tap Poll / Game Options -->
        <template x-if="spark.options && spark.options.length > 0">
          <div class="d-flex flex-column gap-2" role="group" aria-label="Spark poll options">
            <template x-for="opt in spark.options" :key="opt.index">
              <button type="button"
                      class="w-100 px-3 py-2 fp-spark-option-btn position-relative overflow-hidden d-flex align-items-center justify-content-between gap-2"
                      :class="{ 'is-selected': spark.user_vote_index === opt.index }"
                      :disabled="votingOption !== null"
                      @click="castVote(opt.index)">
                <div class="position-relative z-1 d-flex align-items-center gap-2 min-w-0 flex-grow-1">
                  <span class="spinner-border spinner-border-sm text-danger"
                        role="status"
                        aria-hidden="true"
                        x-show="votingOption === opt.index"></span>
                  <i class="bi"
                     :class="spark.user_vote_index === opt.index ? 'bi-check-circle-fill text-danger' : 'bi-circle text-secondary'"
                     x-show="votingOption !== opt.index"
                     aria-hidden="true"></i>
                  <span class="fw-semibold" style="overflow-wrap: anywhere; text-align: left;" x-text="opt.label"></span>
                </div>
                <span class="position-relative z-1 fp-spark-option-pct flex-shrink-0"
                      x-show="spark.total_votes > 0"
                      x-text="opt.percentage + '% (' + opt.votes + ')'"></span>
              </button>
            </template>
            <div class="fp-spark-meta d-flex align-items-center justify-content-between flex-wrap gap-2">
              <span x-text="spark.total_votes + (spark.total_votes === 1 ? ' family vote' : ' family votes')"></span>
              <span x-show="spark.user_vote_index !== null">Tap another option anytime to change your vote</span>
            </div>
          </div>
        </template>

        <!-- Action Toolbar (≥44px tap targets, ≥8px gaps) -->
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-1">
          <button type="button"
                  class="btn btn-outline-secondary rounded-pill px-3 fp-spark-btn-action d-inline-flex align-items-center gap-2"
                  @click="showReplies = !showReplies"
                  :aria-expanded="showReplies.toString()">
            <i class="bi bi-chat-dots-fill" aria-hidden="true"></i>
            <span x-text="spark.total_replies + (spark.total_replies === 1 ? ' Reply' : ' Replies')"></span>
          </button>

          <a :href="spark.whatsapp_url"
             target="_blank"
             rel="noopener noreferrer"
             class="btn btn-success rounded-pill px-3 fp-spark-btn-action d-inline-flex align-items-center gap-2 text-white">
            <i class="bi bi-whatsapp" aria-hidden="true"></i>
            <span>Share to Family WhatsApp</span>
          </a>
        </div>

        <!-- Aggregated Living Replies Drawer (Smooth Alpine Collapse) -->
        <div x-show="showReplies" x-collapse>
          <div class="pt-2 border-top d-flex flex-column gap-2">

            <!-- State 3: Empty Replies State -->
            <template x-if="!spark.replies || spark.replies.length === 0">
              <div class="p-3 rounded-3 text-center fp-spark-reply-bubble">
                <p class="mb-1 fp-spark-empty-title">No family replies yet.</p>
                <p class="mb-0 fp-spark-empty-sub">Start the conversation below—your reply stays inside this single Living Spark Card.</p>
              </div>
            </template>

            <!-- Aggregated Replies List -->
            <template x-for="reply in spark.replies" :key="reply.id">
              <div class="p-3 fp-spark-reply-bubble d-flex align-items-start gap-3">
                <img :src="reply.author_avatar || '/resources/images/profile/avatarM.png'"
                     alt=""
                     class="rounded-circle flex-shrink-0 object-fit-cover"
                     style="width: 40px; height: 40px;">
                <div class="min-w-0 flex-grow-1">
                  <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                      <span class="fw-bold fp-spark-reply-author" style="overflow-wrap: anywhere;" x-text="reply.author_name"></span>
                      <span class="fp-spark-reply-edited" x-show="reply.updated_at">(edited)</span>
                    </div>
                    <template x-if="spark.current_user_id && String(reply.user_id) === String(spark.current_user_id)">
                      <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary rounded-pill px-3 fp-spark-btn-action d-inline-flex align-items-center gap-1"
                                @click="startEditReply(reply)"
                                :disabled="editingReplyId === reply.id || deletingReplyId === reply.id"
                                aria-label="Edit reply"
                                title="Edit your reply">
                          <i class="bi bi-pencil" aria-hidden="true"></i>
                          <span>Edit</span>
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger rounded-pill px-3 fp-spark-btn-action fp-spark-delete-btn d-inline-flex align-items-center gap-1"
                                @click="deleteReply(reply)"
                                :disabled="deletingReplyId === reply.id"
                                aria-label="Delete reply"
                                title="Delete your reply">
                          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" x-show="deletingReplyId === reply.id"></span>
                          <i class="bi bi-trash" aria-hidden="true" x-show="deletingReplyId !== reply.id"></i>
                          <span x-text="deletingReplyId === reply.id ? 'Deleting...' : 'Delete'"></span>
                        </button>
                      </div>
                    </template>
                  </div>

                  <!-- Display Mode -->
                  <template x-if="editingReplyId !== reply.id">
                    <p class="mb-0 mt-1 fp-spark-reply-body" style="overflow-wrap: anywhere;" x-html="formatSparkReplyBody(reply.body)"></p>
                  </template>

                  <!-- Inline Edit Mode -->
                  <template x-if="editingReplyId === reply.id">
                    <form @submit.prevent="saveEditReply(reply)" class="mt-2 d-flex flex-column gap-2">
                      @include('csrf')
                      <label :for="'sparkEditInput_' + reply.id" class="visually-hidden">Edit your reply</label>
                      <input :id="'sparkEditInput_' + reply.id"
                             type="text"
                             class="form-control rounded-pill px-3"
                             style="min-height: 44px;"
                             maxlength="500"
                             x-model="editReplyText"
                             :disabled="savingEditReplyId === reply.id"
                             required>
                      <div class="d-flex align-items-center gap-2 flex-wrap">
                        <button type="submit"
                                class="btn btn-danger rounded-pill px-3 fw-semibold fp-spark-btn-action d-inline-flex align-items-center gap-2 text-white"
                                :disabled="savingEditReplyId === reply.id || editReplyText.trim() === ''">
                          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" x-show="savingEditReplyId === reply.id"></span>
                          <span x-text="savingEditReplyId === reply.id ? 'Saving...' : 'Save'"></span>
                        </button>
                        <button type="button"
                                class="btn btn-outline-secondary rounded-pill px-3 fp-spark-btn-action"
                                @click="cancelEditReply()"
                                :disabled="savingEditReplyId === reply.id">
                          Cancel
                        </button>
                      </div>
                    </form>
                  </template>
                </div>
              </div>
            </template>

            <!-- Inline Reply Form with @Mention Autocomplete & Emoji Studio Picker (Universal CSRF + Universal Button Spinner) -->
            <div class="position-relative mt-1" style="overflow: visible;">
              <!-- Emoji Picker Popup (Standard + Living 3D + Me Stickers) -->
              <div x-show="sparkEmojiOpen"
                   x-cloak
                   x-transition.opacity.duration.120ms
                   @click.outside="sparkEmojiOpen = false"
                   @keydown.escape.window="sparkEmojiOpen = false"
                   role="dialog"
                   aria-label="Choose an emoji"
                   data-testid="spark-emoji-picker"
                   class="comment-picker comment-picker--emoji">
                <!-- Fitzpatrick Skin Tone Palette -->
                <div :class="sparkEmojiTab === 'me' ? 'd-none' : 'd-flex'"
                     class="align-items-center justify-content-between px-2 py-1 border-bottom mb-1 bg-light rounded"
                     role="toolbar"
                     aria-label="Spark emoji skin tone diversity selector">
                  <span class="text-muted fw-semibold" style="font-size: 0.68rem;">Skin:</span>
                  <div class="d-flex align-items-center gap-1">
                    <template x-for="st in sparkSkinTones" :key="st.tone">
                      <button type="button"
                              class="btn btn-sm p-0 border-0 rounded-circle"
                              :class="sparkSkinTone === st.tone ? 'shadow-sm border border-primary' : 'opacity-75'"
                              style="font-size: 0.82rem; width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer;"
                              :title="st.label"
                              :aria-label="st.label"
                              @click.stop="setSparkSkinTone(st.tone)"
                              x-text="st.swatch"></button>
                    </template>
                  </div>
                </div>

                <div class="comment-emoji-tabs" role="tablist" aria-label="Emoji type">
                  <button type="button" class="comment-emoji-tab" role="tab"
                          :class="sparkEmojiTab === 'standard' ? 'active' : ''"
                          :aria-selected="sparkEmojiTab === 'standard' ? 'true' : 'false'"
                          @click.stop="sparkEmojiTab = 'standard'">😀 Standard</button>
                  <button type="button" class="comment-emoji-tab" role="tab"
                          :class="sparkEmojiTab === 'living' ? 'active' : ''"
                          :aria-selected="sparkEmojiTab === 'living' ? 'true' : 'false'"
                          @click.stop="sparkEmojiTab = 'living'">✨ Living</button>
                  <button type="button" class="comment-emoji-tab" role="tab"
                          :class="sparkEmojiTab === 'me' ? 'active' : ''"
                          :aria-selected="sparkEmojiTab === 'me' ? 'true' : 'false'"
                          @click.stop="openSparkMeTab()">🙋 Me</button>
                </div>

                <template x-if="sparkEmojiOpen && sparkEmojiTab === 'me'">
                  <div class="me-sticker-grid me-sticker-grid--comment">
                    <span class="me-sticker-hint" x-show="sparkMeStickers && sparkMeStickers.available">Your avatar comes from <a href="/member/emoji-studio">Emoji Studio</a>.</span>
                    <span class="text-muted small" x-show="!sparkMeStickers && !sparkMeStickersError">Loading your avatar…</span>
                    <span class="text-muted small" x-show="sparkMeStickersError">Could not load your avatar.</span>
                    <template x-if="sparkMeStickers && !sparkMeStickers.available">
                      <a href="/member/emoji-studio" class="btn btn-sm btn-warning rounded-pill fw-semibold me-sticker-unlock">🎨 Design your avatar in Emoji Studio</a>
                    </template>
                    <template x-for="st in (sparkMeStickers && sparkMeStickers.available ? sparkMeStickers.stickers : [])" :key="st.reaction">
                      <button type="button" class="me-sticker-btn" :title="st.label" :aria-label="'Add my avatar: ' + st.label"
                              @click="selectSparkMeSticker(st.token, st.url, st.label)">
                        <img :src="st.url" width="52" height="52" alt="" loading="lazy">
                        <span x-text="st.label"></span>
                      </button>
                    </template>
                  </div>
                </template>

                <div class="comment-emoji-grid" x-show="sparkEmojiTab === 'standard'">
                  <template x-for="emoji in sparkEmojiPalette" :key="emoji">
                    <button type="button" class="comment-emoji-btn"
                            @click="insertSparkEmoji(emoji)"
                            :aria-label="'Insert ' + emoji"
                            x-text="emoji"></button>
                  </template>
                </div>

                <template x-if="sparkEmojiOpen && sparkEmojiTab === 'living'">
                  <div class="comment-living-grid">
                    <template x-for="le in sparkLivingPalette" :key="le.id">
                      <button type="button" class="comment-living-btn" :data-emoji-id="le.id"
                              @click="insertSparkLivingEmoji(le.id)"
                              :title="le.title"
                              :aria-label="'Insert living emoji ' + le.title">
                        <img :src="'/public/img/emoji/' + le.id + '.svg?v=20261107a'" :alt="le.title" width="38" height="38" loading="lazy" class="atg-3d-emoji">
                      </button>
                    </template>
                  </div>
                </template>
              </div>

              <!-- @Mention Autocomplete Dropdown -->
              <div x-show="sparkMentionOpen && sparkMentionMatches.length > 0"
                   x-cloak
                   x-transition.opacity.duration.100ms
                   @click.outside="sparkMentionOpen = false"
                   role="listbox"
                   aria-label="Mention a family member"
                   class="mention-autocomplete shadow-lg">
                <div class="mention-autocomplete__header d-flex align-items-center justify-content-between px-2 py-1">
                  <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Mention a family member</span>
                  <span style="font-size: 0.7rem;">&uarr;&darr; &bull; Enter</span>
                </div>
                <div class="mention-autocomplete__list">
                  <template x-for="(m, idx) in sparkMentionMatches" :key="m.id">
                    <button type="button"
                            class="mention-autocomplete__item d-flex align-items-center gap-2 w-100 text-start px-2 py-1.5 border-0"
                            :class="{ 'mention-autocomplete__item--active': sparkMentionIndex === idx }"
                            @click="selectSparkMention(m)"
                            @mouseenter="sparkMentionIndex = idx"
                            :aria-selected="sparkMentionIndex === idx">
                      <img :src="m.img ? ((m.img.startsWith('/') || m.img.startsWith('http')) ? m.img : '/resources/images/profile/' + m.img) : '/resources/images/profile/avatarM.png'"
                           alt="" class="rounded-circle flex-shrink-0" width="26" height="26" style="object-fit: cover;">
                      <div class="flex-grow-1 min-w-0">
                        <span class="mention-autocomplete__name text-truncate d-block" x-text="m.firstName + ' ' + m.lastName"></span>
                      </div>
                      <span class="badge rounded-pill mention-autocomplete__badge flex-shrink-0">Family</span>
                    </button>
                  </template>
                </div>
              </div>

              <!-- Selected Me-Sticker Preview Chip -->
              <div x-show="sparkSticker" x-cloak class="comment-composer__gif comment-composer__sticker mb-2">
                <img :src="sparkSticker?.url || null" :alt="'My avatar: ' + (sparkSticker?.label || '')" width="64" height="64" style="display:block; width:64px; height:64px;">
                <button type="button" @click="sparkSticker = null" aria-label="Remove sticker">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>

              <form @submit.prevent="submitReply()" class="d-flex align-items-stretch gap-2">
                @include('csrf')
                <label for="sparkReplyInput" class="visually-hidden">Write a family reply</label>
                <input id="sparkReplyInput"
                       type="text"
                       class="form-control rounded-pill px-3"
                       style="min-height: 44px;"
                       maxlength="500"
                       placeholder="Reply or @tag someone..."
                       x-model="replyText"
                       @input="handleSparkReplyInput($event)"
                       @keydown="handleSparkReplyKeydown($event)"
                       :disabled="submittingReply">
                <button type="button"
                        class="btn btn-outline-secondary rounded-pill fp-spark-btn-action d-inline-flex align-items-center justify-content-center"
                        style="min-width: 44px; min-height: 44px;"
                        @click.stop="sparkEmojiOpen = !sparkEmojiOpen"
                        :aria-expanded="sparkEmojiOpen ? 'true' : 'false'"
                        data-testid="spark-emoji-btn"
                        title="Add Emoji - Standard, Living 3D & Me stickers"
                        aria-label="Add emoji">
                  <i class="bi bi-emoji-smile" aria-hidden="true"></i>
                </button>
                <button type="submit"
                        class="btn btn-danger rounded-pill px-4 fw-semibold fp-spark-btn-action d-inline-flex align-items-center justify-content-center gap-2 text-white"
                        :disabled="submittingReply || (replyText.trim() === '' && !sparkSticker)">
                  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" x-show="submittingReply"></span>
                  <span x-text="submittingReply ? 'Sending...' : 'Reply'"></span>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>

  <!-- "Spark a Question" Modal (WCAG Focus Trap + Universal CSRF + Spinner) -->
  <div x-show="showCreateModal"
       x-trap.noscroll="showCreateModal"
       @keydown.escape.window="showCreateModal = false"
       :class="showCreateModal ? 'd-flex' : 'd-none'"
       class="position-fixed top-0 start-0 w-100 h-100 align-items-center justify-content-center p-3"
       style="background: rgba(15, 23, 42, 0.72); z-index: 1080;"
       role="dialog"
       aria-modal="true"
       aria-labelledby="sparkCreateModalTitle">
    <div class="card shadow-lg w-100 fp-spark-card" style="max-width: 520px;" @click.outside="showCreateModal = false">
      <div class="fp-spark-header px-4 py-3 d-flex align-items-center justify-content-between gap-2">
        <h3 id="sparkCreateModalTitle" class="mb-0 fp-spark-modal-title">Spark a Family Question</h3>
        <button type="button"
                class="btn-close btn-close-white fp-spark-btn-action"
                aria-label="Close modal"
                @click="showCreateModal = false"></button>
      </div>
      <form @submit.prevent="createSpark()" class="card-body p-4 d-flex flex-column gap-3">
        @include('csrf')
        <template x-if="createErrorMessage">
          <div class="alert alert-danger py-2 px-3 mb-0 fw-semibold rounded-3" style="font-size: var(--text-sm, 0.875rem);" role="alert" x-text="createErrorMessage"></div>
        </template>
        <div>
          <label for="sparkTitleInput" class="form-label fp-spark-modal-label">Headline</label>
          <input id="sparkTitleInput"
                 type="text"
                 class="form-control"
                 style="min-height: 44px;"
                 maxlength="140"
                 required
                 placeholder="e.g. Best Family Holiday Memory"
                 x-model="newSparkTitle">
        </div>
        <div>
          <label for="sparkPromptInput" class="form-label fp-spark-modal-label">Your Question</label>
          <textarea id="sparkPromptInput"
                    class="form-control"
                    rows="3"
                    maxlength="500"
                    required
                    placeholder="Ask something that gets everyone talking..."
                    x-model="newSparkPrompt"></textarea>
        </div>
        <div>
          <label for="sparkOptionsInput" class="form-label fp-spark-modal-label">Optional Quick Poll Choices (comma-separated, up to 4)</label>
          <input id="sparkOptionsInput"
                 type="text"
                 class="form-control"
                 style="min-height: 44px;"
                 placeholder="e.g. Beach, Countryside, City Break"
                 x-model="newSparkOptions">
        </div>
        <div class="d-flex align-items-center justify-content-end gap-2 pt-2">
          <button type="button"
                  class="btn btn-outline-secondary rounded-pill px-3 fp-spark-btn-action"
                  @click="showCreateModal = false">Cancel</button>
          <button type="submit"
                  class="btn btn-danger rounded-pill px-4 fw-semibold fp-spark-btn-action d-inline-flex align-items-center gap-2 text-white"
                  :disabled="submittingCreate">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" x-show="submittingCreate"></span>
            <span x-text="submittingCreate ? 'Publishing...' : 'Publish Spark'"></span>
          </button>
        </div>
      </form>
    </div>
  </div>
</section>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
function dailyFamilySparkWidget() {
  return {
    loading: true,
    loaded: false,
    errorMessage: '',
    spark: null,
    showReplies: true,
    votingOption: null,
    submittingReply: false,
    replyText: '',
    editingReplyId: null,
    editReplyText: '',
    savingEditReplyId: null,
    deletingReplyId: null,
    sparkFamilyRoster: [],
    sparkMentionOpen: false,
    sparkMentionMatches: [],
    sparkMentionIndex: 0,
    sparkEmojiOpen: false,
    sparkEmojiTab: 'standard',
    sparkSkinTone: (typeof localStorage !== 'undefined' ? (parseInt(localStorage.getItem('fp_skin_tone') || '0', 10) || 0) : 0),
    sparkSkinTones: [
      { tone: 0, swatch: '🟡', label: 'Default' },
      { tone: 1, swatch: '🏻', label: 'Light' },
      { tone: 2, swatch: '🏼', label: 'Medium-Light' },
      { tone: 3, swatch: '🏽', label: 'Medium' },
      { tone: 4, swatch: '🏾', label: 'Medium-Dark' },
      { tone: 5, swatch: '🏿', label: 'Dark' },
    ],
    sparkEmojiItems: [
      { base: '👍', skins: ['👍', '👍🏻', '👍🏼', '👍🏽', '👍🏾', '👍🏿'] },
      { base: '👋', skins: ['👋', '👋🏻', '👋🏼', '👋🏽', '👋🏾', '👋🏿'] },
      { base: '👏', skins: ['👏', '👏🏻', '👏🏼', '👏🏽', '👏🏾', '👏🏿'] },
      { base: '🙏', skins: ['🙏', '🙏🏻', '🙏🏼', '🙏🏽', '🙏🏾', '🙏🏿'] },
      { base: '🙌', skins: ['🙌', '🙌🏻', '🙌🏼', '🙌🏽', '🙌🏾', '🙌🏿'] },
      { base: '💪', skins: ['💪', '💪🏻', '💪🏼', '💪🏽', '💪🏾', '💪🏿'] },
      { base: '🤝', skins: ['🤝', '🤝🏻', '🤝🏼', '🤝🏽', '🤝🏾', '🤝🏿'] },
      { base: '🫶', skins: ['🫶', '🫶🏻', '🫶🏼', '🫶🏽', '🫶🏾', '🫶🏿'] },
      { base: '✌️', skins: ['✌️', '✌🏻', '✌🏼', '✌🏽', '✌🏾', '✌🏿'] },
      '❤️', '💖', '🥰', '😍', '😂', '🤣',
      '😊', '🥹', '🥳', '😎', '🤗', '😮',
      '😢', '😭', '🔥', '💯', '🎉', '✨',
      '🎂', '🎈', '🥂', '💐', '🌟', '👑',
      '🍰', '☕', '🍕', '🏡', '🌸', '🍀',
      '🤔', '😅', '👀', '🏆', '🎁', '📸',
    ],
    sparkMeStickers: null,
    sparkMeStickersError: false,
    sparkSticker: null,

    sparkMediaUrl(url) {
      if (!url || typeof url !== 'string') return '';
      if (/^https:\/\/lh3\.googleusercontent\.com\/.+=[sw]\d+(?:-no)?$/i.test(url)) {
        return url.replace(/=[sw]\d+(?:-no)?$/i, '=w720');
      }
      return url;
    },

    get sparkEmojiPalette() {
      const tone = this.sparkSkinTone || 0;
      return this.sparkEmojiItems.map(item => (typeof item === 'string' ? item : ((item.skins && item.skins[tone]) ? item.skins[tone] : item.base)));
    },

    get sparkLivingPalette() {
      const cfg = window.ATG_LIVING_EMOJIS;
      if (!cfg || !Array.isArray(cfg.catalog)) return [];
      const toneKey = (cfg.tones || [])[(this.sparkSkinTone || 0) - 1] || null;
      const toneable = new Set(cfg.toneable || []);
      return cfg.catalog.map(e => ({
        id: (toneKey && toneable.has(e.id)) ? e.id.replace(/_3d$/, `_${toneKey}_3d`) : e.id,
        title: e.title,
      }));
    },

    setSparkSkinTone(tone) {
      this.sparkSkinTone = tone;
      try { localStorage.setItem('fp_skin_tone', String(tone)); } catch (_) {}
    },

    openSparkMeTab() {
      this.sparkEmojiTab = 'me';
      if (this.sparkMeStickers || !window.AtgEmojiEngine || !window.AtgEmojiEngine.getMeStickers) return;
      this.sparkMeStickersError = false;
      window.AtgEmojiEngine.getMeStickers()
        .then(d => { this.sparkMeStickers = d || { available: false }; })
        .catch(() => { this.sparkMeStickersError = true; });
    },

    insertSparkEmoji(emoji) {
      const input = document.getElementById('sparkReplyInput');
      const current = this.replyText || '';
      if (input && typeof input.selectionStart === 'number') {
        const start = input.selectionStart;
        const end = input.selectionEnd ?? start;
        this.replyText = current.slice(0, start) + emoji + current.slice(end);
        const caret = start + emoji.length;
        this.$nextTick(() => {
          input.focus();
          input.setSelectionRange(caret, caret);
        });
      } else {
        this.replyText = current + emoji;
      }
      this.sparkEmojiOpen = false;
    },

    insertSparkLivingEmoji(emojiId) {
      if (!/^[a-z0-9_]+_3d$/.test(emojiId)) return;
      const current = this.replyText || '';
      const sep = current && !/\s$/.test(current) ? ' ' : '';
      this.replyText = `${current}${sep}:${emojiId}: `;
      this.sparkEmojiOpen = false;
    },

    selectSparkMeSticker(token, url = '', label = '') {
      if (!/^:me_[a-z]+@[A-Za-z0-9_-]{1,64}:$/.test(token)) return;
      this.sparkSticker = { token, url: String(url || ''), label: String(label || '') };
      this.sparkEmojiOpen = false;
    },
    showCreateModal: false,
    submittingCreate: false,
    createErrorMessage: '',
    newSparkTitle: '',
    newSparkPrompt: '',
    newSparkOptions: '',

    async loadSparkRoster() {
      if (this.sparkFamilyRoster.length > 0) return;
      try {
        const res = await fetch('/allMembers/processApiData', {
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin'
        });
        const json = await res.json();
        const members = json?.data || json?.message || [];
        if (Array.isArray(members)) {
          this.sparkFamilyRoster = members.map(m => ({
            id: String(m.id || ''),
            firstName: String(m.firstName || '').trim(),
            lastName: String(m.lastName || '').trim(),
            img: m.img || ''
          }));
        }
      } catch (_) {}
    },

    async handleSparkReplyInput(event) {
      const input = event.target;
      const text = input.value || '';
      const cursorPos = input.selectionEnd ?? text.length;
      const match = text.slice(0, cursorPos).match(/(?:^|\s)@([A-Za-z0-9_'\- ]{0,40})$/);
      if (!match) {
        this.sparkMentionOpen = false;
        this.sparkMentionMatches = [];
        return;
      }
      if (this.sparkFamilyRoster.length === 0) {
        await this.loadSparkRoster();
      }
      const latestText = input.value || '';
      const latestPos = input.selectionEnd ?? latestText.length;
      const latestMatch = latestText.slice(0, latestPos).match(/(?:^|\s)@([A-Za-z0-9_'\- ]{0,40})$/);
      if (!latestMatch) {
        this.sparkMentionOpen = false;
        this.sparkMentionMatches = [];
        return;
      }
      const query = (latestMatch[1] || '').trim().toLowerCase();
      const meId = String(this.spark?.current_user_id || '');
      const matches = this.sparkFamilyRoster.filter(m => {
        if (!m.firstName && !m.lastName) return false;
        if (meId && String(m.id) === meId) return false;
        const full = `${m.firstName} ${m.lastName}`.toLowerCase();
        if (!query) return true;
        return full.includes(query) || m.firstName.toLowerCase().startsWith(query) || m.lastName.toLowerCase().startsWith(query);
      }).slice(0, 6);
      this.sparkMentionMatches = matches;
      this.sparkMentionIndex = 0;
      this.sparkMentionOpen = matches.length > 0;
    },

    handleSparkReplyKeydown(event) {
      if (!this.sparkMentionOpen || !this.sparkMentionMatches.length) return;
      const len = this.sparkMentionMatches.length;
      if (event.key === 'ArrowDown') {
        event.preventDefault();
        this.sparkMentionIndex = (this.sparkMentionIndex + 1) % len;
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        this.sparkMentionIndex = (this.sparkMentionIndex - 1 + len) % len;
      } else if (event.key === 'Enter' || event.key === 'Tab') {
        const chosen = this.sparkMentionMatches[this.sparkMentionIndex];
        if (chosen) {
          event.preventDefault();
          this.selectSparkMention(chosen);
        }
      } else if (event.key === 'Escape') {
        this.sparkMentionOpen = false;
      }
    },

    selectSparkMention(member) {
      if (!member) return;
      const input = document.getElementById('sparkReplyInput');
      const text = this.replyText || '';
      const cursorPos = input && typeof input.selectionEnd === 'number' ? input.selectionEnd : text.length;
      const before = text.slice(0, cursorPos);
      const after = text.slice(cursorPos);
      const replacedBefore = before.replace(/(?:^|\s)@([A-Za-z0-9_'\- ]*)$/, (m) => {
        const prefix = m.startsWith(' ') ? ' ' : '';
        return `${prefix}@${member.firstName} ${member.lastName} `;
      });
      const newCaret = replacedBefore.length;
      this.replyText = replacedBefore + after;
      this.sparkMentionOpen = false;
      this.sparkMentionMatches = [];
      this.$nextTick(() => {
        if (input) {
          input.focus();
          input.setSelectionRange(newCaret, newCaret);
        }
      });
    },

    formatSparkReplyBody(raw) {
      let esc = String(raw || '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
      }[c]));
      const stickerRe = /:(?:[a-z0-9_]+_3d|me_[a-z]+@[A-Za-z0-9_-]{1,64}):/gi;
      const emojiOnly = /:(?:[a-z0-9_]+_3d|me_[a-z]+@[A-Za-z0-9_-]{1,64}):/i.test(raw || '') && String(raw || '').replace(stickerRe, '').trim() === '';
      const liveClass = emojiOnly ? 'atg-emoji-container atg-live-emoji atg-live-emoji--jumbo' : 'atg-emoji-container atg-live-emoji';
      const meClass = emojiOnly ? 'atg-me-sticker atg-me-sticker--jumbo' : 'atg-me-sticker';

      esc = esc.replace(/:me_(hello|laugh|wow|love|nod|dance|point|clap)@([A-Za-z0-9_-]{1,64}):/g, (m, reaction, uid) => {
        return `<span class="${meClass}" role="button" tabindex="0" onclick="event.stopPropagation(); window.AtgEmojiEngine && window.AtgEmojiEngine.tapSticker(this, '${reaction}')"><img src="/api/emoji/avatar-sticker/${uid}/${reaction}?v=live2" loading="lazy" width="56" height="56" alt="Avatar sticker: ${reaction}" onerror="this.closest('.atg-me-sticker').remove()"></span>`;
      });

      const cfg = window.ATG_LIVING_EMOJIS;
      const knownIds = new Set();
      if (cfg && Array.isArray(cfg.catalog)) {
        const tones = cfg.tones || [];
        const toneable = new Set(cfg.toneable || []);
        cfg.catalog.forEach(e => {
          knownIds.add(String(e.id || '').toLowerCase());
          if (toneable.has(e.id)) {
            tones.forEach(t => knownIds.add(String(e.id).replace(/_3d$/, `_${t}_3d`).toLowerCase()));
          }
        });
      }

      esc = esc.replace(/:([a-z0-9_]+_3d):/gi, (m, rawId) => {
        const id = rawId.toLowerCase();
        if (knownIds.size > 0 && !knownIds.has(id)) return m;
        return `<span class="${liveClass}" role="button" tabindex="0" onclick="event.stopPropagation(); window.AtgEmojiEngine && window.AtgEmojiEngine.tapLive(this, '${id}')"><img src="/public/img/emoji/${id}.svg?v=20261107a" onerror="this.src='/img/emoji/${id}.svg?v=20261107a'" class="atg-3d-emoji" loading="lazy" width="26" height="26" alt="${id}" title="Tap to play"></span>`;
      });

      const honorificsOn = String(this.spark?.kinship_honorifics || 'on').toLowerCase() !== 'off';
      const dir = (this.spark?.kinship_directory && typeof this.spark.kinship_directory === 'object')
        ? this.spark.kinship_directory
        : {};
      const meId = String(this.spark?.current_user_id || '').trim();
      const escAttr = (s) => String(s || '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
      }[c]));
      const mentionRe = /(?:(\b(?:grandpa|grandad|granddad|grandfather|grandma|gran|nanny|nan|grandmother|great-grandpa|great-grandma|great-grandfather|great-grandmother|great-uncle|great-aunt|great-aunty|dad|daddy|father|pa|papa|baba|mum|mummy|mom|mommy|mother|ma|mama|uncle|aunt|aunty|auntie)[ \t]+))?(?<![\w@])@([A-Za-z\u00C0-\u024F0-9_.'-]+(?:&#0*39;[A-Za-z\u00C0-\u024F0-9_.-]+)?(?:[ \t]+[A-Za-z\u00C0-\u024F0-9_.'-]+(?:&#0*39;[A-Za-z\u00C0-\u024F0-9_.-]+)?){0,2})(?![\w])/gi;

      return esc.replace(mentionRe, (full, vocative, captured) => {
        const words = String(captured || '').split(/[ \t]+/);
        let matched = null;
        let used = 0;
        for (let len = Math.min(words.length, 3); len >= 1; len -= 1) {
          const cand = words.slice(0, len).join(' ').replace(/&#0*39;/g, "'").replace(/&amp;/g, '&').toLowerCase().trim();
          if (dir[cand]) {
            matched = dir[cand];
            used = len;
            break;
          }
        }
        if (!matched || matched.ambiguous) {
          const cnt = Math.min(words.length, 2);
          const mPart = words.slice(0, cnt).join(' ');
          const rest = words.slice(cnt).length ? ' ' + words.slice(cnt).join(' ') : '';
          return `${vocative || ''}<span class="mention-chip">@${mPart}</span>${rest}`;
        }
        const rest = words.slice(used).length ? ' ' + words.slice(used).join(' ') : '';
        const targetId = String(matched.id || '').trim();
        const isSelf = Boolean(meId && targetId && meId === targetId);
        const hon = (!isSelf && honorificsOn && matched.honorific) ? String(matched.honorific) : '';
        const labelName = hon ? (matched.displayFullName || `${hon} ${matched.fullName}`) : matched.fullName;
        const href = targetId ? (isSelf ? '/profilePage' : `/allMembers/seeProfile/${encodeURIComponent(targetId)}`) : '';
        const tip = isSelf
          ? `Mentioned you (${matched.fullName})`
          : (matched.kinship && !['Family Member', 'Relative', 'Extended Relative'].includes(matched.kinship)
            ? `${matched.kinship} · ${matched.fullName}`
            : `Mentioned: ${matched.fullName}`);
        const prefix = hon ? '' : (vocative || '');
        if (href) {
          return `${prefix}<a href="${escAttr(href)}" class="mention-chip mention-chip--link" title="${escAttr(tip)}">@${escAttr(labelName)}</a>${rest}`;
        }
        return `${prefix}<span class="mention-chip" title="${escAttr(tip)}">@${escAttr(labelName)}</span>${rest}`;
      });
    },

    startEditReply(reply) {
      this.editingReplyId = reply.id;
      this.editReplyText = reply.body || '';
      this.$nextTick(() => {
        const el = document.getElementById('sparkEditInput_' + reply.id);
        if (el) el.focus();
      });
    },

    cancelEditReply() {
      this.editingReplyId = null;
      this.editReplyText = '';
    },

    async saveEditReply(reply) {
      if (!this.spark || !reply || this.savingEditReplyId || this.editReplyText.trim() === '') return;
      this.savingEditReplyId = reply.id;
      this.errorMessage = '';
      try {
        const res = await fetch('/api/sparks/reply/update', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.getCsrfToken()
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            csrf_token: this.getCsrfToken(),
            spark_id: this.spark.id,
            reply_id: reply.id,
            body: this.editReplyText.trim()
          })
        });
        const json = await res.json();
        if (!res.ok || json.status !== 'success') {
          throw new Error(json.message || 'Could not update your reply.');
        }
        this.spark = json.data;
        this.editingReplyId = null;
        this.editReplyText = '';
        if (typeof this.$toast === 'function') {
          this.$toast('Reply updated!', 'success');
        }
      } catch (err) {
        this.errorMessage = err.message || 'Unable to update your reply.';
      } finally {
        this.savingEditReplyId = null;
      }
    },

    async deleteReply(reply) {
      if (!this.spark || !reply || this.deletingReplyId) return;
      if (typeof window.confirm === 'function' && !window.confirm('Delete this reply? This cannot be undone.')) {
        return;
      }
      this.deletingReplyId = reply.id;
      this.errorMessage = '';
      try {
        const res = await fetch('/api/sparks/reply/delete', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.getCsrfToken()
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            csrf_token: this.getCsrfToken(),
            spark_id: this.spark.id,
            reply_id: reply.id
          })
        });
        const json = await res.json();
        if (!res.ok || json.status !== 'success') {
          throw new Error(json.message || 'Could not delete your reply.');
        }
        this.spark = json.data;
        if (this.editingReplyId === reply.id) {
          this.editingReplyId = null;
          this.editReplyText = '';
        }
        if (typeof this.$toast === 'function') {
          this.$toast('Reply deleted.', 'success');
        }
      } catch (err) {
        this.errorMessage = err.message || 'Unable to delete your reply.';
      } finally {
        this.deletingReplyId = null;
      }
    },

    getCsrfToken() {
      if (typeof this.$csrf === 'function') {
        return this.$csrf();
      }
      const meta = document.querySelector('meta[name="csrf-token"]');
      if (meta && meta.content) return meta.content;
      const input = document.querySelector('input[name="csrf_token"], input[name="_token"]');
      return input ? input.value : '';
    },

    async initSpark() {
      await this.loadSpark();
    },

    async loadSpark() {
      this.loading = true;
      this.errorMessage = '';
      try {
        const res = await fetch('/api/sparks/active', {
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin'
        });
        const json = await res.json();
        if (!res.ok || json.status !== 'success') {
          throw new Error(json.message || 'Could not load today\'s Family Spark.');
        }
        this.spark = json.data;
        this.loaded = true;
      } catch (err) {
        this.errorMessage = err.message || 'Unable to connect right now.';
      } finally {
        this.loading = false;
      }
    },

    async castVote(optionIndex) {
      if (!this.spark || this.votingOption !== null) return;
      this.votingOption = optionIndex;
      this.errorMessage = '';
      try {
        const res = await fetch('/api/sparks/respond', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.getCsrfToken()
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            csrf_token: this.getCsrfToken(),
            spark_id: this.spark.id,
            response_type: 'vote',
            selected_option_index: optionIndex
          })
        });
        const json = await res.json();
        if (!res.ok || json.status !== 'success') {
          throw new Error(json.message || 'Vote could not be saved.');
        }
        this.spark = json.data;
        if (typeof this.$toast === 'function') {
          this.$toast('Vote recorded!', 'success');
        }
      } catch (err) {
        this.errorMessage = err.message || 'Unable to save your vote.';
      } finally {
        this.votingOption = null;
      }
    },

    async submitReply() {
      const typed = (this.replyText || '').trim();
      const stickerTok = this.sparkSticker?.token || '';
      const combined = [typed, stickerTok].filter(Boolean).join(' ');
      if (!this.spark || this.submittingReply || combined === '') return;
      this.submittingReply = true;
      this.errorMessage = '';
      try {
        const res = await fetch('/api/sparks/respond', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.getCsrfToken()
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            csrf_token: this.getCsrfToken(),
            spark_id: this.spark.id,
            response_type: 'text',
            body: combined
          })
        });
        const json = await res.json();
        if (!res.ok || json.status !== 'success') {
          throw new Error(json.message || 'Reply could not be posted.');
        }
        this.spark = json.data;
        this.replyText = '';
        this.sparkSticker = null;
        this.showReplies = true;
        if (typeof this.$toast === 'function') {
          this.$toast('Reply added to the Spark!', 'success');
        }
      } catch (err) {
        this.errorMessage = err.message || 'Unable to post your reply.';
      } finally {
        this.submittingReply = false;
      }
    },

    openCreateModal() {
      this.newSparkTitle = '';
      this.newSparkPrompt = '';
      this.newSparkOptions = '';
      this.createErrorMessage = '';
      this.showCreateModal = true;
    },

    async createSpark() {
      if (this.submittingCreate) return;
      this.submittingCreate = true;
      this.createErrorMessage = '';
      const opts = this.newSparkOptions
        .split(',')
        .map(s => s.trim())
        .filter(s => s.length > 0)
        .slice(0, 4);

      try {
        const res = await fetch('/api/sparks/create', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.getCsrfToken()
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            csrf_token: this.getCsrfToken(),
            title: this.newSparkTitle.trim(),
            prompt_text: this.newSparkPrompt.trim(),
            options: opts
          })
        });
        const json = await res.json();
        if (!res.ok || json.status !== 'success') {
          throw new Error(json.message || 'Could not publish your Family Spark.');
        }
        this.spark = json.data;
        this.showCreateModal = false;
        this.showReplies = true;
        if (typeof this.$toast === 'function') {
          this.$toast('Your Family Spark is live!', 'success');
        }
      } catch (err) {
        this.createErrorMessage = err.message || 'Unable to publish your Family Spark.';
      } finally {
        this.submittingCreate = false;
      }
    }
  };
}
</script>
