@php
    $token = $_SESSION['token'] ?? '';
@endphp
<div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content glass-modal glass-overlay shadow-lg border-0" style="border-radius: 20px; background-color: var(--card-bg); max-height: calc(100vh - 3.5rem);">

            <div class="modal-header border-bottom py-3 px-4 align-items-center position-relative flex-shrink-0">
                <h5 class="modal-title fw-bold w-100 text-center mb-0" id="postModalLabel" style="color: var(--text-color); font-size: 1.15rem;">
                    Create Post
                </h5>
                <button type="button" class="btn-close position-absolute end-0 me-3 shadow-none" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--close-btn-filter);"></button>
            </div>

            <div class="modal-body px-4 pt-3 pb-4" style="overflow-y: auto;">
    
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ str_starts_with($data['img'] ?? '', '/') ? $data['img'] : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png') }}" alt="Avatar"
                        class="rounded-circle me-3 shadow-sm" width="44" height="44" style="border: 2px solid var(--primary-color); object-fit: cover;">
                    <div>
                        <h6 class="mb-1 fw-bold fs-6" style="color: var(--text-color);">{{ $data['firstName'] }} {{ $data['lastName'] }}</h6>
                        <span class="badge rounded-pill bg-light-subtle text-muted border border-secondary-subtle fw-medium px-2.5 py-1" style="font-size: 0.75rem;">
                            <i class="bi bi-people-fill me-1 text-primary"></i> Family Members <i class="bi bi-caret-down-fill ms-1 small" style="font-size: 0.65rem;"></i>
                        </span>
                    </div>
                </div>

                <p id="formPostMessageModal_notification"></p>

                <form id='formPostMessageModal' enctype='multipart/form-data' class="formPostMessageModal">

                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="post_no" id="editPostNo" value="">

                    <textarea class="form-control mb-2 border-0 bg-transparent shadow-none px-0" data-emoji-target
                        placeholder="What's on your mind, {{ $data['firstName'] }}?" name="postMessage" id="postMessage"
                        rows="4" style="font-size: 1.15rem; color: var(--text-color); resize: none;"></textarea>

                    <!-- Smart Family Hashtags Quick Pill Suggestions -->
                    <div class="family-hashtag-quick-tray mb-3 px-1">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="text-muted small fw-medium" style="font-size: 0.76rem;">
                                <i class="bi bi-hash text-primary"></i> Family Milestones &amp; Topics:
                            </span>
                            <span class="text-muted small" style="font-size: 0.72rem;">Tap to add</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1.5 align-items-center" id="composerHashtagPillContainer">
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill text-primary fw-semibold" style="font-size: 0.75rem;" onclick="insertHashtagIntoComposer('#SundayDinner')">
                                #SundayDinner
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill text-primary fw-semibold" style="font-size: 0.75rem;" onclick="insertHashtagIntoComposer('#FirstSteps')">
                                #FirstSteps
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill text-primary fw-semibold" style="font-size: 0.75rem;" onclick="insertHashtagIntoComposer('#FamilyReunion')">
                                #FamilyReunion
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill text-primary fw-semibold" style="font-size: 0.75rem;" onclick="insertHashtagIntoComposer('#Throwback')">
                                #Throwback
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill text-primary fw-semibold" style="font-size: 0.75rem;" onclick="insertHashtagIntoComposer('#HeritageRecipe')">
                                #HeritageRecipe
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill text-primary fw-semibold" style="font-size: 0.75rem;" onclick="insertHashtagIntoComposer('#Milestone')">
                                #Milestone
                            </button>
                        </div>
                    </div>

                    <small id="editPostNotice" class="d-none text-muted d-block mb-2 px-1">Editing text only — images and polls can't be changed here.</small>

                    <div id="imagePreviewContainer" class="mb-3 d-none position-relative">
                        <div id="imagePreviewList" class="d-flex flex-wrap gap-2"></div>
                        <button type="button" id="closeImagePreview"
                            class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2 rounded-circle"
                            style="width: 30px; height: 30px; padding: 0;"
                            aria-label="Remove all images">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    <span id="postModalImgFileNames" class="invalid-feedback d-block mb-2 text-muted small px-2"></span>

                    <!-- Poll Creation UI (Hidden by Default) -->
                    <div id="pollCreationContainer" class="poll-builder d-none mb-3">
                        <div class="poll-builder__header">
                            <span class="poll-builder__icon"><i class="bi bi-bar-chart-fill"></i></span>
                            <span class="poll-builder__title">Create a poll</span>
                            <button type="button" id="removePollBtn" class="poll-builder__close" aria-label="Remove poll">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <!-- Quick Trivia Templates -->
                        <div class="d-flex align-items-center gap-1.5 mb-2 px-1">
                            <span class="text-muted small" style="font-size: 0.75rem;">Trivia Starters:</span>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill text-dark fw-semibold" style="font-size: 0.72rem;" onclick="applyTriviaTemplate('guess_who')">
                                👶 Guess Who
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 rounded-pill text-dark fw-semibold" style="font-size: 0.72rem;" onclick="applyTriviaTemplate('family_lore')">
                                🧠 Family Lore
                            </button>
                        </div>

                        <div class="poll-builder__field">
                            <input type="text" name="poll_question" class="poll-builder__question" placeholder="Ask a question…" maxlength="150">
                        </div>

                        <div id="pollOptionsContainer" class="poll-builder__options">
                            <input type="text" name="poll_options[]" class="poll-builder__option" placeholder="Option 1" maxlength="80">
                            <input type="text" name="poll_options[]" class="poll-builder__option" placeholder="Option 2" maxlength="80">
                        </div>

                        <button type="button" id="addPollOptionBtn" class="poll-builder__add">
                            <i class="bi bi-plus-lg"></i> Add option
                        </button>
                    </div>

                    <!-- Video Embed Container (Hidden by Default) -->
                    <div id="videoEmbedContainer" class="video-builder d-none mb-3 p-3 rounded-3" style="background: var(--bg-color); border: 1px solid var(--border-color);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold small text-primary"><i class="bi bi-play-circle-fill me-1"></i> Add Video</span>
                                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1" style="font-size: 0.7rem;">
                                    <i class="bi bi-clock-history me-1"></i> Max 30s
                                </span>
                            </div>
                            <button type="button" id="removeVideoBtn" class="btn-close" style="font-size: 0.7rem;" aria-label="Remove video"></button>
                        </div>
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <label for="videoDirectFileInput" class="btn btn-sm btn-outline-primary mb-0 d-flex align-items-center gap-1" style="cursor: pointer; border-radius: 8px; font-size: 0.8rem; padding: 5px 12px;">
                                <i class="bi bi-cloud-arrow-up-fill"></i> Upload Video (Max 30s)
                            </label>
                            <input type="file" id="videoDirectFileInput" accept="video/mp4,video/quicktime,video/webm" hidden>
                            <span class="text-muted small">or paste video link below</span>
                        </div>
                        <div class="input-group input-group-sm mb-1">
                            <input type="url" id="postVideoInput" class="form-control form-control-sm" placeholder="Paste YouTube, Vimeo or Cloudflare Stream URL">
                        </div>

                        <!-- 30-Second Rule Notification Banner -->
                        <div class="d-flex align-items-center gap-2 mt-2 px-2 py-1 rounded-2 bg-light-subtle border border-dashed border-secondary-subtle" style="font-size: 0.75rem;">
                            <i class="bi bi-info-circle-fill text-primary"></i>
                            <span class="text-secondary"><strong>Family Clip Rule:</strong> Direct video uploads are limited to a maximum of <strong>30 seconds</strong>.</span>
                        </div>

                        <!-- Dynamic Video Retention Expiration Control -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2 px-2 py-1 rounded-2 bg-light-subtle border border-light-subtle">
                            <div class="d-flex align-items-center gap-1">
                                <i class="bi bi-hourglass-split text-primary"></i>
                                <span class="small fw-semibold text-secondary" style="font-size: 0.78rem;">Keep Video For:</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <select id="videoExpirySelect" class="form-select form-select-sm" style="font-size: 0.75rem; padding: 3px 8px; border-radius: 8px; width: auto; font-weight: 500;">
                                    <option value="86400">⏳ 24 Hours (Free)</option>
                                    <option value="604800">📅 7 Days (Free)</option>
                                    <option value="2592000" selected>🗓️ 30 Days (Free Default)</option>
                                    <option value="31536000" data-premium="true">👑 1 Year (Premium Only)</option>
                                    <option value="0" data-premium="true">👑 Permanent / Never (Premium Only)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="videoUploadProgressWrapper" class="d-none mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small id="videoUploadStatus" class="text-primary fw-semibold" style="font-size: 0.75rem;">Uploading directly to Cloudflare...</small>
                                <small id="videoUploadPercent" class="text-muted fw-bold" style="font-size: 0.75rem;">0%</small>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 4px;">
                                <div id="videoProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;"></div>
                            </div>
                        </div>

                        <div id="videoLivePreview" class="mt-2 d-none ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm" style="max-height: 180px;"></div>
                    </div>

                    <!-- Facebook-Style "Add to your post" Card -->
                    <div class="add-to-post-box d-flex align-items-center justify-content-between p-2 mb-3 rounded-3 position-relative"
                        style="border: 1px solid var(--border-color); background-color: var(--bg-color);">
                        <span class="fw-semibold small px-2" style="color: var(--text-color);">Add to your post</span>
                        
                        <div class="d-flex gap-1 align-items-center">
                            <label for="imageUpload" class="btn-composer-action action-photo mb-0" title="Attach Photo" style="cursor: pointer;" aria-label="Attach photo">
                                <i class="bi bi-image-fill"></i>
                            </label>
                            <input type="file" name="post_img[]" id="imageUpload" accept="image/*" multiple hidden>

                            <button type="button" class="btn-composer-action action-emoji" id="emojiPost" title="Add Emoji" aria-label="Add emoji">
                                <i class="bi bi-emoji-smile-fill"></i>
                            </button>

                            <button type="button" class="btn-composer-action action-video" id="addVideoBtn" title="Embed Video (YouTube / Vimeo)" aria-label="Add video">
                                <i class="bi bi-play-btn-fill"></i>
                            </button>

                            <button type="button" class="btn-composer-action action-gif gif-btn" title="Add GIF" aria-label="Add GIF">GIF</button>
                            
                            <button type="button" class="btn-composer-action action-tag" title="Tag People / Stickers" aria-label="Tag people">
                                <i class="bi bi-tag-fill"></i>
                            </button>
                            
                            <button type="button" class="btn-composer-action action-poll" id="addPollBtn" title="Create Poll" aria-label="Create poll">
                                <i class="bi bi-bar-chart-fill"></i>
                            </button>

                            <button type="button" class="btn-composer-action action-apple" id="iosShareBtn" title="Share from iPhone 13 Apple Photos" aria-label="Share from iPhone Photos" data-bs-toggle="modal" data-bs-target="#iosFamilyShareModal">
                                <i class="bi bi-apple"></i>
                            </button>
                        </div>

                        <div id="emojiPickerContainer"
                            class="d-none position-absolute modern-emoji-picker"
                            style="z-index: 1000; bottom: 55px; right: 10px;">
                            <div class="emoji-picker-caret"></div>
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2"
                                id="closeEmojiPicker" aria-label="Close" style="font-size: 0.7rem; z-index: 10;"></button>
                            <div id="emojiListPost" class="mt-2" role="listbox"></div>
                        </div>
                    </div>

                    <!-- Full-Width Facebook-Style Post Submit Button -->
                    <button type="button" id="submitPost" name="submit" class="btn btn-primary w-100 fw-semibold submitPost shadow-sm" style="border-radius: 12px; min-height: 44px; font-size: 0.92rem; letter-spacing: -0.01em;">
                        Post
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>

<!-- iOS Apple Photos Direct Share Modal -->
<div class="modal fade" id="iosFamilyShareModal" tabindex="-1" aria-labelledby="iosFamilyShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-modal glass-overlay shadow-lg border-0" style="border-radius: 20px; background-color: var(--card-bg);">
            <div class="modal-header border-bottom py-3 px-4 align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-dark text-white rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-apple fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="iosFamilyShareModalLabel" style="color: var(--text-color);">iPhone 13 Photos Share Sheet</h5>
                        <p class="text-muted small mb-0">Share directly from Apple Photos gallery</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--close-btn-filter);"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 rounded-3 mb-3 border bg-light-subtle">
                    <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> How To Share from iPhone Photos</h6>
                    <ol class="small text-secondary ps-3 mb-0" style="line-height: 1.7;">
                        <li>Tap <strong>Install Shortcut</strong> below to add <em>&quot;Share to FamilyPlatform&quot;</em> to your iPhone.</li>
                        <li>Open any photo or video in your iPhone <strong>Apple Photos</strong> gallery.</li>
                        <li>Tap the iOS <strong>Share</strong> button and choose <strong>&quot;Share to FamilyPlatform&quot;</strong>.</li>
                        <li>It posts directly to your family feed instantly!</li>
                    </ol>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <a href="/api/media/ios-shortcut?fam_code={{ $_SESSION['famCode'] ?? '' }}&amp;format=shortcut" class="btn btn-primary rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center shadow-sm" style="min-height: 48px;">
                        <i class="bi bi-download me-2"></i> Download iPhone Shortcut
                    </a>
                    <button type="button" class="btn btn-outline-secondary rounded-pill py-2 fw-medium d-flex align-items-center justify-content-center" style="min-height: 44px;" onclick="copyFamilyShareEndpoint('{{ $_SESSION['famCode'] ?? '' }}')">
                        <i class="bi bi-clipboard me-2"></i> Copy Family Code &amp; Endpoint
                    </button>
                </div>

                <div id="famCopyAlertSuccess" class="alert alert-success small py-2 px-3 text-center d-none" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> Copied to clipboard!
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal" style="min-height: 44px;">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
window.copyFamilyShareEndpoint = function(famCode) {
    const text = window.location.origin + '/api/media/native-share?fam_code=' + encodeURIComponent(famCode);
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            const el = document.getElementById('famCopyAlertSuccess');
            if (el) {
                el.classList.remove('d-none');
                setTimeout(() => el.classList.add('d-none'), 3000);
            }
        });
    } else {
        prompt('Copy your direct share URL:', text);
    }
};
window.insertHashtagIntoComposer = function(tag) {
    const textarea = document.getElementById('postMessage');
    if (!textarea) return;
    const currentVal = textarea.value.trim();
    if (currentVal.includes(tag)) {
        textarea.focus();
        return;
    }
    textarea.value = currentVal ? currentVal + ' ' + tag + ' ' : tag + ' ';
    textarea.focus();
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
};

window.applyTriviaTemplate = function(type) {
    const qInput = document.querySelector('input[name="poll_question"]');
    const msgInput = document.getElementById('postMessage');
    const optInputs = document.querySelectorAll('input[name="poll_options[]"]');
    if (type === 'guess_who') {
        if (qInput) qInput.value = '👶 Guess Who: Who is this in the throwback photo?';
        if (msgInput && !msgInput.value.trim()) msgInput.value = '👶 Weekly Family Trivia! Can anyone guess who this was? Cast your vote!';
        if (optInputs[0]) optInputs[0].value = 'Cousin Ade';
        if (optInputs[1]) optInputs[1].value = 'Uncle Tunde';
    } else {
        if (qInput) qInput.value = '🧠 Family Trivia: Who knows this family secret?';
        if (msgInput && !msgInput.value.trim()) msgInput.value = '🧠 Weekly Family Trivia Challenge! Vote below and debate in the comments!';
        if (optInputs[0]) optInputs[0].value = 'True';
        if (optInputs[1]) optInputs[1].value = 'False';
    }
};

window.openComposerWithTrivia = function(type = 'guess_who') {
    const postModalEl = document.getElementById('postModal');
    if (!postModalEl) return;
    const bsModal = bootstrap.Modal.getOrCreateInstance(postModalEl);
    bsModal.show();
    setTimeout(() => {
        const pollBtn = document.getElementById('addPollBtn');
        const pollContainer = document.getElementById('pollCreationContainer');
        if (pollContainer && pollContainer.classList.contains('d-none') && pollBtn) {
            pollBtn.click();
        }
        if (window.applyTriviaTemplate) {
            window.applyTriviaTemplate(type);
        }
    }, 250);
};
</script>