@php
    $token = $_SESSION['token'] ?? '';
@endphp
<div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel">
    <div class="modal-dialog">
        <div class="modal-content glass-modal glass-overlay shadow-lg border-0" style="border-radius: 20px; background-color: var(--card-bg);">

            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h4 class="modal-title fw-bold" id="postModalLabel" style="font-family: 'Playfair Display', serif; color: var(--text-color);">
                    Create Post
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--close-btn-filter);"></button>
            </div>

            <div class="modal-body px-4 pt-3">
    
                <div class="d-flex align-items-center mb-4">
                        <img src="{{ str_starts_with($data['img'] ?? '', '/') ? $data['img'] : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png') }}" alt="Avatar"
                            class="rounded-circle me-3 shadow-sm" width="48" height="48" style="border: 2px solid var(--primary-color); object-fit: cover;">
                    <div>
                        <h6 class="mb-0 fw-bold" style="color: var(--text-color);">{{ $data['firstName'] }} {{ $data['lastName'] }}</h6>
                        <small class="text-muted"><i class="bi bi-people-fill"></i> Family Members</small>
                    </div>
                </div>

                <p id="formPostMessageModal_notification"></p>

                <form id='formPostMessageModal' enctype='multipart/form-data' class="formPostMessageModal">

                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="post_no" id="editPostNo" value="">

                    <textarea class="form-control mb-3 border-0" data-emoji-target
                        placeholder="What's on your mind, {{ $data['firstName'] }}?" name="postMessage" id="postMessage"
                        rows="4" style="background-color: var(--bg-color); border-radius: 15px; font-size: 1.1rem; padding: 15px; resize: none;"></textarea>

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
                    <div id="pollCreationContainer" class="poll-builder d-none">
                        <div class="poll-builder__header">
                            <span class="poll-builder__icon"><i class="bi bi-bar-chart-fill"></i></span>
                            <span class="poll-builder__title">Create a poll</span>
                            <button type="button" id="removePollBtn" class="poll-builder__close" aria-label="Remove poll">
                                <i class="bi bi-x-lg"></i>
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

                    <!-- Video Embed & Cloudflare Stream Direct Upload UI (Hidden by Default) -->
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

                    <div class="composer-action-bar">
                        <div class="d-flex gap-1 align-items-center">
                            <button type="button" class="btn-composer-action action-emoji" id="emojiPost" title="Add emoji" aria-label="Add emoji">
                                <i class="bi bi-emoji-smile-fill"></i>
                            </button>

                            <label for="imageUpload" class="btn-composer-action action-photo mb-0" title="Attach image" style="cursor: pointer;" aria-label="Attach image">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                            <input type="file" name="post_img[]" id="imageUpload" accept="image/*" multiple hidden>

                            <button type="button" class="btn-composer-action action-video fw-bold" id="addVideoBtn" title="Embed Video (YouTube / Vimeo)" aria-label="Add video">
                                <i class="bi bi-play-btn-fill"></i>
                            </button>

                            <button type="button" class="btn-composer-action action-gif gif-btn" title="Add GIF" aria-label="Add GIF">GIF</button>
                            <button type="button" class="btn-composer-action action-tag" title="Tags & Stickers" aria-label="Tags and stickers">
                                <i class="bi bi-tag-fill"></i>
                            </button>
                            <button type="button" class="btn-composer-action action-poll fw-bold" id="addPollBtn" title="Create Poll" aria-label="Create poll">
                                <i class="bi bi-bar-chart-fill"></i>
                            </button>
                        </div>

                        <div id="emojiPickerContainer"
                            class="d-none position-absolute modern-emoji-picker"
                            style="z-index: 1000; bottom: 55px; left: 24px;">
                            <div class="emoji-picker-caret"></div>
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2"
                                id="closeEmojiPicker" aria-label="Close" style="font-size: 0.7rem; z-index: 10;"></button>
                            <div id="emojiListPost" class="mt-2" role="listbox"></div>
                        </div>

                        <button type="button" id="submitPost" name="submit" class="btn text-white fw-semibold px-4 py-2 submitPost"
                            style="background-color: var(--primary-color); border-radius: 20px; font-size: 0.9rem;">
                            Post
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>