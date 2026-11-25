@extends('layouts.profileBase')

@section('title', 'LIVING AVATAR & EMOJI STUDIO')

@push('styles')
<link rel="stylesheet" href="/public/css/atg-emoji.css?v={{ time() }}">
<link rel="stylesheet" href="/public/css/studio-pip.css?v={{ assetVersion('css/studio-pip.css') }}">
@endpush

@section('content')
<div class="atg-studio-wrapper" x-data="livingPhotoPip()" x-init="initLivingPhotoPip()">
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
                        {{-- ATG 60fps Living Canvas Stage (LivingHeirloomAnimator + Spring Physics + Particles) --}}
                        <canvas id="atgPhotoLivingStage" class="atg-photo-living-stage" width="512" height="512"
                                aria-label="Your animated living photo avatar" hidden></canvas>
                        {{-- Fallback SVG sticker if Canvas/JS is unavailable. --}}
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

                @if (!empty($hasProfilePhoto))
                    <div class="atg-photo-pills-row atg-photo-only" role="group" aria-label="Challenge frames">
                        <button type="button" class="atg-photo-pill" data-photo-frame="none" aria-pressed="true">Clean</button>
                        <button type="button" class="atg-photo-pill" data-photo-frame="birthday" aria-pressed="false">🥂 Birthday</button>
                        <button type="button" class="atg-photo-pill" data-photo-frame="sunday" aria-pressed="false">💃 Groove</button>
                        <button type="button" class="atg-photo-pill" data-photo-frame="proud" aria-pressed="false">👏 Proud</button>
                        <button type="button" class="atg-photo-pill" data-photo-frame="surprise" aria-pressed="false">😮 News</button>
                    </div>
                    <div class="atg-photo-pills-row atg-photo-only" role="group" aria-label="Aura packs">
                        <button type="button" class="atg-photo-pill" data-photo-pack="classic" aria-pressed="true">✨ Gold</button>
                        <button type="button" class="atg-photo-pill" data-photo-pack="royal_crown" aria-pressed="false" data-locked="true" title="Try 3 reactions or share to unlock">👑 Crown</button>
                        <button type="button" class="atg-photo-pill" data-photo-pack="fireworks" aria-pressed="false" data-locked="true" title="Try 5 reactions or download to unlock">🎆 Fireworks</button>
                        <button type="button" class="atg-photo-pill" data-photo-pack="kente_prism" aria-pressed="false" data-locked="true" title="Try all 8 reactions to unlock">🌈 Prism</button>
                    </div>
                @endif
            </div>

            <div class="atg-motion-controls">
                @if (!empty($hasProfilePhoto))
                    <button type="button" id="atgPhotoVoiceBtn" class="atg-motion-toggle atg-photo-only" aria-pressed="false">
                        <span class="atg-motion-icon">🎤</span>
                        <span><strong>Talk &amp; sing mode</strong><small>Real mouth &amp; jaw mesh moves to your voice · audio stays on your device</small></span>
                    </button>
                    <div class="atg-photo-pills-row atg-photo-only" role="group" aria-label="Synchronised duet mode">
                        <label class="atg-photo-pill" for="atgDuetPhotoInput" style="display:inline-flex;align-items:center;gap:6px;">
                            <span>🤝 Merge 2nd photo (Sync Duet)</span>
                            <input type="file" id="atgDuetPhotoInput" accept="image/png,image/jpeg,image/webp" hidden>
                        </label>
                        <button type="button" id="atgDuetClearBtn" class="atg-photo-pill" hidden>✕ Solo view</button>
                    </div>
                @endif
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
                @if (!empty($hasProfilePhoto))
                    <div class="atg-photo-export-row atg-photo-only">
                        <button type="button" id="atgDownloadPhotoAnimBtn" class="atg-btn-primary btn-spin-on-click" data-spin="true"
                                title="Download your animated living avatar as a video">
                            <span>⬇️ Video MP4</span>
                        </button>
                        <button type="button" id="atgDownloadPhotoGifBtn" class="atg-btn-secondary btn-spin-on-click" data-spin="true"
                                title="Download a looping animated GIF for WhatsApp, Messages &amp; social">
                            <span>🎞️ Loop GIF</span>
                        </button>
                        <button type="button" id="atgSharePhotoAnimBtn" class="atg-btn-secondary btn-spin-on-click" data-spin="true"
                                title="Share your animated living avatar to WhatsApp, Messages or social media">
                            <span>📤 Share</span>
                        </button>
                    </div>
                    <div class="atg-photo-pills-row atg-photo-only">
                        <select id="atgPhotoAspectSelect" class="form-select form-select-sm" style="max-width: 210px; min-height: 44px;" aria-label="Export aspect ratio">
                            <option value="1:1" selected>1:1 Square</option>
                            <option value="9:16">9:16 Story / Reels</option>
                            <option value="16:9">16:9 Widescreen</option>
                            <option value="4:5">4:5 Portrait</option>
                            <option value="3:4">3:4 Classic</option>
                            <option value="2:3">2:3 Tall</option>
                            <option value="3:2">3:2 Photo</option>
                            <option value="4:3">4:3 Standard</option>
                            <option value="5:4">5:4 Print</option>
                            <option value="9:21">9:21 Ultra Tall</option>
                            <option value="21:9">21:9 Cinema</option>
                        </select>
                    </div>
                @endif
            </div>
            <p class="atg-stage-note atg-cartoon-only">Tap a reaction above, then <em>Use in a post</em> — your avatar joins the post as a Me-sticker. Your profile photo stays as it is.</p>
            <p class="atg-stage-note atg-photo-only">This is your real profile photo. Tap a reaction above, then <em>Use in a post</em>: exactly this goes into your post.</p>
            @if (!empty($hasSavedLook))
                <p class="atg-stage-note">Loaded your current studio look.</p>
            @endif
        </div>

        <!-- Right Column: Trait Customisation & Kinship Catalog -->
        <div class="atg-controls-card">

            @php $defaultPhotoTab = !empty($hasProfilePhoto); @endphp
            <!-- Navigation Tabs (3D Tactile Mode Switcher) -->
            <div class="atg-tabs" role="tablist" aria-label="Emoji Studio modes">
                <button type="button" class="atg-tab-btn {{ !$defaultPhotoTab ? 'active' : '' }}" role="tab" data-atg-tab="avatar-tab" onclick="switchAtgTab('avatar-tab', this)" aria-selected="{{ !$defaultPhotoTab ? 'true' : 'false' }}">
                    <span class="atg-tab-icon3d" aria-hidden="true">
                        <svg viewBox="0 0 40 40" width="34" height="34" fill="none">
                            <defs>
                                <radialGradient id="atgTabAvBg" cx="35%" cy="28%" r="72%">
                                    <stop offset="0%" stop-color="#fde68a"/>
                                    <stop offset="55%" stop-color="#f59e0b"/>
                                    <stop offset="100%" stop-color="#b45309"/>
                                </radialGradient>
                                <linearGradient id="atgTabAvSkin" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#fff7ed"/>
                                    <stop offset="100%" stop-color="#fed7aa"/>
                                </linearGradient>
                            </defs>
                            <circle cx="20" cy="20" r="18" fill="url(#atgTabAvBg)"/>
                            <ellipse cx="14" cy="11" rx="6" ry="3" fill="rgba(255,255,255,0.45)" transform="rotate(-20 14 11)"/>
                            <circle cx="20" cy="15.5" r="5.5" fill="url(#atgTabAvSkin)"/>
                            <path d="M10.5 31c1.2-5 5-7.5 9.5-7.5s8.3 2.5 9.5 7.5" fill="url(#atgTabAvSkin)"/>
                        </svg>
                    </span>
                    <span class="atg-tab-copy">
                        <span class="atg-tab-title">Build 3D Avatar</span>
                        <span class="atg-tab-sub">Customise face &amp; outfit</span>
                    </span>
                </button>
                <button type="button" class="atg-tab-btn {{ $defaultPhotoTab ? 'active' : '' }}" role="tab" data-atg-tab="photo-avatar-tab" onclick="switchAtgTab('photo-avatar-tab', this)" aria-selected="{{ $defaultPhotoTab ? 'true' : 'false' }}">
                    <span class="atg-tab-icon3d" aria-hidden="true">
                        <svg viewBox="0 0 40 40" width="34" height="34" fill="none">
                            <defs>
                                <linearGradient id="atgTabPhCard" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#38bdf8"/>
                                    <stop offset="55%" stop-color="#2563eb"/>
                                    <stop offset="100%" stop-color="#1e3a8a"/>
                                </linearGradient>
                                <radialGradient id="atgTabPhSpark" cx="35%" cy="35%" r="70%">
                                    <stop offset="0%" stop-color="#fef08a"/>
                                    <stop offset="100%" stop-color="#f59e0b"/>
                                </radialGradient>
                            </defs>
                            <rect x="6" y="7" width="24" height="26" rx="6" transform="rotate(-6 18 20)" fill="url(#atgTabPhCard)" stroke="rgba(255,255,255,0.65)" stroke-width="1.5"/>
                            <circle cx="17.5" cy="17" r="4" fill="#e0f2fe"/>
                            <path d="M10.5 27.5c1.3-3.5 3.8-5.2 7-5.2s5.7 1.7 7 5.2" fill="#bae6fd"/>
                            <path d="M31 6l1.7 4.3L37 12l-4.3 1.7L31 18l-1.7-4.3L25 12l4.3-1.7L31 6z" fill="url(#atgTabPhSpark)"/>
                        </svg>
                    </span>
                    <span class="atg-tab-copy">
                        <span class="atg-tab-title">Photo to Avatar</span>
                        <span class="atg-tab-sub">Turn your photo into AI art</span>
                    </span>
                </button>
                <button type="button" class="atg-tab-btn" role="tab" data-atg-tab="catalog-tab" onclick="switchAtgTab('catalog-tab', this)" aria-selected="false">
                    <span class="atg-tab-icon3d" aria-hidden="true">
                        <svg viewBox="0 0 40 40" width="34" height="34" fill="none">
                            <defs>
                                <radialGradient id="atgTabEmOrb" cx="32%" cy="28%" r="72%">
                                    <stop offset="0%" stop-color="#fef08a"/>
                                    <stop offset="55%" stop-color="#fbbf24"/>
                                    <stop offset="100%" stop-color="#d97706"/>
                                </radialGradient>
                            </defs>
                            <circle cx="20" cy="20" r="17" fill="url(#atgTabEmOrb)"/>
                            <ellipse cx="14" cy="11" rx="5.5" ry="2.6" fill="rgba(255,255,255,0.55)" transform="rotate(-22 14 11)"/>
                            <circle cx="14.5" cy="18" r="2" fill="#451a03"/>
                            <circle cx="25.5" cy="18" r="2" fill="#451a03"/>
                            <path d="M13.5 23.5c1.6 3.2 4.1 4.5 6.5 4.5s4.9-1.3 6.5-4.5" stroke="#451a03" stroke-width="2.2" stroke-linecap="round"/>
                            <circle cx="33" cy="9" r="3" fill="#f43f5e" stroke="#ffffff" stroke-width="1.2"/>
                        </svg>
                    </span>
                    <span class="atg-tab-copy">
                        <span class="atg-tab-title">Living Emojis</span>
                        <span class="atg-tab-sub">Animated stickers for posts</span>
                    </span>
                </button>
            </div>

            <!-- TAB 1: Avatar Customiser -->
            <div id="avatar-tab" class="atg-tab-content" @if($defaultPhotoTab) style="display: none;" @endif>
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
            <div id="photo-avatar-tab" class="atg-tab-content" @if(!$defaultPhotoTab) style="display: none;" @endif>
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
                                <div class="spip-stage-bar">
                                    <span class="small fw-semibold" style="color: var(--atg-p2a-muted, #64748b);">Live Portrait Stage</span>
                                    <button type="button" class="spip-trigger" @click="togglePipPopout()" :aria-pressed="pipForced && !pipMinimised" title="Pop out preview while editing">
                                        <i class="bi bi-pip" aria-hidden="true"></i>
                                        <span x-text="(pipForced && !pipMinimised) ? 'Docked' : 'Pop out'"></span>
                                    </button>
                                </div>
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
                                    <canvas id="atgPhotoLivingCanvas" width="960" height="1080"
                                            data-profile-photo="{{ $currentProfilePhotoUrl ?? '/resources/images/profile/avatarM.png' }}"
                                            class="atg-living-after-canvas"
                                            title="Tap your Living Avatar to make it react · move cursor for 2.5D depth"
                                            aria-label="Interactive stylised living avatar preview"></canvas>

                                    <div class="atg-compare-before-layer" id="atgCompareBeforeLayer" aria-hidden="true">
                                        <canvas id="atgPhotoBeforeCanvas" width="960" height="1080" class="atg-before-raw-canvas"></canvas>
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

                                <!-- Tier B: In-House Neural Talking Movie Generator (Kokoro TTS + LivePortrait/MuseTalk) -->
                                <div class="atg-p2a-panel mt-3 text-start" id="atgNeuralTalkingCard">
                                    <div class="atg-p2a-panel-title mb-2">🎬 HD Talking Movie (Neural AI)</div>
                                    <label for="atgTalkingScript" class="atg-p2a-field-label">Type what your avatar should say (optional if uploading audio):</label>
                                    <textarea id="atgTalkingScript" class="form-control mb-2" rows="2" maxlength="500"
                                              placeholder="Happy birthday! Wishing our whole family a wonderful celebration today!"></textarea>

                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <select id="atgTalkingVoice" class="form-select" style="flex: 1 1 160px; min-height: 44px;" aria-label="Choose voice">
                                            @foreach (\App\services\TalkingAvatarNeuralService::VOICES as $vKey => $vLabel)
                                                <option value="{{ $vKey }}">{{ $vLabel }}</option>
                                            @endforeach
                                        </select>
                                        <select id="atgTalkingPreset" class="form-select" style="flex: 1 1 150px; min-height: 44px;" aria-label="Choose expression">
                                            @foreach (\App\services\TalkingAvatarNeuralService::EXPRESSIONS as $eKey => $eLabel)
                                                <option value="{{ $eKey }}">{{ $eLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2 mb-2 align-items-center">
                                        <label for="atgTalkingAudioInput" class="btn btn-sm btn-outline-secondary rounded-pill px-3 mb-0 d-inline-flex align-items-center" style="min-height: 44px; white-space: nowrap; flex-shrink: 0; cursor: pointer;">
                                            <span>📁 Upload voice memo</span>
                                            <input type="file" id="atgTalkingAudioInput" accept="audio/wav,audio/mpeg,audio/mp3,audio/webm,audio/ogg,audio/mp4" class="d-none">
                                        </label>
                                        <span id="atgTalkingAudioName" class="small text-muted"></span>
                                    </div>

                                    <button type="button" id="atgGenerateTalkingMovieBtn" class="atg-btn-primary w-100 btn-spin-on-click" data-spin="true" style="min-height: 44px; white-space: nowrap; font-size: 0.86rem; padding: 10px 12px;">
                                        <span>🎬 Generate Talking Video</span>
                                    </button>
                                    <div id="atgTalkingStatusMsg" class="small mt-2 text-muted" aria-live="polite"></div>

                                    <div id="atgTalkingVideoWrap" class="mt-2" hidden>
                                        <video id="atgTalkingResultVideo" class="w-100 rounded-3" controls playsinline></video>
                                        <a id="atgTalkingDownloadLink" class="atg-btn-secondary w-100 mt-2 text-decoration-none d-inline-flex align-items-center justify-content-center" download="talking-avatar.mp4" style="min-height: 44px; white-space: nowrap;">
                                            <span>⬇️ Download HD MP4</span>
                                        </a>
                                    </div>
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

                            <!-- 2 & 3 Inline Row: Synthesise/Fine-Tune + Apply/Publish/Export -->
                            <div class="atg-p2a-inline-row">
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

                                <!-- Inline Fine-Tuning Sliders (Visible by default for direct access) -->
                                <div class="atg-finetune-accordion mt-3">
                                    <button type="button" id="atgToggleFineTuneBtn" class="atg-finetune-toggle" aria-expanded="true" aria-controls="atgFineTuneDrawer">
                                        <span>🎛️ Fine-Tune Stylisation &amp; Linework</span>
                                        <span class="atg-finetune-chevron" id="atgFineTuneChevron" aria-hidden="true">▴</span>
                                    </button>
                                    <div id="atgFineTuneDrawer" class="atg-finetune-drawer">
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

                            <!-- 3. Save as Living Profile Picture, Post to Family Feed, Download HD PNG, or Sync Cartoon Palette -->
                            <div class="atg-p2a-panel">
                                <div class="atg-p2a-panel-title mb-2">3. Apply, Publish or Export Your Avatar</div>
                                <div class="d-grid gap-2" style="min-width: 0;">
                                    <button type="button" id="atgSaveLivingPortraitBtn" class="atg-btn-primary btn-spin-on-click w-100" data-spin="true" style="min-height: 44px; min-width: 0; white-space: nowrap; flex-shrink: 0;">
                                        <span>✨ Set as Profile Picture</span>
                                    </button>
                                    <button type="button" id="atgPostLivingPortraitBtn" class="atg-btn-secondary btn-spin-on-click w-100" data-spin="true" style="min-height: 44px; min-width: 0; white-space: nowrap; flex-shrink: 0; font-weight: 700;">
                                        <span>📣 Post to Family Feed</span>
                                    </button>
                                    <div class="atg-p2a-secondary-actions" style="min-width: 0;">
                                        <button type="button" id="atgDownloadAvatarBtn" class="atg-btn-secondary w-100" style="min-height: 44px; min-width: 0; white-space: nowrap; flex-shrink: 0;">
                                            <span>⬇️ Download HD Avatar</span>
                                        </button>
                                        <button type="button" id="atgShareAvatarBtn" class="atg-btn-secondary w-100" style="min-height: 44px; min-width: 0; white-space: nowrap; flex-shrink: 0;">
                                            <span>📤 Share Branded Avatar</span>
                                        </button>
                                        <button type="button" id="atgSyncPhotoToAvatarBtn" class="atg-btn-secondary btn-spin-on-click w-100" data-spin="true" style="min-height: 44px; min-width: 0; white-space: nowrap; flex-shrink: 0;">
                                            <span>🎨 Colour My 3D Cartoon</span>
                                        </button>
                                    </div>
                                </div>
                                <div id="atgPhotoMatchSummary" class="atg-p2a-match-summary" style="display: none;"></div>
                            </div>
                            </div><!-- /.atg-p2a-inline-row -->
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

    {{-- Floating PiP & Expandable Preview for Living Photo --}}
    @include('member.includes.studioPip', [
        'mode' => 'canvas',
        'title' => 'Living photo preview',
        'blockedExpr' => '!livingPhotoTabActive',
        'hasVideoControls' => false,
    ])
</div>

<script src="/public/js/studio-pip.js?v={{ assetVersion('js/studio-pip.js') }}" nonce="{{ csp_nonce($nonce ?? null) }}"></script>
<script nonce="{{ csp_nonce($nonce ?? null) }}">
    function livingPhotoPip() {
        const base = window.FPStudioPip ? window.FPStudioPip.create({
            stageSelector: '#atgPhotoStageCard',
            canvasSelector: '#atgPhotoLivingCanvas',
            storageKey: 'fp_pip_living_photo',
            fps: 12,
        }) : {
            stageVisible: true,
            pipForced: false,
            pipMinimised: false,
            pipExpanded: false,
            pipBottom: false,
            initPip: function () {},
            isPipDockVisible: function () { return false; },
            isPipPillVisible: function () { return false; },
            togglePipPopout: function () {},
            minimisePip: function () {},
            restorePip: function () {},
            expandPip: function () {},
            closeExpandedPip: function () {},
            toggleCorner: function () {},
        };

        return Object.assign({}, base, {
            livingPhotoTabActive: false,
            initLivingPhotoPip: function () {
                const self = this;
                if (typeof this.initPip === 'function') {
                    this.initPip();
                }
                const syncTab = function (tabId) {
                    self.livingPhotoTabActive = (tabId === 'photo-avatar-tab');
                    if (!self.livingPhotoTabActive) {
                        self.pipExpanded = false;
                    }
                };
                const activeTab = document.querySelector('.atg-tab-btn.active');
                syncTab(activeTab ? activeTab.getAttribute('data-atg-tab') : '');
                window.addEventListener('atg:tab-changed', function (e) {
                    syncTab(e && e.detail ? e.detail.tabId : '');
                });
            },
        });
    }

    function switchAtgTab(tabId, btn) {
        document.querySelectorAll('.atg-tab-btn').forEach(b => {
            b.classList.remove('active');
            b.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('.atg-tab-content').forEach(c => c.style.display = 'none');
        if (btn) {
            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');
        }
        const target = document.getElementById(tabId);
        if (target) target.style.display = 'block';
        window.dispatchEvent(new CustomEvent('atg:tab-changed', { detail: { tabId: tabId } }));
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

    // Photo avatar: ATG 60fps Living Canvas Stage (LivingHeirloomAnimator + spring physics + particles + video export/share),
    // with graceful fallback to the SVG Me-sticker if Canvas is unavailable.
    (function () {
        const img = document.getElementById('atgPhotoStickerStage');
        const canvas = document.getElementById('atgPhotoLivingStage');
        const viewport = document.getElementById('atgAvatarViewport');
        const btns = document.querySelectorAll('.atg-look-btn');
        if (!img || !viewport || !btns.length) return;
        const order = ['hello', 'laugh', 'wow', 'love', 'nod', 'dance', 'point', 'clap'];
        let current = 'hello';
        let bust = '';
        let livingStage = null;
        let livingReady = false;

        const show = (reaction, playSound) => {
            if (order.indexOf(reaction) === -1) return;
            current = reaction;
            syncLink();
            if (livingReady && livingStage) {
                img.hidden = true;
                livingStage.start();
                livingStage.triggerReaction(reaction, { playSound: Boolean(playSound) });
            } else {
                img.src = img.dataset.base + reaction + '?look=photo' + bust;
                img.alt = 'Your photo avatar: ' + reaction;
            }
        };

        const initLivingStage = async () => {
            if (!canvas || !img.dataset.photoUrl || typeof window.AtgLivingPhotoAvatarStage !== 'function') return;
            const syncPacksUi = (stageInst) => {
                document.querySelectorAll('.atg-photo-pill[data-photo-pack]').forEach(btn => {
                    const key = btn.dataset.photoPack;
                    const unlocked = stageInst.isPackUnlocked(key);
                    btn.dataset.locked = unlocked ? 'false' : 'true';
                    btn.setAttribute('aria-pressed', String(stageInst.currentPack === key));
                });
            };
            const stageInstance = new window.AtgLivingPhotoAvatarStage({
                canvas: canvas,
                viewport: viewport,
                photoUrl: img.dataset.photoUrl,
                senderName: @json(trim((string)($userData['firstname'] ?? ''))),
                onPackUnlocked: (pack) => {
                    syncPacksUi(stageInstance);
                    if (window.swalToast) window.swalToast('Unlocked ' + pack.icon + ' ' + pack.label + ' aura!', 'success');
                },
                onVoiceStateChange: (active) => {
                    const vBtn = document.getElementById('atgPhotoVoiceBtn');
                    if (vBtn) vBtn.setAttribute('aria-pressed', String(Boolean(active)));
                },
                onDuetStateChange: (active) => {
                    const clearBtn = document.getElementById('atgDuetClearBtn');
                    if (clearBtn) clearBtn.hidden = !active;
                },
            });
            const ok = await stageInstance.init();
            if (!ok) return;
            livingStage = stageInstance;
            livingReady = true;
            syncPacksUi(livingStage);
            if (window.atgStickerLook === 'photo') {
                img.hidden = true;
                livingStage.start();
                livingStage.triggerReaction(current, { playSound: false });
            }
        };

        document.querySelectorAll('.atg-photo-pill[data-photo-frame]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!livingStage) return;
                livingStage.setFrame(btn.dataset.photoFrame);
                document.querySelectorAll('.atg-photo-pill[data-photo-frame]').forEach(b => b.setAttribute('aria-pressed', String(b === btn)));
            });
        });

        document.querySelectorAll('.atg-photo-pill[data-photo-pack]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!livingStage) return;
                const key = btn.dataset.photoPack;
                if (!livingStage.setPack(key)) {
                    if (window.swalToast) window.swalToast(btn.getAttribute('title') || 'Keep reacting to unlock this aura pack!', 'info');
                    return;
                }
                document.querySelectorAll('.atg-photo-pill[data-photo-pack]').forEach(b => b.setAttribute('aria-pressed', String(b === btn)));
                livingStage.triggerReaction(current, { playSound: true });
            });
        });

        const voiceBtn = document.getElementById('atgPhotoVoiceBtn');
        if (voiceBtn) {
            voiceBtn.addEventListener('click', async () => {
                if (!livingStage || !livingReady) return;
                try {
                    await livingStage.toggleVoiceReactive();
                } catch (err) {
                    if (window.Swal) {
                        window.Swal.fire({ icon: 'warning', title: 'Microphone unavailable', text: String(err && err.message || err) });
                    }
                }
            });
        }

        const duetInput = document.getElementById('atgDuetPhotoInput');
        const duetClearBtn = document.getElementById('atgDuetClearBtn');
        if (duetInput) {
            duetInput.addEventListener('change', (e) => {
                const file = e.target && e.target.files && e.target.files[0];
                if (file && livingStage) livingStage.loadDuetFile(file);
            });
        }
        if (duetClearBtn) {
            duetClearBtn.addEventListener('click', () => {
                if (livingStage) livingStage.clearDuet();
                if (duetInput) duetInput.value = '';
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initLivingStage, { once: true });
        } else {
            initLivingStage();
        }

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
                    if (livingStage) livingStage.applyFaceBox(payload);
                    if (window.atgStickerLook === 'photo') show(current, false);
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
            if (photo) {
                if (livingReady && livingStage) {
                    img.hidden = true;
                    livingStage.start();
                } else {
                    img.hidden = false;
                }
                show(current, false);
                findFace();
            } else {
                img.hidden = true;
                if (livingStage) livingStage.stop();
            }
            btns.forEach(b => b.setAttribute('aria-checked', String(b.dataset.look === look)));
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
            b.addEventListener('click', () => { if (window.atgStickerLook === 'photo') show(b.dataset.reaction, true); }));
        viewport.addEventListener('click', () => {
            if (window.atgStickerLook === 'photo' && !livingReady) {
                show(order[(order.indexOf(current) + 1) % order.length], true);
            }
        });
        viewport.addEventListener('keydown', (e) => {
            if (window.atgStickerLook === 'photo' && (e.key === 'Enter' || e.key === ' ')) {
                e.preventDefault();
                show(order[(order.indexOf(current) + 1) % order.length], true);
            }
        });

        // Full-animation Download & Share via SovereignVideoStudioClient + ShareManager
        const runExport = async (btn, mode) => {
            if (!btn || btn.dataset.busy === '1') return;
            if (!livingStage || !livingReady) {
                if (window.Swal) {
                    window.Swal.fire({ icon: 'info', title: 'Preparing your living photo', text: 'Please wait a moment for your portrait to finish loading.' });
                }
                return;
            }
            btn.dataset.busy = '1';
            btn.disabled = true;
            if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.start(btn);
            try {
                if (mode === 'download') {
                    await livingStage.downloadAnimatedVideo();
                    if (window.swalToast) window.swalToast('Animated avatar video downloaded!', 'success');
                } else if (mode === 'gif') {
                    await livingStage.downloadAnimatedGif();
                    if (window.swalToast) window.swalToast('Looping GIF downloaded!', 'success');
                } else {
                    await livingStage.shareAnimatedVideo();
                }
            } catch (err) {
                if (window.Swal) {
                    window.Swal.fire({ icon: 'error', title: 'Could not export animation', text: String(err && err.message || err) });
                }
            } finally {
                btn.dataset.busy = '';
                btn.disabled = false;
                if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
            }
        };

        const dlBtn = document.getElementById('atgDownloadPhotoAnimBtn');
        const gifBtn = document.getElementById('atgDownloadPhotoGifBtn');
        const shBtn = document.getElementById('atgSharePhotoAnimBtn');
        const aspectSel = document.getElementById('atgPhotoAspectSelect');
        if (dlBtn) dlBtn.addEventListener('click', () => runExport(dlBtn, 'download'));
        if (gifBtn) gifBtn.addEventListener('click', () => runExport(gifBtn, 'gif'));
        if (shBtn) shBtn.addEventListener('click', () => runExport(shBtn, 'share'));
        if (aspectSel) {
            aspectSel.addEventListener('change', () => {
                if (livingStage) livingStage.setAspectRatio(aspectSel.value);
            });
        }

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
<script type="module" nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/atg-living-photo-avatar.js?v={{ assetVersion('js/atg-living-photo-avatar.js') }}"></script>
<script nonce="{{ csp_nonce($nonce ?? null) }}" src="/public/js/atg-avatar-styliser.js?v={{ assetVersion('js/atg-avatar-styliser.js') }}" defer></script>
<script nonce="{{ csp_nonce($nonce ?? null) }}">
    // Tier B: Neural Talking Movie Generator (Kokoro TTS + LivePortrait/MuseTalk sidecar)
    (function () {
        const btn = document.getElementById('atgGenerateTalkingMovieBtn');
        const scriptEl = document.getElementById('atgTalkingScript');
        const voiceEl = document.getElementById('atgTalkingVoice');
        const presetEl = document.getElementById('atgTalkingPreset');
        const audioInput = document.getElementById('atgTalkingAudioInput');
        const audioNameEl = document.getElementById('atgTalkingAudioName');
        const statusEl = document.getElementById('atgTalkingStatusMsg');
        const videoWrap = document.getElementById('atgTalkingVideoWrap');
        const videoEl = document.getElementById('atgTalkingResultVideo');
        const dlLink = document.getElementById('atgTalkingDownloadLink');
        if (!btn) return;

        let audioDataUrl = '';
        if (audioInput) {
            audioInput.addEventListener('change', () => {
                const file = audioInput.files && audioInput.files[0];
                if (!file) { audioDataUrl = ''; if (audioNameEl) audioNameEl.textContent = ''; return; }
                if (audioNameEl) audioNameEl.textContent = file.name;
                const reader = new FileReader();
                reader.onload = () => { audioDataUrl = typeof reader.result === 'string' ? reader.result : ''; };
                reader.readAsDataURL(file);
            });
        }

        const showResultVideo = (url) => {
            if (!videoWrap || !videoEl || !dlLink) return;
            videoEl.src = url;
            dlLink.href = url;
            videoWrap.hidden = false;
        };

        const renderBrowserTalkingFallback = (canvas, preset, uploadedAudioUrl) => {
            return new Promise((resolve, reject) => {
                if (typeof canvas.captureStream !== 'function' || typeof MediaRecorder === 'undefined') {
                    reject(new Error('Your browser does not support canvas video recording.'));
                    return;
                }
                const engine = window.AtgEmojiEngine;
                const actionMap = { natural: 'laugh', smile: 'smile', laugh: 'laugh', wink: 'wink', sing: 'dance' };
                const mappedAction = actionMap[preset] || 'smile';
                if (engine && typeof engine.playPhotoReaction === 'function') {
                    engine.playPhotoReaction(mappedAction);
                    if (engine.motion) {
                        engine.motion.talking = true;
                        engine.motion.talkLevel = 0.65;
                    }
                }

                const stream = canvas.captureStream(30);
                let audioEl = null;
                let audioCtx = null;
                if (uploadedAudioUrl) {
                    try {
                        audioEl = new Audio(uploadedAudioUrl);
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        audioCtx = new AudioCtx();
                        const src = audioCtx.createMediaElementSource(audioEl);
                        const dest = audioCtx.createMediaStreamDestination();
                        src.connect(dest);
                        src.connect(audioCtx.destination);
                        dest.stream.getAudioTracks().forEach(t => stream.addTrack(t));
                        audioEl.play().catch(() => {});
                    } catch (_) { /* video-only fallback */ }
                }

                const mimes = ['video/mp4;codecs=avc1', 'video/webm;codecs=vp9', 'video/webm'];
                const mime = mimes.find(m => MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(m)) || 'video/webm';
                const chunks = [];
                let recorder;
                try {
                    recorder = new MediaRecorder(stream, { mimeType: mime, videoBitsPerSecond: 6000000 });
                } catch (_) {
                    recorder = new MediaRecorder(stream);
                }
                recorder.ondataavailable = (e) => { if (e.data && e.data.size > 0) chunks.push(e.data); };
                recorder.onstop = () => {
                    if (engine && engine.motion) {
                        engine.motion.talking = false;
                        engine.motion.talkLevel = 0;
                    }
                    if (audioEl) { audioEl.pause(); audioEl.src = ''; }
                    if (audioCtx && typeof audioCtx.close === 'function') audioCtx.close().catch(() => {});
                    resolve(URL.createObjectURL(new Blob(chunks, { type: mime })));
                };
                recorder.onerror = (err) => reject(err);
                recorder.start(100);
                setTimeout(() => { if (recorder.state === 'recording') recorder.stop(); }, 3200);
            });
        };

        const pollStatus = async (jobId) => {
            for (let i = 0; i < 40; i++) {
                await new Promise(r => setTimeout(r, 1500));
                const res = await fetch('/api/emoji/talking-avatar/status/' + encodeURIComponent(jobId), {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json());
                const data = res && res.data ? res.data : res;
                if (data && data.status === 'completed' && data.video_url) {
                    showResultVideo(data.video_url);
                    if (statusEl) statusEl.textContent = '✅ Your HD neural talking video is ready!';
                    return;
                }
                if (data && data.status === 'failed') {
                    throw new Error(data.error || 'Neural render failed.');
                }
            }
            throw new Error('Render timed out waiting for GPU worker.');
        };

        btn.addEventListener('click', async () => {
            if (btn.dataset.busy === '1') return;
            const canvas = document.getElementById('atgPhotoLivingCanvas');
            if (!canvas) return;
            const scriptText = scriptEl ? scriptEl.value.trim() : '';
            if (!scriptText && !audioDataUrl) {
                if (window.swalToast) window.swalToast('Type a script or upload a voice memo first.', 'info');
                return;
            }
            btn.dataset.busy = '1';
            btn.disabled = true;
            if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.start(btn);
            if (statusEl) statusEl.textContent = 'Submitting portrait to ATG Neural GPU worker…';

            try {
                const token = document.querySelector('input[name="token"]')?.value ||
                    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('/api/emoji/talking-avatar/render', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({
                        token: token,
                        csrf_token: token,
                        image_data_url: canvas.toDataURL('image/png'),
                        script_text: scriptText,
                        audio_data_url: audioDataUrl,
                        voice_id: voiceEl ? voiceEl.value : 'bf_emma',
                        expression_preset: presetEl ? presetEl.value : 'natural',
                    })
                }).then(r => r.json());

                const payload = res && res.data ? res.data : res;
                if (!res || res.success === false) {
                    throw new Error((res && res.message) || (res && res.error) || 'Could not queue render.');
                }
                if (payload.video_url) {
                    showResultVideo(payload.video_url);
                    if (statusEl) statusEl.textContent = payload.message || 'Ready!';
                } else if (payload.sidecar_online && payload.job_id) {
                    if (statusEl) statusEl.textContent = payload.message || 'Rendering on GPU…';
                    await pollStatus(payload.job_id);
                } else {
                    if (statusEl) statusEl.textContent = 'GPU worker offline — rendering locally with ATG Mesh Puppet…';
                    const localUrl = await renderBrowserTalkingFallback(canvas, presetEl ? presetEl.value : 'natural', audioDataUrl);
                    showResultVideo(localUrl);
                    if (statusEl) statusEl.textContent = '✅ Your animated talking video is ready (local ATG engine)!';
                }
            } catch (err) {
                if (statusEl) statusEl.textContent = String(err && err.message || err);
                if (window.Swal) window.Swal.fire({ icon: 'error', title: 'Talking movie error', text: String(err && err.message || err) });
            } finally {
                btn.dataset.busy = '';
                btn.disabled = false;
                if (window.ScoreTenantSpinner) window.ScoreTenantSpinner.stop(btn);
            }
        });
    })();
</script>
@endsection
