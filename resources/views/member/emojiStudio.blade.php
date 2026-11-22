@extends('layouts.profileBase')

@section('title', 'LIVING AVATAR & EMOJI STUDIO')

@push('styles')
<link rel="stylesheet" href="/public/css/atg-emoji.css?v={{ time() }}">
@endpush

@section('content')
<div class="atg-studio-wrapper">
    @include('csrf')
    @include('component.submitSpinner')

    <!-- Header & Hero -->
    <div class="atg-studio-header">
        <span class="atg-studio-badge">ATG Sovereign R&amp;D Engine</span>
        <h1 class="atg-studio-title mt-2">
            <span>✨ Living Avatar &amp; Emoji Studio</span>
        </h1>
        <p class="atg-studio-subtitle">
            Build an avatar that truly looks like you — every complexion, hair texture, headwear and feature — then watch it follow your gaze, react to your taps and talk when you talk.
        </p>
    </div>

    <!-- Main Studio Grid -->
    <div class="atg-studio-grid">

        <!-- Left Column: Live Motion Stage -->
        <div class="atg-preview-stage">
            <div class="atg-stage-sticky">
                @if (!empty($hasProfilePhoto))
                    <div class="atg-look-switch" role="radiogroup" aria-label="Build my avatar from">
                        <button type="button" class="atg-look-btn" role="radio" data-look="photo" aria-checked="true">📸 My photo</button>
                        <button type="button" class="atg-look-btn" role="radio" data-look="avatar" aria-checked="false">🎨 Cartoon</button>
                    </div>
                @endif
                <div class="atg-canvas-viewport" id="atgAvatarViewport"
                     data-traits='@json($initialTraits)'
                     tabindex="0" role="button"
                     aria-label="Your live avatar. Tap to make it react; drag to rotate."
                     title="Tap to react · drag to rotate">
                    {!! $initialSvg !!}
                    @if (!empty($hasProfilePhoto))
                        {{-- Your real portrait acting out the chosen reaction (same art as the Me-sticker). --}}
                        <img id="atgPhotoStickerStage" class="atg-photo-sticker-stage" alt="Your photo avatar" hidden
                             data-base="/api/emoji/avatar-sticker/{{ rawurlencode((string)$stickerUserId) }}/"
                             data-photo-url="{{ $stickerPhotoUrl ?? '' }}"
                             data-face-known="{{ !empty($stickerFaceKnown) ? '1' : '0' }}"
                             src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==">
                    @endif
                    <div class="atg-react-layer" aria-hidden="true"></div>
                </div>

                <p class="atg-stage-hint" id="atgStageHint">
                    👀 It follows your cursor · tap it to react
                </p>

                <div class="atg-reaction-bar" role="group" aria-label="Avatar reactions">
                    <button type="button" class="atg-react-btn" data-reaction="hello" aria-label="Wave hello">👋</button>
                    <button type="button" class="atg-react-btn" data-reaction="laugh" aria-label="Laugh">😂</button>
                    <button type="button" class="atg-react-btn" data-reaction="wow" aria-label="Wow">😮</button>
                    <button type="button" class="atg-react-btn" data-reaction="love" aria-label="Love">❤️</button>
                    <button type="button" class="atg-react-btn" data-reaction="nod" aria-label="Nod yes">🙆</button>
                    <button type="button" class="atg-react-btn" data-reaction="dance" aria-label="Dance">💃</button>
                    <button type="button" class="atg-react-btn" data-reaction="point" aria-label="Point at you">🫵</button>
                    <button type="button" class="atg-react-btn" data-reaction="clap" aria-label="Clap">👏</button>
                </div>
            </div>

            <div class="atg-motion-controls">
                <button type="button" id="atgTalkBtn" class="atg-motion-toggle atg-cartoon-only" aria-pressed="false">
                    <span class="atg-motion-icon">🎙️</span>
                    <span><strong>Talk mode</strong><small>Lip-syncs to your voice · audio never leaves your device</small></span>
                </button>
                <button type="button" id="atgSensorBtn" class="atg-motion-toggle atg-cartoon-only" aria-pressed="false" hidden>
                    <span class="atg-motion-icon">📱</span>
                    <span><strong>Tilt &amp; shake</strong><small>Tilt to steer its gaze · shake for a laugh</small></span>
                </button>
                <label class="atg-living-toggle atg-cartoon-only">
                    <input type="checkbox" id="atgAnimatedSave" checked>
                    <span><strong>Living profile photo</strong><small>Blinks &amp; breathes wherever your photo shows</small></span>
                </label>
            </div>

            <div class="atg-preview-actions">
                <button type="button" id="atgSaveAvatarBtn" class="atg-btn-primary btn-spin-on-click atg-cartoon-only" data-spin="true">
                    <span>✨ Set as Profile Picture</span>
                </button>
                <button type="button" id="atgRandomAvatarBtn" class="atg-btn-secondary atg-cartoon-only">
                    <span>🎲 Surprise Me</span>
                </button>
                <a id="atgUseInPostBtn" class="atg-btn-secondary text-decoration-none" href="/member/profilePage?compose_sticker=hello"
                   title="Post your saved avatar doing the last reaction you tried">
                    <span>📣 Use in a post</span>
                </a>
            </div>
            <p class="atg-stage-note atg-cartoon-only">Tap a reaction above, then <em>Use in a post</em> — your avatar joins the post as a Me-sticker. Your profile photo stays as it is.</p>
            <p class="atg-stage-note atg-photo-only">This is your real profile photo. Tap a reaction above, then <em>Use in a post</em>: exactly this goes into your post.</p>
            @if (!empty($hasSavedLook))
                <p class="atg-stage-note">Loaded your current studio look.</p>
            @endif
        </div>

        <!-- Right Column: Trait Customisation & Kinship Catalog -->
        <div class="atg-controls-card">

            <!-- Navigation Tabs -->
            <div class="atg-tabs" role="tablist">
                <button type="button" class="atg-tab-btn active" role="tab" data-atg-tab="avatar-tab" onclick="switchAtgTab('avatar-tab', this)">
                    👤 Avatar
                </button>
                <button type="button" class="atg-tab-btn" role="tab" data-atg-tab="photo-avatar-tab" onclick="switchAtgTab('photo-avatar-tab', this)">
                    📸 Living Photo
                </button>
                <button type="button" class="atg-tab-btn" role="tab" data-atg-tab="catalog-tab" onclick="switchAtgTab('catalog-tab', this)">
                    🛡️ Living Emojis
                </button>
            </div>

            <!-- TAB 1: Avatar Customiser -->
            <div id="avatar-tab" class="atg-tab-content">
                @php $avatarOptions = \App\services\AtgAvatarRenderer::options(); @endphp

                <div class="atg-section-chips" role="tablist" aria-label="Customiser sections">
                    @foreach (\App\services\AtgAvatarRenderer::SECTIONS as $sectionKey => $section)
                        <button type="button" class="atg-section-chip {{ $loop->first ? 'active' : '' }}"
                                data-atg-section="{{ $sectionKey }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                            {{ $section['label'] }}
                        </button>
                    @endforeach
                </div>

                @foreach (\App\services\AtgAvatarRenderer::SECTIONS as $sectionKey => $section)
                    <div class="atg-section-panel" data-atg-panel="{{ $sectionKey }}" @if (!$loop->first) hidden @endif>
                        @foreach ($section['traits'] as $traitKey => $traitLabel)
                            <div class="atg-trait-section">
                                <div class="atg-trait-label">
                                    <span>{{ $traitLabel }}</span>
                                </div>
                                <div class="atg-swatches-grid">
                                    @foreach ($avatarOptions[$traitKey] as $optKey => $opt)
                                        @php $sw = \App\services\AtgAvatarRenderer::swatch($traitKey, $optKey); @endphp
                                        <button type="button"
                                                class="atg-trait-pill {{ ($initialTraits[$traitKey] ?? '') === $optKey ? 'active' : '' }}"
                                                data-trait-group="{{ $traitKey }}"
                                                data-trait-value="{{ $optKey }}"
                                                aria-pressed="{{ ($initialTraits[$traitKey] ?? '') === $optKey ? 'true' : 'false' }}">
                                            @if ($sw)
                                                <span class="atg-color-dot" style="background: {{ $sw }};"></span>
                                            @endif
                                            <span>{{ $opt['label'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <!-- TAB 2: Photo-to-Living Avatar (OpenArt-Grade Hybrid Transformation Studio) -->
            <div id="photo-avatar-tab" class="atg-tab-content" style="display: none;">
                @php
                    $studioAvatarStyles = $avatarStyles ?? \App\services\AtgPhotoAvatarEngine::getStylesForUi();
                @endphp
                <div class="atg-photo-studio-card">
                    <div class="atg-p2a-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span class="atg-p2a-eyebrow">ATG Photo-to-Avatar Engine</span>
                            <span class="atg-p2a-engine-pill" id="atgAvatarEngineBadge">⚡ Instant GPU Styliser Ready</span>
                        </div>
                        <h3 class="atg-p2a-title">Transform Your Photo into a Stylised Living Avatar</h3>
                        <p class="atg-p2a-subtitle">
                            Upload a portrait, snap a webcam photo, or use your profile picture to redraw it into an identity-preserving illustrated, anime, 3D or oil-painted avatar — drag the Before / After slider to compare, then bring it to life.
                        </p>
                    </div>

                    <div class="atg-p2a-grid">
                        <!-- Left Stage Column: Before/After Split Comparison + 2.5D Living Motion Canvas -->
                        <div class="atg-p2a-stage-col">
                            <div class="atg-p2a-stage-card" id="atgPhotoStageCard">
                                <div class="atg-p2a-view-switch" role="group" aria-label="Portrait preview mode">
                                    <button type="button" id="atgViewModeCompareBtn" class="atg-p2a-view-btn active" aria-pressed="true">
                                        <span>🔀 Before / After</span>
                                    </button>
                                    <button type="button" id="atgViewModeLivingBtn" class="atg-p2a-view-btn" aria-pressed="false">
                                        <span>✨ Living Motion</span>
                                    </button>
                                </div>

                                <!-- Interactive Before/After Split Stage wrapping #atgPhotoLivingCanvas -->
                                <div class="atg-compare-stage is-comparing" id="atgPhotoCompareStage" style="--atg-split: 50%;">
                                    <canvas id="atgPhotoLivingCanvas" width="240" height="270"
                                            data-profile-photo="{{ $currentProfilePhotoUrl ?? '/resources/images/profile/avatarM.png' }}"
                                            class="atg-living-after-canvas"
                                            title="Tap your Living Avatar to make it react · move cursor for 2.5D depth"
                                            aria-label="Interactive stylised living avatar preview"></canvas>

                                    <div class="atg-compare-before-layer" id="atgCompareBeforeLayer" aria-hidden="true">
                                        <canvas id="atgPhotoBeforeCanvas" width="240" height="270" class="atg-before-raw-canvas"></canvas>
                                    </div>

                                    <span class="atg-compare-badge atg-compare-badge--before" id="atgCompareBadgeBefore">BEFORE</span>
                                    <span class="atg-compare-badge atg-compare-badge--after" id="atgCompareBadgeAfter">AFTER</span>

                                    <div class="atg-compare-divider" id="atgCompareDivider">
                                        <button type="button" id="atgCompareHandle" class="atg-compare-handle"
                                                role="slider" aria-label="Drag to compare original photo and stylised avatar"
                                                aria-valuemin="0" aria-valuemax="100" aria-valuenow="50">
                                            <span aria-hidden="true">↔</span>
                                        </button>
                                    </div>

                                    <div class="atg-p2a-loading-overlay" id="atgTransformLoadingOverlay" hidden>
                                        <div class="atg-p2a-loading-spinner" aria-hidden="true"></div>
                                        <div class="atg-p2a-loading-text" id="atgTransformLoadingText">Redrawing your portrait...</div>
                                    </div>

                                    <div id="atgPhotoReactLayer" class="atg-react-layer" aria-hidden="true" style="pointer-events: none;"></div>
                                </div>

                                <div class="atg-p2a-status-line" id="atgPhotoCanvasStatus">
                                    Drag the ↔ slider to compare Before &amp; After · or tap an expression below
                                </div>

                                <!-- Inline Webcam Snapshot Panel (Hidden until Camera is clicked) -->
                                <div class="atg-camera-panel" id="atgCameraCapturePanel" hidden>
                                    <video id="atgCameraVideo" class="atg-camera-video" autoplay playsinline muted></video>
                                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-2">
                                        <button type="button" id="atgCaptureSnapBtn" class="atg-btn-primary">
                                            <span>📸 Capture Portrait</span>
                                        </button>
                                        <button type="button" id="atgCancelCameraBtn" class="atg-btn-secondary">
                                            <span>✕ Cancel</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Upload / Camera / Current Profile Photo Bar -->
                                <div class="atg-p2a-upload-bar">
                                    <label for="atgPhotoUploadInput" class="atg-btn-primary atg-p2a-upload-btn" style="cursor: pointer; margin: 0;">
                                        <span>📷 Choose Photo</span>
                                    </label>
                                    <input type="file" id="atgPhotoUploadInput" accept="image/png,image/jpeg,image/webp" class="d-none">
                                    <button type="button" id="atgOpenCameraBtn" class="atg-btn-secondary atg-p2a-upload-btn">
                                        <span>📸 Camera</span>
                                    </button>
                                    <button type="button" id="atgUseCurrentProfilePhotoBtn"
                                            data-profile-photo="{{ $currentProfilePhotoUrl ?? '/resources/images/profile/avatarM.png' }}"
                                            class="atg-btn-secondary atg-p2a-upload-btn">
                                        <span>🖼️ Use Profile Photo</span>
                                    </button>
                                </div>
                                <div class="atg-p2a-drop-hint">
                                    Or drag and drop a JPEG, PNG or WEBP portrait onto this card
                                </div>

                                <!-- Living Photo Expressions & Choreography Bar -->
                                <div class="atg-p2a-expressions-wrap">
                                    <div class="atg-p2a-sublabel">Animate Your Stylised Avatar</div>
                                    <div class="d-flex flex-wrap justify-content-center gap-2" role="group" aria-label="Living photo expressions">
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold atg-photo-react-btn" data-photo-action="smile" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                            😄 Smile
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold atg-photo-react-btn" data-photo-action="dance" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                            💃 Dance
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold atg-photo-react-btn" data-photo-action="wink" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                            😉 Wink
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold atg-photo-react-btn" data-photo-action="laugh" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                            😂 Laugh
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold atg-photo-react-btn" data-photo-action="nod" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                            👍 Nod
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold atg-photo-react-btn" data-photo-action="love" style="min-height: 44px; min-width: 44px; white-space: nowrap; flex-shrink: 0;">
                                            ❤️ Love
                                        </button>
                                    </div>
                                </div>

                                <div class="atg-p2a-choreo-wrap text-start">
                                    <label for="atgPhotoChoreographySelect" class="atg-p2a-field-label">Saved Living Profile Motion:</label>
                                    <select id="atgPhotoChoreographySelect" class="form-select atg-p2a-select">
                                        <option value="smile" selected>😄 Warm Smile, Blink &amp; Breathe</option>
                                        <option value="dance">💃 Happy Dance &amp; Groove Loop</option>
                                        <option value="wink">😉 Cheeky Wink &amp; Head Sway</option>
                                        <option value="laugh">😂 Joyful Laugh &amp; Bounce</option>
                                        <option value="idle">😌 Natural Breathe &amp; Blink Only</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Right Controls Column: Avatar Style Gallery, Neural AI HD Redraw, Fine-Tuning & Save -->
                        <div class="atg-p2a-controls-col">
                            <!-- 1. Avatar Art Style Picker -->
                            <div class="atg-p2a-panel">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                    <div class="atg-p2a-panel-title">1. Choose Avatar Art Style</div>
                                    <button type="button" class="atg-style-reset-chip" id="atgStyleOriginalBtn" data-avatar-style="original" aria-pressed="false">
                                        📷 Original Photo
                                    </button>
                                </div>
                                <div class="atg-style-grid" role="radiogroup" aria-label="Avatar transformation style">
                                    @foreach ($studioAvatarStyles as $idx => $st)
                                        <button type="button"
                                                class="atg-style-card {{ $idx === 0 ? 'active' : '' }}"
                                                data-avatar-style="{{ $st['key'] }}"
                                                aria-pressed="{{ $idx === 0 ? 'true' : 'false' }}"
                                                style="--style-accent: {{ $st['accent'] }};">
                                            <div class="atg-style-card-top">
                                                <span class="atg-style-icon" aria-hidden="true">{{ $st['icon'] }}</span>
                                                <span class="atg-style-badge">{{ $st['badge'] }}</span>
                                            </div>
                                            <div class="atg-style-name">{{ $st['label'] }}</div>
                                            <div class="atg-style-tagline">{{ $st['tagline'] }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- 2. Neural AI Deep Redraw & On-Device Fine-Tuning -->
                            <div class="atg-p2a-panel">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                    <div class="atg-p2a-panel-title">2. Synthesise &amp; Fine-Tune</div>
                                    <span class="atg-identity-badge" id="atgIdentityScoreBadge">Identity Lock: 98.4%</span>
                                </div>
                                <p class="atg-p2a-helper">
                                    Selecting a style above previews instantly on your device. For a deep studio illustration redraw that preserves your exact facial geometry, spectacles, hair and pose, run the Neural AI Studio pass:
                                </p>
                                <div class="d-grid gap-2">
                                    <button type="button" id="atgTransformAvatarBtn" class="atg-btn-primary atg-btn-neural btn-spin-on-click" data-spin="true">
                                        <span>✨ Generate Neural AI Avatar (HD)</span>
                                    </button>
                                </div>

                                <!-- Progressive Disclosure Fine-Tuning Sliders -->
                                <div class="atg-finetune-accordion mt-3">
                                    <button type="button" id="atgToggleFineTuneBtn" class="atg-finetune-toggle" aria-expanded="false" aria-controls="atgFineTuneDrawer">
                                        <span>🎛️ Fine-Tune Stylisation &amp; Linework</span>
                                        <span class="atg-finetune-chevron" id="atgFineTuneChevron" aria-hidden="true">▾</span>
                                    </button>
                                    <div id="atgFineTuneDrawer" class="atg-finetune-drawer" hidden>
                                        <div class="atg-slider-row">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label for="atgStyleStrength" class="atg-slider-label">Stylisation Strength</label>
                                                <span class="atg-slider-val" id="atgStyleStrengthVal">78%</span>
                                            </div>
                                            <input type="range" id="atgStyleStrength" class="form-range" min="0.25" max="1.00" step="0.01" value="0.78">
                                        </div>
                                        <div class="atg-slider-row">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label for="atgStyleLineWeight" class="atg-slider-label">Ink Linework Crispness</label>
                                                <span class="atg-slider-val" id="atgStyleLineWeightVal">68%</span>
                                            </div>
                                            <input type="range" id="atgStyleLineWeight" class="form-range" min="0.00" max="1.00" step="0.01" value="0.68">
                                        </div>
                                        <div class="atg-slider-row">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label for="atgStyleDetailLock" class="atg-slider-label">Facial Detail Preservation</label>
                                                <span class="atg-slider-val" id="atgStyleDetailLockVal">80%</span>
                                            </div>
                                            <input type="range" id="atgStyleDetailLock" class="form-range" min="0.30" max="0.95" step="0.01" value="0.80">
                                        </div>
                                        <div class="text-end mt-2">
                                            <button type="button" id="atgResetStyleControlsBtn" class="atg-style-reset-chip">
                                                ↺ Reset to Balanced Defaults
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Save as Living Profile Picture, Download HD PNG, or Sync Cartoon Palette -->
                            <div class="atg-p2a-panel">
                                <div class="atg-p2a-panel-title mb-2">3. Apply or Export Your Avatar</div>
                                <div class="d-grid gap-2">
                                    <button type="button" id="atgSaveLivingPortraitBtn" class="atg-btn-primary btn-spin-on-click" data-spin="true">
                                        <span>✨ Set Avatar as Living Profile Picture</span>
                                    </button>
                                    <div class="atg-p2a-secondary-actions">
                                        <button type="button" id="atgDownloadAvatarBtn" class="atg-btn-secondary">
                                            <span>⬇️ Download HD Avatar</span>
                                        </button>
                                        <button type="button" id="atgShareAvatarBtn" class="atg-btn-secondary">
                                            <span>📤 Share Branded Avatar</span>
                                        </button>
                                        <button type="button" id="atgSyncPhotoToAvatarBtn" class="atg-btn-secondary btn-spin-on-click" data-spin="true">
                                            <span>🎨 Colour My 3D Cartoon</span>
                                        </button>
                                    </div>
                                </div>
                                <div id="atgPhotoMatchSummary" class="atg-p2a-match-summary" style="display: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Living Emojis Catalog -->
            <div id="catalog-tab" class="atg-tab-content" style="display: none;">
                <div class="atg-tone-bar" role="group" aria-label="Emoji skin tone">
                    <span class="atg-tone-label">Skin tone</span>
                    <button type="button" class="atg-tone-chip active" data-emoji-tone="" aria-pressed="true" aria-label="Classic" title="Classic">
                        <span class="atg-color-dot" style="background:#fbbf24"></span>
                    </button>
                    @foreach ($catalog['emoji_tones'] as $toneKey => $tone)
                        <button type="button" class="atg-tone-chip" data-emoji-tone="{{ $toneKey }}" aria-pressed="false" aria-label="{{ $tone['label'] }}" title="{{ $tone['label'] }}">
                            <span class="atg-color-dot" style="background: {{ $tone['base'] }};"></span>
                        </button>
                    @endforeach
                </div>
                <div class="atg-emoji-howto">
                    <strong>What do living emojis do?</strong>
                    <ol>
                        <li>They're animated family emojis — they wave, clap, point, beat and sparkle right inside posts and comments.</li>
                        <li>Tap one below to copy it, then paste it into a post or comment. Or pick it from <em>✨ 3D Family Emojis</em> in the post composer.</li>
                        <li>In the feed, anyone can tap it to open it in 3D / AR. Hands and people come in your chosen skin tone.</li>
                    </ol>
                </div>

                @foreach ($catalog['categories'] as $categoryName => $emojis)
                    <div class="atg-catalog-section">
                        <h3 class="atg-catalog-heading">
                            {{ $categoryName }}
                        </h3>
                        <div class="atg-catalog-grid">
                            @foreach ($emojis as $emoji)
                                <div class="atg-emoji-card {{ !empty($emoji['toneable']) ? 'is-toneable' : '' }}"
                                     data-emoji-id="{{ $emoji['id'] }}"
                                     data-emoji-base="{{ $emoji['id'] }}"
                                     data-emoji-title="{{ $emoji['title'] }}"
                                     data-toneable="{{ !empty($emoji['toneable']) ? '1' : '0' }}"
                                     role="button" tabindex="0"
                                     aria-label="{{ $emoji['title'] }} — tap to copy"
                                     title="Tap to copy :{{ $emoji['id'] }}:">
                                    <div class="atg-emoji-container">
                                        {!! $emoji['svg'] !!}
                                    </div>
                                    <div class="atg-emoji-card-title">{{ $emoji['title'] }}</div>
                                    <div class="atg-emoji-card-meaning">{{ $emoji['meaning'] }}</div>
                                    @if (!empty($emoji['toneable']))
                                        <span class="atg-tone-badge" aria-hidden="true">🎨</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</div>

<script nonce="{{ csp_nonce($nonce ?? null) }}">
    function switchAtgTab(tabId, btn) {
        document.querySelectorAll('.atg-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.atg-tab-content').forEach(c => c.style.display = 'none');
        if (btn) btn.classList.add('active');
        const target = document.getElementById(tabId);
        if (target) target.style.display = 'block';
    }

    (function () {
        const params = new URLSearchParams(window.location.search || '');
        const tabParam = params.get('tab');
        if (tabParam === 'photo-avatar' || params.has('photo_url')) {
            const photoTabBtn = document.querySelector('.atg-tab-btn[data-atg-tab="photo-avatar-tab"]');
            switchAtgTab('photo-avatar-tab', photoTabBtn);
        }
    })();

    // RFC-0011: "Use in a post" follows the last reaction the member tried.
    document.querySelectorAll('.atg-react-btn[data-reaction]').forEach(b => b.addEventListener('click', () => {
        const link = document.getElementById('atgUseInPostBtn');
        if (link && /^[a-z]+$/.test(b.dataset.reaction)) {
            link.href = '/member/profilePage?compose_sticker=' + b.dataset.reaction;
        }
    }));

    // Photo avatar: the member's real portrait claps, laughs, waves… exactly as in the Me-sticker.
    (function () {
        const img = document.getElementById('atgPhotoStickerStage');
        const viewport = document.getElementById('atgAvatarViewport');
        const btns = document.querySelectorAll('.atg-look-btn');
        if (!img || !viewport || !btns.length) return;
        const order = ['hello', 'laugh', 'wow', 'love', 'nod', 'dance', 'point', 'clap'];
        let current = 'hello';
        let bust = '';
        const show = (reaction) => {
            if (order.indexOf(reaction) === -1) return;
            current = reaction;
            syncLink();
            img.src = img.dataset.base + reaction + '?look=photo' + bust;
            img.alt = 'Your photo avatar: ' + reaction;
        };

        // Find the face once per photo (MediaPipe BlazeFace, runs on this device only) and
        // store its position so every sticker zooms in on it. Never blocks the studio.
        let faceTried = false;
        const MP = 'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14';
        const MODEL = 'https://storage.googleapis.com/mediapipe-models/face_detector/blaze_face_short_range/float16/1/blaze_face_short_range.tflite';
        const largest = (res) => (res && res.detections || [])
            .map(d => d.boundingBox).filter(Boolean)
            .sort((a, b) => b.width * b.height - a.width * a.height)[0] || null;
        const findFace = async () => {
            if (faceTried || img.dataset.faceKnown === '1' || !img.dataset.photoUrl) return;
            faceTried = true;
            try {
                const photo = new Image();
                photo.decoding = 'async';
                photo.src = img.dataset.photoUrl;
                await photo.decode();
                const W = photo.naturalWidth, H = photo.naturalHeight;
                const vision = await import(MP + '/vision_bundle.mjs');
                const fileset = await vision.FilesetResolver.forVisionTasks(MP + '/wasm');
                const detector = await vision.FaceDetector.createFromOptions(fileset, {
                    baseOptions: { modelAssetPath: MODEL, delegate: 'CPU' }, runningMode: 'IMAGE', minDetectionConfidence: 0.5
                });
                // Pass 1: whole photo (selfies, head-and-shoulders).
                let box = largest(detector.detect(photo));
                let fx = 0, fy = 0, fw = W, fh = H;
                if (!box) {
                    // Pass 2: small faces in full-length photos: zoom into the top-centre area.
                    fw = Math.round(W * 0.6); fh = Math.round(Math.min(H, fw * 1.2));
                    fx = Math.round((W - fw) / 2); fy = 0;
                    const c = document.createElement('canvas');
                    const k = Math.min(1, 1024 / Math.max(fw, fh));
                    c.width = Math.round(fw * k); c.height = Math.round(fh * k);
                    c.getContext('2d').drawImage(photo, fx, fy, fw, fh, 0, 0, c.width, c.height);
                    const b2 = largest(detector.detect(c));
                    if (b2) box = { originX: b2.originX / k, originY: b2.originY / k, width: b2.width / k, height: b2.height / k };
                }
                detector.close();
                if (!box) return;
                const payload = {
                    x: Math.max(0, (fx + box.originX) / W),
                    y: Math.max(0, (fy + box.originY) / H),
                    w: Math.min(1, box.width / W),
                    h: Math.min(1, box.height / H)
                };
                payload.w = Math.min(payload.w, 1 - payload.x);
                payload.h = Math.min(payload.h, 1 - payload.y);
                const token = document.querySelector('input[name="token"]')?.value ||
                    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const r = await fetch('/api/emoji/me-stickers/face', {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ box: payload, token: token, csrf_token: token })
                }).then(res => res.json());
                if (r && r.success) {
                    img.dataset.faceKnown = '1';
                    bust = '&f=' + encodeURIComponent(r.version || Date.now());
                    if (window.atgStickerLook === 'photo') show(current);
                }
            } catch (err) {
                console.info('[Me-sticker] face zoom skipped:', err && err.message ? err.message : err);
            }
        };
        const stage = viewport.closest('.atg-preview-stage');
        const link = document.getElementById('atgUseInPostBtn');
        const syncLink = () => { if (link) link.href = '/member/profilePage?compose_sticker=' + current; };
        const setLook = (look) => {
            window.atgStickerLook = look;
            const photo = look === 'photo';
            viewport.classList.toggle('atg-photo-look', photo);
            if (stage) stage.classList.toggle('atg-photo-mode', photo);
            img.hidden = !photo;
            btns.forEach(b => b.setAttribute('aria-checked', String(b.dataset.look === look)));
            if (photo) { show(current); findFace(); }
            try { localStorage.setItem('atgStickerLook', look); } catch (e) { /* private mode */ }
        };
        btns.forEach(b => b.addEventListener('click', () => setLook(b.dataset.look)));
        // Anything that edits the cartoon shows the cartoon, so changes are never invisible.
        document.querySelectorAll('#avatar-tab, #atgSyncPhotoToAvatarBtn').forEach(el =>
            el.addEventListener('click', (e) => {
                if (window.atgStickerLook === 'photo' && (el.id === 'atgSyncPhotoToAvatarBtn' || e.target.closest('.atg-trait-pill'))) {
                    setLook('avatar');
                }
            }));
        document.querySelectorAll('.atg-react-btn[data-reaction]').forEach(b =>
            b.addEventListener('click', () => { if (window.atgStickerLook === 'photo') show(b.dataset.reaction); }));
        viewport.addEventListener('click', () => {
            if (window.atgStickerLook === 'photo') show(order[(order.indexOf(current) + 1) % order.length]);
        });
        let saved = null;
        try { saved = localStorage.getItem('atgStickerLook'); } catch (e) { /* private mode */ }
        setLook(saved === 'avatar' ? 'avatar' : 'photo');
    })();

    // "Use in a post" stickers the look on screen right now — saved as the Me-sticker
    // avatar only, so a member's real profile photo is never replaced.
    (function () {
        const link = document.getElementById('atgUseInPostBtn');
        if (!link) return;
        link.addEventListener('click', function (e) {
            const engine = window.AtgEmojiEngine;
            if (!engine || !engine.state || link.dataset.busy === '1') return;
            e.preventDefault();
            link.dataset.busy = '1';
            link.setAttribute('aria-busy', 'true');
            const token = document.querySelector('input[name="token"]')?.value ||
                document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const go = () => { window.location.href = link.href; };
            const source = window.atgStickerLook === 'photo' ? 'photo' : 'avatar';
            fetch('/api/emoji/me-stickers/look', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(Object.assign({}, engine.state, { token: token, csrf_token: token, source: source }))
            }).then(r => r.json()).then(d => {
                if (d && d.success) { go(); return; }
                throw new Error((d && d.error) || 'save failed');
            }).catch(err => {
                link.dataset.busy = '';
                link.removeAttribute('aria-busy');
                if (window.Swal) {
                    window.Swal.fire({ icon: 'error', title: 'Could not prepare your sticker', text: String(err && err.message || err) });
                }
            });
        });
    })();
</script>
<script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/atg-avatar-styliser.js?v={{ assetVersion('js/atg-avatar-styliser.js') }}" defer></script>
@endsection
