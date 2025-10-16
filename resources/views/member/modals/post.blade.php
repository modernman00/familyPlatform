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
    <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                {{-- Modal Header --}}
                <div class="modal-header">
                    <h5 class="modal-title" id="postModalLabel"><i class="bi bi-pencil">Create Post </i></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Modal Body --}}

                <div class="modal-body">

                    <div class="d-flex align-items-center mb-3">
                        {{-- User Info --}}
                        @isset($data['img'])
                            <img src="{{ route('resources/images/profile/' . $data['img']) }}" alt="Avatar"
                                class="rounded-circle me-3 profileImg" width="40" height="40">
                        @else
                            <img src="{{ asset('img/avatar/avatarF.png') }}" alt="Avatar" width="40" height="40"
                                class="rounded-circle me-3">
                        @endisset
                        <div>
                            <h6 class="mb-0">{{ $data['firstName'] }} {{ $data['lastName'] }}</h6>
                        </div>
                    </div>

                    {{-- Notification Area --}}
                    <p id="formPostMessageModal_notification"></p>

                    {{-- Post Form --}}
                    <form id='formPostMessageModal' enctype='multipart/form-data' class="formPostMessageModal">

                        {{-- Textarea --}}
                        <textarea class="form-control mb-3" data-emoji-target placeholder="What's on your mind, {{ $data['firstName'] }}?"
                            name="postMessage" id="postMessage" rows="4"></textarea>

                        {{-- Image Preview --}}
                        <div id="imagePreviewContainer" class="mb-3 .preview-img d-none position-relative">
                            <div id="imagePreviewList" class="d-flex flex-wrap gap-2"></div>
                            <button type="button" id="closeImagePreview"
                                class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2"
                                aria-label="Remove all images">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>

                        <span id="postModalImgFileNames" class="invalid-feedback" aria-live="polite"
                            class="d-block mb-2 text-muted small"></span>

                        {{-- Emoji Picker & Upload --}}
                        <div class="mt-3 d-flex justify-content-between align-items-center position-relative">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="emojiToggle"
                                    title="add emoji" aria-expanded="false" aria-controls="emojiPickerList">
                                    😊
                                </button>
                                <div id="emojiPickerList" class="d-flex flex-wrap gap-2 mt-2 d-none" role="listbox"
                                    aria-hidden="true">
                                    <!-- Emojis will be dynamically inserted here -->

                                </div>

                                {{-- image upload button --}}

                                <label for="imageUpload" class="btn btn-sm btn-outline-secondary mb-0"
                                    title="Attach image">
                                    📷
                                </label>
                                <input type="file" name="post_img[]" id="imageUpload" accept="image/*" multiple
                                    hidden>

                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Add GIF"
                                    aria-label="Add GIF">GIF</button>



                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Stickers"
                                    aria-label="Stickers">🏷️</button>

                            </div>



                            {{-- Visibility Notice --}}
                            {{-- <div class="border-top pt-3">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="text-muted">Only members with your family code can see you
                                        post</span>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                        id="audienceDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-people-fill"></i> Friends
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="audienceDropdown">
                                        <li><a class="dropdown-item" href="#">Public</a></li>
                                        <li><a class="dropdown-item" href="#">Friends</a></li>
                                        <li><a class="dropdown-item" href="#">Only me</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div> --}}


                            <input type="hidden" name="token" value="{{ $token }}">


                            <button type="button" id="submitPost" name="submit"
                                class="btn btn-primary btn-sm submitPost">
                                Post</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
