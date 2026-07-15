<!-- Post Composer Modal -->
@php

    $token = urlencode(base64_encode(random_bytes(32)));
    $_SESSION['token'] = $token;
    setcookie('XSRF-TOKEN-EDITPROFILEFORM', $token, [
        'expires' => time() + 900,
        'path' => '/',
        'samesite' => 'Lax',
        'secure' => ($_ENV['APP_ENV'] ?? 'production') === 'production',
        'httponly' => false,
    ]);

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
                    @isset($data['profilePics'])
                        <img src="{{ route('resources/images/profile/' . $data['profilePics']) }}" alt="Avatar"
                            class="rounded-circle me-3 shadow-sm" width="48" height="48" style="border: 2px solid var(--primary-color); object-fit: cover;">
                    @else
                        <img src="{{ asset('img/avatar/avatarF.png') }}" alt="Avatar" width="48" height="48"
                            class="rounded-circle me-3 shadow-sm">
                    @endisset
                    <div>
                        <h6 class="mb-0 fw-bold" style="color: var(--text-color);">{{ $data['firstName'] }} {{ $data['lastName'] }}</h6>
                        <small class="text-muted"><i class="bi bi-people-fill"></i> Family Members</small>
                    </div>
                </div>

                <p id="formPostMessageModal_notification"></p>

                <form id='formPostMessageModal' enctype='multipart/form-data' class="formPostMessageModal">

                    <textarea class="form-control mb-3 border-0" data-emoji-target
                        placeholder="What's on your mind, {{ $data['firstName'] }}?" name="postMessage" id="postMessage"
                        rows="4" style="background-color: var(--bg-color); border-radius: 15px; font-size: 1.1rem; padding: 15px; resize: none;"></textarea>

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
                            <button type="button" class="btn-composer-action text-primary fw-bold" id="addPollBtn" title="Create Poll">
                                <i class="bi bi-bar-chart-fill"></i> Poll
                            </button>
                        </div>

                        <!-- Poll Creation UI (Hidden by Default) -->
                        <div id="pollCreationContainer" class="w-100 mt-3 d-none bg-light p-3 rounded border">
                            <h6 class="fw-bold mb-2 text-primary">Create a Poll</h6>
                            <input type="text" name="poll_question" class="form-control mb-2 border-primary" placeholder="Ask a question..." style="border-radius: 10px;">
                            <div id="pollOptionsContainer">
                                <input type="text" name="poll_options[]" class="form-control mb-2" placeholder="Option 1" style="border-radius: 10px;">
                                <input type="text" name="poll_options[]" class="form-control mb-2" placeholder="Option 2" style="border-radius: 10px;">
                            </div>
                            <button type="button" id="addPollOptionBtn" class="btn btn-sm btn-outline-primary rounded-pill mt-1">
                                <i class="bi bi-plus-circle"></i> Add Option
                            </button>
                            <button type="button" id="removePollBtn" class="btn btn-sm btn-outline-danger rounded-pill mt-1 ms-2">
                                Cancel Poll
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

                        <input type="hidden" name="token" value="{{ $token }}">

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