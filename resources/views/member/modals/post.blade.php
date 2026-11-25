@php
    $token = $_SESSION['token'] ?? '';
    if (!isset($postAudiences) && !empty($_SESSION['id'])) {
        $postAudiences = (new \App\services\FamilyPostAudienceService())->resolveUserAudiences((string)$_SESSION['id']);
    }
    $canChooseAudience = !empty($postAudiences['can_choose']);
    $hasDualLineage = !empty($postAudiences['has_dual_lineage']);
    $defaultScope = $postAudiences['default_scope'] ?? 'all';
    $birthCodes = $postAudiences['birth'] ?? (!empty($maternalFamCode) ? [$maternalFamCode] : []);
    $marriedCodes = $postAudiences['married'] ?? (!empty($primaryFamCode) ? [$primaryFamCode] : []);
    $allCodes = $postAudiences['all'] ?? array_merge($marriedCodes, $birthCodes);
    $activeFamCode = $primaryFamCode ?? ($data['famCode'] ?? ($_SESSION['famCode'] ?? ''));
    $audienceItems = $postAudiences['items'] ?? [];
@endphp
<div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" data-can-choose="{{ $canChooseAudience ? '1' : '0' }}">
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
                            @if($canChooseAudience)
                                <div class="d-inline-flex align-items-center gap-1.5 mt-1">
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1" style="font-size: 0.78rem;">
                                        <i class="bi bi-people-fill text-primary" id="composerHeaderAudienceIcon"></i>
                                        <span id="composerHeaderAudienceLabel">All Family Circles &amp; Connected Kin</span>
                                    </span>
                                    <button type="button" class="btn btn-link p-0 text-muted small text-decoration-none" style="font-size: 0.75rem;" onclick="window.showComposerAudienceStep()" title="Tap to choose target audience">
                                        Change
                                    </button>
                                </div>
                            @else
                                <div class="d-inline-flex align-items-center gap-1.5 mt-1">
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill px-2.5 py-1" style="font-size: 0.78rem;">
                                        <i class="bi bi-shield-check text-primary me-1"></i> #{{ ltrim((string)$activeFamCode, '#') }}
                                    </span>
                                    @if(empty($hasDualLineage))
                                    <a href="/accountSetting#v-pills-familycode" class="text-decoration-none small text-muted d-inline-flex align-items-center" style="font-size: 0.75rem;" title="{{ __('modal.setup_dual_lineage', 'Set up dual lineage (birth & married family codes) in Account Settings') }}">
                                        <i class="bi bi-diagram-3 me-1"></i> Dual Lineage
                                    </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <p id="formPostMessageModal_notification"></p>

                    @include('csrf')
                    <input type="hidden" name="post_no" id="editPostNo" value="">
                    <input type="hidden" name="target_scope" id="composerTargetScope" value="{{ $defaultScope }}">
                    <input type="hidden" name="target_fam_code" id="composerTargetFamCode" value="" disabled>
                    <input type="hidden" name="attached_gif_url" id="attachedGifUrlInput" value="">
                    <input type="hidden" name="attached_video_url" id="attachedVideoUrl" value="">
                    <input type="hidden" name="attached_video_urls" id="attachedVideoUrls" value="">
                    <input type="hidden" name="post_style" id="composerPostStyleInput" value="">

                    <!-- Step 1 View: Post Creator Content -->
                    <div id="composerStep1" class="composer-step-view">

                    <div id="composerEditorStageWrapper" class="composer-editor-wrapper position-relative w-100">
                        <div id="composerStageBokehLayer" class="fp-text-stage__bokeh d-none" aria-hidden="true"></div>
                        <div id="composerStageCosmosLayer" class="fp-cosmos-orrery d-none" aria-hidden="true">
                            <div class="fp-cosmos-galaxy fp-cosmos-galaxy--primary"></div>
                            <div class="fp-cosmos-galaxy fp-cosmos-galaxy--distant"></div>
                            <div class="fp-cosmos-comet"></div>
                            <div class="fp-cosmos-system">
                                <div class="fp-cosmos-sun"></div>
                                <div class="fp-cosmos-orbit fp-cosmos-orbit--1"><div class="fp-cosmos-planet fp-cosmos-planet--mercury"></div></div>
                                <div class="fp-cosmos-orbit fp-cosmos-orbit--2"><div class="fp-cosmos-planet fp-cosmos-planet--venus"></div></div>
                                <div class="fp-cosmos-orbit fp-cosmos-orbit--3"><div class="fp-cosmos-planet fp-cosmos-planet--earth"><div class="fp-cosmos-moon-orbit"><div class="fp-cosmos-moon"></div></div></div></div>
                                <div class="fp-cosmos-orbit fp-cosmos-orbit--4"><div class="fp-cosmos-planet fp-cosmos-planet--mars"></div></div>
                                <div class="fp-cosmos-orbit fp-cosmos-orbit--5"><div class="fp-cosmos-planet fp-cosmos-planet--saturn"><div class="fp-cosmos-saturn-ring"></div></div></div>
                                <div class="fp-cosmos-orbit fp-cosmos-orbit--6"><div class="fp-cosmos-planet fp-cosmos-planet--neptune"></div></div>
                            </div>
                        </div>
                        <div id="composerStageHeaderBadge" class="d-none mb-2">
                            <span id="composerStageHeaderPill" class="fp-stage-badge">
                                <span id="composerStageHeaderText">🔮 3D HOLOGRAM STAGE</span>
                            </span>
                        </div>
                        <div class="composer-text form-control border-0 bg-transparent shadow-none px-0"
                            id="postMessageEditor"
                            contenteditable="true"
                            role="textbox"
                            aria-multiline="true"
                            data-placeholder="{{ __('feed.share_prompt', 'Share a moment with your family...') }}"
                            style="min-height: 58px; max-height: 220px; overflow-y: auto; outline: none; white-space: pre-wrap; word-break: break-word; font-size: 1.05rem; line-height: 1.55; color: var(--text-main, #0f172a); position: relative; z-index: 2;"></div>
                        <textarea class="d-none" data-emoji-target
                            placeholder="{{ __('feed.share_prompt', 'Share a moment with your family...') }}" name="postMessage" id="postMessage"
                            rows="2"></textarea>

                        <!-- Inline @mention Autocomplete Dropdown for Post Composer -->
                        <div id="composerMentionDropdown" class="mention-autocomplete mention-autocomplete--below shadow-lg d-none" role="listbox" aria-label="Mention a family member">
                            <div class="mention-autocomplete__header d-flex align-items-center justify-content-between px-2 py-1">
                                <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Mention a family member</span>
                                <span style="font-size: 0.7rem;">&uarr;&darr; &bull; Enter</span>
                            </div>
                            <div class="mention-autocomplete__list" id="composerMentionList"></div>
                        </div>
                    </div>

                    <!-- Scenic Post Backdrops & 3D Milestone Stage Studio Strip -->
                    <div id="composerTextStageBar" class="composer-stage-studio mt-2 mb-2" role="region" aria-label="Scenic Post Backdrops and 3D Stage Designer">
                        <div class="d-flex align-items-center justify-content-between mb-1.5 px-1">
                            <span class="small fw-bold d-inline-flex align-items-center gap-1" style="font-size: 0.76rem; color: var(--text-muted-aa, #475569); letter-spacing: 0.02em;">
                                <i class="bi bi-stars text-warning"></i>
                                <span>Post Backdrop &amp; Stage</span>
                            </span>
                            <button type="button" id="composerSmartStageSuggestBtn" class="btn btn-sm rounded-pill d-none py-0.5 px-2.5 fw-bold" style="font-size: 0.72rem; background: linear-gradient(135deg, #0284c7 0%, #059669 100%); color: #ffffff; border: none; min-height: 28px; white-space: nowrap; flex-shrink: 0;">
                                ✨ Smart Match
                            </button>
                        </div>
                        <div class="composer-stage-studio__row" role="radiogroup" aria-label="Choose post background or 3D stage style">
                            <button type="button" class="composer-stage-pill is-active" data-stage-slug="" onclick="window.__setComposerTextStage('')" aria-pressed="true" title="Standard clean text post">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--plain">Aa</span>
                                <span>Plain</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="starlight_bokeh" onclick="window.__setComposerTextStage('starlight_bokeh')" aria-pressed="false" title="Midnight Sapphire &amp; Cyan Starlight Bokeh Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--starlight">✨</span>
                                <span>Starlight Bokeh</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="golden_ember" onclick="window.__setComposerTextStage('golden_ember')" aria-pressed="false" title="Warm Obsidian &amp; Golden Fireflies Bokeh Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--fireflies">🔥</span>
                                <span>Golden Fireflies</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="sunset_horizon" onclick="window.__setComposerTextStage('sunset_horizon')" aria-pressed="false" title="Twilight Dusk &amp; Sunset Horizon Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--sunset">🌅</span>
                                <span>Sunset Horizon</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="emerald_canopy" onclick="window.__setComposerTextStage('emerald_canopy')" aria-pressed="false" title="Deep Botanical Woodland &amp; Jade Bokeh Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--forest">🌿</span>
                                <span>Enchanted Forest</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="ocean_biolum" onclick="window.__setComposerTextStage('ocean_biolum')" aria-pressed="false" title="Midnight Sea &amp; Bioluminescent Glow Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--ocean">🌊</span>
                                <span>Deep Ocean</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="rose_stardust" onclick="window.__setComposerTextStage('rose_stardust')" aria-pressed="false" title="Deep Mulberry Velvet &amp; Rose Stardust Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--rose">🌸</span>
                                <span>Rose Velvet</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="northern_sky" onclick="window.__setComposerTextStage('northern_sky')" aria-pressed="false" title="Polar Midnight &amp; Northern Lights Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--northern">🌌</span>
                                <span>Aurora Sky</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="royal_parchment" onclick="window.__setComposerTextStage('royal_parchment')" aria-pressed="false" title="Editorial Onyx Silk &amp; 24k Gold Dust Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--royal">👑</span>
                                <span>Royal Noir</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="xmas_snowfall" onclick="window.__setComposerTextStage('xmas_snowfall')" aria-pressed="false" title="Festive Alpine Snowfall &amp; Frost Bokeh Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--xmas-snow">❄️</span>
                                <span>Snowfall</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="xmas_hearth" onclick="window.__setComposerTextStage('xmas_hearth')" aria-pressed="false" title="Cosy Christmas Hearth, Fairy Lights &amp; Embers Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--xmas-hearth">🎄</span>
                                <span>Cosy Hearth</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="xmas_lights" onclick="window.__setComposerTextStage('xmas_lights')" aria-pressed="false" title="Festive Pine &amp; Multi-Colour Fairy Lights Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--xmas-lights">✨</span>
                                <span>Fairy Lights</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="xmas_winter_gold" onclick="window.__setComposerTextStage('xmas_winter_gold')" aria-pressed="false" title="Emerald &amp; 24k Champagne Winter Gold Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--xmas-gold">🌟</span>
                                <span>Winter Gold</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="solar_galaxy" onclick="window.__setComposerTextStage('solar_galaxy')" aria-pressed="false" title="Solar System, Spiral Galaxies &amp; Orbiting Planets Backdrop">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--solar-galaxy">🪐</span>
                                <span>Solar Galaxy</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="dynasty_holo" onclick="window.__setComposerTextStage('dynasty_holo')" aria-pressed="false" title="Translucent 3D Emerald Dynasty Tree Stage">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--dynasty">🌳</span>
                                <span>Dynasty Tree</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="cyber_glass" onclick="window.__setComposerTextStage('cyber_glass')" aria-pressed="false" title="Futuristic 3D Translucent Holo Glass Stage">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--cyber">🔮</span>
                                <span>3D Holo Glass</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="alert_pulse" onclick="window.__setComposerTextStage('alert_pulse')" aria-pressed="false" title="3D Pulsing Family Alert Trigger Beacon">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--alert">🚨</span>
                                <span>Alert Trigger</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="grand_announcement" onclick="window.__setComposerTextStage('grand_announcement')" aria-pressed="false" title="3D Royal Gold &amp; Obsidian Broadcast Announcement">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--announce">📣</span>
                                <span>Announcement</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="aurora_prism" onclick="window.__setComposerTextStage('aurora_prism')" aria-pressed="false" title="Iridescent 3D Futuristic Aurora Prism Stage">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--prism">💎</span>
                                <span>Aurora Prism</span>
                            </button>
                            <button type="button" class="composer-stage-pill" data-stage-slug="heirloom_velvet" onclick="window.__setComposerTextStage('heirloom_velvet')" aria-pressed="false" title="Deep Velvet &amp; Gold Leaf Family Decree Stage">
                                <span class="composer-stage-pill__dot composer-stage-pill__dot--velvet">🏛️</span>
                                <span>Royal Velvet</span>
                            </button>
                        </div>
                    </div>

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
                    <div id="composerLinkPreviewContainer" class="d-none mb-3 position-relative rounded-3 overflow-hidden shadow-sm link-preview-card">
                        <button type="button" id="closeComposerLinkPreview" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2 rounded-circle shadow-sm" style="width: 32px; height: 32px; padding: 0; z-index: 10; opacity: 0.9;" aria-label="Dismiss link preview">
                            <i class="bi bi-x"></i>
                        </button>
                        <div id="composerLinkPreviewImageWrapper" class="position-relative w-100 overflow-hidden" style="aspect-ratio: 16 / 9; max-height: 240px; background-color: #0f172a;">
                            <img id="composerLinkPreviewImg" src="" alt="Link preview" class="w-100 h-100 object-fit-cover" referrerpolicy="no-referrer" style="display: block;">
                            <span id="composerLinkPreviewDomainBadge" class="badge rounded-pill bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-2 px-2.5 py-1 small" style="font-size: 0.72rem; letter-spacing: 0.5px; backdrop-filter: blur(6px);"></span>
                        </div>
                        <div class="p-3 link-preview-card__body">
                            <div class="d-flex justify-content-between align-items-center mb-1 link-preview-card__eyebrow">
                                <small id="composerLinkPreviewSiteName" class="text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;"></small>
                                <small class="fw-medium link-preview-card__type"><i class="bi bi-lightning-charge-fill me-1"></i> Rich Preview</small>
                            </div>
                            <h6 id="composerLinkPreviewTitle" class="fw-bold mb-1 link-preview-card__title" style="font-size: 0.95rem;"></h6>
                            <p id="composerLinkPreviewDesc" class="small mb-2 link-preview-card__desc" style="font-size: 0.82rem;"></p>
                            <div class="pt-1.5 d-flex justify-content-between align-items-center link-preview-card__footer">
                                <span id="composerLinkPreviewUrl" class="small text-truncate link-preview-card__url" style="font-size: 0.75rem; max-width: 65%;"></span>
                                <span id="composerLinkPreviewCta" class="badge rounded-pill px-2.5 py-1 link-preview-card__cta" style="font-size: 0.75rem; min-height: 28px; display: inline-flex; align-items: center;"></span>
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

                        <!-- 1. Video Embed & Auto-Trim Container (Direct Upload Focus) -->
                        <div id="videoEmbedContainer" class="composer-drawer video-builder d-none mb-3">
                            <div class="video-builder__header">
                                <span class="video-builder__icon"><i class="bi bi-camera-video-fill"></i></span>
                                <span class="video-builder__title">Add videos</span>
                                <button type="button" id="removeVideoBtn" class="video-builder__close" aria-label="Close video upload drawer">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <label for="videoDirectFileInput" class="video-builder__dropzone">
                                <span class="video-builder__dropzone-icon"><i class="bi bi-cloud-arrow-up-fill"></i></span>
                                <span class="video-builder__dropzone-title">Choose up to 3 videos</span>
                                <span class="video-builder__dropzone-hint">MP4, MOV or WebM &middot; up to 3m portrait or 5m landscape</span>
                            </label>
                            <input type="file" id="videoDirectFileInput" accept="video/mp4,video/quicktime,video/webm" multiple hidden>
                            <input type="url" id="postVideoInput" class="d-none" placeholder="" aria-hidden="true">

                            <!-- Interactive On-Demand Video Auto-Trim Studio -->
                            <div id="videoAutoTrimStudio" class="d-none mt-3 p-3 rounded-3 border" style="background: #0f172a; color: #f8fafc; border-color: rgba(248, 250, 252, 0.16) !important;">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background-color: #a31621; color: #ffffff; font-size: 0.72rem;">
                                            <i class="bi bi-scissors me-1"></i> Auto-Trim Studio
                                        </span>
                                        <span id="videoTrimMetaBadge" class="small" style="color: #cbd5e1; font-size: 0.75rem;">Select highlight window (up to 3m portrait / 5m landscape)</span>
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
                                        Drag sliders to customise your clip window, or tap Auto-Trim below.
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 flex-wrap mt-2 pt-2 border-top border-secondary border-opacity-25">
                                    <button type="button" id="btnConfirmAutoTrim" class="btn btn-sm fw-bold rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1.5 shadow-sm" style="background-color: #a31621; color: #ffffff !important; border: none; min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                        <i class="bi bi-scissors"></i>
                                        <span id="btnConfirmAutoTrimLabel">Auto-Trim &amp; Attach</span>
                                    </button>
                                    <button type="button" id="btnQuickTrimFirst30" class="btn btn-sm btn-outline-light rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0; font-size: 0.78rem;">
                                        <i class="bi bi-lightning-charge-fill text-warning"></i>
                                        <span>First 30s (0:00–0:30)</span>
                                    </button>
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

                            <div class="video-builder__footer">
                                <label for="videoExpirySelect" class="video-builder__expiry">
                                    <i class="bi bi-hourglass-split"></i> Keep for
                                </label>
                                <select id="videoExpirySelect" class="form-select form-select-sm video-builder__select">
                                    <option value="86400">24 hours</option>
                                    <option value="604800">7 days</option>
                                    <option value="2592000" selected>30 days (free)</option>
                                    <option value="31536000" data-premium="true">1 year &middot; Premium</option>
                                    <option value="0" data-premium="true">Forever &middot; Premium</option>
                                </select>
                                <button type="button" id="videoUseLinkBtn" class="video-builder__link">
                                    <i class="bi bi-link-45deg"></i> Have a YouTube link?
                                </button>
                            </div>
                        </div>

                        <!-- 2. Integrated Emoji Picker Drawer (Standard & 3D Family AR Emojis) -->
                        <div id="emojiPickerDrawer" class="composer-drawer d-none mb-3 p-3 rounded-3 shadow-xs" style="background: var(--bg-color); border: 1px solid var(--border-color);">
                            <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2 border-bottom flex-wrap gap-2">
                                <div class="btn-group p-0.5 rounded-pill bg-light border" role="tablist" aria-label="Emoji type selector">
                                    <button type="button" id="emojiTabStandardBtn" class="btn btn-sm rounded-pill fw-bold active px-3 py-1" style="font-size: 0.76rem;">
                                        😀 Standard Emojis
                                    </button>
                                    <button type="button" id="emojiTab3dBtn" class="btn btn-sm rounded-pill fw-semibold text-secondary px-3 py-1" style="font-size: 0.76rem;">
                                        ✨ 3D Family Emojis
                                    </button>
                                    <button type="button" id="emojiTabMeBtn" class="btn btn-sm rounded-pill fw-semibold text-secondary px-3 py-1" style="font-size: 0.76rem;" data-testid="composer-me-tab">
                                        🙋 Me
                                    </button>
                                </div>
                                <button type="button" id="closeEmojiDrawerBtn" class="btn-close" style="font-size: 0.7rem;" aria-label="Close emoji picker"></button>
                            </div>

                            <!-- Tab 1: Standard Diverse Emojis Pane -->
                            <div id="standardEmojiTabPane">
                                <div id="emojiListPost" role="listbox"></div>
                            </div>

                            <!-- Tab 2: ATG Specialised 3D & Augmented Reality (AR) Emojis Pane -->
                            <div id="atg3dEmojiTabPane" class="d-none">
                                <!-- Mode Toggle: Attach as AR Badge vs Insert in Text -->
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2.5 pb-2 border-bottom flex-wrap">
                                    <div class="btn-group p-0.5 rounded-pill bg-light border" role="group">
                                        <button type="button" id="atgModeAttachBtn" class="btn btn-sm rounded-pill fw-bold active px-3 py-1" style="font-size: 0.75rem;">
                                            🌟 Attach as Post's AR Emoji
                                        </button>
                                        <button type="button" id="atgModeInsertBtn" class="btn btn-sm rounded-pill fw-semibold text-secondary px-3 py-1" style="font-size: 0.75rem;">
                                            ✍️ Insert in Caption
                                        </button>
                                    </div>
                                    <span class="text-muted small" style="font-size: 0.72rem;" id="atgModeHelpText">
                                        Click an emoji to attach it as this post's interactive AR badge.
                                    </span>
                                </div>

                                <div class="d-flex flex-wrap gap-2" id="atg3dEmojiComposerList" style="max-height: 260px; overflow-y: auto;">
                                    @php
                                        $atgEmojiCatalog = \App\services\AtgEmojiService::EMOJI_CATALOG;
                                    @endphp
                                    @foreach ($atgEmojiCatalog as $e)
                                        <button type="button" 
                                                class="btn btn-sm btn-light border p-1 rounded-3 d-inline-flex flex-column align-items-center atg-post-emoji-btn" 
                                                data-emoji-id="{{ $e['id'] }}"
                                                data-shortcode=":{{ $e['id'] }}:" 
                                                data-title="{{ $e['title'] }}"
                                                data-desc="{{ $e['meaning'] }}"
                                                style="width: 82px; min-height: 78px; text-decoration: none;" 
                                                title="{{ $e['title'] }}: {{ $e['meaning'] }}">
                                            <img src="/img/emoji/{{ $e['id'] }}.svg?v=20261103c" onerror="this.src='/img/emoji/{{ $e['id'] }}.png?v=20261103c'" width="42" height="42" loading="lazy" class="atg-3d-emoji" alt="{{ $e['title'] }}">
                                            <span class="text-truncate w-100 text-center fw-semibold text-secondary mt-1" style="font-size: 0.65rem;">{{ $e['title'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Tab 3: Me-stickers (RFC-0011): your own avatar reacting -->
                            <div id="meStickerTabPane" class="d-none" data-testid="composer-me-pane">
                                <p class="text-muted small mb-2" style="font-size: 0.78rem;">Your own avatar, reacting. Tap one to add it to your post.</p>
                                <div class="me-sticker-grid" id="meStickerComposerList" aria-live="polite"></div>
                            </div>
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

                        <!-- 5b. Then & Now (before / after) Builder — Concept A: Integrated Heirloom Frame Studio -->
                        <div id="thenNowContainer" class="composer-drawer poll-builder then-now-studio d-none mb-3 shadow-xs">
                            <div class="poll-builder__header then-now-studio__header">
                                <div class="d-flex align-items-center gap-2 min-w-0 flex-wrap">
                                    <span class="poll-builder__icon"><i class="bi bi-hourglass-split"></i></span>
                                    <span class="poll-builder__title" id="thenNowTitle">Then &amp; Now</span>
                                    <span id="thenNowLiveDeltaBadge" class="then-now-studio__delta-pill d-none" aria-live="polite"></span>
                                </div>
                                <div class="d-flex align-items-center gap-2 ms-auto">
                                    <div class="then-now-studio__mode-group" role="group" aria-label="Comparison theme">
                                        <button type="button" id="tnModeClassicBtn" class="then-now-studio__mode-btn active" aria-pressed="true">
                                            ⏳ Time Travel
                                        </button>
                                        <button type="button" id="tnModeEchoBtn" class="then-now-studio__mode-btn text-secondary" aria-pressed="false">
                                            🧬 Like Parent, Like Child
                                        </button>
                                    </div>
                                    <button type="button" id="removeThenNowBtn" class="poll-builder__close" aria-label="Remove Then and Now">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="then_now" id="thenNowFlag" value="">
                            <input type="hidden" name="reply_to_post" id="thenNowReplyTo" value="">
                            <input type="hidden" name="comparison_mode" id="thenNowModeInput" value="classic">
                            <input type="hidden" name="mystery_year" id="thenNowMysteryInput" value="0">
                            <input type="hidden" name="then_now_voice_base64" id="thenNowVoiceInput" value="">

                            <!-- Integrated Polaroid / Heirloom Split-Frame Cards -->
                            <div class="then-now-slots position-relative">
                                <!-- Centre Time-Bridge Badge -->
                                <div class="then-now-slots__bridge" aria-hidden="true" title="Side-by-side comparison">
                                    <i class="bi bi-arrow-right"></i>
                                </div>

                                <!-- THEN FRAME CARD -->
                                <div class="then-now-slot then-now-card then-now-card--then">
                                    <div class="then-now-card__header">
                                        <span class="then-now-card__tag then-now-card__tag--then" id="thenCardTag">THEN</span>
                                        <input type="text" name="then_label" id="thenLabelInput" class="poll-builder__option then-now-card__year-input" placeholder="Year, e.g. 1998" maxlength="40" aria-label="Then year or label">
                                    </div>
                                    <label for="thenImgInput" class="then-now-slot__pick" id="thenSlotPick">
                                        <img id="thenPreview" class="d-none" alt="Then photo preview">
                                        <span class="then-now-slot__replace-pill d-none" id="thenReplaceBadge"><i class="bi bi-arrow-repeat me-1"></i>Change</span>
                                        <span id="thenPlaceholder" class="then-now-slot__empty">
                                            <span class="then-now-slot__icon-ring"><i class="bi bi-image"></i></span>
                                            <span class="then-now-slot__prompt">Pick the <strong id="thenPromptStrong">Then</strong> photo</span>
                                            <span class="then-now-slot__upload-pill"><i class="bi bi-folder2-open"></i> Upload from device</span>
                                        </span>
                                    </label>
                                    <input type="file" id="thenImgInput" accept="image/*" hidden>
                                    <div class="then-now-card__footer">
                                        <button type="button" id="thenArchivePickBtn" class="then-now-card__action-btn then-now-card__action-btn--archive">
                                            <i class="bi bi-collection-fill"></i>
                                            <span>Pick from Family Archive</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- NOW FRAME CARD -->
                                <div class="then-now-slot then-now-card then-now-card--now">
                                    <div class="then-now-card__header">
                                        <span class="then-now-card__tag then-now-card__tag--now" id="nowCardTag">NOW</span>
                                        <input type="text" name="now_label" id="nowLabelInput" class="poll-builder__option then-now-card__year-input" placeholder="Year, e.g. 2026" maxlength="40" aria-label="Now year or label">
                                    </div>
                                    <label for="nowImgInput" class="then-now-slot__pick" id="nowSlotPick">
                                        <img id="nowPreview" class="d-none" alt="Now photo preview">
                                        <span class="then-now-slot__replace-pill d-none" id="nowReplaceBadge"><i class="bi bi-arrow-repeat me-1"></i>Change</span>
                                        <span id="nowPlaceholder" class="then-now-slot__empty">
                                            <span class="then-now-slot__icon-ring"><i class="bi bi-image"></i></span>
                                            <span class="then-now-slot__prompt">Pick the <strong id="nowPromptStrong">Now</strong> photo</span>
                                            <span class="then-now-slot__upload-pill"><i class="bi bi-folder2-open"></i> Upload from device</span>
                                        </span>
                                    </label>
                                    <input type="file" id="nowImgInput" accept="image/*" hidden>
                                    <div class="then-now-card__footer">
                                        <button type="button" id="nowCameraBtn" class="then-now-card__action-btn then-now-card__action-btn--camera" title="Overlays your Then photo on the camera so you can line up the exact same pose">
                                            <i class="bi bi-camera-fill"></i>
                                            <span>Ghost-Pose Camera</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Family Archive Photo Picker Drawer (for Then Slot) -->
                            <div id="thenArchiveDrawer" class="d-none mt-2 p-2.5 rounded-3 border then-now-archive-drawer">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <span class="small fw-bold then-now-archive-drawer__title"><i class="bi bi-clock-history me-1"></i> Tap an archive photo to set as &ldquo;Then&rdquo;</span>
                                    <button type="button" id="closeThenArchiveBtn" class="btn-close" style="font-size: 0.65rem; min-width: 44px; min-height: 44px;" aria-label="Close archive picker"></button>
                                </div>
                                <div id="thenArchiveGrid" class="d-flex gap-2 overflow-x-auto py-1" style="min-height: 76px;"></div>
                            </div>

                            <!-- Unified Bottom Story Bar: 15-Second Voice Story + Guess the Year Challenge -->
                            <div class="then-now-story-bar mt-2.5">
                                <div class="d-flex align-items-center gap-2 flex-wrap min-w-0">
                                    <button type="button" id="tnVoiceRecordBtn" class="then-now-story-bar__voice-btn">
                                        <i class="bi bi-mic-fill"></i>
                                        <span id="tnVoiceRecordLabel">Add 15s Voice Story</span>
                                    </button>
                                    <span id="tnVoiceStatusText" class="then-now-story-bar__hint">Tell the story behind these years</span>
                                </div>
                                <div id="tnVoicePreviewWrap" class="d-none align-items-center gap-1.5">
                                    <audio id="tnVoiceAudioPreview" controls preload="metadata" style="height: 32px; max-width: 165px;"></audio>
                                    <button type="button" id="tnVoiceClearBtn" class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px; min-height: 44px;" aria-label="Remove voice story">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                                <button type="button" id="tnMysteryToggleBtn" class="then-now-story-bar__mystery-btn" aria-pressed="false" title="Blur the Then year on the feed so relatives can guess it before revealing">
                                    <i class="bi bi-dice-5-fill text-warning"></i>
                                    <span>Guess the Year</span>
                                </button>
                            </div>
                        </div>

                        <!-- 6. Mobile Gallery Direct Share Guide Drawer (Android & Apple iOS) -->
                        <div id="iosShareDrawer" class="composer-drawer d-none mb-3 p-3 rounded-3 shadow-xs" style="background: var(--bg-color); border: 1px solid var(--border-color);">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                <span class="fw-bold small text-dark d-flex align-items-center gap-1.5">
                                    <i class="bi bi-phone-fill text-primary fs-6"></i> Mobile Gallery 1-Tap Share
                                </span>
                                <button type="button" id="closeIosShareDrawerBtn" class="btn-close" style="font-size: 0.7rem;" aria-label="Close guide"></button>
                            </div>
                            
                            <!-- Apple iPhone Section -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center gap-1.5 fw-semibold small text-dark mb-1">
                                    <i class="bi bi-apple fs-6"></i> Apple iPhone (Apple Photos)
                                </div>
                                <p class="small text-muted mb-2" style="font-size: 0.8rem; line-height: 1.45;">
                                    Share photos directly from <strong>Apple Photos</strong> without opening Safari:
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
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-medium d-flex align-items-center gap-1.5" style="font-size: 0.78rem;" onclick="if(typeof window.pastePhotoFromClipboard==='function'){window.pastePhotoFromClipboard();}">
                                        <i class="bi bi-clipboard2-plus-fill"></i> Paste from Camera Roll
                                    </button>
                                </div>
                                <p class="small text-muted mb-0" style="font-size: 0.76rem; line-height: 1.4;">
                                    <em>Frictionless tip:</em> You can also tap <strong>Copy</strong> on any picture in Apple Photos, then tap the blue <strong>Clipboard icon</strong> in the toolbar below to attach it instantly!
                                </p>
                            </div>

                            <!-- Android Section -->
                            <div class="pt-2 border-top">
                                <div class="d-flex align-items-center gap-1.5 fw-semibold small text-dark mb-1">
                                    <i class="bi bi-android2 text-success fs-6"></i> Android (Google Photos &amp; Gallery)
                                </div>
                                <p class="small text-muted mb-0" style="font-size: 0.78rem; line-height: 1.45;">
                                    Install FamilyPlatform to your home screen (tap browser menu &vellip; &rarr; <strong>Add to Home screen</strong>). Then in <strong>Google Photos</strong> or <strong>Samsung Gallery</strong>, tap <strong>Share</strong> &rarr; select <strong>FamilyPlatform</strong> to open the composer with your photo pre-loaded!
                                </p>
                            </div>
                        </div>

                        <!-- 7. Linked Media Drawer (Zero-Storage Architecture: Photos & Videos) -->
                        <div id="googlePhotoDrawer" class="composer-drawer video-builder link-builder d-none mb-3">
                            <div class="video-builder__header">
                                <span class="video-builder__icon"><i class="bi bi-link-45deg"></i></span>
                                <span class="video-builder__title">Link photos &amp; videos</span>
                                <button type="button" id="closeGooglePhotoDrawerBtn" class="video-builder__close" aria-label="Close media drawer">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <div class="link-builder__field">
                                <i class="bi bi-link-45deg link-builder__field-icon" aria-hidden="true"></i>
                                <input type="text" id="googlePhotoLinkInput" class="link-builder__input" placeholder="Paste one or more links…" inputmode="url" autocomplete="off" aria-label="Paste photo or video link">
                                <button type="button" id="btnAttachGooglePhoto" class="link-builder__attach">Attach</button>
                            </div>

                            <div class="link-builder__sources" aria-label="Supported sources">
                                <span class="link-builder__chip"><i class="bi bi-google"></i> Google Photos</span>
                                <span class="link-builder__chip"><i class="bi bi-apple"></i> iCloud</span>
                                <span class="link-builder__chip"><i class="bi bi-youtube"></i> YouTube</span>
                                <span class="link-builder__chip"><i class="bi bi-vimeo"></i> Vimeo</span>
                                <span class="link-builder__chip"><i class="bi bi-globe2"></i> Any web link</span>
                            </div>

                            <p class="link-builder__note">
                                <i class="bi bi-cloud-check"></i> Full quality, no upload &mdash; several links become a swipeable carousel.
                            </p>

                            <details class="link-builder__help">
                                <summary><i class="bi bi-question-circle"></i> How do I get a link?</summary>
                                <p>In Google Photos or iCloud, select your photo(s) &rarr; <strong>Share</strong> &rarr; <strong>Create link</strong> (or copy image address), then paste it above.</p>
                            </details>
                        </div>

                        <input type="hidden" name="ar_emoji" id="attachedArEmojiInput" value="">

                        <!-- Attached 3D AR Emoji Preview Strip -->
                        <div id="attachedArEmojiPreview" class="d-none mb-3 p-2.5 rounded-3 d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(217, 119, 6, 0.16) 100%); border: 1.5px solid rgba(245, 158, 11, 0.45); box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);">
                            <div class="d-flex align-items-center gap-2.5">
                                <span id="attachedArEmojiIcon" class="atg-3d-emoji" style="transform: scale(0.85); display: inline-block;"></span>
                                <div>
                                    <span class="badge rounded-pill text-white fw-bold px-2 py-0.5" style="background: linear-gradient(135deg, #f59e0b, #d97706); font-size: 0.68rem; letter-spacing: 0.04em;">
                                        ✨ FEATURED 3D AR EMOJI ATTACHED
                                    </span>
                                    <span class="text-secondary small fw-bold d-block mt-0.5" id="attachedArEmojiTitle"></span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1.5">
                                <button type="button" class="btn btn-sm btn-outline-warning text-dark py-0.5 px-2 rounded-pill fw-bold" id="previewAttachedArBtn" style="font-size: 0.72rem;">
                                    View in AR
                                </button>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none ms-1" id="removeAttachedArEmojiBtn" title="Remove 3D AR Emoji" aria-label="Remove 3D AR Emoji">
                                    <i class="bi bi-x-circle-fill fs-5"></i>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Composer action tools dock -->
                    <div class="add-to-post-box composer-tools">
                        <div class="composer-tools__grid">
                            <label for="imageUpload" class="btn-composer-action composer-tool action-photo mb-0" title="Attach Photos - Upload family pictures or camera roll memories" aria-label="Attach photos">
                                <i class="bi bi-image-fill"></i>
                            </label>
                            <input type="file" name="post_img[]" id="imageUpload" accept="image/*,video/*" multiple hidden>

                            <button type="button" class="btn-composer-action composer-tool action-link-media" id="addGooglePhotoBtn" title="Link Photos &amp; Videos - Share high-resolution photos or videos from Google Photos, iCloud, YouTube, Vimeo, or web links (Zero Storage)" aria-label="Link photos and videos" style="color: #a31621;">
                                <i class="bi bi-link-45deg fs-5"></i>
                            </button>

                            <button type="button" class="btn-composer-action composer-tool action-emoji" id="emojiPost" title="Add Emoji - Express feelings with diverse standard and 3D family emojis" aria-label="Add emoji">
                                <i class="bi bi-emoji-smile-fill"></i>
                            </button>

                            <button type="button" class="btn-composer-action composer-tool action-video" id="addVideoBtn" title="Upload Video - Share family video clips (up to 3m portrait or 5m landscape)" aria-label="Upload video">
                                <i class="bi bi-camera-video-fill"></i>
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

                            <button type="button" class="btn-composer-action composer-tool action-clipboard" id="pasteClipboardBtn" title="Paste Photo - 1-Tap attach copied photo from Apple Photos or phone gallery" aria-label="Paste copied photo from camera roll" onclick="if(typeof window.pastePhotoFromClipboard==='function'){window.pastePhotoFromClipboard();}" style="color: #0284c7;">
                                <i class="bi bi-clipboard2-plus-fill"></i>
                            </button>

                            <button type="button" class="btn-composer-action composer-tool action-apple" id="iosShareBtn" title="Phone Gallery Share - Setup 1-tap sharing from Apple Photos or Android gallery" aria-label="Phone Gallery Direct Share Setup">
                                <i class="bi bi-phone-fill"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Close Step 1 View -->
                    </div>

                    @if($canChooseAudience)
                    <!-- Step 2 View: Who Should See This Post? Destination Screen -->
                    <div id="composerStep2" class="composer-audience-step d-none py-1" data-confirmed="false">
                        <div class="mb-3 px-1">
                            <h6 class="fw-bold mb-1" style="color: var(--text-main, #0f172a); font-size: 1.05rem;">
                                <i class="bi bi-shield-check text-primary me-1.5"></i> {{ __('modal.who_should_see_post', 'Who should see this post?') }}
                            </h6>
                            <p class="text-muted small mb-0">Choose which family circles or roots can view and interact with this memory.</p>
                        </div>

                        <div class="composer-audience-cards d-flex flex-column gap-2.5">
                            <!-- Option 1: All Circles & Connected Kin -->
                            <label class="composer-audience-card p-3 rounded-3 border d-flex align-items-start gap-3 cursor-pointer transition-all active" 
                                   style="cursor: pointer;"
                                   onclick="selectPostAudienceCard(this, 'all', 'All Family Circles &amp; Connected Kin', 'bi-people-fill text-primary', '')">
                                <input type="radio" name="audience_choice_radio" value="all" data-scope="all" data-code="" class="form-check-input mt-1 flex-shrink-0" checked style="cursor: pointer; width: 1.15rem; height: 1.15rem;">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-1.5 fw-bold" style="font-size: 0.95rem; color: var(--text-main, #0f172a);">
                                        <i class="bi bi-people-fill text-primary fs-5"></i>
                                        <span>All Family Circles &amp; Connected Kin</span>
                                    </div>
                                    <div class="text-muted small mt-0.5" style="font-size: 0.82rem; line-height: 1.4;">
                                        Broadcast to all your branches ({{ implode(', ', array_map(fn($c) => '#' . $c, $allCodes)) }}) and approved Connected Kin.
                                    </div>
                                </div>
                            </label>

                            @if(!empty($audienceItems))
                                @foreach($audienceItems as $item)
                                    <label class="composer-audience-card p-3 rounded-3 border d-flex align-items-start gap-3 cursor-pointer transition-all" 
                                           style="cursor: pointer;"
                                           onclick="selectPostAudienceCard(this, '{{ $item['kind'] }}', '{{ addslashes($item['label']) }}', '{{ $item['icon'] }}', '{{ $item['code'] }}')">
                                        <input type="radio" name="audience_choice_radio" value="{{ $item['code'] }}" data-scope="{{ $item['kind'] }}" data-code="{{ $item['code'] }}" class="form-check-input mt-1 flex-shrink-0" style="cursor: pointer; width: 1.15rem; height: 1.15rem;">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center gap-1.5 fw-bold" style="font-size: 0.95rem; color: var(--text-main, #0f172a);">
                                                <i class="bi {{ $item['icon'] }} fs-5"></i>
                                                <span>{{ $item['label'] }}</span>
                                            </div>
                                            <div class="text-muted small mt-0.5" style="font-size: 0.82rem; line-height: 1.4;">
                                                {{ $item['description'] }}
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            @endif

                            @if(count($birthCodes) > 1)
                                <label class="composer-audience-card p-3 rounded-3 border d-flex align-items-start gap-3 cursor-pointer transition-all" 
                                       style="cursor: pointer;"
                                       onclick="selectPostAudienceCard(this, 'birth', 'All Birth Roots (Paternal &amp; Maternal)', 'bi-tree-fill text-warning', '')">
                                    <input type="radio" name="audience_choice_radio" value="all_birth" data-scope="birth" data-code="" class="form-check-input mt-1 flex-shrink-0" style="cursor: pointer; width: 1.15rem; height: 1.15rem;">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-1.5 fw-bold" style="font-size: 0.95rem; color: var(--text-main, #0f172a);">
                                            <i class="bi bi-tree-fill text-warning fs-5"></i>
                                            <span>All Birth Roots (Paternal &amp; Maternal)</span>
                                        </div>
                                        <div class="text-muted small mt-0.5" style="font-size: 0.82rem; line-height: 1.4;">
                                            Visible to both paternal and maternal branches ({{ implode(', ', array_map(fn($c) => '#' . $c, $birthCodes)) }}). Connected Kin and marital household excluded.
                                        </div>
                                    </div>
                                </label>
                            @endif
                        </div>
                    </div>
                    @endif

                </div>

                <!-- Sticky footer: Step 1 Post Button and Step 2 Confirmation Buttons -->
                <div class="composer-sheet__footer flex-shrink-0" id="composerFooterStep1">
                    <button type="button" id="submitPost" name="submit" class="btn btn-primary fw-semibold submitPost w-100" style="min-height: 46px;">
                        {{ __('feed.post_btn', 'Post') }}
                    </button>
                </div>

                @if($canChooseAudience)
                <div class="composer-sheet__footer flex-shrink-0 d-none align-items-center gap-2" id="composerFooterStep2">
                    <button type="button" id="composerBackToStep1Btn" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold" style="min-height: 46px; white-space: nowrap;">
                        <i class="bi bi-arrow-left me-1"></i> Back to Edit
                    </button>
                    <button type="button" id="composerConfirmShareBtn" class="btn btn-primary rounded-pill px-4 fw-bold flex-grow-1" style="min-height: 46px; white-space: nowrap;">
                        <i class="bi bi-send-fill me-1.5"></i> Confirm &amp; Share Post
                    </button>
                </div>
                @endif

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

<script nonce="{{ csp_nonce($nonce ?? null) }}">
// Global Drawer Mutual Exclusivity Coordinator (Prevents crowded modal drawers)
window.closeAllComposerDrawers = function(exceptId) {
    const drawers = [
        'videoEmbedContainer',
        'emojiPickerDrawer',
        'gifPickerDrawer',
        'tagPeopleContainer',
        'pollCreationContainer',
        'thenNowContainer',
        'iosShareDrawer',
        'googlePhotoDrawer'
    ];
    drawers.forEach(id => {
        if (id !== exceptId) {
            const el = document.getElementById(id);
            if (el) el.classList.add('d-none');
        }
    });
    if (exceptId !== 'thenNowContainer') {
        const stageBar = document.getElementById('composerTextStageBar');
        if (stageBar && stageBar.dataset.hiddenByThenNow === '1') {
            stageBar.classList.remove('d-none');
            delete stageBar.dataset.hiddenByThenNow;
        }
    }
};

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
    let activePreviewController = null;
    let dismissedUrl = null;
    let lastFetchedUrl = null;
    let inFlightUrl = null;

    const textarea = document.getElementById('postMessage');
    const editor = document.getElementById('postMessageEditor');
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

    function hideAndClearPreview() {
        if (container) container.classList.add('d-none');
        if (imgEl) imgEl.removeAttribute('src');
        if (imgWrapper) imgWrapper.classList.add('d-none');
        if (titleEl) titleEl.textContent = '';
        if (descEl) descEl.textContent = '';
        if (domainBadge) domainBadge.textContent = '';
        if (siteNameEl) siteNameEl.textContent = '';
        if (urlEl) urlEl.textContent = '';
        if (ctaEl) ctaEl.textContent = '';
    }

    function resetLinkPreview() {
        if (previewDebounceTimer) {
            clearTimeout(previewDebounceTimer);
            previewDebounceTimer = null;
        }
        if (activePreviewController) {
            try { activePreviewController.abort(); } catch (_) {}
            activePreviewController = null;
        }
        dismissedUrl = null;
        lastFetchedUrl = null;
        inFlightUrl = null;
        hideAndClearPreview();
    }
    window.__resetLinkPreview = resetLinkPreview;

    if (closeBtn && container) {
        closeBtn.addEventListener('click', function() {
            hideAndClearPreview();
            dismissedUrl = lastFetchedUrl || inFlightUrl;
        });
    }

    const modalEl = document.getElementById('postModal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', resetLinkPreview);
    }

    function extractFirstComposerUrl(text) {
        if (!text || typeof text !== 'string') return null;
        const pattern = /(https?:\/\/[^\s<>"'`]+)|(?:^|[\s(\[{"'])(www\.[a-z0-9-]+(?:\.[a-z0-9-]+)+(?:\/[^\s<>"'`]*)?)|(?:^|[\s(\[{"'])([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9-]{1,63})*\.(?:com|org|net|io|app|dev|co\.uk|org\.uk|uk|edu|gov|ai|me|info|biz|tv|co)(?:\/[^\s<>"'`]*)?)(?![a-z0-9@._-])/i;
        const match = text.match(pattern);
        if (!match) return null;
        const raw = match[1] ? match[1] : ('https://' + (match[2] || match[3] || ''));
        const cleaned = raw.replace(/:[a-z0-9_]+_3d:.*$/i, '').replace(/[.,;:!?)]+$/, '');
        return (cleaned && cleaned !== 'https://') ? cleaned : null;
    }

    function checkAndUnfurlLink(immediate = false) {
        const val = (editor ? editor.innerText : '') || (textarea ? textarea.value : '');
        const url = extractFirstComposerUrl(val);
        if (!url) {
            if (previewDebounceTimer) clearTimeout(previewDebounceTimer);
            if (activePreviewController) {
                try { activePreviewController.abort(); } catch (_) {}
                activePreviewController = null;
            }
            hideAndClearPreview();
            lastFetchedUrl = null;
            inFlightUrl = null;
            return;
        }
        if (url === dismissedUrl || url === lastFetchedUrl || url === inFlightUrl) {
            return;
        }

        // Immediately clear any stale previous preview while the new URL resolves
        hideAndClearPreview();
        lastFetchedUrl = null;

        if (previewDebounceTimer) clearTimeout(previewDebounceTimer);
        if (activePreviewController) {
            try { activePreviewController.abort(); } catch (_) {}
            activePreviewController = null;
        }

        const runFetch = async () => {
            const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
            activePreviewController = controller;
            inFlightUrl = url;
            try {
                const response = await fetch('/api/link-preview?url=' + encodeURIComponent(url), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    signal: controller ? controller.signal : undefined
                });
                if (!response.ok) return;
                const res = await response.json();
                if (controller && controller.signal.aborted) return;
                if (res?.status === 'success' && res?.data && (res.data.title || res.data.image || res.data.domain)) {
                    const d = res.data;
                    lastFetchedUrl = url;
                    if (d.is_photo_link || d.is_google_photo) {
                        const hasVideos = Array.isArray(d.album_videos) && d.album_videos.length > 0;
                        if (titleEl) titleEl.textContent = hasVideos ? 'Linked Family Videos (Zero Storage)' : 'Linked Photo (Zero Storage)';
                        if (descEl) descEl.textContent = hasVideos
                            ? 'These videos will stream natively on the family feed with zero storage used. The link will be hidden from the caption.'
                            : 'This photo will appear natively on the family feed with zero storage used. The link will be automatically hidden from the caption.';
                        if (urlEl) urlEl.textContent = d.site_name || d.domain || 'Google Photos';
                        if (domainBadge) domainBadge.textContent = d.is_google_photo ? 'Google Photos' : 'Linked Photo';
                        if (siteNameEl) siteNameEl.textContent = d.site_name || 'Google Photos';
                        if (ctaEl) ctaEl.textContent = hasVideos ? 'Native Video Album' : 'Native Photo Card';
                    } else {
                        if (titleEl) titleEl.textContent = d.title || d.domain || '';
                        if (descEl) descEl.textContent = d.description || '';
                        if (urlEl) urlEl.textContent = d.site_name || d.domain || 'Web Link';
                        if (domainBadge) domainBadge.textContent = d.is_party_event ? 'PartyPlatform' : (d.domain || 'Link');
                        if (siteNameEl) siteNameEl.textContent = d.site_name || d.domain || '';
                        if (ctaEl) ctaEl.textContent = d.cta_label || 'View Event / RSVP →';
                    }
                    if (imgEl && imgWrapper) {
                        if (d.image) {
                            imgEl.referrerPolicy = 'no-referrer';
                            imgEl.src = d.image;
                            imgWrapper.classList.remove('d-none');
                        } else {
                            imgEl.removeAttribute('src');
                            imgWrapper.classList.add('d-none');
                        }
                    }
                    if (container) {
                        const currentStyle = document.getElementById('composerPostStyleInput')?.value || '';
                        if (currentStyle) {
                            container.classList.add('fp-text-stage__composer-preview');
                        } else {
                            container.classList.remove('fp-text-stage__composer-preview');
                        }
                        container.classList.remove('d-none');
                    }
                }
            } catch (_) {
                // Fail silently on abort or network failure
            } finally {
                if (inFlightUrl === url) inFlightUrl = null;
                if (activePreviewController === controller) activePreviewController = null;
            }
        };

        if (immediate) {
            runFetch();
        } else {
            previewDebounceTimer = setTimeout(runFetch, 150);
        }
    }

    window.__unfurlComposerLinkPreview = checkAndUnfurlLink;

    if (textarea) {
        textarea.addEventListener('input', () => checkAndUnfurlLink(false));
    }
    if (editor) {
        editor.addEventListener('input', () => checkAndUnfurlLink(false));
        editor.addEventListener('keyup', () => checkAndUnfurlLink(false));
        editor.addEventListener('paste', () => setTimeout(() => checkAndUnfurlLink(true), 20));
    }
})();

// Integrated Emoji Picker (Tabs: Standard Emojis + 3D Family AR Emojis) & Rich Composer Editor
(function() {
    const tabStandardBtn = document.getElementById('emojiTabStandardBtn');
    const tab3dBtn = document.getElementById('emojiTab3dBtn');
    const standardPane = document.getElementById('standardEmojiTabPane');
    const atg3dPane = document.getElementById('atg3dEmojiTabPane');
    const textarea = document.getElementById('postMessage');
    const editor = document.getElementById('postMessageEditor');
    
    const modeAttachBtn = document.getElementById('atgModeAttachBtn');
    const modeInsertBtn = document.getElementById('atgModeInsertBtn');
    const modeHelpText = document.getElementById('atgModeHelpText');
    const attachedInput = document.getElementById('attachedArEmojiInput');
    const previewStrip = document.getElementById('attachedArEmojiPreview');
    const previewIcon = document.getElementById('attachedArEmojiIcon');
    const previewTitle = document.getElementById('attachedArEmojiTitle');
    const removeBtn = document.getElementById('removeAttachedArEmojiBtn');
    const previewArBtn = document.getElementById('previewAttachedArBtn');

    let currentMode = 'attach'; // 'attach' | 'insert'

    const ATG_EMOJI_REGEX = /:([a-z0-9_]+_3d):/gi;

    // Helper: Build live emoji element
    function createLiveEmojiSpan(emojiId, title) {
        const span = document.createElement('span');
        span.className = 'atg-emoji-container atg-live-emoji';
        span.contentEditable = 'false';
        span.dataset.emojiId = emojiId;
        span.title = title || emojiId.replace(/_/g, ' ');
        span.innerHTML = `<img src="/img/emoji/${emojiId}.svg?v=20261103c" onerror="this.src='/img/emoji/${emojiId}.png?v=20261103c'" class="atg-3d-emoji" width="26" height="26" alt="${emojiId}" style="pointer-events:none; vertical-align:middle;">`;
        return span;
    }

    // Sync Editor -> Hidden Textarea (serializes emojis as :shortcode:)
    let isInternalSync = false;
    window.syncEditorToTextarea = function() {
        if (!editor || !textarea) return;
        isInternalSync = true;
        try {
            let text = '';
            function traverse(node) {
                if (node.nodeType === Node.TEXT_NODE) {
                    text += node.textContent;
                } else if (node.nodeType === Node.ELEMENT_NODE) {
                    const el = node;
                    if (el.hasAttribute('data-sticker-token')) {
                        if (text.length > 0 && !/\s$/.test(text)) {
                            text += ' ';
                        }
                        text += el.getAttribute('data-sticker-token') + ' ';
                        return;
                    }
                    if (el.hasAttribute('data-emoji-id')) {
                        if (text.length > 0 && !/\s$/.test(text)) {
                            text += ' ';
                        }
                        text += ':' + el.getAttribute('data-emoji-id') + ': ';
                        return;
                    }
                    if (el.tagName === 'BR') {
                        text += '\n';
                        return;
                    }
                    const isBlock = ['DIV', 'P', 'LI'].includes(el.tagName);
                    if (isBlock && text.length > 0 && !text.endsWith('\n')) {
                        text += '\n';
                    }
                    for (let child = el.firstChild; child; child = child.nextSibling) {
                        traverse(child);
                    }
                }
            }
            for (let child = editor.firstChild; child; child = child.nextSibling) {
                traverse(child);
            }
            textarea.value = text.replace(/\u00A0/g, ' ').replace(/ {2,}/g, ' ');
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
        } finally {
            isInternalSync = false;
        }
    };

    // Sync Hidden Textarea -> Editor (turns :shortcode: into living 3D emoji spans)
    window.syncTextareaToEditor = function(force = false) {
        // While user is actively typing in the editor or during internal sync, never overwrite editor DOM unless forced!
        if (!editor || !textarea || isInternalSync || (!force && document.activeElement === editor)) return;
        let raw = textarea.value || '';
        const styleMatch = raw.match(/\[style:([a-z0-9_]+)\]/i);
        if (styleMatch && styleMatch[1]) {
            raw = raw.replace(/\[style:[a-z0-9_]+\]\s*/gi, '');
            if (typeof window.__setComposerTextStage === 'function') {
                window.__setComposerTextStage(styleMatch[1].toLowerCase());
            }
        }
        if (!raw.trim()) {
            editor.innerHTML = '';
            return;
        }
        let safe = raw
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/\n/g, '<br>');

        const atgRegex = /:([a-z0-9_]+_3d):/gi;
        safe = safe.replace(atgRegex, (match, emojiId) => {
            return `<span class="atg-emoji-container atg-live-emoji" contenteditable="false" data-emoji-id="${emojiId}"><img src="/img/emoji/${emojiId}.svg?v=20261103c" onerror="this.src='/img/emoji/${emojiId}.png?v=20261103c'" class="atg-3d-emoji" width="26" height="26" alt="${emojiId}" style="pointer-events:none; vertical-align:middle;"></span>`;
        });

        safe = safe.replace(/:me_(hello|laugh|wow|love|nod|dance|point|clap)@([A-Za-z0-9_-]{1,64}):/g, (match, reaction, uid) => {
            return `<span class="atg-me-sticker" contenteditable="false" data-sticker-token="${match}"><img src="/api/emoji/avatar-sticker/${uid}/${reaction}?v=live2" width="56" height="56" alt="My avatar: ${reaction}" style="pointer-events:none;"></span>`;
        });

        isInternalSync = true;
        editor.innerHTML = safe;
        isInternalSync = false;
    };

    // Intercept programmatic writes to textarea.value so editor updates instantly
    try {
        const protoDesc = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value');
        if (protoDesc) {
            Object.defineProperty(textarea, 'value', {
                get: function() {
                    return protoDesc.get.call(this);
                },
                set: function(newVal) {
                    protoDesc.set.call(this, newVal);
                    window.syncTextareaToEditor(true);
                }
            });
        }
    } catch (_) {}

    textarea.addEventListener('input', () => window.syncTextareaToEditor());

    // Insert Live 3D Emoji at current cursor in the Editor
    window.insertAtgEmojiIntoComposer = function(emojiId, title) {
        if (!editor) return;
        editor.focus();

        const emojiSpan = createLiveEmojiSpan(emojiId, title);
        const spaceNode = document.createTextNode('\u00A0');

        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0 && editor.contains(sel.anchorNode)) {
            const range = sel.getRangeAt(0);
            range.deleteContents();
            range.insertNode(spaceNode);
            range.insertNode(emojiSpan);
            range.setStartAfter(spaceNode);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
        } else {
            editor.appendChild(emojiSpan);
            editor.appendChild(spaceNode);
            const range = document.createRange();
            range.setStartAfter(spaceNode);
            range.collapse(true);
            const newSel = window.getSelection();
            newSel.removeAllRanges();
            newSel.addRange(range);
        }

        window.syncEditorToTextarea();
    };

    // Editor input listener: convert typed :emoji_3d: live and sync
    if (editor) {
        editor.addEventListener('input', function() {
            // Auto-convert any typed shortcodes like :family_crest_3d:
            const shortcodeRegex = /:([a-z0-9_]+_3d):/i;
            const walker = document.createTreeWalker(editor, NodeFilter.SHOW_TEXT, null);
            let textNode;
            while ((textNode = walker.nextNode())) {
                const match = shortcodeRegex.exec(textNode.nodeValue);
                if (match) {
                    const fullMatch = match[0];
                    const emojiId = match[1];
                    const idx = match.index;
                    const before = textNode.nodeValue.substring(0, idx);
                    const after = textNode.nodeValue.substring(idx + fullMatch.length);

                    const span = createLiveEmojiSpan(emojiId, emojiId);
                    const parent = textNode.parentNode;

                    if (before) parent.insertBefore(document.createTextNode(before), textNode);
                    parent.insertBefore(span, textNode);
                    const afterNode = document.createTextNode(after || '\u00A0');
                    parent.insertBefore(afterNode, textNode);
                    parent.removeChild(textNode);

                    // Place cursor after new emoji
                    const sel = window.getSelection();
                    const range = document.createRange();
                    range.setStartAfter(afterNode);
                    range.collapse(true);
                    sel.removeAllRanges();
                    sel.addRange(range);
                    break;
                }
            }

            window.syncEditorToTextarea();
        });

        // Helper to extract image files from clipboard (handles files, screenshots, copied browser bitmaps)
        function extractClipboardImages(clipboard) {
            if (!clipboard) return [];
            const images = [];

            if (clipboard.files && clipboard.files.length) {
                for (let i = 0; i < clipboard.files.length; i++) {
                    const f = clipboard.files[i];
                    if (f && f.type && f.type.startsWith('image/')) {
                        images.push(f);
                    }
                }
            }

            if (!images.length && clipboard.items && clipboard.items.length) {
                for (let i = 0; i < clipboard.items.length; i++) {
                    const item = clipboard.items[i];
                    if (item && item.kind === 'file' && item.type && item.type.startsWith('image/')) {
                        const blob = item.getAsFile();
                        if (blob) {
                            const ext = item.type.split('/')[1] ? item.type.split('/')[1].replace('+xml', '') : 'png';
                            const file = new File([blob], `pasted_image_${Date.now()}_${i}.${ext}`, { type: item.type });
                            images.push(file);
                        }
                    }
                }
            }

            return images;
        }

        // Helper to assign pasted images into #imageUpload and trigger the media preview pipeline
        function attachPastedImagesToComposer(images) {
            if (!images || !images.length) return false;
            const imageInput = document.getElementById('imageUpload');
            if (!imageInput) return false;

            const dt = new DataTransfer();
            images.forEach(f => dt.items.add(f));
            imageInput.files = dt.files;
            imageInput.dispatchEvent(new Event('change', { bubbles: true }));
            return true;
        }

        // Intercept paste on the rich text editor (handles both image attachments and clean plain text)
        editor.addEventListener('paste', function(e) {
            const clipboard = e.clipboardData || window.clipboardData;
            if (!clipboard) return;

            const pastedImages = extractClipboardImages(clipboard);
            if (pastedImages.length > 0) {
                e.preventDefault();
                e.__fpImagePasteHandled = true;
                attachPastedImagesToComposer(pastedImages);
            }

            const text = clipboard.getData('text/plain');
            if (text) {
                e.preventDefault();
                if (document.queryCommandSupported('insertText')) {
                    document.execCommand('insertText', false, text);
                } else {
                    const sel = window.getSelection();
                    if (sel && sel.rangeCount > 0) {
                        const range = sel.getRangeAt(0);
                        range.deleteContents();
                        range.insertNode(document.createTextNode(text));
                    }
                }
                editor.dispatchEvent(new Event('input', { bubbles: true }));
            } else if (pastedImages.length === 0) {
                e.preventDefault();
            }
        });

        // Modal-level paste listener so pasting anywhere in the open composer attaches images
        const composerModalEl = document.getElementById('postModal');
        if (composerModalEl) {
            composerModalEl.addEventListener('paste', function(e) {
                if (e.__fpImagePasteHandled || e.defaultPrevented) return;
                const activeEl = document.activeElement;
                const isOtherTextInput = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA') && activeEl.id !== 'postMessage';

                const clipboard = e.clipboardData || window.clipboardData;
                const pastedImages = extractClipboardImages(clipboard);
                if (pastedImages.length > 0) {
                    e.preventDefault();
                    e.__fpImagePasteHandled = true;
                    attachPastedImagesToComposer(pastedImages);
                } else if (!isOtherTextInput && activeEl !== editor) {
                    const text = clipboard ? clipboard.getData('text/plain') : '';
                    if (text && editor) {
                        editor.focus();
                        if (document.queryCommandSupported('insertText')) {
                            document.execCommand('insertText', false, text);
                        } else {
                            const sel = window.getSelection();
                            if (sel && sel.rangeCount > 0) {
                                const range = sel.getRangeAt(0);
                                range.deleteContents();
                                range.insertNode(document.createTextNode(text));
                            }
                        }
                        editor.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            });
        }
    }

    if (tabStandardBtn && tab3dBtn && standardPane && atg3dPane) {
        tabStandardBtn.addEventListener('click', function(e) {
            e.preventDefault();
            tabStandardBtn.classList.add('active', 'fw-bold');
            tabStandardBtn.classList.remove('text-secondary');
            tab3dBtn.classList.remove('active', 'fw-bold');
            tab3dBtn.classList.add('text-secondary');
            standardPane.classList.remove('d-none');
            atg3dPane.classList.add('d-none');
        });

        tab3dBtn.addEventListener('click', function(e) {
            e.preventDefault();
            tab3dBtn.classList.add('active', 'fw-bold');
            tab3dBtn.classList.remove('text-secondary');
            tabStandardBtn.classList.remove('active', 'fw-bold');
            tabStandardBtn.classList.add('text-secondary');
            atg3dPane.classList.remove('d-none');
            standardPane.classList.add('d-none');
        });
    }

    // ── Me-stickers tab (RFC-0011) ──────────────────────────────────────
    const tabMeBtn = document.getElementById('emojiTabMeBtn');
    const mePane = document.getElementById('meStickerTabPane');
    const meList = document.getElementById('meStickerComposerList');
    const setTabState = (btn, on) => {
        if (!btn) return;
        btn.classList.toggle('active', on);
        btn.classList.toggle('fw-bold', on);
        btn.classList.toggle('text-secondary', !on);
    };
    [tabStandardBtn, tab3dBtn].forEach(b => b && b.addEventListener('click', () => {
        if (mePane) mePane.classList.add('d-none');
        setTabState(tabMeBtn, false);
    }));

    window.insertMeStickerIntoComposer = function(token, url, label) {
        if (!editor || !/^:me_[a-z]+@[A-Za-z0-9_-]{1,64}:$/.test(token)) return;
        editor.focus();
        const span = document.createElement('span');
        span.className = 'atg-me-sticker';
        span.contentEditable = 'false';
        span.dataset.stickerToken = token;
        const img = document.createElement('img');
        img.src = url;
        img.width = 56;
        img.height = 56;
        img.alt = label || 'My avatar';
        img.style.pointerEvents = 'none';
        span.appendChild(img);
        const spaceNode = document.createTextNode('\u00A0');
        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0 && editor.contains(sel.anchorNode)) {
            const range = sel.getRangeAt(0);
            range.deleteContents();
            range.insertNode(spaceNode);
            range.insertNode(span);
            range.setStartAfter(spaceNode);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
        } else {
            editor.appendChild(span);
            editor.appendChild(spaceNode);
        }
        window.syncEditorToTextarea();
    };

    const renderMeStickers = (data) => {
        if (!meList) return;
        meList.textContent = '';
        if (!data || !data.available) {
            const a = document.createElement('a');
            a.href = (data && data.studio_url) || '/member/emoji-studio';
            a.className = 'btn btn-sm btn-warning rounded-pill fw-semibold me-sticker-unlock';
            a.textContent = '🎨 Design your avatar in Emoji Studio to unlock Me-stickers';
            meList.appendChild(a);
            return;
        }
        data.stickers.forEach(st => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'me-sticker-btn';
            b.dataset.token = st.token;
            b.title = st.label;
            b.setAttribute('aria-label', 'Add my avatar: ' + st.label);
            const img = document.createElement('img');
            img.src = st.url;
            img.width = 64;
            img.height = 64;
            img.alt = '';
            img.loading = 'lazy';
            const cap = document.createElement('span');
            cap.textContent = st.label;
            b.append(img, cap);
            b.addEventListener('click', (e) => {
                e.preventDefault();
                window.insertMeStickerIntoComposer(st.token, st.url, st.label);
            });
            meList.appendChild(b);
        });
    };

    if (tabMeBtn && mePane) {
        tabMeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            setTabState(tabMeBtn, true);
            setTabState(tabStandardBtn, false);
            setTabState(tab3dBtn, false);
            if (standardPane) standardPane.classList.add('d-none');
            if (atg3dPane) atg3dPane.classList.add('d-none');
            mePane.classList.remove('d-none');
            if (meList && !meList.dataset.loaded) {
                meList.dataset.loaded = '1';
                meList.textContent = 'Loading your avatar…';
                const loader = window.AtgEmojiEngine && window.AtgEmojiEngine.getMeStickers
                    ? window.AtgEmojiEngine.getMeStickers()
                    : fetch('/api/emoji/me-stickers', { credentials: 'same-origin' }).then(r => r.json());
                loader.then(renderMeStickers).catch(() => { meList.dataset.loaded = ''; meList.textContent = 'Could not load your avatar. Try again.'; });
            }
        });
    }

    // Deep link from Emoji Studio: /member/profilePage?compose_sticker=<reaction>
    (function openComposerWithSticker() {
        let want = '';
        try { want = new URLSearchParams(window.location.search).get('compose_sticker') || ''; } catch (_) {}
        if (!/^(hello|laugh|wow|love|nod|dance|point|clap)$/.test(want)) return;
        try {
            const url = new URL(window.location.href);
            url.searchParams.delete('compose_sticker');
            window.history.replaceState(null, '', url.toString());
        } catch (_) {}
        let tries = 0;
        const go = () => {
            const modalEl = document.getElementById('postModal');
            if (!modalEl) return;
            if (!window.bootstrap || !window.bootstrap.Modal || !window.AtgEmojiEngine) {
                // Deferred scripts not ready yet: retry briefly instead of waiting for every feed image.
                if (++tries < 50) setTimeout(go, 100);
                return;
            }
            window.AtgEmojiEngine.getMeStickers().then(d => {
                window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                const insert = () => {
                    const st = d && d.available ? d.stickers.find(s => s.reaction === want) : null;
                    if (st) {
                        window.insertMeStickerIntoComposer(st.token, st.url, st.label);
                    } else {
                        // No saved avatar yet: open the Me tab so the unlock prompt shows.
                        const drawer = document.getElementById('emojiPickerDrawer');
                        const toggle = document.getElementById('emojiPost');
                        if (drawer && drawer.classList.contains('d-none') && toggle) toggle.click();
                        if (tabMeBtn) tabMeBtn.click();
                    }
                };
                if (modalEl.classList.contains('show')) insert();
                else modalEl.addEventListener('shown.bs.modal', insert, { once: true });
            }).catch(() => {});
        };
        // Open as soon as the DOM + deferred scripts are ready — NOT on window "load", which
        // waits for every image in the feed and made "Use in a post" feel broken.
        if (document.readyState !== 'loading') go(); else document.addEventListener('DOMContentLoaded', go, { once: true });
    })();

    if (modeAttachBtn && modeInsertBtn) {
        modeAttachBtn.addEventListener('click', function(e) {
            e.preventDefault();
            currentMode = 'attach';
            modeAttachBtn.classList.add('active', 'fw-bold');
            modeAttachBtn.classList.remove('text-secondary');
            modeInsertBtn.classList.remove('active', 'fw-bold');
            modeInsertBtn.classList.add('text-secondary');
            if (modeHelpText) modeHelpText.textContent = 'Click an emoji to attach it as this post\'s interactive AR badge.';
        });

        modeInsertBtn.addEventListener('click', function(e) {
            e.preventDefault();
            currentMode = 'insert';
            modeInsertBtn.classList.add('active', 'fw-bold');
            modeInsertBtn.classList.remove('text-secondary');
            modeAttachBtn.classList.remove('active', 'fw-bold');
            modeAttachBtn.classList.add('text-secondary');
            if (modeHelpText) modeHelpText.textContent = 'Click an emoji to insert it into your caption.';
        });
    }

    // Handle clicking on a 3D emoji in the integrated drawer
    document.querySelectorAll('.atg-post-emoji-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const emojiId = this.dataset.emojiId;
            const title = this.dataset.title;
            const desc = this.dataset.desc;

            // In ALL modes, insert the living 3D emoji visually into the text caption so users see how it looks!
            window.insertAtgEmojiIntoComposer(emojiId, title);

            if (currentMode === 'attach') {
                // Also attach as featured AR badge on post
                if (attachedInput) attachedInput.value = emojiId;
                if (previewTitle) previewTitle.textContent = `${title} — ${desc}`;
                if (previewIcon) {
                    previewIcon.innerHTML = `<img src="/img/emoji/${emojiId}.svg?v=20261103c" onerror="this.src='/img/emoji/${emojiId}.png?v=20261103c'" style="width:40px;height:40px;vertical-align:middle;">`;
                }
                if (previewStrip) previewStrip.classList.remove('d-none');

                const drawer = document.getElementById('emojiPickerDrawer');
                if (drawer) drawer.classList.add('d-none');

                if (window.Swal) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: `✨ Attached 3D AR Emoji: ${title}`
                    });
                }
            } else {
                if (window.Swal) {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1600,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: `Added ${title}`
                    });
                }
            }
        });
    });

    if (removeBtn) {
        removeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (attachedInput) attachedInput.value = '';
            if (previewStrip) previewStrip.classList.add('d-none');
        });
    }

    if (previewArBtn) {
        previewArBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const emojiId = attachedInput ? attachedInput.value : '';
            if (emojiId && window.AtgEmojiEngine) {
                window.AtgEmojiEngine.openArViewer(emojiId);
            }
        });
    }
})();

// Unified Media Link Drawer Toggle & Attach (Zero-Storage Architecture: Photos & Videos)
(function() {
    const btn = document.getElementById('addGooglePhotoBtn');
    const drawer = document.getElementById('googlePhotoDrawer');
    const closeBtn = document.getElementById('closeGooglePhotoDrawerBtn');
    const input = document.getElementById('googlePhotoLinkInput');
    const attachBtn = document.getElementById('btnAttachGooglePhoto');
    const textarea = document.getElementById('postMessage');

    function toggleDrawer() {
        if (!drawer) return;
        const isHidden = drawer.classList.contains('d-none');
        if (isHidden) {
            if (window.closeAllComposerDrawers) window.closeAllComposerDrawers('googlePhotoDrawer');
            drawer.classList.remove('d-none');
            try { drawer.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch(_) {}
            if (input) setTimeout(() => input.focus(), 80);
        } else {
            drawer.classList.add('d-none');
        }
    }

    if (btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleDrawer();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (drawer) drawer.classList.add('d-none');
        });
    }

    function attachLink() {
        if (!input || !textarea) return;
        const raw = input.value.trim();
        if (!raw) return;

        // Support pasting multiple links separated by spaces, commas, or newlines
        const links = raw.split(/[\s,]+/).map(s => s.trim()).filter(s => s.startsWith('http://') || s.startsWith('https://'));
        const toAppend = links.length > 0 ? links : [raw];

        let currentVal = textarea.value.trim();
        toAppend.forEach(link => {
            if (!currentVal.includes(link)) {
                currentVal = currentVal ? `${currentVal}\n${link}` : link;
            }

            // If a video link is entered, also render attached video embed preview if available
            if (typeof window.parseVideoUrl === 'function' && typeof window.renderAttachedVideoPreview === 'function') {
                const parsedVid = window.parseVideoUrl(link);
                if (parsedVid && (parsedVid.type === 'youtube' || parsedVid.type === 'vimeo' || parsedVid.type === 'cloudflare' || parsedVid.type === 'tiktok')) {
                    try {
                        window.renderAttachedVideoPreview(link, parsedVid.trimStart ?? null, parsedVid.trimEnd ?? null);
                    } catch (_) {}
                }
            }
        });
        textarea.value = currentVal;
        const editorEl = document.getElementById('postMessageEditor');
        if (editorEl) {
            editorEl.innerText = currentVal;
        }

        input.value = '';
        if (drawer) drawer.classList.add('d-none');
        (editorEl || textarea).focus();
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        if (typeof window.__unfurlComposerLinkPreview === 'function') {
            window.__unfurlComposerLinkPreview(true);
        }

        if (window.Swal) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2400,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: toAppend.length > 1 ? `${toAppend.length} Media links attached (Zero Storage)` : 'Media link attached (Zero Storage)'
            });
        }
    }

    if (attachBtn) {
        attachBtn.addEventListener('click', function(e) {
            e.preventDefault();
            attachLink();
        });
    }

    if (input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                attachLink();
            }
        });
    }
})();

window.showComposerAudienceStep = function() {
    const step1 = document.getElementById('composerStep1');
    const step2 = document.getElementById('composerStep2');
    const footer1 = document.getElementById('composerFooterStep1');
    const footer2 = document.getElementById('composerFooterStep2');
    const titleEl = document.getElementById('postModalLabel');

    if (step1) step1.classList.add('d-none');
    if (step2) step2.classList.remove('d-none');
    if (footer1) footer1.classList.add('d-none');
    if (footer2) {
        footer2.classList.remove('d-none');
        footer2.classList.add('d-flex');
    }
    if (titleEl) {
        titleEl.innerHTML = '<i class="bi bi-people-fill text-primary me-1.5"></i> Who should see this post?';
    }
    const body = document.querySelector('.composer-sheet__body');
    if (body) body.scrollTop = 0;
};

window.hideComposerAudienceStep = function() {
    const step1 = document.getElementById('composerStep1');
    const step2 = document.getElementById('composerStep2');
    const footer1 = document.getElementById('composerFooterStep1');
    const footer2 = document.getElementById('composerFooterStep2');
    const titleEl = document.getElementById('postModalLabel');

    if (step2) step2.classList.add('d-none');
    if (step1) step1.classList.remove('d-none');
    if (footer2) {
        footer2.classList.add('d-none');
        footer2.classList.remove('d-flex');
    }
    if (footer1) footer1.classList.remove('d-none');
    if (titleEl) {
        titleEl.textContent = 'Create Post';
    }
};

window.selectPostAudienceCard = function(labelEl, scope, label, iconClass, famCode) {
    if (!labelEl) return;
    const radio = labelEl.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;

    document.querySelectorAll('.composer-audience-card').forEach(card => {
        card.classList.remove('active');
    });
    labelEl.classList.add('active');

    const scopeInput = document.getElementById('composerTargetScope');
    if (scopeInput) scopeInput.value = scope;
    const codeInput = document.getElementById('composerTargetFamCode');
    if (codeInput) {
        if (famCode) {
            codeInput.value = famCode;
            codeInput.disabled = false;
        } else {
            codeInput.value = '';
            codeInput.disabled = true;
        }
    }

    const headerLabel = document.getElementById('composerHeaderAudienceLabel');
    if (headerLabel) headerLabel.textContent = label;
    const headerIcon = document.getElementById('composerHeaderAudienceIcon');
    if (headerIcon) headerIcon.className = 'bi ' + iconClass;
};

window.selectPostAudience = function(scope, label, iconClass, famCode) {
    const scopeInput = document.getElementById('composerTargetScope');
    if (scopeInput) scopeInput.value = scope;
    const codeInput = document.getElementById('composerTargetFamCode');
    if (codeInput) {
        if (famCode) {
            codeInput.value = famCode;
            codeInput.disabled = false;
        } else {
            codeInput.value = '';
            codeInput.disabled = true;
        }
    }
    const headerLabel = document.getElementById('composerHeaderAudienceLabel');
    if (headerLabel) headerLabel.textContent = label;
    const headerIcon = document.getElementById('composerHeaderAudienceIcon');
    if (headerIcon) headerIcon.className = 'bi ' + iconClass;
};

// ── 2-Step Post Flow Controller ──────────────────────────────────────────────
(function() {
    // Intercept click on #submitPost in capturing phase
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('#submitPost, .submitPost');
        if (!btn) return;

        const modal = document.getElementById('postModal');
        if (!modal) return;

        // Skip when editing an existing post
        const editPostNo = document.getElementById('editPostNo')?.value;
        if (editPostNo) return;

        // Ensure editor is synced before checking contents
        if (typeof window.syncEditorToTextarea === 'function') {
            window.syncEditorToTextarea();
        }

        const msg = (document.getElementById('postMessage')?.value || '').trim();
        const fileInput = document.getElementById('imageUpload');
        const hasSharedTemp = Boolean(document.querySelector('input[name="shared_temp_images[]"]'));
        const hasFiles = (fileInput && fileInput.files && fileInput.files.length > 0) || (typeof window.getSelectedPostFiles === 'function' && window.getSelectedPostFiles().length > 0) || hasSharedTemp;
        const hasGif = Boolean(document.getElementById('attachedGifUrlInput')?.value);
        const hasVideo = Boolean(document.getElementById('attachedVideoUrl')?.value);
        const hasPoll = document.getElementById('pollCreationContainer') && !document.getElementById('pollCreationContainer').classList.contains('d-none');
        const hasThenNow = document.getElementById('thenNowFlag')?.value === '1';

        if (!msg && !hasFiles && !hasGif && !hasVideo && !hasPoll && !hasThenNow) {
            e.preventDefault();
            e.stopImmediatePropagation();
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Empty Post',
                    text: 'Please add some text, an image, a video, or a poll before posting.',
                    confirmButtonColor: '#a31621'
                });
            }
            return;
        }

        const step2 = document.getElementById('composerStep2');
        if (!step2) return;

        const isConfirmed = step2.getAttribute('data-confirmed') === 'true';
        if (isConfirmed) return;

        const canChoose = modal.getAttribute('data-can-choose') === '1';
        if (!canChoose) return;

        // Intercept: transition to Step 2!
        e.preventDefault();
        e.stopImmediatePropagation();
        window.showComposerAudienceStep();
    }, true);

    document.addEventListener('click', function(e) {
        const backBtn = e.target.closest('#composerBackToStep1Btn');
        if (backBtn) {
            e.preventDefault();
            window.hideComposerAudienceStep();
            return;
        }

        const confirmBtn = e.target.closest('#composerConfirmShareBtn');
        if (confirmBtn) {
            e.preventDefault();
            const checkedRadio = document.querySelector('#composerStep2 input[name="audience_choice_radio"]:checked');
            if (checkedRadio) {
                const chosenScope = checkedRadio.getAttribute('data-scope') || 'all';
                const chosenCode = checkedRadio.getAttribute('data-code') || '';
                const scopeInput = document.getElementById('composerTargetScope');
                if (scopeInput) scopeInput.value = chosenScope;
                const codeInput = document.getElementById('composerTargetFamCode');
                if (codeInput) {
                    if (chosenCode) {
                        codeInput.value = chosenCode;
                        codeInput.disabled = false;
                    } else {
                        codeInput.value = '';
                        codeInput.disabled = true;
                    }
                }
            }
            const step2 = document.getElementById('composerStep2');
            if (step2) {
                step2.setAttribute('data-confirmed', 'true');
            }
            if (window.ScoreTenantSpinner) {
                window.ScoreTenantSpinner.start(confirmBtn);
            } else {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status" aria-hidden="true"></span> Sharing...';
            }
            const submitBtn = document.getElementById('submitPost');
            if (submitBtn) {
                submitBtn.click();
            }
        }
    });

    window.__flushPostComposer = function() {
        const editorEl = document.getElementById('postMessageEditor');
        if (editorEl) editorEl.innerHTML = '';
        const textareaEl = document.getElementById('postMessage');
        if (textareaEl) textareaEl.value = '';
        const gPhotoInput = document.getElementById('googlePhotoLinkInput');
        if (gPhotoInput) gPhotoInput.value = '';
        const gPhotoDrawer = document.getElementById('googlePhotoDrawer');
        if (gPhotoDrawer) gPhotoDrawer.classList.add('d-none');

        const vidUrlEl = document.getElementById('attachedVideoUrl');
        if (vidUrlEl) vidUrlEl.value = '';
        const vidUrlsEl = document.getElementById('attachedVideoUrls');
        if (vidUrlsEl) vidUrlsEl.value = '';
        const gifUrlEl = document.getElementById('attachedGifUrlInput');
        if (gifUrlEl) gifUrlEl.value = '';
        const legacyGifEl = document.getElementById('attachedGifUrl');
        if (legacyGifEl) legacyGifEl.value = '';

        const arPreview = document.getElementById('attachedArEmojiPreview');
        if (arPreview) arPreview.classList.add('d-none');
        const arInput = document.getElementById('attachedArEmojiInput');
        if (arInput) arInput.value = '';

        document.querySelectorAll('input[name="media_meta"], input[name="shared_temp_images[]"]').forEach(el => el.remove());

        if (typeof window.__resetLinkPreview === 'function') window.__resetLinkPreview();
        if (typeof window.__resetMediaPreviews === 'function') window.__resetMediaPreviews();
        if (typeof window.__resetVideoBuilder === 'function') window.__resetVideoBuilder();
        if (typeof window.__resetGifPicker === 'function') window.__resetGifPicker();
        if (typeof window.__resetPollBuilder === 'function') window.__resetPollBuilder();
        if (typeof window.__resetThenNow === 'function') window.__resetThenNow();
        if (typeof window.__resetTagBuilder === 'function') window.__resetTagBuilder();
        if (typeof window.__resetTextStageBuilder === 'function') window.__resetTextStageBuilder();
        if (typeof window.closeAllComposerDrawers === 'function') window.closeAllComposerDrawers();

        const step2 = document.getElementById('composerStep2');
        if (step2) step2.setAttribute('data-confirmed', 'false');
        if (typeof window.hideComposerAudienceStep === 'function') {
            window.hideComposerAudienceStep();
        }
        const firstCard = document.querySelector('#composerStep2 .composer-audience-card');
        if (firstCard && typeof window.selectPostAudienceCard === 'function') {
            window.selectPostAudienceCard(firstCard, 'all', 'All Family Circles & Connected Kin', 'bi-people-fill text-primary', '');
        }
        const confirmBtn = document.getElementById('composerConfirmShareBtn');
        if (confirmBtn) {
            if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(confirmBtn);
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="bi bi-send-fill me-1.5"></i> Confirm &amp; Share Post';
        }
    };

    const modalEl = document.getElementById('postModal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            window.__flushPostComposer();
        });
    }
})();

// ── Scenic Backdrops & 3D Milestone Stage Studio Controller ──────────────────
(function() {
    const STAGE_META = {
        starlight_bokeh: {
            badge: null,
            category: 'scenic',
            label: '✨ Starlight Bokeh',
        },
        golden_ember: {
            badge: null,
            category: 'scenic',
            label: '🔥 Golden Fireflies',
        },
        sunset_horizon: {
            badge: null,
            category: 'scenic',
            label: '🌅 Sunset Horizon',
        },
        emerald_canopy: {
            badge: null,
            category: 'scenic',
            label: '🌿 Enchanted Forest',
        },
        ocean_biolum: {
            badge: null,
            category: 'scenic',
            label: '🌊 Deep Ocean',
        },
        rose_stardust: {
            badge: null,
            category: 'scenic',
            label: '🌸 Rose Velvet',
        },
        northern_sky: {
            badge: null,
            category: 'scenic',
            label: '🌌 Aurora Sky',
        },
        royal_parchment: {
            badge: null,
            category: 'scenic',
            label: '👑 Royal Noir',
        },
        xmas_snowfall: {
            badge: null,
            category: 'scenic',
            label: '❄️ Festive Snowfall',
        },
        xmas_hearth: {
            badge: null,
            category: 'scenic',
            label: '🎄 Cosy Hearth',
        },
        xmas_lights: {
            badge: null,
            category: 'scenic',
            label: '✨ Fairy Lights',
        },
        xmas_winter_gold: {
            badge: null,
            category: 'scenic',
            label: '🌟 Winter Gold',
        },
        solar_galaxy: {
            badge: null,
            category: 'scenic',
            label: '🪐 Solar Galaxy',
        },
        dynasty_holo: {
            badge: '🌳 DYNASTY LINEAGE CALL',
            category: 'stage',
            label: '🌳 Dynasty Tree',
        },
        cyber_glass: {
            badge: '🔮 3D HOLOGRAM STAGE',
            category: 'stage',
            label: '🔮 3D Holo Glass',
        },
        alert_pulse: {
            badge: '🚨 FAMILY ALERT TRIGGER',
            category: 'stage',
            label: '🚨 Alert Trigger',
        },
        grand_announcement: {
            badge: '📣 OFFICIAL BROADCAST',
            category: 'stage',
            label: '📣 Announcement',
        },
        aurora_prism: {
            badge: '🌌 FUTURISTIC PRISM',
            category: 'stage',
            label: '💎 Aurora Prism',
        },
        heirloom_velvet: {
            badge: '🏛️ HEIRLOOM DECREE',
            category: 'stage',
            label: '🏛️ Royal Velvet',
        },
    };

    const ALL_CLASSES = Object.keys(STAGE_META).map(s => 'fp-text-stage--' + s);

    window.__setComposerTextStage = function(slug) {
        const cleanSlug = (slug && STAGE_META[slug]) ? slug : '';
        const input = document.getElementById('composerPostStyleInput');
        const wrapper = document.getElementById('composerEditorStageWrapper');
        const editor = document.getElementById('postMessageEditor');
        const badgeWrap = document.getElementById('composerStageHeaderBadge');
        const badgeText = document.getElementById('composerStageHeaderText');
        const bokehLayer = document.getElementById('composerStageBokehLayer');
        const cosmosLayer = document.getElementById('composerStageCosmosLayer');

        if (input) input.value = cleanSlug;

        document.querySelectorAll('#composerTextStageBar .composer-stage-pill').forEach(btn => {
            const btnSlug = btn.getAttribute('data-stage-slug') || '';
            const active = btnSlug === cleanSlug;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (!wrapper || !editor) return;

        const previewContainer = document.getElementById('composerLinkPreviewContainer');
        wrapper.classList.remove('fp-text-stage', 'fp-text-stage--composer', 'fp-text-stage--scenic', ...ALL_CLASSES);
        if (cleanSlug) {
            const meta = STAGE_META[cleanSlug];
            const isScenic = meta.category === 'scenic';
            wrapper.classList.add('fp-text-stage', 'fp-text-stage--composer', 'fp-text-stage--' + cleanSlug);
            if (isScenic) {
                wrapper.classList.add('fp-text-stage--scenic');
            }
            if (bokehLayer) {
                bokehLayer.classList.toggle('d-none', !isScenic);
            }
            if (cosmosLayer) {
                cosmosLayer.classList.toggle('d-none', cleanSlug !== 'solar_galaxy');
            }
            editor.style.color = '#ffffff';
            editor.style.fontWeight = isScenic ? '800' : '700';
            editor.style.fontSize = isScenic ? '1.35rem' : '1.22rem';
            editor.style.textAlign = 'center';
            if (badgeWrap && badgeText) {
                if (meta.badge) {
                    badgeText.textContent = meta.badge;
                    badgeWrap.classList.remove('d-none');
                } else {
                    badgeWrap.classList.add('d-none');
                }
            }
            if (previewContainer) {
                previewContainer.classList.add('fp-text-stage__composer-preview');
            }
        } else {
            if (bokehLayer) bokehLayer.classList.add('d-none');
            if (cosmosLayer) cosmosLayer.classList.add('d-none');
            editor.style.color = 'var(--text-main, #0f172a)';
            editor.style.fontWeight = '';
            editor.style.fontSize = '1.05rem';
            editor.style.textAlign = '';
            if (badgeWrap) badgeWrap.classList.add('d-none');
            if (previewContainer) {
                previewContainer.classList.remove('fp-text-stage__composer-preview');
            }
        }
    };

    window.__resetTextStageBuilder = function() {
        window.__setComposerTextStage('');
        const suggestBtn = document.getElementById('composerSmartStageSuggestBtn');
        if (suggestBtn) suggestBtn.classList.add('d-none');
        const previewContainer = document.getElementById('composerLinkPreviewContainer');
        if (previewContainer) {
            previewContainer.classList.remove('fp-text-stage__composer-preview');
        }
    };

    const textarea = document.getElementById('postMessage');
    const suggestBtn = document.getElementById('composerSmartStageSuggestBtn');
    let suggestedSlug = '';

    if (textarea && suggestBtn) {
        textarea.addEventListener('input', function() {
            const val = (textarea.value || '').toLowerCase();
            const currentStyle = document.getElementById('composerPostStyleInput')?.value || '';
            if (currentStyle || val.length < 5) {
                suggestBtn.classList.add('d-none');
                return;
            }
            if (val.includes('solar') || val.includes('galaxy') || val.includes('planet') || val.includes('cosmos') || val.includes('universe') || val.includes('space')) {
                suggestedSlug = 'solar_galaxy';
                suggestBtn.textContent = '🪐 Try Solar Galaxy Backdrop';
                suggestBtn.classList.remove('d-none');
            } else if (val.includes('xmas') || val.includes('christmas') || val.includes('fairy light') || val.includes('santa') || val.includes('festive') || val.includes('snow')) {
                suggestedSlug = val.includes('snow') ? 'xmas_snowfall' : (val.includes('light') ? 'xmas_lights' : 'xmas_hearth');
                suggestBtn.textContent = val.includes('snow') ? '❄️ Try Festive Snowfall Backdrop' : (val.includes('light') ? '✨ Try Fairy Lights Backdrop' : '🎄 Try Cosy Hearth Backdrop');
                suggestBtn.classList.remove('d-none');
            } else if (val.includes('family tree') || val.includes('branch') || val.includes('lineage')) {
                suggestedSlug = 'dynasty_holo';
                suggestBtn.textContent = '✨ Try 3D Dynasty Tree Stage';
                suggestBtn.classList.remove('d-none');
            } else if (val.includes('urgent') || val.includes('alert') || val.includes('emergency') || val.includes('important')) {
                suggestedSlug = 'alert_pulse';
                suggestBtn.textContent = '🚨 Try 3D Alert Trigger';
                suggestBtn.classList.remove('d-none');
            } else if (val.includes('announce') || val.includes('welcome') || val.includes('congrat') || val.includes('reunion')) {
                suggestedSlug = 'grand_announcement';
                suggestBtn.textContent = '📣 Try 3D Announcement Stage';
                suggestBtn.classList.remove('d-none');
            } else if (val.length >= 12 && val.length <= 180) {
                suggestedSlug = 'starlight_bokeh';
                suggestBtn.textContent = '✨ Try Starlight Bokeh Backdrop';
                suggestBtn.classList.remove('d-none');
            } else {
                suggestBtn.classList.add('d-none');
            }
        });

        suggestBtn.addEventListener('click', function() {
            if (suggestedSlug) {
                window.__setComposerTextStage(suggestedSlug);
                suggestBtn.classList.add('d-none');
            }
        });
    }
})();
</script>

<style>
.composer-stage-studio__row {
    display: flex;
    align-items: center;
    gap: var(--space-2, 8px);
    overflow-x: auto;
    padding: var(--space-1, 4px) 2px 6px;
    scrollbar-width: none;
}
.composer-stage-studio__row::-webkit-scrollbar {
    display: none;
}
.composer-stage-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: var(--tap-min, 44px);
    min-width: var(--tap-min, 44px);
    padding: 6px 12px;
    border-radius: 999px;
    border: 1.5px solid rgba(148, 163, 184, 0.32);
    background: var(--card-bg, #ffffff);
    color: var(--text-main, #0f172a);
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    cursor: pointer;
    transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
}
.composer-stage-pill:hover {
    transform: translateY(-1px);
    border-color: #6366f1;
}
.composer-stage-pill:active {
    transform: scale(0.98);
}
.composer-stage-pill.is-active {
    background: #0f172a;
    color: #ffffff;
    border-color: #38bdf8;
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.22);
}
.composer-stage-pill__dot {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    font-size: var(--text-xs, 0.75rem);
    flex-shrink: 0;
}
.composer-stage-pill__dot--plain {
    background: #e2e8f0;
    color: #0f172a;
    font-weight: 800;
}
.composer-stage-pill__dot--starlight {
    background: radial-gradient(circle at 30% 30%, #38bdf8 0%, #0369a1 45%, #020817 100%);
}
.composer-stage-pill__dot--fireflies {
    background: radial-gradient(circle at 30% 30%, #fbbf24 0%, #b45309 48%, #140802 100%);
}
.composer-stage-pill__dot--sunset {
    background: linear-gradient(135deg, #312e81 0%, #be185d 52%, #c2410c 100%);
}
.composer-stage-pill__dot--forest {
    background: radial-gradient(circle at 32% 28%, #34d399 0%, #047857 48%, #021810 100%);
}
.composer-stage-pill__dot--ocean {
    background: radial-gradient(circle at 32% 30%, #22d3ee 0%, #0e7490 48%, #020c1b 100%);
}
.composer-stage-pill__dot--rose {
    background: radial-gradient(circle at 30% 28%, #f472b6 0%, #9d174d 48%, #230514 100%);
}
.composer-stage-pill__dot--northern {
    background: linear-gradient(135deg, #020617 0%, #059669 50%, #6366f1 100%);
}
.composer-stage-pill__dot--royal {
    background: linear-gradient(135deg, #09090b 0%, #292524 60%, #d97706 100%);
}
.composer-stage-pill__dot--xmas-snow {
    background: radial-gradient(circle at 30% 28%, #e0f2fe 0%, #0284c7 45%, #06283d 100%);
}
.composer-stage-pill__dot--xmas-hearth {
    background: radial-gradient(circle at 30% 30%, #fbbf24 0%, #b91c1c 48%, #3b0712 100%);
}
.composer-stage-pill__dot--xmas-lights {
    background: linear-gradient(135deg, #064e3b 0%, #b91c1c 50%, #f59e0b 100%);
}
.composer-stage-pill__dot--xmas-gold {
    background: radial-gradient(circle at 30% 28%, #fef08a 0%, #ca8a04 45%, #052e16 100%);
}
.composer-stage-pill__dot--solar-galaxy {
    background: radial-gradient(circle at 35% 35%, #fde047 0%, #ea580c 26%, #4338ca 62%, #02030d 100%);
}
.composer-stage-pill__dot--dynasty {
    background: linear-gradient(135deg, #065f46, #10b981);
}
.composer-stage-pill__dot--cyber {
    background: linear-gradient(135deg, #0ea5e9, #6366f1);
}
.composer-stage-pill__dot--alert {
    background: linear-gradient(135deg, #991b1b, #e11d48);
}
.composer-stage-pill__dot--announce {
    background: linear-gradient(135deg, #b45309, #f59e0b);
}
.composer-stage-pill__dot--prism {
    background: linear-gradient(135deg, #7c3aed, #ec4899);
}
.composer-stage-pill__dot--velvet {
    background: linear-gradient(135deg, #4c0519, #9f1239);
}
.fp-text-stage--composer {
    padding: var(--space-4, 18px) !important;
    border-radius: var(--radius-lg, 20px) !important;
    margin-bottom: var(--space-2, 8px);
}
.fp-text-stage--composer #postMessageEditor:empty::before {
    color: rgba(255, 255, 255, 0.75) !important;
}

/* Glassmorphic 3D Composer Link Preview Elevation */
.fp-text-stage__composer-preview {
    background: rgba(15, 23, 42, 0.9) !important;
    border: 1.5px solid rgba(255, 255, 255, 0.22) !important;
    backdrop-filter: blur(16px) !important;
    box-shadow: 0 12px 30px -6px rgba(0, 0, 0, 0.45) !important;
}
.fp-text-stage__composer-preview #composerLinkPreviewTitle {
    color: #ffffff !important;
}
.fp-text-stage__composer-preview #composerLinkPreviewDesc {
    color: #cbd5e1 !important;
}
.fp-text-stage__composer-preview #composerLinkPreviewSiteName {
    color: #94a3b8 !important;
}
.fp-text-stage__composer-preview #composerLinkPreviewUrl {
    color: #94a3b8 !important;
}

/* Audience Selector Dropdown: Inviolable Red Surface Rule Compliance */
#composerAudienceDropdown .dropdown-menu {
    border: 1px solid var(--border-color, #e2e8f0) !important;
    padding: 6px !important;
    border-radius: 14px !important;
}
#composerAudienceDropdown .dropdown-item {
    border-radius: 10px !important;
    padding: 8px 12px !important;
    transition: background-color 0.15s ease, color 0.15s ease;
}
#composerAudienceDropdown .dropdown-item.active,
#composerAudienceDropdown .dropdown-item.active:hover,
#composerAudienceDropdown .dropdown-item.active:focus {
    background-color: var(--brand-primary, #a31621) !important;
    border-color: var(--brand-primary, #a31621) !important;
    color: #ffffff !important;
}
#composerAudienceDropdown .dropdown-item.active *,
#composerAudienceDropdown .dropdown-item.active .fw-semibold,
#composerAudienceDropdown .dropdown-item.active div {
    color: #ffffff !important;
}
#composerAudienceDropdown .dropdown-item.active small,
#composerAudienceDropdown .dropdown-item.active .text-muted {
    color: #fff7ed !important; /* Crisp warm champagne with 8.6:1 WCAG AAA contrast, zero grey on red */
    opacity: 0.95 !important;
}
#composerAudienceDropdown .dropdown-item.active i,
#composerAudienceDropdown .dropdown-item.active .bi {
    color: #ffd700 !important; /* Luminous gold on red */
}
.composer-audience-card {
    border-color: rgba(148, 163, 184, 0.35) !important;
    background: rgba(248, 250, 252, 0.85);
    transition: transform 0.16s ease, border-color 0.16s ease, background-color 0.16s ease, box-shadow 0.16s ease;
    user-select: none;
    -webkit-user-select: none;
    touch-action: manipulation;
}
[data-theme="dark"] .composer-audience-card,
.dark-mode .composer-audience-card {
    border-color: rgba(148, 163, 184, 0.25) !important;
    background: rgba(30, 41, 59, 0.72);
}
.composer-audience-card:hover {
    transform: translateY(-1.5px);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}
.composer-audience-card:active {
    transform: scale(0.985);
}
.composer-audience-card.active {
    border-color: var(--brand-primary, #a31621) !important;
    background: rgba(163, 22, 33, 0.04) !important;
    box-shadow: 0 0 0 1px var(--brand-primary, #a31621), 0 4px 14px rgba(163, 22, 33, 0.12) !important;
}
[data-theme="dark"] .composer-audience-card.active,
.dark-mode .composer-audience-card.active {
    border-color: var(--brand-primary, #fb7185) !important;
    background: rgba(163, 22, 33, 0.14) !important;
    box-shadow: 0 0 0 1px var(--brand-primary, #fb7185), 0 4px 14px rgba(225, 29, 72, 0.2) !important;
}

/* ── Concept A: Integrated Heirloom Frame Studio (Then & Now) ──────────────── */
.then-now-studio {
    padding: var(--space-3, 12px) !important;
    border-radius: var(--radius-lg, 16px) !important;
}
.then-now-studio__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: var(--space-2, 8px);
    margin-bottom: var(--space-3, 12px) !important;
}
.then-now-studio__delta-pill {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1, 4px);
    padding: var(--space-1, 4px) var(--space-2, 10px);
    border-radius: var(--radius-full, 999px);
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    background: rgba(245, 158, 11, 0.16);
    color: #b45309;
    border: 1px solid rgba(245, 158, 11, 0.38);
    white-space: nowrap;
}
[data-theme="dark"] .then-now-studio__delta-pill,
.dark-mode .then-now-studio__delta-pill {
    background: rgba(245, 158, 11, 0.2);
    color: #fcd34d;
    border-color: rgba(251, 191, 36, 0.35);
}
.then-now-studio__mode-group {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1, 4px);
    padding: var(--space-1, 4px);
    border-radius: var(--radius-full, 999px);
    background: var(--surface-bg, rgba(15, 23, 42, 0.06));
    border: 1px solid var(--border-color, rgba(148, 163, 184, 0.28));
}
.then-now-studio__mode-btn {
    min-height: var(--tap-min, 44px);
    min-width: var(--tap-min, 44px);
    padding: var(--space-1, 4px) var(--space-3, 12px);
    border-radius: var(--radius-full, 999px);
    border: none;
    background: transparent;
    color: var(--text-muted-aa, #475569);
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    white-space: nowrap;
    flex-shrink: 0;
    cursor: pointer;
    transition: background-color 0.16s ease, color 0.16s ease, transform 0.12s ease;
}
.then-now-studio__mode-btn:active {
    transform: scale(0.98);
}
.then-now-studio__mode-btn.active {
    background: var(--card-bg, #0f172a);
    color: var(--text-main, #ffffff);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.18);
}
:root:not([data-theme="dark"]) .then-now-studio__mode-btn.active {
    background: #0f172a;
    color: #ffffff;
}
.then-now-slots {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-3, 12px);
    position: relative;
}
.then-now-slots__bridge {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    z-index: 6;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #0f172a;
    color: #fcd34d;
    border: 2px solid rgba(251, 191, 36, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--text-xs, 0.75rem);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.35);
    pointer-events: none;
}
.then-now-card {
    display: flex;
    flex-direction: column;
    border-radius: var(--radius-md, 14px);
    border: 1px solid var(--border-color, rgba(148, 163, 184, 0.28));
    background: var(--surface-bg, rgba(15, 23, 42, 0.03));
    overflow: hidden;
    transition: border-color 0.16s ease, box-shadow 0.16s ease;
}
.then-now-card:focus-within {
    border-color: #38bdf8;
    box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
}
.then-now-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-2, 8px);
    padding: var(--space-1, 4px) var(--space-2, 10px);
    background: rgba(15, 23, 42, 0.06);
    border-bottom: 1px solid var(--border-color, rgba(148, 163, 184, 0.22));
    min-height: var(--tap-min, 44px);
}
[data-theme="dark"] .then-now-card__header,
.dark-mode .then-now-card__header {
    background: rgba(2, 6, 23, 0.55);
}
.then-now-card__tag {
    display: inline-flex;
    align-items: center;
    padding: 2px var(--space-2, 8px);
    border-radius: var(--radius-sm, 6px);
    font-size: var(--text-xs, 0.75rem);
    font-weight: 800;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    white-space: nowrap;
    flex-shrink: 0;
}
.then-now-card__tag--then {
    background: rgba(245, 158, 11, 0.18);
    color: #d97706;
}
[data-theme="dark"] .then-now-card__tag--then,
.dark-mode .then-now-card__tag--then {
    color: #fbbf24;
}
.then-now-card__tag--now {
    background: rgba(16, 185, 129, 0.18);
    color: #059669;
}
[data-theme="dark"] .then-now-card__tag--now,
.dark-mode .then-now-card__tag--now {
    color: #34d399;
}
.then-now-card__year-input {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    text-align: right;
    font-size: var(--text-xs, 0.75rem) !important;
    font-weight: 700 !important;
    color: var(--text-main, #0f172a) !important;
    padding: var(--space-1, 4px) var(--space-1, 4px) !important;
    margin: 0 !important;
    min-height: 38px;
    width: 100%;
    min-width: 0;
}
.then-now-card__year-input:focus {
    outline: none !important;
}
.then-now-card .then-now-slot__pick {
    position: relative;
    aspect-ratio: 4 / 3 !important;
    min-height: 128px;
    max-height: 165px;
    margin-bottom: 0 !important;
    border: none !important;
    border-radius: 0 !important;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-2, 10px);
}
.then-now-slot__empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: var(--space-1, 6px);
    text-align: center;
}
.then-now-slot__icon-ring {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(148, 163, 184, 0.14);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.then-now-slot__icon-ring i {
    font-size: var(--text-base, 1rem) !important;
}
.then-now-slot__prompt {
    font-size: var(--text-xs, 0.75rem);
    line-height: 1.3;
}
.then-now-slot__upload-pill {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1, 4px);
    padding: var(--space-1, 4px) var(--space-2, 10px);
    border-radius: var(--radius-full, 999px);
    background: rgba(148, 163, 184, 0.16);
    border: 1px solid rgba(148, 163, 184, 0.28);
    font-size: var(--text-xs, 0.75rem);
    font-weight: 600;
    white-space: nowrap;
}
.then-now-slot__replace-pill {
    position: absolute;
    bottom: var(--space-2, 8px);
    right: var(--space-2, 8px);
    z-index: 4;
    padding: var(--space-1, 4px) var(--space-2, 10px);
    border-radius: var(--radius-full, 999px);
    background: rgba(15, 23, 42, 0.82);
    color: #ffffff;
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    backdrop-filter: blur(6px);
    white-space: nowrap;
}
.then-now-card__footer {
    padding: var(--space-1, 6px);
    border-top: 1px solid var(--border-color, rgba(148, 163, 184, 0.22));
    background: rgba(15, 23, 42, 0.03);
}
.then-now-card__action-btn {
    width: 100%;
    min-height: var(--tap-min, 44px);
    padding: var(--space-1, 6px) var(--space-2, 10px);
    border-radius: var(--radius-md, 10px);
    border: 1px solid var(--border-color, rgba(148, 163, 184, 0.3));
    background: var(--card-bg, rgba(15, 23, 42, 0.4));
    color: var(--text-main, #0f172a);
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-1, 6px);
    white-space: nowrap;
    flex-shrink: 0;
    cursor: pointer;
    transition: transform 0.14s ease, background-color 0.14s ease, border-color 0.14s ease;
}
.then-now-card__action-btn:active {
    transform: scale(0.98);
}
.then-now-card__action-btn--archive i {
    color: #e11d48;
}
.then-now-card__action-btn--camera i {
    color: #10b981;
}
.then-now-story-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: var(--space-2, 8px);
    padding: var(--space-2, 8px) var(--space-3, 10px);
    border-radius: var(--radius-md, 12px);
    background: rgba(163, 22, 33, 0.06);
    border: 1px solid rgba(163, 22, 33, 0.22);
}
.then-now-story-bar__voice-btn {
    min-height: var(--tap-min, 44px);
    min-width: var(--tap-min, 44px);
    padding: var(--space-1, 6px) var(--space-3, 14px);
    border-radius: var(--radius-full, 999px);
    background-color: #a31621;
    color: #ffffff !important;
    border: none;
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: var(--space-1, 6px);
    white-space: nowrap;
    flex-shrink: 0;
    cursor: pointer;
    transition: transform 0.14s ease, filter 0.14s ease;
}
.then-now-story-bar__voice-btn:hover {
    filter: brightness(1.06);
}
.then-now-story-bar__voice-btn:active {
    transform: scale(0.98);
}
.then-now-story-bar__hint {
    font-size: var(--text-xs, 0.75rem);
    color: var(--text-muted-aa, #64748b);
}
.then-now-story-bar__mystery-btn {
    min-height: var(--tap-min, 44px);
    min-width: var(--tap-min, 44px);
    padding: var(--space-1, 6px) var(--space-3, 12px);
    border-radius: var(--radius-full, 999px);
    border: 1px solid var(--border-color, rgba(148, 163, 184, 0.35));
    background: var(--card-bg, #ffffff);
    color: var(--text-main, #0f172a);
    font-size: var(--text-xs, 0.75rem);
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: var(--space-1, 6px);
    white-space: nowrap;
    flex-shrink: 0;
    cursor: pointer;
    transition: transform 0.14s ease, background-color 0.14s ease, color 0.14s ease;
}
.then-now-story-bar__mystery-btn.btn-dark,
.then-now-story-bar__mystery-btn[aria-pressed="true"] {
    background: #0f172a !important;
    color: #ffffff !important;
    border-color: #f59e0b !important;
}
@media (max-width: 480px) {
    .then-now-slots {
        gap: var(--space-2, 8px);
    }
    .then-now-card__action-btn {
        padding: var(--space-1, 4px) var(--space-2, 8px);
    }
}
</style>