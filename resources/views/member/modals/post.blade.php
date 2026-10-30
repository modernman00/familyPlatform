@php
    $token = $_SESSION['token'] ?? '';
@endphp
<div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content composer-sheet glass-modal glass-overlay shadow-lg border-0">

            <div class="modal-header composer-sheet__header flex-shrink-0">
                <span class="composer-sheet__grab" aria-hidden="true"></span>
                <h5 class="modal-title" id="postModalLabel">{{ __('modal.create_post', 'Create Post') }}</h5>
                <button type="button" class="composer-sheet__close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form id='formPostMessageModal' enctype='multipart/form-data' class="formPostMessageModal composer-form">

                <div class="modal-body composer-sheet__body">

                    <div class="composer-author">
                        <img src="{{ str_starts_with($data['img'] ?? '', '/') ? $data['img'] : '/resources/images/profile/' . ($data['img'] ?? $data['profilePics'] ?? 'avatarM.png') }}" alt="Avatar"
                            class="composer-author__avatar flex-shrink-0" width="48" height="48" style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; aspect-ratio: 1 / 1; object-fit: cover; border-radius: 50%;">
                        <div class="composer-author__meta">
                            <h6 class="composer-author__name">
                                {{ $data['firstName'] }} {{ $data['lastName'] }}
                                <span id="composerTagHeaderWrapper" class="d-none fw-normal text-muted">
                                    is with <span id="composerTagHeaderNames" class="fw-semibold text-primary"></span>
                                </span>
                            </h6>
                            <span class="composer-audience">
                                <i class="bi bi-shield-check"></i> {{ __('modal.shared_with_family', 'Shared with Family') }}
                            </span>
                        </div>
                    </div>

                    <p id="formPostMessageModal_notification"></p>

                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="post_no" id="editPostNo" value="">
                    <input type="hidden" name="attached_gif_url" id="attachedGifUrlInput" value="">

                    <textarea class="composer-text form-control border-0 bg-transparent shadow-none px-0" data-emoji-target
                        placeholder="{{ __('feed.share_prompt', 'Share a moment with your family...') }}" name="postMessage" id="postMessage"
                        rows="2"></textarea>

                    <!-- Smart Family Hashtags Quick Pill Suggestions (single swipeable row) -->
                    <div class="composer-tags" aria-label="Family topics, tap to add">
                        <div class="composer-tags__row" id="composerHashtagPillContainer">
                            <button type="button" class="composer-chip" onclick="insertHashtagIntoComposer('#SundayDinner')">#SundayDinner</button>
                            <button type="button" class="composer-chip" onclick="insertHashtagIntoComposer('#FirstSteps')">#FirstSteps</button>
                            <button type="button" class="composer-chip" onclick="insertHashtagIntoComposer('#FamilyReunion')">#FamilyReunion</button>
                            <button type="button" class="composer-chip" onclick="insertHashtagIntoComposer('#Throwback')">#Throwback</button>
                            <button type="button" class="composer-chip" onclick="insertHashtagIntoComposer('#HeritageRecipe')">#HeritageRecipe</button>
                            <button type="button" class="composer-chip" onclick="insertHashtagIntoComposer('#Milestone')">#Milestone</button>
                        </div>
                    </div>

                    <small id="editPostNotice" class="d-none text-muted d-block mb-2 px-1">Editing text only — images and polls can't be changed here.</small>

                    <!-- Live OpenGraph Link Preview in Composer (16:9 Full Graphics) -->
                    <div id="composerLinkPreviewContainer" class="d-none mb-3 position-relative rounded-3 overflow-hidden border border-light-subtle shadow-sm" style="background-color: var(--bs-card-bg, #ffffff);">
                        <button type="button" id="closeComposerLinkPreview" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2 rounded-circle shadow-sm" style="width: 28px; height: 28px; padding: 0; z-index: 10; opacity: 0.85;" aria-label="Dismiss link preview">
                            <i class="bi bi-x"></i>
                        </button>
                        <div id="composerLinkPreviewImageWrapper" class="position-relative w-100 overflow-hidden" style="aspect-ratio: 16 / 9; max-height: 240px; background-color: #0f172a;">
                            <img id="composerLinkPreviewImg" src="" alt="Link preview" class="w-100 h-100 object-fit-cover" style="display: block;">
                            <span id="composerLinkPreviewDomainBadge" class="badge rounded-pill bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-2 px-2.5 py-1 small" style="font-size: 0.72rem; letter-spacing: 0.5px; backdrop-filter: blur(6px);"></span>
                        </div>
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small id="composerLinkPreviewSiteName" class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;"></small>
                                <small class="text-primary fw-medium"><i class="bi bi-lightning-charge-fill me-1"></i> Rich Preview</small>
                            </div>
                            <h6 id="composerLinkPreviewTitle" class="fw-bold mb-1 text-dark" style="font-size: 0.95rem; line-height: 1.35;"></h6>
                            <p id="composerLinkPreviewDesc" class="text-secondary small mb-2" style="font-size: 0.82rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"></p>
                            <div class="pt-1 border-top d-flex justify-content-between align-items-center">
                                <span id="composerLinkPreviewUrl" class="small text-muted text-truncate" style="font-size: 0.75rem; max-width: 65%;"></span>
                                <span id="composerLinkPreviewCta" class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1" style="font-size: 0.75rem;"></span>
                            </div>
                        </div>
                    </div>

                    <div id="imagePreviewContainer" class="composer-media d-none">
                        <div class="composer-media__bar">
                            <span id="postModalImgFileNames" class="composer-media__count"></span>
                            <button type="button" id="closeImagePreview" class="composer-media__clear" aria-label="Remove all images">
                                Remove all
                            </button>
                        </div>
                        <div id="imagePreviewList" class="composer-media__list"></div>
                    </div>

                    <!-- Selected GIF Attached Preview -->
                    <div id="selectedGifPreview" class="d-none mb-3 position-relative rounded-3 overflow-hidden border border-light-subtle shadow-sm" style="background: #0f172a;">
                        <img id="selectedGifImg" src="" alt="Attached GIF" class="w-100" style="max-height: 220px; object-fit: contain; display: block;">
                        <button type="button" id="removeSelectedGifBtn" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2 rounded-circle shadow" style="width: 28px; height: 28px; padding: 0; opacity: 0.9;" aria-label="Remove GIF">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    <!-- Mobile Camera Roll Trigger (hidden once photos are attached; the thumbnail row has its own "+" tile) -->
                    <label for="imageUpload" class="composer-camera-cta d-md-none">
                        <span class="composer-camera-cta__icon"><i class="bi bi-camera-fill"></i></span>
                        <span class="composer-camera-cta__text">
                            <strong>Add photos</strong>
                            <small>From your camera roll</small>
                        </span>
                        <i class="bi bi-chevron-right composer-camera-cta__chev"></i>
                    </label>

                    <!-- Interactive Composer Action Drawers Container (Directly Above Toolbar) -->
                    <div id="composerActiveDrawers" class="mb-3">

                        <!-- 1. Video Embed Container -->
                        <div id="videoEmbedContainer" class="composer-drawer video-builder d-none mb-3 p-3 rounded-3 shadow-xs" style="background: var(--bg-color); border: 1px solid var(--border-color);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="fw-bold small text-danger"><i class="bi bi-play-circle-fill me-1"></i> Add Video</span>
                                    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1" style="font-size: 0.7rem;">
                                        <i class="bi bi-scissors me-1"></i> Smart 30s Auto-Trim
                                    </span>
                                </div>
                                <button type="button" id="removeVideoBtn" class="btn-close" style="font-size: 0.7rem;" aria-label="Remove video"></button>
                            </div>
                            <div class="d-flex gap-2 align-items-center flex-wrap mb-2">
                                <label for="videoDirectFileInput" class="btn btn-sm btn-outline-danger mb-0 d-inline-flex align-items-center gap-1" style="cursor: pointer; border-radius: 8px; font-size: 0.8rem; padding: 8px 14px; min-height: 44px; white-space: nowrap; flex-shrink: 0;">
                                    <i class="bi bi-cloud-arrow-up-fill"></i> Upload Video (Auto-Trim to 30s)
                                </label>
                                <input type="file" id="videoDirectFileInput" accept="video/mp4,video/quicktime,video/webm" hidden>
                                <span class="text-muted small">or paste video link below</span>
                            </div>
                            <div class="input-group input-group-sm mb-1">
                                <input type="url" id="postVideoInput" class="form-control form-control-sm" placeholder="Paste YouTube, Vimeo or Cloudflare Stream URL">
                            </div>

                            <!-- 30-Second Rule & Auto-Trim Notification Banner -->
                            <div class="d-flex align-items-center gap-2 mt-2 px-2 py-1 rounded-2 bg-light-subtle border border-dashed border-secondary-subtle" style="font-size: 0.75rem;">
                                <i class="bi bi-scissors text-danger"></i>
                                <span class="text-secondary"><strong>Smart 30-Second Auto-Trim:</strong> Videos longer than 30 seconds can be automatically trimmed to a <strong>30-second</strong> family highlight clip on request.</span>
                            </div>

                            <!-- Interactive On-Demand Video Auto-Trim Studio -->
                            <div id="videoAutoTrimStudio" class="d-none mt-3 p-3 rounded-3 border" style="background: #0f172a; color: #f8fafc; border-color: rgba(248, 250, 252, 0.16) !important;">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background-color: #a31621; color: #ffffff; font-size: 0.72rem;">
                                            <i class="bi bi-scissors me-1"></i> Auto-Trim Studio
                                        </span>
                                        <span id="videoTrimMetaBadge" class="small" style="color: #cbd5e1; font-size: 0.75rem;">Select 30s highlight window</span>
                                    </div>
                                    <span id="videoTrimDurationBadge" class="badge rounded-pill bg-success text-white px-2.5 py-1" style="font-size: 0.72rem;">
                                        Clip: 30.0s
                                    </span>
                                </div>

                                <div class="ratio ratio-16x9 rounded-3 overflow-hidden mb-2 border border-secondary border-opacity-25" style="max-height: 190px; background: #000000;">
                                    <video id="videoTrimPreviewPlayer" playsinline muted controls preload="metadata" style="width: 100%; height: 100%; object-fit: contain; background: #000000;"></video>
                                </div>

                                <div class="mb-2">
                                    <div class="d-flex justify-content-between align-items-center small mb-1" style="font-size: 0.75rem; color: #e2e8f0;">
                                        <span>Start Time: <strong id="videoTrimStartLabel" style="color: #fbbf24;">0.0s</strong></span>
                                        <span>End Time: <strong id="videoTrimEndLabel" style="color: #fbbf24;">30.0s</strong></span>
                                    </div>
                                    <label for="videoTrimStartRange" class="visually-hidden">Trim Start Time (seconds)</label>
                                    <input type="range" class="form-range mb-1" id="videoTrimStartRange" min="0" max="60" step="0.5" value="0">
                                    <label for="videoTrimEndRange" class="visually-hidden">Trim End Time (seconds)</label>
                                    <input type="range" class="form-range" id="videoTrimEndRange" min="1" max="30" step="0.5" value="30">
                                    <div class="small" style="font-size: 0.72rem; color: #94a3b8;">
                                        Drag sliders to customise your 30-second clip window, or tap Auto-Trim below.
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 flex-wrap mt-2 pt-2 border-top border-secondary border-opacity-25">
                                    <button type="button" id="btnConfirmAutoTrim" class="btn btn-sm fw-bold rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1.5 shadow-sm" style="background-color: #a31621; color: #ffffff !important; border: none; min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                        <i class="bi bi-scissors"></i>
                                        <span id="btnConfirmAutoTrimLabel">Auto-Trim to 30s &amp; Attach</span>
                                    </button>
                                    <button type="button" id="btnQuickTrimFirst30" class="btn btn-sm btn-outline-light rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0; font-size: 0.78rem;">
                                        <i class="bi bi-lightning-charge-fill text-warning"></i>
                                        <span>First 30s (0:00–0:30)</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Dynamic Video Retention Expiration Control -->
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2 px-2 py-1 rounded-2 bg-light-subtle border border-light-subtle">
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-hourglass-split text-danger"></i>
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
                                    <small id="videoUploadStatus" class="text-danger fw-semibold" style="font-size: 0.75rem;">Optimising and uploading video clip...</small>
                                    <small id="videoUploadPercent" class="text-muted fw-bold" style="font-size: 0.75rem;">0%</small>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 4px;">
                                    <div id="videoProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 0%;"></div>
                                </div>
                            </div>

                            <div id="videoLivePreview" class="mt-2 d-none ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm" style="max-height: 180px;"></div>
                        </div>

                        <!-- 2. Race-Conscious Emoji Picker Drawer -->
                        <div id="emojiPickerDrawer" class="composer-drawer d-none mb-3 p-3 rounded-3 shadow-xs" style="background: var(--bg-color); border: 1px solid var(--border-color);">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                <span class="fw-bold small d-flex align-items-center gap-1.5" style="color: #a31621 !important;">
                                    <i class="bi bi-emoji-smile-fill fs-5"></i> Choose an Emoji
                                </span>
                                <button type="button" id="closeEmojiDrawerBtn" class="btn-close" style="font-size: 0.7rem;" aria-label="Close emoji picker"></button>
                            </div>
                            <div id="emojiListPost" role="listbox"></div>
                        </div>

                        <!-- 3. Family GIF Picker Drawer -->
                        <div id="gifPickerDrawer" class="composer-drawer d-none mb-3 p-3 rounded-3 shadow-xs" style="background: var(--bg-color); border: 1px solid var(--border-color);">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                <span class="fw-bold small text-info d-flex align-items-center gap-1.5" style="color: #a31621 !important;">
                                    <i class="bi bi-filetype-gif fs-5"></i> Choose a Family GIF
                                </span>
                                <button type="button" id="closeGifDrawerBtn" class="btn-close" style="font-size: 0.7rem;" aria-label="Close GIF picker"></button>
                            </div>

                            <!-- Fast Category Filter Chips -->
                            <div class="d-flex flex-wrap gap-1.5 mb-2.5" id="gifCategoryPills">
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2.5 rounded-pill fw-semibold gif-cat-pill active" data-cat="all" style="font-size: 0.75rem;">🌟 All</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2.5 rounded-pill fw-semibold gif-cat-pill" data-cat="stickers" style="font-size: 0.75rem;">🏷️ Stickers</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2.5 rounded-pill fw-semibold gif-cat-pill" data-cat="celebrate" style="font-size: 0.75rem;">🎉 Celebrate</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2.5 rounded-pill fw-semibold gif-cat-pill" data-cat="love" style="font-size: 0.75rem;">❤️ Love &amp; Hugs</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2.5 rounded-pill fw-semibold gif-cat-pill" data-cat="family" style="font-size: 0.75rem;">👨‍👩‍👧‍👦 Family</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2.5 rounded-pill fw-semibold gif-cat-pill" data-cat="laugh" style="font-size: 0.75rem;">😂 Laughs</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2.5 rounded-pill fw-semibold gif-cat-pill" data-cat="birthday" style="font-size: 0.75rem;">🎂 Birthday</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2.5 rounded-pill fw-semibold gif-cat-pill" data-cat="dance" style="font-size: 0.75rem;">💃 Joy</button>
                                <button type="button" class="btn btn-sm btn-light border py-0 px-2.5 rounded-pill fw-semibold gif-cat-pill" data-cat="kids" style="font-size: 0.75rem;">👶 Kids</button>
                            </div>

                            <!-- Search Filter Input -->
                            <div class="position-relative mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-transparent border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" id="gifSearchInput" class="form-control form-control-sm border-start-0 shadow-none" placeholder="Filter GIFs (e.g., love, proud, cheers, dance)..." style="border-radius: 0 8px 8px 0;" autocomplete="off">
                                </div>
                            </div>

                            <!-- GIFs Grid Container -->
                            <div id="gifResultsGrid" class="d-flex flex-wrap gap-2 overflow-y-auto" style="max-height: 220px;"></div>
                        </div>

                        <!-- 4. Tag People Container -->
                        <div id="tagPeopleContainer" class="composer-drawer tag-builder d-none mb-3 p-3 rounded-3 shadow-xs" style="background: var(--bg-color); border: 1px solid var(--border-color);">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold small text-primary"><i class="bi bi-people-fill me-1"></i> Tag Family Members</span>
                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5" style="font-size: 0.72rem;" id="tagCountBadge">
                                        0 Tagged
                                    </span>
                                </div>
                                <button type="button" id="removeTagContainerBtn" class="btn-close" style="font-size: 0.7rem;" aria-label="Close tag tray"></button>
                            </div>

                            <!-- Active Tagged Member Chips -->
                            <div id="selectedTagsTray" class="d-flex flex-wrap gap-1.5 mb-2 d-none"></div>

                            <!-- Hidden Inputs Container for POST submission -->
                            <div id="taggedUserInputs"></div>

                            <!-- Search Member Input -->
                            <div class="position-relative mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-transparent border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" id="tagSearchInput" class="form-control form-control-sm border-start-0 shadow-none" placeholder="Type a name to search family members…" style="border-radius: 0 8px 8px 0;" autocomplete="off">
                                </div>
                            </div>

                            <!-- Suggestion & Search Results Member Tray -->
                            <div id="tagSuggestionsList" class="d-flex flex-column gap-1 overflow-y-auto" style="max-height: 180px;">
                                <div class="text-center py-2 text-muted small" id="tagSuggestionsLoading">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Loading family members…
                                </div>
                            </div>
                        </div>

                        <!-- 5. Poll Creation UI -->
                        <div id="pollCreationContainer" class="composer-drawer poll-builder d-none mb-3 shadow-xs">
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

                        <!-- 5b. Then & Now (before / after) Builder -->
                        <div id="thenNowContainer" class="composer-drawer poll-builder d-none mb-3 shadow-xs">
                            <div class="poll-builder__header">
                                <span class="poll-builder__icon"><i class="bi bi-hourglass-split"></i></span>
                                <span class="poll-builder__title" id="thenNowTitle">Then &amp; Now</span>
                                <button type="button" id="removeThenNowBtn" class="poll-builder__close" aria-label="Remove Then and Now">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <input type="hidden" name="then_now" id="thenNowFlag" value="">
                            <input type="hidden" name="reply_to_post" id="thenNowReplyTo" value="">
                            <div class="then-now-slots">
                                <div class="then-now-slot">
                                    <label for="thenImgInput" class="then-now-slot__pick" id="thenSlotPick">
                                        <img id="thenPreview" class="d-none" alt="Then photo preview">
                                        <span id="thenPlaceholder"><i class="bi bi-image"></i><br>Pick the <strong>Then</strong> photo</span>
                                    </label>
                                    <input type="file" id="thenImgInput" accept="image/*" hidden>
                                    <input type="text" name="then_label" class="poll-builder__option" placeholder="Then, e.g. 1998" maxlength="40" aria-label="Then year or label">
                                </div>
                                <div class="then-now-slot">
                                    <label for="nowImgInput" class="then-now-slot__pick" id="nowSlotPick">
                                        <img id="nowPreview" class="d-none" alt="Now photo preview">
                                        <span id="nowPlaceholder"><i class="bi bi-image"></i><br>Pick the <strong>Now</strong> photo</span>
                                    </label>
                                    <input type="file" id="nowImgInput" accept="image/*" hidden>
                                    <input type="text" name="now_label" class="poll-builder__option" placeholder="Now, e.g. 2026" maxlength="40" aria-label="Now year or label">
                                    <button type="button" id="nowCameraBtn" class="btn btn-sm btn-light border rounded-pill w-100 mt-2" style="min-height:44px;">
                                        <i class="bi bi-camera-fill"></i> Match it with the camera
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 6. iOS Direct Apple Photos Shortcut Guide Drawer -->
                        <div id="iosShareDrawer" class="composer-drawer d-none mb-3 p-3 rounded-3 shadow-xs" style="background: var(--bg-color); border: 1px solid var(--border-color);">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                <span class="fw-bold small text-dark d-flex align-items-center gap-1.5">
                                    <i class="bi bi-apple fs-6"></i> iPhone Photos 1-Tap Share
                                </span>
                                <button type="button" id="closeIosShareDrawerBtn" class="btn-close" style="font-size: 0.7rem;" aria-label="Close guide"></button>
                            </div>
                            <p class="small text-muted mb-2.5" style="font-size: 0.8rem; line-height: 1.45;">
                                Post photos and videos straight from your iPhone <strong>Photos</strong> app directly to your family feed:
                            </p>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @php
                                    $signedDeviceToken = (!empty($_SESSION['id']) && !empty($_SESSION['famCode'])) 
                                        ? \App\controller\members\NativeShareController::generateUserToken((string)$_SESSION['id'], (string)$_SESSION['famCode']) 
                                        : '';
                                @endphp
                                <a href="/api/media/ios-shortcut?fam_code={{ $_SESSION['famCode'] ?? '' }}&amp;token={{ $signedDeviceToken }}&amp;format=shortcut" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                                    <i class="bi bi-download"></i> Install iPhone Shortcut
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-medium d-flex align-items-center gap-1.5" style="font-size: 0.78rem;" onclick="copyFamilyShareEndpoint('{{ $_SESSION['famCode'] ?? '' }}', '{{ $signedDeviceToken }}')">
                                    <i class="bi bi-clipboard"></i> Copy Direct URL
                                </button>
                            </div>
                            <p class="small text-muted mb-0" style="font-size: 0.78rem; line-height: 1.45;">
                                After tapping <strong>Install iPhone Shortcut</strong>, Safari saves the file. Tap <strong>Copy Direct URL</strong> first, then install the shortcut (Safari ↓ Downloads, open the file, <strong>Add Shortcut</strong>) and paste the link when asked.
                            </p>
                        </div>

                    </div>

                    <!-- Composer action tools dock -->
                    <div class="add-to-post-box composer-tools">
                        <div class="composer-tools__grid">
                            <label for="imageUpload" class="btn-composer-action composer-tool action-photo mb-0" title="Attach Photos - Upload family pictures or camera roll memories" aria-label="Attach photos">
                                <i class="bi bi-image-fill"></i>
                            </label>
                            <input type="file" name="post_img[]" id="imageUpload" accept="image/*,video/*" multiple hidden>

                            <button type="button" class="btn-composer-action composer-tool action-emoji" id="emojiPost" title="Add Emoji - Express feelings with diverse emoji reactions" aria-label="Add emoji">
                                <i class="bi bi-emoji-smile-fill"></i>
                            </button>

                            <button type="button" class="btn-composer-action composer-tool action-video" id="addVideoBtn" title="Embed Video - Share family videos from YouTube, Vimeo, or your device" aria-label="Add video">
                                <i class="bi bi-play-btn-fill"></i>
                            </button>

                            <button type="button" class="btn-composer-action composer-tool action-gif gif-btn" id="addGifBtn" title="Add GIF - Search and attach fun animated family GIFs" aria-label="Add GIF">
                                <i class="bi bi-filetype-gif"></i>
                            </button>

                            <button type="button" class="btn-composer-action composer-tool action-tag" id="addTagBtn" title="Tag Family - Mention and tag relatives present in this memory" aria-label="Tag people">
                                <i class="bi bi-tag-fill"></i>
                            </button>

                            <button type="button" class="btn-composer-action composer-tool action-poll" id="addPollBtn" title="Create Poll - Ask questions and collect votes from family members" aria-label="Create poll">
                                <i class="bi bi-bar-chart-fill"></i>
                            </button>

                            <button type="button" class="btn-composer-action composer-tool action-thennow" id="addThenNowBtn" title="Then &amp; Now - Showcase your journey with side-by-side milestone photos" aria-label="Then and Now photo comparison">
                                <i class="bi bi-hourglass-split"></i>
                            </button>

                            <button type="button" class="btn-composer-action composer-tool action-apple" id="iosShareBtn" title="iPhone Share - Direct 1-tap sharing from Apple Photos gallery" aria-label="iPhone Photos Direct Share">
                                <i class="bi bi-apple"></i>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Sticky footer: the Post button is always on screen, whatever is attached above -->
                <div class="composer-sheet__footer flex-shrink-0">
                    <button type="button" id="submitPost" name="submit" class="btn btn-primary fw-semibold submitPost">{{ __('feed.post_btn', 'Post') }}</button>
                </div>

            </form>

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
                        <li>Tap <strong>Download iPhone Shortcut</strong> below, then tap the <strong>Downloads</strong> icon (↓) in Safari&rsquo;s address bar, open the file and tap <strong>Add Shortcut</strong>.</li>
                        <li>Open any photo or video in your iPhone <strong>Apple Photos</strong> gallery.</li>
                        <li>Tap the iOS <strong>Share</strong> button and choose <strong>&quot;Share to FamilyPlatform&quot;</strong>.</li>
                        <li>It posts directly to your family feed instantly!</li>
                    </ol>
                </div>

                <div class="d-grid gap-2 mb-3">
                    @php
                        $signedDeviceToken = (!empty($_SESSION['id']) && !empty($_SESSION['famCode'])) 
                            ? \App\controller\members\NativeShareController::generateUserToken((string)$_SESSION['id'], (string)$_SESSION['famCode']) 
                            : '';
                    @endphp
                    <a href="/api/media/ios-shortcut?fam_code={{ $_SESSION['famCode'] ?? '' }}&amp;token={{ $signedDeviceToken }}&amp;format=shortcut" class="btn btn-primary rounded-pill py-2 fw-bold d-flex align-items-center justify-content-center shadow-sm" style="min-height: 48px;">
                        <i class="bi bi-download me-2"></i> Download iPhone Shortcut
                    </a>
                    <button type="button" class="btn btn-outline-secondary rounded-pill py-2 fw-medium d-flex align-items-center justify-content-center" style="min-height: 44px;" onclick="copyFamilyShareEndpoint('{{ $_SESSION['famCode'] ?? '' }}', '{{ $signedDeviceToken }}')">
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
window.copyFamilyShareEndpoint = function(famCode, userToken) {
    let text = window.location.origin + '/api/media/native-share?fam_code=' + encodeURIComponent(famCode);
    if (userToken) {
        text += '&user_token=' + encodeURIComponent(userToken);
    }
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

// Live Link Preview unfurler for composer
(function() {
    let previewDebounceTimer = null;
    let dismissedUrl = null;
    let lastFetchedUrl = null;

    const textarea = document.getElementById('postMessage');
    const container = document.getElementById('composerLinkPreviewContainer');
    const closeBtn = document.getElementById('closeComposerLinkPreview');
    const imgEl = document.getElementById('composerLinkPreviewImg');
    const imgWrapper = document.getElementById('composerLinkPreviewImageWrapper');
    const titleEl = document.getElementById('composerLinkPreviewTitle');
    const descEl = document.getElementById('composerLinkPreviewDesc');
    const domainBadge = document.getElementById('composerLinkPreviewDomainBadge');
    const siteNameEl = document.getElementById('composerLinkPreviewSiteName');
    const urlEl = document.getElementById('composerLinkPreviewUrl');
    const ctaEl = document.getElementById('composerLinkPreviewCta');

    if (closeBtn && container) {
        closeBtn.addEventListener('click', function() {
            container.classList.add('d-none');
            dismissedUrl = lastFetchedUrl;
        });
    }

    if (textarea) {
        textarea.addEventListener('input', function() {
            const val = textarea.value;
            const urlMatch = val.match(/(https?:\/\/[^\s<>"']+)/i);
            if (!urlMatch || !urlMatch[1]) {
                if (container) container.classList.add('d-none');
                lastFetchedUrl = null;
                return;
            }

            const url = urlMatch[1].replace(/[.,;:!?)]+$/, '');
            if (url === dismissedUrl || url === lastFetchedUrl) {
                return;
            }

            if (previewDebounceTimer) clearTimeout(previewDebounceTimer);
            previewDebounceTimer = setTimeout(async function() {
                try {
                    const response = await fetch('/api/link-preview?url=' + encodeURIComponent(url), {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) return;
                    const res = await response.json();
                    if (res?.status === 'success' && res?.data && (res.data.title || res.data.image)) {
                        const d = res.data;
                        lastFetchedUrl = url;
                        if (titleEl) titleEl.textContent = d.title || '';
                        if (descEl) descEl.textContent = d.description || '';
                        if (urlEl) urlEl.textContent = d.url || url;
                        if (domainBadge) domainBadge.textContent = d.is_party_event ? 'PartyPlatform' : (d.domain || 'Link');
                        if (siteNameEl) siteNameEl.textContent = d.site_name || d.domain || '';
                        if (ctaEl) ctaEl.textContent = d.cta_label || 'View Event / RSVP →';
                        if (imgEl && imgWrapper) {
                            if (d.image) {
                                imgEl.src = d.image;
                                imgWrapper.classList.remove('d-none');
                            } else {
                                imgWrapper.classList.add('d-none');
                            }
                        }
                        if (container) container.classList.remove('d-none');
                    }
                } catch (_) {
                    // Fail silently
                }
            }, 400);
        });
    }
})();
</script>