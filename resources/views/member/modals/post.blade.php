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

                    <div class="composer-action-bar">
                        <div class="d-flex gap-1">
                            <button type="button" class="btn-composer-action" id="emojiPost" title="Add emoji">
                                <i class="bi bi-emoji-smile"></i>
                            </button>

                            <label for="imageUpload" class="btn-composer-action mb-0" title="Attach image" style="cursor: pointer;">
                                <i class="bi bi-camera"></i>
                            </label>
                            <input type="file" name="post_img[]" id="imageUpload" accept="image/*" multiple hidden>

                            <button type="button" class="btn-composer-action gif-btn" title="Add GIF">GIF</button>
                            <button type="button" class="btn-composer-action" title="Stickers">
                                <i class="bi bi-tag"></i>
                            </button>
                            <button type="button" class="btn-composer-action fw-bold" id="addPollBtn" title="Create Poll">
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