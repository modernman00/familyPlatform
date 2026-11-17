<style>
  [x-cloak] { display: none !important; }
  .fp-spark-card {
    border-radius: 20px;
    background-color: var(--card-bg, #ffffff);
    border: 1px solid var(--border-color, #e2e8f0);
    color: var(--text-color, #0f172a);
    overflow: hidden;
  }
  body.dark-mode .fp-spark-card,
  [data-bs-theme="dark"] .fp-spark-card {
    background-color: var(--card-bg, #1e293b);
    border-color: var(--border-color, #334155);
    color: var(--text-color, #f8fafc);
  }
  .fp-spark-header {
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
    font-size: 0.75rem;
    letter-spacing: 0.04em;
    white-space: nowrap;
    flex-shrink: 0;
  }
  .fp-spark-btn-action {
    min-height: 44px;
    min-width: 44px;
    white-space: nowrap;
    flex-shrink: 0;
    transition: transform 0.15s ease, filter 0.15s ease;
  }
  .fp-spark-btn-action:active {
    transform: scale(0.98);
  }
  .fp-spark-option-btn {
    min-height: 48px;
    border-radius: 12px;
    border: 1.5px solid var(--border-color, #cbd5e1);
    background-color: var(--card-bg, #ffffff);
    color: var(--text-color, #0f172a);
    text-align: left;
    transition: border-color 0.15s ease, transform 0.15s ease;
  }
  body.dark-mode .fp-spark-option-btn,
  [data-bs-theme="dark"] .fp-spark-option-btn {
    background-color: #0f172a;
    border-color: #475569;
    color: #f8fafc;
  }
  .fp-spark-option-btn.is-selected {
    border-color: #a31621;
    background-color: rgba(163, 22, 33, 0.06);
  }
  body.dark-mode .fp-spark-option-btn.is-selected,
  [data-bs-theme="dark"] .fp-spark-option-btn.is-selected {
    border-color: #f87171;
    background-color: rgba(163, 22, 33, 0.22);
  }
  .fp-spark-option-btn:active {
    transform: scale(0.98);
  }
  .fp-spark-reply-bubble {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #0f172a;
    border-radius: 14px;
  }
  body.dark-mode .fp-spark-reply-bubble,
  [data-bs-theme="dark"] .fp-spark-reply-bubble {
    background-color: #0f172a;
    border-color: #334155;
    color: #f8fafc;
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
      <span class="small fw-semibold opacity-90" style="color: #fef3c7 !important;"
            x-text="spark ? ('By ' + spark.created_by_name) : 'Loading today\'s spark...'"></span>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button type="button"
              class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 fp-spark-btn-action d-inline-flex align-items-center gap-2"
              @click="openCreateModal()">
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
        <span class="small fw-semibold" x-text="errorMessage"></span>
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
          <h3 class="fw-bold mb-1" style="font-size: 1.2rem; line-height: 1.35; overflow-wrap: anywhere;"
              x-text="spark.title"></h3>
          <p class="mb-0" style="font-size: 0.96rem; line-height: 1.5; opacity: 0.9; overflow-wrap: anywhere;"
             x-text="spark.prompt_text"></p>
        </div>

        <!-- Optional Archive Flashback Photo -->
        <template x-if="spark.media_url">
          <div class="rounded-3 overflow-hidden border" style="max-height: 280px; background: #0f172a;">
            <img :src="spark.media_url"
                 alt="Family archive memory"
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
                <span class="position-relative z-1 small fw-bold flex-shrink-0"
                      x-show="spark.total_votes > 0"
                      x-text="opt.percentage + '% (' + opt.votes + ')'"></span>
              </button>
            </template>
            <div class="small opacity-75 d-flex align-items-center justify-content-between">
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
                <p class="mb-1 fw-semibold small">No family replies yet.</p>
                <p class="mb-0 small opacity-75">Start the conversation below—your reply stays inside this single Living Spark Card.</p>
              </div>
            </template>

            <!-- Aggregated Replies List -->
            <template x-for="reply in spark.replies" :key="reply.id">
              <div class="p-2 p-sm-3 fp-spark-reply-bubble d-flex align-items-start gap-2">
                <img :src="reply.author_avatar || '/resources/images/profile/avatarM.png'"
                     alt=""
                     class="rounded-circle flex-shrink-0 object-fit-cover"
                     style="width: 36px; height: 36px;">
                <div class="min-w-0 flex-grow-1">
                  <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <span class="fw-bold small" style="overflow-wrap: anywhere;" x-text="reply.author_name"></span>
                  </div>
                  <p class="mb-0 small mt-1" style="line-height: 1.45; overflow-wrap: anywhere;" x-text="reply.body"></p>
                </div>
              </div>
            </template>

            <!-- Inline Reply Form (Universal CSRF + Universal Button Spinner) -->
            <form @submit.prevent="submitReply()" class="d-flex align-items-stretch gap-2 mt-1">
              @include('csrf')
              <label for="sparkReplyInput" class="visually-hidden">Write a family reply</label>
              <input id="sparkReplyInput"
                     type="text"
                     class="form-control rounded-pill px-3"
                     style="min-height: 44px;"
                     maxlength="500"
                     placeholder="Add your answer or story..."
                     x-model="replyText"
                     :disabled="submittingReply">
              <button type="submit"
                      class="btn btn-danger rounded-pill px-4 fw-semibold fp-spark-btn-action d-inline-flex align-items-center justify-content-center gap-2 text-white"
                      :disabled="submittingReply || replyText.trim() === ''">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" x-show="submittingReply"></span>
                <span x-text="submittingReply ? 'Sending...' : 'Reply'"></span>
              </button>
            </form>
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
        <h3 id="sparkCreateModalTitle" class="mb-0 fw-bold" style="font-size: 1.1rem;">Spark a Family Question</h3>
        <button type="button"
                class="btn-close btn-close-white fp-spark-btn-action"
                aria-label="Close modal"
                @click="showCreateModal = false"></button>
      </div>
      <form @submit.prevent="createSpark()" class="card-body p-4 d-flex flex-column gap-3">
        @include('csrf')
        <template x-if="createErrorMessage">
          <div class="alert alert-danger py-2 px-3 mb-0 small fw-semibold rounded-3" role="alert" x-text="createErrorMessage"></div>
        </template>
        <div>
          <label for="sparkTitleInput" class="form-label fw-semibold small">Headline</label>
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
          <label for="sparkPromptInput" class="form-label fw-semibold small">Your Question</label>
          <textarea id="sparkPromptInput"
                    class="form-control"
                    rows="3"
                    maxlength="500"
                    required
                    placeholder="Ask something that gets everyone talking..."
                    x-model="newSparkPrompt"></textarea>
        </div>
        <div>
          <label for="sparkOptionsInput" class="form-label fw-semibold small">Optional Quick Poll Choices (comma-separated, up to 4)</label>
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
    showCreateModal: false,
    submittingCreate: false,
    createErrorMessage: '',
    newSparkTitle: '',
    newSparkPrompt: '',
    newSparkOptions: '',

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
      if (!this.spark || this.submittingReply || this.replyText.trim() === '') return;
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
            body: this.replyText.trim()
          })
        });
        const json = await res.json();
        if (!res.ok || json.status !== 'success') {
          throw new Error(json.message || 'Reply could not be posted.');
        }
        this.spark = json.data;
        this.replyText = '';
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
