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
                            <img src="{{ asset('img/profile/' . $data['img']) }}" alt="Avatar"
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
                        @csrf
                        {{-- Textarea --}}
                        <textarea class="form-control mb-3" placeholder="What's on your mind, {{ $data['firstName'] }}?" name="postMessage"
                            id="postMessage" rows="4"></textarea>

                        {{-- Image Preview --}}
                        <div id="imagePreviewContainer" class="mb-3 d-none position-relative">
                            <img id="imagePreview" class="image-preview mb-2" alt="Preview">
                            <button type="button" id="closeImagePreview"
                                class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2"
                                aria-label="Remove image">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                        <span id="postModalImgFileNames"></span>

                        {{-- Emoji Picker & Upload --}}
                        <div class="mb-3 position-relative">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="emojiToggle"
                                aria-expanded="false" aria-controls="emojiPicker">
                                <i class="bi bi-emoji-smile"></i> Emoji
                            </button>

                            <div class="emoji-picker d-none mt-3" id="emojiPicker" role="listbox" aria-hidden="true">
                                @foreach (['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳'] as $emoji)
                                    <button type="button" class="emoji-btn"
                                        aria-label="{{ $emoji }}">{{ $emoji }}</button>
                                @endforeach
                            </div>


                            {{-- image upload button --}}

                            <label for="imageUpload" class="btn btn-sm btn-outline-secondary mb-0">
                                <i class="bi bi-image"></i> Photo/Video
                            </label>
                            <input type="file" name="post_img[]" id="imageUpload" accept="image/*" multiple
                                style="display: none;">

                        </div>

                        {{-- Visibility Notice --}}
                        <div class="border-top pt-3">
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
                        </div>


                        <input type="hidden" name="token" value="{{ $token }}">

                        {{-- Modal Footer --}}
                        <div class="modal-footer">
                            <button type="button" id="submitPost" name="submit"
                                class="btn btn-primary submitPost w-100">
                                Post</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const emojiToggle = document.getElementById('emojiToggle');
                const emojiPicker = document.getElementById('emojiPicker');
                const emojiButtons = document.querySelectorAll('.emoji-btn');
                const postMessage = document.getElementById('postMessage');
                const imageUpload = document.getElementById('imageUpload');
                const imagePreview = document.getElementById('imagePreview');
                const imagePreviewContainer = document.getElementById('imagePreviewContainer');
                const closeImagePreview = document.getElementById('closeImagePreview');
                const fileNames = document.getElementById('postModalImgFileNames');

                // Emoji Picker Toggle
                emojiToggle.addEventListener('click', (e) => {
                    e.stopPropagation(); // Prevent event bubbling
                    emojiPicker.classList.toggle('d-none');
                    const expanded = emojiToggle.getAttribute('aria-expanded') === 'true';
                    emojiToggle.setAttribute('aria-expanded', !expanded);

                    emojiPicker.setAttribute('aria-hidden', expanded);
                });

                // Emoji Insert
                emojiPicker.addEventListener('click', (e) => {
                    if (e.target.classList.contains('emoji-btn')) {
                        e.preventDefault();
                        e.stopPropagation(); // Prevent event bubbling
                        const emoji = e.target.textContent;
                        const textarea = postMessage;
                        const cursorPos = textarea.selectionStart;
                        const textBefore = textarea.value.substring(0, cursorPos);
                        const textAfter = textarea.value.substring(textarea.selectionEnd);

                        // Insert emoji at cursor position
                        textarea.value = textBefore + emoji + textAfter;

                        // Move cursor to after the inserted emoji
                        const newCursorPos = cursorPos + emoji.length;
                        textarea.setSelectionRange(newCursorPos, newCursorPos);
                        textarea.focus();

                        // Close emoji picker after selection
                        emojiPicker.classList.add('d-none');
                        emojiToggle.setAttribute('aria-expanded', 'false');
                        emojiPicker.setAttribute('aria-hidden', 'true');
                    }
                });

                // Close emoji picker when clicking outside
                document.addEventListener('click', (e) => {
                    if (!emojiToggle.contains(e.target) && !emojiPicker.contains(e.target)) {
                        emojiPicker.classList.add('d-none');
                        emojiToggle.setAttribute('aria-expanded', 'false');
                        emojiPicker.setAttribute('aria-hidden', 'true');
                    }
                });


                // Image Preview
                imageUpload.addEventListener('change', function() {
                    const files = this.files;
                    console.log(files)
                    if (files.length > 0) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            console.log(e)
                            console.log(e.target.result)
                            imagePreview.src = e.target.result;
                            imagePreviewContainer.classList.remove('d-none');
                        };
                        reader.readAsDataURL(files[0]);

                        const names = Array.from(files).map(file => file.name).join(', ');
                        fileNames.textContent = `Selected: ${names}`;
                    } else {
                        clearImagePreview();
                    }
                });



                // emojiButtons.forEach(button => {

                //     button.addEventListener('click', () => {
                //         postMessage.value += button.textContent;
                //     });
                // });

                // Close emoji picker when clicking outside
                document.addEventListener('click', (e) => {
                    if (!emojiToggle.contains(e.target) && !emojiPicker.contains(e.target)) {
                        emojiPicker.style.display = 'none';
                    }
                });

                // // Image Upload Preview
                // imageUpload.addEventListener('change', function() {
                //     const file = this.files[0];
                //     if (file) {
                //         const reader = new FileReader();
                //         reader.onload = function(e) {
                //             imagePreview.src = e.target.result;
                //             imagePreview.style.display = 'block';
                //         }
                //         reader.readAsDataURL(file);
                //     }
                // });


                // Close Preview
                closeImagePreview.addEventListener('click', () => {
                    clearImagePreview();
                    imageUpload.value = '';
                });

                function clearImagePreview() {
                    imagePreview.src = '';
                    imagePreviewContainer.classList.add('d-none');
                    fileNames.textContent = '';
                      imageUpload.value = '';
                }
            });
        </script>
