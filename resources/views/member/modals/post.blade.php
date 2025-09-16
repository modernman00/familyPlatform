    <!-- Post Composer Modal -->
    <div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="postModalLabel">Create Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3">
                          @isset($data['img'])

                            <img src="/public/img/profile/{{ $data['img'] }}" alt="Avatar" class="rounded-circle me-3 profileImg" width="40" height="40">
                            @else
                            <img src="/public/avatar/avatarF.png" alt="Avatar" width="40" height="40" class="rounded-circle me-3">

                          @endisset
                    <div>
                            <h6 class="mb-0">{{$data['firstName']}} {{ $data['lastName']}}</h6>
                        </div>
                    </div>

                  <p id="formPostMessageModal_notification"></p>

                  <form id='formPostMessageModal'  enctype='multipart/form-data'>

                    <textarea class="form-control mb-3" placeholder="What's on your mind, {{$data['firstName']}}?" name="postMessage" id="postMessage" rows="4"></textarea>

                           <!-- Image Preview -->
                    <img id="imagePreview" class="image-preview" alt="Preview">
                    
                    <div class="mb-3 position-relative">
                        <div class="emoji-picker" id="emojiPicker">
                            <button class="emoji-btn">😀</button>
                            <button class="emoji-btn">😃</button>
                            <button class="emoji-btn">😄</button>
                            <button class="emoji-btn">😁</button>
                            <button class="emoji-btn">😆</button>
                            <button class="emoji-btn">😅</button>
                            <button class="emoji-btn">😂</button>
                            <button class="emoji-btn">🤣</button>
                            <button class="emoji-btn">😊</button>
                            <button class="emoji-btn">😇</button>
                            <button class="emoji-btn">🙂</button>
                            <button class="emoji-btn">🙃</button>
                            <button class="emoji-btn">😉</button>
                            <button class="emoji-btn">😌</button>
                            <button class="emoji-btn">😍</button>
                            <button class="emoji-btn">🥰</button>
                            <button class="emoji-btn">😘</button>
                            <button class="emoji-btn">😗</button>
                            <button class="emoji-btn">😙</button>
                            <button class="emoji-btn">😚</button>
                            <button class="emoji-btn">😋</button>
                            <button class="emoji-btn">😛</button>
                            <button class="emoji-btn">😝</button>
                            <button class="emoji-btn">😜</button>
                            <button class="emoji-btn">🤪</button>
                            <button class="emoji-btn">🤨</button>
                            <button class="emoji-btn">🧐</button>
                            <button class="emoji-btn">🤓</button>
                            <button class="emoji-btn">😎</button>
                            <button class="emoji-btn">🤩</button>
                            <button class="emoji-btn">🥳</button>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary me-2" id="emojiToggle">
                            <i class="bi bi-emoji-smile"></i> Emoji
                        </button>

                        <label for="imageUpload" class="btn btn-sm btn-outline-secondary mb-0">
                            <i class="bi bi-image"></i> Photo/Video
                        </label>
                        <input type="file" name="post_img[]" id="imageUpload" accept="image/*" multiple style="display: none;">
                    </div>
                    
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="text-muted">Who can see your post?</span>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="audienceDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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
                </div>
                <div class="modal-footer">
                    <button type="button" id="submitPost" name="submit" class="btn btn-primary submitPost w-100">Post</button>
                </div>
              </form>
          
            </div>
        </div>
    </div>